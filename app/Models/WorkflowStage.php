<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStage extends Model
{
    protected $fillable = [
        'workflow_version_id', 'name', 'code', 'stage_order', 'required_permission',
        'responsible_role_id', 'assigned_user_id', 'assignment_mode',
        'is_terminal', 'is_final', 'requires_placement', 'is_starting',
    ];

    protected $casts = [
        'is_terminal' => 'boolean',
        'is_final' => 'boolean',
        'requires_placement' => 'boolean',
        'is_starting' => 'boolean',
    ];

    public function version(): BelongsTo { return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id'); }
    public function responsibleRole(): BelongsTo { return $this->belongsTo(Role::class, 'responsible_role_id'); }
    public function assignedUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_user_id'); }
    public function transitions(): HasMany { return $this->hasMany(WorkflowTransition::class, 'from_stage_id'); }
    public function duties(): HasMany { return $this->hasMany(WorkflowStageDuty::class, 'workflow_stage_id')->orderBy('duty_order'); }
}
