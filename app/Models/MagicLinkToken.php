<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MagicLinkToken extends Model
{
    protected $fillable = ['user_id', 'token_hash', 'expires_at', 'ip_address', 'user_agent'];
    protected $casts = ['expires_at' => 'datetime', 'used_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** Generate a fresh token for this user. Returns [plainToken, MagicLinkToken]. */
    public static function issueFor(User $user, string $ip, ?string $ua, int $minutes = 30): array
    {
        $plain = Str::random(64);
        $token = static::create([
            'user_id'    => $user->id,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addMinutes($minutes),
            'ip_address' => $ip,
            'user_agent' => $ua ? mb_substr($ua, 0, 255) : null,
        ]);
        return [$plain, $token];
    }

    /** Look up by plain token. Returns null if not found, expired, or already used. */
    public static function findValid(string $plainToken): ?self
    {
        $hash = hash('sha256', $plainToken);
        $token = static::where('token_hash', $hash)->first();
        if (! $token || $token->used_at !== null || $token->expires_at->isPast()) {
            return null;
        }
        return $token;
    }

    public function consume(): void
    {
        $this->used_at = now();
        $this->save();
    }
}
