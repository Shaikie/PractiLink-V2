<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationWindow extends Model
{
    protected $fillable = ['name', 'training_type_id', 'opens_at', 'closes_at', 'is_active'];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function trainingType(): BelongsTo
    {
        return $this->belongsTo(TrainingType::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isOpen(): bool
    {
        return $this->is_active && now()->between($this->opens_at, $this->closes_at);
    }
}
