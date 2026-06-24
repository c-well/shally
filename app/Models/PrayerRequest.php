<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrayerRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'body',
        'want_followup', 'keep_private',
        'ip', 'user_agent', 'read_at', 'spam_swept_at',
    ];

    protected $casts = [
        'want_followup' => 'boolean',
        'keep_private'  => 'boolean',
        'read_at'       => 'datetime',
        'spam_swept_at' => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    public function scopeUnread($q) { return $q->whereNull('read_at'); }
}
