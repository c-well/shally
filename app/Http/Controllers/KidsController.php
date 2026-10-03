<?php

namespace App\Http\Controllers;

use App\Models\GameLevel;
use App\Models\GamePlayer;
use App\Models\GameProgress;
use App\Models\GameQuestion;
use App\Models\GameRun;
use App\Services\KidsGameEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class KidsController extends Controller
{
    public function index(): Response
    {
        $levels = GameLevel::active()->orderBy('age_band')->orderBy('game_type')->orderBy('sort_order')->get();
        $leaders = $this->leaders();

        return response()->view('kids.index', compact('levels', 'leaders'))->header('Cache-Control', 'no-store, private');
    }

    private function leaders()
    {
        // Public totals use the new checked rewards. Historical totals stay in admin.
        return GamePlayer::whereNotIn('review_status', ['test', 'exclude'])
            ->withSum('progress as checked_stars', 'earned_stars')
            ->whereHas('progress', fn ($q) => $q->where('earned_stars', '>', 0))
            ->orderByDesc('checked_stars')->limit(8)->get(['id', 'name']);
    }

    public function play(GameLevel $level): Response
    {
        abort_unless($level->is_active, 404);
        $nextLevel = GameLevel::active()->where('age_band', $level->age_band)->where('id', '!=', $level->id)->orderByRaw('id > ? desc', [$level->id])->orderBy('id')->first();
        $data = ['level' => $level, 'nextLevel' => $nextLevel, 'keywords' => $level->keywords()];
        if ($level->game_type === 'verse_tetris') {
            $data['questions'] = GameQuestion::active()->inRandomOrder()->limit(24)->get(['question', 'options', 'answer', 'teaching']);

            return response()->view('kids.tetris', $data)->header('Cache-Control', 'no-store, private');
        }

        return response()->view('kids.play', $data)->header('Cache-Control', 'no-store, private');
    }

    public function register(Request $request): JsonResponse
    {
        $d = $request->validate(['name' => 'required|string|max:30', 'preview' => 'sometimes|boolean']);
        $name = trim(strip_tags($d['name']));
        if ($name === '') {
            return response()->json(['message' => 'Choose a nickname.'], 422);
        }
        $p = GamePlayer::create(['name' => $name, 'token' => (string) Str::uuid(), 'review_status' => ! empty($d['preview']) ? 'test' : 'auto']);

        return response()->json(['ok' => true, 'token' => $p->token, 'name' => $p->name, 'total_stars' => 0]);
    }

    private function player(Request $r): GamePlayer
    {
        $r->validate(['token' => 'required|uuid']);

        return GamePlayer::where('token', $r->input('token'))->firstOrFail();
    }

    public function profile(Request $request): JsonResponse
    {
        $p = $this->player($request);

        return response()->json(['ok' => true, 'name' => $p->name, 'preview' => $p->review_status === 'test', 'total_stars' => (int) $p->progress()->sum('earned_stars'),
            'levels' => $p->progress()->get(['game_level_id', 'earned_stars'])->mapWithKeys(fn ($v) => [$v->game_level_id => $v->earned_stars])]);
    }

    public function begin(Request $request, KidsGameEngine $engine): JsonResponse
    {
        $p = $this->player($request);
        $d = $request->validate(['level_id' => 'required|integer', 'run_token' => 'nullable|uuid']);
        $level = GameLevel::active()->findOrFail($d['level_id']);
        $hash = hash('sha256', $level->verse_text.'|'.$level->age_band.'|'.$level->game_type);
        $run = null;
        if ($level->game_type !== 'verse_tetris' && ! empty($d['run_token'])) {
            $run = GameRun::where('token', $d['run_token'])->where('game_player_id', $p->id)->where('game_level_id', $level->id)
                ->where('verse_hash', $hash)->whereNull('completed_at')->where('created_at', '>', now()->subDay())->first();
        }
        if (! $run) {
            try {
                $challenge = $engine->challenge($level);
            } catch (InvalidArgumentException $e) {
                return response()->json(['message' => 'This verse needs an adjustment. Please choose another game.'], 422);
            }
            $run = GameRun::create(['token' => (string) Str::uuid(), 'game_player_id' => $p->id, 'game_level_id' => $level->id, 'game_type' => $level->game_type,
                'verse_hash' => $hash, 'challenge' => $challenge, 'state' => ['progress' => 0, 'score' => 0, 'completed' => false]]);
        }

        return response()->json($this->result($run, $p) + ['run_token' => $run->token, 'challenge' => $run->challenge]);
    }

    public function save(Request $request, KidsGameEngine $engine): JsonResponse
    {
        $p = $this->player($request);
        if (! $request->filled('run_token')) {
            return response()->json(['message' => 'Reload this game to use the updated scoring.'], 409);
        }
        $d = $request->validate(['run_token' => 'required|uuid', 'sequence' => 'required|integer|min:1', 'event' => 'required|array', 'event.type' => 'required|string|max:20', 'event.word' => 'sometimes|string|max:200', 'event.index' => 'sometimes|integer|min:0|max:600', 'event.lines' => 'sometimes|integer|min:0|max:4', 'event.start' => 'sometimes|array|size:2', 'event.start.*' => 'integer|min:0|max:12', 'event.end' => 'sometimes|array|size:2', 'event.end.*' => 'integer|min:0|max:12']);

        return DB::transaction(function () use ($p, $d, $engine) {
            // Serialize all writes for one player, including concurrent tabs.
            $p = GamePlayer::whereKey($p->id)->lockForUpdate()->firstOrFail();
            $run = GameRun::where('token', $d['run_token'])->where('game_player_id', $p->id)->lockForUpdate()->firstOrFail();
            $eventHash = hash('sha256', json_encode($d['event']));
            if ($d['sequence'] === $run->sequence && hash_equals($run->last_event_hash ?? '', $eventHash)) {
                return response()->json($this->result($run, $p));
            }
            if ($d['sequence'] !== $run->sequence + 1 || $run->completed_at || $run->created_at->lt(now()->subDay())) {
                return response()->json(['message' => 'This game session has ended. Reload to start again.'], 409);
            }
            $level = GameLevel::active()->findOrFail($run->game_level_id);
            if ($run->verse_hash !== hash('sha256', $level->verse_text.'|'.$level->age_band.'|'.$level->game_type)) {
                return response()->json(['message' => 'This verse was updated. Reload to play the new version.'], 409);
            }
            try {
                $state = $engine->move($run->game_type, $run->challenge, $run->state, $d['event']);
            } catch (InvalidArgumentException $e) {
                $run->increment('invalid_actions');

                return response()->json(['message' => $e->getMessage()], 422);
            }
            $elapsed = max(0, (int) ($run->last_action_at ?? $run->created_at)->diffInSeconds(now()));
            $run->active_seconds += min(20, $elapsed); // Ignore long idle gaps; no raw keystrokes, IPs or fingerprints.
            $run->actions++;
            $run->sequence = $d['sequence'];
            $run->last_event_hash = $eventHash;
            $run->last_action_at = now();
            $run->state = $state;
            if ($state['completed']) {
                $run->completed_at = now();
            }
            $run->qualifies = ($run->completed_at && $run->actions >= 3 && $run->active_seconds >= 10)
                || ($run->actions >= 12 && $run->active_seconds >= 30 && $state['progress'] > 0);
            $run->save();
            $stars = $state['completed'] ? 3 : ($run->game_type === 'verse_tetris' && $state['progress'] > 0 ? 1 : 0);
            if (in_array($p->review_status, ['test', 'exclude'], true)) {
                $stars = 0;
            }
            $prog = GameProgress::firstOrNew(['game_player_id' => $p->id, 'game_level_id' => $run->game_level_id]);
            $prog->state = $state;
            $prog->best_score = max($prog->best_score ?? 0, $state['score']);
            $prog->earned_stars = max($prog->earned_stars ?? 0, $stars);
            // Preserve historical stars separately; never let a claimed client reward drive checked totals.
            $prog->stars = max($prog->stars ?? 0, $stars);
            if ($state['completed'] && ! $prog->completed_at) {
                $prog->completed_at = now();
            }
            $prog->save();

            return response()->json($this->result($run, $p));
        });
    }

    private function result(GameRun $run, GamePlayer $p): array
    {
        return ['ok' => true, 'state' => $run->state, 'sequence' => $run->sequence,
            'total_stars' => (int) $p->progress()->sum('earned_stars'), 'preview' => $p->review_status === 'test'];
    }

    public function leaderboard(): JsonResponse
    {
        return response()->json(['leaders' => $this->leaders()->map(fn ($p) => ['name' => $p->name, 'total_stars' => (int) $p->checked_stars])]);
    }
}
