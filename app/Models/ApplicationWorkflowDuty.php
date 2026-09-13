<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationWorkflowDuty extends Model
{
    protected $fillable = [
        'application_workflow_id',
        'workflow_stage_id',
        'workflow_stage_duty_id',
        'completed_by',
        'completed_at',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function workflow(): BelongsTo { return $this->belongsTo(ApplicationWorkflow::class, 'application_workflow_id'); }
    public function stage(): BelongsTo { return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id'); }
    public function duty(): BelongsTo { return $this->belongsTo(WorkflowStageDuty::class, 'workflow_stage_duty_id'); }
    public function completedBy(): BelongsTo { return $this->belongsTo(User::class, 'completed_by'); }
}
