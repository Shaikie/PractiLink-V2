<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\ApplicationLifecycleService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roleIds = $user->roles()->pluck('roles.id');

        $query = Application::with([
            'student',
            'department',
            'applicationWindow.trainingType',
            'workflow.currentStage',
        ])
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED'])
            ->latest('submitted_at');

        if (!$user->roles()->where('slug', 'administrator')->exists()) {
            $query->where(function ($q) use ($roleIds) {
                $q->whereHas('workflow.currentStage', fn ($stage) => $stage->whereIn('responsible_role_id', $roleIds))
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
            'student.institution',
            'student.course',
            'student.studyLevel',
            'department',
            'applicationWindow.trainingType',
            'documents.documentType',
            'workflow.currentStage',
            'workflow.version',
            'workflow.history.toStage',
        ]);

        $transitions = $application->workflow?->version?->transitions()
            ->where('from_stage_id', $application->workflow->current_stage_id)
            ->where('action', '!=', 'COMPLETE_PLACEMENT')
            ->with(['fromStage', 'toStage', 'responsibleRole'])
            ->get() ?? collect();

        return view('admin.applications.show', [
            'application' => $application,
            'transitions' => $transitions,
        ]);
    }

    public function action(
        Request $request,
        Application $application,
        ApplicationLifecycleService $lifecycle,
    ) {
        $data = $request->validate([
            'action' => ['required', 'string', 'max:100', 'alpha_dash'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $old = $application->only(['status', 'reviewed_at']);
        $updated = $lifecycle->act(
            $application,
            $request->user(),
            $data['action'],
            $data['comment'] ?? null,
        );

        // Lifecycle changes are audited inside the service. Keep the controller
        // responsible only for the user-facing notification and redirect.
        if ($old !== $updated->only(['status', 'reviewed_at'])) {
            $updated->student->notify(new ApplicationStatusUpdated($updated));
        }

        return back()->with('success', 'Workflow action completed successfully.');
    }
}
