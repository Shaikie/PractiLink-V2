<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_type', 'actor_id', 'action', 'auditable_type', 'auditable_id', 'old_values', 'new_values',
    ];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    public function actor(): MorphTo { return $this->morphTo(); }
    public function auditable(): MorphTo { return $this->morphTo(); }
}
