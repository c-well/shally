<?php
namespace App\Services\Intake;

use App\Models\IntakeSubmission;

/**
 * Renders a 1920x1080 graduation slide (PNG) for ProPresenter.
 *
 * Editorial, not decorative: robust type, hard restraint, space as the hero.
 * No frames, no drop shadows, no rounded "cards" — left-aligned, generous
 * margins, a single quiet accent. IBM Plex Serif (even color at every size,
 * unlike a delicate display serif) for the name; Poppins for the small set
 * matter. Pure GD + bundled fonts.
 *
 * $style: 'serif' (name set in Plex Serif) or 'sans' (name set in Poppins).
 */
class GradCardRenderer
{
    private const W = 1920;
    private const H = 1080;
    private const MARGIN = 150;

    private array $palette = [
        'parchment' => [249, 246, 236],
        'ink'       => [28, 33, 44],
        'ink_soft'  => [108, 114, 126],
        'ink_faint' => [150, 154, 163],
        'teal'      => [47, 107, 107],
        'line'      => [222, 215, 198],
    ];

    private string $serif, $serifMed, $serifSemi, $serifItalic, $sans, $sansMed, $sansReg;
    private string $style;

    public function __construct(string $style = 'sans')
    {
        $b = storage_path('fonts');
        $this->serif       = $b . '/IBMPlexSerif-Regular.ttf';
        $this->serifMed    = $b . '/IBMPlexSerif-Medium.ttf';
        $this->serifSemi   = $b . '/IBMPlexSerif-SemiBold.ttf';
        $this->serifItalic = $b . '/IBMPlexSerif-Italic.ttf';
        $this->sans        = $b . '/Poppins-SemiBold.ttf';
        $this->sansMed     = $b . '/Poppins-Medium.ttf';
        $this->sansReg     = $b . '/Poppins-Regular.ttf';
        $this->style       = $style;
    }

    public function render(IntakeSubmission $sub): string
    {
        $im = imagecreatetruecolor(self::W, self::H);
        imagealphablending($im, true);
        imagefill($im, 0, 0, $this->c($im, 'parchment'));

        $photo = $sub->photo_path ? $this->loadPhoto(public_path($sub->photo_path)) : null;

        $M = self::MARGIN;
        if ($photo && ! $sub->show_text) {
            // Photo only — clean, generous, no chrome.
            $this->placePhoto($im, $photo, $M, 110, self::W - $M * 2, self::H - 220);
        } elseif ($photo) {
            // Always photo-left / text-right; the photo BLOCK takes the shape of the
            // photo (so a horizontal shot isn't cropped to a portrait), and the
            // right-hand text simply gets whatever width is left — it always
            // centres in the full height, so it never overflows.
            $ar = imagesx($photo) / max(1, imagesy($photo));
            if ($ar >= 1.25)      { $pw = 820; $ph = 500; }  // landscape — wide & short
            elseif ($ar >= 0.95)  { $pw = 620; $ph = 620; }  // square
            else                  { $pw = 540; $ph = 760; }  // portrait — tall & narrow
            $px = $M; $py = (int) ((self::H - $ph) / 2);
            $this->placePhoto($im, $photo, $px, $py, $pw, $ph);
            $this->drawText($im, $sub, $px + $pw + 110, self::W - $M, 110, self::H - 110);
        } else {
            // No photo — text only, comfortable measure, lots of space.
            $this->drawText($im, $sub, $M, min(self::W - $M, $M + 1180), 110, self::H - 110);
        }

        if ($photo) imagedestroy($photo);
        $this->footer($im);

        $dir = public_path('intake-media/grad');
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        $rel = 'intake-media/grad/' . $sub->id . '.png';
        imagepng($im, public_path($rel), 6);
        imagedestroy($im);
        return $rel;
    }

    /* ─────────────────────── text block ─────────────────────── */

