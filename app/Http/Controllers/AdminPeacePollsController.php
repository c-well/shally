<?php
namespace App\Http\Controllers;

use App\Models\PeacePoll;
use App\Models\PeacePollOption;
use App\Models\PeaceSermon;
use App\Models\PeaceTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin CRUD for the Finding Peace poll widget.
 *
 * Routes (all gated by auth + role:super_admin):
 *   GET    /admin/peace/polls              — list
 *   GET    /admin/peace/polls/create       — new form
 *   POST   /admin/peace/polls              — store
 *   GET    /admin/peace/polls/{poll}/edit  — edit form
 *   POST   /admin/peace/polls/{poll}       — update + replace options
 *   DELETE /admin/peace/polls/{poll}       — destroy
 */
class AdminPeacePollsController extends Controller
{
    public function index(): View
    {
        $polls = PeacePoll::with(['topic', 'sermon', 'options'])
            ->withCount('responses')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();
        return view('admin.peace.polls.index', compact('polls'));
    }

    public function create(): View
    {
        return view('admin.peace.polls.edit', [
            'poll'    => new PeacePoll(['is_active' => true, 'rotation_days' => 14]),
            'topics'  => PeaceTopic::has('sermons')->orderBy('name')->get(),
            'sermons' => PeaceSermon::orderByDesc('sermon_date')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateForm($request);
        $poll = DB::transaction(function () use ($data) {
            $poll = PeacePoll::create([
                'question'            => $data['question'],
                'scripture_ref'       => $data['scripture_ref'] ?? null,
                'scripture_explainer' => $data['scripture_explainer'],
                'topic_id'            => $data['topic_id'] ?? null,
                'sermon_id'           => $data['sermon_id'] ?? null,
                'is_active'           => (bool) ($data['is_active'] ?? false),
                'display_priority'    => $data['display_priority'] ?? 0,
                'rotation_days'       => $data['rotation_days'] ?? 14,
            ]);
            $this->saveOptions($poll, $data['options'] ?? [], $data['canonical_index'] ?? null);
            return $poll;
        });
        return redirect()->route('admin.peace.polls.edit', $poll)->with('status', 'Poll created.');
    }

    public function edit(PeacePoll $poll): View
    {
        $poll->load('options', 'responses');
        return view('admin.peace.polls.edit', [
            'poll'    => $poll,
            'topics'  => PeaceTopic::has('sermons')->orderBy('name')->get(),
            'sermons' => PeaceSermon::orderByDesc('sermon_date')->get(),
        ]);
    }

    public function update(Request $request, PeacePoll $poll): RedirectResponse
    {
        $data = $this->validateForm($request);
        DB::transaction(function () use ($poll, $data) {
            $poll->update([
                'question'            => $data['question'],
                'scripture_ref'       => $data['scripture_ref'] ?? null,
                'scripture_explainer' => $data['scripture_explainer'],
                'topic_id'            => $data['topic_id'] ?? null,
                'sermon_id'           => $data['sermon_id'] ?? null,
                'is_active'           => (bool) ($data['is_active'] ?? false),
                'display_priority'    => $data['display_priority'] ?? 0,
                'rotation_days'       => $data['rotation_days'] ?? 14,
            ]);
            // Replace options — but preserve existing IDs & response_counts where the option_text matches
            $this->saveOptions($poll, $data['options'] ?? [], $data['canonical_index'] ?? null);
        });
        return redirect()->route('admin.peace.polls.edit', $poll)->with('status', 'Saved.');
    }

    public function destroy(PeacePoll $poll): RedirectResponse
    {
        $poll->delete();
        return redirect()->route('admin.peace.polls.index')->with('status', 'Poll deleted.');
    }

    private function validateForm(Request $request): array
    {
        return $request->validate([
            'question'             => 'required|string|max:500',
            'scripture_ref'        => 'nullable|string|max:100',
            'scripture_explainer'  => 'required|string|max:5000',
            'topic_id'             => 'nullable|integer|exists:peace_topics,id',
            'sermon_id'            => 'nullable|integer|exists:peace_sermons,id',
            'is_active'            => 'sometimes|boolean',
            'display_priority'     => 'nullable|integer',
            'rotation_days'        => 'nullable|integer|min:1|max:365',
            'options'              => 'required|array|min:2|max:6',
            'options.*'            => 'required|string|max:300',
            'canonical_index'      => 'nullable|integer',
        ]);
    }

    /**
     * Replace a poll's options with the provided list.
     * Preserves any existing option whose text exactly matches a new entry (keeps response_count).
     * Removes options that no longer appear.
     */
    private function saveOptions(PeacePoll $poll, array $optionTexts, ?int $canonicalIndex): void
    {
        $existing = $poll->options()->get()->keyBy(fn($o) => trim(mb_strtolower($o->option_text)));
        $keptIds = [];
        foreach ($optionTexts as $i => $text) {
            $text = trim((string) $text);
            if ($text === '') continue;
            $key = mb_strtolower($text);
            $isCanonical = ($canonicalIndex !== null && (int) $canonicalIndex === $i);
            if (isset($existing[$key])) {
                $opt = $existing[$key];
                $opt->update([
                    'is_canonical'  => $isCanonical,
                    'display_order' => $i,
                ]);
                $keptIds[] = $opt->id;
            } else {
                $opt = $poll->options()->create([
                    'option_text'   => $text,
                    'is_canonical'  => $isCanonical,
                    'display_order' => $i,
                ]);
                $keptIds[] = $opt->id;
            }
        }
        // Drop any options not in the new list (and their responses cascade)
        $poll->options()->whereNotIn('id', $keptIds)->delete();
    }
}
