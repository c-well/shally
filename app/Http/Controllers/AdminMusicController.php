<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MusicDay;
use App\Models\MusicEntry;
use Illuminate\Http\Request;

/**
 * André's side of music day: make the day, text the link, watch it fill in,
 * put it in running order, and take every track home in one download.
 */
class AdminMusicController extends Controller
{
    public function index(Request $request)
    {
        $days = MusicDay::withCount('entries')->orderByDesc('service_date')->limit(30)->get();
        $day  = $request->filled('day')
            ? MusicDay::findOrFail((int) $request->input('day'))
            : $days->first();

        return view('admin.music', [
            'days'    => $days,
            'day'     => $day,
            'entries' => $day?->entries()->get() ?? collect(),
        ]);
    }

    /** JSON for the page to poll while YouTube links are still being fetched. */
    public function state(MusicDay $day)
    {
        return response()->json([
            'entries' => $day->entries()->get()->map(fn ($e) => [
                'id'       => $e->id,
                'status'   => $e->status,
                'label'    => $e->statusLabel(),
                'song'     => $e->song_title,
                'duration' => $e->durationLabel(),
                'error'    => $e->error,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_date' => ['required', 'date'],
            'title'        => ['nullable', 'string', 'max:120'],
        ]);

        $day = MusicDay::create([
            'service_date' => $data['service_date'],
            'title'        => trim((string) ($data['title'] ?? '')) ?: 'Music Day',
        ]);

        AuditLog::record(event: 'music_day_created', userId: auth()->id(), description: 'Music day: ' . $day->label());

        return redirect()->route('admin.music', ['day' => $day->id]);
    }

    public function toggle(MusicDay $day)
    {
        $day->update(['is_open' => ! $day->is_open]);
        return response()->json(['ok' => true, 'is_open' => $day->is_open]);
    }

    public function reorder(Request $request, MusicDay $day)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        $own = $day->entries()->pluck('id')->all();

        foreach (array_values(array_intersect($ids, $own)) as $i => $id) {
            MusicEntry::where('id', $id)->update(['sort_order' => $i + 1]);
        }
        return response()->json(['ok' => true]);
    }

    public function update(Request $request, MusicEntry $entry)
    {
        $data = $request->validate([
            'song_title' => ['nullable', 'string', 'max:200'],
            'name'       => ['sometimes', 'string', 'max:120'],
        ]);
        $entry->update($data);
        return response()->json(['ok' => true]);
    }

    /** Try a failed YouTube pull again — it goes back on the queue. */
    public function retry(MusicEntry $entry)
    {
        if ($entry->method !== 'youtube') return response()->json(['ok' => false], 422);
        $entry->update(['status' => 'queued', 'error' => null, 'attempts' => 0]);
        return response()->json(['ok' => true]);
    }

    /** The CD or USB arrived — mark it so it stops showing as outstanding. */
    public function received(MusicEntry $entry)
    {
        if ($entry->method !== 'bringing') return response()->json(['ok' => false], 422);
        $entry->update(['status' => $entry->status === 'ready' ? 'bringing' : 'ready', 'error' => null]);
        return response()->json(['ok' => true, 'status' => $entry->status, 'label' => $entry->statusLabel()]);
    }

    public function destroy(MusicEntry $entry)
    {
        AuditLog::record(event: 'music_entry_deleted', userId: auth()->id(),
            description: "Removed {$entry->name} — " . ($entry->song_title ?: 'untitled'));
        $entry->delete();
        return response()->json(['ok' => true]);
    }

    /** Stream one track. BinaryFileResponse handles Range, so the player can scrub. */
    public function track(MusicEntry $entry)
    {
        abort_unless($entry->hasTrack(), 404);
        return response()->file($entry->file_path, ['Cache-Control' => 'private, max-age=0']);
    }

    public function download(MusicEntry $entry)
    {
        abort_unless($entry->hasTrack(), 404);
        $pos = $entry->day->entries()->pluck('id')->search($entry->id) + 1;
        return response()->download($entry->file_path, $entry->exportName($pos));
    }

    /**
     * Every track for the day in one zip, numbered in running order, so it can
     * go straight onto a USB for the sound desk. Built under the user's own
     * backup dir — /tmp on this box is a small loopback and fills up.
     */
    public function zip(MusicDay $day)
    {
        $entries = $day->entries()->get();
        $ready   = $entries->filter->hasTrack();
        abort_if($ready->isEmpty(), 404, 'No tracks yet.');

        $dir = storage_path('app/music/_zips');
        if (! is_dir($dir)) mkdir($dir, 0750, true);
        $path = $dir . '/' . $day->id . '-' . now()->format('YmdHis') . '.zip';

        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($entries->values() as $i => $e) {
            if ($e->hasTrack()) $zip->addFile($e->file_path, $e->exportName($i + 1));
        }
        $zip->close();

        $name = preg_replace('/[^A-Za-z0-9 -]+/', '', $day->title) . ' ' . $day->service_date->format('Y-m-d') . '.zip';
        return response()->download($path, $name)->deleteFileAfterSend(true);
    }
}
