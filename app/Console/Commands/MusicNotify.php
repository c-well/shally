<?php
namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\MusicDay;
use App\Models\MusicEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The evening email to André about music day songs. Runs at 8pm New York.
 *
 * Two kinds, at most one email per music day per night:
 *
 *   New songs   — the songs that came in since the last email. Sent only on
 *                 evenings when something arrived, so between music days it
 *                 is silent.
 *   The rundown — the night before a music day: the full running order and
 *                 everything still outstanding, so he knows what to chase
 *                 before the service. It replaces that night's "new songs"
 *                 email rather than sending a second one.
 *
 * The address is the AppSetting 'music_notify_email', kept in the database
 * rather than in the code, because it is a person's email. If it is not set,
 * nothing is sent and the scheduler log says so.
 *
 * --dry-run prints the emails instead of sending them and marks nothing.
 * --to=address sends to someone else, for testing, and marks nothing.
 */
class MusicNotify extends Command
{
    protected $signature = 'music:notify {--dry-run} {--to=}';
    protected $description = "Email André the day's new music day songs, and the rundown the night before";

    public function handle(): int
    {
        $to = $this->option('to') ?: AppSetting::get('music_notify_email');
        if (! $to && ! $this->option('dry-run')) {
            $this->warn('music_notify_email is not set, so nothing was sent.');
            return self::SUCCESS;
        }

        $today    = now('America/New_York')->startOfDay();
        $tomorrow = $today->copy()->addDay()->toDateString();
        $sent     = 0;

        /* Upcoming days only. An email at 8pm on the day itself would arrive after the service. */
        $days = MusicDay::where('service_date', '>', $today->toDateString())->orderBy('service_date')->get();

        foreach ($days as $day) {
            $all   = $day->entries()->get();
            $fresh = $all->whereNull('notified_at')->values();

            if ($day->service_date->toDateString() === $tomorrow && ! $day->rundown_sent_at) {
                [$subject, $body] = $this->rundown($day, $all, $fresh);
                if ($this->deliver($to, $subject, $body)) {
                    $sent++;
                    if ($this->marks()) {
                        $day->update(['rundown_sent_at' => now()]);
                        MusicEntry::whereIn('id', $all->pluck('id'))->whereNull('notified_at')->update(['notified_at' => now()]);
                    }
                }
                continue;
            }

            if ($fresh->isEmpty()) {
                continue;
            }

            [$subject, $body] = $this->newSongs($day, $all, $fresh);
            if ($this->deliver($to, $subject, $body)) {
                $sent++;
                if ($this->marks()) {
                    MusicEntry::whereIn('id', $fresh->pluck('id'))->update(['notified_at' => now()]);
                }
            }
        }

        $this->line($sent ? "sent {$sent}" : 'nothing to send');
        return self::SUCCESS;
    }

    /** Only a real run to André marks songs as reported. Tests change nothing. */
    private function marks(): bool
    {
        return ! $this->option('dry-run') && ! $this->option('to');
    }

    private function deliver(?string $to, string $subject, string $body): bool
    {
        if ($this->option('dry-run')) {
            $this->line("──────── to: " . ($to ?: '(not set)') . "\nSubject: {$subject}\n\n{$body}");
            return true;
        }
        try {
            Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
            return true;
        } catch (\Throwable $e) {
            Log::error('music:notify could not send', ['to' => $to, 'error' => $e->getMessage()]);
            $this->error('send failed: ' . $e->getMessage());
            return false;
        }
    }

