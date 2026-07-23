<?php
namespace App\Http\Middleware;

use App\Models\IntercessorSession;
use Closure;
use Illuminate\Http\Request;

/**
 * Gate for /intercessors/* pages. The auth cookie is opaque (32-byte hex);
 * we store only its sha256 in the DB. IP is remembered for UX ("welcome back
 * on your usual network") but NEVER checked — Karlon: "wifi changes keep them
 * logged in anyway, it's only text they are reading."
 */
class EnsureIntercessor
{
    public const COOKIE = 'intercessor_token';

    public function handle(Request $request, Closure $next)
    {
        $raw = $request->cookie(self::COOKIE);
        if (! $raw) return redirect()->route('intercessors.signIn');

        $session = IntercessorSession::where('token_hash', IntercessorSession::hashToken($raw))
            ->where('expires_at', '>', now())
            ->with('intercessor')
            ->first();

        if (! $session || ! $session->intercessor || ! $session->intercessor->active) {
            return redirect()->route('intercessors.signIn')->withCookie(cookie()->forget(self::COOKIE));
        }

        // Sliding window: touch last-seen, and if within 30 days of expiry, extend another 6 months.
        $session->last_seen_at = now();
        $session->last_ip      = $request->ip();
        if ($session->expires_at->lt(now()->addDays(30))) {
            $session->expires_at = now()->addMonths(6);
        }
        $session->save();

        $session->intercessor->update(['last_seen_at' => now(), 'last_ip' => $request->ip()]);

        $request->attributes->set('intercessor', $session->intercessor);
        return $next($request);
    }
}
