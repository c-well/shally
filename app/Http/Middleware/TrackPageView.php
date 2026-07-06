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
    private const BOT_AGENTS    = ["bot", "spider", "crawl", "slurp", "preview", "lighthouse", "headlesschrome", "feedfetcher", "facebookexternalhit",
        // 2026-07-05: 525 of Sabbath's 632 "uniques" were our own tooling — every curl
        // got a fresh session. HTTP clients are not congregants.
        "curl", "wget", "go-http", "python-urllib", "python-requests", "httpie", "libwww",
        "okhttp", "java/", "shalomaudit", "shalomsabbathaudit", "mozilla/5.0 audit", "mozilla/5.0 shalomaudit"];

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
        // Crawlers that spoof REAL browser strings give themselves away with museum-piece
        // versions (Chrome 73, Windows 7, iOS 13...). Found inflating /lesson 2026-07:
        // 29% of all recorded views. No real member runs these.
        if ($ua === '' ||
            preg_match('/Chrome\/[1-9][0-9]?[.]/', $ua) ||          // Chrome <= 99 (2022)
            str_contains($ua, 'Windows NT 6.') ||                    // Windows 7 / 8
            preg_match('/Mac OS X 10_([0-9]|1[01])_/', $ua) ||       // macOS <= 10.11
            preg_match('/iPhone OS (9|1[0-3])_/', $ua)) {            // iOS <= 13
            return $response;
        }

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

        $this->cronWatchdog();

        return $response;
    }

    /**
     * Cron died silently for 7 days once (Jun 25 - Jul 2, 2026) — backups and the
     * whole scheduler stopped with no signal. Web traffic is independent of cron,
     * so piggyback a cheap check here: if the scheduler heartbeat is over 60 min
     * stale, email the super-admins (at most once per 6h). Cache-gated to ~1 real
     * check per 5 minutes; wrapped so it can never break a page.
     */
    private function cronWatchdog(): void
    {
        try {
            \Illuminate\Support\Facades\Cache::remember('cron_watchdog_checked', 300, function () {
                $beat = \App\Models\AppSetting::get('cron_heartbeat_at');
                if (! $beat) return true;

                $age = (int) now()->diffInMinutes(\Carbon\Carbon::parse($beat));
                if ($age < 60) return true;

                $lastAlert = \App\Models\AppSetting::get('cron_stale_alerted_at');
                if ($lastAlert && now()->diffInHours(\Carbon\Carbon::parse($lastAlert)) < 6) return true;
                \App\Models\AppSetting::set('cron_stale_alerted_at', now()->toIso8601String());

                $admins = \App\Models\User::where('role', 'super_admin')->pluck('email')->all();
                if ($admins) {
                    $body = "The scheduler heartbeat is {$age} minutes old — cron has likely stopped on the Shalom account.\n\n"
                        . "Until it's revived there are NO database backups and NO scheduled jobs (flyer prune, sermon refresh, Peace pipeline, lesson rollover, spam sweep).\n\n"
                        . "Fix that worked last time: SSH in and re-register the crontab:\n"
                        . "    crontab -l | crontab -\n"
                        . "then confirm the heartbeat moves within 2 minutes.";
                    \Illuminate\Support\Facades\Mail::raw($body, function ($m) use ($admins) {
                        $m->to($admins)
                          ->cc('contact@c-wellpics.com')
                          ->subject('[Church of Peace] ALERT: cron appears to be down');
                    });
                }

                return true;
            });
        } catch (\Throwable $e) {
            // watchdog must never break a page
        }
    }
}
