<?php
namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PurgeOldAuditLogs extends Command
{
    protected $signature = 'audit:purge-old
                            {--days=40 : Delete entries older than N days (default 40)}
                            {--dry-run : Count but do not delete}';

    protected $description = 'Hard-delete audit log entries older than N days (default 40 — matches retention policy).';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $query = AuditLog::where('created_at', '<', $cutoff);
        $count = $query->count();

        if ($count === 0) {
            $this->info("Nothing to purge — no entries older than {$days} days.");
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN: would delete {$count} entries.");
            return 0;
        }

        $query->delete();
        $this->info("Purged {$count} audit log entries (> {$days} days old).");
        return 0;
    }
}
