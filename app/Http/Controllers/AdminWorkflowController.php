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
    public function index()
    {
        return view('admin.workflows.index', ['workflows' => WorkflowDefinition::with(['trainingType', 'versions'])->latest()->get(), 'trainingTypes' => TrainingType::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name'=>'required|string|max:255','code'=>'required|string|max:100|alpha_dash|unique:workflow_definitions,code','training_type_id'=>'nullable|exists:training_types,id','description'=>'nullable|string|max:5000']);
        $definition = WorkflowDefinition::create($data);
        app(WorkflowService::class)->createVersion($definition, $request->user(), 'Initial workflow version');
        return redirect()->route('admin.workflows.show', $definition)->with('success', 'Workflow created with a draft version.');
    }

    public function show(WorkflowDefinition $workflow)
    {
        $workflow->load(['trainingType','versions.stages','versions.transitions']);
        return view('admin.workflows.show', ['workflow' => $workflow, 'draft' => $workflow->versions()->where('status','DRAFT')->latest('version')->first()]);
    }

    public function createVersion(Request $request, WorkflowDefinition $workflow)
    {
        $data = $request->validate(['change_summary'=>'nullable|string|max:5000']);
        $version = app(WorkflowService::class)->createVersion($workflow, $request->user(), $data['change_summary'] ?? null);
        return back()->with('success', "Draft version {$version->version} created.");
    }

    public function updateVersion(Request $request, WorkflowVersion $version)
    {
        abort_unless($version->status === 'DRAFT', 422, 'Only draft workflow versions can be edited.');
        $data = $request->validate([
            'stages' => ['required','array','min:1'],
            'stages.*.name' => ['required','string','max:150'],
            'stages.*.code' => ['required','string','max:100','alpha_dash'],
            'stages.*.required_permission' => ['nullable','string','max:150'],
            'stages.*.is_terminal' => ['nullable','boolean'],
        ]);

        DB::transaction(function () use ($version, $data) {
            $version->load('stages');
            $oldIds = $version->stages->pluck('id')->all();
            WorkflowTransition::where('workflow_version_id', $version->id)->delete();
            WorkflowStage::whereIn('id', $oldIds)->delete();
            foreach (array_values($data['stages']) as $index => $stage) {
                WorkflowStage::create([
                    'workflow_version_id' => $version->id, 'name' => $stage['name'], 'code' => $stage['code'],
                    'stage_order' => $index + 1, 'required_permission' => $stage['required_permission'] ?? null,
                    'is_terminal' => !empty($stage['is_terminal']),
                ]);
            }
        });
        return back()->with('success', 'Draft workflow version updated.');
    }

    public function publish(Request $request, WorkflowVersion $version)
    {
        abort_unless($version->status === 'DRAFT', 422, 'Only draft versions can be published.');
        $version->load(['definition','stages']);
        if ($version->stages->isEmpty() || ! $version->stages->where('is_terminal', true)->count()) {
            throw ValidationException::withMessages(['workflow' => 'A workflow must contain at least one stage and one terminal stage before publishing.']);
        }
        DB::transaction(function () use ($version) {
            $version->definition->versions()->where('status','PUBLISHED')->update(['status'=>'ARCHIVED']);
            $version->update(['status'=>'PUBLISHED','published_at'=>now()]);
        });
        return back()->with('success', "Workflow version {$version->version} published.");
    }
}
