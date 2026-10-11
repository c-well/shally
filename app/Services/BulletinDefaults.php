<?php
namespace App\Services;

use App\Models\Bulletin;
use App\Models\BulletinDefault;
use Illuminate\Support\Facades\DB;

/**
 * Save and restore "our normal week".
 *
 * The one engine both the admin page and the site menu call, so the two can
 * never drift into meaning different things by the word "default" — which is
 * exactly what happened before: the menu's "Reset to default" ran a fourteen
 * line list hardcoded in PHP while the real bulletin had eighteen lines and
 * eight standing announcements, so the button quietly made the bulletin worse.
 *
 * Shape of a snapshot:
 *   lines[]         section, part, kind          (person is never stored —
 *                                                 who serves changes weekly)
 *   announcements[] title, detail, is_web_only   (only the ones ticked
 *                                                 "every week" when saving)
 *   meta            theme, title, saved_from, saved_at
 */
class BulletinDefaults
{
    /** Scopes a restore can run at. */
    public const SCOPE_ORDER = 'order';
    public const SCOPE_FULL  = 'order_announcements';

    /**
     * Read a bulletin into a snapshot array.
     *
     * $keepAnnouncementIds limits which announcements count as "every week".
     * Passing null means all of them; passing [] means none. This is the whole
     * defence against a dated notice — "Upcoming Events" and the like — coming
     * back to life months later on a restore.
     */
    public function snapshot(Bulletin $b, ?array $keepAnnouncementIds = null): array
    {
        $lines = $b->lines()->orderBy('sort_order')->get()
            ->map(fn ($l) => [
                'section' => $l->section,
                'part'    => $l->part,
                'kind'    => $l->kind,
            ])->values()->all();

        $anns = $b->announcements()->orderBy('sort_order')->get()
            ->when($keepAnnouncementIds !== null,
                fn ($c) => $c->whereIn('id', $keepAnnouncementIds))
            ->map(fn ($a) => [
                'title'       => $a->title,
                'detail'      => $a->detail,
                'is_web_only' => (bool) $a->is_web_only,
            ])->values()->all();

        return [
            'lines'         => $lines,
            'announcements' => $anns,
            'meta' => [
                'theme'      => $b->theme,
                'title'      => $b->title,
                'saved_from' => optional($b->service_date)->toDateString(),
                'saved_at'   => now()->toIso8601String(),
            ],
        ];
    }

    /** Store a snapshot as the new active default. Never overwrites — appends. */
    public function saveDefault(Bulletin $b, ?array $keepAnnouncementIds, ?int $userId, ?string $note = null): BulletinDefault
    {
        return BulletinDefault::create([
            'kind'     => 'default',
            'payload'  => $this->snapshot($b, $keepAnnouncementIds),
            'note'     => $note,
            'saved_by' => $userId,
        ]);
    }

    /**
     * Put a snapshot onto a bulletin.
     *
     * Writes an undo copy FIRST, inside the same transaction, so a restore is
     * never a one way door — the whole point of the feature is to be a safety
     * net, and a safety net you cannot climb back out of is a trap.
     *
     * SCOPE_ORDER touches only the order of service. Announcements are left
     * exactly as they are, because most of the time the order is what got
     * mangled and the notices are fine.
     */
    public function restore(Bulletin $b, array $payload, string $scope, ?int $userId): BulletinDefault
    {
        $undo = null;

        DB::transaction(function () use ($b, $payload, $scope, $userId, &$undo) {
            $undo = BulletinDefault::create([
                'kind'        => 'undo',
                'bulletin_id' => $b->id,
                'payload'     => $this->snapshot($b),   // everything, so undo is total
                'note'        => 'before restore (' . $scope . ')',
                'saved_by'    => $userId,
            ]);

            $this->applyLines($b, $payload['lines'] ?? []);

            if ($scope === self::SCOPE_FULL) {
                $this->applyAnnouncements($b, $payload['announcements'] ?? []);
            }
        });

        return $undo;
    }

    /** Put a bulletin back to a pre-restore copy. */
    public function applyUndo(Bulletin $b, BulletinDefault $undo): void
    {
        DB::transaction(function () use ($b, $undo) {
            $this->applyLines($b, $undo->payload['lines'] ?? []);
            $this->applyAnnouncements($b, $undo->payload['announcements'] ?? []);
            $undo->delete();   // an undo is spent once used
        });
    }

