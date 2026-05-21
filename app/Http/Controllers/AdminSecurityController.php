<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * /admin/security — live view of app + OS-level defenses.
 *
 * App-level: blocked_ips table (auto-populated by AuditLog::record when an IP
 *            crosses 20 failures/24h). Fully manageable here — one-click unblock.
 *
 * OS-level:  CSF firewall deny list (~200 IPs). Read-only summary; managed via
 *            cPanel UI or root SSH. Snapshot updated every 5 min by a root cron
 *            at /usr/local/bin/csf-stats-snapshot.sh.
 */
class AdminSecurityController extends Controller
{
    public function index(): View
    {
        // ── App-level: active blocked_ips ──────────────────────────────
        $blocked = DB::table('blocked_ips')
            ->orderByDesc('blocked_at')
            ->get();

        // ── Top abusers (last 30 days), regardless of block status ────
        $now    = now();
        $win30  = $now->copy()->subDays(30);
        $win24h = $now->copy()->subDay();

        $topAbusers30d = AuditLog::query()
            ->whereIn('event', ['login_failed', 'magic_link_request', 'magic_link_honeypot', 'pin_login_failed'])
            ->where('created_at', '>=', $win30)
            ->selectRaw('ip_address, count(*) as n, max(created_at) as last_seen, count(distinct event) as distinct_events')
            ->groupBy('ip_address')
            ->orderByDesc('n')
            ->limit(20)
            ->get();

        $topAbusers24h = AuditLog::query()
            ->whereIn('event', ['login_failed', 'magic_link_request', 'magic_link_honeypot', 'pin_login_failed'])
            ->where('created_at', '>=', $win24h)
            ->selectRaw('ip_address, count(*) as n, max(created_at) as last_seen')
            ->groupBy('ip_address')
            ->orderByDesc('n')
            ->limit(10)
            ->get();

        // Mark the ones already blocked
        $blockedIps = collect($blocked)->pluck('ip_address')->all();
        $topAbusers30d->each(fn($r) => $r->is_blocked = in_array($r->ip_address, $blockedIps, true));
        $topAbusers24h->each(fn($r) => $r->is_blocked = in_array($r->ip_address, $blockedIps, true));

        // ── Recent failed auth events (live feed, last 50) ────────────
        $recent = AuditLog::query()
            ->whereIn('event', ['login_failed', 'magic_link_request', 'magic_link_honeypot', 'pin_login_failed', 'ip_auto_blocked'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // ── OS-level: CSF snapshot ───────────────────────────────────
        $csf = ['available' => false, 'total_blocked' => 0, 'recent_top' => [], 'updated_at' => null];
        $csfPath = '/home/shalom/security/csf-stats.json';
        if (is_readable($csfPath)) {
            $data = json_decode((string) @file_get_contents($csfPath), true);
            if (is_array($data)) {
                $csf['available']     = true;
                $csf['total_blocked'] = $data['total_blocked'] ?? 0;
                $csf['recent_top']    = $data['recent_top']    ?? [];
                $csf['updated_at']    = $data['updated_at']    ?? null;
            }
        }

        return view('admin.security', [
            'blocked'        => $blocked,
            'topAbusers30d'  => $topAbusers30d,
            'topAbusers24h'  => $topAbusers24h,
            'recent'         => $recent,
            'csf'            => $csf,
        ]);
    }

    /** POST /admin/security/unblock/{id} — remove an entry from the app blocklist. */
    public function unblock(Request $request, int $id): RedirectResponse
    {
        $row = DB::table('blocked_ips')->where('id', $id)->first();
        if (! $row) {
            return back()->with('status', 'That block does not exist.');
        }
        DB::table('blocked_ips')->where('id', $id)->delete();
        AuditLog::record(
            event: 'ip_manual_unblocked',
            userId: auth()->id(),
            description: 'Admin unblocked ' . $row->ip_address . ' (was: ' . ($row->reason ?? 'no reason') . ')',
        );
        return back()->with('status', $row->ip_address . ' unblocked.');
    }

    /** POST /admin/security/block — add a new manual block. */
    public function block(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ip_address' => ['required', 'ip'],
            'reason'     => ['nullable', 'string', 'max:200'],
            'duration'   => ['nullable', 'in:24h,7d,30d,permanent'],
        ]);
        $expires = match ($data['duration'] ?? '24h') {
            '24h'       => now()->addDay(),
            '7d'        => now()->addWeek(),
            '30d'       => now()->addMonth(),
            'permanent' => null,
            default     => now()->addDay(),
        };
        DB::table('blocked_ips')->updateOrInsert(
            ['ip_address' => $data['ip_address']],
            [
                'reason'        => $data['reason'] ?? 'Manual block by admin',
                'blocked_at'    => now(),
                'expires_at'    => $expires,
                'hits_at_block' => 0,
            ]
        );
        AuditLog::record(
            event: 'ip_manual_blocked',
            userId: auth()->id(),
            description: 'Admin manually blocked ' . $data['ip_address'] . ' until ' . ($expires?->toDateTimeString() ?? 'forever'),
        );
        return back()->with('status', $data['ip_address'] . ' blocked.');
    }
}
