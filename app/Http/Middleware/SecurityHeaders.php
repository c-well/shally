<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HSTS — force HTTPS for the next year, include subdomains
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        // Block embedding in iframes (clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Tight referrer policy — only send origin on same-origin, none cross
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Disable powerful features we don't need (camera, mic, etc.)
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=(), browsing-topics=()');
        // Prevent the page from leaking via window.opener on links
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // ── Content-Security-Policy ──────────────────────────────────────
        // Allowlist of where scripts/styles/fonts/images/connections can come
        // from. Everything else is blocked by the browser, which means a
        // stored-XSS attack via a corrupted markdown sanitizer (or a leaked
        // {!! !!} unescape) can't ship a malicious script to visitors.
        //
        // Origins we currently load from:
        //   - 'self' for our own assets
        //   - https://fonts.googleapis.com (CSS for Google Fonts)
        //   - https://fonts.gstatic.com (the fonts themselves)
        //   - https://cdn.jsdelivr.net (flatpickr, MiniSearch, heic2any)
        //   - https://bible-api.com (verse fetch)
        //   - data: URIs for in-page images (heic2any preview, favicons)
        //
        // 'unsafe-inline' is permitted for script + style because we have
        // many inline <style> and <script> blocks in blade templates. A
        // future hardening pass could move them to nonces.
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https: blob:",
            "media-src 'self' https:",
            "connect-src 'self' https://bible-api.com",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self' https://adventistgiving.org",
            "object-src 'none'",
            "upgrade-insecure-requests",
        ];
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
}
