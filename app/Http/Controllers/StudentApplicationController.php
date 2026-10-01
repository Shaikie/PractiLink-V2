<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationWindow;
use App\Models\Department;
use App\Models\DocumentType;
use App\Services\ApplicationLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentApplicationController extends Controller
{
    public function index(): View
    {
        $student = Auth::guard('students')->user();

        return view('student.applications.index', [
            'applications' => $student->applications()
                ->with(['applicationWindow.trainingType', 'department', 'documents.documentType', 'workflow.currentStage'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('student.applications.create', [
            'windows' => ApplicationWindow::with('trainingType')
                ->where('is_active', true)
                ->where('opens_at', '<=', now())
                ->where('closes_at', '>=', now())
                ->whereDoesntHave('applications', fn ($query) => $query->where('student_id', Auth::guard('students')->id()))
                ->orderBy('closes_at')
                ->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function edit(Application $application): View
    {
        $this->authorizeStudent($application);
        abort_unless($application->isEditable(), 422, 'This application cannot be edited in its current state.');

        return view('student.applications.edit', [
            'application' => $application->load(['applicationWindow.trainingType', 'department']),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDraft($request);
        $student = Auth::guard('students')->user();
        $window = ApplicationWindow::whereKey($validated['application_window_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $application = DB::transaction(function () use ($student, $window, $validated): Application {
            $lockedWindow = ApplicationWindow::query()
                ->lockForUpdate()
                ->findOrFail($window->id);

            abort_unless($lockedWindow->isOpen(), 422, 'This application window is not currently open.');

            $application = Application::query()
                ->where('student_id', $student->id)
                ->where('application_window_id', $lockedWindow->id)
                ->lockForUpdate()
                ->first();

            if (! $application) {
                $application = Application::create([
                    'student_id' => $student->id,
                    'application_window_id' => $lockedWindow->id,
                    'reference_number' => $this->referenceNumber(),
                    'status' => 'DRAFT',
                ]);
            }

            abort_unless($application->isEditable(), 422, 'You already have a non-editable application for this window.');

            $application->update(collect($validated)->except('application_window_id')->all());

            return $application;
        });

        return redirect()->route('student.applications.show', $application)
            ->with('success', 'Application draft saved.');
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        $this->authorizeStudent($application);
        $validated = $this->validateDraft($request, false);

        $application = DB::transaction(function () use ($application, $validated): Application {
            $locked = Application::query()->lockForUpdate()->findOrFail($application->id);
            $this->authorizeStudent($locked);
            abort_unless($locked->isEditable(), 422, 'This application cannot be edited in its current state.');
            $locked->update(collect($validated)->except('application_window_id')->all());

            return $locked;
        });

        return back()->with('success', 'Application draft updated.');
    }

    public function show(Application $application)
    {
        $this->authorizeStudent($application);

        return view('student.applications.show', [
            'application' => $application->load([
                'applicationWindow.trainingType',
                'department',
                'documents.documentType',
                'workflow.currentStage',
                'workflow.version',
                'workflow.history.toStage',
                'workflow.history.actor',
                'placement.organization',
                'placement.letters',
            ]),
            'documentTypes' => DocumentType::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function submit(Application $application, ApplicationLifecycleService $lifecycle)
    {
        $this->authorizeStudent($application);
        $updated = $lifecycle->submit($application);

        return redirect()->route('student.applications.show', $updated)
            ->with('success', 'Application submitted successfully.');
    }

    public function cancel(Application $application, ApplicationLifecycleService $lifecycle)
    {
        $this->authorizeStudent($application);
        $lifecycle->cancel($application);

        return redirect()->route('student.applications.index')
            ->with('success', 'Application cancelled.');
    }

    private function validateDraft(Request $request, bool $requireWindow = true): array
    {
        return $request->validate([
            'application_window_id' => [
                $requireWindow ? 'required' : 'nullable',
                'integer',
                'exists:application_windows,id',
            ],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'reason_for_application' => ['nullable', 'string', 'min:20', 'max:5000'],
            'interests' => ['nullable', 'string', 'min:10', 'max:5000'],
            'expected_objectives' => ['nullable', 'string', 'min:20', 'max:5000'],
            'current_study_year' => ['nullable', 'integer', 'min:1', 'max:20'],
            'training_start_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'training_end_date' => ['nullable', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function authorizeStudent(Application $application): void
    {
        abort_unless($application->student_id === Auth::guard('students')->id(), 403);
    }

    private function referenceNumber(): string
    {
        do {
            $reference = 'PL-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Application::where('reference_number', $reference)->exists());

        return $reference;
    }
}
