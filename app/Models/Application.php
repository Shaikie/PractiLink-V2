<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applicationWindow(): BelongsTo
    {
        return $this->belongsTo(ApplicationWindow::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function workflow(): HasOne
    {
        return $this->hasOne(ApplicationWorkflow::class);
    }

    public function placement(): HasOne
    {
        return $this->hasOne(Placement::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['DRAFT', 'RETURNED'], true);
    }

    /**
     * Limit staff to the applications available to their workflow role.
     * Administrators may view every application.
     */
    public function scopeVisibleToStaff(Builder $query, User $user): void
    {
        if ($user->roles()->where('slug', 'administrator')->exists()) {
            return;
        }

        $roleIds = $user->roles()->pluck('roles.id');

        $query->where(function (Builder $query) use ($roleIds): void {
            $query->whereHas('workflow.currentStage', fn (Builder $stage) => $stage->whereIn('responsible_role_id', $roleIds))
                ->orWhereHas('workflow.currentStage.transitions', fn (Builder $transition) => $transition->whereIn('responsible_role_id', $roleIds));
        });

        if ($user->roles()->where('slug', 'hod')->exists()) {
            $query->whereIn('department_id', $user->departments()->select('departments.id'));
        }
    }
}
