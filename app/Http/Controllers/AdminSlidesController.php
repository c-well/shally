<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSlidesController extends Controller
{
    public function index(): View
    {
        $slides = Slide::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.slides.index', compact('slides'));
    }

    public function store(Request $request): RedirectResponse
    {
        @set_time_limit(120);
        // SLIDE_UPLOAD_DEBUG — log every attempt regardless of validation outcome so we
        // can see what users are actually trying to upload. Remove once we know.
        if ($request->hasFile('image')) {
            $f = $request->file('image');
            \Log::info('Slide upload attempt', [
                'name'      => $f->getClientOriginalName(),
                'ext'       => $f->getClientOriginalExtension(),
                'guessed'   => $f->guessExtension(),
                'size_kb'   => round($f->getSize() / 1024),
                'mime'      => $f->getMimeType(),
                'real_path' => $f->getRealPath(),
                'is_valid'  => $f->isValid(),
            ]);
        }
        $data = $request->validate([
            // Broader list — Affinity, Photoshop, phones, GIMP can all export these.
            'image'       => 'required|file|max:25600|extensions:jpg,jpeg,png,webp,gif,heic,heif,tif,tiff,bmp',
            'caption'     => 'nullable|string|max:200',
            'subcaption'  => 'nullable|string|max:200',
            'link_url'    => 'nullable|url|max:500',
        ]);

        $f = $request->file('image');
        $dir = public_path('uploads/slides');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        // Open the image with format-tolerant fallback. If we can't, return a USER-FRIENDLY
        // message back to the form (NOT a generic 422) so Andre knows what to do next.
        $img = $this->openAsImage($f->getRealPath(), $f->getClientOriginalName());
        if (!$img) {
            $hint = $this->diagnoseUnreadable($f->getRealPath(), $f->getClientOriginalName());
            \Log::warning('Slide upload: image unreadable', [
                'name' => $f->getClientOriginalName(),
                'size' => $f->getSize(),
                'mime' => @mime_content_type($f->getRealPath()) ?: 'unknown',
                'hint' => $hint,
            ]);
            return back()->withInput()->withErrors([
                'image' => "We couldn't process that image. {$hint}",
            ]);
        }
        [$w, $h] = [imagesx($img), imagesy($img)];
        if ($w > 2000) {
            $newW = 2000;
            $newH = (int) round($h * ($newW / $w));
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }
        $filename = \Str::slug(pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME))
                    . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.webp';
        imagewebp($img, $dir . '/' . $filename, 78);
        imagedestroy($img);

        $maxOrder = Slide::max('sort_order') ?? 0;
        Slide::create([
            'image_path' => 'uploads/slides/' . $filename,
            'caption'    => $data['caption'] ?? null,
            'subcaption' => $data['subcaption'] ?? null,
            'link_url'   => $data['link_url'] ?? null,
            'sort_order' => $maxOrder + 1,
            'is_active'  => true,
        ]);

        AuditLog::record(
            event: 'slide_added',
            userId: $request->user()->id,
            description: $request->user()->name . ' added a slide',
        );

        return back()->with('status', 'Slide added.');
    }

    public function update(Request $request, Slide $slide): RedirectResponse
    {
        $data = $request->validate([
            'caption'    => 'nullable|string|max:200',
            'subcaption' => 'nullable|string|max:200',
            'link_url'   => 'nullable|url|max:500',
            'is_active'  => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0|max:9999',
        ]);
        $slide->fill($data)->save();
        return back()->with('status', 'Saved.');
    }

    public function destroy(Request $request, Slide $slide): RedirectResponse
    {
        if ($slide->image_path && is_file(public_path($slide->image_path))) {
            @unlink(public_path($slide->image_path));
        }
        $slide->delete();
        AuditLog::record(
            event: 'slide_deleted',
            userId: $request->user()->id,
            description: $request->user()->name . ' deleted a slide',
        );
        return back()->with('status', 'Deleted.');
    }

    /**
     * Open an uploaded file as a GD image, format-tolerant.
     * Strategy: try imagecreatefromstring first (auto-detects JPEG/PNG/GIF/WebP from magic bytes,
     * works regardless of extension — handles iPhone "JPEG" files that are actually HEIC, etc.).
     * If that fails, try the extension-matched function as a last resort.
     */
    private function openAsImage(string $path, string $originalName = ''): \GdImage|false
    {
        // Primary: imagecreatefromstring is GD's universal opener. Reads magic bytes,
        // detects format, returns GdImage. Doesn't trust the file extension at all.
        $bytes = @file_get_contents($path);
        if ($bytes !== false && strlen($bytes) > 0) {
            $img = @imagecreatefromstring($bytes);
            if ($img !== false) return $img;
        }

        // Secondary: try the extension-matched function (in case magic bytes are off)
        $ext = strtolower(pathinfo($originalName ?: $path, PATHINFO_EXTENSION));
        return match ($ext) {
            "jpg", "jpeg" => @imagecreatefromjpeg($path),
            "png"         => @imagecreatefrompng($path),
            "webp"        => @imagecreatefromwebp($path),
            "gif"         => @imagecreatefromgif($path),
            default       => false,
        };
    }

    /**
     * Sniff what's wrong with an unreadable upload, return a user-friendly hint.
     * Reads the first 16 bytes to detect HEIC/AVIF/etc. — formats GD cannot decode.
     */
    private function diagnoseUnreadable(string $path, string $originalName): string
    {
        $head = @file_get_contents($path, false, null, 0, 32);
        if ($head === false || strlen($head) < 12) {
            return 'The file may not have uploaded completely. Try again.';
        }
        // HEIC/HEIF: bytes 4..12 contain "ftypheic", "ftypheix", "ftypmif1", "ftypheim"
        $brand = substr($head, 4, 8);
        if (in_array($brand, ["ftypheic", "ftypheix", "ftypmif1", "ftypmsf1", "ftypheim", "ftypheis", "ftyphevc"])) {
            return "That looks like an iPhone HEIC photo. Please save it as JPEG and try again, or use a non-iPhone browser to upload (the page auto-converts HEIC there).";
        }
        // AVIF
        if ($brand === "ftypavif" || $brand === "ftypavis") {
            return "That looks like an AVIF photo, which our server can't process. Please convert to JPEG first.";
        }
        // PDF (sometimes mis-named to .jpg)
        if (substr($head, 0, 4) === "%PDF") {
            return "That looks like a PDF, not an image. Please use Apple Photos or another tool to export a JPEG of the first page.";
        }
        // Tiff / RAW
        if (substr($head, 0, 4) === "II*\x00" || substr($head, 0, 4) === "MM\x00*") {
            return "That looks like a TIFF/RAW photo. Please save it as JPEG first.";
        }
        // CMYK JPEG (start of JPEG, but adobe APP14 marker)
        if (substr($head, 0, 2) === "\xFF\xD8" && str_contains($head, "Adobe")) {
            return "That JPEG appears to use CMYK colors (export setting in Photoshop). Re-save with sRGB color space.";
        }
        return "Unsupported or corrupt image. Please try a different file (JPEG, PNG, WebP, or GIF up to 10MB).";
    }
}
