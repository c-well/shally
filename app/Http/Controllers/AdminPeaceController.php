<?php
namespace App\Http\Controllers;

use App\Models\PeaceQaPair;
use App\Models\PeaceScripture;
use App\Models\PeaceSermon;
use App\Models\PeaceTopic;
use App\Services\Peace\ScriptureValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Admin UI for Peace sermons.
 *
 * Routes:
 *   GET    /admin/peace                              — list all sermons
 *   GET    /admin/peace/schedule                     — automation schedule view
 *   POST   /admin/peace/schedule/trigger             — manual fire of peace:scan-channel
 *   GET    /admin/peace/{slug}/edit                  — edit form for one sermon
 *   POST   /admin/peace/{slug}                       — save sermon edits
 *   POST   /admin/peace/{slug}/qa                    — add a new Q&A
 *   PATCH  /admin/peace/{slug}/qa/{qa}               — edit existing Q&A
 *   DELETE /admin/peace/{slug}/qa/{qa}               — remove Q&A
 *   POST   /admin/peace/{slug}/scripture             — add a scripture (bible-api validated)
 *   DELETE /admin/peace/{slug}/scripture/{ref}       — remove scripture
 *
 * Auth: protected by auth + role:super_admin middleware on the route group.
 */
class AdminPeaceController extends Controller
{
    public function index(): View
    {
        $sermons = PeaceSermon::orderByDesc('sermon_date')
            ->withCount(['qaPairs', 'scriptures'])
            ->get();
        return view('admin.peace.index', ['sermons' => $sermons]);
    }

    public function edit(string $slug): View
    {
        $sermon = PeaceSermon::where('slug', $slug)
            ->with(['qaPairs' => fn($q) => $q->orderBy('display_order'),
                    'scriptures' => fn($q) => $q->orderBy('display_order'),
                    'topics'])
            ->firstOrFail();

        // Compute current audio duration via ffprobe (Symfony Process — shell_exec is disabled on this host).
        $audioDur = null;
        if ($sermon->audio_url) {
            $abs = public_path(ltrim($sermon->audio_url, '/'));
            if (is_file($abs)) {
                $proc = new \Symfony\Component\Process\Process([
                    '/usr/local/bin/ffprobe', '-v', 'error',
                    '-show_entries', 'format=duration',
                    '-of', 'default=noprint_wrappers=1:nokey=1',
                    $abs,
                ]);
                $proc->setTimeout(20);
                try {
                    $proc->run();
                    if ($proc->isSuccessful()) {
                        $audioDur = (int) trim($proc->getOutput());
                    }
                } catch (\Throwable $e) { /* swallow — duration is best-effort */ }
            }
        }
        // BOUNDARY_EDITOR — condensed caption events for live caption sync
        $captionEvents = [];
        $cachePath = storage_path('app/peace-cache/' . $sermon->youtube_video_id . '.en.json3');
        if (file_exists($cachePath)) {
            $json = json_decode(file_get_contents($cachePath), true);
            foreach ($json['events'] ?? [] as $ev) {
                $t = ($ev['tStartMs'] ?? 0) / 1000;
                $text = trim(implode('', array_column($ev['segs'] ?? [], 'utf8')));
                if ($text === '') continue;
                $captionEvents[] = ['t' => (int) $t, 'text' => mb_substr($text, 0, 160)];
            }
        }
        return view('admin.peace.edit', ['sermon' => $sermon, 'audioDur' => $audioDur, 'captionEvents' => $captionEvents]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'speaker'            => 'nullable|string|max:255',
            'heart_line'         => 'nullable|string|max:1000',
            'summary_paragraphs' => 'nullable|array',
            'summary_paragraphs.*' => 'nullable|string|max:5000',
            'is_offsite'         => 'sometimes|boolean',
            'is_no_sermon'       => 'sometimes|boolean',
            'topics'             => 'nullable|string',
        ]);

        $sermon->fill([
            'title'              => $data['title'],
            'speaker'            => $data['speaker'] ?? null,
            'heart_line'         => $data['heart_line'] ?? null,
            'summary_paragraphs' => array_values(array_filter($data['summary_paragraphs'] ?? [], fn($p) => trim($p) !== '')),
            'is_offsite'         => (bool) ($data['is_offsite'] ?? false),
            'is_no_sermon'       => (bool) ($data['is_no_sermon'] ?? false),
        ]);
        $sermon->save();

        if (isset($data['topics'])) {
            $names = array_filter(array_map('trim', explode(',', $data['topics'])));
            $topicIds = collect($names)->map(fn($n) => PeaceTopic::fromName($n)->id)->all();
            $sermon->topics()->sync($topicIds);
        }

