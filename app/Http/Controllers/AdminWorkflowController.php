<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\TrainingType;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStage;
use App\Models\WorkflowTransition;
use App\Models\WorkflowVersion;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminWorkflowController extends Controller
{
    private const ACTIONS = [
        'START_REVIEW' => ['label' => 'Start review', 'permission' => 'applications.review', 'result_status' => 'UNDER_REVIEW'],
        'FORWARD' => ['label' => 'Forward', 'permission' => 'applications.forward', 'result_status' => 'UNDER_REVIEW'],
        'RETURN' => ['label' => 'Return for correction', 'permission' => 'applications.return', 'result_status' => 'RETURNED'],
        'REJECT' => ['label' => 'Reject', 'permission' => 'applications.reject', 'result_status' => 'REJECTED'],
        'ACCEPT' => ['label' => 'Approve for next stage', 'permission' => 'applications.accept', 'result_status' => 'ACCEPTED'],
        'COMPLETE_PLACEMENT' => ['label' => 'Complete placement', 'permission' => 'placements.manage', 'result_status' => 'ACCEPTED'],
    ];

    public function index()
    {
        return view('admin.workflows.index', ['workflows' => WorkflowDefinition::with(['trainingType', 'versions'])->latest()->paginate(15)]);
    }

    public function create()
    {
        return view('admin.workflows.create', ['trainingTypes' => TrainingType::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:workflow_definitions,code'],
            'training_type_id' => ['nullable', 'exists:training_types,id'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
        $definition = WorkflowDefinition::create($data);
        
        app(WorkflowService::class)->createVersion($definition, $request->user(), 'Initial workflow version');
        return redirect()->route('admin.workflows.show', $definition)->with('success', 'Workflow created. Configure the draft before publishing it.');
    }

    public function show(WorkflowDefinition $workflow)
    {
        $workflow->load(['trainingType', 'versions.stages.responsibleRole', 'versions.transitions.fromStage', 'versions.transitions.toStage', 'versions.transitions.responsibleRole']);
        $draft = $workflow->versions()->where('status', 'DRAFT')->latest('version')->first();
        return view('admin.workflows.designer', [
            'workflow' => $workflow,
            'draft' => $draft,
            'roles' => Role::orderBy('name')->get(['id', 'name', 'slug']),
            'actions' => self::ACTIONS,
            'resultStatuses' => ['UNDER_REVIEW'=>'Under review','RETURNED'=>'Returned for correction','ACCEPTED'=>'Accepted','REJECTED'=>'Rejected'],
        ]);
    }

    public function createVersion(Request $request, WorkflowDefinition $workflow)
    {
        $data = $request->validate(['change_summary' => ['nullable', 'string', 'max:5000']]);
        $version = app(WorkflowService::class)->createVersion($workflow, $request->user(), $data['change_summary'] ?? null);
        $published = $workflow->versions()->where('status', 'PUBLISHED')->latest('version')->with(['stages', 'transitions'])->first();

        if ($published) {
            $stageMap = [];
            foreach ($published->stages->sortBy('stage_order') as $stage) {
                $stageMap[$stage->code] = WorkflowStage::create([
                    'workflow_version_id'=>$version->id,'name'=>$stage->name,'code'=>$stage->code,'stage_order'=>$stage->stage_order,
                    'required_permission'=>$stage->required_permission,'responsible_role_id'=>$stage->responsible_role_id,
                    'is_terminal'=>$stage->is_terminal,'is_starting'=>$stage->is_starting,
                ]);
            }
            foreach ($published->transitions as $transition) {
                if (isset($stageMap[$transition->fromStage->code], $stageMap[$transition->toStage->code])) {
                    WorkflowTransition::create([
                        'workflow_version_id'=>$version->id,'from_stage_id'=>$stageMap[$transition->fromStage->code]->id,
                        'to_stage_id'=>$stageMap[$transition->toStage->code]->id,'action'=>$transition->action,'label'=>$transition->label,
                        'result_status'=>$transition->result_status,'required_permission'=>$transition->required_permission,
                        'responsible_role_id'=>$transition->responsible_role_id,'requires_comment'=>$transition->requires_comment,
                    ]);
                }
            }
        }
        return back()->with('success', "Draft version {$version->version} created.");
    }

    public function updateVersion(Request $request, WorkflowVersion $version)
    {
        abort_unless($version->status === 'DRAFT', 422, 'Only draft workflow versions can be edited.');
        $data = $request->validate([
            'starting_stage_code'=>['required','string','max:100','alpha_dash'],
            'stages'=>['required','array','min:1'],
            'stages.*.name'=>['required','string','max:150'],
            'stages.*.code'=>['required','string','max:100','alpha_dash'],
            'stages.*.responsible_role_id'=>['nullable','integer','exists:roles,id'],
            'stages.*.is_terminal'=>['nullable','boolean'],
            'transitions'=>['nullable','array'],
            'transitions.*.from_code'=>['required','string','max:100','alpha_dash'],
            'transitions.*.to_code'=>['required','string','max:100','alpha_dash'],
            'transitions.*.action'=>['required',Rule::in(array_keys(self::ACTIONS))],
            'transitions.*.label'=>['required','string','max:150'],
            'transitions.*.responsible_role_id'=>['nullable','integer','exists:roles,id'],
            'transitions.*.result_status'=>['required',Rule::in(['UNDER_REVIEW','RETURNED','ACCEPTED','REJECTED'])],
            'transitions.*.requires_comment'=>['nullable','boolean'],
        ]);
        $codes=collect($data['stages'])->pluck('code');
        if($codes->duplicates()->isNotEmpty()) throw ValidationException::withMessages(['stages'=>'Stage codes must be unique.']);
        if(!$codes->contains($data['starting_stage_code'])) throw ValidationException::withMessages(['starting_stage_code'=>'The starting stage must be one of the configured stages.']);

        DB::transaction(function() use($version,$data){
            WorkflowTransition::where('workflow_version_id',$version->id)->delete();
            WorkflowStage::where('workflow_version_id',$version->id)->delete();
            $stageMap=[];
            foreach(array_values($data['stages']) as $index=>$stage){
                $created=WorkflowStage::create([
                    'workflow_version_id'=>$version->id,'name'=>$stage['name'],'code'=>$stage['code'],'stage_order'=>$index+1,
                    'required_permission'=>null,'responsible_role_id'=>$stage['responsible_role_id']??null,
                    'is_terminal'=>!empty($stage['is_terminal']),'is_starting'=>$stage['code']===$data['starting_stage_code'],
                ]);
                $stageMap[$created->code]=$created;
            }
            foreach($data['transitions']??[] as $transition){
                if(!isset($stageMap[$transition['from_code']],$stageMap[$transition['to_code']])) throw ValidationException::withMessages(['transitions'=>'Every transition must reference existing stages.']);
                $action=self::ACTIONS[$transition['action']];
                WorkflowTransition::create([
                    'workflow_version_id'=>$version->id,'from_stage_id'=>$stageMap[$transition['from_code']]->id,'to_stage_id'=>$stageMap[$transition['to_code']]->id,
                    'action'=>$transition['action'],'label'=>$transition['label'],'result_status'=>$transition['result_status'],
                    'required_permission'=>$action['permission'],'responsible_role_id'=>$transition['responsible_role_id']??null,
                    'requires_comment'=>!empty($transition['requires_comment']),
                ]);
            }
        });
        return back()->with('success','Draft workflow saved successfully.');
    }

    public function publish(Request $request, WorkflowVersion $version)
    {
        abort_unless($version->status === 'DRAFT', 422, 'Only draft versions can be published.');
        $version->load(['definition','stages','transitions']);
        if($version->stages->isEmpty()) throw ValidationException::withMessages(['workflow'=>'Add at least one stage before publishing.']);
        if(!$version->stages->where('is_terminal',true)->count()) throw ValidationException::withMessages(['workflow'=>'Mark at least one stage as a final stage.']);
        if($version->stages->where('is_starting',true)->count()!==1) throw ValidationException::withMessages(['workflow'=>'A workflow must have exactly one starting stage.']);
        if($version->transitions->isEmpty()) throw ValidationException::withMessages(['workflow'=>'Add at least one transition before publishing.']);
        $stageIds=$version->stages->pluck('id');
        foreach($version->transitions as $transition) if(!$stageIds->contains($transition->from_stage_id)||!$stageIds->contains($transition->to_stage_id)) throw ValidationException::withMessages(['workflow'=>'Every transition must use stages from this version.']);
        DB::transaction(function() use($version){$version->definition->versions()->where('status','PUBLISHED')->update(['status'=>'ARCHIVED']);$version->update(['status'=>'PUBLISHED','published_at'=>now()]);});
        return back()->with('success',"Workflow version {$version->version} published successfully.");
    }
}
