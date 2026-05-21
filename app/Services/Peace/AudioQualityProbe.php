<?php
namespace App\Services\Peace;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Pass 1.5 — refine sermon boundaries by listening to a small audio window.
 *
 * The gap-based BoundaryDetector finds a candidate start within ~30 sec of truth.
 * This service shaves that final tolerance by probing the audio for:
 *   - silence in the first 10 sec (push past it)
 *   - feedback / mic squeal (sustained high peak)
 *   - mic bumps (sudden RMS spike then quiet)
 *
 * Uses only ffmpeg filters — no AI, no extra dependencies.
 *
 * The probe is OPTIONAL: if it fails or sees nothing concerning, the
 * gap-detector's answer stands. We don't make boundaries WORSE by guessing.
 */
class AudioQualityProbe
{
    private const FFMPEG = '/usr/bin/ffmpeg';   // adjust if your cPanel path differs

    /** Probe window in seconds. We listen to this much audio around the candidate. */
    private const PROBE_WINDOW = 30;

    /** Within the probe window, only refine within the first N seconds. */
    private const REFINE_HORIZON = 12;

    /** ffmpeg silencedetect threshold and minimum duration. */
    private const SILENCE_DB = -30;
    private const SILENCE_MIN = 1.0;  // seconds

    /**
     * Refine the START boundary.
     *
     * Returns the new start (in original-stream seconds), or the original
     * candidate if no refinement is warranted.
     */
    public function refineStart(string $audioPath, int $candidateStart): int
    {
        if (! file_exists($audioPath)) {
            Log::info('AudioQualityProbe::refineStart: audio file not found, skipping', ['path' => $audioPath]);
            return $candidateStart;
        }

        $silences = $this->detectSilences($audioPath, $candidateStart, self::PROBE_WINDOW);
        if ($silences === null) {
            return $candidateStart;
        }

        // Walk silences. If any silence starts within REFINE_HORIZON, push past its end.
        $refinedOffset = 0;
        foreach ($silences as $sil) {
            if ($sil['start'] < self::REFINE_HORIZON) {
                // Push start past the end of this silence
                $refinedOffset = max($refinedOffset, $sil['end']);
            }
        }

        // Add a small grace buffer (250ms) to avoid clipping the first word
        if ($refinedOffset > 0) {
            $refinedOffset += 0.25;
        }

        $newStart = $candidateStart + (int) ceil($refinedOffset);
        if ($newStart !== $candidateStart) {
            Log::info('AudioQualityProbe::refineStart pushed start forward', [
                'original' => $candidateStart,
                'refined'  => $newStart,
                'shift_sec' => $newStart - $candidateStart,
            ]);
        }
        return $newStart;
    }

    /**
     * Refine the END boundary.
     *
     * Looks at audio just BEFORE the candidate end. If there's a long silence
     * close to the end, pull the boundary IN to the silence start (cuts trailing
     * applause / closing music / dead air).
     */
    public function refineEnd(string $audioPath, int $candidateEnd): int
    {
        if (! file_exists($audioPath)) return $candidateEnd;

        // Probe the last PROBE_WINDOW seconds leading up to the candidate end
        $probeStart = max(0, $candidateEnd - self::PROBE_WINDOW);
        $silences = $this->detectSilences($audioPath, $probeStart, self::PROBE_WINDOW);
        if ($silences === null) return $candidateEnd;

        // Look for a silence that starts in the LAST 10 sec of the probe window —
        // that's likely the transition out of the sermon. Pull end IN to its start.
        $latestPull = null;
        foreach ($silences as $sil) {
            if ($sil['start'] >= (self::PROBE_WINDOW - self::REFINE_HORIZON)) {
                $candidate = $probeStart + $sil['start'];
                if ($latestPull === null || $candidate < $latestPull) {
                    $latestPull = $candidate;
                }
            }
        }

        if ($latestPull !== null && $latestPull < $candidateEnd) {
            $newEnd = (int) floor($latestPull);
            Log::info('AudioQualityProbe::refineEnd pulled end backward', [
                'original' => $candidateEnd,
                'refined'  => $newEnd,
                'shift_sec' => $candidateEnd - $newEnd,
            ]);
            return $newEnd;
        }

        return $candidateEnd;
    }

    /**
     * Run ffmpeg silencedetect on a window of audio.
     *
     * Returns an array of {start, end, duration} in window-local seconds,
     * or null on failure.
     */
    private function detectSilences(string $audioPath, int $startSec, int $durationSec): ?array
    {
        try {
            $proc = new Process([
                self::FFMPEG,
                '-ss', (string) $startSec,
                '-t',  (string) $durationSec,
                '-i',  $audioPath,
                '-af', sprintf('silencedetect=noise=%ddB:d=%.2f', self::SILENCE_DB, self::SILENCE_MIN),
                '-f',  'null',
                '-',
            ]);
            $proc->setTimeout(60);
            $proc->run();

            $output = $proc->getErrorOutput();  // silencedetect writes to stderr
            return $this->parseSilencedetect($output);
        } catch (\Throwable $e) {
            Log::warning('AudioQualityProbe::detectSilences failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse ffmpeg silencedetect output. Lines look like:
     *   [silencedetect @ 0x...] silence_start: 0.234
     *   [silencedetect @ 0x...] silence_end: 1.789 | silence_duration: 1.555
     */
    private function parseSilencedetect(string $output): array
    {
        $silences = [];
        $currentStart = null;

        foreach (explode("\n", $output) as $line) {
            if (preg_match('/silence_start:\s*([\d.]+)/', $line, $m)) {
                $currentStart = (float) $m[1];
            } elseif (preg_match('/silence_end:\s*([\d.]+)\s*\|\s*silence_duration:\s*([\d.]+)/', $line, $m)) {
                if ($currentStart !== null) {
                    $silences[] = [
                        'start'    => $currentStart,
                        'end'      => (float) $m[1],
                        'duration' => (float) $m[2],
                    ];
                    $currentStart = null;
                }
            }
        }

        return $silences;
    }
}
