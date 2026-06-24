<?php
namespace App\Console\Commands;

use App\Models\ContactMessage;
use App\Services\SpamFilter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SPAM_AUTO_SWEEP (2026-06-18)
 *
 * Light daily pass that soft-deletes obvious spam from contact messages.
 *
 * SAFETY RULES (deliberately conservative — a wrongly-deleted message erodes
 * trust far more than a spam message left in the inbox):
 *   1. CONTACT MESSAGES ONLY. Prayer requests are never auto-swept — a real
 *      cry for help must never be auto-removed. Only the manual delete button
 *      touches prayers.
 *   2. SOFT DELETE ONLY. Swept messages are recoverable (deleted_at set,
 *      spam_swept_at stamped). A separate 30-day prune hard-deletes trash.
 *   3. HIGH-CONFIDENCE ONLY. Uses the same SpamFilter the contact form uses.
 *      If the filter wouldn't have blocked it at submit time, the sweep
 *      leaves it alone.
 *   4. SKIP READ MESSAGES. If a human already read it, don't sweep it — they
 *      may be keeping it intentionally.
 *   5. LOG EVERYTHING. Every sweep writes an audit_log row with what + why.
 *
 * Usage:
 *   php artisan messages:sweep-spam              — sweep
 *   php artisan messages:sweep-spam --dry-run    — report only, delete nothing
 *   php artisan messages:sweep-spam --prune-days=30  — also hard-delete trash older than N days
 */
class SweepSpamCommand extends Command
{
    protected $signature = 'messages:sweep-spam
                            {--dry-run : Report what would be swept, delete nothing}
                            {--prune-days=30 : Hard-delete soft-deleted spam older than this many days}';

    protected $description = 'Soft-delete obvious spam from contact messages (conservative, recoverable).';

    public function handle(SpamFilter $filter): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $pruneDays = (int) $this->option('prune-days');

        $this->info('=== messages:sweep-spam @ ' . now()->toIso8601String() . ($dryRun ? ' (DRY RUN)' : '') . ' ===');

        // RULE 1 + 4: contact messages only, skip already-read, skip already-trashed
        $candidates = ContactMessage::whereNull('read_at')->get();
        $swept = 0;
        $reasons = [];

        foreach ($candidates as $msg) {
            $reason = $filter->detect($msg->name, $msg->email, (string) $msg->message);
            if ($reason === null) continue;  // RULE 3: not spam by our filter → leave it

            $swept++;
            $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
            $this->line(sprintf('  %s #%d "%s" — %s',
                $dryRun ? 'WOULD SWEEP' : 'SWEPT',
                $msg->id,
                \Illuminate\Support\Str::limit($msg->name ?: $msg->email, 30),
                $reason
            ));

            if (! $dryRun) {
                $msg->spam_swept_at = now();
                $msg->save();
                $msg->delete();  // RULE 2: soft delete (recoverable)
            }
        }

        // Hard-prune old trash (only auto-swept spam, never human-deleted, and only
        // past the retention window — so a mistaken sweep has 30 days to be caught)
        $pruned = 0;
        if (! $dryRun && $pruneDays > 0) {
            $cutoff = now()->subDays($pruneDays);
            $pruned = ContactMessage::onlyTrashed()
                ->whereNotNull('spam_swept_at')
                ->where('spam_swept_at', '<', $cutoff)
                ->forceDelete();
        }

        $this->info(sprintf('Done. %s %d spam message%s%s.',
            $dryRun ? 'Would sweep' : 'Swept',
            $swept, $swept === 1 ? '' : 's',
            $pruned > 0 ? ", hard-pruned {$pruned} old trashed" : ''
        ));

        if ($swept > 0 && ! $dryRun) {
            \App\Models\AuditLog::record(
                event: 'spam_auto_swept',
                description: "Auto-swept {$swept} contact spam message(s); pruned {$pruned} old trashed.",
                meta: ['swept' => $swept, 'pruned' => $pruned, 'reasons' => $reasons],
            );
        }

        return self::SUCCESS;
    }
}
