<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = ['name', 'code', 'email', 'phone', 'address', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function placements(): HasMany { return $this->hasMany(Placement::class); }
}
