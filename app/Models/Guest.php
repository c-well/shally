<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'phone', 'email', 'birthday_month', 'birthday_day',
        'wants_updates', 'wants_volunteer', 'visited_on', 'notes', 'ip_hash'];
    protected $casts = ['visited_on' => 'date', 'wants_updates' => 'boolean', 'wants_volunteer' => 'boolean'];

    public function followups() { return $this->hasMany(GuestFollowup::class)->orderBy('due_on'); }

    public function firstName(): string
    {
        return \Illuminate\Support\Str::of($this->name)->trim()->explode(' ')->first() ?: 'friend';
    }
}
