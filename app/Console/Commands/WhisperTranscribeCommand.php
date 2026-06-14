<?php
namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\PeaceSermon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * WHISPER_FALLBACK (2026-06-14)
 *
 * Runs Wednesdays at 6 PM ET as the last-ditch fallback when YouTube never
 * generates auto-captions for a Sabbath stream. Picks the most recent
 * caption-less video that hasn't been processed, transcribes via OpenAI
 * Whisper, saves the result as the json3 format the rest of the pipeline
 * expects, then invokes peace:process to finish the run.
 *
 * Cost: ~$0.006/min audio. A 3-hour service runs about $1.10.
 *
 * Idempotency: if a peace_sermons row already exists for this calendar week,
 * exits clean — never duplicates the auto-pipeline's work.
 *
 * Chunking: Whisper API caps uploads at 25 MB. We re-encode to 32 kbps mono
 * and split into 20-minute chunks (~4.6 MB each), preserving timing offsets
 * when stitching the response segments back together.
 *
 * Usage:
 *   php artisan peace:whisper-transcribe                   — auto-pick
 *   php artisan peace:whisper-transcribe --video-id=XYZ    — force a specific video
 *   php artisan peace:whisper-transcribe --dry-run         — show plan, no API calls
 *   php artisan peace:whisper-transcribe --force           — skip "already have one this week" guard
 */
class WhisperTranscribeCommand extends Command
{
    protected $signature = 'peace:whisper-transcribe
                            {--video-id= : Force a specific YouTube video ID instead of auto-picking}
                            {--dry-run : Show what would happen, no API calls}
                            {--force : Skip the "already have a sermon this week" guard}';

    protected $description = 'OpenAI Whisper fallback for caption-less Sabbath streams.';

    private const CHUNK_SECONDS    = 1200;        // 20 minutes per chunk
    private const CHUNK_BITRATE    = '32k';       // mono 32kbps target
    private const MAX_CHUNK_BYTES  = 24_000_000;  // 24 MB safety margin (API limit 25 MB)
    private const WHISPER_ENDPOINT = 'https://api.openai.com/v1/audio/transcriptions';
    private const YT_DLP_WRAPPER   = '/home/shalom/bin/ytdlp-auth';
    private const FFMPEG_PATH      = '/usr/local/bin/ffmpeg';
    private const FFPROBE_PATH     = '/usr/local/bin/ffprobe';

