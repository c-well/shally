<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin CRUD for the editable public pages (/about, /visit, etc.).
 *
 * Pages are stored in the `pages` table with markdown source + cached HTML.
 * The edit view is a split-pane markdown editor with live preview and inline
 * image upload. See {@see edit()} for the view and the editor JS at
 * resources/views/admin/pages/edit.blade.php.
 */
class AdminPagesController extends Controller
{
    /** GET /admin/pages — list every editable page. */
    public function index(): View
    {
        $pages = Page::orderBy('slug')->get();
        return view('admin.pages.index', compact('pages'));
    }

    /** GET /admin/pages/{slug}/edit — split editor. */
    public function edit(string $slug): View
    {
        $page = Page::where('slug', $slug)->firstOrFail();
        return view('admin.pages.edit', compact('page'));
    }

    /** PATCH /admin/pages/{slug} — save the new body + title. */
    public function update(Request $request, string $slug): RedirectResponse
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'title'   => 'required|string|max:200',
            'eyebrow' => 'nullable|string|max:120',
            'body_md' => 'required|string|max:200000',
        ]);

        $page->fill($data);
        $page->updated_by_user_id = $request->user()->id;
        $page->save();   // booted saving() auto-renders body_html

        AuditLog::record(
            event: 'page_edited',
            userId: $request->user()->id,
            description: $request->user()->name . " edited page /{$page->slug}",
        );

        return redirect()->route('admin.pages.edit', $page->slug)
            ->with('status', "Saved — /{$page->slug} is live.");
    }

    /**
     * POST /admin/pages/upload-image — XHR endpoint the editor calls when an
     * image is dragged/picked. Saves to /public/uploads/pages/, returns
     * {url, alt} so the editor can insert the markdown image line.
     *
     * Uses GD to normalize: any input format (HEIC, PNG, JPG, WEBP) becomes
     * a quality-82 JPEG capped at 1600px wide. Keeps pages fast on mobile.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        // MimeTypeDetector may bark on this server (fileinfo extension missing);
        // validate by extension instead of MIME so the upload still works.
        $request->validate([
            'image' => 'required|file|max:10240|extensions:jpg,jpeg,png,webp,gif,heic,heif',
        ]);

        $file = $request->file('image');
        $dir  = public_path('uploads/pages');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        // Normalize via GD: resize to max 1600w, save as JPEG q82.
        $img = $this->openAsImage($file->getRealPath());
        if (!$img) {
            return response()->json(['error' => 'Could not open image'], 422);
        }

        [$w, $h] = [imagesx($img), imagesy($img)];
        if ($w > 1600) {
            $newW = 1600;
            $newH = (int) round($h * ($newW / $w));
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }

        $filename = \Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.webp';
        $path = $dir . '/' . $filename;
        imagewebp($img, $path, 78);
        imagedestroy($img);

        return response()->json([
            'url' => '/uploads/pages/' . $filename,
            'alt' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);
    }

    /**
     * Open any supported image format as a GD resource. Uses ImageMagick for
     * HEIC/HEIF (iPhone native) since GD doesn't speak those.
     */
    private function openAsImage(string $path): \GdImage|false
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // HEIC/HEIF: convert to JPG first via ImageMagick, then load via GD
        if (in_array($ext, ['heic', 'heif']) || !in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $tmp = tempnam(sys_get_temp_dir(), 'pg') . '.webp';
            exec('/usr/bin/convert ' . escapeshellarg($path) . ' ' . escapeshellarg($tmp), $_, $rc);
            if ($rc !== 0) return false;
            $img = @imagecreatefromjpeg($tmp);
            @unlink($tmp);
            return $img ?: false;
        }

        return match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png'         => @imagecreatefrompng($path),
            'webp'        => @imagecreatefromwebp($path),
            'gif'         => @imagecreatefromgif($path),
            default       => false,
        };
    }
}
