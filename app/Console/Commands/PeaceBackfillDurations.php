<?php

namespace App\Console\Commands;

use App\Models\PeaceSermon;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Fills in audio_duration_seconds where the pipeline trimmed the file but
 * never wrote the length back.
 *
 * The audio itself is fine — each file matches its detected sermon boundaries
 * to the second. It is only the number that is missing, and the number is what
 * the player shows, so a good recording was reading 0:00 and looking broken.
 */
class PeaceBackfillDurations extends Command
{
    protected $signature = 'peace:backfill-durations {--all : Re-read every message, not only the ones showing zero}';

    protected $description = 'Read each message\'s audio length off the file and store it';

    public function handle(): int
    {
        $q = PeaceSermon::whereNotNull('audio_url');

        if (! $this->option('all')) {
            $q->where(fn ($w) => $w->whereNull('audio_duration_seconds')->orWhere('audio_duration_seconds', 0));
        }

        $fixed = $missing = 0;

        foreach ($q->orderBy('sermon_date')->get() as $s) {
            $path = public_path(ltrim((string) $s->audio_url, '/'));

            if (! is_file($path)) {
                $this->warn($s->slug.': audio file is not on disk');
                $missing++;

                continue;
            }

            $p = new Process(['ffprobe', '-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', $path]);
            $p->setTimeout(30);
            $p->run();

            $seconds = (int) round((float) trim($p->getOutput()));

            if (! $p->isSuccessful() || $seconds <= 0) {
                $this->warn($s->slug.': could not read a duration');
                $missing++;

                continue;
            }

            $s->update(['audio_duration_seconds' => $seconds]);
            $this->line(sprintf('%-26s %d:%02d', $s->slug, intdiv($seconds, 60), $seconds % 60));
            $fixed++;
        }

        $this->info("set {$fixed}".($missing ? ", could not read {$missing}" : ''));

        return self::SUCCESS;
    }
}
