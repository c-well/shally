<?php
namespace App\Console\Commands;

use App\Models\AppSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Finds the newest *available* full service on the YouTube channel and caches
 * its video id in AppSetting('latest_service_video_id'). The landing page
 * embeds that exact id instead of the raw uploads playlist — so if the latest
 * upload is pulled (private/removed), this rolls back to the previous good one,
 * and the one before that, however many weeks are missing.
 *
 * Availability is settled by actually probing each candidate with yt-dlp
 * (the same authenticated wrapper the sermon pipeline uses); a probe that
 * returns valid metadata = the video is watchable. We prefer real services
 * (>= 10 min) over shorts/clips.
 */
class RefreshLatestVideoCommand extends Command
{
    protected $signature = 'sermons:refresh-latest {--probe=8 : How many recent uploads to consider}';
    protected $description = 'Cache the newest available full-service video id (failsafe for the home-page embed).';

    private const YTDLP = '/home/shalom/bin/ytdlp-auth';
    private const MIN_SERVICE_SECONDS = 600; // 10 min — filters out shorts/clips

    public function handle(): int
    {
        $channel = config('services.youtube.channel_id');
        if (! $channel) { $this->error('No YOUTUBE_CHANNEL_ID configured.'); return self::FAILURE; }

        // Full services are LIVESTREAMS, which live on the channel's /streams tab —
        // not /videos (that surfaces old popular uploads). The live-streams system
        // playlist is the channel id with its "UC" prefix swapped for "UULV".
        $suffix  = preg_replace('/^UC/', '', $channel);
        $listUrl = "https://www.youtube.com/playlist?list=UULV{$suffix}";
        $end = max(3, (int) $this->option('probe'));

        // 1. Recent uploads, newest first (flat = fast, ids only).
        $json = $this->yt(['--flat-playlist', '--playlist-end', (string) $end, '-J', $listUrl], 90);
        if (! $json) { $this->error('Could not list channel uploads.'); return self::FAILURE; }
        $data = json_decode($json, true);
        $entries = $data['entries'] ?? [];
        if (! $entries) { $this->warn('No uploads returned.'); return self::FAILURE; }

        // 2. Walk newest → oldest; pick the first that actually plays AND is a full service.
        $fallback = null; // first available video of ANY length, as a last resort
        foreach ($entries as $e) {
            $id = $e['id'] ?? null;
            if (! $id) continue;
            $meta = $this->probe($id);
            if (! $meta) { $this->line("  {$id}: unavailable — skipping"); continue; }
            $dur = (int) ($meta['duration'] ?? 0);
            $title = $meta['title'] ?? '';
            if ($fallback === null) $fallback = $id;
            if ($dur >= self::MIN_SERVICE_SECONDS) {
                $this->store($id, $title, $dur);
                $this->info("  ✓ latest service = {$id} ({$title}, " . round($dur / 60) . " min)");
                return self::SUCCESS;
            }
            $this->line("  {$id}: available but short (" . round($dur / 60, 1) . " min) — keeping as fallback");
        }

        if ($fallback) {
            $this->store($fallback, '(short)', 0);
            $this->info("  ✓ no long service found; using newest available = {$fallback}");
            return self::SUCCESS;
        }

        $this->error('No available videos found in the recent uploads.');
        return self::FAILURE;
    }

    /** Full metadata probe; null if the video can't be watched. */
    private function probe(string $id): ?array
    {
        $json = $this->yt(['--skip-download', '--no-warnings', '-J', "https://www.youtube.com/watch?v={$id}"], 60);
        if (! $json) return null;
        $m = json_decode($json, true);
        if (! is_array($m) || empty($m['title'])) return null;
        // Exclude things that are listed but not actually watchable.
        $avail = $m['availability'] ?? 'public';
        if (in_array($avail, ['private', 'needs_auth', 'premium_only', 'subscriber_only'], true)) return null;
        return $m;
    }

    private function store(string $id, string $title, int $dur): void
    {
        AppSetting::set('latest_service_video_id', $id);
        AppSetting::set('latest_service_video_checked_at', now()->toIso8601String());
        Log::info('latest service video refreshed', ['id' => $id, 'title' => $title, 'duration' => $dur]);
    }

    /** Run the yt-dlp wrapper, return stdout or null on failure. */
    private function yt(array $args, int $timeout): ?string
    {
        $cmd = escapeshellarg(self::YTDLP) . ' ' . implode(' ', array_map('escapeshellarg', $args)) . ' 2>/dev/null';
        $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (! is_resource($proc)) return null;
        stream_set_blocking($pipes[1], false);
        $out = ''; $start = time();
        while (true) {
            $status = proc_get_status($proc);
            $out .= stream_get_contents($pipes[1]);
            if (! $status['running']) break;
            if (time() - $start > $timeout) { proc_terminate($proc); break; }
            usleep(100000);
        }
        $out .= stream_get_contents($pipes[1]);
        foreach ($pipes as $p) { if (is_resource($p)) fclose($p); }
        proc_close($proc);
        return trim($out) ?: null;
    }
}
