<?php
namespace App\Services\Intake;

use App\Models\IntakeSubmission;

/**
 * Renders a 1920x1080 graduation slide (PNG) for ProPresenter.
 *
 * With a photo: portrait photo on the left, details on the right.
 * Without a photo: details centered.
 * With show_text = false ("remove text"): just the photo, full and clean, so
 * Andre can lay his own text over it in ProPresenter.
 *
 * Pure GD + the bundled brand fonts (storage/fonts). No external services.
 */
class GradCardRenderer
{
    private const W = 1920;
    private const H = 1080;

    // Brand palette (matches the site theme-vars).
    private array $palette = [
        'parchment' => [250, 247, 237],
        'ink'       => [26, 35, 50],
        'ink_soft'  => [97, 105, 120],
        'teal'      => [47, 107, 107],
        'line'      => [223, 216, 198],
        'shadow'    => [200, 192, 172],
    ];

    private string $fontSerif;
    private string $fontSerifItalic;
    private string $fontSans;
    private string $fontSansMed;

    public function __construct()
    {
        $base = storage_path('fonts');
        $this->fontSerif       = $base . '/CormorantGaramond.ttf';
        $this->fontSerifItalic = $base . '/CormorantGaramond-Italic.ttf';
        $this->fontSans        = $base . '/Poppins-SemiBold.ttf';
        $this->fontSansMed     = $base . '/Poppins-Medium.ttf';
    }

    /** Build the slide and return its public-relative path (e.g. intake-media/grad/12.png). */
    public function render(IntakeSubmission $sub): string
    {
        $im = imagecreatetruecolor(self::W, self::H);
        imagealphablending($im, true);
        imagefill($im, 0, 0, $this->c($im, 'parchment'));

        // Elegant double inset frame — the "designed" border.
        $this->frame($im);

        $photo = null;
        if ($sub->photo_path) {
            $photo = $this->loadPhoto(public_path($sub->photo_path));
        }

        $showText = $sub->show_text;

        if ($photo && ! $showText) {
            // Photo only — large, centered, clean.
            $this->placePhoto($im, $photo, 200, 120, self::W - 400, self::H - 240);
        } elseif ($photo) {
            // Photo left, text right.
            $px = 130; $py = 170; $pw = 680; $ph = self::H - 340;
            $this->placePhoto($im, $photo, $px, $py, $pw, $ph);
            $this->drawDetails($im, $sub, $px + $pw + 90, self::W - 130, false);
        } else {
            // No photo — center the details.
            $this->drawDetails($im, $sub, 220, self::W - 220, true);
        }

        if ($photo) imagedestroy($photo);

        // Footer mark.
        $this->footer($im);

        $dir = public_path('intake-media/grad');
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        $rel = 'intake-media/grad/' . $sub->id . '.png';
        imagepng($im, public_path($rel), 6);
        imagedestroy($im);

        return $rel;
    }

    /* ───────────────────────── drawing helpers ───────────────────────── */

    private function frame($im): void
    {
        $line = $this->c($im, 'line');
        imagesetthickness($im, 2);
        imagerectangle($im, 46, 46, self::W - 46, self::H - 46, $line);
        imagerectangle($im, 54, 54, self::W - 54, self::H - 54, $line);
        imagesetthickness($im, 1);
    }

    private function footer($im): void
    {
        $teal = $this->c($im, 'teal');
        $txt = 'SHALOM SDA   ·   THE CHURCH OF PEACE';
        $w = $this->trackedWidth(15, $this->fontSans, $txt, 5);
        $this->tracked($im, 15, (int) ((self::W - $w) / 2), self::H - 78, $teal, $this->fontSans, $txt, 5);
    }

