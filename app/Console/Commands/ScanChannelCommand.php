<?php
namespace App\Console\Commands;

use App\Models\PeaceSermon;
use App\Services\Peace\PeaceReviewMailer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Saturday 3 PM ET cron entry point.
 *
 *   php artisan peace:scan-channel
 *   php artisan peace:scan-channel --dry-run   # report only, no DB write, no API spend
 *
 * Flow:
 *   1. List recent streams from @gotoshalom via authenticated yt-dlp (~/bin/ytdlp-auth).
 *   2. Filter out video IDs already in peace_sermons.
 *   3. Filter out titles that look like memorials/events/prayer weeks/music days
 *      (heuristic skip list — admin can always force-process from /admin via peace:process).
 *   4. Take the most recent surviving candidate.
 *   5. Hand off to `peace:process VIDEO_ID` (which runs Pass 1 + Pass 2 + audio).
 *   6. Set processing_status = pending_review, generate review_token,
 *      set review_deadline = now() + 72h, fire Email #1.
 */
class ScanChannelCommand extends Command
{
    protected $signature = 'peace:scan-channel
                            {--dry-run : Print what would be processed, no DB/API spend}
                            {--limit=20 : How many recent streams to scan}';

    protected $description = 'Scan @gotoshalom for the latest unprocessed Sabbath sermon and run the Peace pipeline.';

    /** Skip titles matching any of these phrases (case-insensitive). */
    private const TITLE_SKIP_PATTERNS = [
        'in memory',
        'celebration of life',
        'music day',
        'choirs in praise',
        'pathfinder',
        'time for prayer',
        '10 days of prayer',
        'days of prayer',
        'unleashed',
        'homecoming',
        'camp meeting',
        'graduation',
        'wedding',
        'baby dedication',
    ];

