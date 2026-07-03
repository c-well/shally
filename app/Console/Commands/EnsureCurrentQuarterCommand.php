<?php
namespace App\Console\Commands;

use App\Models\QuarterlyLesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Keeps /lesson current across quarter boundaries — nobody should ever have to
 * remember to add the new quarterly (Q3 2026 was missed for a week; never again).
 *
 * Looks ahead: if no quarter row covers NEXT Sabbath, derives the next
 * year/quarter from the latest row, pulls the real theme + dates from Adventech,
 * creates the row, syncs all readings into the local cache, and emails the
 * super-admins what it did. Safe to run daily — exits quietly when covered.
 */
class EnsureCurrentQuarterCommand extends Command
{
    protected $signature = 'lesson:ensure-current {--lookahead=7 : days ahead that must be covered}';
    protected $description = 'Auto-create + sync the next Sabbath School quarter from Adventech when the current one is ending.';

    public function handle(): int
    {
        $horizon = now('America/New_York')->addDays((int) $this->option('lookahead'))->toDateString();

        $covered = QuarterlyLesson::where('starts_on', '<=', $horizon)
            ->where('ends_on', '>=', $horizon)
            ->exists();
        if ($covered) {
            $this->info("Covered through {$horizon} — nothing to do.");
            return self::SUCCESS;
        }

        $latest = QuarterlyLesson::orderByDesc('starts_on')->first();
        if (! $latest) {
            $this->error('No quarters in the table at all — add one at /admin/lessons first.');
            return self::FAILURE;
        }

        $year    = $latest->quarter === 4 ? $latest->year + 1 : $latest->year;
        $quarter = $latest->quarter === 4 ? 1 : $latest->quarter + 1;
        $slug    = sprintf('%04d-%02d', $year, $quarter);

        $this->info("Gap after {$latest->quarterLabel()} — fetching {$slug} from Adventech…");
        $resp = Http::timeout(30)->get("https://sabbath-school.adventech.io/api/v2/en/quarterlies/{$slug}/index.json");
        if (! $resp->ok()) {
            $this->error("Adventech has no {$slug} yet (HTTP {$resp->status()}). Will retry on next run.");
            return self::FAILURE;
        }

        $q = $resp->json('quarterly') ?? [];
        $theme = trim((string) ($q['title'] ?? ''));
        $start = $this->date($q['start_date'] ?? null);
        $end   = $this->date($q['end_date'] ?? null);
        if ($theme === '' || ! $start || ! $end) {
            $this->error("Adventech {$slug} payload incomplete — not creating.");
            return self::FAILURE;
        }

        // fustero publishes the print PDF on a stable pattern; only attach if it exists.
        $pdfUrl = sprintf('https://www.fustero.es/en_%dt%d.pdf', $year, $quarter);
        try {
            if (! Http::timeout(15)->head($pdfUrl)->ok()) $pdfUrl = null;
        } catch (\Throwable) {
            $pdfUrl = null;
        }

        $row = QuarterlyLesson::updateOrCreate(
            ['year' => $year, 'quarter' => $quarter],
            ['theme' => $theme, 'starts_on' => $start, 'ends_on' => $end, 'pdf_url' => $pdfUrl]
        );
        $this->info("Created {$row->quarterLabel()} · {$theme} ({$start} → {$end}). Syncing readings…");

        Artisan::call('lesson:sync', ['--slug' => $slug]);
        $stats = $row->cacheStats();
        $summary = "{$row->quarterLabel()} · {$theme}\n{$start} → {$end}\nReadings cached: {$stats['days']}/{$stats['total_days']} days" . ($pdfUrl ? "\nPDF: {$pdfUrl}" : '');
        $this->info($summary);

        try {
            $admins = \App\Models\User::where('role', 'super_admin')->pluck('email')->all();
            if ($admins) {
                Mail::raw(
                    "The new Sabbath School quarter was added and synced automatically — /lesson rolled over on its own.\n\n{$summary}\n\nApp: " . config('app.url') . '/lesson',
                    fn ($m) => $m->to($admins)->cc('contact@c-wellpics.com')
                        ->subject("[Church of Peace] New quarter live: {$theme}")
                );
            }
        } catch (\Throwable $e) {
            $this->warn('Notification email failed: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    /** Adventech dates are dd/MM/yyyy. */
    private function date(?string $raw): ?string
    {
        if (! $raw) return null;
        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y', trim($raw))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
