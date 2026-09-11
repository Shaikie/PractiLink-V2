<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationWorkflow;
use App\Models\ApplicationWorkflowHistory;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowTransition;
use App\Models\WorkflowVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowService
{
    public function startFor(Application $application): ApplicationWorkflow
    {
        return DB::transaction(function () use ($application) {
            $existing = $application->workflow;
            if ($existing) return $existing->load('currentStage', 'version');
            $definition = WorkflowDefinition::where('is_active', true)->where(function ($q) use ($application) {
                $q->where('training_type_id', $application->applicationWindow->training_type_id)->orWhereNull('training_type_id');
            })->with(['versions'=>fn($q)=>$q->where('status','PUBLISHED')->latest('version')])->get()->first(fn($d)=>$d->versions->isNotEmpty());
            if (!$definition) throw ValidationException::withMessages(['workflow'=>'No published workflow is configured for this training type.']);
            $version=$definition->versions->first(); $stage=$version->stages()->orderBy('stage_order')->first();
            if (!$stage) throw ValidationException::withMessages(['workflow'=>'The published workflow has no stages.']);
            $workflow=ApplicationWorkflow::create(['application_id'=>$application->id,'workflow_version_id'=>$version->id,'current_stage_id'=>$stage->id]);
            ApplicationWorkflowHistory::create(['application_workflow_id'=>$workflow->id,'to_stage_id'=>$stage->id,'acted_at'=>now()]);
            return $workflow->load('currentStage','version');
        });
    }

    public function transition(ApplicationWorkflow $workflow, string $action, ?User $actor, ?string $comment=null): ApplicationWorkflow
    {
        return DB::transaction(function () use ($workflow,$action,$actor,$comment) {
            $workflow = ApplicationWorkflow::query()->whereKey($workflow->id)->lockForUpdate()->firstOrFail();
            $workflow->load('currentStage','version');
            $transition=WorkflowTransition::where('workflow_version_id',$workflow->workflow_version_id)->where('from_stage_id',$workflow->current_stage_id)->where('action',strtoupper($action))->first();
            if (!$transition) throw ValidationException::withMessages(['transition'=>'This action is not configured for the current workflow stage.']);
            if ($transition->requires_comment && blank($comment)) throw ValidationException::withMessages(['comment'=>'A comment is required for this workflow action.']);
            if ($workflow->currentStage?->required_permission && (!$actor || !$actor->hasPermission($workflow->currentStage->required_permission))) abort(403,'You do not have permission to act on this workflow stage.');
            if ($transition->required_permission && (!$actor || !$actor->hasPermission($transition->required_permission))) abort(403,'You do not have permission to perform this workflow action.');
            $from=$workflow->current_stage_id;
            $workflow->update(['current_stage_id'=>$transition->to_stage_id,'completed_at'=>$transition->toStage()->value('is_terminal')?now():null]);
            ApplicationWorkflowHistory::create(['application_workflow_id'=>$workflow->id,'from_stage_id'=>$from,'to_stage_id'=>$transition->to_stage_id,'transition_id'=>$transition->id,'acted_by'=>$actor?->id,'comment'=>$comment,'acted_at'=>now()]);
            return $workflow->fresh()->load('currentStage','version','history');
        });
    }

    public function createVersion(WorkflowDefinition $definition, ?User $actor, ?string $summary=null): WorkflowVersion
    {
        $next=((int)$definition->versions()->max('version'))+1;
        return $definition->versions()->create(['version'=>$next,'status'=>'DRAFT','change_summary'=>$summary,'created_by'=>$actor?->id]);
    }
}
