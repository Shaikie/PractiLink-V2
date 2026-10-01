<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\WorkflowStage;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\ApplicationLifecycleService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED'])],
            'stage' => ['nullable', 'string', 'max:100', Rule::exists('workflow_stages', 'code')],
        ]);
        $user = $request->user();

        $query = Application::query()
            ->with([
                'student',
                'department',
                'applicationWindow.trainingType',
                'workflow.currentStage',
            ])
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED'])
            ->visibleToStaff($user)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($query) use ($search): void {
                            $query->where('registration_number', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['stage'] ?? null, fn ($query, string $stage) => $query->whereHas(
                'workflow.currentStage',
                fn ($query) => $query->where('code', $stage),
            ))
            ->latest('submitted_at')
            ->latest('id');

        return view('admin.applications.index', [
            'applications' => $query->paginate(15)->withQueryString(),
            'filters' => $filters,
            'workflowStages' => WorkflowStage::query()
                ->select(['id', 'name', 'code'])
                ->orderBy('name')
                ->orderBy('id')
                ->get()
                ->unique('code')
                ->sortBy('name')
                ->values(),
        ]);
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
            'workflow.history.actor',
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
        abort_unless(
            Application::query()->visibleToStaff($request->user())->whereKey($application)->exists(),
            403,
        );

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
