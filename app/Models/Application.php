<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'student_id',
        'application_window_id',
        'reference_number',
        'status',
        'notes',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function applicationWindow(): BelongsTo
    {
        return $this->belongsTo(ApplicationWindow::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['DRAFT', 'RETURNED'], true);
    }
}
