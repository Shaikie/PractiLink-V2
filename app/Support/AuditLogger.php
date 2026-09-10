<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function record(string $action, ?Model $auditable = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $actor = Auth::guard('web')->user() ?: Auth::guard('students')->user();

        AuditLog::create([
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
