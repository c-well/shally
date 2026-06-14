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
        'prayer & fasting',
        'worship through warfare',
        'watch night',
        'all night prayer',
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

        // WATERMARK_GUARDRAIL (2026-05-23, Karlons directive after Dr. Calvin Watkins incident):
        // The auto-scanner adds NEW videos only. It must never reach back into archives —
        // that's how a month-old North Bronx simulcast got auto-published as today's sermon.
        // The watermark stores the YYYYMMDD of the most recent upload we've successfully
        // considered. Anything dated ≤ watermark is treated as archive and ignored here.
        // Archive backfill is a separate admin-only manual flow.
        $watermark = \App\Models\AppSetting::get('peace_scanner_watermark', now('America/New_York')->format('Ymd'));
        $beforeWatermark = count($newOnly);
        $newOnly = array_filter($newOnly, fn($c) => ($c['upload_date'] ?? '') >= $watermark);
        $blockedByWatermark = $beforeWatermark - count($newOnly);
        if ($blockedByWatermark > 0) {
            $this->line("  After watermark filter ({$watermark}): " . count($newOnly) . " candidates ({$blockedByWatermark} archive videos blocked — use admin Archive Backfill to import manually).");
        } else {
            $this->line("  After watermark filter ({$watermark}): " . count($newOnly) . " candidates.");
        }

        $newOnly = array_values(array_filter($newOnly, fn($c) => !$this->shouldSkipTitle($c['title'])));
        $this->line("  After event-skip filter: " . count($newOnly) . " candidates.");

        if (empty($newOnly)) {
            $this->info('Nothing new to process. Exiting clean.');
            // Advance watermark to today so tomorrow's run starts fresh (only newer-than-today).
            \App\Models\AppSetting::set('peace_scanner_watermark', now('America/New_York')->format('Ymd'));
            return self::SUCCESS;
        }

        // CAPTION_PRECHECK — newest-first, but skip any candidate that doesn't have captions yet.
        // YouTube auto-captions can take 30-60 min after a long stream ends. If the current
        // top candidate has no captions, try the next one (might be older but processable).
        //
        // NO_CAPTIONS_MEMO (2026-06-14): Some videos NEVER get captions (the channel disabled
        // them for that upload, audio quality too poor, etc.). Without persistence the scanner
        // re-checks the same dead videos every fire — 5 wasted YouTube probes per scan, every
        // scan, forever. We remember "tried at <ts>" in interaction_events meta as a lightweight
        // cache: videos checked within the last 24h are skipped. After 24h, re-probe (captions
        // sometimes appear days late).
        $skipCacheKey = 'no_captions_cache';
        $skipCache = json_decode(\App\Models\AppSetting::get($skipCacheKey, '{}'), true) ?: [];
        $now = time();
        // Prune entries older than 7 days — give up on those, don't probe again
        $skipCache = array_filter($skipCache, fn($ts) => ($now - $ts) < 86400 * 7);

        $pick = null;
        $fetcher = app(\App\Services\Peace\TranscriptFetcher::class);
        foreach ($newOnly as $candidate) {
            $vid = $candidate['id'];
            $lastChecked = $skipCache[$vid] ?? 0;
            if ($lastChecked && ($now - $lastChecked) < 86400) {
                $this->line("  ↷ Skipping {$candidate['title']} ({$vid}) — no captions on last check " . round(($now - $lastChecked) / 3600) . "h ago");
                continue;
            }
            $this->line("  Checking captions on: {$candidate['title']} ({$vid}) …");
            $probe = $fetcher->getTranscript($vid);
            if (!empty($probe)) {
                $pick = $candidate;
                $this->line("  ✓ Captions available — " . count($probe) . " caption events");
                // Remove from skip cache if previously listed
                unset($skipCache[$vid]);
                break;
            }
            $this->line("  ✗ No captions yet — trying next candidate.");
            $skipCache[$vid] = $now;
        }

        // Persist the cache regardless of outcome
        \App\Models\AppSetting::set($skipCacheKey, json_encode($skipCache));

        if ($pick === null) {
            $this->warn('No candidates have captions yet. Try again in 30-60 min.');
            return self::SUCCESS;  // not a failure — captions just not ready
        }
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

        // SHORT_MESSAGE_GATE (2026-05-23) — Children's Day / brief homily handling.
        // The boundary detector picks the longest gap-free span of caption speech. On
        // services dominated by recitations, songs, or kids' performances, that span
        // can be tiny (a 4-minute pastoral blessing, a 3-minute closing prayer, etc.).
        // We do NOT want those auto-publishing to Find Peace — they aren't preachable
        // content for a 2am seeker. Hold them for Sunday-morning manual review.
        //
        // Thresholds:
        //   - detected sermon span < 6 minutes (360s)  → likely not a sermon
        //   - sermon-portion word count  < 800 words   → too thin for a Peace page
        // Either trip → status = 'short_message_review', no published_at, no SEO ping.
        $detectedSpan   = (int) (($sermon->sermon_end_seconds ?? 0) - ($sermon->sermon_start_seconds ?? 0));
        $sermonWordCount = $this->wordCountWithinSpan(
            (string) ($sermon->transcript_raw ?? ''),
            (int) ($sermon->sermon_start_seconds ?? 0),
            (int) ($sermon->sermon_end_seconds ?? 0),
        );
        $shortGated = ($detectedSpan > 0 && $detectedSpan < 360)
                   || ($sermonWordCount > 0 && $sermonWordCount < 800);

        $sermon->review_token                 = bin2hex(random_bytes(32));
        $sermon->review_deadline              = now()->addHours(72);
        $sermon->discarded_at                 = null;
        $sermon->draft_email_sent_at          = null;
        $sermon->confirm_delete_email_sent_at = null;

        // PRE_PUBLISH_VALIDATION_GATE (2026-05-27) — six-check audit before publish.
        // Catches the failure modes the short-message gate alone missed (Dr. Calvin
        // Watkins incident, missing audio_url, hallucinated heart-line, etc.).
        $validationFailed = [];
        if (! $shortGated) {
            $gate = app(\App\Services\Peace\PrePublishValidationGate::class);
            $result = $gate->run($sermon);
            if (! $result['ok']) {
                $validationFailed = $result['failed'];
            }
        }
        $heldByGate = $shortGated || !empty($validationFailed);

        if ($heldByGate) {
            $sermon->processing_status = $shortGated ? 'short_message_review' : 'needs_review';
            $sermon->published_at = null;
            if ($shortGated) {
                $this->warn(sprintf(
                    'SHORT MESSAGE detected (span=%ds, words=%d). Held — NOT published.',
                    $detectedSpan, $sermonWordCount
                ));
            } else {
                $this->warn('VALIDATION GATE held the sermon. Failed checks:');
                foreach ($validationFailed as $check => $reason) {
                    $this->warn("  ✗ {$check}: {$reason}");
                }
            }
        } else {
            $sermon->processing_status = 'pending_review';
            if (!$sermon->published_at) $sermon->published_at = now();
        }
        $sermon->save();

        $sermon->loadMissing('qaPairs');

        if ($heldByGate) {
            // Skip review email + SEO ping. Karlon will get a separate "needs your eyes"
            // heartbeat email below; nothing public-facing should fire.
            $this->info("Sermon held at /admin/peace/{$sermon->slug}/edit — review required.");
        } else {
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
        }

        // KARLON_HEARTBEAT — one-line "Sabbath scan worked" email so absence of email
        // = something needs attention. Only fires when an actual sermon was processed.
        try {
            if ($shortGated) {
                $subject = '[The Church of Peace] ⚠ Short message held — review needed: ' . $sermon->title;
                $body  = "Short-message gate tripped — sermon held for manual review.\n\n";
                $body .= "Possible reasons: Children's Day, brief homily, youth-led service,\n";
                $body .= "or detector picked a prayer/announcement segment instead of a sermon.\n\n";
                $body .= "  Title:   " . $sermon->title . "\n";
                $body .= "  ID:      #" . $sermon->id . "\n";
                $body .= "  Span:    " . gmdate('i:s', max(0, $detectedSpan)) . " (threshold: 06:00)\n";
                $body .= "  Words:   " . $sermonWordCount . " (threshold: 800)\n";
                $body .= "  Status:  short_message_review (NOT published)\n\n";
                $body .= "Review:  " . url('/admin/peace/' . $sermon->slug . '/edit') . "\n\n";
                $body .= "From the admin edit screen you can publish manually if the message is\n";
                $body .= "preachable, or discard it for the week.\n";
            } elseif (!empty($validationFailed)) {
                $subject = '[The Church of Peace] ⚠ Validation gate held sermon — review: ' . $sermon->title;
                $body  = "Pre-publish validation gate caught a problem. Sermon held for review.\n\n";
                $body .= "  Title:   " . $sermon->title . "\n";
                $body .= "  ID:      #" . $sermon->id . "\n";
                $body .= "  Status:  needs_review (NOT published)\n\n";
                $body .= "Failed checks:\n";
                foreach ($validationFailed as $check => $reason) {
                    $body .= "  ✗ {$check}: {$reason}\n";
                }
                $body .= "\nReview:  " . url('/admin/peace/' . $sermon->slug . '/edit') . "\n\n";
                $body .= "If the sermon looks right after review, publish manually.\n";
                $body .= "If the pipeline picked the wrong content (e.g. a guest speaker\n";
                $body .= "instead of the main message), discard it for the week.\n";
            } else {
                $subject = '[The Church of Peace] Sabbath sermon processed: ' . $sermon->title;
                $body  = "Sabbath sermon processed.\n\n";
                $body .= "  Title:   " . $sermon->title . "\n";
                $body .= "  ID:      #" . $sermon->id . "\n";
                $body .= "  Length:  " . gmdate('i:s', $sermon->sermon_end_seconds - $sermon->sermon_start_seconds) . "\n";
                $body .= "  URL:     " . url('/find-peace/' . $sermon->slug) . "\n\n";
                $body .= "Admin edit: " . url('/admin/peace/' . $sermon->slug . '/edit') . "\n";
            }
            \Illuminate\Support\Facades\Mail::raw($body, function ($m) use ($subject) {
                $m->to('contact@c-wellpics.com')->subject($subject);
            });
        } catch (\Throwable $e) {
            $this->warn('Heartbeat email failed (non-fatal): ' . $e->getMessage());
        }

        // WATERMARK_ADVANCE — successful run, advance to picked video's upload_date
        // (or today if no valid upload_date present). Tomorrow's scanner starts from here.
        //
        // WATERMARK_NA_GUARD (2026-06-14): yt-dlp returns the literal string "NA" when
        // the channel doesn't expose upload_date for a video. The null-coalesce above
        // doesn't catch that — the string "NA" gets stored and then string-compares
        // weirdly against valid YYYYMMDD watermarks, breaking future scans for weeks.
        // Require an 8-digit numeric value or fall back to today.
        $raw = $pick['upload_date'] ?? null;
        $advance = ($raw && preg_match('/^\d{8}$/', $raw)) ? $raw : now('America/New_York')->format('Ymd');
        \App\Models\AppSetting::set('peace_scanner_watermark', $advance);

        return self::SUCCESS;
    }

    /**
     * Count words spoken within [startSec, endSec] of a VTT/SRT-ish caption blob.
     * Robust against either timestamp format. If no cues fall in the window, falls
     * back to counting all words in the blob so we don't false-trip the gate.
     */
    private function wordCountWithinSpan(string $transcript, int $startSec, int $endSec): int
    {
        if ($transcript === '' || $endSec <= $startSec) return 0;

        // Match HH:MM:SS.mmm or MM:SS.mmm timestamps. Capture the line(s) until the next stamp.
        $pattern = '/(\d{1,2}:\d{2}(?::\d{2})?(?:[.,]\d{1,3})?)\s*-->\s*(\d{1,2}:\d{2}(?::\d{2})?(?:[.,]\d{1,3})?)/';
        if (!preg_match_all($pattern, $transcript, $m, PREG_OFFSET_CAPTURE)) {
            // No timestamps — count everything as a soft fallback.
            return str_word_count(strip_tags($transcript));
        }

        $words = 0;
        $count = count($m[0]);
        for ($i = 0; $i < $count; $i++) {
            $cueStart = $this->stampToSeconds($m[1][$i][0]);
            $cueEnd   = $this->stampToSeconds($m[2][$i][0]);
            if ($cueEnd < $startSec || $cueStart > $endSec) continue;

            // Text between this stamp and the next.
            $textStart = $m[0][$i][1] + strlen($m[0][$i][0]);
            $textEnd   = ($i + 1 < $count) ? $m[0][$i + 1][1] : strlen($transcript);
            $chunk = substr($transcript, $textStart, $textEnd - $textStart);
            $chunk = preg_replace('/<[^>]+>/', ' ', $chunk); // strip VTT tags like <c>...</c>
            $words += str_word_count((string) $chunk);
        }
        return $words;
    }

    /** Convert HH:MM:SS.mmm (or MM:SS.mmm) to integer seconds. */
    private function stampToSeconds(string $stamp): int
    {
        $stamp = str_replace(',', '.', $stamp);
        $parts = explode(':', $stamp);
        $parts = array_map('floatval', $parts);
        if (count($parts) === 3) return (int) ($parts[0] * 3600 + $parts[1] * 60 + $parts[2]);
        if (count($parts) === 2) return (int) ($parts[0] * 60 + $parts[1]);
        return (int) $parts[0];
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
