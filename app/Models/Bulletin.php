<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Concerns\HasRevisions;

class Bulletin extends Model
{
    use SoftDeletes, HasRevisions;

    /* ── revision history (see App\Concerns\HasRevisions) ── */
    public function revisionFields(): array { return ['title', 'body', 'theme', 'service_date', 'service_time', 'event_name']; }
    public function revisionLabel(): string { return 'Bulletin · ' . ($this->title ?: (optional($this->service_date)->format('M j, Y') ?: ('#' . $this->id))); }


    protected $fillable = [
        'title', 'service_date', 'body', 'pdf_path', 'is_published', 'always_show_during_week',
        'published_at', 'published_snapshot',
        'previous_published_snapshot', 'previous_published_at',
        'theme', 'author_id',
        'kind', 'service_time', 'event_name',
        'event_night_number', 'event_total_nights', 'event_ends_at',
    ];
    protected $casts = [
        'service_date'                 => 'date',
        'is_published'                 => 'boolean',
        'always_show_during_week'      => 'boolean',
        'published_at'                 => 'datetime',
        'published_snapshot'           => 'array',
        'previous_published_snapshot'  => 'array',
        'previous_published_at'        => 'datetime',
        'event_ends_at'                => 'date',
    ];

    public function author()        { return $this->belongsTo(User::class, 'author_id'); }
    public function lines()         { return $this->hasMany(BulletinLine::class)->orderBy('sort_order'); }
    public function announcements() { return $this->hasMany(Announcement::class)->orderBy('sort_order'); }

    /**
     * Fold title-less announcements into the one above (Rosharde's parent/child pattern):
     * a row with an empty title means "these lines belong to the previous announcement" —
     * its detail is appended as extra bullet lines. Used by the PDF renders; the editor
     * and public page keep the rows separate so inline editing still works per-row.
     * Also strips leading manual bullet markers (o, -, •, *) so bullets don't double up.
     */
    public static function foldAnnouncements(array $anns): array
    {
        $folded = [];
        foreach ($anns as $a) {
            $title  = trim((string) ($a['title'] ?? ''));
            $detail = trim((string) ($a['detail'] ?? ''));
            if ($title === '' && $detail !== '' && ! empty($folded)) {
                $i = count($folded) - 1;
                $folded[$i]['detail'] = trim(($folded[$i]['detail'] ?? '') . "\n" . $detail);
                continue;
            }
            if ($title === '' && $detail === '') continue;
            $folded[] = $a;
        }
        foreach ($folded as &$a) {
            $lines = preg_split("/\r?\n/", (string) ($a['detail'] ?? ''));
            $a['detail'] = implode("\n", array_map(
                fn ($l) => preg_replace('/^\s*(?:o|O|•|\*|-)\s+/u', '', $l),
                $lines
            ));
        }
        return $folded;
    }

    /* ─────── Event helpers ─────── */

    public function isEvent(): bool { return $this->kind === 'event_night'; }

    /** All nights in this event series (shared event_name + overlapping window), in date+time order. */
    public function seriesNights()
    {
        if (! $this->isEvent() || ! $this->event_name) return collect();
        return static::where('kind', 'event_night')
            ->where('event_name', $this->event_name)
            ->where('event_ends_at', $this->event_ends_at)
            ->orderBy('service_date')->orderBy('service_time')
            ->get();
    }

    /**
     * Pick the bulletin to show on the home page RIGHT NOW.
     * Rule: if today has both a morning and an evening bulletin, 3 PM (NYC) is the switchover.
     * Otherwise fall back to the single bulletin for today, else the nearest upcoming.
     *
     * Timezone note: the cutoff is in America/New_York because that's where the church is.
     * Server runs UTC; without converting, the 3 PM check would fire at 11 AM EDT — way too early.
     */
    public static function activeForNow(?\Carbon\Carbon $now = null): ?self
    {
        $now   = ($now ?? now())->copy()->setTimezone('America/New_York');
        $today = $now->toDateString();

        $todays = static::whereDate('service_date', $today)->get();

        if ($todays->count() >= 2) {
            $wantEvening = $now->hour >= 15; // 3 PM NYC time
            return $todays->firstWhere('service_time', $wantEvening ? 'evening' : 'morning')
                ?? $todays->first();
        }

        if ($todays->count() === 1) {
            return $todays->first();
        }

        // No bulletin for today — show the nearest upcoming (published preferred)
        return static::where('service_date', '>=', $today)
            ->orderBy('service_date')
            ->orderByRaw("FIELD(service_time, 'morning', 'evening')")
            ->first()
            ?? static::orderByDesc('service_date')->first();
    }

    /* ─────── Snapshot + publish (unchanged surface, same behavior) ─────── */

    public function snapshotCurrentState(): array
    {
        return [
            'title' => $this->title,
            'service_date' => $this->service_date->toDateString(),
            'kind' => $this->kind,
            'service_time' => $this->service_time,
            'event_name' => $this->event_name,
            'event_night_number' => $this->event_night_number,
            'event_total_nights' => $this->event_total_nights,
            'lines' => $this->lines()->get()->map(fn($l) => [
                'section' => $l->section, 'part' => $l->part,
                'person' => $l->person, 'kind' => $l->kind,
            ])->all(),
            'announcements' => $this->announcements()->get()->map(fn($a) => [
                'title' => $a->title, 'detail' => $a->detail, 'image_path' => $a->image_path, 'video_url' => $a->video_url, 'is_web_only' => (bool) $a->is_web_only,
            ])->all(),
        ];
    }

    public function publish(): void
    {
        if ($this->published_snapshot && $this->published_at) {
            $this->previous_published_snapshot = $this->published_snapshot;
            $this->previous_published_at       = $this->published_at;
        }
        $this->published_at       = now();
        $this->published_snapshot = $this->snapshotCurrentState();
        $this->save();
    }

    public function hasAvailablePreviousVersion(): bool
    {
        return $this->previous_published_snapshot
            && $this->previous_published_at
            && $this->previous_published_at->gt(now()->subHours(8));
    }

    public function hasUnpublishedChanges(): bool
    {
        if (! $this->published_snapshot) return true;
        return json_encode($this->snapshotCurrentState()) !== json_encode($this->published_snapshot);
    }
}
