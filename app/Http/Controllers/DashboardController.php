<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ApplicationWindow;
use App\Models\Placement;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::guard('web')->user();

        if ($user instanceof User) {
            return $this->staffDashboard($user);
        }

        $student = Auth::guard('students')->user();

        abort_unless($student instanceof Student, 403);

        return $this->studentDashboard($student);
    }

    private function staffDashboard(User $user): View
    {
        $roles = $user->roles()->with('permissions:id,slug')->get();
        $permissions = $roles->flatMap->permissions->pluck('slug')->unique()->values();
        $visibleApplications = fn (): Builder => Application::query()->visibleToStaff($user);

        return view('dashboard', [
            'accountType' => 'staff',
            'account' => $user,
            'roles' => $roles->pluck('name'),
            'permissions' => $permissions,
            'reviewQueueCount' => $visibleApplications()
                ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RETURNED'])
                ->count(),
            'openWindowCount' => ApplicationWindow::query()
                ->where('is_active', true)
                ->where('opens_at', '<=', now())
                ->where('closes_at', '>=', now())
                ->count(),
            'activePlacementCount' => $visibleApplications()
                ->whereHas('placement', fn (Builder $query) => $query->whereIn('status', ['ALLOCATED', 'ACTIVE']))
                ->count(),
            'recentApplications' => $visibleApplications()
                ->with(['student', 'applicationWindow.trainingType', 'workflow.currentStage'])
                ->latest('submitted_at')
                ->limit(5)
                ->get(),
        ]);
    }

    private function studentDashboard(Student $student): View
    {
        $applications = fn () => $student->applications();

        return view('dashboard', [
            'accountType' => 'student',
            'account' => $student,
            'roles' => collect(),
            'permissions' => collect(),
            'applicationCount' => $applications()->count(),
            'activeApplicationCount' => $applications()
                ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED'])
                ->count(),
            'documentCount' => ApplicationDocument::query()
                ->whereHas('application', fn (Builder $query) => $query->where('student_id', $student->id))
                ->count(),
            'placement' => Placement::query()
                ->where('student_id', $student->id)
                ->with('organization')
                ->latest()
                ->first(),
            'recentApplications' => $applications()
                ->with(['applicationWindow.trainingType', 'workflow.currentStage'])
                ->latest()
                ->limit(4)
                ->get(),
        ]);
    }
}
