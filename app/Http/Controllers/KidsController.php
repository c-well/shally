<?php
namespace App\Http\Controllers;

use App\Models\GameLevel;
use App\Models\GamePlayer;
use App\Models\GameProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Kids Scripture games — /kids.
 *
 * Not entertainment: every level is a real verse. A name (kept by a
 * localStorage token, no login) lets progress autosave and earns gentle stars.
 * Levels are admin-authored (game_type + age_band + verse), so a leader can pick
 * a book and grow the set.
 */
class KidsController extends Controller
{
    public function index(): View
    {
        $levels  = GameLevel::active()->orderBy('age_band')->orderBy('game_type')->orderBy('sort_order')->get();
        $leaders = GamePlayer::where('total_stars', '>', 0)->orderByDesc('total_stars')->limit(8)->get(['name', 'total_stars']);
        $books   = $levels->pluck('book')->unique()->sort()->values();

        return view('kids.index', compact('levels', 'leaders', 'books'));
    }

    public function play(GameLevel $level): View
    {
        abort_unless($level->is_active, 404);

        if ($level->game_type === 'verse_tetris') {
            return view('kids.tetris', [
                'level'     => $level,
                'questions' => \App\Models\GameQuestion::active()->inRandomOrder()->limit(24)
                    ->get(['question', 'options', 'answer', 'teaching']),
            ]);
        }

        return view('kids.play', ['level' => $level, 'keywords' => $level->keywords()]);
    }

    /** Create a player (just a name). Returns a token the browser keeps. */
    public function register(Request $request): JsonResponse
    {
        $name = trim((string) $request->input('name'));
        if ($name === '' || mb_strlen($name) > 60) return response()->json(['ok' => false], 422);
        $p = GamePlayer::create(['name' => $name, 'token' => (string) Str::uuid()]);
        return response()->json(['ok' => true, 'token' => $p->token, 'name' => $p->name, 'total_stars' => 0]);
    }

    /** Autosave / complete a level for a player. */
    public function save(Request $request): JsonResponse
    {
        $p = GamePlayer::where('token', $request->input('token'))->first();
        $level = GameLevel::find($request->input('level_id'));
        if (! $p || ! $level) return response()->json(['ok' => false], 422);

        $stars     = max(0, min(3, (int) $request->input('stars', 0)));
        $score     = max(0, (int) $request->input('score', 0));
        $completed = $request->boolean('completed');

        $prog = GameProgress::firstOrNew(['game_player_id' => $p->id, 'game_level_id' => $level->id]);
        $prog->state = $request->input('state');
        if ($score > $prog->best_score) $prog->best_score = $score;
        if ($stars > $prog->stars)      $prog->stars = $stars;
        if ($completed && ! $prog->completed_at) $prog->completed_at = now();
        $prog->save();

        $p->total_stars = (int) GameProgress::where('game_player_id', $p->id)->sum('stars');
        $p->save();

        return response()->json(['ok' => true, 'best_score' => $prog->best_score, 'stars' => $prog->stars, 'total_stars' => $p->total_stars]);
    }

    public function leaderboard(): JsonResponse
    {
        $leaders = GamePlayer::where('total_stars', '>', 0)->orderByDesc('total_stars')->limit(10)->get(['name', 'total_stars']);
        return response()->json(['leaders' => $leaders]);
    }
}
