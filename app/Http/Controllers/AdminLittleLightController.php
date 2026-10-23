<?php

namespace App\Http\Controllers;

use App\Models\LittleLightRun;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminLittleLightController extends Controller
{
    public function index(Request $request)
    {
        $days = in_array((int) $request->query('days', 30), [7, 30, 90], true) ? (int) $request->query('days', 30) : 30;
        $tests = $request->query('tests') === '1';
        $base = LittleLightRun::where('is_test', $tests)->where('created_at', '>=', now()->subDays($days));
        $totals = ['starts' => (clone $base)->count(), 'browsers' => (clone $base)->distinct()->count('browser_hash'),
            'engaged' => (clone $base)->engaged()->count(), 'completed' => (clone $base)->whereNotNull('completed_at')->count(),
            'seconds' => (clone $base)->sum('active_seconds'), 'latest' => (clone $base)->max('last_active_at')];
        $names = [1 => 'The Young Shepherd', 2 => 'David & Goliath', 3 => 'The Cave of Mercy'];
        $chapters = [];
        foreach ($names as $chapter => $name) {
            $q = (clone $base)->where('chapter', $chapter);
            $chapters[] = ['name' => $name, 'starts' => (clone $q)->count(), 'engaged' => (clone $q)->engaged()->count(),
                'completed' => (clone $q)->whereNotNull('completed_at')->count(), 'scrolls' => (clone $q)->sum('scrolls'),
                'quarter' => (clone $q)->where('furthest_percent', '>=', 25)->count(), 'half' => (clone $q)->where('furthest_percent', '>=', 50)->count(),
                'three_quarters' => (clone $q)->where('furthest_percent', '>=', 75)->count(), 'stumbles' => (clone $q)->sum('stumbles')];
        }
        $recent = (clone $base)->latest('last_seen_at')->paginate(25)->withQueryString();
        return response()->view('admin.little-light', compact('days', 'tests', 'totals', 'chapters', 'recent', 'names'))->header('Cache-Control', 'no-store, private');
    }

    public function review(Request $request, LittleLightRun $run)
    {
        $d = $request->validate(['is_test' => 'required|boolean']);
        $run->update(['is_test' => $d['is_test']]);
        AuditLog::record(event: 'little_light_review', userId: $request->user()->id, description: 'Reviewed Little Light attempt #'.$run->id, meta: ['is_test' => $d['is_test']]);
        return back()->with('status', 'Attempt classification saved.');
    }
}
