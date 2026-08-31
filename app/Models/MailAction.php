<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An intent, not a result. The room writes one of these and updates its own
 * view straight away; the scheduler applies it to Dovecot a minute later and
 * stamps applied_at. If it fails, error holds why and the room can say so.
 */
class MailAction extends Model
{
    protected $guarded = [];

    protected $casts = ['applied_at' => 'datetime'];

    public const ACTIONS = ['seen', 'unseen', 'flag', 'unflag', 'archive', 'trash', 'restore', 'fetch'];

    public function scopePending($q)
    {
        return $q->whereNull('applied_at')->whereNull('error');
    }
}
