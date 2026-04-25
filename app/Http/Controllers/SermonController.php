<?php
namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Sermon;
use Illuminate\View\View;

/**
 * Public sermon archive — replaces the WordPress /yp/ blog.
 *
 * Index ({@see index()}) lists all sermons newest-first under the existing
 * Peace Notes intro markdown. Show ({@see show()}) renders a single sermon
 * with an HTML5 audio player + description.
 */
class SermonController extends Controller
{
    /** GET /peace-notes — index page combining the editable intro + sermon list. */
    public function index(): View
    {
        return view('peace-notes', [
            'page'    => Page::bySlug('peace-notes'),
            'sermons' => Sermon::orderByDesc('preached_on')->get(),
        ]);
    }

    /** GET /peace-notes/{slug} — single sermon detail page. */
    public function show(string $slug): View
    {
        $sermon = Sermon::where('slug', $slug)->firstOrFail();
        // Adjacent navigation — prev/next by preached_on
        $prev = Sermon::where('preached_on', '<', $sermon->preached_on)
            ->orderByDesc('preached_on')->first();
        $next = Sermon::where('preached_on', '>', $sermon->preached_on)
            ->orderBy('preached_on')->first();
        return view('sermon-show', compact('sermon', 'prev', 'next'));
    }
}
