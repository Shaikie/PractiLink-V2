<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Placement extends Model
{
    protected $fillable=['application_id','student_id','organization_id','department_id','supervisor_user_id','reference_number','status','start_date','end_date','location','notes'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date']; }
    public function application(): BelongsTo { return $this->belongsTo(Application::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function supervisor(): BelongsTo { return $this->belongsTo(User::class,'supervisor_user_id'); }
    public function statusHistory(): HasMany { return $this->hasMany(PlacementStatusHistory::class); }
    public function letters(): HasMany { return $this->hasMany(PlacementLetter::class); }
}
