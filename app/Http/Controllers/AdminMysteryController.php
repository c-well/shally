<?php
namespace App\Http\Controllers;

use App\Models\MysteryQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin question bank for the teen hidden-identity mystery ("Undercover").
 *
 * Leaders own the content; the app never invents questions. Each question the
 * app asks privately can become an anonymized public CLUE the room uses to work
 * out who is who. No question ever asks a player to deceive — concealment is the
 * app's job, never the player's. Edits save as you type, mirroring /admin/games.
 */
class AdminMysteryController extends Controller
{
    public function index(): View
    {
        $questions = MysteryQuestion::orderBy('kind')->orderByDesc('id')->get();
        return view('admin.mystery', ['questions' => $questions]);
    }

    public function store(Request $request): RedirectResponse
    {
        MysteryQuestion::create($this->validated($request) + ['created_by' => $request->user()->id]);
        return back()->with('status', 'Question added.');
    }

    public function update(Request $request, MysteryQuestion $question): JsonResponse
    {
        $question->update($this->validated($request, true));
        return response()->json(['ok' => true]);
    }

    public function toggle(MysteryQuestion $question): JsonResponse
    {
        $question->update(['is_active' => ! $question->is_active]);
        return response()->json(['ok' => true, 'is_active' => $question->is_active]);
    }

    public function destroy(MysteryQuestion $question): JsonResponse
    {
        $question->delete();
        return response()->json(['ok' => true]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req  = $partial ? 'sometimes|' : 'required|';
        $data = $request->validate([
            'prompt'   => $req . 'string|max:300',
            'kind'     => $req . 'in:getknow,value,scripture',
            'options'  => 'nullable',
            'clueable' => 'nullable',
        ]);

        if (array_key_exists('options', $data)) {
            $data['options'] = $this->normalizeOptions($data['options']);
        }
        if ($request->has('clueable')) {
            $data['clueable'] = $request->boolean('clueable');
        }

        return $data;
    }

    /** Accept a newline/pipe list (or array) and store a clean array — null = short answer. */
    private function normalizeOptions($raw): ?array
    {
        $list = is_array($raw) ? $raw : preg_split('/\r\n|\r|\n|\|/', (string) $raw);
        $list = array_values(array_filter(array_map('trim', $list), fn ($s) => $s !== ''));

        return $list ?: null;
    }
}
