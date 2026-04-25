<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'color_hex', 'description', 'lead_user_id'];

    public function lead() { return $this->belongsTo(User::class, 'lead_user_id'); }
    public function events() { return $this->hasMany(Event::class); }
    public function members() { return $this->hasMany(DepartmentMember::class)->orderBy('sort_order')->orderBy('name'); }
    public function activeMembers() { return $this->members()->where('is_active', true); }
    public function assignments() { return $this->hasMany(DutyAssignment::class); }
}
