<?php

namespace App\Services;

use App\Models\Application;
use App\Models\DocumentType;
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

    private const LIFECYCLE_ACTIONS = [
        self::ACTION_START_REVIEW,
        self::ACTION_FORWARD,
        self::ACTION_RETURN,
        self::ACTION_REJECT,
        self::ACTION_ACCEPT,
    ];

    private const RESULT_STATUSES = [
        'SUBMITTED',
        'UNDER_REVIEW',
        'RETURNED',
        'ACCEPTED',
        'REJECTED',
    ];

    public function __construct(
        private readonly WorkflowService $workflows,
    ) {}

    public function submit(Application $application): Application
    {
        return DB::transaction(function () use ($application): Application {
            $locked = Application::query()
                ->lockForUpdate()
                ->findOrFail($application->id);

            if (! $locked->isEditable()) {
                throw ValidationException::withMessages([
                    'application' => 'This application cannot be submitted in its current state.',
                ]);
            }

            $this->assertReadyForSubmission($locked);

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

        if (! in_array($action, self::LIFECYCLE_ACTIONS, true)) {
            throw ValidationException::withMessages(['action' => 'Unsupported application lifecycle action.']);
        }

        return DB::transaction(function () use ($application, $actor, $action, $comment): Application {
            $locked = Application::query()
                ->lockForUpdate()
                ->with('workflow')
                ->findOrFail($application->id);

            if (! $locked->workflow) {
                throw ValidationException::withMessages([
                    'workflow' => 'This application has not been initialized in the workflow.',
                ]);
            }

            $transition = $this->workflows->findTransition($locked->workflow, $action);
            $status = strtoupper((string) $transition->result_status);

            if (! in_array($status, self::RESULT_STATUSES, true)) {
                throw ValidationException::withMessages([
                    'transition' => 'The workflow contains an invalid application outcome.',
                ]);
            }

            $old = $locked->only(['status', 'reviewed_at']);
            $this->workflows->transition($locked->workflow, $action, $actor, $comment);

            $updates = ['status' => $status];
            if (in_array($status, ['ACCEPTED', 'REJECTED'], true)) {
                $updates['reviewed_at'] = now();
            } elseif ($status === 'RETURNED') {
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
            $locked = Application::query()
                ->lockForUpdate()
                ->with(['workflow', 'placement'])
                ->findOrFail($application->id);

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

            $transition = $this->workflows->findTransition(
                $locked->workflow,
                self::ACTION_COMPLETE_PLACEMENT,
            );
            $status = strtoupper((string) $transition->result_status);

            if ($status !== 'ACCEPTED') {
                throw ValidationException::withMessages([
                    'transition' => 'The placement completion transition must preserve the accepted application outcome.',
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

    private function assertReadyForSubmission(Application $application): void
    {
        if (! $application->department_id) {
            throw ValidationException::withMessages([
                'department_id' => 'Please select the department that should review this application.',
            ]);
        }

        if (! $application->applicationWindow?->isOpen()) {
            throw ValidationException::withMessages([
                'application_window_id' => 'The application window is no longer open.',
            ]);
        }

        $requiredFields = [
            'reason_for_application',
            'interests',
            'expected_objectives',
            'current_study_year',
            'training_start_date',
            'training_end_date',
        ];

        $missing = [];
        foreach ($requiredFields as $field) {
            if (blank($application->{$field})) {
                $missing[$field] = 'This field is required before submission.';
            }
        }

        if ($missing !== []) {
            throw ValidationException::withMessages($missing);
        }

        if ($application->current_study_year < 1 || $application->current_study_year > 20) {
            throw ValidationException::withMessages([
                'current_study_year' => 'Current study year is invalid.',
            ]);
        }

        if ($application->training_start_date->isPast() && !$application->training_start_date->isToday()) {
            throw ValidationException::withMessages([
                'training_start_date' => 'Training start date cannot be in the past.',
            ]);
        }

        if (!$application->training_end_date->isAfter($application->training_start_date)) {
            throw ValidationException::withMessages([
                'training_end_date' => 'Training end date must be after the start date.',
            ]);
        }

        $required = DocumentType::query()
            ->where('is_active', true)
            ->where('is_required', true)
            ->pluck('id');
        $uploaded = $application->documents()->pluck('document_type_id');

        if ($required->diff($uploaded)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'documents' => 'Please upload all required documents before submitting your application.',
            ]);
        }
    }
}
