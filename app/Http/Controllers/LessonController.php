<?php
namespace App\Http\Controllers;

use App\Models\QuarterlyLesson;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    /**
     * GET /lesson           — current week of the active quarter
     * GET /lesson/{lesson}  — a specific lesson number (1-13)
     *
     * The page renders ALL 7 days of the chosen week inline so the day strip
     * becomes a client-side tab switcher (zero network round-trips while reading)
     * and the registered service worker caches the response for offline access.
     */
    public function show(Request $req, ?int $lesson = null): View
    {
        $quarter = QuarterlyLesson::current();

        // Either an explicit ?lesson=N path param, or fall back to "this week".
        $lessonNum = null;
        if ($quarter) {
            $lessonNum = $lesson
                ? max(1, min(13, $lesson))
                : ($quarter->currentLessonNumber() ?? 1);
        }

        // Pull cached lesson + days. Returns nulls if sync hasn't run yet.
        $lessonData = $quarter && $lessonNum ? $quarter->loadLesson($lessonNum) : null;
        $days = [];
        if ($quarter && $lessonNum && $lessonData) {
            foreach (($lessonData['days'] ?? []) as $D) {
                $did = (int) ($D['id'] ?? 0);
                if (! $did) continue;
                $full = $quarter->loadDay($lessonNum, $did);
                if ($full) $days[] = $full;
            }
        }

        // Current week date range for the strip header.
        // Anchor weekStart to NY timezone so the diff with `$today` (also NY) is whole days,
        // not fractional (the old bug: starts_on in UTC midnight + today in NY midnight =
        // 0.166-day offset → activeDay = 1.166 → no panel matched id "01" → blank page).
        $range = $quarter ? $quarter->currentLessonRange() : null;
        $weekStart = $quarter && $lessonNum
            ? $quarter->starts_on->copy()->setTimezone('America/New_York')->startOfDay()->addDays(($lessonNum - 1) * 7)
            : null;
        $weekEnd = $weekStart?->copy()->addDays(6);

        // Default-active day: today if we're inside this week, else day 1.
        // Cast to int so comparison against day id (which we also cast to int in the view) is strict.
        $today = now()->setTimezone('America/New_York')->startOfDay();
        $activeDay = 1;
        if ($weekStart && $today->gte($weekStart) && $today->lte($weekEnd)) {
            $activeDay = (int) floor($weekStart->diffInDays($today)) + 1;
        }

        return view('lesson.show', compact(
            'quarter', 'lessonNum', 'lessonData', 'days',
            'weekStart', 'weekEnd', 'activeDay', 'range'
        ));
    }
}
