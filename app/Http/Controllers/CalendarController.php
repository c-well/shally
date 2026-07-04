<?php
namespace App\Http\Controllers;

use App\Models\Bulletin;
use App\Models\Event;
use App\Models\PeaceSermon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The Shalom calendar — a LENS over data the church already keeps, not a new silo.
 * It aggregates three sources into one month grid:
 *   • Events            → explicit church events (Pathfinder Day, trips, crusades)
 *   • Bulletins         → each service_date = a Sabbath service; the Sermon line = who preached
 *   • Peace sermons     → the "preached on" archive, filling in history the bulletins don't cover
 * Add a date to a bulletin and the calendar already knows. One source of truth.
 * Phase 1: public month view (read + aggregate). Editing / other views come next.
 */
class CalendarController extends Controller
{
    private const TZ = 'America/New_York';

    public function index(Request $request): View
    {
        $ym = (string) $request->query('ym');
        $month = preg_match('/^\d{4}-\d{2}$/', $ym)
            ? Carbon::createFromFormat('Y-m', $ym, self::TZ)->startOfMonth()
            : Carbon::now(self::TZ)->startOfMonth();

        $gridStart = $month->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd   = $month->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
        $entries   = $this->aggregate($gridStart, $gridEnd);
        $today     = Carbon::now(self::TZ);

        $days = [];
        for ($c = $gridStart->copy(); $c <= $gridEnd; $c->addDay()) {
            $days[] = [
                'date'    => $c->copy(),
                'inMonth' => $c->month === $month->month,
                'isToday' => $c->isSameDay($today),
                'entries' => $entries[$c->toDateString()] ?? [],
            ];
        }

        return view('calendar.month', [
            'month'   => $month,
            'days'    => $days,
            'prevYm'  => $month->copy()->subMonth()->format('Y-m'),
            'nextYm'  => $month->copy()->addMonth()->format('Y-m'),
            'todayYm' => $today->format('Y-m'),
        ]);
    }

    /** Build [ 'Y-m-d' => [entry, ...] ] across the grid range. */
    private function aggregate(Carbon $start, Carbon $end): array
    {
        $out = [];
        $push = function (string $date, array $entry) use (&$out) { $out[$date][] = $entry; };

        // ── 1. Explicit events ──
        foreach (Event::whereBetween('start_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                     ->orderBy('start_at')->get() as $e) {
            $push(Carbon::parse($e->start_at, self::TZ)->toDateString(), [
                'type'  => 'event',
                'title' => $e->title,
                'time'  => Carbon::parse($e->start_at, self::TZ)->format('g:i a'),
                'id'    => $e->id,
                'source'=> 'event',
            ]);
        }

        // ── 2. Bulletins → service + preacher ──
        $covered = [];   // service dates a bulletin already owns (so the archive doesn't double up)
        foreach (Bulletin::whereNotNull('service_date')
                     ->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
                     ->with('lines')->get() as $b) {
            $date = Carbon::parse($b->service_date, self::TZ)->toDateString();
            $covered[$date] = true;
            $push($date, [
                'type'  => 'service',
                'title' => ($b->kind === 'event_night' && $b->event_name) ? $b->event_name : 'Sabbath Worship',
                'time'  => $b->service_time ?: '11:00 am',
                'id'    => $b->id,
                'source'=> 'bulletin',
            ]);
            $sermon = $b->lines->first(fn ($l) =>
                stripos((string) $l->part, 'sermon') !== false && trim((string) $l->person) !== '');
            if ($sermon) {
                $push($date, ['type' => 'sermon', 'title' => trim($sermon->person) . ' preached', 'id' => $b->id, 'source' => 'bulletin']);
            }
        }

        // ── 3. Sermon archive → fill in dates no bulletin covers ──
        if (class_exists(PeaceSermon::class)) {
            foreach (PeaceSermon::whereNotNull('sermon_date')->whereNotNull('published_at')
                         ->whereBetween('sermon_date', [$start->toDateString(), $end->toDateString()])
                         ->get() as $s) {
                $date = Carbon::parse($s->sermon_date, self::TZ)->toDateString();
                if (isset($covered[$date])) continue;
                $who = trim((string) $s->speaker) ?: 'Message';
                $push($date, ['type' => 'sermon', 'title' => $who === 'Message' ? $s->title : $who . ' preached', 'id' => $s->id, 'source' => 'sermon']);
            }
        }

        $rank = ['service' => 0, 'sermon' => 1, 'event' => 2];
        foreach ($out as $k => $list) {
            usort($out[$k], fn ($a, $z) => ($rank[$a['type']] ?? 3) <=> ($rank[$z['type']] ?? 3));
        }
        return $out;
    }
}
