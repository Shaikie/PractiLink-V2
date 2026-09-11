<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationWorkflow extends Model
{
    protected $fillable = ['application_id', 'workflow_version_id', 'current_stage_id', 'completed_at'];
    protected function casts(): array { return ['completed_at' => 'datetime']; }
    public function application(): BelongsTo { return $this->belongsTo(Application::class); }
    public function version(): BelongsTo { return $this->belongsTo(WorkflowVersion::class, 'workflow_version_id'); }
    public function currentStage(): BelongsTo { return $this->belongsTo(WorkflowStage::class, 'current_stage_id'); }
    public function history(): HasMany { return $this->hasMany(ApplicationWorkflowHistory::class); }
}
