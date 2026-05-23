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

        // 1. Download full audio
        $url = "https://www.youtube.com/watch?v={$videoId}";
        $dl = new Process([
            self::YT_DLP, '--no-warnings',
            '--ffmpeg-location', '/usr/local/bin/ffmpeg', '-x', '--audio-format', 'mp3', '--audio-quality', '0',
            '-o', $fullPath, $url,
        ]);
        $dl->setTimeout(self::TIMEOUT_DOWNLOAD_SEC);
        $dl->run();
        if (! $dl->isSuccessful()) {
            Log::warning('AudioExtractor: yt-dlp download failed', [
                'video_id' => $videoId, 'stderr' => $dl->getErrorOutput(),
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
}
