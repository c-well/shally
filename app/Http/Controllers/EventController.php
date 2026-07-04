<?php
namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
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
            'recur_until' => 'sometimes|nullable|date',
            'recur_times' => 'sometimes|nullable|array',
            'stream_url'  => 'sometimes|nullable|url|max:500',
        ]);
        if (array_key_exists('recur_times', $data)) {
            $data['recur_times'] = $this->cleanRecurTimes($data['recur_times']);
        }
        $event = Event::create([
            'title'         => $data['title'] ?? 'New event',
            'start_at'      => $data['start_at'] ?? now()->next('Saturday')->setTime(10, 30),
            'location'      => $data['location'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'recur_until'   => $data['recur_until'] ?? null,
            'recur_times'   => $data['recur_times'] ?? null,
            'stream_url'    => $data['stream_url'] ?? null,
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
            'recur_until' => 'sometimes|nullable|date',
            'recur_times' => 'sometimes|nullable|array',
            'stream_url'  => 'sometimes|nullable|url|max:500',
        ]);
        if (array_key_exists('recur_times', $data)) {
            $data['recur_times'] = $this->cleanRecurTimes($data['recur_times']);
        }
        $event->update($data);
        return response()->json(['ok' => true, 'event' => $event->fresh('department')]);
    }

    /**
     * POST /events/smart-parse — Andre types the details once (title/date/notes),
     * Claude reads them and fills the recurrence grid. Human reviews, then saves.
     */
    public function smartParse(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:180',
            'date'  => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);
        $apiKey = config('services.anthropic.key');
        abort_unless($apiKey, 503, 'Assistant not configured');

        $text = "Event title: " . ($data['title'] ?? '(none)') . "\n"
              . "Start date: " . ($data['date'] ?? '(none)') . "\n"
              . "Details: " . ($data['notes'] ?? '(none)');

        try {
            $client = new \Anthropic\Client(apiKey: $apiKey);
            $resp = $client->messages->create(
                model: 'claude-haiku-4-5-20251001',
                maxTokens: 500,
                system: [[
                    'type' => 'text',
                    'text' => "You extract recurring event schedules for a church calendar. Today is " . now('America/New_York')->toDateString() . ". "
                        . "Given an event description, return ONLY a JSON object (no prose, no fences) with keys: "
                        . "recur_until (\"YYYY-MM-DD\" last day of the series, or null if not recurring), "
                        . "times (object mapping weekday numbers 0-6 where 0=Sunday to arrays of times like \"7:30 pm\"; "
                        . "include ONLY weekdays that have services; empty object if not recurring), "
                        . "stream_url (a URL to watch online if one is mentioned, else null). "
                        . "Weekday numbers: 0=Sunday, 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday. NEVER confuse Saturday (6) with Sunday (0). "
                        . "Phrases like \"nightly except Mondays and Thursdays\" mean every day EXCEPT those; days given their own time (e.g. \"Saturdays 10am\") use that time INSTEAD of the nightly time. "
                        . "If a date range like \"July 4th-25th\" has no year, infer it from today and the start date. "
                        . "Example — input: \"nightly 7pm except Tuesdays; Saturdays 10am & 5pm, through Aug 2 2026\" → "
                        . "{\"recur_until\":\"2026-08-02\",\"times\":{\"0\":[\"7:00 pm\"],\"1\":[\"7:00 pm\"],\"3\":[\"7:00 pm\"],\"4\":[\"7:00 pm\"],\"5\":[\"7:00 pm\"],\"6\":[\"10:00 am\",\"5:00 pm\"]},\"stream_url\":null}",
                ]],
                messages: [['role' => 'user', 'content' => $text]],
            );
            $raw = trim($resp->content[0]->text ?? '');
            $raw = preg_replace('/^```(?:json)?|```$/m', '', $raw);
            $out = json_decode(trim($raw), true);
            if (! is_array($out)) return response()->json(['ok' => false, 'error' => 'parse'], 422);

            return response()->json([
                'ok'          => true,
                'recur_until' => $out['recur_until'] ?? null,
                'times'       => $this->cleanRecurTimes(is_array($out['times'] ?? null) ? $out['times'] : null) ?? (object) [],
                'stream_url'  => filter_var($out['stream_url'] ?? null, FILTER_VALIDATE_URL) ?: null,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('events.smart-parse failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'error' => 'api'], 502);
        }
    }

    /** Keep only weekdays 0-6 with non-empty string time lists (max 4 each). */
    private function cleanRecurTimes(?array $raw): ?array
    {
        if (! $raw) return null;
        $out = [];
        foreach ($raw as $k => $times) {
            if (! is_numeric($k) || (int) $k < 0 || (int) $k > 6 || ! is_array($times)) continue;
            $ts = array_values(array_filter(array_map(fn ($t) => trim((string) $t), $times), fn ($t) => $t !== ''));
            if ($ts) $out[(string) (int) $k] = array_slice($ts, 0, 4);
        }
        return $out ?: null;
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
        // exec() is disabled on the web SAPI but proc_open is not — use Process.
        try {
            $p = Process::timeout(120)->run($cmd);
            return $p->successful() && is_file($destPath);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
