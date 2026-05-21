<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * App-level IP blocklist for auth-form abuse.
 *
 * Checked at request entry on the auth POST routes. If the requester's IP is
 * in `blocked_ips` and the block hasn't expired, return 429 immediately.
 *
 * Inserts to this table happen in the auth controllers after a failed
 * attempt that crosses the threshold (default: > 20 failures in 24h).
 */
class BlockAbusiveIps
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $row = DB::table('blocked_ips')->where('ip_address', $ip)->first();
        if (!$row) return $next($request);

        if ($row->expires_at && now()->greaterThan($row->expires_at)) {
            DB::table('blocked_ips')->where('id', $row->id)->delete();
            return $next($request);
        }

        return response('Access temporarily blocked due to repeated failed attempts. Try again later.', 429)
            ->header('Retry-After', '3600');
    }
}
