<?php

namespace App\Services;

use App\Models\PeaceSermon;

/**
 * The picture that shows when somebody shares a link.
 *
 * A message shared into a group chat should look like the message, not like a
 * logo on a square — the title is the reason anyone taps. So each sermon gets
 * its own card: its title, its series, its date, in the church's own type.
 *
 * Cards are written into public/og/, which means Apache serves them directly
 * on every request after the first and PHP is never in the path again.
 */
class OgCard
{
    private const W = 1200;

    private const H = 630;

    private const CREAM = [254, 252, 239];

    private const TEAL = [3, 97, 122];

    private const INK = [26, 35, 50];

    private const SOFT = [96, 112, 128];

    public static function sermonRelPath(PeaceSermon $sermon): string
    {
        // The hash means a retitled sermon gets a new URL, so Facebook and
        // iMessage — which cache per URL, forever — pick the new card up.
        $stamp = substr(sha1($sermon->title.'|'.$sermon->heart_line.'|'.$sermon->speaker), 0, 8);

        return 'og/find-peace/'.$sermon->slug.'-'.$stamp.'.png';
    }

    public static function sermonUrl(PeaceSermon $sermon): string
    {
        return url('/'.self::sermonRelPath($sermon));
    }

    /** Renders the card if it is not already on disk. Returns its path. */
    public static function ensureSermon(PeaceSermon $sermon): string
    {
        $rel = self::sermonRelPath($sermon);
        $abs = public_path($rel);

        if (is_file($abs)) {
            return $abs;
        }

        if (! is_dir(dirname($abs))) {
            mkdir(dirname($abs), 0755, true);
        }

        self::render($sermon, $abs);

        return $abs;
    }

    /* ── Music day ───────────────────────────────────────────────────────
     * The card that shows when André texts the singers their link. Purple,
     * green and gold, from the music day flyer, but none of the flyer's
     * effects: one quiet gradient, five faint staff lines as the only nod to
     * music, and the action itself, "Send your song", so the preview says
     * what the link is for before anyone taps it.
     *
     * Named by id and a hash, never by the link token. The card URL sits in
     * the page head, and the token is the permission to submit.
     */
    private const M_TOP   = [74, 26, 110];
    private const M_FOOT  = [40, 11, 64];
    private const M_GOLD  = [214, 178, 96];
    private const M_CREAM = [251, 246, 236];
    private const M_LAV   = [204, 186, 226];
    private const M_GREEN = [36, 138, 72];

    public static function musicDayRelPath(\App\Models\MusicDay $day): string
    {
        $stamp = substr(sha1($day->title . '|' . $day->service_date->toDateString() . '|v4'), 0, 10);

        return 'og/music/' . $day->id . '-' . $stamp . '.png';
    }

    public static function musicDayUrl(\App\Models\MusicDay $day): string
    {
        return url('/' . self::musicDayRelPath($day));
    }

    public static function ensureMusicDay(\App\Models\MusicDay $day): string
    {
        $abs = public_path(self::musicDayRelPath($day));
        if (is_file($abs)) {
            return $abs;
        }
        if (! is_dir(dirname($abs))) {
            mkdir(dirname($abs), 0755, true);
        }
        self::renderMusic($day, $abs);

        return $abs;
    }

