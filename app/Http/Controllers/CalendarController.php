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
 * Aggregates three sources:
 *   • Events        → explicit church events (Pathfinder Day, trips, crusades)
 *   • Bulletins     → each service_date = a Sabbath service; the Sermon line = who preached
 *   • Peace sermons → the "preached on" archive, filling history no bulletin covers
 * Add a date to a bulletin and the calendar already knows. One source of truth.
 *
 * Phase 2 (genesis-fluidity model): the controller ships a WIDE window of entries
 * as one JSON payload; the client renders Month/Week/Day locally so switching is
 * instant — no round-trip per navigation. (Pattern studied from /home/adminkc/genesis.)
 */
class CalendarController extends Controller
{
    private const TZ = 'America/New_York';

    public function index(Request $request): View
    {
        $today = Carbon::now(self::TZ);
        $start = $today->copy()->subMonths(18)->startOfMonth();
        $end   = $today->copy()->addMonths(18)->endOfMonth();

        return view('calendar.index', [
            'payload' => [
                'today'   => $today->toDateString(),
                'entries' => $this->aggregate($start, $end),
            ],
        ]);
    }

    /** Build [ 'Y-m-d' => [entry, ...] ] across the range. */
    private function aggregate(Carbon $start, Carbon $end): array
    {
        $out = [];
        $push = function (string $date, array $entry) use (&$out) { $out[$date][] = $entry; };

        // ── 1. Explicit events (series unroll into every occurrence) ──
        $events = Event::where(fn ($q) => $q
                ->whereBetween('start_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                ->orWhere(fn ($q2) => $q2->whereNotNull('recur_until')
                    ->where('recur_until', '>=', $start->toDateString())
                    ->where('start_at', '<=', $end->copy()->endOfDay())))
            ->with('department')->orderBy('start_at')->get();
        foreach ($events as $e) {
            $base = [
                't'    => 'event',
                'n'    => $e->title,
                'loc'  => $e->location,
                'dept' => $e->department->name ?? null,
                'pub'  => (bool) $e->is_public,
            ];
            if ($e->stream_url) $base['url'] = $e->stream_url;
            if ($e->isRecurring()) {
                $d = Carbon::parse($e->start_at, self::TZ)->startOfDay()->max($start);
                $last = $e->recur_until->copy()->min($end);
                for (; $d->lte($last); $d->addDay()) {
                    $times = $e->timesOn($d);
                    if ($times) $push($d->toDateString(), $base + ['time' => implode(' & ', $times)]);
                }
            } else {
                $push(Carbon::parse($e->start_at, self::TZ)->toDateString(),
                    $base + ['time' => Carbon::parse($e->start_at, self::TZ)->format('g:i a')]);
            }
        }

        // ── 2. Bulletins → service + preacher ──
        $covered = [];
        foreach (Bulletin::whereNotNull('service_date')
                     ->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
                     ->with('lines')->get() as $b) {
            $date = Carbon::parse($b->service_date, self::TZ)->toDateString();
            $covered[$date] = true;
            $push($date, [
                't'    => 'service',
                'n'    => ($b->kind === 'event_night' && $b->event_name) ? $b->event_name : 'Sabbath Worship',
                'time' => $b->service_time ?: '11:00 am',
                'url'  => '/bulletin/' . $b->id,
            ]);
            $sermon = $b->lines->first(fn ($l) =>
                stripos((string) $l->part, 'sermon') !== false && trim((string) $l->person) !== '');
            if ($sermon) {
                $push($date, ['t' => 'sermon', 'n' => trim($sermon->person) . ' preached', 'url' => '/bulletin/' . $b->id]);
            }
        }

        // ── 3. Sermon archive → history no bulletin covers ──
        foreach (PeaceSermon::whereNotNull('sermon_date')->whereNotNull('published_at')
                     ->whereBetween('sermon_date', [$start->toDateString(), $end->toDateString()])
                     ->get() as $s) {
            $date = Carbon::parse($s->sermon_date, self::TZ)->toDateString();
            if (isset($covered[$date])) continue;
            $who = trim((string) $s->speaker);
            $push($date, [
                't'   => 'sermon',
                'n'   => $who !== '' ? $who . ' preached' : $s->title,
                'sub' => $s->title,
                'url' => '/find-peace/' . $s->slug,
            ]);
        }

        $rank = ['service' => 0, 'sermon' => 1, 'event' => 2];
        foreach ($out as $k => $list) {
            usort($out[$k], fn ($a, $z) => ($rank[$a['t']] ?? 3) <=> ($rank[$z['t']] ?? 3));
        }
        ksort($out);
        return $out;
    }
}
