<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bot trap. Forms include a hidden <input name="website"> that real users never see.
 * Bots auto-fill every visible+hidden text field — when this field is filled in,
 * we reject silently with a 200 OK so the bot thinks it succeeded and moves on.
 *
 * Apply via route middleware:
 *   ->middleware('honeypot')
 *
 * Counterpart Blade markup (paste right after @csrf in any protected form):
 *   <div style="position:absolute;left:-9999px;opacity:0;pointer-events:none" aria-hidden="true">
 *     <label for="website">Website (leave blank)</label>
 *     <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
 *   </div>
 */
class Honeypot
{
    public function handle(Request $request, Closure $next): Response
    {
        // 'website' is an AUTOFILL TOKEN — browsers filled it from contact cards and
        // three REAL PRAYERS were eaten (June 27/28/30, found 2026-07-06). The live trap
        // is now the meaningless 'form_meta_field'; 'website' stays checked because
        // blind bots still spray it, but it no longer exists in our markup.
        if (filled($request->input('form_meta_field')) || filled($request->input('website'))) {
            // Log the catch with IP for spotting attack patterns later, but don't 4xx.
            // 4xx tells the bot to retry; 200 OK convinces them they succeeded.
            \Log::info('honeypot triggered', [
                'ip'   => $request->ip(),
                'ua'   => substr($request->userAgent() ?? '', 0, 200),
                'path' => $request->path(),
            ]);
            // Look like success — never a raw OK page (a human hit this once).
            return back()->with('sent', true);
        }
        return $next($request);
    }
}
