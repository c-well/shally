<?php
namespace App\Http\Middleware;

use App\Models\PeaceSubscriber;
use App\Services\Peace\SeekerAuth;
use Closure;
use Illuminate\Http\Request;

/**
 * Reads the seeker session cookie, loads the PeaceSubscriber, sets it on the
 * request as $request->attributes->set('seeker', $sub) so controllers can pull
 * it via request()->attributes->get('seeker').
 *
 * If $required (constructor arg passed via route middleware param), redirects
 * to /find-peace when no cookie or invalid. Otherwise just sets to null and
 * continues — lets the same page render for both anon + logged-in viewers.
 */
class SeekerAuthMiddleware
{
    public function __construct(private SeekerAuth $auth) {}

    public function handle(Request $request, Closure $next, string $required = 'optional')
    {
        $cookieVal = $request->cookie($this->auth->cookieName());
        $subscriberId = $this->auth->parseCookieValue($cookieVal);

        $sub = $subscriberId ? PeaceSubscriber::find($subscriberId) : null;
        if ($sub) {
            // Refresh last_seen (cheap update, don't bother if recent)
            if (!$sub->last_seen_at || $sub->last_seen_at->lt(now()->subMinutes(10))) {
                $sub->last_seen_at = now();
                $sub->saveQuietly();
            }
        }
        $request->attributes->set('seeker', $sub);

        if ($required === 'required' && !$sub) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'error' => 'auth required'], 401);
            }
            return redirect('/find-peace');
        }

        return $next($request);
    }
}
