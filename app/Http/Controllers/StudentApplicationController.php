<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationWindow;
use App\Models\Department;
use App\Models\DocumentType;
use App\Services\ApplicationLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentApplicationController extends Controller
{
    public function index()
    {
        $student = Auth::guard('students')->user();

        return view('student.applications.index', [
            'applications' => $student->applications()
                ->with(['applicationWindow.trainingType', 'department', 'documents.documentType', 'workflow.currentStage'])
                ->latest()
                ->get(),
        ]);
    }

    public function create()
    {
        return view('student.applications.create', [
            'windows' => ApplicationWindow::with('trainingType')
                ->where('is_active', true)
                ->where('opens_at', '<=', now())
                ->where('closes_at', '>=', now())
                ->orderBy('closes_at')
                ->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDraft($request);
        $student = Auth::guard('students')->user();
        $window = ApplicationWindow::whereKey($validated['application_window_id'])
            ->where('is_active', true)
            ->firstOrFail();

        abort_unless($window->isOpen(), 422, 'This application window is not currently open.');

        $application = DB::transaction(fn () => Application::firstOrCreate(
            ['student_id' => $student->id, 'application_window_id' => $window->id],
            ['reference_number' => $this->referenceNumber(), 'status' => 'DRAFT']
        ));

        if (!$application->wasRecentlyCreated && !$application->isEditable()) {
            return back()->withErrors([
                'application_window_id' => 'You already have a non-editable application for this window.',
            ]);
        }

        $application->update(collect($validated)->except('application_window_id')->all());

        return redirect()->route('student.applications.show', $application)
            ->with('success', 'Application draft saved.');
    }

    public function update(Request $request, Application $application)
    {
        $this->authorizeStudent($application);
        abort_unless($application->isEditable(), 422, 'This application cannot be edited in its current state.');

        $validated = $this->validateDraft($request, false);
        $application->update(collect($validated)->except('application_window_id')->all());

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
            ]),
            'documentTypes' => DocumentType::where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function submit(Application $application, ApplicationLifecycleService $lifecycle)
    {
        $this->authorizeStudent($application);
        abort_unless($application->isEditable(), 422, 'This application cannot be submitted in its current state.');
        abort_unless($application->applicationWindow->isOpen(), 422, 'The application window is no longer open.');
        abort_unless($application->department_id, 422, 'Please select the department that should review your application.');

        $requiredFields = [
            'reason_for_application',
            'interests',
            'expected_objectives',
            'current_study_year',
            'training_start_date',
            'training_end_date',
        ];

        foreach ($requiredFields as $field) {
            if (blank($application->{$field})) {
                throw ValidationException::withMessages([
                    $field => 'This field is required before submission.',
                ]);
            }
        }

        abort_unless(
            $application->current_study_year >= 1 && $application->current_study_year <= 20,
            422,
            'Current study year is invalid.'
        );
        abort_unless(
            $application->training_start_date->isToday() || $application->training_start_date->isFuture(),
            422,
            'Training start date cannot be in the past.'
        );
        abort_unless(
            $application->training_end_date->isAfter($application->training_start_date),
            422,
            'Training end date must be after the start date.'
        );

        $required = DocumentType::where('is_active', true)
            ->where('is_required', true)
            ->pluck('id');
        $uploaded = $application->documents()->pluck('document_type_id');
        $missing = $required->diff($uploaded);

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'documents' => 'Please upload all required documents before submitting your application.',
            ]);
        }

        $updated = $lifecycle->submit($application);

        return redirect()->route('student.applications.show', $updated)
            ->with('success', 'Application submitted successfully.');
    }

    public function cancel(Application $application, ApplicationLifecycleService $lifecycle)
    {
        $this->authorizeStudent($application);
        $updated = $lifecycle->cancel($application);

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
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'reason_for_application' => ['required', 'string', 'min:20', 'max:5000'],
            'interests' => ['required', 'string', 'min:10', 'max:5000'],
            'expected_objectives' => ['required', 'string', 'min:20', 'max:5000'],
            'current_study_year' => ['required', 'integer', 'min:1', 'max:20'],
            'training_start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'training_end_date' => ['required', 'date_format:Y-m-d', 'after:training_start_date'],
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
