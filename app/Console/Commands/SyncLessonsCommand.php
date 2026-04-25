<?php
namespace App\Console\Commands;

use App\Models\QuarterlyLesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Pull the current quarter's lesson content from the Adventech Sabbath School API
 * and cache it as JSON files under storage/app/lessons/{quarter}/{lesson}/{day}.json.
 *
 *   php artisan lesson:sync                  # current quarter
 *   php artisan lesson:sync --slug=2026-02   # specific quarter
 *   php artisan lesson:sync --force          # re-fetch even if files exist
 *
 * Designed to be cheap to re-run — skips files that already exist unless --force.
 * Adventech: https://sabbath-school.adventech.io/api/v2/en/quarterlies/{slug}/...
 */
class SyncLessonsCommand extends Command
{
    protected $signature = 'lesson:sync {--slug= : Adventech quarter slug like 2026-02} {--force : re-fetch existing days}';
    protected $description = 'Sync the current quarter\'s Sabbath School readings from Adventech into local cache.';

    private const BASE = 'https://sabbath-school.adventech.io/api/v2/en/quarterlies';

    public function handle(): int
    {
        $slug = $this->option('slug');
        if (! $slug) {
            $q = QuarterlyLesson::current();
            if (! $q) {
                $this->error('No QuarterlyLesson row found. Pass --slug=YYYY-NN or seed the table first.');
                return self::FAILURE;
            }
            $slug = $q->slug();
        }

        if (! preg_match('/^\d{4}-(0[1-4])$/', $slug)) {
            $this->error("Invalid slug \"$slug\" — must look like 2026-02.");
            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $base = storage_path("app/lessons/$slug");
        @mkdir($base, 0755, true);
        $this->info("Syncing $slug" . ($force ? ' (force)' : '') . " → $base");

        // 1. Quarter index — list of 13 lessons.
        $idx = $this->fetch("$slug/index.json");
        if (! $idx) return self::FAILURE;
        $this->writeJson("$base/quarter.json", $idx);
        $lessons = $idx['lessons'] ?? [];
        if (! $lessons) {
            $this->error('No lessons in quarter index — Adventech response shape changed?');
            return self::FAILURE;
        }
        $this->info(count($lessons) . ' lessons listed.');

        $totalDays = 0;
        $skipped = 0;
        foreach ($lessons as $L) {
            $lid = $L['id'] ?? null;
            if (! $lid) continue;
            @mkdir("$base/$lid", 0755, true);

            // 2. Lesson index — title + day list.
            $lpath = "$base/$lid/index.json";
            if (! $force && is_file($lpath)) {
                $lessonData = json_decode(file_get_contents($lpath), true);
            } else {
                $lessonData = $this->fetch("$slug/lessons/$lid/index.json");
                if (! $lessonData) { $this->warn("  · skipped lesson $lid (fetch failed)"); continue; }
                $this->writeJson($lpath, $lessonData);
            }

            $days = $lessonData['days'] ?? [];
            $this->line("  Lesson $lid · " . ($lessonData['lesson']['title'] ?? '?') . " · " . count($days) . ' days');

            foreach ($days as $D) {
                $did = $D['id'] ?? null;
                if (! $did) continue;
                $dpath = "$base/$lid/$did.json";
                if (! $force && is_file($dpath)) { $skipped++; continue; }

                $dayData = $this->fetch("$slug/lessons/$lid/days/$did/read/index.json");
                if (! $dayData) { $this->warn("    · day $did fetch failed"); continue; }
                $this->writeJson($dpath, $dayData);
                $totalDays++;
                usleep(120_000); // be polite — ~8 req/sec ceiling
            }
        }

        $this->info("Done. Wrote $totalDays day(s), skipped $skipped already-cached.");
        return self::SUCCESS;
    }

    /** Write JSON atomically (tmp + rename) so a half-fetched file never replaces a good one. */
    private function writeJson(string $path, array $data): void
    {
        $tmp = $path . '.tmp';
        file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        rename($tmp, $path);
    }

    /** GET a JSON path under the API base; logs and returns null on error. */
    private function fetch(string $path): ?array
    {
        $url = self::BASE . '/' . ltrim($path, '/');
        try {
            $res = Http::timeout(15)->retry(2, 400)->get($url);
        } catch (\Throwable $e) {
            $this->warn("  HTTP error: $url — " . $e->getMessage());
            return null;
        }
        if (! $res->ok()) {
            $this->warn("  HTTP {$res->status()}: $url");
            return null;
        }
        $j = $res->json();
        return is_array($j) ? $j : null;
    }
}
