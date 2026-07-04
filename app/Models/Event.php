<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'start_at', 'end_at', 'location', 'notes', 'department_id', 'created_by', 'is_public', 'flyer_path', 'recur_until', 'recur_times', 'stream_url'];
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime', 'is_public' => 'boolean', 'recur_until' => 'date', 'recur_times' => 'array'];

    private const TZ = 'America/New_York';

    /** A series (e.g. the Crusade): per-weekday times + an end date, described once. */
    public function isRecurring(): bool
    {
        return $this->recur_until !== null && ! empty($this->recur_times);
    }

    /** Times this event holds on a given calendar day ([] = not that day). */
    public function timesOn(\Carbon\Carbon $day): array
    {
        if (! $this->isRecurring()) {
            return $day->isSameDay($this->start_at) ? [$this->start_at->timezone(self::TZ)->format('g:i a')] : [];
        }
        if ($day->lt($this->start_at->copy()->timezone(self::TZ)->startOfDay()) || $day->gt($this->recur_until->copy()->endOfDay())) {
            return [];
        }
        return $this->recur_times[(string) $day->dayOfWeek] ?? [];
    }

    /** Next upcoming [day Carbon, 'g:i a'] within 60 days, or null. */
    public function nextOccurrence(): ?array
    {
        $now = now(self::TZ);
        for ($i = 0; $i <= 60; $i++) {
            $d = $now->copy()->startOfDay()->addDays($i);
            foreach ($this->timesOn($d) as $t) {
                if (\Carbon\Carbon::parse($d->toDateString() . ' ' . $t, self::TZ)->gt($now->copy()->subHours(3))) {
                    return [$d, $t];
                }
            }
        }
        return null;
    }

    /** Start time if we're inside a live window right now (30 min early → 3h after). */
    public function liveNow(): ?\Carbon\Carbon
    {
        $now = now(self::TZ);
        foreach ($this->timesOn($now->copy()->startOfDay()) as $t) {
            $start = \Carbon\Carbon::parse($now->toDateString() . ' ' . $t, self::TZ);
            if ($now->between($start->copy()->subMinutes(30), $start->copy()->addHours(3))) {
                return $start;
            }
        }
        return null;
    }

    /** The public event happening right now that has somewhere to tune in. */
    public static function happeningNow(): ?self
    {
        return static::where('is_public', true)->whereNotNull('stream_url')->whereNotNull('recur_times')
            ->where('recur_until', '>=', now(self::TZ)->toDateString())
            ->get()->first(fn ($e) => $e->liveNow());
    }

    /** Human schedule line: "Sun/Tue/Wed/Fri 7:30 pm · Sat 9:30 am & 6:00 pm · through Jul 25". */
    public function scheduleSummary(): ?string
    {
        if (! $this->isRecurring()) return null;
        $names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $groups = [];
        foreach (range(0, 6) as $i) {
            $ts = $this->recur_times[(string) $i] ?? [];
            if ($ts) $groups[implode(' & ', $ts)][] = $i;
        }
        if (! $groups) return null;
        $parts = [];
        if (count($groups) === 1 && count(reset($groups)) >= 6) {
            $off = array_values(array_diff(range(0, 6), reset($groups)));
            $parts[] = 'Nightly ' . array_key_first($groups) . ($off ? ' · except ' . implode(' & ', array_map(fn ($i) => $names[$i], $off)) : '');
        } else {
            foreach ($groups as $time => $days) {
                $parts[] = implode('/', array_map(fn ($i) => $names[$i], $days)) . ' ' . $time;
            }
        }
        return implode(' · ', $parts) . ' · through ' . $this->recur_until->format('M j');
    }

    public function department() { return $this->belongsTo(Department::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
