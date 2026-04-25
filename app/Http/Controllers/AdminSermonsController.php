<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Sermon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin CRUD for the sermon archive.
 *
 * All forms POST in a single multipart request (audio file + metadata).
 * No fancy chunked upload — sermons are typically 5-30MB so one POST is fine.
 * The 12M post limit set in app/public/.user.ini covers most; bump that if
 * you regularly preach 30+ minute sermons in lossless format.
 */
class AdminSermonsController extends Controller
{
    public function index(): View
    {
        $sermons = Sermon::orderByDesc('preached_on')->get();
        return view('admin.sermons.index', compact('sermons'));
    }

    public function create(): View
    {
        return view('admin.sermons.form', ['sermon' => new Sermon()]);
    }

    public function edit(Sermon $sermon): View
    {
        return view('admin.sermons.form', compact('sermon'));
    }

    public function store(Request $request): RedirectResponse
    {
        @set_time_limit(180);
        $data = $this->validateFields($request);
        $sermon = new Sermon();
        $this->saveSermon($request, $sermon, $data, true);

        AuditLog::record(
            event: 'sermon_created',
            userId: $request->user()->id,
            description: $request->user()->name . " added sermon: {$sermon->title}",
        );

        return redirect()->route('admin.sermons.index')->with('status', "Added: {$sermon->title}");
    }

    public function update(Request $request, Sermon $sermon): RedirectResponse
    {
        @set_time_limit(180);
        $data = $this->validateFields($request);
        $this->saveSermon($request, $sermon, $data, false);

        AuditLog::record(
            event: 'sermon_updated',
            userId: $request->user()->id,
            description: $request->user()->name . " updated sermon: {$sermon->title}",
        );

        return redirect()->route('admin.sermons.edit', $sermon)->with('status', "Saved: {$sermon->title}");
    }

    public function destroy(Request $request, Sermon $sermon): RedirectResponse
    {
        $title = $sermon->title;

        // Remove audio file from disk
        if ($sermon->audio_path && is_file(public_path($sermon->audio_path))) {
            @unlink(public_path($sermon->audio_path));
        }
        $sermon->delete();

        AuditLog::record(
            event: 'sermon_deleted',
            userId: $request->user()->id,
            description: $request->user()->name . " deleted sermon: {$title}",
        );

        return redirect()->route('admin.sermons.index')->with('status', "Deleted: {$title}");
    }

    /** Shared validation rules. Audio is optional on update (keep existing file). */
    private function validateFields(Request $request): array
    {
        return $request->validate([
            'title'          => 'required|string|max:200',
            'header_text'    => 'nullable|string|max:200',
            'speaker'        => 'nullable|string|max:120',
            'preached_on'    => 'required|date',
            'scripture_ref'  => 'nullable|string|max:200',
            'description_md' => 'nullable|string|max:50000',
            'video_url'      => 'nullable|url|max:500',
            'audio'          => 'nullable|file|max:81920|extensions:mp3,m4a,wav,ogg,aac',  // 50MB cap
            'cover'          => 'nullable|file|max:10240|extensions:jpg,jpeg,png,webp,gif',  // 10MB cap
            'cover_media_id' => 'nullable|integer|exists:media_items,id',
        ]);
    }

    /** Persist sermon + handle audio + cover uploads. */
    private function saveSermon(Request $request, Sermon $sermon, array $data, bool $audioRequired): void
    {
        $sermon->fill([
            'title'          => $data['title'],
            'header_text'    => $data['header_text'] ?? null,
            'speaker'        => $data['speaker'] ?? null,
            'preached_on'    => $data['preached_on'],
            'scripture_ref'  => $data['scripture_ref'] ?? null,
            'description_md' => $data['description_md'] ?? null,
            'video_url'      => $data['video_url'] ?? null,
            'updated_by_user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('audio')) {
            $f = $request->file('audio');
            $dir = public_path('uploads/sermons');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);

            $ext = strtolower($f->getClientOriginalExtension() ?: 'mp3');
            $filename = \Str::slug(pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME))
                       . '-' . substr(bin2hex(random_bytes(4)), 0, 6)
                       . '.' . $ext;
            $f->move($dir, $filename);

            // If replacing existing audio, delete the old file
            if ($sermon->audio_path && is_file(public_path($sermon->audio_path))) {
                @unlink(public_path($sermon->audio_path));
            }

            $sermon->audio_path       = 'uploads/sermons/' . $filename;
            $sermon->audio_size_bytes = filesize($dir . '/' . $filename);
        } elseif ($audioRequired) {
            abort(422, 'Audio file required for new sermons.');
        }

        // Cover from media library (picker) — takes precedence over a fresh upload.
        // We REFERENCE the path directly; deleting the media item later will break the
        // sermon's cover, but the media library shows usage so the admin gets a warning.
        if (!empty($data['cover_media_id'])) {
            $media = \App\Models\MediaItem::find($data['cover_media_id']);
            if ($media && $media->kind === 'image') {
                $sermon->cover_path = $media->path;
            }
        }
        // Cover image upload — normalize via GD (max 1600w, JPEG q82). Only when picker not used.
        elseif ($request->hasFile('cover')) {
            $cf = $request->file('cover');
            $dir = public_path('uploads/sermon-covers');
            if (!is_dir($dir)) @mkdir($dir, 0755, true);

            // Universal decoder — handles JPEG/PNG/GIF/WebP regardless of extension
            $bytes = @file_get_contents($cf->getRealPath());
            $img = $bytes ? @imagecreatefromstring($bytes) : false;
            if ($img !== false) {
                $w = imagesx($img); $h = imagesy($img);
                if ($w > 1600) {
                    $newW = 1600;
                    $newH = (int) round($h * ($newW / $w));
                    $resized = imagecreatetruecolor($newW, $newH);
                    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
                    imagedestroy($img);
                    $img = $resized;
                }

                $cname = \Str::slug(pathinfo($cf->getClientOriginalName(), PATHINFO_FILENAME))
                       . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.jpg';
                imagejpeg($img, $dir . '/' . $cname, 82);
                imagedestroy($img);

                if ($sermon->cover_path && is_file(public_path($sermon->cover_path))) {
                    @unlink(public_path($sermon->cover_path));
                }
                $sermon->cover_path = 'uploads/sermon-covers/' . $cname;
            }
        }

        $sermon->save();
    }
    /**
     * POST /admin/sermons/upload-image — used by the description editor to embed images.
     * Saves to public/uploads/sermon-images/ and returns the public URL + markdown snippet as JSON.
     */
    public function uploadImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image' => 'required|file|max:10240|extensions:jpg,jpeg,png,webp,gif',
        ]);
        $f = $request->file('image');
        $dir = public_path('uploads/sermon-images');
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        // Universal decoder — handles JPEG/PNG/GIF/WebP regardless of extension.
        $bytes = @file_get_contents($f->getRealPath());
        $img = $bytes ? @imagecreatefromstring($bytes) : false;
        if ($img === false) {
            return response()->json(['error' => 'Could not read image'], 422);
        }

        // Resize if wider than 1200px (sermon body text is ~640px wide; 1200 is plenty for retina)
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w > 1200) {
            $newW = 1200;
            $newH = (int) round($h * ($newW / $w));
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }

        $name = \Str::slug(pathinfo($f->getClientOriginalName(), PATHINFO_FILENAME))
              . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.jpg';
        imagejpeg($img, $dir . '/' . $name, 82);
        imagedestroy($img);

        $url = '/uploads/sermon-images/' . $name;
        return response()->json([
            'url'      => $url,
            'markdown' => '![](' . $url . ')',
        ]);
    }
}
