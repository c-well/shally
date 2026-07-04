<?php
namespace App\Http\Controllers;

use App\Models\MessageComment;
use App\Models\PeaceSermon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public "Messages" — the CHURCH's sermon archive (members' turf).
 * Built on the Find Peace audio pipeline (PeaceSermon) but a separate front:
 * Find Peace serves seekers and keeps the SEO; /messages serves the family —
 * listen, share outward, and leave a word (signed-in comments).
 */
class MessagesController extends Controller
{
    public function index(): View
    {
        $messages = PeaceSermon::whereNotNull('published_at')
            ->whereNotNull('audio_url')
            ->where('audio_status', '!=', 'failed')
            ->orderByDesc('sermon_date')->orderByDesc('id')
            ->get();

        return view('messages.index', ['messages' => $messages]);
    }

    /** GET /messages/{slug} — one message: player, summary, scriptures, comments. */
    public function show(string $slug): View|RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->whereNotNull('published_at')
            ->with(['scriptures' => fn ($q) => $q->orderBy('display_order')])
            ->first();
        if (! $sermon && preg_match('/^(.+)-[A-Za-z0-9_-]{6}$/', $slug, $m)) {
            $clean = PeaceSermon::where('slug', $m[1])->whereNotNull('published_at')->first();
            if ($clean) return redirect()->route('messages.show', $clean->slug, 301);
        }
        abort_unless($sermon, 404);

        $comments = MessageComment::where('sermon_id', $sermon->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('messages.show', ['sermon' => $sermon, 'comments' => $comments]);
    }

    /** POST /messages/{slug}/comments — signed-in members; honeypot at the route. */
    public function storeComment(Request $request, string $slug): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->whereNotNull('published_at')->firstOrFail();
        $data = $request->validate(['body' => 'required|string|min:2|max:1000']);

        MessageComment::create([
            'sermon_id' => $sermon->id,
            'user_id'   => $request->user()->id,
            'body'      => trim($data['body']),
        ]);

        return redirect()->route('messages.show', $sermon->slug)->with('commented', true);
    }
}
