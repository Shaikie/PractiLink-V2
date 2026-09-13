<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\WorkflowService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
    public function index()
    {
        return view('admin.applications.index', [
            'applications' => Application::with(['student', 'applicationWindow.trainingType', 'workflow.currentStage'])
                ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED'])
                ->latest('submitted_at')
                ->get(),
        ]);
    }

    public function show(Application $application)
    {
        $application->load([
            'student.institution',
            'student.course',
            'student.studyLevel',
            'applicationWindow.trainingType',
            'documents.documentType',
            'workflow.currentStage',
            'workflow.version',
            'workflow.history.toStage',
        ]);

        $transitions = $application->workflow?->version?->transitions()
            ->where('from_stage_id', $application->workflow->current_stage_id)
            ->with(['toStage', 'responsibleRole'])
            ->get() ?? collect();

        return view('admin.applications.show', [
            'application' => $application,
            'transitions' => $transitions,
        ]);
    }

    public function action(Request $request, Application $application, WorkflowService $workflows)
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'max:100', 'alpha_dash'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $workflow = $application->workflow ?? $workflows->startFor($application);
        $old = $application->only(['status', 'reviewed_at']);

        $workflows->transition(
            $workflow,
            $data['action'],
            $request->user(),
            $data['comment'] ?? null,
        );

        $application->refresh();
        AuditLogger::record(
            'application.workflow_action',
            $application,
            $old,
            $application->only(['status', 'reviewed_at']),
        );

        $application->student->notify(new ApplicationStatusUpdated($application));

        return back()->with('success', 'Workflow action completed successfully.');
    }
}
