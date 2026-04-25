<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DutyAssignment extends Model
{
    protected $fillable = ['department_id', 'week_date', 'member_id', 'role', 'text_value', 'sort_order'];
    protected $casts = ['week_date' => 'date'];
    public function department() { return $this->belongsTo(Department::class); }
    public function member() { return $this->belongsTo(DepartmentMember::class, 'member_id'); }
}
