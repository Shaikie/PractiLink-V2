<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementLetter extends Model
{
    protected $fillable = ['placement_id','version','reference_number','issued_at','content','issued_by'];
    protected function casts(): array { return ['issued_at'=>'date']; }
    public function placement(): BelongsTo { return $this->belongsTo(Placement::class); }
    public function issuer(): BelongsTo { return $this->belongsTo(User::class,'issued_by'); }
}
