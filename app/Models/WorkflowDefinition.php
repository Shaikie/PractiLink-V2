<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowDefinition extends Model
{
    protected $fillable = ['name', 'code', 'training_type_id', 'description', 'is_active'];

    public function trainingType(): BelongsTo { return $this->belongsTo(TrainingType::class); }
    public function versions(): HasMany { return $this->hasMany(WorkflowVersion::class); }
    public function publishedVersion(): ?WorkflowVersion { return $this->versions()->where('status', 'PUBLISHED')->latest('version')->first(); }
}
