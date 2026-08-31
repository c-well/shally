<?php
namespace App\Services\Peace;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Validates every scripture reference before it reaches a sermon page.
 *
 * Why: protects ministry credibility against AI hallucination of biblical
 * references. If Claude returns "Hebrews 17:42" (Hebrews has 13 chapters)
 * we drop it cleanly rather than publish it.
 *
 * LOCAL_FIRST (2026-08-31, Karlon): this used to call bible-api.com for every
 * reference. We already ship the full KJV at public/lib/kjv.json -- 31,102
 * verses, the same text the API returns -- so the network call was buying us
 * nothing but a dependency that could go down and take Sabbath publishing with
 * it. Local is now the source of truth; bible-api is kept only as a fallback
 * for reference spellings our own index does not recognise.
 *
 * Cascade rule: any Q&A whose answer mentions a scripture that failed
 * validation is also dropped (handled by the caller).
 */
class ScriptureValidator
{
    private const ENDPOINT = 'https://bible-api.com/';
    private const TIMEOUT_SECONDS = 10;
    private const TRANSLATION = 'kjv';
    private const LOCAL_BIBLE = 'lib/kjv.json';

    /** Lazily-built map of "Book C:V" => verse text. Held for the life of the process. */
    private static ?array $index = null;

    /**
     * Books the model may name differently to our index. Left side is what we
     * might be handed; right side is the spelling kjv.json actually uses.
     */
    private const BOOK_ALIASES = [
        'psalms'         => 'Psalm',
        'song of songs'  => 'Song of Solomon',
        'canticles'      => 'Song of Solomon',
        'ecclesiasties'  => 'Ecclesiastes',
        'revelations'    => 'Revelation',
        'i samuel'       => '1 Samuel',   'ii samuel'  => '2 Samuel',
        'i kings'        => '1 Kings',    'ii kings'   => '2 Kings',
        'i chronicles'   => '1 Chronicles','ii chronicles' => '2 Chronicles',
        'i corinthians'  => '1 Corinthians','ii corinthians' => '2 Corinthians',
        'i thessalonians'=> '1 Thessalonians','ii thessalonians' => '2 Thessalonians',
        'i timothy'      => '1 Timothy',  'ii timothy' => '2 Timothy',
        'i peter'        => '1 Peter',    'ii peter'   => '2 Peter',
        'i john'         => '1 John',     'ii john'    => '2 John', 'iii john' => '3 John',
    ];

    /**
     * Validate a single reference. Returns an enriched record, or null if the
     * reference does not exist in scripture.
     *
     * @return array{
     *   book: string, chapter: int, verse_start: int, verse_end: ?int,
     *   reference_display: string, verse_text: string, translation: string, validated: true
     * }|null
     */
    public function validate(array $ref): ?array
    {
        $display = trim((string) ($ref['reference_display'] ?? ''));
        if ($display === '') return null;

        // Rung 1 -- our own copy of the KJV. No network, no rate limit, no outage.
        $text = $this->lookupLocal($ref, $display);
        if ($text !== null) {
            return $this->record($ref, $display, $text);
        }

        // Rung 2 -- the reference did not resolve locally. That is usually a
        // genuine hallucination, but it can also be a book spelling we do not
        // know. Ask bible-api before discarding it, so an odd-but-real
        // reference still makes it through.
        $text = $this->lookupRemote($display);
        if ($text !== null) {
            Log::info('ScriptureValidator: resolved via bible-api after a local miss', ['reference' => $display]);
            return $this->record($ref, $display, $text);
        }

        Log::info('ScriptureValidator: invalid reference — dropped', ['reference' => $display]);
        return null;
    }

    /**
     * Validate a batch — returns only the references that survived.
     * @return array<array>
     */
    public function validateAll(array $refs): array
    {
        $valid = [];
        foreach ($refs as $ref) {
            $r = $this->validate($ref);
            if ($r !== null) $valid[] = $r;
        }
        return $valid;
    }

    /** Shape a successful validation into the record the caller expects. */
    private function record(array $ref, string $display, string $text): array
    {
        return [
            'book'              => $ref['book'] ?? '',
            'chapter'           => (int) ($ref['chapter'] ?? 0),
            'verse_start'       => (int) ($ref['verse_start'] ?? 0),
            'verse_end'         => isset($ref['verse_end']) && $ref['verse_end'] !== null ? (int) $ref['verse_end'] : null,
            'reference_display' => $display,
            'verse_text'        => trim($text),
            'translation'       => strtoupper(self::TRANSLATION),
            'validated'         => true,
        ];
    }