    private static function renderMusic(\App\Models\MusicDay $day, string $to): void
    {
        $im = imagecreatetruecolor(self::W, self::H);
        imagealphablending($im, true);

        /* Ground: one vertical gradient, top to foot. */
        for ($y = 0; $y < self::H; $y++) {
            $t = $y / (self::H - 1);
            $c = imagecolorallocate($im,
                (int) round(self::M_TOP[0] + (self::M_FOOT[0] - self::M_TOP[0]) * $t),
                (int) round(self::M_TOP[1] + (self::M_FOOT[1] - self::M_TOP[1]) * $t),
                (int) round(self::M_TOP[2] + (self::M_FOOT[2] - self::M_TOP[2]) * $t));
            imageline($im, 0, $y, self::W, $y, $c);
        }

        $gold   = imagecolorallocate($im, ...self::M_GOLD);
        $cream  = imagecolorallocate($im, ...self::M_CREAM);
        $lav    = imagecolorallocate($im, ...self::M_LAV);
        $green  = imagecolorallocate($im, ...self::M_GREEN);

        /*
         * Three things and nothing else (Karlon, 22 Oct): what it is, its
         * name, and the date, so whoever opens the text is reminded when it
         * is. The colour carries the flyer; the words carry the message.
         */
        imagefilledrectangle($im, 0, self::H - 14, self::W, self::H, $green);
        imagefilledrectangle($im, 0, self::H - 17, self::W, self::H - 15, $gold);

        $isDefault = trim(mb_strtolower($day->title)) === 'music day';
        $serif     = self::font('CormorantGaramond.ttf');
        $date      = $day->service_date->format('l, F j');

        if ($isDefault) {
            self::centred($im, $serif, 112, 330, 'Music Day', $cream);
            self::centred($im, self::font('Poppins-Regular.ttf'), 30, 430, $date, $lav);
        } else {
            $lines = null;
            for ($size = 100; $size >= 74; $size -= 2) {
                $bx = imagettfbbox($size, 0, $serif, $day->title);
                if (($bx[2] - $bx[0]) <= 1000) { $lines = [$day->title]; break; }
            }
            if (! $lines) {
                for ($size = 88; $size >= 50; $size -= 2) {
                    if ($lines = self::balanced2($serif, $size, $day->title, 1000)) break;
                }
                $lines ??= [mb_strimwidth($day->title, 0, 60, '…')];
            }
            $lh    = (int) round($size * 1.06);
            $block = 60 + (count($lines) - 1) * $lh + 110;
            $top   = (int) ((self::H - 17 - $block) / 2) + 40;

            self::centred($im, self::font('Poppins-SemiBold.ttf'), 22, $top, 'MUSIC DAY', $gold, 9);
            $y = $top + 40 + (int) round($size * 0.78);
            foreach ($lines as $i => $line) {
                self::centred($im, $serif, $size, $y + $i * $lh, $line, $cream);
            }
            $last = $y + (count($lines) - 1) * $lh;
            self::centred($im, self::font('Poppins-Regular.ttf'), 30, $last + (int) round($size * 0.34) + 66, $date, $lav);
        }

        imagepng($im, $to, 8);
        imagedestroy($im);
    }

    /** Two lines of near-equal width, or null if no split fits. @return string[]|null */
    private static function balanced2(string $font, float $size, string $text, int $width): ?array
    {
        $words = preg_split('/\s+/u', trim($text));
        $best = null; $bestW = PHP_INT_MAX;
        for ($k = 1; $k < count($words); $k++) {
            $a = implode(' ', array_slice($words, 0, $k));
            $b = implode(' ', array_slice($words, $k));
            $wa = imagettfbbox($size, 0, $font, $a); $wa = $wa[2] - $wa[0];
            $wb = imagettfbbox($size, 0, $font, $b); $wb = $wb[2] - $wb[0];
            $w = max($wa, $wb);
            if ($w <= $width && $w < $bestW) { $best = [$a, $b]; $bestW = $w; }
        }
        return $best;
    }

    private static function font(string $name): string
    {
        return storage_path('fonts/'.$name);
    }

