<?php
namespace App\Console\Commands;

use App\Models\Bulletin;
use Illuminate\Console\Command;

class ClearStalePreviousSnapshots extends Command
{
    protected $signature = 'bulletins:clear-stale-previous-snapshots
                            {--hours=8 : Clear previous snapshots older than N hours (default 8)}
                            {--dry-run : Count but do not modify}';

    protected $description = 'Clear previous_published_snapshot + previous_published_at columns on bulletins whose previous version is older than the retention window. UI already hides "Prev PDF" past 8h — this just reclaims the DB space.';

    public function handle(): int
    {
        $hours  = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $query = Bulletin::whereNotNull('previous_published_at')
            ->where('previous_published_at', '<', $cutoff);

        $count = $query->count();
        if ($count === 0) {
            $this->info("Nothing to clear — no stale previous snapshots older than {$hours}h.");
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN: would clear previous snapshots on {$count} bulletin(s).");
            return 0;
        }

        // Route through BulletinPublisher service for atomic + audit-logged write
        $cleared = app(\App\Services\BulletinPublisher::class)->clearStaleSnapshots($hours);
        $this->info("Cleared {$cleared} stale previous snapshot(s) (> {$hours}h old).");
        return 0;
    }
}
