<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'message', 'ip', 'user_agent', 'read_at', 'spam_swept_at'];
    protected $casts = ['read_at' => 'datetime', 'spam_swept_at' => 'datetime', 'deleted_at' => 'datetime'];
    public function scopeUnread($q) { return $q->whereNull('read_at'); }
}
