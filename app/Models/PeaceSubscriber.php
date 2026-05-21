<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A seeker account — email-only, no password.
 * Created lazily by PeaceAuthController when someone first requests a magic link.
 */
class PeaceSubscriber extends Model
{
    protected $table = 'peace_subscribers';

    protected $fillable = [
        'email', 'email_verified_at', 'source',
        'subscribed_newsletter', 'ip_hash', 'ua_hash', 'last_seen_at',
    ];

    protected $casts = [
        'email_verified_at'     => 'datetime',
        'last_seen_at'          => 'datetime',
        'subscribed_newsletter' => 'boolean',
    ];

    public function magicLinks(): HasMany
    {
        return $this->hasMany(PeaceSubscriberMagicLink::class, 'subscriber_id');
    }

    public function savedQas(): BelongsToMany
    {
        return $this->belongsToMany(PeaceQaPair::class, 'peace_saved_qas', 'subscriber_id', 'qa_id')
                    ->withPivot('saved_at')
                    ->orderByPivot('saved_at', 'desc');
    }

    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }
}
