<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Server-side ESV search.
 *
 * Why this exists (2026-08-31, Karlon): the Bible page used to fetch
 * /lib/esv.json straight into the browser and index it there. That worked, but
 * it also meant the complete ESV -- 7.3MB, copyright Crossway -- was sitting at
 * a public URL that anyone could pull in one request, with no attribution
 * attached. Crossway permit quoting within limits; they do not permit
 * redistributing the whole work.
 *
 * So the file now lives outside the web root and searches come through here,
 * which returns a capped page of verses rather than the corpus. KJV is public
 * domain and still loads client-side as before -- there is nothing to protect
 * there and the instant search is worth keeping.
 */
class BibleSearchController extends Controller
{
    private const CORPUS      = 'bible/esv.json';   // relative to storage/app
    private const MAX_RESULTS = 120;                // a generous page, not a corpus
    private const MIN_QUERY   = 2;

    public function esv(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < self::MIN_QUERY) {
            return response()->json(['ok' => true, 'total' => 0, 'results' => [], 'capped' => false]);
        }

        $rows = $this->corpus();
        if ($rows === []) {
            return response()->json(['ok' => false, 'err' => 'ESV is unavailable right now.'], 503);
        }

        $hits = $this->search($rows, $q);
        $total = count($hits);

        return response()->json([
            'ok'        => true,
            'total'     => $total,
            'capped'    => $total > self::MAX_RESULTS,
            'results'   => array_slice($hits, 0, self::MAX_RESULTS),
            'attribution' => 'Scripture quotations are from the ESV® Bible, copyright © 2001 by Crossway, a publishing ministry of Good News Publishers. Used by permission. All rights reserved.',
        ]);
    }

    /**
     * Rank matches. A reference typed straight in ("John 3:16") should win
     * outright; after that we prefer verses containing every word, then any.
     *
     * @return array<array{ref:string,text:string}>
     */
    private function search(array $rows, string $q): array
    {
        $needle = mb_strtolower($q);
        $terms  = array_values(array_filter(preg_split('/\s+/', $needle), fn ($t) => mb_strlen($t) >= 2));

        $exactRef = [];
        $allWords = [];

        foreach ($rows as $r) {
            $ref  = $r['ref']  ?? '';
            $text = $r['text'] ?? '';
            if ($ref === '' || $text === '') continue;

            $refLower  = mb_strtolower($ref);
            $textLower = mb_strtolower($text);

            if (str_starts_with($refLower, $needle)) {
                $exactRef[] = ['ref' => $ref, 'text' => $text];
                continue;
            }

            if ($terms === []) continue;

            $matched = 0;
            foreach ($terms as $t) {
                if (str_contains($textLower, $t) || str_contains($refLower, $t)) $matched++;
            }

            // Every term must appear. The client-side KJV index uses
            // combineWith AND, and results should mean the same thing whichever
            // tab you are on -- a partial-match bucket made "fear not" return
            // thousands of verses that merely contained "not".
            if ($matched === count($terms)) $allWords[] = ['ref' => $ref, 'text' => $text];
        }

        return array_merge($exactRef, $allWords);
    }

    /** Decode the ESV once per request. Held outside the web root on purpose. */
    private function corpus(): array
    {
        static $rows = null;
        if ($rows !== null) return $rows;

        $path = storage_path('app/' . self::CORPUS);
        if (! is_readable($path)) return $rows = [];

        $decoded = json_decode((string) file_get_contents($path), true);
        return $rows = is_array($decoded) ? $decoded : [];
    }
}
