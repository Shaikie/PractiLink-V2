<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingType extends Model
{
    protected $fillable = ['name', 'code', 'description'];

    public function applicationWindows(): HasMany
    {
        return $this->hasMany(ApplicationWindow::class);
    }
}
