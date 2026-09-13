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