        return redirect()->route('admin.peace.edit', $slug)->with('status', 'Saved.');
    }

    public function addQa(Request $request, string $slug): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'question' => 'required|string|max:1000',
            'answer'   => 'required|string|max:5000',
        ]);
        $maxOrder = $sermon->qaPairs()->max('display_order') ?? 0;
        PeaceQaPair::create([
            'sermon_id'        => $sermon->id,
            'question'         => $data['question'],
            'answer'           => $data['answer'],
            'display_order'    => $maxOrder + 1,
            'confidence_score' => 1.0,
        ]);
        return redirect()->route('admin.peace.edit', $slug)->with('status', 'Q&A added.');
    }

    public function updateQa(Request $request, string $slug, int $qa): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();
        $row = PeaceQaPair::where('sermon_id', $sermon->id)->where('id', $qa)->firstOrFail();
        $data = $request->validate([
            'question'      => 'required|string|max:1000',
            'answer'        => 'required|string|max:5000',
            'display_order' => 'nullable|integer',
        ]);
        $row->fill($data)->save();
        return redirect()->route('admin.peace.edit', $slug)->with('status', "Q&A #{$qa} updated.");
    }

    public function destroyQa(string $slug, int $qa): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();
        PeaceQaPair::where('sermon_id', $sermon->id)->where('id', $qa)->delete();
        return redirect()->route('admin.peace.edit', $slug)->with('status', "Q&A #{$qa} removed.");
    }

    public function addScripture(Request $request, string $slug, ScriptureValidator $validator): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'reference_display' => 'required|string|max:100',
        ]);
        if (preg_match('/^((?:[123]\s+)?[A-Z][a-z]+(?:\s[A-Z][a-z]+)*)\s+(\d+)(?::(\d+)(?:-(\d+))?)?$/', $data['reference_display'], $m)) {
            $payload = [
                'book' => $m[1],
                'chapter' => (int) $m[2],
                'verse_start' => isset($m[3]) ? (int) $m[3] : 1,
                'verse_end' => isset($m[4]) ? (int) $m[4] : null,
                'reference_display' => $data['reference_display'],
            ];
            $validated = $validator->validate($payload);
            if (! $validated) {
                return redirect()->route('admin.peace.edit', $slug)->with('status', "Couldn't validate \"{$data['reference_display']}\" against bible-api. Reference dropped.");
            }
            $maxOrder = $sermon->scriptures()->max('display_order') ?? 0;
            PeaceScripture::create(array_merge($validated, [
                'sermon_id' => $sermon->id,
                'display_order' => $maxOrder + 1,
            ]));
            return redirect()->route('admin.peace.edit', $slug)->with('status', "Scripture {$data['reference_display']} added.");
        }
        return redirect()->route('admin.peace.edit', $slug)->with('status', "Couldn't parse reference. Use format like \"1 Samuel 30:6\" or \"Acts 2\".");
    }

    public function destroyScripture(string $slug, int $id): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();
        PeaceScripture::where('sermon_id', $sermon->id)->where('id', $id)->delete();
        return redirect()->route('admin.peace.edit', $slug)->with('status', "Scripture removed.");
    }

    // ────────────────────────────────────────────────────────────────
    // Automation schedule view (Saturday 3 PM cron + ad-hoc fires)
    // ────────────────────────────────────────────────────────────────

    public function schedule(): View
    {
        // Laravel-registered scheduled tasks for peace:
        $scheduled = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->filter(fn($e) => str_contains((string) $e->command, 'peace:'))
            ->map(function ($e) {
                try {
                    $next = \Cron\CronExpression::factory($e->expression)
                        ->getNextRunDate(now()->setTimezone($e->timezone ?? config('app.timezone')))
                        ->setTimezone('America/New_York');
                } catch (\Throwable $ex) {
                    $next = null;
                }
                return [
                    'kind'        => 'recurring',
                    'command'     => preg_replace('/.*artisan\s+/', 'artisan ', (string) $e->command),
                    'expression'  => $e->expression,
                    'timezone'    => $e->timezone ?? config('app.timezone'),
                    'next_run_et' => $next,
                ];
            })
            ->sortBy('next_run_et')
            ->values()
            ->all();

        // System crontab one-off peace entries:
        $oneOff = [];
        $cronLines = [];
        @\exec('crontab -l 2>/dev/null', $cronLines);
        foreach ($cronLines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, 'peace:')) continue;
            if (str_contains($line, 'schedule:run')) continue;
            $parts = preg_split('/\s+/', $line, 6);
            if (count($parts) < 6) continue;
            $expr = implode(' ', array_slice($parts, 0, 5));
            try {
                $next = \Cron\CronExpression::factory($expr)
                    ->getNextRunDate(now()->setTimezone('Europe/London'))
                    ->setTimezone('America/New_York');
            } catch (\Throwable $e) {
                $next = null;
            }
            $oneOff[] = [
                'kind'        => 'one-off',
                'command'     => preg_replace('/.*artisan\s+/', 'artisan ', $parts[5]),
                'expression'  => $expr . '  (server time / BST)',
                'timezone'    => 'Europe/London',
                'next_run_et' => $next,
            ];
        }

        $recentSermons = PeaceSermon::orderByDesc('created_at')->limit(10)->get([
            'id','title','slug','speaker','sermon_date','processing_status','published_at','created_at','review_deadline',
        ]);

        $logTail = [];
        foreach (['/home/shalom/peace-test-fire.log', '/home/shalom/peace-manual-fire.log'] as $logFile) {
            if (is_readable($logFile)) {
                // Pure-PHP tail-40 — read last ~32 KB then take final 40 lines
                $fh = @fopen($logFile, 'r');
                if (!$fh) continue;
                $size = filesize($logFile);
                fseek($fh, max(0, $size - 32768));
                $tailBytes = stream_get_contents($fh);
                fclose($fh);
                $lines = explode("\n", $tailBytes);
                $logTail[basename($logFile)] = array_slice(array_values(array_filter($lines)), -40);
            }
        }

        return view('admin.peace.schedule', [
            'scheduled'     => array_merge($scheduled, $oneOff),
            'recentSermons' => $recentSermons,
            'logTail'       => $logTail,
        ]);
    }

    public function scheduleTrigger(Request $request): RedirectResponse
    {
        $request->validate(['confirm' => 'required|in:yes']);
        $artisanPath = '/opt/cpanel/ea-php83/root/usr/bin/php ' . base_path('artisan');
        $logPath     = '/home/shalom/peace-manual-fire.log';
        $cmd = $artisanPath . ' peace:scan-channel >> ' . escapeshellarg($logPath) . ' 2>&1 &';

        $proc = @\proc_open($cmd, [], $pipes);
        if (\is_resource($proc)) @\proc_close($proc);

        \App\Models\AuditLog::record(
            event: 'peace_manual_fire',
            userId: auth()->id(),
            description: 'Manual fire of peace:scan-channel from admin schedule page',
        );

        return redirect()->route('admin.peace.schedule')
            ->with('status', 'Manual fire triggered. Pipeline runs in the background (3-5 min). Watch the log tail below.');
    }


    /** POST /admin/peace/{slug}/recompress — re-encode the CURRENT audio with a different compressor,
     *  keeping existing boundaries. No re-download, no cost, just ffmpeg. */
    public function recompress(\Illuminate\Http\Request $request, string $slug): \Illuminate\Http\RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();
        if (!$sermon->audio_url) {
            return back()->with('status', 'No audio file to re-encode.');
        }
        $data = $request->validate([
            'compressor' => ['required', 'in:none,simple,fairchild,ssl'],
        ]);
        $compType = $data['compressor'];

        $audioAbs = public_path(ltrim($sermon->audio_url, '/'));
        if (!is_file($audioAbs)) {
            return back()->with('status', 'Audio file not found on disk: ' . $audioAbs);
        }

        // Backup before re-encode (one-time, idempotent)
        $backupDir = '/home/shalom/tmp/peace-work/originals_backup';
        @\mkdir($backupDir, 0755, true);
        $backupFile = $backupDir . '/' . $sermon->youtube_video_id . '.mp3';
        if (!is_file($backupFile)) {
            @\copy($audioAbs, $backupFile);
        }

        // Get duration via ffprobe so we can place the 3-sec fade-out
        $probe = new \Symfony\Component\Process\Process([
            '/usr/local/bin/ffprobe', '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1',
            $audioAbs,
        ]);
        $probe->run();
        $duration = (int) floor((float) trim($probe->getOutput()));
        if ($duration < 10) {
            return back()->with('status', 'Could not read audio duration. Re-encode aborted.');
        }
        $fadeStart = max(0, $duration - 3);
        $fadeFilt = "afade=t=out:st={$fadeStart}:d=3";

        $chains = [
            'none'      => $fadeFilt,
            'simple'    => 'acompressor=threshold=-22dB:ratio=2.5:attack=50:release=300:knee=6:makeup=2' . ',' . $fadeFilt,
            'fairchild' => 'compand=attacks=0.3:decays=0.8:points=-80/-80|-60/-55|-40/-30|-20/-14|-6/-6|0/-3,treble=g=1:f=6000:width_type=q:width=0.7,acompressor=threshold=-2dB:ratio=10:attack=2:release=50:knee=2:makeup=1' . ',' . $fadeFilt,
            'ssl'       => 'acompressor=threshold=-12dB:ratio=4:attack=20:release=100:knee=4:makeup=2,treble=g=0.5:f=10000:width_type=q:width=0.7' . ',' . $fadeFilt,
        ];
        $filter = $chains[$compType] ?? $fadeFilt;

        $tmpOut = '/home/shalom/tmp/peace-work/' . $sermon->youtube_video_id . '.recompress.mp3';
        $proc = new \Symfony\Component\Process\Process([
            '/usr/local/bin/ffmpeg', '-y', '-loglevel', 'error',
            '-i', $audioAbs,
            '-af', $filter,
            '-c:a', 'libmp3lame', '-b:a', '160k',
            $tmpOut,
        ]);
        $proc->setTimeout(180);
        $proc->run();
        if (!$proc->isSuccessful() || !is_file($tmpOut) || filesize($tmpOut) < 1000) {
            return back()->with('status', 'ffmpeg failed. Output: ' . substr(trim($proc->getErrorOutput() . $proc->getOutput()), 0, 300));
        }
        // Atomic replace — old file is gone the moment new lands.
        @rename($tmpOut, $audioAbs);

        // Update DB to note the compressor change (boundaries unchanged).
        $sermon->update([
            'boundary_source' => 'manual',
            'boundary_reason' => 'Re-encoded via admin edit page (' . $compType . ' compressor, 3s fade, 160k, boundaries unchanged)',
        ]);

        return redirect()->route('admin.peace.edit', $slug)->with('status',
            'Audio re-encoded with ' . $compType . ' compressor (boundaries unchanged, old file replaced).');
    }

    /** POST /admin/peace/{slug}/trim — re-cut the existing trimmed mp3 to new in/out offsets. */
    public function trim(Request $request, string $slug): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();
        if (!$sermon->audio_url) {
            return back()->with('status', 'No audio file to trim.');
        }
        $data = $request->validate([
            'start'      => ['required', 'regex:/^\d+:[0-5]\d$/'],
            'end'        => ['required', 'regex:/^\d+:[0-5]\d$/'],
            'compressor' => ['sometimes', 'in:none,simple,fairchild,ssl'],
        ]);
        $mmss = fn($v) => array_sum(array_map(fn($p, $i) => (int)$p * pow(60, count(explode(':', $v))-1-$i), explode(':', $v), array_keys(explode(':', $v))));
        $startSec = $mmss($data['start']);
        $endSec   = $mmss($data['end']);
        if ($endSec <= $startSec + 5) {
            return back()->with('status', 'End must be at least 5 seconds after start.');
        }

        $audioAbs = public_path(ltrim($sermon->audio_url, '/'));
        if (!is_file($audioAbs)) {
            return back()->with('status', 'Audio file not found on disk: ' . $audioAbs);
        }

        // Backup before re-trim (only if we don't already have one)
        $backupDir = '/home/shalom/tmp/peace-work/originals_backup';
        @\mkdir($backupDir, 0755, true);
        $backupFile = $backupDir . '/' . $sermon->youtube_video_id . '.mp3';
        if (!is_file($backupFile)) {
            @\copy($audioAbs, $backupFile);
        }

        // Re-trim into a temp file then atomically replace
        $tmpOut = '/home/shalom/tmp/peace-work/' . $sermon->youtube_video_id . '.retrim.mp3';
        $duration = $endSec - $startSec;
        $fadeStart = max(0, $duration - 3);
        $compType = $data['compressor'] ?? 'none';
        $fadeFilt = "afade=t=out:st={$fadeStart}:d=3";
        $chains = [
            'none'      => $fadeFilt,
            'simple'    => 'acompressor=threshold=-22dB:ratio=2.5:attack=50:release=300:knee=6:makeup=2' . ',' . $fadeFilt,
            'fairchild' => 'compand=attacks=0.3:decays=0.8:points=-80/-80|-60/-55|-40/-30|-20/-14|-6/-6|0/-3,treble=g=1:f=6000:width_type=q:width=0.7,acompressor=threshold=-2dB:ratio=10:attack=2:release=50:knee=2:makeup=1' . ',' . $fadeFilt,
            'ssl'       => 'acompressor=threshold=-12dB:ratio=4:attack=20:release=100:knee=4:makeup=2,treble=g=0.5:f=10000:width_type=q:width=0.7' . ',' . $fadeFilt,
        ];
        $filter = $chains[$compType] ?? $fadeFilt;
        $proc = new \Symfony\Component\Process\Process([
            '/usr/local/bin/ffmpeg', '-y', '-loglevel', 'error',
            '-ss', (string) $startSec, '-to', (string) $endSec,
            '-i', $audioAbs,
            '-af', $filter,
            '-c:a', 'libmp3lame', '-b:a', '160k',
            $tmpOut,
        ]);
        $proc->setTimeout(180);
        $proc->run();
        if (!$proc->isSuccessful() || !is_file($tmpOut) || filesize($tmpOut) < 1000) {
            return back()->with('status', 'ffmpeg failed. Output: ' . substr(trim($proc->getErrorOutput() . $proc->getOutput()), 0, 300));
        }
        @rename($tmpOut, $audioAbs);

        // Update DB boundaries (offsets are within the previous trimmed file — adjust against original)
        $prevStart = (int) $sermon->sermon_start_seconds;
        $sermon->update([
            'sermon_start_seconds' => $prevStart + $startSec,
            'sermon_end_seconds'   => $prevStart + $endSec,
            'boundary_source'      => 'manual',
            'boundary_reason'      => 'Trim via admin edit page (' . $data['start'] . ' → ' . $data['end'] . ', 3s fade' . ($compType !== 'none' ? ' + ' . $compType . ' compressor' : '') . ', 160k)',
        ]);

        return redirect()->route('admin.peace.edit', $slug)->with('status',
            'Audio re-trimmed. New length: ~' . floor($duration/60) . ':' . str_pad($duration%60, 2, '0', STR_PAD_LEFT) . ' with 3-sec fade-out' . ($compType !== 'none' ? ' + ' . $compType . ' compressor' : '') . ' @ 160k.');
    }


    /** GET /admin/peace/analytics — Finding Peace traffic dashboard. */
    public function analytics(): \Illuminate\View\View
    {
        $now = now();
        $win7  = $now->copy()->subDays(7);
        $win30 = $now->copy()->subDays(30);

        // Bucket: all path-views under /find-peace
        $base = \DB::table('page_views')->where('path', 'like', '/find-peace%');

        $totals = [
            '24h'  => (clone $base)->where('viewed_at', '>=', $now->copy()->subDay())->count(),
            '7d'   => (clone $base)->where('viewed_at', '>=', $win7)->count(),
            '30d'  => (clone $base)->where('viewed_at', '>=', $win30)->count(),
            'all'  => (clone $base)->count(),
            'uniq7d' => (clone $base)->where('viewed_at', '>=', $win7)->distinct('session_id')->count('session_id'),
        ];

        // Top sermons last 30 days (by view count)
        $topSermons = (clone $base)
            ->where('path', 'like', '/find-peace/%')
            ->where('path', 'not like', '/find-peace/topic/%')
            ->where('viewed_at', '>=', $win30)
            ->selectRaw('path, COUNT(*) as n, COUNT(DISTINCT session_id) as uniq')
            ->groupBy('path')
            ->orderByDesc('n')
            ->limit(10)
            ->get();
        // Hydrate sermon titles
        $slugMap = PeaceSermon::pluck('title', 'slug')->all();
        $topSermons->each(function ($row) use ($slugMap) {
            $slug = ltrim(str_replace('/find-peace/', '', $row->path), '/');
            $row->title = $slugMap[$slug] ?? $row->path;
            $row->slug  = $slug;
        });

        // Top topics last 30 days
        $topTopics = (clone $base)
            ->where('path', 'like', '/find-peace/topic/%')
            ->where('viewed_at', '>=', $win30)
            ->selectRaw('path, COUNT(*) as n')
            ->groupBy('path')
            ->orderByDesc('n')
            ->limit(10)
            ->get();
        $topTopics->each(function ($row) {
            $row->slug = ltrim(str_replace('/find-peace/topic/', '', $row->path), '/');
        });

        // Referrers (last 30d) — exclude internal + null
        $referrers = (clone $base)
            ->where('viewed_at', '>=', $win30)
            ->whereNotNull('referrer')
            ->where('referrer', 'not like', '%thechurchofpeace.org%')
            ->selectRaw('referrer, COUNT(*) as n')
            ->groupBy('referrer')
            ->orderByDesc('n')
            ->limit(10)
            ->get();

        // Countries (last 30d)
        $countries = (clone $base)
            ->where('viewed_at', '>=', $win30)
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->selectRaw('country, COUNT(*) as n')
            ->groupBy('country')
            ->orderByDesc('n')
            ->limit(10)
            ->get();

        // Devices (last 30d)
        $devices = (clone $base)
            ->where('viewed_at', '>=', $win30)
            ->whereNotNull('device')
            ->selectRaw('device, COUNT(*) as n')
            ->groupBy('device')
            ->orderByDesc('n')
            ->get();

        // Daily view chart (last 30 days, zero-filled)
        $byDay = (clone $base)
            ->where('viewed_at', '>=', $win30)
            ->selectRaw('DATE(viewed_at) as d, COUNT(*) as n')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');
        $chart = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $chart[$day] = (int) ($byDay[$day]->n ?? 0);
        }

        // Poll response stats
        $pollStats = [
            'polls_active' => \App\Models\PeacePoll::where('is_active', true)->count(),
            'responses_30d' => \DB::table('peace_poll_responses')->where('created_at', '>=', $win30)->count(),
        ];

        return view('admin.peace.analytics', compact(
            'totals', 'topSermons', 'topTopics', 'referrers', 'countries', 'devices', 'chart', 'pollStats'
        ));
    }


    /** GET /admin/peace/subscribers — view of all peace_subscribers. */
    public function subscribers(): View
    {
        $subs = \App\Models\PeaceSubscriber::orderByDesc('created_at')
            ->withCount(['savedQas', 'magicLinks'])
            ->get();
        return view('admin.peace.subscribers', compact('subs'));
    }


    /** GET /admin/peace/submissions — moderation inbox for seeker submissions. */
    public function submissions(Request $request): View
    {
        $filter = $request->input('status', 'pending');
        $allowed = ['all', 'pending', 'approved', 'replied', 'archived'];
        if (!in_array($filter, $allowed, true)) $filter = 'pending';

        $q = \App\Models\PeaceUserSubmission::with(['subscriber', 'sermon', 'topic', 'replier'])
            ->orderByDesc('created_at');
        if ($filter !== 'all') $q->where('status', $filter);
        $subs = $q->get();

        $kpi = [
            'pending'  => \App\Models\PeaceUserSubmission::where('status', 'pending')->count(),
            'approved' => \App\Models\PeaceUserSubmission::where('status', 'approved')->count(),
            'replied'  => \App\Models\PeaceUserSubmission::where('status', 'replied')->count(),
            'archived' => \App\Models\PeaceUserSubmission::where('status', 'archived')->count(),
            'total'    => \App\Models\PeaceUserSubmission::count(),
        ];

        return view('admin.peace.submissions', compact('subs', 'kpi', 'filter'));
    }

    /** POST /admin/peace/submissions/{id} — approve, archive, restore, or reply. */
    public function submissionsUpdate(Request $request, int $id): RedirectResponse
    {
        $sub = \App\Models\PeaceUserSubmission::findOrFail($id);
        $action = $request->input('action');

        switch ($action) {
            case 'approve':
                $sub->status = 'approved';
                $sub->save();
                \App\Models\AuditLog::record(
                    event: 'peace_submission_approved',
                    userId: auth()->id(),
                    description: 'Approved submission #' . $sub->id . ' (' . $sub->type . ')',
                );
                return back()->with('status', 'Marked approved.');

            case 'archive':
                $sub->status = 'archived';
                $sub->save();
                \App\Models\AuditLog::record(
                    event: 'peace_submission_archived',
                    userId: auth()->id(),
                    description: 'Archived submission #' . $sub->id,
                );
                return back()->with('status', 'Archived.');

            case 'restore':
                $sub->status = 'pending';
                $sub->save();
                return back()->with('status', 'Restored to pending.');

            case 'reply':
                $reply = trim((string) $request->input('pastor_reply', ''));
                if (mb_strlen($reply) < 10) {
                    return back()->with('status', 'Reply must be at least 10 characters.');
                }
                $sub->pastor_reply = $reply;
                $sub->pastor_replied_at = now();
                $sub->pastor_replied_by = auth()->id();
                $sub->status = 'replied';
                $sub->save();

                // Email the seeker
                $seekerEmail = optional($sub->subscriber)->email;
                if ($seekerEmail) {
                    try {
                        $original = mb_substr($sub->body, 0, 600);
                        $body  = "Hi —\n\n";
                        $body .= "You shared this with Finding Peace " . $sub->created_at->diffForHumans() . ":\n\n";
                        $body .= "  " . str_replace("\n", "\n  ", $original) . "\n\n";
                        $body .= str_repeat('─', 50) . "\n\n";
                        $body .= $reply . "\n\n";
                        $body .= "— A pastor at The Church of Peace\n\n";
                        $body .= "Reply to this email if you want to continue the conversation. Or come back to Finding Peace anytime: " . url('/find-peace') . "\n";

                        \Illuminate\Support\Facades\Mail::raw($body, function ($m) use ($seekerEmail, $sub) {
                            $m->to($seekerEmail)
                              ->cc('contact@c-wellpics.com')
                              ->replyTo('app@thechurchofpeace.org')
                              ->subject('Reply from a pastor — your ' . $sub->type . ' on Finding Peace');
                        });
                    } catch (\Throwable $e) {
                        \App\Models\AuditLog::record(
                            event: 'peace_reply_email_failed',
                            description: 'Reply email failed for submission #' . $sub->id . ': ' . $e->getMessage(),
                        );
                        return back()->with('status', 'Reply saved but email failed: ' . $e->getMessage());
                    }
                }

                \App\Models\AuditLog::record(
                    event: 'peace_submission_replied',
                    userId: auth()->id(),
                    description: 'Replied to submission #' . $sub->id . ' (' . $sub->type . '), emailed ' . $seekerEmail,
                );
                return back()->with('status', 'Reply sent to ' . $seekerEmail);

            default:
                return back()->with('status', 'Unknown action.');
        }
    }
    /**
     * BOUNDARY_EDITOR (2026-05-30) — set sermon_start/end_seconds against the
     * FULL YouTube video. Three actions:
     *   - save_only           → update DB only
     *   - save_and_reslice    → DB + re-slice audio from YouTube source
     *   - save_reslice_regen  → all of above + regenerate Q&As via Claude
     *
     * Re-slice downloads the full audio via yt-dlp, slices with ffmpeg,
     * replaces the existing sermon mp3. Old file is backed up to .bak.
     *
     * Regenerate calls PeaceContentGenerator on the new caption slice,
     * replaces title / heart_line / summary / speaker / slug / Q&As.
     */
    public function setBoundaries(Request $request, string $slug): RedirectResponse
    {
        $sermon = PeaceSermon::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'start'  => 'required|integer|min:0|max:43200',  // max 12h video
            'end'    => 'required|integer|min:0|max:43200',
            'action' => 'required|string|in:save_only,save_and_reslice,save_reslice_regen',
        ]);

        if ($data['end'] <= $data['start']) {
            return back()->withErrors(['end' => 'OUT must be after IN.']);
        }
        if ($data['end'] - $data['start'] < 60) {
            return back()->withErrors(['end' => 'Span must be at least 1 minute.']);
        }

        $oldStart = $sermon->sermon_start_seconds;
        $oldEnd   = $sermon->sermon_end_seconds;
        $sermon->sermon_start_seconds = $data['start'];
        $sermon->sermon_end_seconds   = $data['end'];
        $sermon->boundary_source = 'manual';
        $sermon->boundary_reason = sprintf(
            'Manually set via admin boundary editor. Previous: %s → %s (%s). New: %s → %s (%s).',
            gmdate('H:i:s', $oldStart),
            gmdate('H:i:s', $oldEnd),
            gmdate('i:s', $oldEnd - $oldStart),
            gmdate('H:i:s', $data['start']),
            gmdate('H:i:s', $data['end']),
            gmdate('i:s', $data['end'] - $data['start']),
        );
        $sermon->boundary_confidence = 1.0;
        $sermon->save();

        // Audit log
        \App\Models\AuditLog::record(
            event: 'peace_boundaries_updated',
            userId: $request->user()?->id,
            description: sprintf('Sermon %s: %s → %s', $sermon->slug,
                gmdate('H:i:s', $oldStart) . '–' . gmdate('H:i:s', $oldEnd),
                gmdate('H:i:s', $data['start']) . '–' . gmdate('H:i:s', $data['end'])),
            meta: ['sermon_id' => $sermon->id, 'old_start' => $oldStart, 'old_end' => $oldEnd, 'new_start' => $data['start'], 'new_end' => $data['end']],
        );

        if ($data['action'] === 'save_only') {
            return back()->with('status',
                'Boundaries saved. Audio + content unchanged — use "Save + Re-slice" when you want them updated to match.');
        }

        // RE-SLICE — download full audio, cut to new span
        $videoId = $sermon->youtube_video_id;
        $audioDir = storage_path('app/public/peace/audio');
        if (! is_dir($audioDir)) mkdir($audioDir, 0755, true);
        $existing = "{$audioDir}/{$videoId}.mp3";
        $fullPath = "/tmp/{$videoId}-full.mp3";

        // Backup old slice
        if (file_exists($existing)) {
            copy($existing, $existing . '.bak.' . now()->format('Ymd-His'));
        }

        // Download full audio (~30-60s for a 2hr video)
        $dl = new \Symfony\Component\Process\Process([
            '/home/shalom/bin/ytdlp-auth', '--no-warnings',
            '--ffmpeg-location', '/usr/local/bin/ffmpeg',
            '-x', '--audio-format', 'mp3', '--audio-quality', '5',
            '-o', $fullPath,
            "https://www.youtube.com/watch?v={$videoId}",
        ]);
        $dl->setTimeout(300);
        $dl->run();
        if (! $dl->isSuccessful() || ! file_exists($fullPath)) {
            return back()->with('status', '❌ Audio download failed: ' . substr($dl->getErrorOutput(), 0, 200));
        }

        // Slice
        $span = $data['end'] - $data['start'];
        $slice = new \Symfony\Component\Process\Process([
            '/usr/local/bin/ffmpeg', '-y',
            '-ss', (string) $data['start'],
            '-i', $fullPath,
            '-t', (string) $span,
            '-acodec', 'libmp3lame', '-b:a', '128k',
            $existing,
        ]);
        $slice->setTimeout(180);
        $slice->run();
        @unlink($fullPath);
        if (! $slice->isSuccessful() || ! file_exists($existing) || filesize($existing) < 100_000) {
            return back()->with('status', '❌ Audio slice failed: ' . substr($slice->getErrorOutput(), 0, 200));
        }

        $sermon->audio_url = '/storage/peace/audio/' . $videoId . '.mp3';
        $sermon->audio_duration_seconds = $span;
        $sermon->audio_status = 'ready';
        $sermon->save();

        if ($data['action'] === 'save_and_reslice') {
            return back()->with('status',
                sprintf('✓ Audio re-sliced to %s. Title + Q&As + heart-line still reflect the OLD span — use Re-slice + Regenerate if they need refreshing.', gmdate('i:s', $span)));
        }

        // REGENERATE — rebuild caption slice + run Claude
        $cachePath = storage_path("app/peace-cache/{$videoId}.en.json3");
        if (! file_exists($cachePath)) {
            return back()->with('status',
                '✓ Audio re-sliced, but caption file missing — content not regenerated. Run the scan command to refresh captions.');
        }
        $json = json_decode(file_get_contents($cachePath), true);
        $sliceText = '';
        foreach ($json['events'] ?? [] as $ev) {
            $t = ($ev['tStartMs'] ?? 0) / 1000;
            if ($t < $data['start'] || $t > $data['end']) continue;
            foreach ($ev['segs'] ?? [] as $seg) {
                $sliceText .= ' ' . ($seg['utf8'] ?? '');
            }
        }
        if (strlen($sliceText) < 500) {
            return back()->with('status', '✓ Audio re-sliced, but caption slice too short to regenerate content (' . strlen($sliceText) . ' chars).');
        }

        try {
            $gen = app(\App\Services\Peace\PeaceContentGenerator::class);
            $result = $gen->generate($sliceText, [
                'video_id'    => $videoId,
                'video_title' => $sermon->title . ' (re-generated)',
            ]);
            $oldSlug = $sermon->slug;
            $sermon->title              = $result['title'] ?? $sermon->title;
            $sermon->heart_line         = $result['heart_line'] ?? $sermon->heart_line;
            $sermon->summary_paragraphs = $result['summary'] ?? $sermon->summary_paragraphs;
            $sermon->speaker            = $result['speaker'] ?? $sermon->speaker;
            // Keep the old slug if title didn't change — preserves URLs
            $newSlug = \Illuminate\Support\Str::slug($sermon->title) . '-' . substr($videoId, 0, 6);
            if ($newSlug !== $oldSlug) $sermon->slug = $newSlug;
            $sermon->save();

            // Replace Q&As
            \App\Models\PeaceQaPair::where('sermon_id', $sermon->id)->delete();
            foreach ($result['qa_pairs'] ?? [] as $i => $qa) {
                \App\Models\PeaceQaPair::create([
                    'sermon_id'        => $sermon->id,
                    'question'         => $qa['question'] ?? '',
                    'answer'           => $qa['answer'] ?? '',
                    'display_order'    => $i,
                    'confidence_score' => $qa['confidence'] ?? 0.8,
                ]);
            }

            return redirect()->route('admin.peace.edit', $sermon->slug)
                ->with('status', sprintf('✓ Boundaries + audio + Q&As all updated. New span %s. Title: %s.',
                    gmdate('i:s', $span), $sermon->title));
        } catch (\Throwable $e) {
            return back()->with('status', '✓ Audio re-sliced. ❌ Content regen failed: ' . substr($e->getMessage(), 0, 200));
        }
    }
}
