<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationWorkflowHistory extends Model
{
    protected $table = 'application_workflow_history';
    protected $fillable = ['application_workflow_id', 'from_stage_id', 'to_stage_id', 'transition_id', 'acted_by', 'comment', 'acted_at'];
    protected function casts(): array { return ['acted_at' => 'datetime']; }
    public function workflow(): BelongsTo { return $this->belongsTo(ApplicationWorkflow::class, 'application_workflow_id'); }
    public function fromStage(): BelongsTo { return $this->belongsTo(WorkflowStage::class, 'from_stage_id'); }
    public function toStage(): BelongsTo { return $this->belongsTo(WorkflowStage::class, 'to_stage_id'); }
    public function transition(): BelongsTo { return $this->belongsTo(WorkflowTransition::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'acted_by'); }
}