    /** Draw text with per-character tracking (letterspacing). Returns end x. */
    private function tracked($im, float $size, int $x, int $y, int $color, string $font, string $text, float $tracking): int
    {
        $cx = $x;
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            imagettftext($im, $size, 0, (int) $cx, $y, $color, $font, $ch);
            $cx += $this->charAdvance($size, $font, $ch) + $tracking;
        }
        return (int) $cx;
    }

    private function trackedWidth(float $size, string $font, string $text, float $tracking): int
    {
        $w = 0; $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($chars as $ch) $w += $this->charAdvance($size, $font, $ch) + $tracking;
        return (int) max(0, $w - $tracking);
    }

    private function charAdvance(float $size, string $font, string $ch): float
    {
        if ($ch === ' ') return $size * 0.34;
        $bb = imagettfbbox($size, 0, $font, $ch);
        return $bb[2] - $bb[0];
    }

    /**
     * Draw the text block. $left/$right bound the column; $center centers each line.
     */
    private function drawDetails($im, IntakeSubmission $sub, int $left, int $right, bool $center): void
    {
        $maxW = $right - $left;
        $year = now()->month >= 7 ? now()->year + 1 : now()->year;

        $name   = trim((string) $sub->value('name'));
        $level  = trim((string) $sub->value('level'));
        $school = trim((string) $sub->value('school'));
        $degree = trim((string) $sub->value('degree'));
        $honors = trim((string) $sub->value('honors'));
        $thanks = trim((string) $sub->value('thanks'));

        // Measure the whole block first so we can vertically center it.
        $lines = [];
        $lines[] = ['eyebrow', 'THE CHURCH OF PEACE', 22, 'teal', $this->fontSans, 46];
        foreach ($this->wrap($name ?: 'Graduate', $this->fontSerif, 88, $maxW) as $i => $ln) {
            $lines[] = ['name', $ln, 88, 'ink', $this->fontSerif, 96];
        }
        $lines[] = ['class', 'Class of ' . $year, 48, 'teal', $this->fontSerifItalic, 78];

        $sub1 = $level . ($school ? '  ·  ' . $school : '');
        if (trim($sub1, ' ·')) {
            foreach ($this->wrap($sub1, $this->fontSansMed, 28, $maxW) as $ln) {
                $lines[] = ['meta', $ln, 28, 'ink_soft', $this->fontSansMed, 44];
            }
        }
        if ($degree) {
            foreach ($this->wrap($degree, $this->fontSans, 30, $maxW) as $ln) {
                $lines[] = ['degree', $ln, 30, 'ink', $this->fontSans, 46];
            }
        }
        if ($honors) {
            foreach ($this->wrap($honors, $this->fontSerifItalic, 32, $maxW) as $ln) {
                $lines[] = ['honors', $ln, 32, 'ink_soft', $this->fontSerifItalic, 48];
            }
        }
        if ($thanks) {
            $lines[] = ['gap', '', 0, 'line', $this->fontSans, 30];
            $lines[] = ['rule', '', 0, 'line', $this->fontSans, 30];
            $lines[] = ['thankslbl', 'WITH THANKS', 16, 'teal', $this->fontSans, 40];
            foreach ($this->wrap('“' . $thanks . '”', $this->fontSerifItalic, 30, $maxW) as $ln) {
                $lines[] = ['thanks', $ln, 30, 'ink_soft', $this->fontSerifItalic, 46];
            }
        }

        $totalH = 0;
        foreach ($lines as $l) $totalH += $l[5];
        $y = (int) ((self::H - $totalH) / 2);

        foreach ($lines as $l) {
            [$kind, $text, $size, $colorKey, $font, $lh] = $l;
            $y += $lh;  // allocate this line's full height ABOVE its baseline first
            if ($kind === 'gap') { continue; }
            if ($kind === 'rule') {
                $rx = $center ? (int) (self::W / 2 - 70) : $left;
                imagesetthickness($im, 2);
                imageline($im, $rx, $y - 14, $rx + 140, $y - 14, $this->c($im, 'line'));
                imagesetthickness($im, 1);
                continue;
            }
            if ($kind === 'eyebrow' || $kind === 'thankslbl') {
                $tr = $size * 0.28;
                $w = $this->trackedWidth($size, $font, $text, $tr);
                $x = $center ? (int) ((self::W - $w) / 2) : $left;
                $this->tracked($im, $size, $x, $y, $this->c($im, $colorKey), $font, $text, $tr);
            } else {
                $x = $left;
                if ($center) {
                    $bb = imagettfbbox($size, 0, $font, $text);
                    $w = $bb[2] - $bb[0];
                    $x = (int) ((self::W - $w) / 2);
                }
                imagettftext($im, $size, 0, $x, $y, $this->c($im, $colorKey), $font, $text);
            }
        }
    }

    /* ───────────────────────── photo helpers ───────────────────────── */

    private function placePhoto($im, $photo, int $x, int $y, int $w, int $h): void
    {
        $cropped = $this->coverCrop($photo, $w, $h);
        // Soft lift: a faint shadow rectangle behind.
        $sh = $this->c($im, 'shadow');
        imagefilledrectangle($im, $x + 10, $y + 14, $x + $w + 10, $y + $h + 14, $sh);
        imagecopy($im, $cropped, $x, $y, 0, 0, $w, $h);
        imagedestroy($cropped);
        // Round the corners against the parchment + cut the shadow's exposed corners too.
        $this->cutRoundedCorners($im, $x, $y, $w, $h, 28, 'parchment');
        // Thin frame line on the straight edges for definition.
        $ln = $this->c($im, 'line');
        imagesetthickness($im, 2);
        imageline($im, $x + 28, $y, $x + $w - 28, $y, $ln);
        imageline($im, $x + 28, $y + $h, $x + $w - 28, $y + $h, $ln);
        imageline($im, $x, $y + 28, $x, $y + $h - 28, $ln);
        imageline($im, $x + $w, $y + 28, $x + $w, $y + $h - 28, $ln);
        imagesetthickness($im, 1);
    }

    private function coverCrop($src, int $tw, int $th)
    {
        $sw = imagesx($src); $sh = imagesy($src);
        $targetAR = $tw / $th;
        $srcAR = $sw / $sh;
        if ($srcAR > $targetAR) {
            // Source too wide — crop the sides.
            $cropH = $sh;
            $cropW = (int) round($sh * $targetAR);
            $srcX = (int) (($sw - $cropW) / 2);
            $srcY = 0;
        } else {
            // Source too tall — crop top/bottom.
            $cropW = $sw;
            $cropH = (int) round($sw / $targetAR);
            $srcX = 0;
            $srcY = (int) (($sh - $cropH) / 2);
        }
        $dst = imagecreatetruecolor($tw, $th);
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $tw, $th, $cropW, $cropH);
        return $dst;
    }

    private function cutRoundedCorners($im, int $x, int $y, int $w, int $h, int $r, string $bgKey): void
    {
        $bg = $this->c($im, $bgKey);
        $corners = [
            [$x, $y, $x + $r, $y + $r],                 // TL center at (x+r,y+r)
            [$x + $w - $r, $y, $x + $w, $y + $r],       // TR center (x+w-r, y+r)
            [$x, $y + $h - $r, $x + $r, $y + $h],       // BL
            [$x + $w - $r, $y + $h - $r, $x + $w, $y + $h], // BR
        ];
        $centers = [
            [$x + $r, $y + $r], [$x + $w - $r, $y + $r],
            [$x + $r, $y + $h - $r], [$x + $w - $r, $y + $h - $r],
        ];
        foreach ($corners as $i => $box) {
            [$cx, $cy] = $centers[$i];
            for ($px = $box[0]; $px < $box[2]; $px++) {
                for ($py = $box[1]; $py < $box[3]; $py++) {
                    $dx = $px - $cx; $dy = $py - $cy;
                    if (($dx * $dx + $dy * $dy) > $r * $r) {
                        imagesetpixel($im, $px, $py, $bg);
                    }
                }
            }
        }
    }

    private function loadPhoto(string $path): mixed
    {
        if (! is_file($path)) return null;
        $info = @getimagesize($path);
        $img = null;
        if ($info) {
            $img = match ($info[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
                IMAGETYPE_PNG  => @imagecreatefrompng($path),
                IMAGETYPE_GIF  => @imagecreatefromgif($path),
                IMAGETYPE_WEBP => @imagecreatefromwebp($path),
                default        => null,
            };
        }
        if (! $img) {
            // Last resort: try an ImageMagick CLI conversion (HEIC etc.) if present.
            $jpg = $this->convertViaCli($path);
            if ($jpg) { $img = @imagecreatefromjpeg($jpg); @unlink($jpg); }
        }
        if ($img) {
            // Honor EXIF orientation for phone photos.
            $img = $this->applyExifOrientation($img, $path);
        }
        return $img ?: null;
    }

    private function convertViaCli(string $path): ?string
    {
        foreach (['magick', 'convert'] as $bin) {
            $which = trim((string) @shell_exec('command -v ' . $bin . ' 2>/dev/null'));
            if ($which) {
                $out = sys_get_temp_dir() . '/intake_' . uniqid() . '.jpg';
                @shell_exec(escapeshellarg($which) . ' ' . escapeshellarg($path) . '[0] ' . escapeshellarg($out) . ' 2>/dev/null');
                if (is_file($out) && filesize($out) > 0) return $out;
            }
        }
        return null;
    }

    private function applyExifOrientation($img, string $path)
    {
        if (! function_exists('exif_read_data')) return $img;
        $exif = @exif_read_data($path);
        $o = $exif['Orientation'] ?? 0;
        if ($o === 3) return imagerotate($img, 180, 0);
        if ($o === 6) return imagerotate($img, -90, 0);
        if ($o === 8) return imagerotate($img, 90, 0);
        return $img;
    }

    /* ───────────────────────── text wrapping ───────────────────────── */

    private function wrap(string $text, string $font, int $size, int $maxW): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = []; $cur = '';
        foreach ($words as $word) {
            $try = $cur === '' ? $word : $cur . ' ' . $word;
            $bb = imagettfbbox($size, 0, $font, $try);
            if (($bb[2] - $bb[0]) > $maxW && $cur !== '') {
                $lines[] = $cur; $cur = $word;
            } else {
                $cur = $try;
            }
        }
        if ($cur !== '') $lines[] = $cur;
        return $lines ?: [''];
    }

    private function c($im, string $key): int
    {
        [$r, $g, $b] = $this->palette[$key];
        return imagecolorallocate($im, $r, $g, $b);
    }
}
