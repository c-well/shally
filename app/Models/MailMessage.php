<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
        'seen' => 'boolean',
        'flagged' => 'boolean',
        'has_attachments' => 'boolean',
        'kind_confidence' => 'float',
    ];

    public function scopeBox($q, string $box)
    {
        return $q->where('mailbox', $box);
    }

    /** How the list shows the time: clock today, day this week, date beyond. */
    public function getWhenAttribute(): string
    {
        $t = $this->sent_at;
        if (! $t) {
            return '';
        }

        if ($t->isToday()) {
            return $t->format('g:i A');
        }
        if ($t->isYesterday()) {
            return 'Yesterday';
        }
        if ($t->greaterThan(now()->subDays(6))) {
            return $t->format('D');
        }

        return $t->format('M j');
    }
}
