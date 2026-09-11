<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementStatusHistory extends Model
{
    protected $table = 'placement_status_history';
    protected $fillable = ['placement_id','from_status','to_status','changed_by','comment','changed_at'];
    protected function casts(): array { return ['changed_at' => 'datetime']; }
    public function placement(): BelongsTo { return $this->belongsTo(Placement::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
