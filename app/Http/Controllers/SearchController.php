<?php
namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\Event;
use App\Models\Page;
use App\Models\QuarterlyLesson;
use App\Models\Sermon;
use Illuminate\View\View;

/**
 * Site-wide search page.
 *
 * Bakes a small corpus from the database (pages, peace notes, bulletins,
 * lessons, events) and renders /search with the corpus inlined as JSON.
 * The browser builds a MiniSearch index client-side and searches across
 * everything in one place.
 *
 * NOT included here: KJV/ESV (lives at /bible) and Hymnal (lives at
 * /hymnal). Those are dedicated, SEO-indexable, single-corpus pages.
 */
class SearchController extends Controller
{
    public function index(): View
    {
        $items = collect();

        // Static site pages — always visible navigation destinations.
        // These are hardcoded because they're well-known, fixed routes.
        $items = $items->merge([
            ['kind' => 'page', 'title' => 'Home',          'desc' => 'Welcome to Shalom Seventh-day Adventist Church.', 'url' => '/'],
            ['kind' => 'page', 'title' => 'About Us',      'desc' => 'Who we are and what we believe.',                'url' => '/about'],
            ['kind' => 'page', 'title' => 'Beliefs',       'desc' => 'The 28 Fundamental Beliefs of the SDA Church.',  'url' => '/beliefs'],
            ['kind' => 'page', 'title' => 'Visit Us',      'desc' => 'Service times, address, what to expect.',        'url' => '/visit'],
            ['kind' => 'page', 'title' => 'Contact',       'desc' => 'Reach the church office.',                        'url' => '/contact'],
            ['kind' => 'page', 'title' => 'Prayer Request','desc' => 'Send a confidential prayer request to our team.','url' => '/prayer'],
            ['kind' => 'page', 'title' => 'Bible (KJV & ESV)', 'desc' => 'Search the Bible, KJV and ESV.',             'url' => '/bible'],
            ['kind' => 'page', 'title' => 'Hymnal',        'desc' => 'The Adventist Hymnal — 694 hymns, searchable, copyable.', 'url' => '/hymnal'],
            ['kind' => 'page', 'title' => 'Sabbath School Lesson', 'desc' => 'This week\'s Adult Bible Study Guide.', 'url' => '/lesson'],
            ['kind' => 'page', 'title' => 'Peace Notes',   'desc' => 'Pastor\'s notes and sermon recaps.',             'url' => '/peace-notes'],
            ['kind' => 'page', 'title' => 'This Sabbath\'s Bulletin', 'desc' => 'Order of service for the upcoming Sabbath.', 'url' => '/welcome'],
            ['kind' => 'page', 'title' => 'Privacy',       'desc' => 'How we handle your data.',                        'url' => '/privacy'],
        ]);

        // Editable pages from DB (in case admin renames or adds new ones)
        foreach (Page::orderBy('slug')->get() as $p) {
            $items->push([
                'kind'  => 'page',
                'title' => $p->title ?? ucfirst($p->slug),
                'desc'  => $p->eyebrow ? $p->eyebrow . ' · ' . strip_tags((string) $p->body_html) : strip_tags((string) $p->body_html),
                'url'   => '/' . $p->slug,
            ]);
        }

        // Sermons (= Peace Notes on the public site)
        foreach (Sermon::orderByDesc('preached_on')->get() as $s) {
            $url = $s->slug ? '/peace-notes/' . $s->slug : '/peace-notes';
            $when = $s->preached_on ? $s->preached_on->format('M j, Y') : '';
            $items->push([
                'kind'  => 'peace-note',
                'title' => $s->title,
                'desc'  => trim(($when ? $when . ' · ' : '') . ($s->scripture_ref ? $s->scripture_ref . ' · ' : '') . ($s->speaker ?? '') . ' — ' . strip_tags((string) $s->description_html)),
                'url'   => $url,
            ]);
        }

        // Bulletins (only published ones)
        foreach (Bulletin::whereNotNull('published_at')->orderByDesc('service_date')->get() as $b) {
            $when = $b->service_date ? $b->service_date->format('M j, Y') : '';
            $items->push([
                'kind'  => 'bulletin',
                'title' => $b->title . ' — ' . $when,
                'desc'  => $b->event_name ? $b->event_name : 'Sabbath order of service',
                'url'   => '/bulletins/' . ($b->service_date ? $b->service_date->format('Y-m-d') : $b->id),
            ]);
        }

        // Quarterly lessons
        foreach (QuarterlyLesson::orderByDesc('starts_on')->get() as $l) {
            $items->push([
                'kind'  => 'lesson',
                'title' => 'Q' . $l->quarter . ' ' . $l->year . ' — ' . ($l->theme ?? 'Sabbath School'),
                'desc'  => 'Sabbath School lesson · ' . ($l->starts_on ? $l->starts_on->format('M j') : '') . ' to ' . ($l->ends_on ? $l->ends_on->format('M j, Y') : ''),
                'url'   => '/lesson',
            ]);
        }

        // Public events
        foreach (Event::where('is_public', true)->orderByDesc('start_at')->get() as $e) {
            $when = $e->start_at ? $e->start_at->format('l, M j · g:ia') : '';
            $items->push([
                'kind'  => 'event',
                'title' => $e->title,
                'desc'  => trim($when . ' · ' . ($e->location ?? '')) . ($e->notes ? ' — ' . strip_tags($e->notes) : ''),
                'url'   => '/events',
            ]);
        }

        // Stamp every item with an ID so MiniSearch is happy
        $corpus = $items->values()->map(function ($it, $i) {
            return [
                'id'    => $i + 1,
                'kind'  => $it['kind'],
                'title' => $it['title'],
                'desc'  => mb_substr((string) $it['desc'], 0, 400),
                'url'   => $it['url'],
            ];
        })->all();

        return view('search', ['corpus' => $corpus]);
    }
}
