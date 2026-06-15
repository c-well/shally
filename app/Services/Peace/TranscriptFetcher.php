<?php
namespace App\Services\Peace;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Fetches YouTube video metadata + auto-captions via yt-dlp.
 *
 * Returns a structured payload the rest of the Peace pipeline can consume.
 * No Claude call here — pure server-side data extraction.
 */
class TranscriptFetcher
{
    private const YT_DLP = '/usr/local/bin/yt-dlp';
    private const TIMEOUT_SECONDS = 180;

    /**
     * Fetch metadata for a YouTube video.
     *
     * @return array{
     *   id: string, title: string, channel: string, channel_id: string,
     *   duration: int, upload_date: string, was_live: bool, view_count: ?int
     * }|null
     */
    public function getMetadata(string $videoId): ?array
    {
        $url = "https://www.youtube.com/watch?v={$videoId}";
        $proc = new Process([
            self::YT_DLP, '--no-warnings', '--skip-download', '--dump-json', $url,
        ]);
        $proc->setTimeout(self::TIMEOUT_SECONDS);
        $proc->run();

        if (! $proc->isSuccessful()) {
            Log::warning('TranscriptFetcher::getMetadata failed', [
                'video_id' => $videoId,
                'stderr'   => $proc->getErrorOutput(),
            ]);
            return null;
        }

        $json = json_decode($proc->getOutput(), true);
        if (! $json) return null;

        return [
            'id'          => $json['id'] ?? $videoId,
            'title'       => $json['title'] ?? '',
            'channel'     => $json['channel'] ?? '',
            'channel_id'  => $json['channel_id'] ?? '',
            'duration'    => (int) ($json['duration'] ?? 0),
            'upload_date' => $json['upload_date'] ?? '',
            'was_live'    => (bool) ($json['was_live'] ?? false),
            'view_count'  => isset($json['view_count']) ? (int) $json['view_count'] : null,
        ];
    }

    /**
     * Fetch English auto-captions as an array of (start_seconds, text) tuples.
     *
     * @return array<array{start: int, text: string}>|null
     */
    public function getTranscript(string $videoId): ?array
    {
        $url = "https://www.youtube.com/watch?v={$videoId}";
        $tmpDir = storage_path('app/peace-cache');
        if (! is_dir($tmpDir)) mkdir($tmpDir, 0755, true);

        $outPath  = "{$tmpDir}/{$videoId}";
        $jsonPath = "{$outPath}.en.json3";

        // WHISPER_CLOBBER_GUARD (2026-06-14): if a Whisper fallback already wrote
        // this transcript (marked by a sidecar .whisper file), use it as-is and do
        // NOT re-run yt-dlp. These videos have no YouTube captions, so a fetch would
        // either no-op or (worst case) overwrite our paid Whisper transcript with
        // nothing. The marker makes the Whisper transcript authoritative.
        $whisperMarker = "{$outPath}.whisper";
        if (file_exists($whisperMarker) && file_exists($jsonPath)) {
            Log::info('TranscriptFetcher: using Whisper transcript (skipping yt-dlp)', ['video_id' => $videoId]);
        } else {
            $proc = new Process([
                self::YT_DLP, '--no-warnings', '--skip-download', '--no-overwrites',
                '--write-auto-subs', '--sub-lang', 'en', '--sub-format', 'json3',
                '-o', "{$outPath}.%(ext)s", $url,
            ]);
            $proc->setTimeout(self::TIMEOUT_SECONDS);
            $proc->run();
        }

        if (! file_exists($jsonPath)) {
            Log::warning('TranscriptFetcher::getTranscript no caption file', [
                'video_id' => $videoId, 'expected' => $jsonPath,
            ]);
            return null;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $events = $data['events'] ?? [];

        $lines = [];
        foreach ($events as $e) {
            if (! isset($e['segs'])) continue;
            $text = '';
            foreach ($e['segs'] as $s) $text .= $s['utf8'] ?? '';
            $text = trim($text);
            if ($text === '') continue;
            // EVENT_END_INCLUDED — keep float precision + add end (start + duration).
            // BoundaryDetector uses gap = (next.start - cur.end). Without end, gap overestimates.
            $startMs = $e['tStartMs'] ?? 0;
            $durMs   = $e['dDurationMs'] ?? 0;
            $lines[] = [
                'start' => $startMs / 1000.0,
                'end'   => ($startMs + $durMs) / 1000.0,
                'text'  => $text,
            ];
        }

        return $lines;
    }

    /**
     * Format transcript lines as a single timestamped string Claude can read.
     * "[hh:mm:ss] line text\n" per entry.
     */
    public function formatForPrompt(array $lines, int $startSec = 0, ?int $endSec = null): string
    {
        $out = '';
        foreach ($lines as $line) {
            if ($line['start'] < $startSec) continue;
            if ($endSec !== null && $line['start'] > $endSec) break;
            $t = $line['start'];
            $out .= sprintf("[%02d:%02d:%02d] %s\n", $t / 3600, ($t % 3600) / 60, $t % 60, $line['text']);
        }
        return $out;
    }
}