    private static function render(PeaceSermon $sermon, string $to): void
    {
        $im = imagecreatetruecolor(self::W, self::H);

        $cream = imagecolorallocate($im, ...self::CREAM);
        $teal = imagecolorallocate($im, ...self::TEAL);
        $ink = imagecolorallocate($im, ...self::INK);
        $soft = imagecolorallocate($im, ...self::SOFT);

        imagefilledrectangle($im, 0, 0, self::W, self::H, $cream);

        // The band at the foot, as on the default card, so the two read as one
        // family rather than two designs.
        imagefilledrectangle($im, 0, self::H - 78, self::W, self::H, $teal);
        self::centred($im, self::font('Poppins-Medium.ttf'), 17, self::H - 30,
            'THECHURCHOFPEACE.ORG', imagecolorallocate($im, 235, 245, 247), 5.5);

        // The wordmark, small — the sermon is the subject here, not the church.
        self::centred($im, self::font('XtreemMedium.ttf'), 46, 118, 'shalom', $teal);

        $eyebrow = trim(implode('  ·  ', array_filter([
            $sermon->speaker ? mb_strtoupper($sermon->speaker) : null,
            $sermon->sermon_date?->format('M j, Y') ? mb_strtoupper($sermon->sermon_date->format('M j, Y')) : null,
        ])));

        if ($eyebrow !== '') {
            self::centred($im, self::font('Poppins-Medium.ttf'), 15, 172, $eyebrow, $soft, 4.5);
        }

        // Title: the biggest size that fits on at most three lines.
        $font = self::font('CormorantGaramond.ttf');
        $body = self::font('Poppins-Regular.ttf');
        [$lines, $size] = self::fitLines($font, $sermon->title, self::W - 220, 3, 92, 46);

        $heart = $sermon->heart_line
            ? self::fitLines($body, $sermon->heart_line, self::W - 260, 2, 27, 21)
            : [[], 0];

        // The block is centred in the room left between the eyebrow and the
        // footer band, so a one-line title and a three-line one both sit
        // properly rather than one of them stranding half the card empty.
        $titleH = count($lines) * $size * 1.16;
        $heartH = $heart[0] ? 56 + count($heart[0]) * $heart[1] * 1.5 : 24;
        $top = 200;
        $bottom = self::H - 78;
        $y = (int) round($top + (($bottom - $top) - ($titleH + $heartH)) / 2 + $size * 0.78);

        foreach ($lines as $line) {
            self::centred($im, $font, $size, $y, $line, $ink);
            $y += (int) round($size * 1.16);
        }

        // A hairline, then the heart line — why anybody would tap.
        $rule = $y - (int) round($size * 0.28);
        imagefilledrectangle($im, (int) (self::W / 2) - 40, $rule, (int) (self::W / 2) + 40, $rule + 2, $teal);

        $hy = $rule + 54;
        foreach ($heart[0] as $line) {
            self::centred($im, $body, $heart[1], $hy, $line, $soft);
            $hy += (int) round($heart[1] * 1.5);
        }

        imagepng($im, $to, 8);
        imagedestroy($im);
    }

    /** Draw one line centred on the canvas, with optional letter-spacing. */
    private static function centred($im, string $font, float $size, int $y, string $text, int $colour, float $track = 0): void
    {
        if ($track <= 0) {
            $box = imagettfbbox($size, 0, $font, $text);
            $x = (int) ((self::W - ($box[2] - $box[0])) / 2);
            imagettftext($im, $size, 0, $x, $y, $colour, $font, $text);

            return;
        }

        // GD has no tracking, so letter-spaced text is drawn a glyph at a time.
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $width = 0;
        foreach ($chars as $c) {
            $b = imagettfbbox($size, 0, $font, $c);
            $width += ($b[2] - $b[0]) + $track;
        }

        $x = (self::W - $width) / 2;
        foreach ($chars as $c) {
            imagettftext($im, $size, 0, (int) round($x), $y, $colour, $font, $c);
            $b = imagettfbbox($size, 0, $font, $c);
            $x += ($b[2] - $b[0]) + $track;
        }
    }

    /**
     * The largest size at which the text fits the given width in at most
     * $maxLines lines. Long titles shrink rather than overflow or get cut.
     *
     * @return array{0: string[], 1: float}
     */
    private static function fitLines(string $font, string $text, int $width, int $maxLines, float $from, float $to): array
    {
        for ($size = $from; $size >= $to; $size -= 2) {
            $lines = self::wrap($font, $size, $text, $width);
            if (count($lines) <= $maxLines) {
                return [$lines, $size];
            }
        }

        return [array_slice(self::wrap($font, $to, $text, $width), 0, $maxLines), $to];
    }

    /** @return string[] */
    private static function wrap(string $font, float $size, string $text, int $width): array
    {
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/u', trim($text)) as $word) {
            $try = $line === '' ? $word : $line.' '.$word;
            $box = imagettfbbox($size, 0, $font, $try);

            if (($box[2] - $box[0]) > $width && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $try;
            }
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }
}
