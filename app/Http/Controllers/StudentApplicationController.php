<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationWindow;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StudentApplicationController extends Controller
{
    public function index()
    {
        $student = Auth::guard('students')->user();

        return view('student.applications.index', [
            'applications' => $student->applications()->with('applicationWindow.trainingType')->latest()->get(),
            'windows' => ApplicationWindow::with('trainingType')->where('is_active', true)->where('opens_at', '<=', now())->where('closes_at', '>=', now())->orderBy('closes_at')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_window_id' => ['required', 'integer', 'exists:application_windows,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $student = Auth::guard('students')->user();
        $window = ApplicationWindow::findOrFail($validated['application_window_id']);
        abort_unless($window->isOpen(), 422, 'This application window is not currently open.');

        $application = Application::firstOrCreate(
            ['student_id' => $student->id, 'application_window_id' => $window->id],
            ['reference_number' => $this->referenceNumber(), 'notes' => $validated['notes'] ?? null, 'status' => 'DRAFT']
        );

        if (! $application->wasRecentlyCreated && ! $application->isEditable()) {
            return back()->withErrors(['application_window_id' => 'You already have a non-editable application for this window.']);
        }

        if ($application->isEditable() && array_key_exists('notes', $validated)) {
            $application->update(['notes' => $validated['notes']]);
        }

        return redirect()->route('student.applications.show', $application);
    }

    public function show(Application $application)
    {
        $this->authorizeStudent($application);
        return view('student.applications.show', ['application' => $application->load('applicationWindow.trainingType')]);
    }

    public function submit(Application $application)
    {
        $this->authorizeStudent($application);
        abort_unless($application->isEditable(), 422, 'This application cannot be submitted in its current state.');
        abort_unless($application->applicationWindow->isOpen(), 422, 'The application window is no longer open.');

        $old = $application->only(['status', 'submitted_at', 'reviewed_at']);
        $application->update(['status' => 'SUBMITTED', 'submitted_at' => now(), 'reviewed_at' => null]);
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
