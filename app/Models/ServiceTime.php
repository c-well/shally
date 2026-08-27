<?php

namespace App\Models;

use App\Concerns\HasRevisions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * One recurring gathering on the home page's "Our service schedule" section.
 *
 * These four cards used to be a hardcoded PHP array inside landing.blade.php,
 * which meant a time change or a new Zoom link needed a developer — the one
 * part of the front page nobody at the church could touch. Now they are rows,
 * edited at /admin/services like slides and events.
 *
 * THE LIVE WINDOW. The old array carried a closure per service deciding
 * "is it on right now" (isSaturday() && between(9:15, 10:50), etc). A closure
 * cannot be stored, so the window is data: `days` holds Carbon dayOfWeek
 * numbers and live_from/live_until bound the hours. isLiveNow() reproduces the
 * previous behaviour exactly, including the deliberately generous edges — the
 * window opens before the published start and closes well after, because the
 * point is "we are gathered", not "the clock says 9:30".
 *
 * The whole living-schedule behaviour still respects the app_settings
 * `living_schedule` switch, which is toggled from the admin hub.
 */
class ServiceTime extends Model
{
    use SoftDeletes;
    use HasRevisions;

    public function revisionFields(): array
    {
        return ['name', 'when_label', 'where_label', 'zoom_url', 'days', 'live_from', 'live_until', 'is_published'];
    }

    public function revisionLabel(): string
    {
        return 'Service · ' . $this->name;
    }

    protected $fillable = [
        'name', 'when_label', 'where_label', 'zoom_url',
        'days', 'live_from', 'live_until', 'sort_order', 'is_published',
    ];

    protected $casts = [
        'days'         => 'array',
        'is_published' => 'boolean',
    ];

    public const TZ = 'America/New_York';

    /** Sunday-first, matching Carbon's dayOfWeek numbering. */
    public const DAY_NAMES = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];

    /** The cards the home page shows, in order. */
    public static function published()
    {
        return static::where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Is this gathering happening right now? Returns false when either bound is
     * unset — a service with no window simply never takes over the section,
     * which is the safe default for something like a one-off.
     */
    public function isLiveNow(?Carbon $now = null): bool
    {
        if (! $this->live_from || ! $this->live_until) {
            return false;
        }

        $now = ($now ?: Carbon::now(self::TZ))->copy()->timezone(self::TZ);

        if (! in_array((int) $now->dayOfWeek, array_map('intval', $this->days ?? []), true)) {
            return false;
        }

        [$fh, $fm] = array_map('intval', explode(':', substr((string) $this->live_from, 0, 5)));
        [$uh, $um] = array_map('intval', explode(':', substr((string) $this->live_until, 0, 5)));

        return $now->between(
            $now->copy()->setTime($fh, $fm),
            $now->copy()->setTime($uh, $um)
        );
    }

    /** Human summary of the live window for the admin list. */
    public function windowLabel(): string
    {
        if (! $this->live_from || ! $this->live_until) {
            return 'never takes over';
        }

        $days = implode(', ', array_map(
            fn ($d) => self::DAY_NAMES[(int) $d] ?? '?',
            $this->days ?? []
        ));

        return $days . ' ' . substr((string) $this->live_from, 0, 5) . '–' . substr((string) $this->live_until, 0, 5);
    }
}
