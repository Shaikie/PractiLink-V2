<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowVersion extends Model
{
    protected $fillable = ['workflow_definition_id', 'version', 'status', 'change_summary', 'published_at', 'created_by'];
    protected function casts(): array { return ['published_at' => 'datetime']; }
    public function definition(): BelongsTo { return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id'); }
    public function stages(): HasMany { return $this->hasMany(WorkflowStage::class); }
    public function transitions(): HasMany { return $this->hasMany(WorkflowTransition::class); }
}
