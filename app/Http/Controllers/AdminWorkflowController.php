<?php

namespace App\Http\Controllers;

use App\Models\TrainingType;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStage;
use App\Models\WorkflowTransition;
use App\Models\WorkflowVersion;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminWorkflowController extends Controller
{
    public function index() { return view('admin.workflows.index',['workflows'=>WorkflowDefinition::with(['trainingType','versions'])->latest()->get(),'trainingTypes'=>TrainingType::orderBy('name')->get()]); }

    public function store(Request $request)
    {
        $data=$request->validate(['name'=>'required|string|max:255','code'=>'required|string|max:100|alpha_dash|unique:workflow_definitions,code','training_type_id'=>'nullable|exists:training_types,id','description'=>'nullable|string|max:5000']);
        $definition=WorkflowDefinition::create($data); app(WorkflowService::class)->createVersion($definition,$request->user(),'Initial workflow version');
        return redirect()->route('admin.workflows.show',$definition)->with('success','Workflow created with a draft version.');
    }

    public function show(WorkflowDefinition $workflow)
    {
        $workflow->load(['trainingType','versions.stages','versions.transitions.fromStage','versions.transitions.toStage']);
        return view('admin.workflows.show',['workflow'=>$workflow,'draft'=>$workflow->versions()->where('status','DRAFT')->latest('version')->first()]);
    }

    public function createVersion(Request $request, WorkflowDefinition $workflow)
    {
        $data=$request->validate(['change_summary'=>'nullable|string|max:5000']);
        $version=app(WorkflowService::class)->createVersion($workflow,$request->user(),$data['change_summary']??null);
        $published=$workflow->versions()->where('status','PUBLISHED')->latest('version')->with('stages')->first();
        if($published){ foreach($published->stages()->orderBy('stage_order')->get() as $stage) WorkflowStage::create(['workflow_version_id'=>$version->id,'name'=>$stage->name,'code'=>$stage->code,'stage_order'=>$stage->stage_order,'required_permission'=>$stage->required_permission,'is_terminal'=>$stage->is_terminal]); foreach($published->transitions as $transition){ $from=$version->stages()->where('code',$transition->fromStage->code)->first(); $to=$version->stages()->where('code',$transition->toStage->code)->first(); if($from&&$to) WorkflowTransition::create(['workflow_version_id'=>$version->id,'from_stage_id'=>$from->id,'to_stage_id'=>$to->id,'action'=>$transition->action,'label'=>$transition->label,'required_permission'=>$transition->required_permission,'requires_comment'=>$transition->requires_comment]); } }
        return back()->with('success',"Draft version {$version->version} created.");
    }

    public function updateVersion(Request $request, WorkflowVersion $version)
    {
        abort_unless($version->status==='DRAFT',422,'Only draft workflow versions can be edited.');
        $data=$request->validate([
            'stages'=>['required','array','min:1'],
            'stages.*.name'=>['required','string','max:150'],
            'stages.*.code'=>['required','string','max:100','alpha_dash'],
            'stages.*.required_permission'=>['nullable','string','max:150'],
            'stages.*.is_terminal'=>['nullable','boolean'],
            'transitions'=>['nullable','array'],
            'transitions.*.from_stage_code'=>['required','string','max:100'],
            'transitions.*.to_stage_code'=>['required','string','max:100'],
            'transitions.*.action'=>['required','string','max:100','alpha_dash'],
            'transitions.*.label'=>['required','string','max:150'],
            'transitions.*.required_permission'=>['nullable','string','max:150'],
            'transitions.*.requires_comment'=>['nullable','boolean'],
        ]);
        $stageCodes=collect($data['stages'])->pluck('code');
        if($stageCodes->duplicates()->isNotEmpty()) throw ValidationException::withMessages(['stages'=>'Stage codes must be unique within a workflow version.']);
        foreach($data['transitions']??[] as $i=>$transition){ if($transition['from_stage_code']===$transition['to_stage_code']) throw ValidationException::withMessages(["transitions.$i.to_stage_code"=>'A transition cannot target the same stage.']); if(!$stageCodes->contains($transition['from_stage_code'])||!$stageCodes->contains($transition['to_stage_code'])) throw ValidationException::withMessages(["transitions.$i.from_stage_code"=>'Transition stages must reference existing stage codes.']); }
        DB::transaction(function() use($version,$data){
            WorkflowTransition::where('workflow_version_id',$version->id)->delete(); WorkflowStage::where('workflow_version_id',$version->id)->delete();
            $stages=[]; foreach(array_values($data['stages']) as $index=>$stage) $stages[$stage['code']]=WorkflowStage::create(['workflow_version_id'=>$version->id,'name'=>$stage['name'],'code'=>$stage['code'],'stage_order'=>$index+1,'required_permission'=>$stage['required_permission']??null,'is_terminal'=>!empty($stage['is_terminal'])]);
            foreach($data['transitions']??[] as $transition) WorkflowTransition::create(['workflow_version_id'=>$version->id,'from_stage_id'=>$stages[$transition['from_stage_code']]->id,'to_stage_id'=>$stages[$transition['to_stage_code']]->id,'action'=>$transition['action'],'label'=>$transition['label'],'required_permission'=>$transition['required_permission']??null,'requires_comment'=>!empty($transition['requires_comment'])]);
        });
        return back()->with('success','Draft workflow version updated.');
    }

    public function publish(Request $request, WorkflowVersion $version)
    {
        abort_unless($version->status==='DRAFT',422,'Only draft versions can be published.'); $version->load(['definition','stages','transitions']);
        if($version->stages->isEmpty() || !$version->stages->where('is_terminal',true)->count()) throw ValidationException::withMessages(['workflow'=>'A workflow must contain at least one stage and one terminal stage before publishing.']);
        $nonTerminal=$version->stages->where('is_terminal',false);
        foreach($nonTerminal as $stage) if(!$version->transitions->where('from_stage_id',$stage->id)->count()) throw ValidationException::withMessages(['workflow'=>"Stage {$stage->name} has no outgoing transition."]);
        DB::transaction(function() use($version){ $version->definition->versions()->where('status','PUBLISHED')->update(['status'=>'ARCHIVED']); $version->update(['status'=>'PUBLISHED','published_at'=>now()]); });
        return back()->with('success',"Workflow version {$version->version} published.");
    }
}
