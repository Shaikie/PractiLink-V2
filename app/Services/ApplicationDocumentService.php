<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplicationDocumentService
{
    public function store(Application $application, DocumentType $type, UploadedFile $file, ?int $userId = null): ApplicationDocument
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $rules = $type->allowed_extensions ?: ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $mimes = $type->allowed_mime_types ?: [
            'application/pdf', 'image/jpeg', 'image/png', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (! in_array($extension, array_map('strtolower', $rules), true) || ! in_array($mime, $mimes, true)) {
            throw ValidationException::withMessages(['document' => 'The uploaded file format does not match the allowed document format.']);
        }

        $sizeKb = (int) ceil($file->getSize() / 1024);
        if ($sizeKb < $type->min_size_kb || $sizeKb > $type->max_size_kb) {
            throw ValidationException::withMessages(['document' => "The document must be between {$type->min_size_kb} KB and {$type->max_size_kb} KB."]);
        }

        $hash = hash_file('sha256', $file->getRealPath());
        $existing = ApplicationDocument::where('application_id', $application->id)->where('sha256', $hash)->first();
        if ($existing) return $existing;

        $old = ApplicationDocument::where('application_id', $application->id)->where('document_type_id', $type->id)->first();
        if ($old) {
            Storage::disk($old->disk)->delete($old->path);
            $old->delete();
        }

        $storedName = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs('applications/'.$application->id.'/documents', $storedName, 'local');

        return ApplicationDocument::create([
            'application_id' => $application->id,
            'document_type_id' => $type->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'disk' => 'local',
            'path' => $path,
            'extension' => $extension,
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
            'sha256' => $hash,
            'uploaded_by' => $userId,
            'uploaded_at' => now(),
        ]);
    }
}
