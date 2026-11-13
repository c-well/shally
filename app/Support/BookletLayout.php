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

    /**
     * The "Your Sabbath" questions under the web booklet: two you tick, one you write.
     * Answers are checked against these lists, so only these words are ever stored.
     * @return array{spoke: array{q:string, options:list<string>}, joined: array{q:string, options:list<string>}, story: string}
     */
    public static function questions(array $cover): array
    {
        $spoke = ['The music', 'The sermon', 'The scripture readings', 'Prayer time', 'Being together'];
        if (($cover['look'] ?? '') === 'pathfinder') array_splice($spoke, 0, 0, ['Our Pathfinders and Adventurers']);

        return [
            'spoke'  => ['q' => 'What spoke to you today?', 'options' => $spoke],
            'joined' => ['q' => 'How were you with us?', 'options' => ['In the sanctuary', 'Watching online', 'My first time visiting', 'I brought someone']],
            'story'  => 'Tell us about your Sabbath',
        ];
    }

    /** Book names as the site's ESV lookup spells them. */
    private const BOOKS = ['Genesis','Exodus','Leviticus','Numbers','Deuteronomy','Joshua','Judges','Ruth','1 Samuel','2 Samuel','1 Kings','2 Kings','1 Chronicles','2 Chronicles','Ezra','Nehemiah','Esther','Job','Psalms','Proverbs','Ecclesiastes','Song of Solomon','Isaiah','Jeremiah','Lamentations','Ezekiel','Daniel','Hosea','Joel','Amos','Obadiah','Jonah','Micah','Nahum','Habakkuk','Zephaniah','Haggai','Zechariah','Malachi','Matthew','Mark','Luke','John','Acts','Romans','1 Corinthians','2 Corinthians','Galatians','Ephesians','Philippians','Colossians','1 Thessalonians','2 Thessalonians','1 Timothy','2 Timothy','Titus','Philemon','Hebrews','James','1 Peter','2 Peter','1 John','2 John','3 John','Jude','Revelation'];

    /** Short forms people actually type that a plain prefix would get wrong. */
    private const ALIASES = ['ps' => 'Psalms', 'psa' => 'Psalms', 'psalm' => 'Psalms', 'pr' => 'Proverbs', 'prov' => 'Proverbs', 'eccl' => 'Ecclesiastes', 'song' => 'Song of Solomon', 'sos' => 'Song of Solomon', 'phil' => 'Philippians', 'php' => 'Philippians', 'phlm' => 'Philemon', 'philem' => 'Philemon', 'jn' => 'John', 'jhn' => 'John', 'mt' => 'Matthew', 'mk' => 'Mark', 'lk' => 'Luke', 'jas' => 'James', 'jud' => 'Jude', 'rev' => 'Revelation', 'judg' => 'Judges', 'jdg' => 'Judges', 'ezek' => 'Ezekiel', 'eze' => 'Ezekiel', 'hab' => 'Habakkuk', 'zeph' => 'Zephaniah', 'zech' => 'Zechariah', 'mal' => 'Malachi', 'lam' => 'Lamentations', 'ob' => 'Obadiah', 'jon' => 'Jonah'];

    /** "Phil", "1Tim", "1 tim." → "Philippians", "1 Timothy". Null when it is not a book. */
    public static function book(string $raw): ?string
    {
        $k = strtolower(preg_replace('/[\s.]+/', '', $raw));
        if (preg_match('/^([1-3])(.+)$/', $k, $m)) { $num = $m[1]; $rest = $m[2]; } else { $num = ''; $rest = $k; }
        if (strlen($rest) < 2) return null;
        if ($num === '' && isset(self::ALIASES[$rest])) return self::ALIASES[$rest];
        $hits = [];
        foreach (self::BOOKS as $b) {
            $bn = strtolower(str_replace(' ', '', $b));
            $bNum = ctype_digit($bn[0]) ? $bn[0] : '';
            if ($bNum !== $num) continue;
            if (str_starts_with(substr($bn, strlen($bNum)), $rest)) $hits[] = $b;
        }
        if ($num === '' && count($hits) > 1) return null;
        return $hits[0] ?? null;
    }

    /** Hymn number → title, from the site's own hymnal (public/lib/hymnal.json). */
    public static function hymns(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('booklet.hymn-titles', 86400, function () {
            $out = [];
            foreach ((array) json_decode((string) @file_get_contents(public_path('lib/hymnal.json')), true) as $h) {
                if (isset($h['number'], $h['title'])) $out[(int) $h['number']] = (string) $h['title'];
            }
            return $out;
        });
    }

    /**
     * Escape a program string and make its scripture references and hymns tappable.
     * "Phil 4:13" → a button that opens the verse; "#294 Power in the Blood", or a line
     * that is exactly a hymn title, → a button that opens the hymn's words.
     */
    public static function linkify(string $text): \Illuminate\Support\HtmlString
    {
        $t = trim($text);
        $hymns = self::hymns();
        $norm = fn ($x) => preg_replace('/[^a-z0-9]+/', ' ', strtolower(str_replace(['’', "'"], '', $x)));

        if (preg_match('/^#\s?(\d{1,3})\b/', $t, $m) && isset($hymns[(int) $m[1]])) {
            return new \Illuminate\Support\HtmlString('<button type="button" class="bk-tap" data-hymn="' . (int) $m[1] . '">' . e($t) . '</button>');
        }
        /* A line may give a hymn's full title or just the part before the comma ("Blessed Assurance"). */
        $byTitle = [];
        foreach ($hymns as $num => $title) {
            $byTitle[trim($norm($title))] ??= $num;
            $byTitle[trim($norm(preg_split('/[,!]/', $title)[0]))] ??= $num;
        }
        $key = trim($norm($t));
        if ($key !== '' && isset($byTitle[$key])) {
            return new \Illuminate\Support\HtmlString('<button type="button" class="bk-tap" data-hymn="' . (int) $byTitle[$key] . '">' . e($t) . '</button>');
        }

        $out = ''; $pos = 0;
        $re = '/((?:[1-3]\s?)?[A-Za-z][A-Za-z.]{1,15}(?:\s+of\s+Solomon)?)\s*(\d{1,3}):(\d{1,3})(?:\s*[-–]\s*(\d{1,3}))?(?!\s*[ap]\.?m\b)/iu';
        if (preg_match_all($re, $t, $all, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            foreach ($all as $m) {
                $book = self::book($m[1][0]);
                if (! $book) continue;
                $from = (int) $m[3][0]; $to = isset($m[4]) && $m[4][0] !== '' ? max($from, (int) $m[4][0]) : $from;
                $ref = $book . ' ' . (int) $m[2][0] . ':' . $from . ($to > $from ? '-' . $to : '');
                $out .= e(substr($t, $pos, $m[0][1] - $pos))
                     . '<button type="button" class="bk-tap" data-verse="' . e($ref) . '">' . e($m[0][0]) . '</button>';
                $pos = $m[0][1] + strlen($m[0][0]);
            }
        }
        return new \Illuminate\Support\HtmlString($out . e(substr($t, $pos)));
    }

    /** A program row's person is a group (set in italics) when it names a team, choir or club. */
    public static function isGroup(string $person): bool
    {
        return $person !== '' && (stripos($person, 'team') !== false || stripos($person, 'choir') !== false || stripos($person, 'pathfinder') !== false);
    }
}
