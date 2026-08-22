<?php
namespace App\Http\Controllers;

use App\Models\InteractionEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Native event ingestion — POST /api/events
 *
 * Accepts a JSON batch of interaction events from the in-page tracker.
 * Rate-limited per session_id (50 events / 60s burst) to prevent abuse.
 *
 * Privacy guarantees:
 *   - IPs are hashed with the app key before storage (matches page_views)
 *   - Honors DNT: 1 header (silently accepts + drops, never bounces a request)
 *   - No CSRF token required (public endpoint, but bot-filtered + rate-limited)
 *   - Auth users (admins, clerks) are flagged is_bot=true so their clicks
 *     don't pollute visitor heatmaps
 */
class EventIngestController extends Controller
{
    private const MAX_EVENTS_PER_BURST = 50;
    private const ALLOWED_EVENT_TYPES  = [
        'click', 'hover', 'scroll', 'time_on_page',
        'qa_open', 'audio_play_30s', 'audio_play_complete',
        'pdf_download', 'subscribe', 'verse_tooltip_open',
        'live_cta_click', 'sermon_share', 'page_unload',
    ];

    public function ingest(Request $request): JsonResponse
    {
        // DNT respect — silently accept + drop
        if ($request->header('DNT') === '1') {
            return response()->json(['ok' => true, 'dropped' => 'dnt']);
        }

        $payload = $request->json('events');
        if (! is_array($payload) || count($payload) === 0) {
            return response()->json(['ok' => false, 'error' => 'no_events'], 400);
        }
        if (count($payload) > self::MAX_EVENTS_PER_BURST) {
            return response()->json(['ok' => false, 'error' => 'too_many'], 413);
        }

        $sessionId = $request->cookie('v_sid') ?: $request->json('session_id');
        if (! $sessionId || strlen($sessionId) < 16 || strlen($sessionId) > 64) {
            return response()->json(['ok' => false, 'error' => 'bad_session'], 400);
        }

        // Rate-limit per session: 200 events / 60s rolling window
        $rateKey = 'evt:' . $sessionId;
        $count = Cache::get($rateKey, 0);
        if ($count >= 200) {
            return response()->json(['ok' => false, 'error' => 'rate_limited'], 429);
        }
        Cache::put($rateKey, $count + count($payload), now()->addMinute());

        $ua       = (string) $request->userAgent();
        $isBot    = $this->detectBot($ua, $request);
        $ipHash   = hash_hmac('sha256', $request->ip() ?? '', config('app.key'));
        $country  = $this->resolveCountry($request);
        $device   = $this->detectDevice($ua);
        $now      = now();

        $rows = [];
        foreach ($payload as $ev) {
            $type = (string) ($ev['type'] ?? '');
            if (! in_array($type, self::ALLOWED_EVENT_TYPES, true)) continue;

            $rows[] = [
                'event_type'       => $type,
                'path'             => substr((string) ($ev['path'] ?? '/'), 0, 255),
                'session_id'       => $sessionId,
                'ip_hash'          => $ipHash,
                'country'          => $country,
                'device'           => $device,
                'viewport_w'       => $this->clamp($ev['vw'] ?? null, 0, 5000),
                'viewport_h'       => $this->clamp($ev['vh'] ?? null, 0, 5000),
                'x'                => $this->clamp($ev['x'] ?? null, 0, 5000),
                'y'                => $this->clamp($ev['y'] ?? null, 0, 50000),
                'scroll_pct'       => $this->clamp($ev['scroll_pct'] ?? null, 0, 100),
                'element_selector' => isset($ev['sel']) ? substr((string) $ev['sel'], 0, 255) : null,
                'element_text'     => isset($ev['txt']) ? substr((string) $ev['txt'], 0, 160) : null,
                'meta'             => isset($ev['meta']) ? json_encode($ev['meta']) : null,
                'is_bot'           => $isBot,
                // createFromTimestampMs() returns UTC no matter what app.timezone says,
                // so a client-sent ts landed 4-5 hours ahead while the $now fallback
                // wrote correct Eastern - the same table held both, and every
                // "last 30 minutes" figure counted rows from the future. The church
                // runs on Eastern; so does this column.
                'created_at'       => isset($ev['ts'])
                    ? \Carbon\Carbon::createFromTimestampMs((int) $ev['ts'])->setTimezone(config('app.timezone'))
                    : $now,
            ];
        }

        if (empty($rows)) {
            return response()->json(['ok' => true, 'accepted' => 0]);
        }

        try {
            // Bulk insert — single round trip
            DB::table('interaction_events')->insert($rows);
        } catch (\Throwable $e) {
            Log::warning('Event ingest failed', ['err' => $e->getMessage(), 'count' => count($rows)]);
            return response()->json(['ok' => false, 'error' => 'persist'], 500);
        }

        return response()->json(['ok' => true, 'accepted' => count($rows)]);
    }

    private function clamp($v, int $min, int $max): ?int
    {
        if ($v === null || $v === '') return null;
        $i = (int) $v;
        return max($min, min($max, $i));
    }

    private function detectBot(string $ua, Request $request): bool
    {
        // Authenticated users (admins, clerks) flagged so their clicks don't
        // pollute visitor data — they show in dashboards via is_bot=true filter
        if ($request->user()) return true;
        $u = strtolower($ua);
        foreach (['bot','spider','crawl','slurp','preview','lighthouse','headless','feedfetcher','facebookexternalhit','curl','python-requests'] as $b) {
            if (str_contains($u, $b)) return true;
        }
        return false;
    }

    private function detectDevice(string $ua): ?string
    {
        $u = strtolower($ua);
        if (str_contains($u, 'mobile')) return 'mobile';
        if (str_contains($u, 'tablet') || str_contains($u, 'ipad')) return 'tablet';
        if ($u === '') return null;
        return 'desktop';
    }

    private function resolveCountry(Request $request): ?string
    {
        // Cloudflare / cPanel headers if present
        return $request->header('CF-IPCountry') ?: $request->header('X-Country') ?: null;
    }
}
