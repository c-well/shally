<?php

namespace App\Support;

use App\Models\Bulletin;
use Carbon\Carbon;

/**
 * One booklet, two readers: the folded print (bulletins.pdf-booklet) and the
 * page-turning web copy (bulletins.booklet). Both take their pages from here,
 * so the cover, the program split and the back page can never disagree.
 */
class BookletLayout
{
    /**
     * @param  array       $snapshot  bulletin snapshot (published or live draft)
     * @param  array|null  $cover     the bulletin's cover JSON
     * @return array{date:Carbon, cover:array, kicker:string, title:string, halves:array, rowsMax:int, gap:int, pt:float, emblems:array, announcements:array}
     */
    public static function make(array $snapshot, ?array $cover): array
    {
        $date  = Carbon::parse($snapshot['service_date'] ?? now());
        $cover = is_array($cover) ? $cover : [];
        $lines = array_values($snapshot['lines'] ?? []);

        /* Split nearest the middle, only where the next row is a real line
           (a hymn name or scripture reference stays with the line above it). */
        $n = count($lines); $split = $n;
        if ($n > 1) {
            $best = null;
            for ($i = 1; $i < $n; $i++) {
                if (($lines[$i]['kind'] ?? 'line') === 'section_header') continue;
                if ($best === null || abs($i - $n / 2) < abs($best - $n / 2)) $best = $i;
            }
            $split = $best ?? $n;
        }
        $halves = [array_slice($lines, 0, $split), array_slice($lines, $split)];

        /* About 470pt of page height for the program. Few lines get generous
           spacing; a long program packs tighter but still fits. dompdf counts
           each gap once, not twice. */
        $rowsMax = max(1, count($halves[0]), count($halves[1]));
        $gap     = (int) max(3, min(24, floor(470 / $rowsMax - 17)));
        $pt      = $rowsMax > 16 ? 10.5 : 12;

        /* Emblem file names that really exist in public/img/emblems. */
        $emblems = [];
        foreach ((array) ($cover['emblems'] ?? []) as $f) {
            $name = basename((string) $f);
            if ($name !== '' && is_file(public_path('img/emblems/' . $name))) $emblems[] = $name;
        }

        $snapTitle = (string) ($snapshot['title'] ?? '');
        $title = $cover['title']
            ?? (in_array(strtolower($snapTitle), ['', 'sabbath service', 'sabbath worship'], true) ? 'Sabbath Worship' : $snapTitle);

        $announcements = Bulletin::foldAnnouncements(array_values(array_filter(
            $snapshot['announcements'] ?? [], fn ($a) => empty($a['is_web_only'])
        )));

        return [
            'date'          => $date,
            'cover'         => $cover,
            'kicker'        => $cover['kicker'] ?? 'Welcome to',
            'title'         => $title,
            'halves'        => $halves,
            'rowsMax'       => $rowsMax,
            'gap'           => $gap,
            'pt'            => $pt,
            'emblems'       => $emblems,
            'announcements' => $announcements,
        ];
    }

    /** A program row's person is a group (set in italics) when it names a team, choir or club. */
    public static function isGroup(string $person): bool
    {
        return $person !== '' && (stripos($person, 'team') !== false || stripos($person, 'choir') !== false || stripos($person, 'pathfinder') !== false);
    }
}
