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

    /**
     * 'send' is the only one of these that carries a payload, and the only one
     * that is not a verb against a UID — a reply's UID is the message being
     * replied TO, which is what threads it.
     */
    public const ACTIONS = ['seen', 'unseen', 'flag', 'unflag', 'archive', 'trash', 'restore', 'fetch', 'send'];

    /** Actions a user can ask for directly from the room's action endpoint. */
    public const UI_ACTIONS = ['seen', 'unseen', 'flag', 'unflag', 'archive', 'trash', 'restore'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'applied_at' => 'datetime'];
    }

    public function scopePending($q)
    {
        return $q->whereNull('applied_at')->whereNull('error');
    }
}
