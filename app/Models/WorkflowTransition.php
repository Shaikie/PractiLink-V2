<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTransition extends Model
{
    protected $fillable = ['workflow_version_id', 'from_stage_id', 'to_stage_id', 'action', 'label', 'required_permission', 'requires_comment'];
    protected $casts = ['requires_comment' => 'boolean'];
    public function version(): BelongsTo { return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id'); }
    public function fromStage(): BelongsTo { return $this->belongsTo(WorkflowStage::class, 'from_stage_id'); }
    public function toStage(): BelongsTo { return $this->belongsTo(WorkflowStage::class, 'to_stage_id'); }
}
