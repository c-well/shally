<?php
namespace App\Http\Controllers;

use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * /admin/analytics — first-party page-view + interaction analytics.
 *
 * Reads from page_views (App\Http\Middleware\TrackPageView writes pageviews)
 * and interaction_events (in-page JS tracker writes clicks/scrolls/hovers
 * + named events).
 *
 * No external trackers. All data lives in our DB. Privacy-first by design:
 * IPs are hashed with the app key, never stored raw. DNT respected.
 *
 * 2026-05-30: extended with click leaderboard, heatmap, scroll-depth,
 * named-event counters, session-trail viewer.
 */
class AdminAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $days = (int) $request->query('days', 30);
        $days = max(1, min(365, $days));
        $since = now()->subDays($days);

        // ============================================================
        // ORIGINAL PAGEVIEW ANALYTICS (unchanged behavior)
        // ============================================================
        $topPaths = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->select('path')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('COUNT(DISTINCT session_id) as uniques')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        $daily = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->selectRaw('DATE(viewed_at) as d')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('COUNT(DISTINCT session_id) as uniques')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $byDate = $daily->keyBy('d');
        $filled = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $row = $byDate->get($d);
            $filled[] = (object) [
                'd' => $d,
                'views'   => $row?->views ?? 0,
                'uniques' => $row?->uniques ?? 0,
            ];
        }

        $ownHost = parse_url(config('app.url'), PHP_URL_HOST);
        $topReferrers = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->whereNotNull('referrer')->where('referrer', '!=', '')
            ->where('referrer', 'NOT LIKE', '%' . $ownHost . '%')
            ->selectRaw('referrer, COUNT(*) as views')
            ->groupBy('referrer')->orderByDesc('views')->limit(10)->get();

        $devices = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->whereNotNull('device')
            ->selectRaw('device, COUNT(*) as views, COUNT(DISTINCT session_id) as uniques')
            ->groupBy('device')->orderByDesc('views')->get();

        $browsers = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->whereNotNull('browser')
            ->selectRaw('browser, COUNT(*) as views')
            ->groupBy('browser')->orderByDesc('views')->get();

        $countries = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as views')
            ->groupBy('country')->orderByDesc('views')->limit(15)->get();

        $totals = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->selectRaw('COUNT(*) as views, COUNT(DISTINCT session_id) as uniques, COUNT(DISTINCT path) as pages')
            ->first();

        // ============================================================
        // NEW — INTERACTION ANALYTICS (2026-05-30)
        // All queries filter out is_bot=1 so visitor signal is clean.
        // ============================================================
        $clickBase = DB::table('interaction_events')
            ->where('created_at', '>=', $since)
            ->where('is_bot', false);

        // Click leaderboard — top clicked elements across the site
        $topClicks = (clone $clickBase)
            ->where('event_type', 'click')
            ->selectRaw('path, element_selector, element_text, COUNT(*) as clicks, COUNT(DISTINCT session_id) as uniques')
            ->whereNotNull('element_selector')
            ->groupBy('path', 'element_selector', 'element_text')
            ->orderByDesc('clicks')
            ->limit(25)
            ->get();

        // Pages with click data (so the heatmap viewer dropdown is populated)
        $heatmapPages = (clone $clickBase)
            ->where('event_type', 'click')
            ->select('path')
            ->selectRaw('COUNT(*) as clicks')
            ->groupBy('path')
            ->orderByDesc('clicks')
            ->limit(20)
            ->get();

        // Heatmap data for a specific path (default = top page)
        $heatmapPath = $request->query('heatmap_path') ?: ($heatmapPages->first()->path ?? '/');
        $heatmapPoints = DB::table('interaction_events')
            ->where('created_at', '>=', $since)
            ->where('is_bot', false)
            ->where('path', $heatmapPath)
            ->whereIn('event_type', ['click', 'hover'])
            ->select('event_type', 'x', 'y', 'viewport_w', 'viewport_h')
            ->whereNotNull('x')->whereNotNull('y')
            ->limit(5000)
            ->get();

        // Scroll-depth distribution per page (using page_unload event with scroll_pct)
        $scrollDepth = DB::table('interaction_events')
            ->where('created_at', '>=', $since)
            ->where('is_bot', false)
            ->where('event_type', 'page_unload')
            ->whereNotNull('scroll_pct')
            ->selectRaw('path,
                SUM(CASE WHEN scroll_pct >= 10  THEN 1 ELSE 0 END) as r10,
                SUM(CASE WHEN scroll_pct >= 25  THEN 1 ELSE 0 END) as r25,
                SUM(CASE WHEN scroll_pct >= 50  THEN 1 ELSE 0 END) as r50,
                SUM(CASE WHEN scroll_pct >= 75  THEN 1 ELSE 0 END) as r75,
                SUM(CASE WHEN scroll_pct >= 100 THEN 1 ELSE 0 END) as r100,
                COUNT(*) as total')
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        // Named-event counters (everything that's not the noise events)
        $noiseEvents = ['click', 'hover', 'scroll', 'page_unload', 'time_on_page'];
        $namedEvents = DB::table('interaction_events')
            ->where('created_at', '>=', $since)
            ->where('is_bot', false)
            ->whereNotIn('event_type', $noiseEvents)
            ->selectRaw('event_type, COUNT(*) as occurrences, COUNT(DISTINCT session_id) as uniques')
            ->groupBy('event_type')
            ->orderByDesc('occurrences')
            ->get();

        // Recent sessions — for the trail viewer
        $recentSessions = DB::table('interaction_events')
            ->where('created_at', '>=', $since)
            ->where('is_bot', false)
            ->selectRaw('session_id, MIN(created_at) as started, MAX(created_at) as ended, COUNT(*) as events, COUNT(DISTINCT path) as pages, MAX(device) as device, MAX(country) as country')
            ->groupBy('session_id')
            ->having('events', '>=', 3)  // skip drive-by 1-event sessions
            ->orderByDesc('started')
            ->limit(30)
            ->get();

        // Session trail — if a session is selected, show its event sequence
        $selectedSession = $request->query('session');
        $sessionTrail = null;
        if ($selectedSession) {
            $sessionTrail = DB::table('interaction_events')
                ->where('session_id', $selectedSession)
                ->orderBy('created_at')
                ->select('event_type', 'path', 'element_selector', 'element_text', 'meta', 'created_at')
                ->limit(200)
                ->get();
        }

        // Site-wide interaction totals
        $interactionTotals = DB::table('interaction_events')
            ->where('created_at', '>=', $since)
            ->where('is_bot', false)
            ->selectRaw('
                COUNT(*) as total_events,
                COUNT(DISTINCT session_id) as sessions,
                SUM(CASE WHEN event_type = "click" THEN 1 ELSE 0 END) as clicks,
                SUM(CASE WHEN event_type = "hover" THEN 1 ELSE 0 END) as hovers,
                SUM(CASE WHEN event_type NOT IN ("click", "hover", "scroll", "page_unload") THEN 1 ELSE 0 END) as named')
            ->first();

        return view('admin.analytics.index', compact(
            // original
            'topPaths', 'filled', 'topReferrers', 'devices', 'browsers',
            'countries', 'totals', 'days',
            // new
            'topClicks', 'heatmapPages', 'heatmapPath', 'heatmapPoints',
            'scrollDepth', 'namedEvents', 'recentSessions',
            'selectedSession', 'sessionTrail', 'interactionTotals'
        ));
    }
}
