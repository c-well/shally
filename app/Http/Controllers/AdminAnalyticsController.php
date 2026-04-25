<?php
namespace App\Http\Controllers;

use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * /admin/analytics — first-party page-view analytics dashboard.
 *
 * Reads from page_views (populated by App\Http\Middleware\TrackPageView).
 * No external trackers. All data lives in our DB. Privacy-first by design:
 * IPs are hashed with the app key, never stored raw.
 */
class AdminAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $days = (int) $request->query('days', 30);
        $days = max(1, min(365, $days));
        $since = now()->subDays($days);

        // Top paths — total views + unique sessions
        $topPaths = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->select('path')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('COUNT(DISTINCT session_id) as uniques')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(20)
            ->get();

        // Daily trend — views + uniques per day
        $daily = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->selectRaw('DATE(viewed_at) as d')
            ->selectRaw('COUNT(*) as views')
            ->selectRaw('COUNT(DISTINCT session_id) as uniques')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        // Fill in missing days with zeros so the trendline doesn't have gaps
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

        // Referrers — strip own domain
        $ownHost = parse_url(config('app.url'), PHP_URL_HOST);
        $topReferrers = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->where('referrer', 'NOT LIKE', '%' . $ownHost . '%')
            ->selectRaw('referrer, COUNT(*) as views')
            ->groupBy('referrer')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // Device + browser breakdown
        $devices = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->whereNotNull('device')
            ->selectRaw('device, COUNT(*) as views, COUNT(DISTINCT session_id) as uniques')
            ->groupBy('device')
            ->orderByDesc('views')
            ->get();

        $browsers = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->whereNotNull('browser')
            ->selectRaw('browser, COUNT(*) as views')
            ->groupBy('browser')
            ->orderByDesc('views')
            ->get();

        $countries = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as views')
            ->groupBy('country')
            ->orderByDesc('views')
            ->limit(15)
            ->get();

        // Totals
        $totals = DB::table('page_views')
            ->where('viewed_at', '>=', $since)
            ->selectRaw('COUNT(*) as views, COUNT(DISTINCT session_id) as uniques, COUNT(DISTINCT path) as pages')
            ->first();

        return view('admin.analytics.index', compact(
            'topPaths', 'filled', 'topReferrers', 'devices', 'browsers',
            'countries', 'totals', 'days'
        ));
    }
}
