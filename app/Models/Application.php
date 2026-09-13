<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    protected $fillable = [
        'student_id', 'department_id', 'application_window_id', 'reference_number', 'status', 'notes',
        'reason_for_application', 'interests', 'expected_objectives', 'current_study_year',
        'training_start_date', 'training_end_date', 'submitted_at', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'training_start_date' => 'date', 'training_end_date' => 'date',
            'submitted_at' => 'datetime', 'reviewed_at' => 'datetime',
            'current_study_year' => 'integer',
        ];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function applicationWindow(): BelongsTo { return $this->belongsTo(ApplicationWindow::class); }
    public function documents(): HasMany { return $this->hasMany(ApplicationDocument::class); }
    public function workflow(): HasOne { return $this->hasOne(ApplicationWorkflow::class); }
    public function placement(): HasOne { return $this->hasOne(Placement::class); }

    public function isEditable(): bool { return in_array($this->status, ['DRAFT', 'RETURNED'], true); }
}
