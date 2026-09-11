<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\WorkflowService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
    public function index() { return view('admin.applications.index', ['applications'=>Application::with(['student','applicationWindow.trainingType','workflow.currentStage'])->whereIn('status',['SUBMITTED','UNDER_REVIEW','RETURNED','ACCEPTED','REJECTED'])->latest('submitted_at')->get()]); }
    public function show(Application $application)
    {
        $application->load(['student.institution','student.course','student.studyLevel','applicationWindow.trainingType','documents.documentType','workflow.currentStage','workflow.version','workflow.history.toStage']);
        $transitions = $application->workflow?->version?->transitions()->where('from_stage_id',$application->workflow->current_stage_id)->get() ?? collect();
        return view('admin.applications.show',['application'=>$application,'transitions'=>$transitions]);
    }

    public function action(Request $request, Application $application, WorkflowService $workflows)
    {
        $data = $request->validate(['action'=>['required','string','max:100','alpha_dash'],'comment'=>['nullable','string','max:5000']]);
        $workflow = $application->workflow ?? $workflows->startFor($application);
        $old = $application->only(['status','reviewed_at']);
        $result = $workflows->transition($workflow,$data['action'],$request->user(),$data['comment'] ?? null);
        $status = match (strtoupper($data['action'])) {
            'REJECT' => 'REJECTED',
            'ACCEPT' => 'ACCEPTED',
            default => $result->currentStage?->is_terminal ? 'ACCEPTED' : 'UNDER_REVIEW',
        };
        $application->update(['status'=>$status,'reviewed_at'=>in_array($status,['ACCEPTED','REJECTED'],true) ? now() : null]);
        AuditLogger::record('application.workflow_action',$application,$old,$application->fresh()->only(['status','reviewed_at']));
        $application->student->notify(new ApplicationStatusUpdated($application->fresh()));
        return back()->with('success','Workflow action completed successfully.');
    }

    public function forward(Request $request, Application $application, WorkflowService $workflows) { return $this->transition($request,$application,$workflows,'FORWARD','UNDER_REVIEW','Application forwarded to the next workflow stage.'); }
    public function returnApplication(Request $request, Application $application, WorkflowService $workflows) { $request->validate(['comment'=>['required','string','min:5','max:5000']]); return $this->transition($request,$application,$workflows,'RETURN','RETURNED','Application returned for correction.'); }
    public function reject(Request $request, Application $application, WorkflowService $workflows) { $request->validate(['comment'=>['required','string','min:5','max:5000']]); return $this->transition($request,$application,$workflows,'REJECT','REJECTED','Application rejected.'); }
    public function accept(Request $request, Application $application, WorkflowService $workflows) { return $this->transition($request,$application,$workflows,'ACCEPT','ACCEPTED','Application accepted.'); }

    private function transition(Request $request, Application $application, WorkflowService $workflows, string $action, string $status, string $message)
    {
        $workflow = $application->workflow ?? $workflows->startFor($application);
        $old = $application->only(['status','reviewed_at']);
        $result = $workflows->transition($workflow,$action,$request->user(),$request->input('comment'));
        $finalStatus = $result->currentStage?->is_terminal && $action === 'FORWARD' ? 'ACCEPTED' : $status;
        $application->update(['status'=>$finalStatus,'reviewed_at'=>in_array($finalStatus,['ACCEPTED','REJECTED'],true) ? now() : null]);
        AuditLogger::record('application.workflow_'.strtolower($action),$application,$old,$application->fresh()->only(['status','reviewed_at']));
        $application->student->notify(new ApplicationStatusUpdated($application->fresh()));
        return back()->with('success',$message);
    }
}
