<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * NoCacheForAdmins — force fresh responses for authenticated admin/clerk users.
 *
 * Problem this solves: Andre published a bulletin from his iPhone, then his Safari
 * served him a cached older version, so he thought publish didn't work and kept
 * re-hitting it. Same risk for any admin editing → publishing → viewing.
 *
 * Strategy: when the responding user is logged in as admin or clerk, attach the
 * strongest possible "do not cache" headers. Guests get normal caching behavior.
 *
 * Why "no-store" not just "no-cache": iOS Safari's back-forward cache (bfcache)
 * ignores "no-cache" in many cases. Only "no-store" reliably prevents bfcache reuse.
 */
class NoCacheForAdmins
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if ($user && in_array($user->role ?? '', ['super_admin', 'clerk'], true)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            // Vary on cookie so any intermediary cache distinguishes admin from guest.
            $response->headers->set('Vary', 'Cookie');
        }

        return $response;
    }
}
