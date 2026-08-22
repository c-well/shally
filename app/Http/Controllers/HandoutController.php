<?php

namespace App\Http\Controllers;

use App\Models\Handout;
use App\Models\HandoutVisit;
use App\Services\HandoutOgImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The public face of a handout — thechurchofpeace.org/h/{token}.
 *
 * Three things this route is careful about:
 *
 * 1. NO INDEX, EVER. The meta tag is not enough on its own: a crawler that
 *    finds the URL in a shared screenshot, a referrer log, or a chat preview
 *    can still list it. The X-Robots-Tag header below is the one that binds
 *    for the fetched response, robots.txt disallows /h/, and the handout is
 *    never written into sitemap.xml. Three layers because a registry link is
 *    a family's private business.
 *
 * 2. AN EXPIRED HANDOUT AND A NONEXISTENT ONE LOOK IDENTICAL — both 404. If
 *    expired returned a distinct page, the token would confirm "something was
 *    here", which is exactly the thing an expired handout is meant to stop
 *    saying.
 *
 * 3. VIEWS ARE COUNTED WITHOUT TRACKING ANYONE. See HandoutVisit.
 */
class HandoutController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $handout = Handout::where('token', $token)->first();

        if (! $handout || ! $handout->isLive()) {
            throw new NotFoundHttpException();
        }

        $this->recordVisit($request, $handout);

        $html = view('handouts.show', [
            'h'    => $handout,
            'body' => $this->renderBody($handout->body),
        ])->render();

        return response($html, 200, [
            // The header, not just the meta tag — see the class docblock.
            'X-Robots-Tag'  => 'noindex, nofollow, noarchive, nosnippet',
            'Referrer-Policy' => 'no-referrer',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * The share preview image. Public and uncounted — a scraper fetching this
     * is not a person reading the card, so it must not inflate the view stats.
     */
    public function og(string $token, HandoutOgImage $images): Response
    {
        $handout = Handout::where('token', $token)->first();

        if (! $handout || ! $handout->isLive()) {
            throw new NotFoundHttpException();
        }

        return response($images->render($handout), 200, [
            'Content-Type'  => 'image/png',
            'X-Robots-Tag'  => 'noindex, noimageindex',
            // Long max-age is safe: the filename is keyed on updated_at, so an
            // edit produces a different cache entry rather than a stale hit.
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Count the landing. The hash rotates daily and is salted with APP_KEY, so
     * the same person on the same day counts once and the same person next
     * week is a stranger — which is the correct resolution for "did the thing
     * we handed out actually get opened."
     */
    private function recordVisit(Request $request, Handout $handout): void
    {
        try {
            $hash = hash('sha256', implode('|', [
                config('app.key'),
                now()->toDateString(),
                $handout->id,
                $request->ip(),
                substr((string) $request->userAgent(), 0, 120),
            ]));

            $isNew = ! HandoutVisit::where('handout_id', $handout->id)
                ->where('visitor_hash', $hash)->exists();

            HandoutVisit::create([
                'handout_id'   => $handout->id,
                'visitor_hash' => $hash,
                // Host only — the full referring URL can itself carry private
                // context, and "came from Facebook" is all the clerk needs.
                'referrer'     => $this->referrerHost($request),
                'created_at'   => now(),
            ]);

            // Counter columns keep the admin list a single cheap query even
            // once a popular handout has thousands of visit rows.
            DB::table('handouts')->where('id', $handout->id)->update([
                'views'        => DB::raw('views + 1'),
                'uniques'      => DB::raw('uniques + ' . ($isNew ? 1 : 0)),
                'last_seen_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // A counting failure must never stop someone reading the card.
            report($e);
        }
    }

    private function referrerHost(Request $request): ?string
    {
        $ref = $request->headers->get('referer');
        if (! $ref) {
            return null;
        }
        $host = parse_url($ref, PHP_URL_HOST);

        return $host ? substr($host, 0, 120) : null;
    }

    /**
     * Bodies are written in the wizard's plain textarea. Markdown is allowed
     * but nobody has to know it exists — a clerk typing ordinary paragraphs
     * gets ordinary paragraphs. html_input is escaped: this content is typed
     * by staff, but a handout is a public URL and there is no reason for raw
     * HTML to survive the trip.
     */
    private function renderBody(?string $raw): string
    {
        if (! trim((string) $raw)) {
            return '';
        }

        return (string) (new CommonMarkConverter([
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]))->convert($raw);
    }
}
