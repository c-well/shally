<?php
namespace App\Http\Controllers;

use App\Models\MusicDay;
use App\Models\MusicEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * The singer's side of music day — the link André texts out.
 *
 * Nobody signs in. The long random token in the URL is the permission, the
 * same way a handout works. Spam defence mirrors /connect and /prayer:
 * honeypot middleware on the route, plus a timing token so a form posted
 * faster than a person could fill it is quietly dropped.
 *
 * YouTube links are not fetched here. The web process cannot run yt-dlp —
 * exec is disabled on the pool — so the link is queued and music:fetch pulls
 * the audio within the minute, the same pattern as the mail room.
 */
class MusicController extends Controller
{
    private const YOUTUBE = '~^https?://(www\.|m\.|music\.)?(youtube\.com/(watch\?|shorts/|live/)|youtu\.be/)~i';

    public function show(string $token)
    {
        $day = MusicDay::where('token', $token)->firstOrFail();

        return view('music.submit', [
            'day'         => $day,
            'renderToken' => Crypt::encryptString((string) time()),
        ]);
    }

    public function store(Request $request, string $token)
    {
        $day = MusicDay::where('token', $token)->firstOrFail();
        $back = redirect()->route('music.show', $token);

        if (! $day->is_open) {
            return $back->withErrors(['name' => 'Song submissions for this day are closed.']);
        }

        $silent = fn (string $why) => tap($back->with('sent', true), fn () =>
            Log::info('music entry rejected', ['reason' => $why, 'ip' => $request->ip()]));

        try {
            $elapsed = time() - (int) Crypt::decryptString((string) $request->input('rendered_at', ''));
            if ($elapsed < 3 || $elapsed > 21600) return $silent("timing.$elapsed");
        } catch (\Throwable $e) {
            return $silent('token.invalid');
        }

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'song_title' => ['nullable', 'string', 'max:200'],
            'method'     => ['required', 'in:' . implode(',', MusicEntry::METHODS)],
            'source_url' => ['required_if:method,youtube', 'nullable', 'url', 'max:500', 'regex:' . self::YOUTUBE],
            'track'      => ['required_if:method,upload', 'nullable', 'file', 'max:51200',
                             'mimes:' . implode(',', MusicEntry::AUDIO_EXT)],
            'medium'     => ['required_if:method,bringing', 'nullable', 'in:' . implode(',', MusicEntry::MEDIUMS)],
        ], [
            'source_url.regex'       => 'That does not look like a YouTube link. Copy it from the Share button on the video.',
            'source_url.required_if' => 'Paste the YouTube link for your song.',
            'track.required_if'      => 'Choose the track file to upload.',
            'track.max'              => 'That file is over 50MB. Send an MP3 instead, or bring it on a USB.',
            'track.mimes'            => 'That is not an audio file. MP3, M4A, WAV or AAC work.',
            'medium.required_if'     => 'Let us know if it is a CD or a USB stick.',
        ]);

        /* Uploads and YouTube links can fill the title in themselves; a CD cannot. */
        if ($data['method'] === 'bringing' && empty($data['song_title'])) {
            return $back->withErrors(['song_title' => 'Tell us the name of the song.'])->withInput();
        }

        $entry = MusicEntry::create([
            'music_day_id' => $day->id,
            'name'         => trim($data['name']),
            'song_title'   => trim((string) ($data['song_title'] ?? '')) ?: null,
            'method'       => $data['method'],
            'medium'       => $data['method'] === 'bringing' ? $data['medium'] : null,
            'source_url'   => $data['method'] === 'youtube' ? $data['source_url'] : null,
            'status'       => match ($data['method']) {
                'youtube'  => 'queued',
                'bringing' => 'bringing',
                'upload'   => 'ready',
            },
            'sort_order'   => (int) $day->entries()->max('sort_order') + 1,
            'ip_hash'      => hash('sha256', (string) $request->ip()),
        ]);

        if ($data['method'] === 'upload') {
            $file = $request->file('track');
            $ext  = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'mp3');
            if (! in_array($ext, MusicEntry::AUDIO_EXT, true)) $ext = 'mp3';

            if (! is_dir($day->dir())) mkdir($day->dir(), 0750, true);
            $file->move($day->dir(), $entry->id . '.' . $ext);

            /* Song title falls back to the file name, minus the extension. */
            $entry->update([
                'file_path'     => $day->dir() . '/' . $entry->id . '.' . $ext,
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
                'size_bytes'    => filesize($day->dir() . '/' . $entry->id . '.' . $ext) ?: null,
                'song_title'    => $entry->song_title
                    ?: mb_substr(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 0, 200),
            ]);
        }

        return $back->with('sent', true)->with('sent_name', $entry->name)->with('sent_method', $entry->method);
    }
}