    public function handle(PeaceReviewMailer $mailer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit  = (int)  $this->option('limit');

        $this->info("=== peace:scan-channel @ " . now()->toIso8601String() . " ===");
        if ($dryRun) $this->warn('DRY RUN — no DB writes, no API spend.');

        $candidates = $this->fetchChannelList($limit);
        if (empty($candidates)) {
            $this->error('No streams returned from yt-dlp. Check cookies or network.');
            return self::FAILURE;
        }
        $this->line("  Pulled " . count($candidates) . " recent streams from channel.");

        $existingIds = PeaceSermon::pluck('youtube_video_id')->all();
        $newOnly = array_filter($candidates, fn($c) => !in_array($c['id'], $existingIds, true));
        $this->line("  After dedup: " . count($newOnly) . " unprocessed.");

        $newOnly = array_values(array_filter($newOnly, fn($c) => !$this->shouldSkipTitle($c['title'])));
        $this->line("  After event-skip filter: " . count($newOnly) . " candidates.");

        if (empty($newOnly)) {
            $this->info('Nothing to process today. Exiting clean.');
            return self::SUCCESS;
        }

        // Pick newest (yt-dlp listings come in newest-first by default)
        $pick = $newOnly[0];
        $this->info("→ Picked: {$pick['title']} ({$pick['id']})");

        if ($dryRun) {
            $this->info('Dry run — stopping before pipeline. Would have invoked peace:process.');
            return self::SUCCESS;
        }

        // Hand off to the existing pipeline.
        $exitCode = Artisan::call('peace:process', ['video_id' => $pick['id']]);
        $this->line(Artisan::output());

        if ($exitCode !== 0) {
            $this->error("peace:process failed with exit code {$exitCode}. No review email sent.");
            return self::FAILURE;
        }

        // Mark it pending_review and queue the email.
        $sermon = PeaceSermon::where('youtube_video_id', $pick['id'])->firstOrFail();
        $sermon->processing_status            = 'pending_review';
        $sermon->review_token                 = bin2hex(random_bytes(32));
        $sermon->review_deadline              = now()->addHours(72);
        $sermon->discarded_at                 = null;
        $sermon->draft_email_sent_at          = null;
        $sermon->confirm_delete_email_sent_at = null;
        if (!$sermon->published_at) $sermon->published_at = now();
        $sermon->save();

        $sermon->loadMissing('qaPairs');
        try {
            $mailer->newReview($sermon);
            $this->info("✓ Email #1 sent to andre.marshall@gmail.com (CC contact@c-wellpics.com).");
        } catch (\Throwable $e) {
            $this->error('Email send failed: ' . $e->getMessage());
        }

        // SEO: ping Google + Bing so the new sermon URL gets indexed in minutes, not days.
        try {
            $pinger = app(\App\Services\Peace\SearchIndexPinger::class);
            $url    = route('find-peace.show', $sermon->slug);
            $results = $pinger->pingAll($url);
            $this->line('  IndexNow ping: ' . ($results['indexnow']['ok'] ? 'ok' : 'skipped (' . ($results['indexnow']['reason'] ?? $results['indexnow']['status'] ?? '?') . ')'));
            $this->line('  Google  ping: ' . ($results['google']['ok']   ? 'ok' : 'skipped (' . ($results['google']['reason']   ?? $results['google']['status']   ?? '?') . ')'));
        } catch (\Throwable $e) {
            $this->warn('Search-index ping failed (non-fatal): ' . $e->getMessage());
        }

        $this->info("Done. Sermon live at /find-peace/{$sermon->slug} pending review until " . $sermon->review_deadline->toDayDateTimeString() . '.');

        // KARLON_HEARTBEAT — one-line "Sabbath scan worked" email so absence of email
        // = something needs attention. Only fires when an actual sermon was processed.
        try {
            $body  = "Sabbath sermon processed.\n\n";
            $body .= "  Title:   " . $sermon->title . "\n";
            $body .= "  ID:      #" . $sermon->id . "\n";
            $body .= "  Length:  " . gmdate('i:s', $sermon->sermon_end_seconds - $sermon->sermon_start_seconds) . "\n";
            $body .= "  URL:     " . url('/find-peace/' . $sermon->slug) . "\n\n";
            $body .= "Admin edit: " . url('/admin/peace/' . $sermon->slug . '/edit') . "\n";
            \Illuminate\Support\Facades\Mail::raw($body, function ($m) use ($sermon) {
                $m->to('contact@c-wellpics.com')
                  ->subject('[Shalom] Sabbath sermon processed: ' . $sermon->title);
            });
        } catch (\Throwable $e) {
            $this->warn('Heartbeat email failed (non-fatal): ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    /** Fetch recent streams from the channel via authenticated yt-dlp wrapper. */
    private function fetchChannelList(int $limit): array
    {
        $cmd = sprintf(
            '%s --flat-playlist --no-warnings --playlist-end %d --print %s %s 2>&1',
            escapeshellarg(getenv('HOME') . '/bin/ytdlp-auth'),
            $limit,
            escapeshellarg('%(id)s|%(upload_date)s|%(duration_string)s|%(title)s'),
            escapeshellarg('https://www.youtube.com/@gotoshalom/streams'),
        );
        exec($cmd, $lines, $exitCode);
        if ($exitCode !== 0) {
            $this->error('yt-dlp exit ' . $exitCode . ': ' . implode("\n", array_slice($lines, -5)));
            return [];
        }
        $out = [];
        foreach ($lines as $line) {
            $parts = explode('|', $line, 4);
            if (count($parts) < 4) continue;
            $id = trim($parts[0]);
            if (!preg_match('/^[A-Za-z0-9_-]{8,15}$/', $id)) continue;
            $out[] = [
                'id'           => $id,
                'upload_date'  => trim($parts[1]),
                'duration'     => trim($parts[2]),
                'title'        => trim($parts[3]),
            ];
        }
        return $out;
    }

    private function shouldSkipTitle(string $title): bool
    {
        $haystack = mb_strtolower($title);
        foreach (self::TITLE_SKIP_PATTERNS as $pat) {
            if (str_contains($haystack, $pat)) return true;
        }
        return false;
    }
}
