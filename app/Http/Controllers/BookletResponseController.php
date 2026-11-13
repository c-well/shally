<?php

namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\BookletResponse;
use App\Support\BookletLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

/** "Your Sabbath" under the web booklet: two tick-box questions and one to write. Private to the church. */
class BookletResponseController extends Controller
{
    public function store(Request $req, Bulletin $bulletin): RedirectResponse
    {
        if (! $bulletin->published_snapshot) abort(404);
        $back = route('bulletins.booklet', $bulletin) . '#sabbath';

        /* Bots: honeypot, or a form sent within 3 seconds of opening, get a quiet thanks. */
        $opened = (int) $req->input('form_opened_at', 0);
        if (filled($req->input('website')) || ($opened > 0 && time() * 1000 - $opened < 3000)) {
            return redirect($back)->with('sabbath_flash', 'Thank you for sharing.');
        }

        $q = BookletLayout::questions((array) $bulletin->cover);
        $data = $req->validate([
            'spoke'      => 'nullable|array|max:10',
            'spoke.*'    => ['string', Rule::in($q['spoke']['options'])],
            'joined'     => 'nullable|array|max:10',
            'joined.*'   => ['string', Rule::in($q['joined']['options'])],
            'story'      => 'nullable|string|max:2000',
            'first_name' => 'nullable|string|max:40',
        ]);
        $story = trim((string) ($data['story'] ?? ''));
        if (empty($data['spoke']) && empty($data['joined']) && $story === '') {
            return redirect($back)->withInput()->with('sabbath_error', 'Tick a box or write a line, then send.');
        }

        $key = 'booklet-response:' . $req->ip();
        if (RateLimiter::tooManyAttempts($key, 4)) {
            return redirect($back)->with('sabbath_flash', 'Thank you. We already have your answers.');
        }
        RateLimiter::hit($key, 3600);

        BookletResponse::create([
            'bulletin_id' => $bulletin->id,
            'picks'       => ['spoke' => array_values($data['spoke'] ?? []), 'joined' => array_values($data['joined'] ?? [])],
            'story'       => $story ?: null,
            'first_name'  => trim((string) ($data['first_name'] ?? '')) ?: null,
            'ip_hash'     => hash('sha256', $req->ip() . config('app.key')),
        ]);

        return redirect($back)->with('sabbath_flash', 'Thank you for sharing. Andre and the church will read it.');
    }

    /** Admin: everything people shared, newest Sabbath first, with the tick-box counts. */
    public function index()
    {
        $bulletins = Bulletin::whereIn('id', BookletResponse::select('bulletin_id'))->orderByDesc('service_date')->get();
        $groups = $bulletins->map(function ($b) {
            $rows = BookletResponse::where('bulletin_id', $b->id)->latest()->get();
            $tally = ['spoke' => [], 'joined' => []];
            foreach ($rows as $r) {
                foreach (['spoke', 'joined'] as $k) {
                    foreach ((array) ($r->picks[$k] ?? []) as $o) $tally[$k][$o] = ($tally[$k][$o] ?? 0) + 1;
                }
            }
            foreach ($tally as &$t) arsort($t);
            return ['bulletin' => $b, 'rows' => $rows, 'tally' => $tally, 'q' => BookletLayout::questions((array) $b->cover)];
        });

        return view('admin.booklet-responses', compact('groups'));
    }
}
