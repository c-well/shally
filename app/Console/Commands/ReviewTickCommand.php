<?php
namespace App\Console\Commands;

use App\Models\PeaceSermon;
use App\Services\Peace\PeaceReviewMailer;
use Illuminate\Console\Command;

/**
 * Hourly cron — moves sermons through the review-state machine on time.
 *
 *   php artisan peace:review-tick
 *
 * Two deadlines watched:
 *   1. pending_review past review_deadline (72h)  →  draft (page offline),
 *      send Email #2 once.
 *   2. soft_discarded past discarded_at + 48h  →  send Email #3 once
 *      (state stays soft_discarded forever until clerk confirms or restores).
 */
class ReviewTickCommand extends Command
{
    protected $signature = 'peace:review-tick {--dry-run : Report only, do not transition or send mail}';

    protected $description = 'Hourly state-machine tick for the Peace sermon review flow.';

    public function handle(PeaceReviewMailer $mailer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $now    = now();

        // --- Bucket 1: pending_review → draft after 72h silence ---
        $expired = PeaceSermon::where('processing_status', 'pending_review')
            ->whereNotNull('review_deadline')
            ->where('review_deadline', '<=', $now)
            ->get();

        foreach ($expired as $s) {
            $this->line("  72h-expired: #{$s->id} {$s->title}");
            if ($dryRun) continue;
            $s->processing_status = 'draft';
            $s->published_at      = null;  // page returns 404 from now on
            $s->save();
            try {
                $mailer->pulledOffline($s);
                $s->draft_email_sent_at = $now;
                $s->save();
                $this->info("    ✓ Email #2 sent.");
            } catch (\Throwable $e) {
                $this->error("    ✗ Email send failed: " . $e->getMessage());
            }
        }

        // --- Bucket 2: soft_discarded past 48h → send confirm-delete email once ---
        $needsConfirmEmail = PeaceSermon::where('processing_status', 'soft_discarded')
            ->whereNotNull('discarded_at')
            ->where('discarded_at', '<=', $now->copy()->subHours(48))
            ->whereNull('confirm_delete_email_sent_at')
            ->get();

        foreach ($needsConfirmEmail as $s) {
            $this->line("  48h-discarded: #{$s->id} {$s->title}");
            if ($dryRun) continue;
            try {
                $mailer->confirmDelete($s);
                $s->confirm_delete_email_sent_at = $now;
                $s->save();
                $this->info("    ✓ Email #3 sent.");
            } catch (\Throwable $e) {
                $this->error("    ✗ Email send failed: " . $e->getMessage());
            }
        }

        $this->info(sprintf(
            'Tick complete @ %s — %d pulled-offline, %d confirm-delete reminders.',
            $now->toIso8601String(),
            $expired->count(),
            $needsConfirmEmail->count()
        ));
        return self::SUCCESS;
    }
}
