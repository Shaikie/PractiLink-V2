<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use ZipArchive;

class ApplicationDocumentService
{
    public function store(Application $application, DocumentType $type, UploadedFile $file, ?int $userId = null): ApplicationDocument
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $allowedExtensions = $type->allowed_extensions === null
            ? ['pdf', 'jpg', 'jpeg', 'png', 'docx']
            : $type->allowed_extensions;
        $allowedMimeTypes = $type->allowed_mime_types === null
            ? ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            : $type->allowed_mime_types;

        if ($allowedExtensions === [] || $allowedMimeTypes === []) {
            throw ValidationException::withMessages([
                'document' => 'This document type does not have an active upload policy.',
            ]);
        }

        if (! in_array($extension, array_map('strtolower', $allowedExtensions), true) || ! in_array($mime, $allowedMimeTypes, true)) {
            throw ValidationException::withMessages([
                'document' => 'The uploaded file format does not match the allowed document format.',
            ]);
        }

        $this->verifySignature($file, $extension);

        $sizeKb = (int) ceil($file->getSize() / 1024);
        if ($sizeKb < $type->min_size_kb || $sizeKb > $type->max_size_kb) {
            throw ValidationException::withMessages([
                'document' => "The document must be between {$type->min_size_kb} KB and {$type->max_size_kb} KB.",
            ]);
        }

        $hash = hash_file('sha256', $file->getRealPath());
        $storedName = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs(
            'applications/'.$application->id.'/documents',
            $storedName,
            'local',
        );

        if (! is_string($path)) {
            throw ValidationException::withMessages([
                'document' => 'The document could not be stored. Please try again.',
            ]);
        }

        $oldPath = null;

        try {
            $record = DB::transaction(function () use ($application, $type, $file, $hash, $storedName, $path, $extension, $mime, $userId, &$oldPath): ApplicationDocument {
                $lockedApplication = Application::query()->lockForUpdate()->findOrFail($application->id);
                if (! $lockedApplication->isEditable()) {
                    throw ValidationException::withMessages([
                        'document' => 'Documents can only be changed while the application is editable.',
                    ]);
                }

                $existing = ApplicationDocument::query()
                    ->where('application_id', $application->id)
                    ->where('document_type_id', $type->id)
                    ->where('sha256', $hash)
                    ->first();

                if ($existing) {
                    Storage::disk('local')->delete($path);

                    return $existing;
                }

                $old = ApplicationDocument::query()
                    ->where('application_id', $application->id)
                    ->where('document_type_id', $type->id)
                    ->first();

                if ($old) {
                    $oldPath = [$old->disk, $old->path];
                    $old->delete();
                }

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
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        if ($oldPath !== null) {
            Storage::disk($oldPath[0])->delete($oldPath[1]);
        }

        return $record;
    }

    private function verifySignature(UploadedFile $file, string $extension): void
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $signature = $handle ? fread($handle, 16) : false;
        if (is_resource($handle)) {
            fclose($handle);
        }

        $valid = match ($extension) {
            'pdf' => is_string($signature) && str_starts_with($signature, '%PDF-'),
            'jpg', 'jpeg' => is_string($signature) && str_starts_with($signature, "\xFF\xD8\xFF"),
            'png' => is_string($signature) && str_starts_with($signature, "\x89PNG\x0D\x0A\x1A\x0A"),
            'docx' => $this->isValidDocx($file),
            default => false,
        };

        if (! $valid) {
            throw ValidationException::withMessages([
                'document' => 'The file content does not match its declared format.',
            ]);
        }
    }

    private function isValidDocx(UploadedFile $file): bool
    {
        if (! class_exists(ZipArchive::class)) {
            return false;
        }

        $archive = new ZipArchive;
        $opened = $archive->open($file->getRealPath()) === true;
        $valid = $opened
            && $archive->locateName('[Content_Types].xml') !== false
            && $archive->locateName('word/document.xml') !== false;

        if ($opened) {
            $archive->close();
        }

        return $valid;
    }
}
