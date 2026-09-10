<?php

namespace App\Http\Controllers;

use App\Models\ApplicationWindow;
use App\Models\TrainingType;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class AdminApplicationWindowController extends Controller
{
    public function index()
    {
        return view('admin.application-windows.index', [
            'windows' => ApplicationWindow::with('trainingType')->latest('opens_at')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.application-windows.form', [
            'window' => new ApplicationWindow(),
            'trainingTypes' => TrainingType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateWindow($request);
        $window = ApplicationWindow::create($validated);
        AuditLogger::record('application_window.created', $window, null, $window->toArray());

        return redirect()->route('admin.application-windows.index')->with('success', 'Application window created.');
    }

    public function edit(ApplicationWindow $applicationWindow)
    {
        return view('admin.application-windows.form', [
            'window' => $applicationWindow,
            'trainingTypes' => TrainingType::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ApplicationWindow $applicationWindow)
    {
        $validated = $this->validateWindow($request);
        $old = $applicationWindow->getOriginal();
        $applicationWindow->update($validated);
        AuditLogger::record('application_window.updated', $applicationWindow, $old, $applicationWindow->fresh()->toArray());

        return redirect()->route('admin.application-windows.index')->with('success', 'Application window updated.');
    }

    public function destroy(ApplicationWindow $applicationWindow)
    {
        abort_if($applicationWindow->applications()->exists(), 422, 'This window cannot be deleted because it has applications.');
        $old = $applicationWindow->toArray();
        $applicationWindow->delete();
        AuditLogger::record('application_window.deleted', null, $old, null);

        return back()->with('success', 'Application window deleted.');
    }

    private function validateWindow(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'training_type_id' => ['required', 'exists:training_types,id'],
            'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after:opens_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        return $validated;
    }
}
