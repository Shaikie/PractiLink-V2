<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Owns the business state of an application.
 *
 * WorkflowService owns routing (stage changes and actor assignment). Keeping
 * the two responsibilities separate prevents controllers from deciding what
 * an application status means while still allowing workflow definitions to
 * decide where the application goes next.
 */
class ApplicationLifecycleService
{
    public const ACTION_START_REVIEW = 'START_REVIEW';
    public const ACTION_FORWARD = 'FORWARD';
    public const ACTION_RETURN = 'RETURN';
    public const ACTION_REJECT = 'REJECT';
    public const ACTION_ACCEPT = 'ACCEPT';
    public const ACTION_COMPLETE_PLACEMENT = 'COMPLETE_PLACEMENT';

    private const ACTION_STATUS = [
        self::ACTION_START_REVIEW => 'UNDER_REVIEW',
        self::ACTION_FORWARD => 'UNDER_REVIEW',
        self::ACTION_RETURN => 'RETURNED',
        self::ACTION_REJECT => 'REJECTED',
        self::ACTION_ACCEPT => 'ACCEPTED',
    ];

    public function __construct(
        private readonly WorkflowService $workflows,
    ) {}

    public function submit(Application $application): Application
    {
        return DB::transaction(function () use ($application): Application {
            $locked = Application::query()->lockForUpdate()->findOrFail($application->id);

            if (! $locked->isEditable()) {
                throw ValidationException::withMessages([
                    'application' => 'This application cannot be submitted in its current state.',
                ]);
            }

            $old = $locked->only(['status', 'submitted_at', 'reviewed_at']);
            $locked->update([
                'status' => 'SUBMITTED',
                'submitted_at' => now(),
                'reviewed_at' => null,
            ]);

            $updated = $locked->fresh();
            $this->workflows->startFor($updated);

            AuditLogger::record(
                'application.submitted',
                $updated,
                $old,
                $updated->only(['status', 'submitted_at', 'reviewed_at']),
            );

            return $updated;
        });
    }

    public function act(Application $application, User $actor, string $action, ?string $comment = null): Application
    {
        $action = strtoupper($action);

        if ($action === self::ACTION_COMPLETE_PLACEMENT) {
            return $this->completePlacement($application, $actor, $comment);
        }

        if (! array_key_exists($action, self::ACTION_STATUS)) {
            throw ValidationException::withMessages(['action' => 'Unsupported application lifecycle action.']);
        }

        return DB::transaction(function () use ($application, $actor, $action, $comment): Application {
            $locked = Application::query()->lockForUpdate()->with('workflow')->findOrFail($application->id);

            if (! $locked->workflow) {
                throw ValidationException::withMessages([
                    'workflow' => 'This application has not been initialized in the workflow.',
                ]);
            }

            $old = $locked->only(['status', 'reviewed_at']);
            $this->workflows->transition($locked->workflow, $action, $actor, $comment);

            $status = self::ACTION_STATUS[$action];
            $updates = ['status' => $status];

            if (in_array($action, [self::ACTION_ACCEPT, self::ACTION_REJECT], true)) {
                $updates['reviewed_at'] = now();
            } elseif ($action === self::ACTION_RETURN) {
                $updates['reviewed_at'] = null;
            }

            $locked->update($updates);
            $updated = $locked->fresh();

            AuditLogger::record(
                'application.workflow_action',
                $updated,
                $old,
                $updated->only(['status', 'reviewed_at']),
            );

            return $updated;
        });
    }

    public function cancel(Application $application): Application
    {
        return DB::transaction(function () use ($application): Application {
            $locked = Application::query()->lockForUpdate()->findOrFail($application->id);

            if (! in_array($locked->status, ['DRAFT', 'SUBMITTED', 'RETURNED'], true)) {
                throw ValidationException::withMessages([
                    'application' => 'This application cannot be cancelled in its current state.',
                ]);
            }

            $old = ['status' => $locked->status];
            $locked->update(['status' => 'CANCELLED']);
            $updated = $locked->fresh();

            AuditLogger::record('application.cancelled', $updated, $old, ['status' => 'CANCELLED']);

            return $updated;
        });
    }

    private function completePlacement(Application $application, User $actor, ?string $comment = null): Application
    {
        return DB::transaction(function () use ($application, $actor, $comment): Application {
            $locked = Application::query()->lockForUpdate()->with(['workflow', 'placement'])->findOrFail($application->id);

            if ($locked->status !== 'ACCEPTED') {
                throw ValidationException::withMessages([
                    'application' => 'Only an accepted application can complete placement.',
                ]);
            }

            if (! $locked->placement) {
                throw ValidationException::withMessages([
                    'placement' => 'A placement must be allocated before the workflow can be completed.',
                ]);
            }

            if (! $locked->workflow) {
                throw ValidationException::withMessages([
                    'workflow' => 'This application has no workflow instance.',
                ]);
            }

            $this->workflows->transition(
                $locked->workflow,
                self::ACTION_COMPLETE_PLACEMENT,
                $actor,
                $comment,
            );

            return $locked->fresh();
        });
    }
}
