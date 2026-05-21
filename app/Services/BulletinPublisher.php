<?php
namespace App\Services;

use App\Models\AuditLog;
use App\Models\Bulletin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * BulletinPublisher — single source of truth for bulletin publish-state writes.
 *
 * The bulletin has FOUR interrelated publish-state columns:
 *   - is_published                  (boolean)
 *   - published_at                  (timestamp)
 *   - published_snapshot            (JSON of frozen state)
 *   - previous_published_snapshot   (JSON of prior publish, 8-hr retention)
 *   - previous_published_at         (timestamp of prior publish)
 *
 * Before this service existed, the four were toggled in different places
 * (controller, model method, cron, even raw SQL) — and `is_published` was
 * never actually touched, leaving rows in inconsistent states. The senior
 * engineering review flagged this as a correctness landmine.
 *
 * Every transition that mutates publish-state goes through here. Each method
 * is wrapped in a DB transaction and audit-logs the result. Idempotent:
 * calling publish() twice within the 8-hour window correctly cycles
 * current → previous, no data loss.
 */
class BulletinPublisher
{
    /**
     * Publish a bulletin: snapshot current state, set is_published=true,
     * stamp published_at, and rotate the prior snapshot to previous_*.
     *
     * Idempotent: calling repeatedly is safe; each call rotates the snapshot.
     */
    public function publish(Bulletin $bulletin, ?User $user = null): Bulletin
    {
        return DB::transaction(function () use ($bulletin, $user) {
            // Rotate current → previous (if we have a current to rotate)
            if ($bulletin->published_snapshot && $bulletin->published_at) {
                $bulletin->previous_published_snapshot = $bulletin->published_snapshot;
                $bulletin->previous_published_at       = $bulletin->published_at;
            }

            // SNAPSHOT_SAFEGUARD — guard against the empty-snapshot bug that bit Andre on May 16.
            // If snapshotCurrentState returns something that's missing core keys OR has zero lines
            // when the live bulletin has lines, ABORT — rolls back the transaction. Better to throw
            // a visible error than silently save "[]" and serve an empty bulletin to guests.
            $snap = $bulletin->snapshotCurrentState();
            $liveLines = $bulletin->lines()->count();
            $liveAnns  = $bulletin->announcements()->count();
            if (! is_array($snap) || ! isset($snap['title']) || ! isset($snap['service_date'])) {
                throw new \RuntimeException('snapshotCurrentState returned malformed array — publish aborted to avoid empty snapshot. Keys: ' . implode(',', array_keys(is_array($snap) ? $snap : [])));
            }
            $snapLines = count($snap['lines'] ?? []);
            $snapAnns  = count($snap['announcements'] ?? []);
            if ($liveLines > 0 && $snapLines === 0) {
                throw new \RuntimeException("snapshotCurrentState: live bulletin has {$liveLines} lines but snapshot has 0. Publish aborted. (bulletin id={$bulletin->id})");
            }
            if ($liveAnns > 0 && $snapAnns === 0) {
                throw new \RuntimeException("snapshotCurrentState: live bulletin has {$liveAnns} announcements but snapshot has 0. Publish aborted. (bulletin id={$bulletin->id})");
            }
            \Illuminate\Support\Facades\Log::info('Bulletin publish snapshot built', [
                'bulletin_id' => $bulletin->id,
                'snap_bytes'  => strlen(json_encode($snap)),
                'snap_lines'  => $snapLines,
                'snap_anns'   => $snapAnns,
            ]);
            $bulletin->is_published       = true;
            $bulletin->published_at       = now();
            $bulletin->published_snapshot = $snap;
            $bulletin->save();

            AuditLog::record(
                event: 'bulletin_published',
                userId: $user?->id,
                description: ($user?->name ?? 'system')
                    . " published bulletin id={$bulletin->id} (service_date="
                    . $bulletin->service_date->toDateString() . ')',
            );

            return $bulletin->fresh();
        });
    }

    /**
     * Unpublish: set is_published=false but PRESERVE the snapshot so
     * /bulletin/{id} URLs continue to work for guests with a published_snapshot
     * until the cron clears it. The snapshot is the public source of truth;
     * is_published is the editorial flag.
     */
    public function unpublish(Bulletin $bulletin, ?User $user = null): Bulletin
    {
        return DB::transaction(function () use ($bulletin, $user) {
            $bulletin->is_published = false;
            $bulletin->save();

            AuditLog::record(
                event: 'bulletin_unpublished',
                userId: $user?->id,
                description: ($user?->name ?? 'system')
                    . " unpublished bulletin id={$bulletin->id}",
            );

            return $bulletin->fresh();
        });
    }

    /**
     * Restore the previous published version: swap previous_* into current.
     * Only works if previous version is within the 8-hour retention window.
     * Returns true on success, false if no recoverable previous exists.
     */
    public function restorePrevious(Bulletin $bulletin, ?User $user = null): bool
    {
        if (! $bulletin->hasAvailablePreviousVersion()) {
            return false;
        }

        DB::transaction(function () use ($bulletin, $user) {
            $bulletin->published_snapshot          = $bulletin->previous_published_snapshot;
            $bulletin->published_at                = $bulletin->previous_published_at;
            $bulletin->previous_published_snapshot = null;
            $bulletin->previous_published_at       = null;
            $bulletin->is_published                = true;
            $bulletin->save();

            AuditLog::record(
                event: 'bulletin_previous_restored',
                userId: $user?->id,
                description: ($user?->name ?? 'system')
                    . " restored previous version of bulletin id={$bulletin->id}",
            );
        });

        return true;
    }

    /**
     * Clear stale previous_* snapshots (older than the retention window).
     * Replaces the direct-DB-mutation in ClearStalePreviousSnapshots command.
     * Returns count of bulletins affected.
     */
    public function clearStaleSnapshots(int $hoursOld = 8): int
    {
        $cutoff = now()->subHours($hoursOld);

        $query = Bulletin::whereNotNull('previous_published_at')
            ->where('previous_published_at', '<', $cutoff);

        $count = $query->count();
        if ($count === 0) return 0;

        DB::transaction(function () use ($query, $count, $hoursOld) {
            $query->update([
                'previous_published_snapshot' => null,
                'previous_published_at'       => null,
            ]);

            AuditLog::record(
                event: 'bulletin_snapshots_pruned',
                userId: null,
                description: "Cleared {$count} stale previous-snapshot(s) older than {$hoursOld}h (cron).",
            );
        });

        return $count;
    }
}
