<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ApplicationDocument extends Model
{
    protected $fillable = ['application_id','document_type_id','original_name','stored_name','disk','path','extension','mime_type','size_bytes','sha256','uploaded_by','uploaded_at'];
    protected function casts(): array { return ['uploaded_at' => 'datetime']; }
    public function application(): BelongsTo { return $this->belongsTo(Application::class); }
    public function documentType(): BelongsTo { return $this->belongsTo(DocumentType::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function isPreviewable(): bool { return str_starts_with($this->mime_type, 'image/') || $this->mime_type === 'application/pdf'; }
    public function url(): string { return route('student.applications.documents.preview', [$this->application_id, $this->id]); }
}
