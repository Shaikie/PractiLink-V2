<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\WorkflowService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminApplicationController extends Controller
{
    public function index() { return view('admin.applications.index', ['applications'=>Application::with(['student','applicationWindow.trainingType','workflow.currentStage'])->whereIn('status',['SUBMITTED','UNDER_REVIEW','RETURNED','ACCEPTED','REJECTED'])->latest('submitted_at')->get()]); }
    public function show(Application $application) { return view('admin.applications.show',['application'=>$application->load(['student.institution','student.course','student.studyLevel','applicationWindow.trainingType','documents.documentType','workflow.currentStage','workflow.version','workflow.history.toStage'])]); }

    public function forward(Request $request, Application $application, WorkflowService $workflows)
    {
        $workflow=$application->workflow ?? $workflows->startFor($application);
        $old=$application->only(['status','reviewed_at']);
        $result=$workflows->transition($workflow,'FORWARD',$request->user(),$request->input('comment'));
        $status=$result->currentStage?->is_terminal ? 'ACCEPTED' : 'UNDER_REVIEW';
        $application->update(['status'=>$status,'reviewed_at'=>$status==='ACCEPTED'?now():null]);
        AuditLogger::record('application.workflow_forwarded',$application,$old,$application->fresh()->only(['status','reviewed_at']));
        $application->student->notify(new ApplicationStatusUpdated($application->fresh()));
        return back()->with('success','Application forwarded to the next workflow stage.');
    }

    public function return(Request $request, Application $application, WorkflowService $workflows)
    {
        $request->validate(['comment'=>['required','string','max:5000']]);
        $workflow=$application->workflow ?? $workflows->startFor($application);
        $result=$workflows->transition($workflow,'RETURN',$request->user(),$request->input('comment'));
        $application->update(['status'=>'RETURNED','reviewed_at'=>null,'notes'=>$request->input('comment')]);
        AuditLogger::record('application.workflow_returned',$application,null,$application->only(['status','notes']));
        $application->student->notify(new ApplicationStatusUpdated($application->fresh()));
        return back()->with('success','Application returned to the previous workflow stage.');
    }

    public function reject(Request $request, Application $application)
    {
        $data=$request->validate(['comment'=>['required','string','max:5000']]);
        $old=$application->only(['status','reviewed_at','notes']);
        $application->update(['status'=>'REJECTED','reviewed_at'=>now(),'notes'=>$data['comment']]);
        AuditLogger::record('application.rejected',$application,$old,$application->fresh()->only(['status','reviewed_at','notes']));
        $application->student->notify(new ApplicationStatusUpdated($application->fresh()));
        return back()->with('success','Application rejected.');
    }

    public function updateStatus(Request $request, Application $application)
    {
        return match($request->input('status')) {
            'UNDER_REVIEW' => $this->forward($request,$application,app(WorkflowService::class)),
            'RETURNED' => $this->return($request,$application,app(WorkflowService::class)),
            'REJECTED' => $this->reject($request,$application),
            default => back()->withErrors(['status'=>'Use the workflow actions to change application status.']),
        };
    }
}
