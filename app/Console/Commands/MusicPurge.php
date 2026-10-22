<?php
namespace App\Console\Commands;

use App\Models\MusicDay;
use Illuminate\Console\Command;

/**
 * Tracks are kept for 30 days after the music day, then the files go.
 * The rows stay, so André can still see who sang what.
 */
class MusicPurge extends Command
{
    protected $signature = 'music:purge {--days=30}';
    protected $description = 'Delete track files for music days that are long past';

    public function handle(): int
    {
        $cutoff = now('America/New_York')->subDays((int) $this->option('days'))->toDateString();

        foreach (MusicDay::whereNull('files_purged_at')->where('service_date', '<', $cutoff)->get() as $day) {
            foreach ($day->entries as $e) {
                if ($e->file_path && is_file($e->file_path)) @unlink($e->file_path);
                $e->update(['file_path' => null]);
            }
            @rmdir($day->dir());
            $day->update(['files_purged_at' => now()]);
            $this->line("purged {$day->label()}");
        }

        foreach (glob(storage_path('app/music/_zips/*.zip')) ?: [] as $z) {
            if (filemtime($z) < time() - 3600) @unlink($z);
        }
        return self::SUCCESS;
    }
}
