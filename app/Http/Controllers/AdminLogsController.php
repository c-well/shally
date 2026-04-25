<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLogsController extends Controller
{
    /** GET /admin/logs — recent audit log entries (super_admin only). */
    public function index(Request $request): View
    {
        $event = $request->query('event');           // optional filter
        $userId = $request->query('user_id');

        // Paginate at 20 per page so the table stays light. Older pages reachable via Prev/Next.
        $logs = AuditLog::with('user:id,name')
            ->when($event,  fn ($q) => $q->where('event', $event))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();  // keep ?event=... when paging

        // Counts for the header chips (last 7 days)
        $since = now()->subDays(7);
        $counts = [
            'total'   => AuditLog::where('created_at', '>=', $since)->count(),
            'logins'  => AuditLog::where('event', 'login_success')->where('created_at', '>=', $since)->count(),
            'failed'  => AuditLog::where('event', 'login_failed')->where('created_at', '>=', $since)->count(),
            'magic'   => AuditLog::where('event', 'magic_link_consume')->where('created_at', '>=', $since)->count(),
            'errors'  => AuditLog::where('event', 'like', 'error_%')->where('created_at', '>=', $since)->count(),
        ];

        // Days until the OLDEST entry hits the 40-day purge threshold (for the latch toggle).
        $oldest = AuditLog::orderBy('created_at')->value('created_at');
        $daysUntilNextPurge = $oldest
            ? max(0, 40 - (int) floor($oldest->diffInDays(now())))
            : 40;

        // The latch toggle is shown only to Karlon (he asked for it explicitly so Andre
        // doesn't see the additional UI cruft).
        $isKarlon = (bool) ($request->user()?->email === 'contact@c-wellpics.com');

        // Group consecutive same-(event,user) entries into runs. Runs of 6+ collapse into a
        // single row with a count + expand affordance. Keeps the page readable when one
        // user signs in 47 times in a row during testing.
        $groups = [];
        $current = null;
        foreach ($logs as $log) {
            $key = $log->event . ':' . ($log->user_id ?? 'anon');
            if ($current && $current['key'] === $key) {
                $current['logs'][] = $log;
            } else {
                if ($current) $groups[] = $current;
                $current = ['key' => $key, 'logs' => [$log]];
            }
        }
        if ($current) $groups[] = $current;

        return view('admin.logs', compact('logs', 'groups', 'counts', 'event', 'userId', 'daysUntilNextPurge', 'isKarlon'));
    }
}