    private function drawText($im, IntakeSubmission $sub, int $left, int $right, int $yTop = 110, int $yBottom = 970): void
    {
        $maxW = $right - $left;
        // Class year is EDITABLE on the submission (admin gallery), defaulting to the
        // year they submitted. The old month-heuristic stamped July+ renders "2027" —
        // wrong for June/July graduates and impossible to correct.
        $year = (int) ($sub->value('class_year') ?: ($sub->created_at?->year ?? now()->year));

        $name   = $this->clean($sub->value('name')) ?: 'Graduate';
        $level  = $this->clean($sub->value('level'));
        $school = $this->clean($sub->value('school'));
        $degree = $this->clean($sub->value('major') ?: $sub->value('degree'));
        $honors = $this->clean($sub->value('honors'));
        $thanks = $this->clean($sub->value('thanks') ?: $sub->value('verse'));

        $serifName = $this->style === 'serif';
        $nameFont  = $serifName ? $this->serifMed : $this->sans;
        $nameSize  = $serifName ? 104 : 90;

        // Build a flat list of [type, text, font, size, colorKey, advance].
        // Advances are generous on purpose — space is the point.
        $L = [];
        $L[] = ['label', 'THE CHURCH OF PEACE', $this->sans, 21, 'teal', 78];
        foreach ($this->wrap($name, $nameFont, $nameSize, $maxW) as $ln) {
            $L[] = ['plain', $ln, $nameFont, $nameSize, 'ink', (int) ($nameSize * 1.14)];
        }
        $L[] = ['gap', '', $nameFont, 0, 'ink', 40];
        $L[] = ['plain', 'Class of ' . $year, $serifName ? $this->serif : $this->sansReg, 40, 'ink_soft', 66];

        $line2 = trim($level . ($school ? '  ·  ' . $school : ''), ' ·');
        if ($line2) foreach ($this->wrap($line2, $this->sansReg, 26, $maxW) as $ln) {
            $L[] = ['plain', $ln, $this->sansReg, 26, 'ink_soft', 50];
        }
        if ($degree) foreach ($this->wrap($degree, $this->sansMed, 29, $maxW) as $ln) {
            $L[] = ['plain', $ln, $this->sansMed, 29, 'ink', 54];
        }
        if ($honors) foreach ($this->wrap($honors, $this->serifItalic, 30, $maxW) as $ln) {
            $L[] = ['plain', $ln, $this->serifItalic, 30, 'ink_soft', 56];
        }
        if ($thanks) {
            $L[] = ['gap', '', $nameFont, 0, 'ink', 56];
            $L[] = ['rule', '', $nameFont, 0, 'line', 38];
            $L[] = ['label', 'WITH THANKS', $this->sans, 14, 'teal', 48];
            foreach ($this->wrap('“' . $thanks . '”', $this->serifItalic, 31, $maxW) as $ln) {
                $L[] = ['plain', $ln, $this->serifItalic, 31, 'ink', 56];
            }
        }

        $total = 0; foreach ($L as $l) $total += $l[5];
        $region = $yBottom - $yTop;
        $y = $yTop + ($total < $region ? (int) (($region - $total) / 2) : 0);

        foreach ($L as [$kind, $text, $font, $size, $ck, $adv]) {
            $y += $adv;
            if ($kind === 'gap') continue;
            if ($kind === 'rule') { imagesetthickness($im, 2); imageline($im, $left, $y - 14, $left + 70, $y - 14, $this->c($im, 'line')); imagesetthickness($im, 1); continue; }
            if ($kind === 'label') { $this->tracked($im, $size, $left, $y, $this->c($im, $ck), $font, $text, $size * 0.30); continue; }
            imagettftext($im, $size, 0, $left, $y, $this->c($im, $ck), $font, $text);
        }
    }

    private function footer($im): void
    {
        $txt = 'SHALOM SDA   ·   THE CHURCH OF PEACE';
        $this->tracked($im, 13, self::MARGIN, self::H - 70, $this->c($im, 'ink_faint'), $this->sans, $txt, 4.5);
    }

    /* ─────────────────────── photo ─────────────────────── */

    private function placePhoto($im, $photo, int $x, int $y, int $w, int $h): void
    {
        $cropped = $this->coverCrop($photo, $w, $h);
        imagecopy($im, $cropped, $x, $y, 0, 0, $w, $h);
        imagedestroy($cropped);
        // One hairline, nothing else.
        imagesetthickness($im, 1);
        imagerectangle($im, $x, $y, $x + $w, $y + $h, $this->c($im, 'line'));
    }

