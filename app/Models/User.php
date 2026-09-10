<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['fullname', 'email', 'username', 'phone', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class, 'user_roles'); }
    public function permissions(): BelongsToMany { return $this->belongsToMany(Permission::class, 'permission_user'); }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('slug', $permission)->exists()
            || $this->roles()->whereHas('permissions', fn ($query) => $query->where('slug', $permission))->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'locked_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
