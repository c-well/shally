<?php
namespace App\Console\Commands;

use App\Models\AppSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Polls YouTube every 5 min to check if the channel is currently live.
 * Caches result to AppSetting so page renders never hit YouTube.
 *
 * Detection: hit /channel/{ID}/live and look for `"isLive":true` in the player
 * config blob. If found → store is_live=1 + extract live_video_id.
 *
 * Fail-safe: any failure (timeout, parse fail, non-200) → is_live=0.
 * Page renderer treats stale cache (>15 min) as not-live. Banner/CTA
 * never shows unless we have a fresh positive confirmation.
 */
class CheckLiveCommand extends Command
{
    protected $signature = 'peace:check-live';
    protected $description = 'Poll YouTube for live status; cache to AppSetting.';

    public function handle(): int
    {
        $channelId = config('services.youtube.channel_id');
        if (! $channelId) {
            $this->error('YOUTUBE_CHANNEL_ID not configured');
            return self::FAILURE;
        }

        $isLive = false;
        $videoId = null;

        try {
            $url = "https://www.youtube.com/channel/{$channelId}/live";
            $resp = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ShalomLiveCheck/1.0)'])
                ->get($url);

            if ($resp->successful()) {
                $html = $resp->body();
                // YouTube's player config blob has "isLive":true when actually live.
                // Also requires the page to NOT be the channel landing (which has no isLive at all).
                // "isLive":true also appears on SCHEDULED waiting rooms (caught 2026-07-04:
                // 1 AM false positive). A real broadcast has isLiveNow:true and no isUpcoming.
                if (preg_match('/"isLiveNow":\s*true/', $html) && ! preg_match('/"isUpcoming":\s*true/', $html)) {
                    $isLive = true;
                    if (preg_match('/"videoId":"([A-Za-z0-9_-]{11})"/', $html, $m)) {
                        $videoId = $m[1];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('peace:check-live failed', ['error' => $e->getMessage()]);
            // is_live stays false — fail-safe
        }

        AppSetting::set('is_live', $isLive ? '1' : '0');
        AppSetting::set('is_live_checked_at', now()->toIso8601String());
        if ($videoId) {
            AppSetting::set('live_video_id', $videoId);
        }

        $this->info(sprintf(
            'Live: %s%s',
            $isLive ? 'YES' : 'no',
            $videoId ? " (video={$videoId})" : ''
        ));
        return self::SUCCESS;
    }
}
