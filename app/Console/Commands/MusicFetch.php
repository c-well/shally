<?php
namespace App\Console\Commands;

use App\Models\MusicEntry;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Snag's job, done on the server.
 *
 * Snag is the Mac app that wraps yt-dlp and ffmpeg. Both are installed on the
 * VPS, so rather than shipping a Mac app here this does what its engine does:
 * pull the audio from a YouTube link and save it as an MP3.
 *
 * Runs every minute from the scheduler. The web process cannot do this —
 * exec is disabled on the FPM pool — so the singer's form only queues the
 * link and this makes it true.
 *
 * Second job: uploads arrive with no duration, because measuring one needs
 * ffprobe and the web process cannot run it either. This fills them in.
 */
class MusicFetch extends Command
{
    protected $signature = 'music:fetch {--limit=3}';
    protected $description = 'Pull queued YouTube tracks as MP3, and measure uploaded ones';

    private const MAX_ATTEMPTS = 3;
    private const MAX_SECONDS  = 900;

    public function handle(): int
    {
        $ytdlp  = config('music.ytdlp', '/usr/local/bin/yt-dlp');
        $ffdir  = config('music.ffmpeg_dir', '/usr/local/bin');
        $node   = config('music.js_runtime', 'node:/usr/bin/node');

        $queue = MusicEntry::where('status', 'queued')->where('method', 'youtube')
            ->orderBy('id')->limit((int) $this->option('limit'))->get();

        foreach ($queue as $e) {
            $e->update(['status' => 'fetching', 'attempts' => $e->attempts + 1]);
            $dir = $e->day->dir();
            if (! is_dir($dir)) mkdir($dir, 0750, true);

            $proc = new Process([
                $ytdlp, '--no-playlist', '--no-progress', '--quiet', '--no-warnings',
                '--js-runtimes', $node,
                '--ffmpeg-location', $ffdir,
                '--match-filter', 'duration < ' . self::MAX_SECONDS,
                '-x', '--audio-format', 'mp3', '--audio-quality', '0',
                '--no-simulate', '--print', 'after_move:%(title)s',
                '-o', $dir . '/' . $e->id . '.%(ext)s',
                $e->source_url,
            ]);
            $proc->setTimeout(300);
            $proc->run();

            $file = $dir . '/' . $e->id . '.mp3';
            if ($proc->isSuccessful() && is_file($file)) {
                $title = trim(strtok($proc->getOutput(), "\n") ?: '');
                $e->update([
                    'status'           => 'ready',
                    'error'            => null,
                    'file_path'        => $file,
                    'size_bytes'       => filesize($file) ?: null,
                    'duration_seconds' => $this->probe($file, $ffdir),
                    'song_title'       => $e->song_title ?: (mb_substr($title, 0, 200) ?: null),
                ]);
                $this->line("ready  #{$e->id} {$e->name}");
                continue;
            }

            $why = $this->explain($proc->getErrorOutput() . ' ' . $proc->getOutput(), is_file($dir . '/' . $e->id . '.mp3'));
            $e->update([
                'status' => $e->attempts >= self::MAX_ATTEMPTS ? 'failed' : 'queued',
                'error'  => mb_substr($why, 0, 255),
            ]);
            $this->warn("fail   #{$e->id} attempt {$e->attempts}: {$why}");
        }

        /* Uploads that have not been measured yet. */
        foreach (MusicEntry::where('status', 'ready')->whereNull('duration_seconds')
                     ->whereNotNull('file_path')->limit(20)->get() as $e) {
            if (is_file($e->file_path)) {
                $e->update(['duration_seconds' => $this->probe($e->file_path, $ffdir) ?? 0]);
            }
        }

        return self::SUCCESS;
    }

    private function probe(string $file, string $ffdir): ?int
    {
        $p = new Process([$ffdir . '/ffprobe', '-v', 'error', '-show_entries', 'format=duration',
                          '-of', 'default=nw=1:nk=1', $file]);
        $p->setTimeout(30);
        $p->run();
        $s = (float) trim($p->getOutput());
        return $s > 0 ? (int) round($s) : null;
    }

    /** André reads this on the card, so it says what happened in plain words. */
    private function explain(string $out, bool $partial): string
    {
        $o = strtolower($out);
        return match (true) {
            str_contains($o, 'does not pass filter')           => 'Longer than 15 minutes — is that the right video?',
            str_contains($o, 'private video')                  => 'That video is private.',
            str_contains($o, 'video unavailable'),
            str_contains($o, 'has been removed')               => 'That video is no longer on YouTube.',
            str_contains($o, 'sign in to confirm your age')    => 'YouTube wants a signed-in account for that one. Ask for the file instead.',
            str_contains($o, 'not available in your country')  => 'YouTube blocks that video here. Ask for the file instead.',
            default => 'YouTube would not hand it over. It will try again.',
        };
    }
}
