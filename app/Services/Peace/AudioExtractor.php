<?php
namespace App\Services\Peace;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Server-side audio extraction.
 *
 * Pipeline:
 *   1. yt-dlp downloads the full audio (m4a → mp3 conversion via ffmpeg)
 *   2. ffmpeg trims to (sermon_start_sec, sermon_end_sec)
 *   3. ffmpeg re-encodes at 96kbps for size (sermon-quality acceptable)
 *   4. Drops the full-length intermediate file
 *
 * Output goes to public_html/storage/peace/audio/ which is the public-served
 * directory (Laravel's storage:link makes it routable).
 */
class AudioExtractor
{
    private const YT_DLP = '/home/shalom/bin/ytdlp-auth';  // wrapper now cookie-less by default — see ytdlp-auth header
    private const FFMPEG = '/usr/local/bin/ffmpeg';
    private const TIMEOUT_DOWNLOAD_SEC = 600; // 10 min max for full-stream pull
    private const TIMEOUT_FFMPEG_SEC   = 120;
    private const TARGET_BITRATE       = '160k';

    public function extract(string $videoId, int $startSec, int $endSec): ?string
    {
        $audioDir = storage_path('app/public/peace/audio');
        if (! is_dir($audioDir)) mkdir($audioDir, 0755, true);

        $fullPath   = "{$audioDir}/{$videoId}-full.mp3";
        $sermonPath = "{$audioDir}/{$videoId}.mp3";

        // 1. Download full audio.
        //
        // SELF_REPAIR (2026-08-31): a stale yt-dlp is the single most common
        // reason this step dies. YouTube rotates the player clients yt-dlp
        // impersonates; when one is retired the extractor still reads metadata
        // and captions fine, then gets handed a media URL that answers 403.
        // That is exactly how the pipeline sat broken through August -- one
        // attempt, a log line, and a null return that nobody saw.
        //
        // So on a download failure that looks like extractor rot, upgrade
        // yt-dlp in place and try once more before giving up. A human only
        // hears about it if the retry fails too.
        $url = "https://www.youtube.com/watch?v={$videoId}";

        $dl = $this->runDownload($fullPath, $url);

        if (! $dl->isSuccessful() && $this->looksLikeStaleExtractor($dl->getErrorOutput())) {
            Log::warning('AudioExtractor: download failed on a stale-extractor signature — attempting yt-dlp self-update', [
                'video_id' => $videoId,
                'version'  => $this->ytDlpVersion(),
            ]);

            if ($this->upgradeYtDlp()) {
                @unlink($fullPath);
                $dl = $this->runDownload($fullPath, $url);

                if ($dl->isSuccessful()) {
                    Log::info('AudioExtractor: self-repair succeeded — yt-dlp upgraded and download retried clean', [
                        'video_id' => $videoId,
                        'version'  => $this->ytDlpVersion(),
                    ]);
                }
            }
        }

        if (! $dl->isSuccessful()) {
            Log::warning('AudioExtractor: yt-dlp download failed', [
                'video_id'  => $videoId,
                'stderr'    => $dl->getErrorOutput(),
                'version'   => $this->ytDlpVersion(),
                'repaired'  => false,
            ]);
            return null;
        }

        // 2. Measure mean volume of a representative slice — if quiet, auto-apply compressor.
        // Probe a 60-sec window starting 5 min into the sermon (past intro music/preamble).
        $probeStart = $startSec + 300;  // 5 min into the sermon proper
        $vd = new Process([
            self::FFMPEG, '-ss', (string) $probeStart, '-t', '60',
            '-i', $fullPath, '-af', 'volumedetect', '-f', 'null', '-',
        ]);
        $vd->setTimeout(60);
        $vd->run();
        $vdOut = $vd->getErrorOutput();
        $meanDb = null;
        if (preg_match('/mean_volume:\s*(-?[0-9.]+)\s*dB/', $vdOut, $m)) {
            $meanDb = (float) $m[1];
        }
        $useCompressor = $meanDb !== null && $meanDb < -28.0;
        Log::info('AudioExtractor: volume probe', [
            'video_id' => $videoId, 'mean_db' => $meanDb, 'compressor' => $useCompressor ? 'fairchild' : 'none',
        ]);

        // 3 + 4. Trim, optionally apply Fairchild Vari-Mu emulation, always add 3-sec fade-out, encode at 160kbps.
        $duration  = $endSec - $startSec;
        $fadeStart = max(0, $duration - 3);
        $fairchild = 'compand=attacks=0.3:decays=0.8:points=-80/-80|-60/-55|-40/-30|-20/-14|-6/-6|0/-3,treble=g=1:f=6000:width_type=q:width=0.7,acompressor=threshold=-2dB:ratio=10:attack=2:release=50:knee=2:makeup=1';
        $fadeFilt  = "afade=t=out:st={$fadeStart}:d=3";
        $af        = $useCompressor ? ($fairchild . ',' . $fadeFilt) : $fadeFilt;

        $trim = new Process([
            self::FFMPEG, '-y',
            '-ss', (string) $startSec,
            '-to', (string) $endSec,
            '-i', $fullPath,
            '-af', $af,
            '-c:a', 'libmp3lame',
            '-b:a', self::TARGET_BITRATE,
            $sermonPath,
        ]);
        $trim->setTimeout(self::TIMEOUT_FFMPEG_SEC);
        $trim->run();
        if (! $trim->isSuccessful()) {
            Log::warning('AudioExtractor: ffmpeg trim failed', [
                'video_id' => $videoId, 'stderr' => $trim->getErrorOutput(),
            ]);
            return null;
        }

        // 4. Drop the full-length intermediate
        @unlink($fullPath);

        // Return the public URL path the browser will hit
        return '/storage/peace/audio/' . $videoId . '.mp3';
    }

    public function durationOf(string $publicPath): ?int
    {
        $absolute = public_path(ltrim($publicPath, '/'));
        if (! file_exists($absolute)) return null;

        $proc = new Process([
            self::FFMPEG, '-i', $absolute, '-hide_banner',
        ]);
        $proc->setTimeout(30);
        $proc->run();
        $stderr = $proc->getErrorOutput();
        if (preg_match('/Duration:\s+(\d+):(\d+):(\d+)/', $stderr, $m)) {
            return ((int) $m[1]) * 3600 + ((int) $m[2]) * 60 + (int) $m[3];
        }
        return null;
    }

    /** Build + run the yt-dlp download. Split out so the self-repair path can re-run it. */
    private function runDownload(string $fullPath, string $url): Process
    {
        $dl = new Process([
            self::YT_DLP, '--no-warnings',
            '--ffmpeg-location', '/usr/local/bin/ffmpeg', '-x', '--audio-format', 'mp3', '--audio-quality', '0',
            '-o', $fullPath, $url,
        ]);
        $dl->setTimeout(self::TIMEOUT_DOWNLOAD_SEC);
        $dl->run();
        return $dl;
    }

    /**
     * Does this stderr look like yt-dlp has fallen behind YouTube rather than
     * something genuinely wrong with the video? 403 on the media URL is the
     * signature -- metadata resolved, the download then got refused. Also catch
     * yt-dlp's own staleness nag and the "no formats" case.
     *
     * Deliberately narrow: a private/removed/age-gated video should NOT trigger
     * an upgrade-and-retry, because no amount of upgrading fixes those.
     */
    private function looksLikeStaleExtractor(string $stderr): bool
    {
        foreach (['HTTP Error 403', 'Forbidden', 'nsig extraction failed', 'Some formats may be missing', 'unable to download video data'] as $needle) {
            if (stripos($stderr, $needle) !== false) return true;
        }
        return false;
    }

    /** Installed yt-dlp version, for the log line. Empty string if it cannot be read. */
    private function ytDlpVersion(): string
    {
        $v = new Process(['/usr/local/bin/yt-dlp', '--version']);
        $v->setTimeout(20);
        $v->run();
        return $v->isSuccessful() ? trim($v->getOutput()) : '';
    }

    /**
     * Upgrade yt-dlp in place via the pip that owns it. Returns true if the
     * version actually moved -- a no-op upgrade means the rot is something an
     * upgrade cannot fix, and the caller should stop rather than retry blindly.
     */
    private function upgradeYtDlp(): bool
    {
        $before = $this->ytDlpVersion();

        $up = new Process(['/usr/bin/python3.11', '-m', 'pip', 'install', '--upgrade', '--quiet', 'yt-dlp']);
        $up->setTimeout(300);
        $up->run();

        if (! $up->isSuccessful()) {
            Log::warning('AudioExtractor: yt-dlp upgrade command failed', [
                'stderr' => substr($up->getErrorOutput(), 0, 400),
            ]);
            return false;
        }

        $after = $this->ytDlpVersion();

        if ($after === '' || $after === $before) {
            Log::info('AudioExtractor: yt-dlp already current — the failure is not extractor rot', [
                'version' => $after ?: $before,
            ]);
            return false;
        }

        Log::info('AudioExtractor: yt-dlp upgraded', ['from' => $before, 'to' => $after]);
        return true;
    }
}
