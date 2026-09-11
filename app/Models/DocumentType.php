<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    protected $fillable = ['name','code','is_required','allowed_extensions','allowed_mime_types','max_size_kb','min_size_kb','is_active','description'];
    protected function casts(): array { return ['is_required'=>'boolean','is_active'=>'boolean','allowed_extensions'=>'array','allowed_mime_types'=>'array']; }
    public function documents(): HasMany { return $this->hasMany(ApplicationDocument::class); }
}
