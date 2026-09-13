<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStageDuty extends Model
{
    protected $fillable = [
        'workflow_stage_id',
        'name',
        'code',
        'description',
        'duty_order',
        'is_required',
    ];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }
}
