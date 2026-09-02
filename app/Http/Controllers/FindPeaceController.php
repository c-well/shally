<?php

namespace App\Http\Controllers;

use App\Models\PeacePoll;
use App\Models\PeaceQaPair;
use App\Models\PeaceSermon;
use App\Models\PeaceTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FindPeaceController extends Controller
{
    /** GET /find-peace — homepage: hero + recent messages */
    public function index(): View
    {
        $recent = PeaceSermon::whereNotNull('published_at')
            ->orderByDesc('sermon_date')
            ->limit(20)
            ->with(['scriptures' => fn ($q) => $q->orderBy('display_order')->limit(3)])
            ->get(['id', 'slug', 'title', 'sermon_date', 'speaker', 'heart_line', 'summary_paragraphs']);

        // Top 3 Q&As from the most recent sermon — default cards under search
        $latest = $recent->first();
        $featuredQAs = collect();
        if ($latest) {
            $featuredQAs = PeaceQaPair::where('sermon_id', $latest->id)
                ->orderBy('display_order')
                ->limit(3)
                ->get()
                ->each(fn ($qa) => $qa->setRelation('sermon', $latest));
        }

        // Full Q&A corpus across all published sermons — fed to MiniSearch client-side
        $qaCorpus = PeaceQaPair::query()
            ->join('peace_sermons', 'peace_sermons.id', '=', 'peace_qa_pairs.sermon_id')
            ->whereNotNull('peace_sermons.published_at')
            ->select(
                'peace_qa_pairs.id',
                'peace_qa_pairs.question',
                'peace_qa_pairs.answer',
                'peace_sermons.title as sermon_title',
                'peace_sermons.slug as sermon_slug'
            )
            ->orderBy('peace_qa_pairs.display_order')
            ->get()
            ->toArray();

        // Topic pills — the words people are actually carrying (every one a real page)
        $topics = PeaceTopic::has('sermons')->orderBy('name')->get(['name', 'slug']);

        return view('find-peace.index', [
            'recent' => $recent,
            'featuredQAs' => $featuredQAs,
            'qaCorpus' => $qaCorpus,
            'topics' => $topics,
        ]);
    }

    /** GET /find-peace/{slug} — single sermon page */
    /**
     * GET /find-peace/search-messages?q= — mini search over the message archive.
     * Matches title, speaker, the written summaries, AND the sermon transcript
     * (search fuel only — transcripts are never displayed). "Some random word
     * they mentioned in the message" finds the message. (Karlon 2026-07-04)
     */
    public function searchMessages(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json(['results' => []]);
        }
        $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';

        $rows = PeaceSermon::whereNotNull('published_at')
            ->where(fn ($w) => $w->where('title', 'like', $like)
                ->orWhere('speaker', 'like', $like)
                ->orWhere('heart_line', 'like', $like)
                ->orWhere('summary_paragraphs', 'like', $like)
                ->orWhere('transcript_raw', 'like', $like))
            ->orderByDesc('sermon_date')
            ->limit(8)
            ->get(['id', 'slug', 'title', 'speaker', 'sermon_date', 'heart_line', 'summary_paragraphs', 'transcript_raw']);

        $results = $rows->map(function ($s) use ($q) {
            $sum = is_array($s->summary_paragraphs) ? implode(' ', $s->summary_paragraphs) : (string) $s->summary_paragraphs;
            $inMeta = stripos($s->title.' '.$s->speaker.' '.$s->heart_line.' '.$sum, $q) !== false;
            $snippet = null;
            if (! $inMeta && $s->transcript_raw) {
                $pos = stripos($s->transcript_raw, $q);
                if ($pos !== false) {
                    $from = max(0, $pos - 45);
                    $snippet = ($from > 0 ? '…' : '').trim(mb_substr($s->transcript_raw, $from, 110)).'…';
                    // auto-captions often SHOUT — soften mostly-caps snippets to sentence case
                    $letters = preg_replace('/[^a-zA-Z]/', '', $snippet);
                    if ($letters !== '' && strlen(preg_replace('/[^A-Z]/', '', $letters)) / strlen($letters) > 0.7) {
                        $snippet = ucfirst(mb_strtolower($snippet));
                    }
                }
            }

            return [
                'slug' => $s->slug,
                'title' => $s->title,
                'speaker' => $s->speaker,
                'when' => optional($s->sermon_date)->format('M j, Y'),
                'heart' => $s->heart_line,
                'snippet' => $snippet,   // present only for transcript-only hits
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)
            ->whereNotNull('published_at')
            ->with([
                'qaPairs' => fn ($q) => $q->orderBy('display_order'),
                'scriptures' => fn ($q) => $q->orderBy('display_order'),
                'topics',
            ])
            ->first();

        // Legacy URLs carried a video-id suffix (not-your-address-hiRMwR).
        // Slugs were cleaned 2026-07-04, and those old URLs are still in
        // Google's index and in whatever anyone shared at the time.
        //
        // Two things the first pass missed, both found in the crawl log:
        // the suffix is not always six characters (put-on-something-Sx07l is
        // five), and some messages were renamed as well as de-suffixed, so the
        // stripped base is only a prefix of the slug it became
        // (unshakeable-focus -> unshakeable-focus-in-a-shaking-world).
        //
        // The suffix must look like a generated id — mixed case, or digits —
        // so an ordinary two-word slug such as "be-fruitful" is never mistaken
        // for one and silently redirected somewhere it does not belong.
        if (! $sermon && preg_match('/^(.+?)-([A-Za-z0-9_-]{4,11})$/', $slug, $m)
            && preg_match('/[A-Z]/', $m[2]) && preg_match('/[A-Z0-9].*[A-Z0-9]/', $m[2])) {

            $base = $m[1];

            $clean = PeaceSermon::where('slug', $base)->whereNotNull('published_at')->first();

            if (! $clean) {
                // Renamed: take the prefix match, but only when there is
                // exactly one — guessing between two is worse than a 404.
                $candidates = PeaceSermon::where('slug', 'like', $base.'-%')
                    ->whereNotNull('published_at')->limit(2)->get();

                $clean = $candidates->count() === 1 ? $candidates->first() : null;
            }

            if ($clean) {
                return redirect()->route('find-peace.show', $clean->slug, 301);
            }
        }
        abort_unless($sermon, 404);

        // "If this spoke to you, also hear…" — rank siblings by shared-topic count
        // (closer match = better UX + better internal linking for SEO; fallback to recency).
        $topicIds = $sermon->topics->pluck('id')->map(fn ($i) => (int) $i)->all();
        $related = PeaceSermon::query()
            ->whereNotNull('published_at')
            ->where('id', '!=', $sermon->id)
            ->when(! empty($topicIds), function ($q) use ($topicIds) {
                $ids = implode(',', $topicIds);
                $q->select('peace_sermons.*')
                    ->selectRaw("(SELECT COUNT(*) FROM peace_sermon_topic pst WHERE pst.sermon_id = peace_sermons.id AND pst.topic_id IN ($ids)) as shared_topics")
                    ->orderByDesc('shared_topics');
            })
            ->orderByDesc('sermon_date')
            ->limit(3)
            ->get();

        $poll = PeacePoll::forSermon($sermon);
        if ($poll) {
            $poll->load('options');
        }
        $seeker = request()->attributes->get('seeker');
        $savedQaIds = $seeker
            ? \DB::table('peace_saved_qas')
                ->where('subscriber_id', $seeker->id)
                ->whereIn('qa_id', $sermon->qaPairs->pluck('id'))
                ->pluck('qa_id')->all()
            : [];

        return view('find-peace.show', [
            'sermon' => $sermon,
            'related' => $related,
            'poll' => $poll,
            'savedQaIds' => $savedQaIds,
        ]);
    }

    public function topic(string $slug): View
    {
        $topic = PeaceTopic::where('slug', $slug)->firstOrFail();
        $sermons = $topic->sermons()
            ->whereNotNull('published_at')
            ->orderByDesc('sermon_date')
            ->get();

        return view('find-peace.topic', compact('topic', 'sermons'));
    }

    /** GET /find-peace/saved — seeker's saved Q&As. */
    public function saved(Request $request): View
    {
        $seeker = $request->attributes->get('seeker');
        if (! $seeker) {
            abort(401);
        }

        $items = $seeker->savedQas()
            ->with(['sermon:id,slug,title,speaker,sermon_date'])
            ->get();

        return view('find-peace.saved', compact('seeker', 'items'));
    }

    /** POST /peace/saved/{qa} — toggle saved state for a Q&A (logged-in seekers only). */
    public function savedToggle(Request $request, int $qa): JsonResponse
    {
        $seeker = $request->attributes->get('seeker');
        if (! $seeker) {
            return response()->json(['ok' => false, 'error' => 'auth required'], 401);
        }

        $exists = PeaceQaPair::where('id', $qa)->exists();
        if (! $exists) {
            return response()->json(['ok' => false, 'error' => 'qa not found'], 404);
        }

        $existing = \DB::table('peace_saved_qas')
            ->where('subscriber_id', $seeker->id)
            ->where('qa_id', $qa)
            ->first();

        if ($existing) {
            \DB::table('peace_saved_qas')
                ->where('subscriber_id', $seeker->id)
                ->where('qa_id', $qa)
                ->delete();

            return response()->json(['ok' => true, 'saved' => false]);
        }

        \DB::table('peace_saved_qas')->insert([
            'subscriber_id' => $seeker->id,
            'qa_id' => $qa,
            'saved_at' => now(),
        ]);

        return response()->json(['ok' => true, 'saved' => true]);
    }
}
