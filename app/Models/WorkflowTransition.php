<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTransition extends Model
{
    protected $fillable = [
        'workflow_version_id',
        'from_stage_id',
        'to_stage_id',
        'action',
        'label',
        'result_status',
        'required_permission',
        'responsible_role_id',
        'requires_comment',
    ];

    protected function casts(): array
    {
        return ['requires_comment' => 'boolean'];
    }

    public function version(): BelongsTo { return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id'); }
    public function fromStage(): BelongsTo { return $this->belongsTo(WorkflowStage::class, 'from_stage_id'); }
    public function toStage(): BelongsTo { return $this->belongsTo(WorkflowStage::class, 'to_stage_id'); }
    public function responsibleRole(): BelongsTo { return $this->belongsTo(Role::class, 'responsible_role_id'); }
}
