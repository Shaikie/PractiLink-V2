<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'code'];

    public function applications(): HasMany { return $this->hasMany(Application::class); }
    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'department_user'); }
}
