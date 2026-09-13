<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStage extends Model
{
    protected $fillable = [
        'workflow_version_id',
        'name',
        'code',
        'stage_order',
        'required_permission',
        'responsible_role_id',
        'is_terminal',
        'is_starting',
    ];

    protected $casts = [
        'is_terminal' => 'boolean',
        'is_starting' => 'boolean',
    ];

    public function version(): BelongsTo { return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id'); }
    public function responsibleRole(): BelongsTo { return $this->belongsTo(Role::class, 'responsible_role_id'); }
    public function transitions(): HasMany { return $this->hasMany(WorkflowTransition::class, 'from_stage_id'); }
}
