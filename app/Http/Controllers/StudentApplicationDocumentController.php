<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentType;
use App\Services\ApplicationDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class StudentApplicationDocumentController extends Controller
{
    public function store(Request $request, Application $application, ApplicationDocumentService $documents)
    {
        abort_unless(Auth::guard('students')->check() && $application->student_id === Auth::guard('students')->id(), 403);
        abort_unless($application->isEditable(), 422, 'Documents can only be changed while the application is editable.');
        $type = DocumentType::whereKey($request->input('document_type_id'))->where('is_active', true)->firstOrFail();
        $extensions = $type->allowed_extensions ?: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $validated = $request->validate([
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'document' => ['required', File::types($extensions)->min(($type->min_size_kb ?: 1).'kb')->max(($type->max_size_kb ?: 5120).'kb'), 'extensions:'.implode(',', $extensions)],
        ]);
        $documents->store($application, $type, $validated['document'], null);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function show(Application $application, ApplicationDocument $document)
    {
        $this->authorizeView($application);
        abort_unless($document->application_id === $application->id, 404);

        return view('student.applications.documents.show', compact('application', 'document'));
    }

    public function preview(Application $application, ApplicationDocument $document)
    {
        $this->authorizeView($application);
        abort_unless($document->application_id === $application->id, 404);
        abort_unless($document->isPreviewable(), 415, 'This file type cannot be previewed in the browser.');
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->response($document->path, $document->original_name, [
            'Content-Disposition' => 'inline; filename="'.addslashes($document->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Application $application, ApplicationDocument $document)
    {
        $this->authorizeView($application);
        abort_unless($document->application_id === $application->id, 404);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }

    public function destroy(Application $application, ApplicationDocument $document)
    {
        abort_unless(Auth::guard('students')->check() && $application->student_id === Auth::guard('students')->id(), 403);
        abort_unless($document->application_id === $application->id, 404);
        abort_unless($application->isEditable(), 422, 'Documents cannot be removed after submission.');
        Storage::disk($document->disk)->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document removed.');
    }

    private function authorizeView(Application $application): void
    {
        if (Auth::guard('students')->check()) {
            abort_unless($application->student_id === Auth::guard('students')->id(), 403);

            return;
        }

        $user = Auth::guard('web')->user();
        abort_unless(
            $user
                && $user->hasPermission('applications.view')
                && Application::query()->visibleToStaff($user)->whereKey($application)->exists(),
            403,
        );
    }
}
