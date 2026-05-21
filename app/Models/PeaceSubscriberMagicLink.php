<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeaceSubscriberMagicLink extends Model
{
    protected $table = 'peace_subscriber_magic_links';

    protected $fillable = [
        'subscriber_id', 'token_hash', 'expires_at', 'used_at',
        'intent', 'source_url', 'ip_hash',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
        'intent'     => 'array',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(PeaceSubscriber::class, 'subscriber_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isUsable(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }
}
