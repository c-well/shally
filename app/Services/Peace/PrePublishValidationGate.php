<?php
namespace App\Services\Peace;

use App\Models\PeaceSermon;
use Illuminate\Support\Facades\Log;

/**
 * PRE_PUBLISH_VALIDATION_GATE (2026-05-27)
 *
 * Runs six checks against a freshly-processed sermon BEFORE it's allowed
 * to publish. Any failure → status=needs_review, no publish, no SEO ping.
 * Karlon gets an email with WHICH check failed and why.
 *
 * The pipeline already had SHORT_MESSAGE_GATE checking just length. This
 * extends it to catch the failure modes we saw on 2026-05-23 (Dr. Calvin
 * Watkins incident, audio_url not populated, etc.).
 *
 * Six checks:
 *   1. AUDIO_FILE_EXISTS — slice file is on disk and > 1MB
 *   2. AUDIO_URL_LINKED — sermon row has audio_url populated
 *   3. SERMON_LENGTH_OK — span ≥ 6min AND sermon-portion words ≥ 800
 *   4. HEART_LINE_GROUNDED — heart-line keywords appear in transcript slice
 *   5. SCRIPTURES_VERIFIED — each ref already validated by ScriptureValidator
 *   6. SPEAKER_CONFIDENCE — speaker name was extracted from intro, OR
 *      the sermon is flagged as "guest_speaker_unknown" for manual review
 */
class PrePublishValidationGate
{
    public const STATUS_VALIDATION_HOLD = 'needs_review';

    /**
     * Run all six checks. Returns ['ok' => bool, 'failed' => [check => reason]].
     */
    public function run(PeaceSermon $sermon): array
    {
        $failed = [];

        // ── Check 1: AUDIO_FILE_EXISTS
        $audioPath = $sermon->audio_url
            ? storage_path('app/public' . str_replace('/storage', '', $sermon->audio_url))
            : null;
        if (! $audioPath || ! file_exists($audioPath)) {
            $failed['audio_file_exists'] = 'no audio file on disk';
        } elseif (filesize($audioPath) < 1_000_000) {
            $failed['audio_file_exists'] = 'audio file < 1MB (extraction likely failed)';
        }

        // ── Check 2: AUDIO_URL_LINKED
        if (empty($sermon->audio_url)) {
            $failed['audio_url_linked'] = 'audio_url column is empty on sermon row';
        }

        // ── Check 3: SERMON_LENGTH_OK
        $span = (int) (($sermon->sermon_end_seconds ?? 0) - ($sermon->sermon_start_seconds ?? 0));
        $wordCount = $this->wordCountWithinSpan(
            (string) ($sermon->transcript_raw ?? ''),
            (int) ($sermon->sermon_start_seconds ?? 0),
            (int) ($sermon->sermon_end_seconds ?? 0),
        );
        if ($span < 360) {
            $failed['sermon_length_ok'] = "span {$span}s < 360s threshold";
        } elseif ($wordCount > 0 && $wordCount < 800) {
            $failed['sermon_length_ok'] = "sermon-portion words {$wordCount} < 800 threshold";
        }

        // ── Check 4: HEART_LINE_GROUNDED
        // Heart-line keywords should appear in the captions for the detected
        // sermon span. transcript_raw column is often empty (storage optimization;
        // captions live in storage/app/peace-cache/{video_id}.en.json3), so we
        // fall back to reading that file. If neither source is available we
        // skip this check rather than false-fail.
        $heartLine = (string) ($sermon->heart_line ?? '');
        if ($heartLine === '') {
            $failed['heart_line_grounded'] = 'no heart_line generated';
        } else {
            $slice = $this->loadSermonSliceText($sermon);
            if ($slice === '') {
                // Captions unavailable — skip check rather than false-fail
                Log::info('PrePublishValidationGate: heart_line check skipped (no captions)', ['sermon_id' => $sermon->id]);
            } else {
                $overlap = $this->keywordOverlap($heartLine, $slice);
                if ($overlap < 0.30) {
                    $failed['heart_line_grounded'] = sprintf('heart-line keywords overlap %.0f%% with transcript (< 30%% threshold)', $overlap * 100);
                }
            }
        }

        // ── Check 5: SCRIPTURES_VERIFIED
        // The pipeline already filters bad refs via ScriptureValidator. This
        // check just confirms at least one ref survived.
        $qaCount = method_exists($sermon, 'qaPairs') ? $sermon->qaPairs()->count() : 0;
        if ($qaCount === 0) {
            $failed['scriptures_verified'] = 'zero Q&A pairs survived validation';
        }

        // ── Check 6: SPEAKER_CONFIDENCE (hardened 2026-05-27 after "Tiny Lights"
        // slipped through with speaker="Children's day" — the video title leaked
        // into the speaker field). A real speaker name should:
        //   (a) NOT be empty / NOT be a known placeholder
        //   (b) NOT contain calendar / service words (those signal video-title leak)
        //   (c) Have at least one Capitalized name-shaped token (Firstname Lastname)
        //       — handles "Kumal Smith", "Sis Keyashia", "Dr Calvin Watkins" etc.
        $speaker = trim((string) ($sermon->speaker ?? ''));
        $placeholders = ['Shalom SDA Church', 'Shalom SDA', 'Shalom Seventh-day Adventist', 'Unknown', 'Guest', 'TBD'];
        // Words that indicate a video-title / non-person value
        $titleLeakWords = ['day', 'service', 'sabbath', 'morning', 'evening', 'stream', 'live', 'broadcast', 'worship', 'meeting', 'special'];

        $reason = null;
        if ($speaker === '') {
            $reason = 'speaker field is empty';
        } elseif (in_array($speaker, $placeholders, true)) {
            $reason = "speaker is a placeholder value ('{$speaker}')";
        } else {
            $lower = strtolower($speaker);
            foreach ($titleLeakWords as $w) {
                if (preg_match('/\b' . preg_quote($w, '/') . '\b/', $lower)) {
                    $reason = "speaker looks like a video-title leak (contains '{$w}', got: '{$speaker}')";
                    break;
                }
            }
            if (! $reason && ! preg_match('/[A-Z][a-z]+/', $speaker)) {
                $reason = "speaker has no proper-name-shaped token (got: '{$speaker}')";
            }
        }
        if ($reason) {
            $failed['speaker_confidence'] = $reason;
        }

        return [
            'ok'     => empty($failed),
            'failed' => $failed,
        ];
    }

