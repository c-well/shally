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

        return $response;
    }
}
