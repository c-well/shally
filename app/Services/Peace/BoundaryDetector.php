<?php
namespace App\Services\Peace;

use Illuminate\Support\Facades\Log;

/**
 * Pass 1 — find sermon start/end within a livestream.
 *
 * NEW (2026-05-20): Deterministic gap-based detection.
 *
 *   The sermon is the LONGEST gap-free span in the stream.
 *   A "gap" = a stretch of caption silence > GAP_THRESHOLD seconds.
 *
 *   Why this works: every other segment of a typical service has natural
 *   breaks every few minutes — songs end, announcements pause between items,
 *   prayers have amen-breaks, transitions between speakers. Only the sermon
 *   has 30-60 minutes of sustained, uninterrupted speech.
 *
 *   Result: deterministic, no AI calls, runs in <50ms on 2,000 caption events.
 *
 * Input:  array of caption events shaped { start: float, end: float, text: string }
 *         (TranscriptFetcher already provides this).
 *
 * Output: { sermon_start_seconds, sermon_end_seconds, confidence, reason, candidates }
 *         where 'candidates' is the top 5 spans by length (for audit log debugging).
 *
 * Confidence is derived from the gap between #1 and #2 longest spans:
 *   - >2x  → 0.95   (clear winner)
 *   - >1.5x → 0.85
 *   - >1.2x → 0.70
 *   - else  → 0.55  (flag for review)
 */
class BoundaryDetector
{
    /** Minimum silence (in seconds) between caption events to count as a "gap". */
    private const GAP_THRESHOLD = 10.0;

    /** Minimum span length we'll consider a sermon candidate (seconds). */
    private const MIN_SERMON_LENGTH = 600;  // 10 minutes

    public function detect(array $captions, int $totalDurationSeconds): ?array
    {
        if (count($captions) < 10) {
            Log::warning('BoundaryDetector: too few caption events', ['count' => count($captions)]);
            return null;
        }

        // Build the list of gap-free spans by walking the captions and breaking on any gap >= threshold.
        $spans = [];
        $spanStart = $captions[0]['start'];
        $spanFirstText = $captions[0]['text'];

        for ($i = 0; $i < count($captions) - 1; $i++) {
            $cur = $captions[$i];
            $next = $captions[$i + 1];

            // Caption "end" may not be present — fall back to next.start if missing.
            $curEnd = $cur['end'] ?? $next['start'];
            $gap = $next['start'] - $curEnd;

            if ($gap >= self::GAP_THRESHOLD) {
                // Close current span
                $spans[] = [
                    'start' => $spanStart,
                    'end'   => $curEnd,
                    'length' => $curEnd - $spanStart,
                    'first_text' => $spanFirstText,
                ];
                // Start new span
                $spanStart = $next['start'];
                $spanFirstText = $next['text'];
            }
        }
        // Close the final span
        $lastCaption = end($captions);
        $lastEnd = $lastCaption['end'] ?? $lastCaption['start'];
        $spans[] = [
            'start' => $spanStart,
            'end'   => $lastEnd,
            'length' => $lastEnd - $spanStart,
            'first_text' => $spanFirstText,
        ];

        // Sort spans by length, descending
        usort($spans, fn($a, $b) => $b['length'] <=> $a['length']);

        // Filter to only spans >= minimum sermon length
        $qualifying = array_filter($spans, fn($s) => $s['length'] >= self::MIN_SERMON_LENGTH);

        if (empty($qualifying)) {
            Log::warning('BoundaryDetector: no gap-free span longer than ' . (self::MIN_SERMON_LENGTH / 60) . ' min', [
                'top_span_length' => $spans[0]['length'] ?? 0,
                'gap_threshold'   => self::GAP_THRESHOLD,
            ]);
            return null;
        }

        $sermon = reset($qualifying);
        $second = next($qualifying);

        // Confidence from ratio of #1 to #2
        $confidence = 0.55;
        $reasonSuffix = '';
        if ($second === false) {
            $confidence = 0.95;
            $reasonSuffix = ' (only one qualifying span — no competitors)';
        } else {
            $ratio = $sermon['length'] / $second['length'];
            if ($ratio >= 2.0) {
                $confidence = 0.95;
                $reasonSuffix = sprintf(' (sermon span %.1fx longer than #2 — clear winner)', $ratio);
            } elseif ($ratio >= 1.5) {
                $confidence = 0.85;
                $reasonSuffix = sprintf(' (sermon span %.1fx longer than #2)', $ratio);
            } elseif ($ratio >= 1.2) {
                $confidence = 0.70;
                $reasonSuffix = sprintf(' (sermon span only %.2fx longer than #2 — moderate)', $ratio);
            } else {
                $confidence = 0.55;
                $reasonSuffix = sprintf(' (sermon span only %.2fx longer than #2 — low confidence, flag for review)', $ratio);
            }
        }

        // Top 5 candidates for audit log
        $topCandidates = array_slice($spans, 0, 5);
        $candidatesStr = [];
        foreach ($topCandidates as $c) {
            $candidatesStr[] = sprintf(
                '%s–%s (%s): "%s"',
                gmdate('H:i:s', (int) $c['start']),
                gmdate('H:i:s', (int) $c['end']),
                gmdate('i:s', (int) $c['length']),
                substr($c['first_text'], 0, 40),
            );
        }

        $reason = sprintf(
            'Longest gap-free span: %s → %s (%s of continuous speech). Opens with: "%s".%s',
            gmdate('H:i:s', (int) $sermon['start']),
            gmdate('H:i:s', (int) $sermon['end']),
            gmdate('H:i:s', (int) $sermon['length']),
            substr($sermon['first_text'], 0, 80),
            $reasonSuffix,
        );

        return [
            'sermon_start_seconds' => (int) $sermon['start'],
            'sermon_end_seconds'   => (int) $sermon['end'],
            'confidence'           => $confidence,
            'reason'               => $reason,
            'source'               => 'gap_heuristic',
            'candidates'           => $candidatesStr,
        ];
    }
}
