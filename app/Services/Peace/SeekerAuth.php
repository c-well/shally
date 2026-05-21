<?php
namespace App\Services\Peace;

use App\Models\PeaceSubscriber;
use App\Models\PeaceSubscriberMagicLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Magic-link auth for /find-peace seekers.
 *
 * Flow:
 *   1. issueLink($email, $intent, $sourceUrl, Request)
 *      — creates the subscriber row (if missing) + magic-link record (token_hash)
 *      — returns the raw token (caller emails it inside the verify URL)
 *   2. consume($rawToken)
 *      — looks up by sha256, validates expiry + single-use
 *      — marks subscriber email_verified_at if not set
 *      — returns ['subscriber' => PeaceSubscriber, 'intent' => array|null, 'source_url' => string|null]
 *   3. cookieHelpers() to mint/parse the seeker session cookie
 */
class SeekerAuth
{
    private const COOKIE_NAME       = 'peace_seeker';
    private const COOKIE_DAYS       = 60;
    private const LINK_TTL_MINUTES  = 30;

    public function issueLink(string $email, ?array $intent, ?string $sourceUrl, Request $req): string
    {
        $email = mb_strtolower(trim($email));

        $sub = PeaceSubscriber::firstOrCreate(
            ['email' => $email],
            [
                'source'   => $intent['type'] ?? 'direct',
                'ip_hash'  => hash('sha256', $req->ip() . config('app.key')),
                'ua_hash'  => hash('sha256', (string) $req->userAgent() . config('app.key')),
            ]
        );

        $raw = bin2hex(random_bytes(32));   // 64 hex chars
        PeaceSubscriberMagicLink::create([
            'subscriber_id' => $sub->id,
            'token_hash'    => hash('sha256', $raw),
            'expires_at'    => now()->addMinutes(self::LINK_TTL_MINUTES),
            'intent'        => $intent,
            'source_url'    => $sourceUrl,
            'ip_hash'       => hash('sha256', $req->ip() . config('app.key')),
        ]);

        return $raw;
    }

    /** @return array{subscriber:PeaceSubscriber,intent:?array,source_url:?string}|null */
    public function consume(string $rawToken): ?array
    {
        $hash = hash('sha256', $rawToken);
        $link = PeaceSubscriberMagicLink::where('token_hash', $hash)->first();
        if (!$link || !$link->isUsable()) return null;

        $sub = $link->subscriber;
        if (!$sub) return null;

        $link->used_at = now();
        $link->save();

        if (!$sub->email_verified_at) {
            $sub->email_verified_at = now();
        }
        $sub->last_seen_at = now();
        $sub->save();

        return [
            'subscriber' => $sub,
            'intent'     => $link->intent,
            'source_url' => $link->source_url,
        ];
    }

    public function cookieName(): string { return self::COOKIE_NAME; }
    public function cookieDays(): int { return self::COOKIE_DAYS; }
    public function ttlMinutes(): int { return self::LINK_TTL_MINUTES; }

    /** Signed value: subscriberId|expiresAtUnix|hmac(appKey) — opaque to attacker, single-machine-verifiable. */
    public function mintCookieValue(int $subscriberId): string
    {
        $expires = time() + (self::COOKIE_DAYS * 86400);
        $body = $subscriberId . '|' . $expires;
        $sig  = hash_hmac('sha256', $body, config('app.key'));
        return $body . '|' . $sig;
    }

    public function parseCookieValue(?string $val): ?int
    {
        if (!$val) return null;
        $parts = explode('|', $val, 3);
        if (count($parts) !== 3) return null;
        [$sid, $exp, $sig] = $parts;
        if (!ctype_digit($sid) || !ctype_digit($exp)) return null;
        if ((int) $exp < time()) return null;
        $expected = hash_hmac('sha256', $sid . '|' . $exp, config('app.key'));
        if (!hash_equals($expected, $sig)) return null;
        return (int) $sid;
    }
}
