<?php
namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs a row to page_views for every successful (200/304) public GET request.
 * Skips: admin routes, API endpoints, asset files, bots, non-GET, redirects.
 * Privacy: stores SHA256-hashed IP only (so we can count uniques without retaining PII).
 * Adds a session-id cookie so we can count unique visitors without cross-site tracking.
 */
class TrackPageView
{
    private const SKIP_PREFIXES = ["/admin", "/api", "/auth", "/login", "/logout", "/profile", "/feedback"];
    private const SKIP_EXACT    = ["/sitemap.xml", "/robots.txt", "/favicon.ico"];
    private const BOT_AGENTS    = ["bot", "spider", "crawl", "slurp", "preview", "lighthouse", "headlesschrome", "feedfetcher", "facebookexternalhit"];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track on success (200/304) GET requests
        if ($request->method() !== "GET") return $response;
        if ($response->getStatusCode() >= 300) return $response;

        $path = $request->path();
        $pathSlash = "/" . ltrim($path, "/");

        // Skip admin/api/auth/etc.
        foreach (self::SKIP_PREFIXES as $p) {
            if (str_starts_with($pathSlash, $p)) return $response;
        }
        if (in_array($pathSlash, self::SKIP_EXACT, true)) return $response;
        // Skip asset extensions (CSS/JS/images served by Apache, but be defensive)
        if (preg_match("/\\.(css|js|jpg|jpeg|png|gif|webp|svg|ico|woff|woff2|ttf|map|json|xml)$/i", $pathSlash)) return $response;

        // Skip bots
        $ua = (string) $request->userAgent();
        $uaLower = strtolower($ua);
        foreach (self::BOT_AGENTS as $b) if (str_contains($uaLower, $b)) return $response;

        // Session cookie for unique-visitor counting (30-day persistence)
        $sid = $request->cookie("v_sid");
        if (!$sid) {
            $sid = bin2hex(random_bytes(16));
            // Attach to response so the cookie is set on this request
            $response->headers->setCookie(cookie(
                "v_sid", $sid,
                60 * 24 * 30,      // 30 days
                "/", null, true, true, false, "lax"
            ));
        }

        // Device + browser sniff (lightweight, no external lib)
        $device = match (true) {
            str_contains($uaLower, "mobile") || (str_contains($uaLower, "android") && !str_contains($uaLower, "tablet")) => "mobile",
            str_contains($uaLower, "ipad") || str_contains($uaLower, "tablet") => "tablet",
            default => "desktop",
        };
        $browser = match (true) {
            str_contains($uaLower, "edg/") || str_contains($uaLower, "edge") => "edge",
            str_contains($uaLower, "chrome") && !str_contains($uaLower, "edg/") => "chrome",
            str_contains($uaLower, "firefox") => "firefox",
            str_contains($uaLower, "safari") && !str_contains($uaLower, "chrome") => "safari",
            default => "other",
        };

        // Country from Cloudflare header if available
        $country = $request->header("CF-IPCountry") ?: null;
        if ($country === "XX" || $country === "T1") $country = null; // CF unknown / Tor

        try {
            PageView::create([
                "path"       => mb_substr($pathSlash, 0, 500),
                "referrer"   => mb_substr((string) $request->header("Referer", ""), 0, 500) ?: null,
                "user_agent" => mb_substr($ua, 0, 500) ?: null,
                "ip_hash"    => hash("sha256", $request->ip() . config("app.key")),
                "country"    => $country,
                "session_id" => $sid,
                "device"     => $device,
                "browser"    => $browser,
                "viewed_at"  => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let telemetry break the page — log + continue
            \Log::warning("PageView write failed: " . $e->getMessage());
        }

        return $response;
    }
}
