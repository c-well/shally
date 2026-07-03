<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\QuarterlyLesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class AdminLessonsController extends Controller
{
    /** GET /admin/lessons — list all + add new. */
    public function index(): View
    {
        $lessons = QuarterlyLesson::orderByDesc('starts_on')->get();
        $stats = $lessons->mapWithKeys(fn ($l) => [$l->id => $l->cacheStats()]);
        return view('admin.lessons', compact('lessons', 'stats'));
    }

    /** POST /admin/lessons — add or replace a quarterly lesson. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year'      => 'required|integer|min:2020|max:2099',
            'quarter'   => 'required|integer|in:1,2,3,4',
            'theme'     => 'required|string|max:180',
            'starts_on' => 'required|date',
            'ends_on'   => 'required|date|after_or_equal:starts_on',
            'pdf_url'   => 'nullable|url|max:500',
            'pdf'       => 'nullable|file|extensions:pdf|max:51200',  // 50MB — extensions: not mimes: (no fileinfo on this host)
        ]);

        $pdfPath = null;
        if ($request->hasFile('pdf')) {
            $f = $request->file('pdf');
            // Magic-byte check replaces the MIME guess fileinfo would have done.
            $head = (string) @file_get_contents($f->getPathname(), false, null, 0, 5);
            if (! str_starts_with($head, '%PDF')) {
                return back()->withInput()->withErrors(['pdf' => 'That file does not look like a real PDF.']);
            }
            $name = sprintf('en_%dt%d.pdf', $data['year'], $data['quarter']);
            $f->move(public_path('lessons'), $name);
            $pdfPath = 'lessons/' . $name;
        }

        $lesson = QuarterlyLesson::updateOrCreate(
            ['year' => $data['year'], 'quarter' => $data['quarter']],
            [
                'theme'     => $data['theme'],
                'starts_on' => $data['starts_on'],
                'ends_on'   => $data['ends_on'],
                'pdf_url'   => $data['pdf_url'] ?? null,
                'pdf_path'  => $pdfPath ?: optional(QuarterlyLesson::where(['year'=>$data['year'],'quarter'=>$data['quarter']])->first())->pdf_path,
            ]
        );

        AuditLog::record(
            event: 'lesson_saved',
            userId: $request->user()->id,
            description: $request->user()->name . " saved Q{$lesson->quarter} {$lesson->year} · {$lesson->theme}",
        );

        return back()->with('status', "Saved {$lesson->quarterLabel()} · {$lesson->theme}.");
    }

    /**
     * POST /admin/lessons/{lesson}/sync — pull readings from Adventech into the local cache.
     * Runs the artisan command in-process. Skips already-cached days unless ?force=1.
     */
    public function sync(Request $request, QuarterlyLesson $lesson): RedirectResponse
    {
        @set_time_limit(180);
        $force = $request->boolean('force');

        try {
            Artisan::call('lesson:sync', [
                '--slug'  => $lesson->slug(),
                '--force' => $force,
            ]);
            $stats = $lesson->cacheStats();
            $msg = "Synced {$lesson->quarterLabel()} · {$stats['days']}/{$stats['total_days']} days cached.";
        } catch (\Throwable $e) {
            $msg = "Sync failed: " . $e->getMessage();
        }

        AuditLog::record(
            event: 'lesson_synced',
            userId: $request->user()->id,
            description: $request->user()->name . " ran lesson sync for {$lesson->quarterLabel()}" . ($force ? ' (force)' : ''),
        );

        return back()->with('status', $msg);
    }

    public function destroy(Request $request, QuarterlyLesson $lesson): RedirectResponse
    {
        $label = $lesson->quarterLabel();
        $lesson->delete();
        AuditLog::record(
            event: 'lesson_deleted',
            userId: $request->user()->id,
            description: $request->user()->name . " deleted {$label}",
        );
        return back()->with('status', "Deleted {$label}.");
    }
}
