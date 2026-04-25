<?php
namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'         => 'nullable|string|max:180',
            'start_at'      => 'nullable|date',
            'location'      => 'nullable|string|max:180',
            'notes'         => 'nullable|string|max:1000',
            'department_id' => 'nullable|exists:departments,id',
        ]);
        $event = Event::create([
            'title'         => $data['title'] ?? 'New event',
            'start_at'      => $data['start_at'] ?? now()->next('Saturday')->setTime(10, 30),
            'location'      => $data['location'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'is_public'     => true,
            'created_by'    => $request->user()->id,
        ]);
        return response()->json(['ok' => true, 'event' => $event]);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'title'      => 'sometimes|string|max:180',
            'start_at'   => 'sometimes|date',
            'end_at'     => 'sometimes|nullable|date',
            'location'   => 'sometimes|nullable|string|max:180',
            'notes'      => 'sometimes|nullable|string|max:1000',
            'department_id' => 'sometimes|nullable|exists:departments,id',
            'is_public'  => 'sometimes|boolean',
        ]);
        $event->update($data);
        return response()->json(['ok' => true, 'event' => $event->fresh('department')]);
    }

    public function destroy(Event $event): JsonResponse
    {
        if ($event->flyer_path) {
            $abs = public_path($event->flyer_path);
            if (is_file($abs)) @unlink($abs);
        }
        $event->delete();
        return response()->json(['ok' => true]);
    }

    /** POST /events/{event}/flyer — accept image or PDF, normalize to compressed JPEG. */
    public function uploadFlyer(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'flyer' => ['required', 'file', 'extensions:jpg,jpeg,png,webp,gif,pdf', 'max:10240'], // 10MB
        ]);
        $file = $request->file('flyer');
        $ext = strtolower($file->getClientOriginalExtension());

        $dir = public_path('flyers');
        if (! is_dir($dir)) mkdir($dir, 0755, true);

        if ($event->flyer_path) {
            $old = public_path($event->flyer_path);
            if (is_file($old)) @unlink($old);
        }

        $filename = 'event-' . $event->id . '-' . now()->format('YmdHis') . '-' . Str::random(6) . '.jpg';
        $absOut = $dir . '/' . $filename;

        $ok = $ext === 'pdf'
            ? $this->convertPdfToJpeg($file->getRealPath(), $absOut, 1600, 82)
            : $this->compressToJpeg($file->getRealPath(), $absOut, 1600, 82);

        if (! $ok || ! is_file($absOut)) {
            return response()->json(['ok' => false, 'error' => 'Could not process file'], 422);
        }

        $relPath = 'flyers/' . $filename;
        $event->flyer_path = $relPath;
        $event->save();

        return response()->json(['ok' => true, 'flyer_url' => '/' . $relPath]);
    }

    /** POST /events/{eventId}/restore — undelete a soft-deleted event (for undo). */
    public function restoreEvent(int $eventId): JsonResponse
    {
        $event = Event::withTrashed()->findOrFail($eventId);
        $event->restore();
        return response()->json(['ok' => true, 'event' => $event]);
    }

    public function removeFlyer(Event $event): JsonResponse
    {
        if ($event->flyer_path) {
            $abs = public_path($event->flyer_path);
            if (is_file($abs)) @unlink($abs);
            $event->flyer_path = null;
            $event->save();
        }
        return response()->json(['ok' => true]);
    }

    private function compressToJpeg(string $srcPath, string $destPath, int $maxWidth, int $quality): bool
    {
        $info = @getimagesize($srcPath);
        if (! $info) return false;

        [$w, $h, $type] = $info;
        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($srcPath),
            IMAGETYPE_PNG  => @imagecreatefrompng($srcPath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($srcPath),
            IMAGETYPE_GIF  => @imagecreatefromgif($srcPath),
            default        => null,
        };
        if (! $src) return false;

        if ($w > $maxWidth) {
            $newW = $maxWidth;
            $newH = (int) round($h * ($maxWidth / $w));
            $dst = imagecreatetruecolor($newW, $newH);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($src);
        } else {
            $dst = imagecreatetruecolor($w, $h);
            imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
            imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
            imagedestroy($src);
        }

        $ok = imagejpeg($dst, $destPath, $quality);
        imagedestroy($dst);
        return $ok;
    }

    /** Render PDF page 1 → JPEG via ImageMagick (which uses Ghostscript). */
    private function convertPdfToJpeg(string $srcPath, string $destPath, int $maxWidth, int $quality): bool
    {
        $cmd = sprintf(
            '/usr/bin/convert -density 150 %s -background white -alpha remove -alpha off -resize %dx -quality %d %s 2>&1',
            escapeshellarg($srcPath . '[0]'),
            $maxWidth,
            $quality,
            escapeshellarg($destPath)
        );
        exec($cmd, $output, $code);
        return $code === 0 && is_file($destPath);
    }
}
