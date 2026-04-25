<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuarterlyLesson extends Model
{
    protected $fillable = ['year', 'quarter', 'theme', 'starts_on', 'ends_on', 'pdf_path', 'pdf_url'];
    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date'];

    /** Quarter that includes today (or the most recent past quarter if none current). */
    public static function current(?\Carbon\Carbon $now = null): ?self
    {
        $now = $now ?? now();
        return static::where('starts_on', '<=', $now->toDateString())
            ->where('ends_on',   '>=', $now->toDateString())
            ->first()
            ?? static::orderByDesc('starts_on')->first();
    }

    /** Which lesson number we're in this week. 1 → 13. Returns null if outside the quarter. */
    public function currentLessonNumber(?\Carbon\Carbon $now = null): ?int
    {
        $now = $now ?? now();
        if ($now->lt($this->starts_on) || $now->gt($this->ends_on)) return null;
        $daysIn = $this->starts_on->diffInDays($now);
        return min(13, max(1, (int) floor($daysIn / 7) + 1));
    }

    /** First & last day of the current week's lesson, NYC time. */
    public function currentLessonRange(?\Carbon\Carbon $now = null): ?array
    {
        $n = $this->currentLessonNumber($now);
        if (! $n) return null;
        $start = $this->starts_on->copy()->addDays(($n - 1) * 7);
        $end   = $start->copy()->addDays(6);
        return ['lesson' => $n, 'starts' => $start, 'ends' => $end];
    }

    /** Best download URL: local file if present, else external. */
    public function downloadUrl(): ?string
    {
        if ($this->pdf_path && file_exists(public_path($this->pdf_path))) {
            return '/' . ltrim($this->pdf_path, '/');
        }
        return $this->pdf_url;
    }

    public function quarterLabel(): string
    {
        return 'Q' . $this->quarter . ' ' . $this->year;
    }

    /** Adventech-style slug, e.g. "2026-02" for Q2 2026. Used for the cache filename and API lookup. */
    public function slug(): string
    {
        return sprintf('%04d-%02d', $this->year, $this->quarter);
    }

    /** Padded ID (e.g. 4 → "04") used by Adventech for both lessons and days. */
    public static function pad(int|string $n): string
    {
        return str_pad((string) $n, 2, '0', STR_PAD_LEFT);
    }

    /** Absolute path on disk where synced JSON lives for this quarter. */
    public function cacheDir(): string
    {
        return storage_path('app/lessons/' . $this->slug());
    }

    /** Loads a single day's reading from the synced cache. Returns null if not synced yet. */
    public function loadDay(int $lesson, int $day): ?array
    {
        $path = $this->cacheDir() . '/' . self::pad($lesson) . '/' . self::pad($day) . '.json';
        if (! is_file($path)) return null;
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : null;
    }

    /** Lesson-level metadata (title + day list). Returns null if not synced. */
    public function loadLesson(int $lesson): ?array
    {
        $path = $this->cacheDir() . '/' . self::pad($lesson) . '/index.json';
        if (! is_file($path)) return null;
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : null;
    }

    /** True if the day reading is in the local cache and ready to render. */
    public function hasDayContent(int $lesson, int $day): bool
    {
        return is_file($this->cacheDir() . '/' . self::pad($lesson) . '/' . self::pad($day) . '.json');
    }

    /** How many of the 13 lessons × 7 days are cached. Used in admin to show sync state. */
    public function cacheStats(): array
    {
        $base = $this->cacheDir();
        $lessons = 0; $days = 0;
        if (is_dir($base)) {
            for ($l = 1; $l <= 13; $l++) {
                $ldir = $base . '/' . self::pad($l);
                if (! is_dir($ldir)) continue;
                if (is_file($ldir . '/index.json')) $lessons++;
                for ($d = 1; $d <= 7; $d++) {
                    if (is_file($ldir . '/' . self::pad($d) . '.json')) $days++;
                }
            }
        }
        return ['lessons' => $lessons, 'days' => $days, 'total_days' => 91];
    }
}
