<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Notifications\ApplicationStatusUpdated;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class AdminApplicationController extends Controller
{
    public function index()
    {
        return view('admin.applications.index', [
            'applications' => Application::with(['student', 'applicationWindow.trainingType'])
                ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'RETURNED', 'ACCEPTED', 'REJECTED'])
                ->latest('submitted_at')
                ->get(),
        ]);
    }

    public function show(Application $application)
    {
        return view('admin.applications.show', [
            'application' => $application->load(['student.institution', 'student.course', 'student.studyLevel', 'applicationWindow.trainingType']),
        ]);
    }

    public function updateStatus(Request $request, Application $application)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:UNDER_REVIEW,RETURNED,ACCEPTED,REJECTED'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $old = $application->only(['status', 'notes', 'reviewed_at']);
        $application->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $application->notes,
            'reviewed_at' => in_array($validated['status'], ['ACCEPTED', 'REJECTED'], true) ? now() : null,
        ]);

        AuditLogger::record('application.status_updated', $application, $old, $application->fresh()->only(['status', 'notes', 'reviewed_at']));
        $application->student->notify(new ApplicationStatusUpdated($application->fresh()));

        return back()->with('success', 'Application status updated.');
    }
}