    private function newSongs(MusicDay $day, Collection $all, Collection $fresh): array
    {
        $n    = $fresh->count();
        $when = $day->service_date->format('l, F j');

        $lines   = [];
        $lines[] = 'Hi André,';
        $lines[] = '';
        $lines[] = ($n === 1 ? '1 song' : "{$n} songs") . " came in for {$day->title} on {$when}:";
        $lines[] = '';
        foreach ($fresh as $e) {
            $lines[] = '  ' . $this->describe($e);
        }

        $trouble = $fresh->where('status', 'failed');
        if ($trouble->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Needs a look:';
            foreach ($trouble as $e) {
                $lines[] = "  {$e->name}: the YouTube link could not be used. " . ($e->error ?: '');
            }
        }

        $lines[] = '';
        $lines[] = 'So far: ' . $this->tally($all) . '.';
        $lines[] = '';
        $lines[] = 'See them all: ' . route('admin.music', ['day' => $day->id]);

        $subject = ($n === 1 ? '1 new song' : "{$n} new songs") . " for {$day->title} ({$day->service_date->format('D M j')})";
        return [$subject, implode("\n", $lines)];
    }

    private function rundown(MusicDay $day, Collection $all, Collection $fresh): array
    {
        $lines   = [];
        $lines[] = 'Hi André,';
        $lines[] = '';
        if ($all->isEmpty()) {
            $lines[] = "{$day->title} is tomorrow, and no songs came in through the link.";
            $lines[] = '';
            $lines[] = 'The page: ' . route('admin.music', ['day' => $day->id]);
            return ["Tomorrow's {$day->title}: no songs came in", implode("\n", $lines)];
        }

        $lines[] = "{$day->title} is tomorrow. Here is the running order:";
        $lines[] = '';
        foreach ($all->values() as $i => $e) {
            $new     = $fresh->contains('id', $e->id) ? '   (new today)' : '';
            $lines[] = sprintf('  %2d. %s%s', $i + 1, $this->describe($e), $new);
        }

        $out = $all->filter(fn ($e) => $e->status !== 'ready');
        if ($out->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Still to get before the service:';
            foreach ($out as $e) {
                $lines[] = '  ' . match ($e->status) {
                    'bringing'           => "{$e->name} is bringing it on " . strtoupper($e->medium ?: 'CD or USB'),
                    'failed'             => "{$e->name}: the YouTube link could not be used. Ask them for the file.",
                    'queued', 'fetching' => "{$e->name}: still being pulled from YouTube",
                    default              => "{$e->name}: " . $e->statusLabel(),
                };
            }
        } else {
            $lines[] = '';
            $lines[] = 'Every track is in.';
        }

        $lines[] = '';
        $lines[] = 'Download all the tracks in running order for the sound desk:';
        $lines[] = route('admin.music', ['day' => $day->id]);

        $subject = "Tomorrow's {$day->title}: " . $all->count() . ' ' . ($all->count() === 1 ? 'song' : 'songs')
            . ($out->isNotEmpty() ? ', ' . $out->count() . ' still to get' : ', all in');
        return [$subject, implode("\n", $lines)];
    }

    private function describe(MusicEntry $e): string
    {
        $song = $e->song_title ? "\"{$e->song_title}\"" : '(no title yet)';
        $how  = match ($e->status) {
            'ready'              => 'track in' . ($e->durationLabel() ? ', ' . $e->durationLabel() : ''),
            'bringing'           => 'bringing it on ' . strtoupper($e->medium ?: 'CD/USB'),
            'queued', 'fetching' => 'being pulled from YouTube',
            'failed'             => 'YouTube link failed',
            default              => $e->status,
        };
        return "{$e->name}: {$song} ({$how})";
    }

    private function tally(Collection $all): string
    {
        $parts = [$all->count() . ' ' . ($all->count() === 1 ? 'singer' : 'singers')];
        $r = $all->where('status', 'ready')->count();
        $b = $all->where('status', 'bringing')->count();
        $f = $all->where('status', 'failed')->count();
        $parts[] = "{$r} " . ($r === 1 ? 'track' : 'tracks') . ' in';
        if ($b) $parts[] = "{$b} bringing it";
        if ($f) $parts[] = "{$f} need a look";
        return implode(', ', $parts);
    }
}
