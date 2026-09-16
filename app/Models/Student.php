<?php

namespace App\Models;

use App\Notifications\PasswordResetNotification;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Model implements Authenticatable, CanResetPasswordContract
{
    use AuthenticatableTrait, CanResetPassword, HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'registration_number', 'email', 'phone', 'gender',
        'nationality_id', 'institution_id', 'course_id', 'study_level_id',
        'password', 'is_active', 'locked_at', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'locked_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function nationality(): BelongsTo { return $this->belongsTo(Nationality::class); }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function studyLevel(): BelongsTo { return $this->belongsTo(StudyLevel::class); }
    public function applications(): HasMany { return $this->hasMany(Application::class); }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new PasswordResetNotification($token, 'student'));
    }
}
