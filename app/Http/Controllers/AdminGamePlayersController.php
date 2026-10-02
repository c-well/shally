<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\GamePlayer;
use App\Models\GameRun;
use App\Services\KidsPlayerEvidence;
use Illuminate\Http\Request;

class AdminGamePlayersController extends Controller
{
    public function index()
    {
        $players = GamePlayer::withSum('progress as checked_stars', 'earned_stars')
            ->withCount(['runs', 'runs as qualifying_runs_count' => fn ($q) => $q->where('qualifies', true),
                'runs as completed_runs_count' => fn ($q) => $q->whereNotNull('completed_at')])
            ->withSum('runs', 'actions')->withSum('runs', 'invalid_actions')->withSum('runs', 'active_seconds')
            ->withMax('runs', 'last_action_at')->addSelect(['play_days' => GameRun::selectRaw('COUNT(DISTINCT DATE(created_at))')->whereColumn('game_player_id', 'game_players.id')])->orderByDesc('id')->paginate(30);
        foreach ($players as $p) {
            $p->evidence = KidsPlayerEvidence::assess($p->review_status, (int) $p->qualifying_runs_count, (int) $p->runs_sum_invalid_actions, (int) $p->runs_sum_actions, (int) $p->runs_count);
        }

        return view('admin.game-players', compact('players'));
    }

    public function update(Request $request, GamePlayer $player)
    {
        $d = $request->validate(['review_status' => 'required|in:auto,known,test,exclude']);
        $before = $player->review_status;
        $player->review_status = $d['review_status'];
        $player->reviewed_by = $request->user()->id;
        $player->reviewed_at = now();
        $player->save();
        AuditLog::record(event: 'kids_player_review', userId: $request->user()->id, description: 'Reviewed game player #'.$player->id,
            meta: ['player_id' => $player->id, 'before' => $before, 'after' => $d['review_status']]);

        return back()->with('status','Player review saved.');
    }
}