    private function applyLines(Bulletin $b, array $lines): void
    {
        $b->lines()->delete();
        foreach ($lines as $i => $l) {
            $b->lines()->create([
                'section'    => $l['section'] ?? null,
                'part'       => $l['part'] ?? null,
                'person'     => null,          // who serves is this week's business
                'kind'       => $l['kind'] ?? 'line',
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * Announcements carrying media are left alone. An image lives on disk and
     * may well have been cleaned up since the default was saved, so putting
     * the row back would hang a broken picture on the bulletin. Text rows are
     * replaced; rows with an image or a video survive the restore and are
     * pushed below the restored set.
     */
    private function applyAnnouncements(Bulletin $b, array $anns): void
    {
        $keep = $b->announcements()->orderBy('sort_order')->get()
            ->filter(fn ($a) => $a->image_path || $a->video_url);

        $b->announcements()->whereNull('image_path')->whereNull('video_url')->delete();

        $sort = 0;
        foreach ($anns as $a) {
            $b->announcements()->create([
                'title'       => $a['title'] ?? '',
                'detail'      => $a['detail'] ?? null,
                'is_web_only' => (bool) ($a['is_web_only'] ?? false),
                'sort_order'  => $sort++,
            ]);
        }
        foreach ($keep as $a) {
            $a->update(['sort_order' => $sort++]);
        }
    }

    /**
     * What to fall back on before anybody has saved a normal week.
     *
     * Reads the ORDER of the most recent published Sabbath bulletin, because
     * that is a real week this church actually ran. The hardcoded list below
     * is only reached if there is no published bulletin at all.
     *
     * This matters: the hardcoded list is fourteen lines and the live bulletin
     * is eighteen. Falling back to the hardcode would drop Praise & Worship,
     * Offertory and Meditation Song every time somebody pressed reset — which
     * is exactly the bug the old "Reset to default" button had.
     *
     * No announcements are ever guessed. Which notices are "every week" is
     * André's call, made by ticking them when he saves.
     */
    public function seedPayload(): array
    {
        $recent = Bulletin::where('kind', 'sabbath')->where('is_published', true)
            ->orderByDesc('service_date')->first();

        if ($recent && $recent->lines()->exists()) {
            $snap = $this->snapshot($recent, []);   // [] = no announcements
            $snap['meta']['seed'] = 'last_published';
            return $snap;
        }

        return $this->shippedPayload();
    }

    /** The order of service shipped with the app. Last resort only. */
    public function shippedPayload(): array
    {
        $pastoral = 'Pastoral Greetings / Baby Dedication';
        $parts = [
            ['section' => null, 'part' => 'Call to Worship', 'kind' => 'line'],
            ['section' => null, 'part' => 'Response Song', 'kind' => 'line'],
            ['section' => null, 'part' => 'Invocation', 'kind' => 'line'],
            ['section' => null, 'part' => 'Announcements', 'kind' => 'line'],
            ['section' => $pastoral, 'part' => null, 'kind' => 'section_header'],
            ['section' => null, 'part' => 'Welcome', 'kind' => 'line'],
            ['section' => null, 'part' => 'Opening Hymn', 'kind' => 'line'],
            ['section' => null, 'part' => 'Scripture Reading', 'kind' => 'line'],
            ['section' => null, 'part' => 'Intercessory Prayer', 'kind' => 'line'],
            ['section' => null, 'part' => "Children's Story", 'kind' => 'line'],
            ['section' => null, 'part' => 'Special Music', 'kind' => 'line'],
            ['section' => null, 'part' => 'Sermon', 'kind' => 'line'],
            ['section' => null, 'part' => 'Closing Hymn', 'kind' => 'line'],
            ['section' => null, 'part' => 'Benediction', 'kind' => 'line'],
        ];
        return ['lines' => $parts, 'announcements' => [], 'meta' => ['seed' => true]];
    }

    /** What the buttons should act on: the saved default, else the shipped seed. */
    public function activePayload(): array
    {
        return BulletinDefault::active()?->payload ?? $this->seedPayload();
    }
}
