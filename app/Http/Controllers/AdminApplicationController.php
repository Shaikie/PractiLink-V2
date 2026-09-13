<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationWorkflowDuty;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\ApplicationLifecycleService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roleIds = $user->roles()->pluck('roles.id');
        $query = Application::with(['student', 'department', 'applicationWindow.trainingType', 'workflow.currentStage', 'workflow.assignedUser'])
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED'])
            ->latest('submitted_at');

        if (!$user->roles()->where('slug', 'administrator')->exists()) {
            $query->where(function ($q) use ($roleIds, $user) {
                $q->whereHas('workflow', fn ($workflow) => $workflow->where('assigned_user_id', $user->id))
                    ->orWhereHas('workflow.currentStage', fn ($stage) => $stage->whereIn('responsible_role_id', $roleIds))
                    ->orWhereHas('workflow.currentStage.transitions', fn ($transition) => $transition->whereIn('responsible_role_id', $roleIds));
            });

            if ($user->roles()->where('slug', 'hod')->exists()) {
                $query->whereIn('department_id', $user->departments()->pluck('departments.id'));
            }
        }

        return view('admin.applications.index', ['applications' => $query->get()]);
    }

    public function show(Application $application)
    {
        $application->load([
            'student.institution', 'student.course', 'student.studyLevel', 'department',
            'applicationWindow.trainingType', 'documents.documentType',
            'workflow.currentStage.duties', 'workflow.currentStage.responsibleRole', 'workflow.assignedUser',
            'workflow.version', 'workflow.history.toStage', 'workflow.duties.duty',
        ]);

        $transitions = $application->workflow?->version?->transitions()
            ->where('from_stage_id', $application->workflow->current_stage_id)
            ->with(['fromStage', 'toStage', 'responsibleRole'])
            ->get() ?? collect();

        return view('admin.applications.show', ['application' => $application, 'transitions' => $transitions]);
    }

    public function completeDuty(Request $request, Application $application, ApplicationWorkflowDuty $duty, WorkflowService $workflows)
    {
        abort_unless($duty->workflow?->application_id === $application->id, 404);
        $workflows->completeDuty($duty, $request->user());
        return back()->with('success', 'Workflow duty marked complete.');
    }

    public function action(Request $request, Application $application, ApplicationLifecycleService $lifecycle)
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'max:100', 'alpha_dash'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $old = $application->only(['status', 'reviewed_at']);
        $updated = $lifecycle->act($application, $request->user(), $data['action'], $data['comment'] ?? null);
        if ($old !== $updated->only(['status', 'reviewed_at'])) {
            $updated->student->notify(new ApplicationStatusUpdated($updated));
        }

        return back()->with('success', 'Workflow action completed successfully.');
    }
}
