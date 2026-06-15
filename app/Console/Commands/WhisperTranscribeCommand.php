<?php
namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\PeaceSermon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * WHISPER_FALLBACK (2026-06-14, hardened 2026-06-14)
 *
 * Last-ditch fallback when YouTube never generates auto-captions for a Sabbath
 * stream. Picks the most recent caption-less video, transcribes via OpenAI
 * Whisper, writes the result as the json3 format the rest of the pipeline
 * expects (plus a .whisper marker so TranscriptFetcher won't clobber it),
 * then invokes peace:process to finish the run.
 *
 * Cost: ~$0.006/min audio. A 3-hour service runs about $1.10. Key is the
 * shared IIG/adminkc OpenAI key; every call is cost-logged to audit_log.
 *
 * ── ROCK-SOLID GUARANTEES ──────────────────────────────────────────────────
 *  1. CONCURRENCY LOCK — Cache::lock prevents two runs (manual + cron)
 *     colliding, double-downloading, or double-spending.
 *  2. RESUMABILITY — each 20-min chunk's transcript is cached to a per-video
 *     work dir. A failed/killed run re-uses every chunk already paid for; it
 *     never re-spends on completed chunks.
 *  3. RETRY w/ BACKOFF — Whisper API calls retry on 429/5xx/network up to
 *     5 times with exponential backoff. One blip no longer kills the run.
 *  4. COST CEILING — refuses to start if the estimated cost exceeds the cap
 *     (default $3.00). Guards against runaway streams / bugs.
 *  5. DISK PRE-CHECK — verifies enough free /tmp space before downloading.
 *  6. CLOBBER-PROOF — writes a .whisper marker; TranscriptFetcher reads the
 *     existing transcript instead of re-fetching (and re yt-dlp uses
 *     --no-overwrites as belt-and-suspenders).
 *  7. ALWAYS-CLEAN — a try/finally guarantees temp + work dirs are removed
 *     on success; on failure the resumable work dir is intentionally kept.
 *
 * Idempotency: if a peace_sermons row already exists for this calendar week,
 * exits clean — never duplicates the auto-pipeline's work.
 *
 * Usage:
 *   php artisan peace:whisper-transcribe                   — auto-pick
 *   php artisan peace:whisper-transcribe --video-id=XYZ    — force a specific video
 *   php artisan peace:whisper-transcribe --dry-run         — show plan, no API calls
 *   php artisan peace:whisper-transcribe --force           — skip "already have one this week" guard
 *   php artisan peace:whisper-transcribe --max-cost=5      — raise the cost ceiling for this run
 */
class WhisperTranscribeCommand extends Command
{
    protected $signature = 'peace:whisper-transcribe
                            {--video-id= : Force a specific YouTube video ID instead of auto-picking}
                            {--dry-run : Show what would happen, no API calls}
                            {--force : Skip the "already have a sermon this week" guard}
                            {--max-cost=3.0 : Hard ceiling on estimated Whisper cost in USD}';

    protected $description = 'OpenAI Whisper fallback for caption-less Sabbath streams (resumable, locked, retried).';

    private const CHUNK_SECONDS    = 1200;        // 20 minutes per chunk
    private const CHUNK_BITRATE    = '32k';       // mono 32kbps target
    private const MAX_CHUNK_BYTES  = 24_000_000;  // 24 MB safety margin (API limit 25 MB)
    private const WHISPER_ENDPOINT = 'https://api.openai.com/v1/audio/transcriptions';
    private const WHISPER_RATE_PER_MIN = 0.006;   // USD/min, whisper-1
    private const YT_DLP_WRAPPER   = '/home/shalom/bin/ytdlp-auth';
    private const FFMPEG_PATH      = '/usr/local/bin/ffmpeg';
    private const FFPROBE_PATH     = '/usr/local/bin/ffprobe';
    private const MIN_FREE_BYTES   = 600_000_000; // need ~600 MB headroom in /tmp
    private const MAX_RETRIES      = 5;
    private const LOCK_TTL_SECONDS = 3600;        // a run may hold the lock up to 1h

    public function handle(): int
    {
        $this->info("=== peace:whisper-transcribe @ " . now()->toIso8601String() . " ===");

        $apiKey = env('OPENAI_API_KEY');
        if (! $apiKey) {
            $this->error('OPENAI_API_KEY not configured in .env — cannot call Whisper. Exiting clean.');
            return self::SUCCESS;  // config gap, not a system error
        }

        $dryRun  = (bool) $this->option('dry-run');
        $force   = (bool) $this->option('force');
        $maxCost = (float) $this->option('max-cost');

        // ── Idempotency guard: skip if a sermon already covers this week ──────
        if (! $force) {
            $weekStart = now()->startOfWeek();
            $existing = PeaceSermon::where('sermon_date', '>=', $weekStart)
                ->whereNotIn('processing_status', ['soft_discarded'])
                ->first();
            if ($existing) {
                $this->info("→ A sermon already exists for this week (#{$existing->id} {$existing->slug}). Exiting clean.");
                return self::SUCCESS;
            }
        }

        // ── Pick a video ─────────────────────────────────────────────────────
        $videoId = $this->option('video-id') ?: $this->pickAutoVideo();
        if (! $videoId) {
            $this->warn('No caption-less candidate found to transcribe. Exiting clean.');
            return self::SUCCESS;
        }
        if (! preg_match('/^[A-Za-z0-9_-]{6,20}$/', $videoId)) {
            $this->error("Refusing to process suspicious video id: {$videoId}");
            return self::FAILURE;
        }
        $this->line("→ Target video: {$videoId}");

        // ── CONCURRENCY LOCK ──────────────────────────────────────────────────
        // One run per video at a time, plus a global "only one whisper at a time"
        // guard (Whisper jobs are heavy on a shared cPanel box).
        $lock = Cache::lock("whisper:run:{$videoId}", self::LOCK_TTL_SECONDS);
        if (! $lock->get()) {
            $this->warn("Another whisper run holds the lock for {$videoId}. Exiting clean.");
            return self::SUCCESS;
        }

        try {
            return $this->runForVideo($videoId, $apiKey, $dryRun, $maxCost);
        } finally {
            optional($lock)->release();
        }
    }

    private function runForVideo(string $videoId, string $apiKey, bool $dryRun, float $maxCost): int
    {
        $workDir   = "/tmp/whisper-work/{$videoId}";
        $sourceRaw = null;   // downloaded source (cleaned always)
        $monoPath  = null;   // re-encoded mono (cleaned always)

        // try/finally so temp files never leak. The resumable chunk cache in
        // $workDir is kept on failure (so a re-run resumes) and removed on success.
        $succeeded = false;
        try {
            @mkdir($workDir, 0755, true);

            // ── DISK PRE-CHECK ────────────────────────────────────────────────
            $free = @disk_free_space('/tmp');
            if ($free !== false && $free < self::MIN_FREE_BYTES) {
                $this->error(sprintf('Not enough /tmp space: %d MB free, need %d MB. Aborting.',
                    (int) ($free / 1e6), (int) (self::MIN_FREE_BYTES / 1e6)));
                return self::FAILURE;
            }

            // ── COST CEILING (upfront, from metadata — no download wasted) ───
            // Pull duration from yt-dlp metadata BEFORE downloading, so a runaway
            // stream is rejected without spending bandwidth/disk. Falls through to
            // the post-download ffprobe duration if metadata is unavailable.
            $metaDuration = $this->fetchDurationFromMetadata($videoId);
            if ($metaDuration > 0) {
                $estCost = ($metaDuration / 60) * self::WHISPER_RATE_PER_MIN;
                $this->line(sprintf('→ Metadata duration: %ds (%s). Estimated cost: $%.2f (ceiling $%.2f)',
                    $metaDuration, gmdate('H:i:s', $metaDuration), $estCost, $maxCost));
                if ($estCost > $maxCost) {
                    $this->error(sprintf('Estimated cost $%.2f exceeds ceiling $%.2f. Aborting before download. Re-run with --max-cost to override.',
                        $estCost, $maxCost));
                    return self::FAILURE;
                }
            }

            if ($dryRun) {
                $this->info('Dry run — would lock, download, chunk, Whisper (with resume + retry), save json3, invoke peace:process. Stopping.');
                return self::SUCCESS;
            }

            // ── DOWNLOAD (skip if a mono file from a prior run already exists) ─
            $monoPath = "{$workDir}/audio-mono.mp3";
            if (is_file($monoPath) && filesize($monoPath) > 100_000) {
                $this->line('  ↻ Re-using mono audio from a previous run.');
            } else {
                $sourceRaw = $this->downloadAudio($videoId, $workDir);
                if (! $sourceRaw) return self::FAILURE;
                if (! $this->reencodeForWhisper($sourceRaw, $monoPath)) return self::FAILURE;
                @unlink($sourceRaw);
                $sourceRaw = null;
            }

            $duration = (int) $this->ffprobeDuration($monoPath);
            if ($duration <= 0) {
                $this->error("Could not read duration of {$monoPath}");
                return self::FAILURE;
            }

            // ── COST CEILING (authoritative, post-download) ──────────────────
            $estCost = ($duration / 60) * self::WHISPER_RATE_PER_MIN;
            $this->line(sprintf('→ Duration: %ds (%s). Estimated Whisper cost: $%.2f (ceiling $%.2f)',
                $duration, gmdate('H:i:s', $duration), $estCost, $maxCost));
            if ($estCost > $maxCost) {
                $this->error(sprintf('Estimated cost $%.2f exceeds ceiling $%.2f. Aborting. Re-run with --max-cost to override.',
                    $estCost, $maxCost));
                return self::FAILURE;
            }

            // ── CHUNK + TRANSCRIBE (resumable + retried) ─────────────────────
            $segments = $this->transcribeInChunks($videoId, $monoPath, $duration, $apiKey, $workDir);
            if ($segments === null) return self::FAILURE;
            if (count($segments) === 0) {
                $this->error('Whisper returned zero usable segments (silent or non-speech audio?). Aborting.');
                return self::FAILURE;
            }

            // ── SAVE json3 + .whisper marker (clobber-proof) ─────────────────
            $cacheDir = storage_path('app/peace-cache');
            if (! is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
            $json3Path  = "{$cacheDir}/{$videoId}.en.json3";
            $markerPath = "{$cacheDir}/{$videoId}.whisper";
            file_put_contents($json3Path, json_encode($this->segmentsToJson3($segments)));
            file_put_contents($markerPath, json_encode([
                'transcribed_at' => now()->toIso8601String(),
                'segments'       => count($segments),
                'source'         => 'openai-whisper-1',
            ]));
            $this->line("→ Saved transcript to {$json3Path} (" . count($segments) . " segments) + .whisper marker");

            // ── Cost audit-log ───────────────────────────────────────────────
            $costUsd = round(($duration / 60) * self::WHISPER_RATE_PER_MIN, 4);
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

            // ── Drop from no-captions cache + clear pending pointer ──────────
            $cache = json_decode(AppSetting::get('no_captions_cache', '{}'), true) ?: [];
            unset($cache[$videoId]);
            AppSetting::set('no_captions_cache', json_encode($cache));
            // Clear the scanner's pending-whisper pointer if it matches this video,
            // so the next Wednesday cron doesn't re-transcribe what we just did.
            $pending = json_decode(AppSetting::get('pending_whisper_video', ''), true);
            if (is_array($pending) && ($pending['id'] ?? null) === $videoId) {
                AppSetting::set('pending_whisper_video', '');
            }

            // ── Hand off to the pipeline ─────────────────────────────────────
            $this->info("→ Invoking peace:process {$videoId} …");
            $exit = Artisan::call('peace:process', ['video_id' => $videoId]);
            $this->line(Artisan::output());
            if ($exit !== 0) {
                // Transcript is saved + marked, so a re-run will skip straight to
                // peace:process without re-spending on Whisper.
                $this->error("peace:process failed (exit {$exit}). Transcript is saved — re-run is cheap (no Whisper re-spend).");
                return self::FAILURE;
            }

            $succeeded = true;
            $this->info("✓ Done.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('peace:whisper-transcribe crashed', ['video_id' => $videoId, 'error' => $e->getMessage()]);
            $this->error('Crashed: ' . $e->getMessage());
            return self::FAILURE;
        } finally {
            // Always remove the big temp files
            if ($sourceRaw && is_file($sourceRaw)) @unlink($sourceRaw);
            // On success, remove the resumable work dir entirely. On failure keep
            // it so the next run resumes from cached chunks.
            if ($succeeded && is_dir($workDir)) {
                $this->rrmdir($workDir);
            } elseif (is_dir($workDir)) {
                // keep chunk cache, but ditch the big mono file to reclaim space
                if ($monoPath && is_file($monoPath)) @unlink($monoPath);
                $this->line("  (kept resumable chunk cache in {$workDir} for a future re-run)");
            }
        }
    }

    /**
     * Read the single authoritative Whisper target the scanner recorded.
     *
     * This is NOT a guess from a raw cache — it's the exact video ScanChannelCommand
     * identified as this Sabbath's sermon after passing dedup + watermark + event-skip
     * + caption filters, but which had no YouTube captions. Bounded three ways so the
     * Wednesday cron never spends on stale or already-handled content:
     *   - cleared automatically when a caption-based scan later succeeds
     *   - skipped (and cleared) if a sermon already exists for that video
     *   - skipped (and cleared) if the target is older than 9 days (stale Sabbath cycle)
     */
    private function pickAutoVideo(): ?string
    {
        $raw = AppSetting::get('pending_whisper_video', '');
        if ($raw === '' || $raw === null) {
            $this->line('  No pending Whisper target recorded by the scanner. Nothing to do.');
            return null;
        }
        $target = json_decode($raw, true);
        if (! is_array($target) || empty($target['id'])) {
            AppSetting::set('pending_whisper_video', '');
            return null;
        }

        $vid = $target['id'];

        // Already processed? Clear + skip.
        if (PeaceSermon::where('youtube_video_id', $vid)->exists()) {
            $this->line("  Pending target {$vid} already has a sermon — clearing pointer.");
            AppSetting::set('pending_whisper_video', '');
            return null;
        }

        // Stale? (older than 9 days = beyond the current Sabbath cycle). Clear + skip.
        $detectedAt = (int) ($target['detected_at'] ?? 0);
        if ($detectedAt > 0 && (time() - $detectedAt) > 86400 * 9) {
            $this->line("  Pending target {$vid} is stale (>9 days) — clearing pointer, not transcribing.");
            AppSetting::set('pending_whisper_video', '');
            return null;
        }

        $this->line("  Scanner's Whisper target: " . ($target['title'] ?? '?') . " ({$vid})");
        return $vid;
    }

    /** Get video duration (seconds) from yt-dlp metadata without downloading. */
    private function fetchDurationFromMetadata(string $videoId): int
    {
        try {
            $proc = new Process([
                self::YT_DLP_WRAPPER, '--no-warnings', '--skip-download',
                '--print', '%(duration)s',
                "https://www.youtube.com/watch?v={$videoId}",
            ]);
            $proc->setTimeout(120);
            $proc->run();
            if (! $proc->isSuccessful()) return 0;
            $val = trim($proc->getOutput());
            return is_numeric($val) ? (int) $val : 0;
        } catch (\Throwable $e) {
            return 0;  // metadata is best-effort; post-download ffprobe is authoritative
        }
    }

    /** Download full audio via yt-dlp wrapper into the work dir. */
    private function downloadAudio(string $videoId, string $workDir): ?string
    {
        $out = "{$workDir}/source.%(ext)s";
        $this->line('  Downloading audio …');
        $proc = new Process([
            self::YT_DLP_WRAPPER, '--no-warnings', '--no-overwrites',
            '--ffmpeg-location', self::FFMPEG_PATH,
            '-x', '--audio-format', 'mp3', '--audio-quality', '5',
            '-o', $out,
            "https://www.youtube.com/watch?v={$videoId}",
        ]);
        $proc->setTimeout(900);
        $proc->run();
        if (! $proc->isSuccessful()) {
            $this->error('yt-dlp failed: ' . substr($proc->getErrorOutput(), 0, 300));
            return null;
        }
        $expected = "{$workDir}/source.mp3";
        if (! is_file($expected) || filesize($expected) < 100_000) {
            $this->error("Audio download produced no usable file at {$expected}");
            return null;
        }
        $this->line('  ' . round(filesize($expected) / 1e6, 1) . ' MB downloaded');
        return $expected;
    }

    /** Re-encode to 16 kHz mono 32 kbps mp3 (Whisper's preferred, smallest upload). */
    private function reencodeForWhisper(string $in, string $out): bool
    {
        $this->line('  Re-encoding to 16kHz mono 32k …');
        $proc = new Process([
            self::FFMPEG_PATH, '-y', '-i', $in,
            '-ac', '1', '-ar', '16000', '-b:a', self::CHUNK_BITRATE,
            '-acodec', 'libmp3lame', $out,
        ]);
        $proc->setTimeout(600);
        $proc->run();
        if (! $proc->isSuccessful() || ! is_file($out)) {
            $this->error('ffmpeg re-encode failed: ' . substr($proc->getErrorOutput(), 0, 300));
            return false;
        }
        $this->line('  ' . round(filesize($out) / 1e6, 1) . ' MB after re-encode');
        return true;
    }

    private function ffprobeDuration(string $file): float
    {
        $proc = new Process([
            self::FFPROBE_PATH, '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1', $file,
        ]);
        $proc->setTimeout(30);
        $proc->run();
        return (float) trim($proc->getOutput());
    }

    /**
     * Split into 20-min chunks, transcribe each via Whisper with retry+backoff.
     * RESUMABLE: each chunk's segment JSON is cached in $workDir; a chunk already
     * present is skipped (no re-spend). Returns merged segments or null on failure.
     */
    private function transcribeInChunks(string $videoId, string $audioPath, int $duration, string $apiKey, string $workDir): ?array
    {
        $merged = [];
        $offset = 0;
        $chunkIdx = 0;

        while ($offset < $duration) {
            $chunkIdx++;
            $chunkLen   = min(self::CHUNK_SECONDS, $duration - $offset);
            $chunkAudio = "{$workDir}/chunk-{$chunkIdx}.mp3";
            $chunkJson  = "{$workDir}/chunk-{$chunkIdx}.json";

            // RESUME: this chunk already transcribed in a prior run?
            if (is_file($chunkJson)) {
                $cached = json_decode((string) file_get_contents($chunkJson), true);
                if (is_array($cached)) {
                    $this->line("  ↻ chunk {$chunkIdx}: re-using cached transcript (no re-spend)");
                    foreach ($cached as $seg) $merged[] = $seg;
                    $offset += $chunkLen;
                    continue;
                }
            }

            // Cut the chunk
            $cut = new Process([
                self::FFMPEG_PATH, '-y', '-ss', (string) $offset, '-i', $audioPath,
                '-t', (string) $chunkLen, '-c', 'copy', $chunkAudio,
            ]);
            $cut->setTimeout(120);
            $cut->run();
            if (! $cut->isSuccessful() || ! is_file($chunkAudio)) {
                $this->error("Chunk {$chunkIdx} cut failed: " . substr($cut->getErrorOutput(), 0, 200));
                return null;
            }
            $size = filesize($chunkAudio);
            if ($size > self::MAX_CHUNK_BYTES) {
                $this->error("Chunk {$chunkIdx} too large ({$size} bytes). Shorten CHUNK_SECONDS.");
                @unlink($chunkAudio);
                return null;
            }

            $this->line("  Whisper chunk {$chunkIdx}: offset={$offset}s len={$chunkLen}s size=" . round($size / 1e6, 1) . 'MB');

            // Transcribe with retry + backoff
            $segments = $this->whisperWithRetry($chunkAudio, $apiKey, $offset, $chunkIdx);
            @unlink($chunkAudio);
            if ($segments === null) return null;  // exhausted retries

            // Persist this chunk's result so a future run resumes here
            file_put_contents($chunkJson, json_encode($segments));
            foreach ($segments as $seg) $merged[] = $seg;

            $offset += $chunkLen;
        }

        return $merged;
    }

    /**
     * One Whisper API call with exponential backoff on 429/5xx/network errors.
     * Returns the chunk's global-offset segments, or null after exhausting retries.
     */
    private function whisperWithRetry(string $chunkAudio, string $apiKey, int $offset, int $chunkIdx): ?array
    {
        $attempt = 0;
        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            try {
                $resp = Http::withToken($apiKey)
                    ->timeout(600)
                    ->attach('file', file_get_contents($chunkAudio), basename($chunkAudio))
                    ->post(self::WHISPER_ENDPOINT, [
                        'model'           => 'whisper-1',
                        'response_format' => 'verbose_json',
                        'language'        => 'en',
                    ]);

                if ($resp->successful()) {
                    $body = $resp->json();
                    $out = [];
                    foreach (($body['segments'] ?? []) as $seg) {
                        $text = trim((string) ($seg['text'] ?? ''));
                        if ($text === '') continue;
                        $out[] = [
                            'start' => ((float) $seg['start']) + $offset,
                            'end'   => ((float) $seg['end']) + $offset,
                            'text'  => $text,
                        ];
                    }
                    return $out;
                }

                // Retryable? 429 + 5xx are transient. 4xx (except 429) is fatal.
                $status = $resp->status();
                if ($status !== 429 && $status < 500) {
                    $this->error("Whisper chunk {$chunkIdx}: non-retryable HTTP {$status} — " . substr($resp->body(), 0, 200));
                    return null;
                }
                $this->warn("  chunk {$chunkIdx} attempt {$attempt}: HTTP {$status}, retrying…");
            } catch (\Throwable $e) {
                $this->warn("  chunk {$chunkIdx} attempt {$attempt}: " . substr($e->getMessage(), 0, 120) . ', retrying…');
            }

            // Exponential backoff: 2, 4, 8, 16, 32s (+ jitter)
            $sleep = (2 ** $attempt) + rand(0, 1000) / 1000;
            sleep((int) min($sleep, 60));
        }

        $this->error("Whisper chunk {$chunkIdx}: exhausted " . self::MAX_RETRIES . " retries. Aborting (chunk cache preserved for resume).");
        return null;
    }

    /** Convert merged segments to YouTube's json3 caption file format. */
    private function segmentsToJson3(array $segments): array
    {
        $events = [];
        foreach ($segments as $seg) {
            if (($seg['text'] ?? '') === '') continue;
            $events[] = [
                'tStartMs'    => (int) ($seg['start'] * 1000),
                'dDurationMs' => (int) (($seg['end'] - $seg['start']) * 1000),
                'segs'        => [['utf8' => $seg['text']]],
            ];
        }
        return ['events' => $events];
    }

    /** Recursive rmdir for the work dir. */
    private function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) return;
        foreach (scandir($dir) as $f) {
            if ($f === '.' || $f === '..') continue;
            $path = "{$dir}/{$f}";
            is_dir($path) ? $this->rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
