<?php
namespace App\Http\Controllers;

use App\Models\GameLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin level builder for the kids Scripture games. A level = a verse + a game
 * type + an age band; leaders pick a book and grow the set so kids 4–9 and teens
 * each get the right challenge.
 */
class AdminGameLevelsController extends Controller
{
    public function index(): View
    {
        $levels = GameLevel::orderBy('game_type')->orderBy('age_band')->orderBy('sort_order')->get();
        return view('admin.games', ['levels' => $levels]);
    }

    public function store(Request $request): RedirectResponse
    {
        GameLevel::create($this->validated($request) + ['created_by' => $request->user()->id]);
        return back()->with('status', 'Level added.');
    }

    public function update(Request $request, GameLevel $level): JsonResponse
    {
        $level->update($this->validated($request, true));
        return response()->json(['ok' => true]);
    }

    public function toggle(GameLevel $level): JsonResponse
    {
        $level->update(['is_active' => ! $level->is_active]);
        return response()->json(['ok' => true, 'is_active' => $level->is_active]);
    }

    public function destroy(GameLevel $level): JsonResponse
    {
        $level->delete();
        return response()->json(['ok' => true]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes|' : 'required|';
        return $request->validate([
            'game_type'  => $req . 'in:word_search,verse_tetris,memory_match,hidden_words',
            'age_band'   => $req . 'in:little,older,teens',
            'book'       => $req . 'string|max:40',
            'reference'  => $req . 'string|max:60',
            'verse_text' => $req . 'string|max:600',
            'title'      => 'nullable|string|max:120',
            'difficulty' => 'nullable|integer|min:1|max:5',
        ]);
    }
}
