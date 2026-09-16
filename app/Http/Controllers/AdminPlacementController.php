<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Placement;
use App\Models\PlacementStatusHistory;
use App\Models\User;
use App\Services\ApplicationLifecycleService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminPlacementController extends Controller
{
    public function index()
    {
        return view('admin.placements.index', [
            'placements' => Placement::with(['student', 'organization', 'department', 'application'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(Application $application)
    {
        $this->authorizeCto(request()->user());
        abort_unless($application->status === 'ACCEPTED', 422, 'Only approved applications can be placed.');
        abort_if($application->placement()->exists(), 422, 'This application already has a placement.');
        abort_unless($application->workflow?->currentStage?->code === 'CTO_PLACEMENT', 422, 'This application is not currently at the CTO placement stage.');

        return view('admin.placements.form', [
            'application' => $application->load('student', 'applicationWindow.trainingType', 'department'),
            'organizations' => Organization::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'supervisors' => $this->eligibleSupervisors()->get(),
        ]);
    }

    public function store(
        Request $request,
        Application $application,
        ApplicationLifecycleService $lifecycle,
    ) {
        $this->authorizeCto($request->user());
        abort_unless($application->status === 'ACCEPTED', 422, 'Only approved applications can be placed.');
        abort_if($application->placement()->exists(), 422, 'This application already has a placement.');
        abort_unless($application->workflow?->currentStage?->code === 'CTO_PLACEMENT', 422, 'This application is not currently at the CTO placement stage.');

        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'supervisor_user_id' => ['nullable', 'exists:users,id'],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after:start_date'],
            'location' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (($data['supervisor_user_id'] ?? null) && ! $this->eligibleSupervisors()->whereKey($data['supervisor_user_id'])->exists()) {
            throw ValidationException::withMessages([
                'supervisor_user_id' => 'The selected supervisor is not eligible for supervisor assignment.',
            ]);
        }

        abort_unless(
            Organization::whereKey($data['organization_id'])->where('is_active', true)->exists(),
            422,
            'The selected organization is not active.'
        );

        if (
            $data['start_date'] !== optional($application->training_start_date)->format('Y-m-d')
            || $data['end_date'] !== optional($application->training_end_date)->format('Y-m-d')
        ) {
            throw ValidationException::withMessages([
                'start_date' => 'Placement dates must match the approved application training dates.',
            ]);
        }

        DB::transaction(function () use ($data, $application, $request, $lifecycle): void {
            $locked = Application::query()
                ->whereKey($application->id)
                ->lockForUpdate()
                ->with('workflow.currentStage')
                ->firstOrFail();

            abort_unless(
                $locked->status === 'ACCEPTED' && ! $locked->placement()->exists(),
                422,
                'This application already has a placement or is no longer approved.'
            );
            abort_unless(
                $locked->workflow?->currentStage?->code === 'CTO_PLACEMENT',
                422,
                'This application is no longer at the CTO placement stage.'
            );

            $placement = Placement::create($data + [
                'application_id' => $locked->id,
                'student_id' => $locked->student_id,
                'reference_number' => $this->reference(),
                'status' => 'ALLOCATED',
            ]);

            PlacementStatusHistory::create([
                'placement_id' => $placement->id,
                'to_status' => 'ALLOCATED',
                'changed_by' => $request->user()->id,
                'changed_at' => now(),
            ]);

            AuditLogger::record('placement.created', $placement, null, $placement->toArray());
            $lifecycle->act($locked, $request->user(), ApplicationLifecycleService::ACTION_COMPLETE_PLACEMENT);
        });

        return redirect()->route('admin.placements.index')
            ->with('success', 'Placement allocated successfully and the application workflow is complete.');
    }

    public function updateStatus(Request $request, Placement $placement)
    {
        $this->authorizeCto($request->user());

        $data = $request->validate([
            'status' => ['required', 'in:ALLOCATED,ACTIVE,COMPLETED,CANCELLED'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $allowedTransitions = [
            'ALLOCATED' => ['ACTIVE', 'CANCELLED'],
            'ACTIVE' => ['COMPLETED', 'CANCELLED'],
            'COMPLETED' => [],
            'CANCELLED' => [],
        ];

        abort_unless(
            in_array($data['status'], $allowedTransitions[$placement->status] ?? [], true),
            422,
            'This placement status transition is not allowed.'
        );

        DB::transaction(function () use ($placement, $data, $request, $allowedTransitions): void {
            $placement->refresh();
            $old = $placement->status;
            abort_unless(
                in_array($data['status'], $allowedTransitions[$old] ?? [], true),
                422,
                'This placement status transition is no longer allowed.'
            );

            $placement->update(['status' => $data['status']]);
            PlacementStatusHistory::create([
                'placement_id' => $placement->id,
                'from_status' => $old,
                'to_status' => $placement->status,
                'changed_by' => $request->user()->id,
                'comment' => $data['comment'] ?? null,
                'changed_at' => now(),
            ]);
            AuditLogger::record(
                'placement.status_updated',
                $placement,
                ['status' => $old],
                ['status' => $placement->status],
            );
        });

        return back()->with('success', 'Placement status updated.');
    }

    private function authorizeCto(User $user): void
    {
        abort_unless($user->roles()->where('slug', 'cto')->exists(), 403, 'Only the CTO can manage final placement.');
    }

    private function eligibleSupervisors()
    {
        return User::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereHas('permissions', fn ($q) => $q->where('slug', 'applications.assign_supervisor'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('slug', 'applications.assign_supervisor'));
            })
            ->orderBy('fullname');
    }

    private function reference(): string
    {
        do {
            $reference = 'PLM-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Placement::where('reference_number', $reference)->exists());

        return $reference;
    }
}
