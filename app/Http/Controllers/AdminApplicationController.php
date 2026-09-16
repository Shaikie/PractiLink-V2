<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\ApplicationLifecycleService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Application::with([
            'student',
            'department',
            'applicationWindow.trainingType',
            'workflow.currentStage',
        ])
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED'])
            ->visibleToStaff($user)
            ->latest('submitted_at');

        return view('admin.applications.index', ['applications' => $query->paginate(15)->withQueryString()]);
    }

    public function show(Request $request, Application $application, WorkflowService $workflows)
    {
        abort_unless(
            Application::query()->visibleToStaff($request->user())->whereKey($application)->exists(),
            403,
        );

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

        $transitions = collect();
        if ($workflow = $application->workflow) {
            $transitions = $workflow->version?->transitions()
                ->where('from_stage_id', $workflow->current_stage_id)
                ->where('action', '!=', 'COMPLETE_PLACEMENT')
                ->with(['fromStage', 'toStage', 'responsibleRole'])
                ->get()
                ->filter(fn ($transition) => $workflows->canAct($workflow, $transition, $request->user()))
                ->values() ?? collect();
        }

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

        if ($old !== $updated->only(['status', 'reviewed_at'])) {
            $updated->student->notify(new ApplicationStatusUpdated($updated));
        }

        return redirect()->route('admin.applications.index')
            ->with('success', 'Workflow action completed successfully.');
    }
}
