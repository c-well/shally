<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DepartmentMember extends Model
{
    protected $fillable = ['department_id', 'name', 'phone', 'email', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];
    public function department() { return $this->belongsTo(Department::class); }
    public function assignments() { return $this->hasMany(DutyAssignment::class, 'member_id'); }
}
