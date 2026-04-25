<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MediaItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMediaController extends Controller
{
    /** Sources to scan for auto-import on first visit. Each entry: relative-to-public dir + source_tag + kind. */
    private const SCAN_SOURCES = [
        ["dir" => "uploads/sermon-covers", "tag" => "sermon-cover", "kind" => "image"],
        ["dir" => "uploads/sermon-images", "tag" => "sermon-image", "kind" => "image"],
        ["dir" => "uploads/slides",        "tag" => "slide",        "kind" => "image"],
        ["dir" => "flyers",                "tag" => "flyer",        "kind" => "image"],
        ["dir" => "uploads/sermons",       "tag" => "sermon-audio", "kind" => "audio"],
    ];

    /** GET /admin/media — gallery + filters. Auto-imports any orphan files on first visit. */
    public function index(Request $request): View|JsonResponse
    {
        $this->autoImportOrphans();

        $kind = $request->query("kind");  // image | audio | null (all)
        $q    = trim((string) $request->query("q", ""));

        $query = MediaItem::query()->orderByDesc("created_at");
        if ($kind && in_array($kind, ["image", "audio"], true)) $query->where("kind", $kind);
        if ($q !== "") $query->where("original_name", "like", "%" . $q . "%");

        $items = $query->limit(500)->get();

        // JSON mode for the picker modal
        if ($request->wantsJson() || $request->boolean("json")) {
            return response()->json([
                "items" => $items->map(fn ($m) => [
                    "id"            => $m->id,
                    "url"           => $m->url(),
                    "kind"          => $m->kind,
                    "original_name" => $m->original_name,
                    "size_human"    => $m->sizeHuman(),
                    "width"         => $m->width,
                    "height"        => $m->height,
                    "tag"           => $m->source_tag,
                ]),
            ]);
        }

        return view("admin.media.index", [
            "items" => $items,
            "kind"  => $kind,
            "q"     => $q,
            "totals" => [
                "all"   => MediaItem::count(),
                "image" => MediaItem::where("kind", "image")->count(),
                "audio" => MediaItem::where("kind", "audio")->count(),
                "bytes" => (int) MediaItem::sum("size_bytes"),
            ],
        ]);
    }

    /** POST /admin/media — upload a new file straight into the library. */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            "file" => "required|file|max:81920", // 80MB cap (covers audio + image)
        ]);
        $f = $request->file("file");
        $mime = $f->getMimeType() ?: "application/octet-stream";
        $kind = str_starts_with($mime, "audio/") ? "audio" : "image";

        $sub = $kind === "audio" ? "uploads/library-audio" : "uploads/library-images";
        $dir = public_path($sub);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if ($kind === "image") {
            // Normalize via universal GD decoder (handles HEIC if pre-converted client-side)
            $bytes = @file_get_contents($f->getRealPath());
            $img = $bytes ? @imagecreatefromstring($bytes) : false;
            if ($img === false) {
                if ($request->wantsJson()) return response()->json(["error" => "Could not read image"], 422);
                return back()->withErrors(["file" => "Could not read that image. Try saving as JPEG and re-upload."]);
            }
            $w = imagesx($img); $h = imagesy($img);
            if ($w > 1600) {
                $newW = 1600;
                $newH = (int) round($h * ($newW / $w));
                $resized = imagecreatetruecolor($newW, $newH);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
                imagedestroy($img); $img = $resized;
                $w = $newW; $h = $newH;
            }
            $name = \Str::slug(pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME))
                  . "-" . substr(bin2hex(random_bytes(4)), 0, 6) . ".jpg";
            imagejpeg($img, $dir . "/" . $name, 82);
            imagedestroy($img);

            $m = MediaItem::create([
                "kind" => "image",
                "path" => $sub . "/" . $name,
                "original_name" => $f->getClientOriginalName(),
                "mime" => "image/jpeg",
                "width" => $w, "height" => $h,
                "size_bytes" => filesize($dir . "/" . $name),
                "uploaded_by_user_id" => $request->user()?->id,
                "source_tag" => "library",
            ]);
        } else {
            // Audio — stream straight to disk, no transcoding
            $ext = strtolower($f->getClientOriginalExtension() ?: "mp3");
            $name = \Str::slug(pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME))
                  . "-" . substr(bin2hex(random_bytes(4)), 0, 6) . "." . $ext;
            $f->move($dir, $name);

            $m = MediaItem::create([
                "kind" => "audio",
                "path" => $sub . "/" . $name,
                "original_name" => $f->getClientOriginalName(),
                "mime" => $mime,
                "size_bytes" => filesize($dir . "/" . $name),
                "uploaded_by_user_id" => $request->user()?->id,
                "source_tag" => "library",
            ]);
        }

        AuditLog::record(
            event: "media_uploaded",
            userId: $request->user()?->id,
            description: ($request->user()->name ?? "?") . " uploaded media: " . $m->original_name,
            meta: ["media_id" => $m->id, "kind" => $m->kind, "size" => $m->size_bytes],
        );

        if ($request->wantsJson()) {
            return response()->json(["item" => [
                "id" => $m->id, "url" => $m->url(), "kind" => $m->kind,
                "original_name" => $m->original_name, "size_human" => $m->sizeHuman(),
            ]]);
        }
        return redirect()->route("admin.media.index")->with("status", "Uploaded: " . $m->original_name);
    }

    /** DELETE /admin/media/{media} — remove from library + delete file from disk if not in use. */
    public function destroy(Request $request, MediaItem $media): RedirectResponse
    {
        $usedIn = $media->usedIn();
        if (!empty($usedIn) && !$request->boolean("force")) {
            return back()->with("status", "In use — refusing to delete: " . implode(", ", $usedIn));
        }
        $abs = public_path($media->path);
        if (is_file($abs)) @unlink($abs);
        $name = $media->original_name;
        $media->delete();
        AuditLog::record(
            event: "media_deleted",
            userId: $request->user()?->id,
            description: ($request->user()->name ?? "?") . " deleted media: " . $name,
        );
        return redirect()->route("admin.media.index")->with("status", "Deleted: " . $name);
    }

    /** Scan known upload directories and register any file not already in media_items. */
    private function autoImportOrphans(): int
    {
        $imported = 0;
        foreach (self::SCAN_SOURCES as $src) {
            $abs = public_path($src["dir"]);
            if (!is_dir($abs)) continue;
            foreach (glob($abs . "/*") as $file) {
                if (!is_file($file)) continue;
                $base = basename($file);
                if ($base === ".htaccess" || str_starts_with($base, ".")) continue;
                $rel = $src["dir"] . "/" . $base;
                if (MediaItem::where("path", $rel)->exists()) continue;

                $size = filesize($file);
                // Avoid mime_content_type() — fileinfo extension not loaded on this server.
                // Images: getimagesize() returns the mime as a side effect.
                // Audio: derive mime from the extension (good enough for catalog purposes).
                $mime = null;
                $w = $h = null;
                if ($src["kind"] === "image") {
                    $info = @getimagesize($file);
                    if ($info) { $w = $info[0]; $h = $info[1]; $mime = $info["mime"] ?? null; }
                } else {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $mime = match ($ext) {
                        "mp3" => "audio/mpeg",
                        "m4a" => "audio/mp4",
                        "wav" => "audio/wav",
                        "ogg" => "audio/ogg",
                        "aac" => "audio/aac",
                        default => "application/octet-stream",
                    };
                }
                MediaItem::create([
                    "kind" => $src["kind"],
                    "path" => $rel,
                    "original_name" => $base,
                    "mime" => $mime,
                    "width" => $w, "height" => $h,
                    "size_bytes" => $size,
                    "uploaded_by_user_id" => null,  // auto-imported, no owning user
                    "source_tag" => $src["tag"],
                ]);
                $imported++;
            }
        }
        return $imported;
    }
}
