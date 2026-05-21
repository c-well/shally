<?php
namespace App\Http\Controllers;

use App\Models\QuarterlyLesson;
use Illuminate\Http\Response;

/**
 * Sitemap + robots.txt generator.
 *
 * One source of truth — every public-indexable URL we have is registered here.
 * When you add a new public page, add it to {@see urls()} so Google finds it
 * on the next crawl. Cache the response for an hour to keep the per-request
 * cost trivial.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(config('app.url', 'https://app.thechurchofpeace.org'), '/');
        $today = now()->toDateString();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($this->urls() as $u) {
            $loc = $base . $u['path'];
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
            $xml .= "    <lastmod>" . ($u['lastmod'] ?? $today) . "</lastmod>\n";
            $xml .= "    <changefreq>" . ($u['freq'] ?? 'monthly') . "</changefreq>\n";
            $xml .= "    <priority>" . ($u['priority'] ?? '0.5') . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>' . "\n";

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $base = rtrim(config('app.url', 'https://app.thechurchofpeace.org'), '/');
        $body = <<<TXT
        User-agent: *
        Allow: /

        # Don't index admin, auth, or API surfaces
        Disallow: /admin
        Disallow: /admin/
        Disallow: /login
        Disallow: /register
        Disallow: /api/
        Disallow: /feedback

        Sitemap: {$base}/sitemap.xml
        TXT;

        return response($body, 200, [
            'Content-Type'  => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /** Public, indexable URLs in priority order. Lessons are added dynamically below. */
    private function urls(): array
    {
        $today = now()->toDateString();

        $static = [
            ['path' => '/',     'priority' => '1.0', 'freq' => 'weekly',  'lastmod' => $today],
            ['path' => '/about',       'priority' => '0.9', 'freq' => 'monthly'],
            ['path' => '/visit',       'priority' => '0.9', 'freq' => 'monthly'],
            ['path' => '/beliefs',     'priority' => '0.85','freq' => 'yearly'],
            ['path' => '/peace-notes', 'priority' => '0.85','freq' => 'weekly',  'lastmod' => $today],
            ['path' => '/lesson',      'priority' => '0.9', 'freq' => 'weekly',  'lastmod' => $today],
            ['path' => '/contact',     'priority' => '0.7', 'freq' => 'yearly'],
        ];

        // Lesson weeks 1–13 (current quarter only)
        $q = QuarterlyLesson::current();
        if ($q) {
            for ($i = 1; $i <= 13; $i++) {
                $static[] = [
                    'path'     => "/lesson/{$i}",
                    'priority' => '0.7',
                    'freq'     => 'weekly',
                    'lastmod'  => $today,
                ];
            }
        }

        // ── Finding Peace — sermon pages + topic browse pages ─────────────
        // Highest-quality long-tail SEO surface — each sermon contributes 5 indexable Q&A entry points
        // and each topic page rolls up all sermons tagged with that theme.
        $static[] = ['path' => '/find-peace', 'priority' => '0.95', 'freq' => 'weekly', 'lastmod' => $today];

        \App\Models\PeaceSermon::query()
            ->whereIn('processing_status', ['published', 'pending_review'])
            ->whereNotNull('published_at')
            ->where('is_offsite', false)
            ->where('is_no_sermon', false)
            ->orderByDesc('sermon_date')
            ->get(['slug', 'updated_at'])
            ->each(function ($s) use (&$static) {
                $static[] = [
                    'path'     => '/find-peace/' . $s->slug,
                    'priority' => '0.90',
                    'freq'     => 'monthly',
                    'lastmod'  => $s->updated_at?->toDateString() ?? now()->toDateString(),
                ];
            });

        \App\Models\PeaceTopic::has('sermons')
            ->orderBy('name')
            ->get(['slug'])
            ->each(function ($t) use (&$static, $today) {
                $static[] = [
                    'path'     => '/find-peace/topic/' . $t->slug,
                    'priority' => '0.75',
                    'freq'     => 'weekly',
                    'lastmod'  => $today,
                ];
            });

        return $static;
    }
}