    /**
     * Look the reference up in our local KJV. Handles single verses and ranges
     * (a range is stitched from the individual verses, since the file stores
     * one verse per row). Returns null if any verse in the range is missing --
     * a partial range is worse than none.
     */
    private function lookupLocal(array $ref, string $display): ?string
    {
        $index = $this->index();
        if ($index === []) return null;

        [$book, $chapter, $start, $end] = $this->parse($ref, $display);
        if ($book === null || $chapter === null || $start === null) return null;

        $end = $end ?: $start;
        if ($end < $start) return null;

        $parts = [];
        for ($v = $start; $v <= $end; $v++) {
            $key = "{$book} {$chapter}:{$v}";
            if (! isset($index[$key])) return null;   // range is incomplete — reject the whole thing
            $parts[] = $index[$key];
        }

        return $parts === [] ? null : implode(' ', $parts);
    }

    /**
     * Work out book / chapter / verse range from the structured fields, falling
     * back to parsing the display string when those are absent or empty.
     *
     * @return array{0: ?string, 1: ?int, 2: ?int, 3: ?int}
     */
    private function parse(array $ref, string $display): array
    {
        $book    = $this->canonicalBook((string) ($ref['book'] ?? ''));
        $chapter = (int) ($ref['chapter'] ?? 0) ?: null;
        $start   = (int) ($ref['verse_start'] ?? 0) ?: null;
        $end     = isset($ref['verse_end']) && $ref['verse_end'] !== null ? (int) $ref['verse_end'] : null;

        if ($book !== null && $chapter !== null && $start !== null) {
            return [$book, $chapter, $start, $end];
        }

        // "1 Corinthians 13:4-7" / "John 3:16"
        if (preg_match('/^\s*(.+?)\s+(\d+):(\d+)(?:\s*[-–—]\s*(\d+))?\s*$/u', $display, $m)) {
            return [
                $this->canonicalBook($m[1]),
                (int) $m[2],
                (int) $m[3],
                isset($m[4]) && $m[4] !== '' ? (int) $m[4] : null,
            ];
        }

        return [null, null, null, null];
    }

    /** Map a book name onto the spelling kjv.json uses, or null if we cannot. */
    private function canonicalBook(string $book): ?string
    {
        $book = trim($book);
        if ($book === '') return null;

        $key = strtolower(preg_replace('/\s+/', ' ', $book));
        if (isset(self::BOOK_ALIASES[$key])) return self::BOOK_ALIASES[$key];

        // Title-case each word so "song of solomon" matches "Song of Solomon",
        // while leaving a leading numeral alone.
        return preg_replace_callback('/\b[a-z]/', fn ($m) => strtoupper($m[0]), $key);
    }

    /**
     * Build (once) the "Book C:V" => text map from public/lib/kjv.json.
     * ~31k entries, parsed a single time per process.
     */
    private function index(): array
    {
        if (self::$index !== null) return self::$index;

        $path = public_path(self::LOCAL_BIBLE);
        if (! is_readable($path)) {
            Log::warning('ScriptureValidator: local KJV missing — falling back to bible-api for everything', ['path' => $path]);
            return self::$index = [];
        }

        $rows = json_decode((string) file_get_contents($path), true);
        if (! is_array($rows)) {
            Log::warning('ScriptureValidator: local KJV could not be decoded', ['path' => $path]);
            return self::$index = [];
        }

        $map = [];
        foreach ($rows as $r) {
            // kjv.json carries the original line breaks; collapse them so a
            // stitched range reads as one clean paragraph on the page.
            if (isset($r['ref'], $r['text'])) {
                $map[$r['ref']] = trim(preg_replace('/\s+/u', ' ', (string) $r['text']));
            }
        }

        return self::$index = $map;
    }

    /** Last resort: ask bible-api.com. Returns the verse text, or null. */
    private function lookupRemote(string $display): ?string
    {
        try {
            $resp = Http::timeout(self::TIMEOUT_SECONDS)
                ->get(self::ENDPOINT . urlencode($display) . '?translation=' . self::TRANSLATION);

            if ($resp->failed() || empty($resp->json('text'))) return null;

            return (string) $resp->json('text');
        } catch (\Throwable $e) {
            Log::warning('ScriptureValidator: bible-api unreachable', ['ref' => $display, 'err' => $e->getMessage()]);
            return null;
        }
    }
}
