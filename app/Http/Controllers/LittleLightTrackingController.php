<?php

namespace App\Http\Controllers;

use App\Models\LittleLightRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LittleLightTrackingController extends Controller
{
    public function bootstrap()
    {
        // The game is static HTML. Fetch a fresh, same-origin CSRF token only on play.
        return response()->json(['csrf' => csrf_token()])->header('Cache-Control', 'no-store, private');
    }

    private function browserHash(string $token): string
    {
        return hash_hmac('sha256', $token, config('app.key'));
    }

    public function start(Request $request)
    {
        $d = $request->validate(['run_token' => 'required|uuid', 'browser_token' => 'required|uuid', 'chapter' => 'required|integer|between:1,3', 'preview' => 'required|boolean']);
        $hash = $this->browserHash($d['browser_token']);
        $test = $d['preview'] || in_array($request->user()?->role, ['super_admin', 'clerk', 'department_head'], true);
        $run = LittleLightRun::firstOrCreate(['token' => $d['run_token']], ['browser_hash' => $hash, 'chapter' => $d['chapter'], 'is_test' => $test, 'last_seen_at' => now()]);
        abort_unless(hash_equals($run->browser_hash, $hash) && $run->chapter == $d['chapter'], 409);
        // Retrying a start must not reset progress or create another attempt.
        return response()->json(['ok' => true])->header('Cache-Control', 'no-store, private');
    }

    public function progress(Request $request)
    {
        $d = $request->validate([
            'run_token' => 'required|uuid', 'browser_token' => 'required|uuid', 'sequence' => 'required|integer|between:1,1000000',
            'active_seconds' => 'required|integer|between:0,86400', 'furthest_percent' => 'required|integer|between:0,99',
            'scrolls' => 'required|integer|between:0,3', 'lights' => 'required|integer|between:0,18',
            'sheep' => 'required|integer|between:0,3', 'stones' => 'required|integer|between:0,5',
            'stumbles' => 'required|integer|between:0,10000', 'status' => 'required|in:playing,paused,reading,retry,left,complete',
        ]);
        return DB::transaction(function () use ($d) {
            $run = LittleLightRun::where('token', $d['run_token'])->where('browser_hash', $this->browserHash($d['browser_token']))->lockForUpdate()->firstOrFail();
            abort_if($run->created_at->lt(now()->subDay()), 410);
            if ($run->completed_at || $d['sequence'] <= $run->sequence) {
                return response()->json(['ok' => true]);
            }
            // Client summaries are evidence, not identity proof or server-verified scores.
            // Bound time by server elapsed time, never count client clock changes.
            $elapsed = max(0, (int) $run->created_at->diffInSeconds(now()));
            $seconds = max($run->active_seconds, min($d['active_seconds'], $elapsed));
            if ($seconds > $run->active_seconds) {
                $run->last_active_at = now();
            }
            $run->active_seconds = $seconds;
            foreach (['furthest_percent', 'scrolls', 'lights', 'stumbles'] as $field) {
                $run->$field = max($run->$field, $d[$field]);
            }
            if ($run->chapter == 1) $run->sheep = max($run->sheep, $d['sheep']);
            if ($run->chapter == 2) $run->stones = max($run->stones, $d['stones']);
            $objective = match ((int) $run->chapter) { 1 => $run->sheep >= 3, 2 => $run->stones >= 5, 3 => $run->scrolls >= 3 };
            if ($d['status'] === 'complete') {
                abort_unless($objective && $run->furthest_percent >= ($run->chapter == 2 ? 85 : 90) && $seconds >= 10, 422, 'Completion needs chapter progress.');
                $run->completed_at = now();
                $run->furthest_percent = 100;
            }
            $run->status = $d['status'];
            $run->sequence = $d['sequence'];
            $run->last_seen_at = now();
            $run->save();
            return response()->json(['ok' => true]);
        });
    }
}
