<?php
/**
 * OPTION B — Bulletin section-header WYSIWYG unification (2026-05-23).
 *
 * Problem this fixes:
 *   The `section` column on bulletin_lines was being used two ways:
 *     1. On kind=section_header rows  → the header's title (correct, intended)
 *     2. On kind=line rows           → a "grouping hint" used by the renderer
 *                                       to auto-inject phantom section pills
 *                                       (broken — caused 2+ duplicate "Pastoral
 *                                       Greetings" pills on Children's Day 2026-05-23)
 *
 *   The line-row usage created a dual source of truth with no UI to edit it,
 *   so Andre couldn't remove the phantoms. André first reported this in
 *   late April 2026 and has been fighting it weekly.
 *
 * What this migration does:
 *   - Sets `section = NULL` on every row where kind='line' (47 rows as of run)
 *   - Leaves section_header rows alone — they still need section as their title
 *   - Backup written to storage/backups/bulletin_lines_pre_section_fix_2026_05_23.sql.gz
 *     BEFORE this migration ran (gzip'd, 3.5KB)
 *
 * What does NOT change:
 *   - Schema is untouched. The `section` column stays — it remains the storage
 *     location for section_header row titles. We just enforce the meaning:
 *     "section is the header row's title, not a per-line attribute".
 *   - All companion code changes (renderer simplification, controller patch,
 *     model defensive hook) ship together with this migration.
 *
 * Rollback:
 *   `gunzip -c storage/backups/bulletin_lines_pre_section_fix_2026_05_23.sql.gz | mysql shalom_app`
 *   (restores the table to its pre-cleanup state)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $count = DB::table('bulletin_lines')
            ->where('kind', 'line')
            ->whereNotNull('section')
            ->update(['section' => null]);

        // Audit trail — visible in /admin/logs so we can see this ran in production.
        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::record(
                event: 'bulletin_section_field_cleaned',
                description: "Option B: nulled `section` on {$count} bulletin_lines rows where kind='line'. Phantom section-header pills eliminated. Backup: storage/backups/bulletin_lines_pre_section_fix_2026_05_23.sql.gz",
                meta: ['rows_cleaned' => $count],
            );
        }
    }

    public function down(): void
    {
        // No automatic restore — by design. If you need to undo, restore from the
        // gzipped SQL backup written before up() ran. We don't reverse-poison the
        // data because that would re-introduce the bug.
        throw new \RuntimeException(
            'This migration has no automatic rollback. To restore the prior state, run: ' .
            'gunzip -c storage/backups/bulletin_lines_pre_section_fix_2026_05_23.sql.gz | mysql shalom_app'
        );
    }
};
