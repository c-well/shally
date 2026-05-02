<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'message', 'ip', 'user_agent', 'read_at'];
    protected $casts = ['read_at' => 'datetime'];
    public function scopeUnread($q) { return $q->whereNull('read_at'); }
}
