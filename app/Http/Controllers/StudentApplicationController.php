<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationWindow;
use App\Services\WorkflowService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentApplicationController extends Controller
{
    public function index()
    {
        $student = Auth::guard('students')->user();
        return view('student.applications.index', [
            'applications' => $student->applications()->with(['applicationWindow.trainingType', 'documents.documentType', 'workflow.currentStage'])->latest()->get(),
            'windows' => ApplicationWindow::with('trainingType')->where('is_active', true)->where('opens_at', '<=', now())->where('closes_at', '>=', now())->orderBy('closes_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_window_id' => ['required', 'integer', 'exists:application_windows,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'training_start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'training_end_date' => ['required', 'date_format:Y-m-d', 'after:training_start_date'],
        ]);

        $student = Auth::guard('students')->user();
        $window = ApplicationWindow::findOrFail($validated['application_window_id']);
        abort_unless($window->isOpen(), 422, 'This application window is not currently open.');

        $application = Application::firstOrCreate(
            ['student_id' => $student->id, 'application_window_id' => $window->id],
            [
                'reference_number' => $this->referenceNumber(), 'notes' => $validated['notes'] ?? null,
                'training_start_date' => $validated['training_start_date'], 'training_end_date' => $validated['training_end_date'], 'status' => 'DRAFT',
            ]
        );

        if (! $application->wasRecentlyCreated && ! $application->isEditable()) {
            return back()->withErrors(['application_window_id' => 'You already have a non-editable application for this window.']);
        }

        if ($application->isEditable()) {
            $application->update(collect($validated)->only(['notes', 'training_start_date', 'training_end_date'])->all());
        }

        return redirect()->route('student.applications.show', $application);
    }

    public function show(Application $application)
    {
        $this->authorizeStudent($application);
        return view('student.applications.show', [
            'application' => $application->load(['applicationWindow.trainingType', 'documents.documentType', 'workflow.currentStage', 'workflow.version', 'workflow.history.toStage']),
            'documentTypes' => \App\Models\DocumentType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function submit(Application $application, WorkflowService $workflows)
    {
        $this->authorizeStudent($application);
        abort_unless($application->isEditable(), 422, 'This application cannot be submitted in its current state.');
        abort_unless($application->applicationWindow->isOpen(), 422, 'The application window is no longer open.');
        abort_unless($application->training_start_date && $application->training_end_date, 422, 'Training start and end dates are required.');
        abort_unless($application->training_start_date->isToday() || $application->training_start_date->isFuture(), 422, 'Training start date cannot be in the past.');
        abort_unless($application->training_end_date->isAfter($application->training_start_date), 422, 'Training end date must be after the start date.');

        $old = $application->only(['status', 'submitted_at', 'reviewed_at']);
        $application->update(['status' => 'SUBMITTED', 'submitted_at' => now(), 'reviewed_at' => null]);
        $workflows->startFor($application->fresh());
        AuditLogger::record('application.submitted', $application, $old, $application->fresh()->only(['status', 'submitted_at', 'reviewed_at']));
        return redirect()->route('student.applications.show', $application)->with('success', 'Application submitted successfully.');
    }

    public function cancel(Application $application)
    {
        $this->authorizeStudent($application);
        abort_unless(in_array($application->status, ['DRAFT', 'SUBMITTED', 'RETURNED'], true), 422, 'This application cannot be cancelled in its current state.');
        $old = ['status' => $application->status];
        $application->update(['status' => 'CANCELLED']);
        AuditLogger::record('application.cancelled', $application, $old, ['status' => 'CANCELLED']);
        return redirect()->route('student.applications.index')->with('success', 'Application cancelled.');
    }

    private function authorizeStudent(Application $application): void { abort_unless($application->student_id === Auth::guard('students')->id(), 403); }
    private function referenceNumber(): string
    {
        do { $reference = 'PL-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)); }
        while (Application::where('reference_number', $reference)->exists());
        return $reference;
    }
}
