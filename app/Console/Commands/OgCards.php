<?php

namespace App\Console\Commands;

use App\Models\PeaceSermon;
use App\Services\OgCard;
use Illuminate\Console\Command;
use Throwable;

/**
 * Draws the share cards ahead of time so the first person to share a link
 * does not wait for one to be rendered, and so a scraper — which does not
 * retry — never hits a missing image.
 */
class OgCards extends Command
{
    protected $signature = 'og:cards {--force : Redraw cards that already exist}';

    protected $description = 'Draw the social share card for every published message';

    public function handle(): int
    {
        $made = $had = $failed = 0;

        PeaceSermon::whereNotNull('published_at')->orderBy('id')->chunk(50, function ($chunk) use (&$made, &$had, &$failed) {
            foreach ($chunk as $sermon) {
                try {
                    $path = public_path(OgCard::sermonRelPath($sermon));

                    if (is_file($path) && ! $this->option('force')) {
                        $had++;

                        continue;
                    }

                    if ($this->option('force')) {
                        @unlink($path);
                    }

                    OgCard::ensureSermon($sermon);
                    $made++;
                } catch (Throwable $e) {
                    $this->warn($sermon->slug.': '.$e->getMessage());
                    $failed++;
                }
            }
        });

        $this->info("drew {$made}, already had {$had}".($failed ? ", failed {$failed}" : ''));

        return $failed && ! $made ? self::FAILURE : self::SUCCESS;
    }
}
