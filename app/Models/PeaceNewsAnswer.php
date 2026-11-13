<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reader's answer to an issue's question.
 * unverified (email not confirmed yet) → pending (waiting for Andre/KC)
 * → approved (may show; quoted next week if share_ok) or rejected.
 */
class PeaceNewsAnswer extends Model
{
    protected $fillable = [
        'issue_id', 'subscriber_id', 'first_name', 'body', 'share_ok',
        'status', 'reviewed_by', 'reviewed_at', 'ip_hash',
    ];

    protected $casts = [
        'share_ok'    => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function issue(): BelongsTo      { return $this->belongsTo(PeaceNewsIssue::class, 'issue_id'); }
    public function subscriber(): BelongsTo { return $this->belongsTo(PeaceSubscriber::class, 'subscriber_id'); }
}
