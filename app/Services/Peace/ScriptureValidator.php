<?php
namespace App\Services\Peace;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Validates every scripture reference against bible-api.com (free public API).
 *
 * Why: protects ministry credibility against AI hallucination of biblical references.
 * If Claude returns "Hebrews 17:42" (Hebrews has 13 chapters) we drop it cleanly.
 *
 * Cascade rule: any Q&A whose answer mentions a scripture that failed validation
 * is also dropped (handled by the caller, see PeaceContentGenerator output handling
 * in ProcessSermonJob).
 */
class ScriptureValidator
{
    private const ENDPOINT = 'https://bible-api.com/';
    private const TIMEOUT_SECONDS = 10;
    private const TRANSLATION = 'kjv'; // bible-api.com defaults to kjv (lowercase). web/asv/etc. also available; ESV requires translation=esv

    /**
     * Validate a single reference. Returns enriched record on success, null on failure.
     *
     * @return array{
     *   book: string, chapter: int, verse_start: int, verse_end: ?int,
     *   reference_display: string, verse_text: string, translation: string, validated: true
     * }|null
     */
    public function validate(array $ref): ?array
    {
        $display = $ref['reference_display'] ?? '';
        if ($display === '') return null;

        try {
            $resp = Http::timeout(self::TIMEOUT_SECONDS)->get(self::ENDPOINT . urlencode($display) . '?translation=' . self::TRANSLATION);

            if ($resp->failed() || empty($resp->json('text'))) {
                Log::info('ScriptureValidator: invalid reference', [
                    'reference' => $display, 'http' => $resp->status(),
                ]);
                return null;
            }

            return [
                'book'              => $ref['book'] ?? '',
                'chapter'           => (int) ($ref['chapter'] ?? 0),
                'verse_start'       => (int) ($ref['verse_start'] ?? 0),
                'verse_end'         => isset($ref['verse_end']) && $ref['verse_end'] !== null ? (int) $ref['verse_end'] : null,
                'reference_display' => $display,
                'verse_text'        => trim((string) $resp->json('text')),
                'translation'       => strtoupper(self::TRANSLATION),
                'validated'         => true,
            ];
        } catch (\Throwable $e) {
            Log::warning('ScriptureValidator: HTTP error', ['ref' => $display, 'err' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate a batch — returns only successful records.
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
}
