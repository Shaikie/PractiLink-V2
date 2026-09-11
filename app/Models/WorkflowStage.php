<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStage extends Model
{
    protected $fillable = ['workflow_version_id', 'name', 'code', 'stage_order', 'required_permission', 'is_terminal'];
    protected $casts = ['is_terminal' => 'boolean'];
    public function version(): BelongsTo { return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id'); }
}
