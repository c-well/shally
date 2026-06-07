<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Daily rollup: aggregate interaction_events older than 30 days into
 * heatmap_summaries + scroll_summaries, then delete the raw rows.
 *
 * Heatmap survives forever as per-day, per-element click/hover counts.
 * Scroll survives as per-day, per-page bucket counts.
 * Named events survive in their counter tables (this command counts them
 * but they're already aggregated at query time so no extra table needed).
 *
 * Schedule: daily at 03:30 ET (after the DB backup at 03:00).
 */
class RollupInteractionEventsCommand extends Command
{
    protected $signature = 'analytics:rollup {--days=30}';
    protected $description = 'Roll up interaction events older than N days into summaries; delete raw rows.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);
        $this->info("=== analytics:rollup cutoff={$cutoff->toDateTimeString()} ===");

        // Heatmap rollup: group clicks + hovers by (path, day, element_selector)
        $heatRows = DB::table('interaction_events')
            ->where('created_at', '<', $cutoff)
            ->whereIn('event_type', ['click', 'hover'])
            ->whereNotNull('element_selector')
            ->where('is_bot', false)
            ->selectRaw('
                path,
                DATE(created_at) as day,
                element_selector,
                MAX(element_text) as element_text,
                SUM(CASE WHEN event_type = "click" THEN 1 ELSE 0 END) as click_count,
                SUM(CASE WHEN event_type = "hover" THEN 1 ELSE 0 END) as hover_count
            ')
            ->groupBy('path', 'day', 'element_selector')
            ->get();

        $heatInserted = 0;
        foreach ($heatRows as $r) {
            DB::table('heatmap_summaries')->updateOrInsert(
                ['path' => $r->path, 'day' => $r->day, 'element_selector' => $r->element_selector],
                [
                    'element_text' => $r->element_text,
                    'click_count'  => $r->click_count,
                    'hover_count'  => $r->hover_count,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );
            $heatInserted++;
        }
        $this->line("  Heatmap: rolled up {$heatInserted} (path,day,element) groups");

        // Scroll rollup: bucket scroll_pct per page per day
        $scrollRows = DB::table('interaction_events')
            ->where('created_at', '<', $cutoff)
            ->where('event_type', 'page_unload')
            ->where('is_bot', false)
            ->whereNotNull('scroll_pct')
            ->selectRaw('
                path,
                DATE(created_at) as day,
                SUM(CASE WHEN scroll_pct >= 10  THEN 1 ELSE 0 END) as r10,
                SUM(CASE WHEN scroll_pct >= 25  THEN 1 ELSE 0 END) as r25,
                SUM(CASE WHEN scroll_pct >= 50  THEN 1 ELSE 0 END) as r50,
                SUM(CASE WHEN scroll_pct >= 75  THEN 1 ELSE 0 END) as r75,
                SUM(CASE WHEN scroll_pct >= 100 THEN 1 ELSE 0 END) as r100
            ')
            ->groupBy('path', 'day')
            ->get();

        $scrollInserted = 0;
        foreach ($scrollRows as $r) {
            foreach ([10, 25, 50, 75, 100] as $bucket) {
                $col = 'r' . $bucket;
                DB::table('scroll_summaries')->updateOrInsert(
                    ['path' => $r->path, 'day' => $r->day, 'bucket' => $bucket],
                    [
                        'session_count' => $r->$col,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]
                );
                $scrollInserted++;
            }
        }
        $this->line("  Scroll: rolled up {$scrollInserted} (path,day,bucket) rows");

        // Delete raw rows
        $deleted = DB::table('interaction_events')->where('created_at', '<', $cutoff)->delete();
        $this->info("  Deleted {$deleted} raw rows older than {$days} days");

        return self::SUCCESS;
    }
}
