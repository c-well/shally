<?php
namespace App\Console\Commands;

use App\Models\Bulletin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeOldBulletins extends Command
{
    protected $signature = 'bulletins:purge-old
                            {--days=14 : Keep bulletins newer than this many days past their service_date}
                            {--dry-run : Count what would be deleted, do not actually delete}';

    protected $description = 'Delete bulletins older than N days past their service_date (events purge N days past event_ends_at).';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $cutoff = now()->subDays($days)->toDateString();
        $dry    = $this->option('dry-run');

        // Sabbath (non-event) bulletins: service_date < cutoff
        // Event bulletins: event_ends_at < cutoff (the WHOLE series goes together)
        $query = Bulletin::where(function ($q) use ($cutoff) {
            $q->where(function ($qq) use ($cutoff) {
                $qq->where('kind', 'sabbath')
                   ->where('service_date', '<', $cutoff);
            })->orWhere(function ($qq) use ($cutoff) {
                $qq->where('kind', 'event_night')
                   ->whereNotNull('event_ends_at')
                   ->where('event_ends_at', '<', $cutoff);
            });
        });

        $count = $query->count();
        if ($count === 0) {
            $this->info('Nothing to purge — all bulletins within the ' . $days . '-day window.');
            return 0;
        }

        if ($dry) {
            $this->warn("DRY RUN: would delete {$count} bulletin(s).");
            $query->orderBy('service_date')->get()->each(function ($b) {
                $this->line(sprintf("  id=%d  %s  %s  %s",
                    $b->id,
                    $b->service_date->toDateString(),
                    $b->kind,
                    $b->kind === 'event_night' ? "({$b->event_name} N{$b->event_night_number})" : ''
                ));
            });
            return 0;
        }

        // FK cascade handles bulletin_lines + announcements automatically — no manual cleanup needed.
        DB::transaction(function () use ($query) {
            $query->delete();
        });

        $this->info("Purged {$count} bulletin(s) older than {$days} days.");
        return 0;
    }
}