    private function coverCrop($src, int $tw, int $th)
    {
        $sw = imagesx($src); $sh = imagesy($src);
        $tAR = $tw / $th; $sAR = $sw / $sh;
        if ($sAR > $tAR) { $cropH = $sh; $cropW = (int) round($sh * $tAR); $sx = (int) (($sw - $cropW) / 2); $sy = 0; }
        else            { $cropW = $sw; $cropH = (int) round($sw / $tAR); $sx = 0; $sy = (int) (($sh - $cropH) / 2); }
        $dst = imagecreatetruecolor($tw, $th);
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $cropW, $cropH);
        return $dst;
    }

    private function loadPhoto(string $path): mixed
    {
        if (! is_file($path)) return null;
        $info = @getimagesize($path);
        $img = $info ? match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default        => null,
        } : null;
        if (! $img) { $jpg = $this->convertViaCli($path); if ($jpg) { $img = @imagecreatefromjpeg($jpg); @unlink($jpg); } }
        if ($img) $img = $this->applyExif($img, $path);
        return $img ?: null;
    }

    private function convertViaCli(string $path): ?string
    {
        // shell_exec is disabled on the web SAPI; proc_open (Process) is not.
        foreach (['/usr/bin/magick', '/usr/bin/convert'] as $bin) {
            if (! is_file($bin)) continue;
            $out = sys_get_temp_dir() . '/intake_' . uniqid() . '.jpg';
            try {
                \Illuminate\Support\Facades\Process::timeout(60)->run(escapeshellarg($bin) . ' ' . escapeshellarg($path) . '[0] ' . escapeshellarg($out));
            } catch (\Throwable $e) {
                continue;
            }
            if (is_file($out) && filesize($out) > 0) return $out;
        }
        return null;
    }

    private function applyExif($img, string $path)
    {
        if (! function_exists('exif_read_data')) return $img;
        $o = (@exif_read_data($path)['Orientation']) ?? 0;
        if ($o === 3) return imagerotate($img, 180, 0);
        if ($o === 6) return imagerotate($img, -90, 0);
        if ($o === 8) return imagerotate($img, 90, 0);
        return $img;
    }

    /**
     * Strip emoji/pictographs before drawing. The card fonts (Plex/Poppins TTF)
     * have no emoji glyphs, so GD renders them as garbled boxes — Andre saw
     * "winding characters" on Melody's card (🙌🏾 in the thanks line). The
     * submission's stored text keeps its emoji; only the render is cleaned.
     */
    private function clean(mixed $text): string
    {
        $t = trim((string) $text);
        if ($t === '') return '';
        $t = (string) preg_replace(
            '/[\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}\x{200D}\x{20E3}\x{2190}-\x{21FF}\x{2300}-\x{23FF}]/u',
            '',
            $t
        );
        // collapse whitespace + orphaned space-before-punctuation left by removals
        $t = (string) preg_replace('/\s{2,}/', ' ', $t);
        $t = (string) preg_replace('/\s+([.,;:!?])/', '$1', $t);
        return trim($t);
    }

    /* ─────────────────────── type helpers ─────────────────────── */

    private function tracked($im, float $size, int $x, int $y, int $color, string $font, string $text, float $tracking): int
    {
        $cx = $x;
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ch) {
            imagettftext($im, $size, 0, (int) $cx, $y, $color, $font, $ch);
            $cx += $this->adv($size, $font, $ch) + $tracking;
        }
        return (int) $cx;
    }

    private function adv(float $size, string $font, string $ch): float
    {
        if ($ch === ' ') return $size * 0.32;
        $bb = imagettfbbox($size, 0, $font, $ch);
        return $bb[2] - $bb[0];
    }

    private function wrap(string $text, string $font, int $size, int $maxW): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = []; $cur = '';
        foreach ($words as $word) {
            $try = $cur === '' ? $word : $cur . ' ' . $word;
            $bb = imagettfbbox($size, 0, $font, $try);
            if (($bb[2] - $bb[0]) > $maxW && $cur !== '') { $lines[] = $cur; $cur = $word; }
            else $cur = $try;
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
