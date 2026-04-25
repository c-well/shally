<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'start_at', 'end_at', 'location', 'notes', 'department_id', 'created_by', 'is_public', 'flyer_path'];
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime', 'is_public' => 'boolean'];

    public function department() { return $this->belongsTo(Department::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
