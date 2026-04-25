<?php
namespace App\Console\Commands;

use App\Models\FeedbackTicket;
use Illuminate\Console\Command;

class PurgeOldTickets extends Command
{
    protected $signature = 'feedback:purge-old
                            {--closed-days=14 : Delete closed tickets older than N days after closed_at}
                            {--dry-run : Just report what would be deleted}';

    protected $description = 'Hard-delete feedback tickets that have been closed for over N days (default 14).';

    public function handle(): int
    {
        $closedDays = (int) $this->option('closed-days');
        $cutoff     = now()->subDays($closedDays);

        $query = FeedbackTicket::closed()->where('closed_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info("Nothing to purge — no closed tickets older than {$closedDays} days.");
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN: would delete {$count} ticket(s).");
            return 0;
        }

        $query->delete();
        $this->info("Purged {$count} feedback ticket(s) closed > {$closedDays} days ago.");
        return 0;
    }
}
