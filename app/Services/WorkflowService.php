<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationWorkflow;
use App\Models\ApplicationWorkflowHistory;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowTransition;
use App\Models\WorkflowVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowService
{
    public function startFor(Application $application): ApplicationWorkflow
    {
        return DB::transaction(function () use ($application) {
            $existing = $application->workflow;
            if ($existing) {
                return $existing->load('currentStage', 'version');
            }

            $definition = WorkflowDefinition::where('is_active', true)
                ->where(function ($q) use ($application) {
                    $q->where('training_type_id', $application->applicationWindow->training_type_id)
                        ->orWhereNull('training_type_id');
                })
                ->with(['versions' => fn ($q) => $q->where('status', 'PUBLISHED')->latest('version')])
                ->get()
                ->first(fn ($d) => $d->versions->isNotEmpty());

            if (!$definition) {
                throw ValidationException::withMessages([
                    'workflow' => 'No published workflow is configured for this training type.',
                ]);
            }

            $version = $definition->versions->first();
            $stage = $version->stages()->where('is_starting', true)->first()
                ?? $version->stages()->orderBy('stage_order')->first();

            if (!$stage) {
                throw ValidationException::withMessages(['workflow' => 'The published workflow has no stages.']);
            }

            $workflow = ApplicationWorkflow::create([
                'application_id' => $application->id,
                'workflow_version_id' => $version->id,
                'current_stage_id' => $stage->id,
            ]);

            ApplicationWorkflowHistory::create([
                'application_workflow_id' => $workflow->id,
                'to_stage_id' => $stage->id,
                'acted_at' => now(),
            ]);

            return $workflow->load('currentStage', 'version');
        });
    }

    public function transition(
        ApplicationWorkflow $workflow,
        string $action,
        ?User $actor,
        ?string $comment = null,
    ): ApplicationWorkflow {
        return DB::transaction(function () use ($workflow, $action, $actor, $comment) {
            $workflow = ApplicationWorkflow::query()
                ->whereKey($workflow->id)
                ->lockForUpdate()
                ->firstOrFail();

            $workflow->load(
                'application.department',
                'application.placement',
                'currentStage',
                'version',
            );

            $transition = $this->findTransition($workflow, $action);

            if ($transition->requires_comment && blank($comment)) {
                throw ValidationException::withMessages([
                    'comment' => 'A comment is required for this workflow action.',
                ]);
            }

            if ($transition->action === 'COMPLETE_PLACEMENT' && !$workflow->application?->placement) {
                throw ValidationException::withMessages([
                    'transition' => 'A placement must be allocated before the placement stage can be completed.',
                ]);
            }

            $this->authorizeActor($workflow, $transition, $actor);

            $from = $workflow->current_stage_id;
            $workflow->update([
                'current_stage_id' => $transition->to_stage_id,
                'completed_at' => $transition->toStage->is_terminal ? now() : null,
            ]);

            ApplicationWorkflowHistory::create([
                'application_workflow_id' => $workflow->id,
                'from_stage_id' => $from,
                'to_stage_id' => $transition->to_stage_id,
                'transition_id' => $transition->id,
                'acted_by' => $actor?->id,
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            return $workflow->fresh()->load('currentStage', 'version', 'history');
        });
    }

    public function findTransition(ApplicationWorkflow $workflow, string $action): WorkflowTransition
    {
        $transition = WorkflowTransition::query()
            ->where('workflow_version_id', $workflow->workflow_version_id)
            ->where('from_stage_id', $workflow->current_stage_id)
            ->where('action', strtoupper($action))
            ->with(['responsibleRole', 'toStage'])
            ->first();

        if (!$transition) {
            throw ValidationException::withMessages([
                'transition' => 'This action is not configured for the current workflow stage.',
            ]);
        }

        return $transition;
    }

    private function authorizeActor(
        ApplicationWorkflow $workflow,
        WorkflowTransition $transition,
        ?User $actor,
    ): void {
        if (!$actor) {
            abort(403, 'An authenticated staff account is required.');
        }

        if (
            $workflow->currentStage?->responsible_role_id
            && !$actor->roles()->whereKey($workflow->currentStage->responsible_role_id)->exists()
        ) {
            abort(403, 'You do not have the role assigned to this workflow stage.');
        }

        if (
            $workflow->currentStage?->required_permission
            && !$actor->hasPermission($workflow->currentStage->required_permission)
        ) {
            abort(403, 'You do not have permission to act on this workflow stage.');
        }

        if (
            $transition->responsible_role_id
            && !$actor->roles()->whereKey($transition->responsible_role_id)->exists()
        ) {
            abort(403, 'You do not have the role assigned to this workflow action.');
        }

        if (
            $transition->required_permission
            && !$actor->hasPermission($transition->required_permission)
        ) {
            abort(403, 'You do not have permission to perform this workflow action.');
        }

        if ($transition->responsibleRole?->slug === 'hod') {
            if (!$workflow->application?->department_id) {
                throw ValidationException::withMessages([
                    'workflow' => 'This application has no department assigned for HOD routing.',
                ]);
            }

            if (!$actor->departments()->whereKey($workflow->application->department_id)->exists()) {
                abort(403, 'This application is routed to a different department HOD.');
            }
        }
    }

    public function createVersion(
        WorkflowDefinition $definition,
        ?User $actor,
        ?string $summary = null,
    ): WorkflowVersion {
        $next = ((int) $definition->versions()->max('version')) + 1;

        return $definition->versions()->create([
            'version' => $next,
            'status' => 'DRAFT',
            'change_summary' => $summary,
            'created_by' => $actor?->id,
        ]);
    }
}