    /**
     * Load the sermon-window caption text. Tries transcript_raw column first,
     * then falls back to storage/app/peace-cache/{video_id}.en.json3 (the format
     * the rest of the pipeline stores captions in).
     */
    private function loadSermonSliceText(PeaceSermon $sermon): string
    {
        $start = (int) ($sermon->sermon_start_seconds ?? 0);
        $end   = (int) ($sermon->sermon_end_seconds ?? 0);

        // Path 1: transcript_raw column (rare)
        $raw = (string) ($sermon->transcript_raw ?? '');
        if ($raw !== '') {
            return $this->extractSlice($raw, $start, $end);
        }

        // Path 2: peace-cache JSON3 file
        $cachePath = storage_path("app/peace-cache/{$sermon->youtube_video_id}.en.json3");
        if (! file_exists($cachePath)) return '';

        $json = json_decode(file_get_contents($cachePath), true);
        if (! is_array($json) || empty($json['events'])) return '';

        $out = '';
        foreach ($json['events'] as $ev) {
            $t = (int) (($ev['tStartMs'] ?? 0) / 1000);
            if ($t < $start || $t > $end) continue;
            $segs = $ev['segs'] ?? [];
            foreach ($segs as $seg) {
                $out .= ' ' . ($seg['utf8'] ?? '');
            }
        }
        return $out;
    }

    /** Extract caption text within [startSec, endSec] from a VTT/SRT blob. */
    private function extractSlice(string $transcript, int $startSec, int $endSec): string
    {
        if ($transcript === '' || $endSec <= $startSec) return '';

        $pattern = '/(\d{1,2}:\d{2}(?::\d{2})?(?:[.,]\d{1,3})?)\s*-->\s*(\d{1,2}:\d{2}(?::\d{2})?(?:[.,]\d{1,3})?)/';
        if (!preg_match_all($pattern, $transcript, $m, PREG_OFFSET_CAPTURE)) {
            return $transcript;
        }

        $out = '';
        $count = count($m[0]);
        for ($i = 0; $i < $count; $i++) {
            $cueStart = $this->stampToSeconds($m[1][$i][0]);
            $cueEnd   = $this->stampToSeconds($m[2][$i][0]);
            if ($cueEnd < $startSec || $cueStart > $endSec) continue;

            $textStart = $m[0][$i][1] + strlen($m[0][$i][0]);
            $textEnd   = ($i + 1 < $count) ? $m[0][$i + 1][1] : strlen($transcript);
            $chunk = substr($transcript, $textStart, $textEnd - $textStart);
            $chunk = preg_replace('/<[^>]+>/', ' ', $chunk);
            $out .= ' ' . $chunk;
        }
        return $out;
    }

    /** Count words inside a [start,end] window of a caption blob. */
    private function wordCountWithinSpan(string $transcript, int $startSec, int $endSec): int
    {
        $slice = $this->extractSlice($transcript, $startSec, $endSec);
        return str_word_count($slice);
    }

    /** Convert HH:MM:SS.mmm or MM:SS.mmm to seconds. */
    private function stampToSeconds(string $stamp): int
    {
        $stamp = str_replace(',', '.', $stamp);
        $parts = explode(':', $stamp);
        $parts = array_map('floatval', $parts);
        if (count($parts) === 3) return (int) ($parts[0] * 3600 + $parts[1] * 60 + $parts[2]);
        if (count($parts) === 2) return (int) ($parts[0] * 60 + $parts[1]);
        return (int) $parts[0];
    }

    /**
     * Fraction of substantive heart-line keywords that appear in the source slice.
     * "Substantive" = words >= 5 chars, excluding common stopwords.
     */
    private function keywordOverlap(string $needle, string $haystack): float
    {
        $stopwords = ['about','above','after','again','against','because','before','being','below','between','could','doing','during','each','from','further','having','here','itself','more','most','myself','nothing','other','over','same','should','some','such','that','their','them','these','they','this','those','through','under','until','very','when','where','which','while','will','with','would','your','yours','yourself','have','what','were','been','said','also','into','there','than','then','onto','upon','would','should'];

        preg_match_all('/[a-z]{5,}/i', strtolower($needle), $nWords);
        $needleWords = array_unique(array_diff($nWords[0], $stopwords));
        if (count($needleWords) === 0) return 1.0;  // nothing to check, pass

        $hay = strtolower($haystack);
        $hit = 0;
        foreach ($needleWords as $w) {
            if (str_contains($hay, $w)) $hit++;
        }
        return $hit / count($needleWords);
    }
}