    public function handle(): int
    {
        $this->info("=== peace:whisper-transcribe @ " . now()->toIso8601String() . " ===");

        $apiKey = env('OPENAI_API_KEY');
        if (! $apiKey) {
            $this->error('OPENAI_API_KEY not configured in .env — cannot call Whisper. Exiting clean.');
            return self::SUCCESS;  // not a failure — config gap, not a system error
        }

        $force  = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        // ── Idempotency guard: skip if a sermon already covers this week
        if (! $force) {
            $weekStart = now()->startOfWeek();  // Monday 00:00
            $existing = PeaceSermon::where('sermon_date', '>=', $weekStart)
                ->whereNotIn('processing_status', ['soft_discarded'])
                ->first();
            if ($existing) {
                $this->info("→ A sermon already exists for this week (#{$existing->id} {$existing->slug}). Exiting clean.");
                return self::SUCCESS;
            }
        }

        // ── Pick a video
        $videoId = $this->option('video-id') ?: $this->pickAutoVideo();
        if (! $videoId) {
            $this->warn('No caption-less candidate found to transcribe. Exiting clean.');
            return self::SUCCESS;
        }
        $this->line("→ Target video: {$videoId}");

        if ($dryRun) {
            $this->info('Dry run — would download audio + chunk + Whisper + save json3 + invoke peace:process. Stopping.');
            return self::SUCCESS;
        }

        // ── Download full audio
        $audioRaw = $this->downloadAudio($videoId);
        if (! $audioRaw) return self::FAILURE;

        // ── Re-encode mono 32 kbps for transcription
        $audioMono = "/tmp/{$videoId}-whisper.mp3";
        if (! $this->reencodeForWhisper($audioRaw, $audioMono)) {
            @unlink($audioRaw);
            return self::FAILURE;
        }
        @unlink($audioRaw);

        $duration = (int) $this->ffprobeDuration($audioMono);
        if ($duration <= 0) {
            $this->error("Could not read duration of {$audioMono}");
            @unlink($audioMono);
            return self::FAILURE;
        }
        $this->line(sprintf("→ Duration: %ds (%s). Estimated Whisper cost: \$%.2f",
            $duration, gmdate('H:i:s', $duration), ($duration / 60) * 0.006));

        // ── Chunk + transcribe
        $allSegments = $this->transcribeInChunks($videoId, $audioMono, $duration, $apiKey);
        @unlink($audioMono);
        if ($allSegments === null) return self::FAILURE;

        // ── Save as json3 (format TranscriptFetcher expects)
        $json3Path = storage_path("app/peace-cache/{$videoId}.en.json3");
        if (! is_dir(dirname($json3Path))) mkdir(dirname($json3Path), 0755, true);
        file_put_contents($json3Path, json_encode($this->segmentsToJson3($allSegments)));
        $this->line("→ Saved transcript to {$json3Path} (" . count($allSegments) . " segments)");

        // ── Mark the cache entry so future scans don't retry this video
        $cache = json_decode(AppSetting::get('no_captions_cache', '{}'), true) ?: [];
        unset($cache[$videoId]);
        AppSetting::set('no_captions_cache', json_encode($cache));

        // ── Hand off to the existing pipeline
        $this->info("→ Invoking peace:process {$videoId} …");
        $exit = Artisan::call('peace:process', ['video_id' => $videoId]);
        $this->line(Artisan::output());
        if ($exit !== 0) {
            $this->error("peace:process failed with exit code {$exit}");
            return self::FAILURE;
        }

        // ── Audit-log with cost. Whisper bills $0.006/min, billed per second.
        $costUsd = round(($duration / 60) * 0.006, 4);
        \App\Models\AuditLog::record(
            event: 'peace_whisper_transcribed',
            description: sprintf('Whisper transcribed video %s (duration %ds, cost $%.4f)', $videoId, $duration, $costUsd),
            meta: [
                'video_id'         => $videoId,
                'duration_seconds' => $duration,
                'cost_usd'         => $costUsd,
                'model'            => 'whisper-1',
                'vendor'           => 'openai',
                'source'           => 'peace.whisper_fallback',
                'key_account'      => 'iig-adminkc',
            ],
        );

        $this->info("✓ Done.");
        return self::SUCCESS;
    }

    /**
     * Pick the most recent caption-less video that hasn't been processed.
     * Sources: the no_captions_cache (videos the scanner gave up on) — pick
     * the one most recently checked but still unprocessed.
     */
    private function pickAutoVideo(): ?string
    {
        $cache = json_decode(AppSetting::get('no_captions_cache', '{}'), true) ?: [];
        if (empty($cache)) return null;

        // Sort by timestamp DESCENDING so we pick the most recently-attempted
        // (which corresponds to the most recent Sabbath video).
        arsort($cache);
        $existingIds = PeaceSermon::pluck('youtube_video_id')->toArray();
        foreach (array_keys($cache) as $vid) {
            if (! in_array($vid, $existingIds, true)) {
                return $vid;
            }
        }
        return null;
    }

    /** Download full audio via yt-dlp wrapper. Returns the file path or null on failure. */
    private function downloadAudio(string $videoId): ?string
    {
        $out = "/tmp/{$videoId}-source.%(ext)s";
        $this->line("  Downloading audio …");
        $proc = new Process([
            self::YT_DLP_WRAPPER, '--no-warnings',
            '--ffmpeg-location', self::FFMPEG_PATH,
            '-x', '--audio-format', 'mp3', '--audio-quality', '5',
            '-o', $out,
            "https://www.youtube.com/watch?v={$videoId}",
        ]);
        $proc->setTimeout(900);  // 15 minutes max for long sermons
        $proc->run();
        if (! $proc->isSuccessful()) {
            $this->error('yt-dlp failed: ' . substr($proc->getErrorOutput(), 0, 300));
            return null;
        }
        $expected = str_replace('%(ext)s', 'mp3', $out);
        if (! file_exists($expected)) {
            $this->error("Expected audio at {$expected} but it does not exist");
            return null;
        }
        $this->line('  ' . round(filesize($expected) / 1_000_000, 1) . ' MB downloaded');
        return $expected;
    }

