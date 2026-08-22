<?php

namespace App\Services;

use App\Models\Handout;
use Illuminate\Support\Facades\Storage;

/**
 * Renders the share preview for a handout — the picture that unfurls when the
 * link is pasted into iMessage, WhatsApp, or Facebook.
 *
 * ── DOCTRINE: the preview looks like the page. ──
 * A generic card — favicon on a coloured square, bare title — tells the person
 * receiving it nothing and looks like spam in a family group chat, which is
 * exactly where these links get pasted. So this draws the real thing: the same
 * parchment, the same theme colour, the same Shalom mark, the same eyebrow and
 * title in the same faces. Somebody who taps through should recognise the page
 * they already saw in the preview. Applies to anything shareable we build.
 *
 * Rendered with GD + FreeType (no Imagick on this box) and cached to disk,
 * keyed on the handout's updated_at, so an edit invalidates it and a scraper
 * hitting the URL a hundred times costs one render.
 */
class HandoutOgImage
{
    private const W = 1200;
    private const H = 630;

    /** Same values as the data-theme blocks in public/css/shalom.css. */
    private const THEMES = [
        'default'      => ['bg' => 'fefcef', 'teal' => '03617A', 'brass' => 'b08d3c'],
        'communion'    => ['bg' => 'f5f0fb', 'teal' => '6b4d8a', 'brass' => '8a6dab'],
        'easter'       => ['bg' => 'f0faf3', 'teal' => '3a8e63', 'brass' => 'c2a652'],
        'christmas'    => ['bg' => 'fbf2f2', 'teal' => '8b3a4b', 'brass' => 'b08d3c'],
        'mothers'      => ['bg' => 'fdf1f5', 'teal' => 'b1657a', 'brass' => 'c8916b'],
        'thanksgiving' => ['bg' => 'fbf3e9', 'teal' => '8a5a2c', 'brass' => 'b08d3c'],
    ];

    private const INK = '1a2332';

    public function path(Handout $handout): string
    {
        return 'handout-og/' . $handout->token . '-' . $handout->updated_at?->timestamp . '.png';
    }

    /** Returns raw PNG bytes, rendering and caching on first request. */
    public function render(Handout $handout): string
    {
        $path = $this->path($handout);
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $disk->get($path);
        }

        $png = $this->draw($handout);

        // Sweep this handout's older renders so an edited handout does not
        // leave a stale image behind for every revision it has ever had.
        foreach ($disk->files('handout-og') as $old) {
            if (str_starts_with(basename($old), $handout->token . '-') && $old !== $path) {
                $disk->delete($old);
            }
        }

        $disk->put($path, $png);

