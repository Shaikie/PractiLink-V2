<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationWorkflow;
use App\Models\ApplicationWorkflowDuty;
use App\Models\ApplicationWorkflowHistory;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStage;
use App\Models\WorkflowTransition;
use App\Models\WorkflowVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowService
{
    public const ASSIGN_ROLE = 'ROLE';
    public const ASSIGN_STAFF = 'STAFF';
    public const ASSIGN_DEPARTMENT_ROLE = 'DEPARTMENT_ROLE';

    public function startFor(Application $application): ApplicationWorkflow
    {
        return DB::transaction(function () use ($application) {
            if ($application->workflow) {
                return $application->workflow->load('currentStage.duties', 'assignedUser', 'version');
            }

            $trainingTypeId = $application->applicationWindow?->training_type_id;
            $definition = WorkflowDefinition::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->where('training_type_id', $trainingTypeId)->orWhereNull('training_type_id'))
                ->with(['versions' => fn ($q) => $q->where('status', 'PUBLISHED')->latest('version')])
                ->get()
                ->sortBy(fn ($d) => $d->training_type_id === $trainingTypeId ? 0 : 1)
                ->first(fn ($d) => $d->versions->isNotEmpty());

            if (!$definition) {
                throw ValidationException::withMessages(['workflow' => 'No published workflow is configured for this training type.']);
            }

            $version = $definition->versions->first();
            $stage = $version->stages()->with('duties')->where('is_starting', true)->first()
                ?? $version->stages()->with('duties')->orderBy('stage_order')->first();

            if (!$stage) {
                throw ValidationException::withMessages(['workflow' => 'The published workflow has no starting stage.']);
            }

            $workflow = ApplicationWorkflow::create([
                'application_id' => $application->id,
                'workflow_version_id' => $version->id,
                'current_stage_id' => $stage->id,
                'assigned_user_id' => $this->resolveAssignee($stage, $application),
            ]);

            $this->seedDuties($workflow, $stage);

            ApplicationWorkflowHistory::create([
                'application_workflow_id' => $workflow->id,
                'to_stage_id' => $stage->id,
                'acted_at' => now(),
            ]);

            return $workflow->fresh()->load('currentStage.duties', 'assignedUser', 'version');
        });
    }

    public function transition(ApplicationWorkflow $workflow, string $action, ?User $actor, ?string $comment = null): ApplicationWorkflow
    {
        return DB::transaction(function () use ($workflow, $action, $actor, $comment) {
            $workflow = ApplicationWorkflow::query()->whereKey($workflow->id)->lockForUpdate()->firstOrFail();
            $workflow->load('application.department', 'application.placement', 'currentStage.duties', 'currentStage.responsibleRole', 'assignedUser', 'version');

            if ($workflow->completed_at) {
                throw ValidationException::withMessages(['workflow' => 'This workflow has already been completed.']);
            }

            $transition = $this->findTransition($workflow, $action);
            if ($transition->requires_comment && blank($comment)) {
                throw ValidationException::withMessages(['comment' => 'A comment is required for this workflow action.']);
            }

            $this->authorizeActor($workflow, $transition, $actor);

            if (in_array($transition->action, ['FORWARD', 'ACCEPT', 'COMPLETE_PLACEMENT'], true)) {
                $this->assertRequiredDutiesComplete($workflow);
            }

            if ($transition->action === 'COMPLETE_PLACEMENT' && !$workflow->application?->placement) {
                throw ValidationException::withMessages(['transition' => 'A placement with a supervisor must be allocated before completing the final stage.']);
            }

            $from = $workflow->current_stage_id;
            $isRejected = $transition->action === 'REJECT';
            $workflow->update([
                'current_stage_id' => $transition->to_stage_id,
                'assigned_user_id' => $this->resolveAssignee($transition->toStage, $workflow->application),
                'completed_at' => ($isRejected || $transition->toStage->is_terminal) ? now() : null,
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

            if (!$workflow->completed_at) {
                $this->resetDutiesForStage($workflow, $transition->toStage);
            }

            return $workflow->fresh()->load('currentStage.duties', 'assignedUser', 'version', 'history.toStage');
        });
    }

    public function completeDuty(ApplicationWorkflowDuty $duty, User $actor): ApplicationWorkflowDuty
    {
        return DB::transaction(function () use ($duty, $actor) {
            $duty->load('workflow.currentStage', 'workflow.application.department', 'stage');
            $workflow = $duty->workflow;

            if ($workflow->completed_at || $workflow->current_stage_id !== $duty->workflow_stage_id) {
                throw ValidationException::withMessages(['duty' => 'This duty is not active for the current workflow stage.']);
            }

            $this->authorizeStageActor($workflow, $actor);
            $duty->update(['completed_by' => $actor->id, 'completed_at' => now()]);

            return $duty->fresh();
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
            throw ValidationException::withMessages(['transition' => 'This action is not configured for the current workflow stage.']);
        }

        return $transition;
    }

    public function createVersion(WorkflowDefinition $definition, ?User $actor, ?string $summary = null): WorkflowVersion
    {
        $next = ((int) $definition->versions()->max('version')) + 1;
        return $definition->versions()->create([
            'version' => $next,
            'status' => 'DRAFT',
            'change_summary' => $summary,
            'created_by' => $actor?->id,
        ]);
    }

    private function authorizeActor(ApplicationWorkflow $workflow, WorkflowTransition $transition, ?User $actor): void
    {
        $this->authorizeStageActor($workflow, $actor);

        if ($transition->responsible_role_id && !$actor->roles()->whereKey($transition->responsible_role_id)->exists()) {
            abort(403, 'You do not have the role assigned to this workflow action.');
        }

        if ($transition->required_permission && !$actor->hasPermission($transition->required_permission)) {
            abort(403, 'You do not have permission to perform this workflow action.');
        }
    }

    private function authorizeStageActor(ApplicationWorkflow $workflow, ?User $actor): void
    {
        if (!$actor || !$actor->is_active) {
            abort(403, 'An active staff account is required.');
        }

        $stage = $workflow->currentStage;
        if ($workflow->assigned_user_id) {
            abort_unless((int) $workflow->assigned_user_id === (int) $actor->id, 403, 'This application is assigned to another staff member.');
            return;
        }

        if ($stage->responsible_role_id && !$actor->roles()->whereKey($stage->responsible_role_id)->exists()) {
            abort(403, 'You do not have the role assigned to this workflow stage.');
        }

        if ($stage->assignment_mode === self::ASSIGN_DEPARTMENT_ROLE) {
            $departmentId = $workflow->application?->department_id;
            if (!$departmentId || !$actor->departments()->whereKey($departmentId)->exists()) {
                abort(403, 'This application is routed to a different department.');
            }
        }

        if ($stage->required_permission && !$actor->hasPermission($stage->required_permission)) {
            abort(403, 'You do not have permission to work on this stage.');
        }
    }

    private function assertRequiredDutiesComplete(ApplicationWorkflow $workflow): void
    {
        $required = $workflow->currentStage->duties->where('is_required', true);
        if ($required->isEmpty()) return;

        $completed = ApplicationWorkflowDuty::query()
            ->where('application_workflow_id', $workflow->id)
            ->where('workflow_stage_id', $workflow->current_stage_id)
            ->whereNotNull('completed_at')
            ->whereIn('workflow_stage_duty_id', $required->pluck('id'))
            ->count();

        if ($completed !== $required->count()) {
            throw ValidationException::withMessages(['duties' => 'Complete every required duty before forwarding or completing this stage.']);
        }
    }

    private function resolveAssignee(WorkflowStage $stage, Application $application): ?int
    {
        if ($stage->assigned_user_id && $stage->assignedUser?->is_active) {
            return $stage->assigned_user_id;
        }

        $query = User::query()->where('is_active', true);
        if ($stage->responsible_role_id) {
            $query->whereHas('roles', fn ($q) => $q->whereKey($stage->responsible_role_id));
        }

        if ($stage->assignment_mode === self::ASSIGN_DEPARTMENT_ROLE) {
            $query->whereHas('departments', fn ($q) => $q->whereKey($application->department_id));
        }

        return $query->orderBy('id')->value('id');
    }

    private function seedDuties(ApplicationWorkflow $workflow, WorkflowStage $stage): void
    {
        foreach ($stage->duties as $duty) {
            ApplicationWorkflowDuty::firstOrCreate([
                'application_workflow_id' => $workflow->id,
                'workflow_stage_duty_id' => $duty->id,
            ], [
                'workflow_stage_id' => $stage->id,
            ]);
        }
    }

    private function resetDutiesForStage(ApplicationWorkflow $workflow, WorkflowStage $stage): void
    {
        ApplicationWorkflowDuty::where('application_workflow_id', $workflow->id)
            ->where('workflow_stage_id', $stage->id)
            ->delete();
        $this->seedDuties($workflow, $stage);
    }
}