    /** Re-encode to 32 kbps mono mp3 for smaller upload to Whisper. */
    private function reencodeForWhisper(string $in, string $out): bool
    {
        $this->line('  Re-encoding to 32k mono …');
        $proc = new Process([
            self::FFMPEG_PATH, '-y', '-i', $in,
            '-ac', '1',                      // mono
            '-ar', '16000',                  // 16kHz sample (Whisper's expected)
            '-b:a', self::CHUNK_BITRATE,     // 32kbps
            '-acodec', 'libmp3lame',
            $out,
        ]);
        $proc->setTimeout(600);
        $proc->run();
        if (! $proc->isSuccessful() || ! file_exists($out)) {
            $this->error('ffmpeg re-encode failed: ' . substr($proc->getErrorOutput(), 0, 300));
            return false;
        }
        $this->line('  ' . round(filesize($out) / 1_000_000, 1) . ' MB after re-encode');
        return true;
    }

    private function ffprobeDuration(string $file): float
    {
        $proc = new Process([
            self::FFPROBE_PATH, '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1',
            $file,
        ]);
        $proc->setTimeout(20);
        $proc->run();
        return (float) trim($proc->getOutput());
    }

    /**
     * Split audio into 20-minute chunks, transcribe each via Whisper API,
     * merge segments with correct global timestamps. Returns merged segment
     * array or null on failure.
     */
    private function transcribeInChunks(string $videoId, string $audioPath, int $duration, string $apiKey): ?array
    {
        $merged = [];
        $offset = 0;
        $chunkIdx = 0;
        while ($offset < $duration) {
            $chunkIdx++;
            $chunkLen = min(self::CHUNK_SECONDS, $duration - $offset);
            $chunkPath = "/tmp/{$videoId}-chunk-{$chunkIdx}.mp3";

            // Cut chunk
            $cut = new Process([
                self::FFMPEG_PATH, '-y',
                '-ss', (string) $offset,
                '-i', $audioPath,
                '-t', (string) $chunkLen,
                '-c', 'copy',
                $chunkPath,
            ]);
            $cut->setTimeout(120);
            $cut->run();
            if (! $cut->isSuccessful() || ! file_exists($chunkPath)) {
                $this->error("Chunk {$chunkIdx} cut failed: " . substr($cut->getErrorOutput(), 0, 200));
                return null;
            }

            $size = filesize($chunkPath);
            if ($size > self::MAX_CHUNK_BYTES) {
                $this->error("Chunk {$chunkIdx} too large ({$size} bytes). Increase compression or shorten CHUNK_SECONDS.");
                @unlink($chunkPath);
                return null;
            }

            $this->line("  Whisper chunk {$chunkIdx}: offset={$offset}s len={$chunkLen}s size=" . round($size / 1_000_000, 1) . 'MB');

            // POST to Whisper
            try {
                $resp = Http::withToken($apiKey)
                    ->timeout(600)
                    ->attach('file', file_get_contents($chunkPath), basename($chunkPath))
                    ->post(self::WHISPER_ENDPOINT, [
                        'model'           => 'whisper-1',
                        'response_format' => 'verbose_json',
                        'language'        => 'en',
                    ]);
                @unlink($chunkPath);

                if (! $resp->successful()) {
                    $this->error("Whisper API failed (chunk {$chunkIdx}): HTTP " . $resp->status() . ' ' . substr($resp->body(), 0, 300));
                    return null;
                }

                $body = $resp->json();
                $segments = $body['segments'] ?? [];
                foreach ($segments as $seg) {
                    $merged[] = [
                        'start' => ((float) $seg['start']) + $offset,
                        'end'   => ((float) $seg['end']) + $offset,
                        'text'  => trim((string) $seg['text']),
                    ];
                }
            } catch (\Throwable $e) {
                @unlink($chunkPath);
                $this->error("Whisper call exception (chunk {$chunkIdx}): " . $e->getMessage());
                return null;
            }

            $offset += $chunkLen;
        }

        return $merged;
    }

    /** Convert merged segments to YouTube's json3 caption file format. */
    private function segmentsToJson3(array $segments): array
    {
        $events = [];
        foreach ($segments as $seg) {
            if ($seg['text'] === '') continue;
            $events[] = [
                'tStartMs'    => (int) ($seg['start'] * 1000),
                'dDurationMs' => (int) (($seg['end'] - $seg['start']) * 1000),
                'segs'        => [
                    ['utf8' => $seg['text']],
                ],
            ];
        }
        return ['events' => $events];
    }
}