        return $png;
    }

    private function draw(Handout $handout): string
    {
        $t = self::THEMES[$handout->theme] ?? self::THEMES['default'];

        $im = imagecreatetruecolor(self::W, self::H);
        imageantialias($im, true);

        $bg    = $this->color($im, $t['bg']);
        $teal  = $this->color($im, $t['teal']);
        $brass = $this->color($im, $t['brass']);
        $ink   = $this->color($im, self::INK);
        $white = imagecolorallocate($im, 255, 255, 255);

        imagefilledrectangle($im, 0, 0, self::W, self::H, $bg);

        // The card, inset — same white plate the page uses on the tinted ground.
        $pad = 46;
        imagefilledrectangle($im, $pad, $pad, self::W - $pad, self::H - $pad, $white);

        // The ribbon along the card's top edge: teal bleeding into brass, drawn
        // a column at a time because GD has no gradient primitive.
        $rib = 9;
        for ($x = $pad; $x <= self::W - $pad; $x++) {
            $p = ($x - $pad) / (self::W - 2 * $pad);
            $c = imagecolorallocate(
                $im,
                (int) round($this->chan($t['teal'], 0) + $p * ($this->chan($t['brass'], 0) - $this->chan($t['teal'], 0))),
                (int) round($this->chan($t['teal'], 1) + $p * ($this->chan($t['brass'], 1) - $this->chan($t['teal'], 1))),
                (int) round($this->chan($t['teal'], 2) + $p * ($this->chan($t['brass'], 2) - $this->chan($t['teal'], 2)))
            );
            imageline($im, $x, $pad, $x, $pad + $rib, $c);
        }

        // storage/fonts is the repo's font store and is tracked in git.
        // public/ is a SYMLINK to /home/shalom/public_html — outside the repo —
        // so anything dropped there is invisible to version control and would
        // vanish on a fresh deploy, taking the preview's typography with it.
        // Xtreem is the exception: the browser needs it at a public URL for the
        // site header, so it lives there and is mirrored here for GD.
        $fontMark  = $this->font('XtreemMedium.ttf', public_path('fonts/XtreemMedium.ttf'));
        $fontTitle = $this->font('CormorantGaramond.ttf');
        $fontMeta  = $this->font('Poppins-SemiBold.ttf');

        $cx = (int) (self::W / 2);

        // Title first, so the whole block can be measured before anything is
        // drawn — the text block is centred as a unit rather than started at a
        // guessed y, which is what made the eyebrow collide with the title's
        // ascenders when a title wrapped to two lines.
        $size  = 74;
        $lines = $this->wrap($handout->title, $fontTitle, $size, self::W - 300);
        while (count($lines) > 3 && $size > 42) {
            $size -= 6;
            $lines = $this->wrap($handout->title, $fontTitle, $size, self::W - 300);
        }
        $lines = array_slice($lines, 0, 3);

        $eyebrowH = $handout->eyebrow ? 26 : 0;
        $gapAfterEyebrow = $handout->eyebrow ? 52 : 0;
        $lineH    = (int) round($size * 1.16);
        $titleH   = count($lines) * $lineH;
        $ruleGap  = 54;
        $blockH   = $eyebrowH + $gapAfterEyebrow + $titleH + $ruleGap;

        // Centre the block in the space above the mark, not in the whole card.
        $markY  = self::H - 104;
        $top    = $pad + $rib;
        $avail  = ($markY - 76) - $top;
        $y      = $top + (int) (($avail - $blockH) / 2);

        if ($handout->eyebrow) {
            $y += $eyebrowH;
            $this->centeredTracked($im, mb_strtoupper($handout->eyebrow), $fontMeta, 19, 7, $brass, $cx, $y);
            $y += $gapAfterEyebrow;
        }

        // imagettftext draws from the BASELINE, so the first line needs a full
        // line-height added before it, not after.
        foreach ($lines as $line) {
            $y += $lineH;
            $this->centered($im, $line, $fontTitle, $size, $ink, $cx, $y);
        }

        // Brass rule, same as the page's divider under the title.
        $y += $ruleGap;
        imagefilledrectangle($im, $cx - 34, $y, $cx + 34, $y + 2, $brass);

        // The mark, bottom-centre. Lowercase because Xtreem's capital S is the
        // large swooping glyph and the logo uses the compact lowercase s —
        // same reason .site-menu-brand em carries text-transform:lowercase.
        $this->centered($im, 'shalom', $fontMark, 62, $teal, $cx, $markY);

        ob_start();
        imagepng($im, null, 9);
        $out = (string) ob_get_clean();
        imagedestroy($im);

        return $out;
    }

    /** @return string[] */
    private function wrap(string $text, string $font, int $size, int $maxWidth): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $cur   = '';

        foreach ($words as $w) {
            $try = $cur === '' ? $w : $cur . ' ' . $w;
            if ($this->width($try, $font, $size) > $maxWidth && $cur !== '') {
                $lines[] = $cur;
                $cur = $w;
            } else {
                $cur = $try;
            }
        }
        if ($cur !== '') {
            $lines[] = $cur;
        }

        return $lines ?: [$text];
    }

    private function width(string $text, string $font, int $size): int
    {
        $b = imagettfbbox($size, 0, $font, $text);

        return $b ? abs($b[2] - $b[0]) : 0;
    }

    private function centered($im, string $text, string $font, int $size, int $color, int $cx, int $y): void
    {
        imagettftext($im, $size, 0, $cx - (int) ($this->width($text, $font, $size) / 2), $y, $color, $font, $text);
    }

    /** GD has no letter-spacing, so tracked caps are drawn a glyph at a time. */
    private function centeredTracked($im, string $text, string $font, int $size, int $track, int $color, int $cx, int $y): void
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $total = 0;
        foreach ($chars as $c) {
            $total += $this->width($c, $font, $size) + $track;
        }
        $total -= $track;

        $x = $cx - (int) ($total / 2);
        foreach ($chars as $c) {
            imagettftext($im, $size, 0, $x, $y, $color, $font, $c);
            $x += $this->width($c, $font, $size) + $track;
        }
    }

    /**
     * Resolve a face from the tracked font store, falling back to a given path
     * for the one file that has to live in the public docroot.
     */
    private function font(string $name, ?string $fallback = null): string
    {
        $path = storage_path('fonts/' . $name);

        if (is_readable($path)) {
            return $path;
        }
        if ($fallback && is_readable($fallback)) {
            return $fallback;
        }

        throw new \RuntimeException("Missing font for share preview: {$name}");
    }

    private function color($im, string $hex): int
    {
        return imagecolorallocate($im, $this->chan($hex, 0), $this->chan($hex, 1), $this->chan($hex, 2));
    }

    private function chan(string $hex, int $i): int
    {
        return (int) hexdec(substr($hex, $i * 2, 2));
    }
}
