<?php
namespace App\Http\Controllers;

use App\Models\IntakeForm;
use App\Models\IntakeSubmission;
use App\Services\Intake\GradCardRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin side of the intake engine — the gallery where Andre downloads slides,
 * toggles the text overlay, removes entries, and grabs everything as a zip.
 */
class AdminIntakeController extends Controller
{
    public function index(): View
    {
        $forms = IntakeForm::withCount(['submissions' => fn ($q) => $q->where('status', 'live')])
            ->orderByDesc('id')->get();

        return view('admin.intake.index', ['forms' => $forms]);
    }


    public function create(): View
    {
        return view('admin.intake.builder', ['form' => null]);
    }

    public function builderEdit(IntakeForm $form): View
    {
        return view('admin.intake.builder', compact('form'));
    }

    public function save(Request $request, ?IntakeForm $form = null): JsonResponse
    {
        $data = $request->validate([
            'title'           => 'required|string|max:180',
            'slug'            => 'required|string|max:80|regex:/^[a-z0-9-]+$/',
            'output_type'     => 'required|in:none,graduation',
            'intro'           => 'nullable|string|max:1000',
            'schema'          => 'required|array',
            'schema.fields'   => 'required|array|min:1',
            'settings'        => 'nullable|array',
        ]);
        $isNew = ($form === null || ! $form->exists);
        if ($isNew) {
            if (IntakeForm::where('slug', $data['slug'])->exists()) {
                return response()->json(['ok' => false, 'message' => 'That slug is already taken — choose another URL.'], 422);
            }
        }
        $payload = [
            'title'       => $data['title'],
            'output_type' => $data['output_type'],
            'intro'       => $data['intro'] ?? null,
            'schema'      => ['fields' => $data['schema']['fields']],
            'settings'    => $data['settings'] ?? [],
            'is_active'   => true,
        ];
        if ($isNew) {
            $payload['slug']       = $data['slug'];
            $payload['created_by'] = auth()->id();
            $form = IntakeForm::create($payload);
        } else {
            $form->update($payload);
        }
        return response()->json(['ok' => true, 'slug' => $form->slug]);
    }
    public function submissions(IntakeForm $form): View
    {
        $live    = $form->submissions()->where('status', 'live')->latest('id')->get();
        $removed = $form->submissions()->where('status', 'removed')->latest('id')->get();

        return view('admin.intake.submissions', compact('form', 'live', 'removed'));
    }

    /** Edit the text that appears on a slide, then re-render (one-off). */
    public function editSubmission(Request $request, IntakeSubmission $submission): JsonResponse
    {
        $fields = ['name', 'level', 'school', 'major', 'honors', 'thanks', 'verse'];
        $data = $submission->data ?? [];
        foreach ($fields as $f) {
            if ($request->has($f)) $data[$f] = trim((string) $request->input($f));
        }
        $submission->data = $data;
        if ($request->filled('name')) $submission->submitter_name = $data['name'];
        $submission->save();

        $this->regenerate($submission);

        return response()->json([
            'ok'      => true,
            'url'     => $submission->outputUrl() . '?v=' . now()->timestamp,
            'message' => 'Slide updated.',
        ]);
    }

    /** Remove or restore the text overlay across every photo slide in this form. */
    public function bulkText(Request $request, IntakeForm $form): JsonResponse
    {
        $show = $request->input('mode') !== 'remove';
        $subs = $form->submissions()->where('status', 'live')->whereNotNull('photo_path')->get();
        foreach ($subs as $s) { $s->update(['show_text' => $show]); $this->regenerate($s); }

        $n = $subs->count();
        return response()->json([
            'ok'      => true,
            'count'   => $n,
            'message' => ($show ? 'Text restored on ' : 'Text removed from ') . $n . ' slide' . ($n === 1 ? '' : 's') . '. Reloading…',
        ]);
    }

    private function regenerate(IntakeSubmission $submission): void
    {
        if ($submission->form->output_type !== 'graduation') return;
        $style = $submission->form->setting('slide_style', 'sans');
        $submission->update(['output_path' => (new GradCardRenderer($style))->render($submission)]);
    }

    /** Flip the text overlay on a graduation card and re-render. */
    public function toggleText(IntakeSubmission $submission): JsonResponse
    {
        $submission->update(['show_text' => ! $submission->show_text]);
        $this->regenerate($submission);
        return response()->json([
            'ok'        => true,
            'show_text' => $submission->show_text,
            'url'       => $submission->outputUrl() . '?v=' . now()->timestamp,
            'message'   => $submission->show_text ? 'Text added back.' : 'Text removed — photo only.',
        ]);
    }

    /** Push a form onto (or off) the public site menu. */
    public function toggleMenu(Request $request, IntakeForm $form): JsonResponse
    {
        $s = $form->settings ?? [];
        $s['in_menu'] = ! ($s['in_menu'] ?? false);
        if ($request->filled('menu_label')) $s['menu_label'] = trim((string) $request->input('menu_label'));
        $form->settings = $s;
        $form->save();
        Cache::forget('intake_menu_forms');

        return response()->json([
            'ok'      => true,
            'in_menu' => $s['in_menu'],
            'message' => $s['in_menu'] ? 'Added to the site menu — it’s live now.' : 'Removed from the site menu.',
        ]);
    }

    public function remove(IntakeSubmission $submission): JsonResponse
    {
        $submission->update(['status' => 'removed']);
        return response()->json(['ok' => true, 'message' => 'Moved to removed.']);
    }

    public function restore(IntakeSubmission $submission): JsonResponse
    {
        $submission->update(['status' => 'live']);
        return response()->json(['ok' => true, 'message' => 'Back in the gallery.']);
    }

    /**
     * Replace a submission's photo with a browser-edited version (rotate/crop/aspect)
     * and regenerate the slide. The pristine original is kept once, so the editor can
     * always re-crop from scratch. Validated via GD (getimagesize) — no php_fileinfo.
     */
    public function adjust(Request $request, IntakeSubmission $submission): JsonResponse
    {
        $file = $request->file('image');
        if (! $file || ! $file->isValid()) {
            return response()->json(['ok' => false, 'message' => 'No image was received.'], 422);
        }
        if ($file->getSize() > 15 * 1024 * 1024) {
            return response()->json(['ok' => false, 'message' => 'That image is too large.'], 422);
        }
        $info = @getimagesize($file->getPathname());
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            return response()->json(['ok' => false, 'message' => 'That file is not a valid image.'], 422);
        }

        // Preserve the pristine original once (non-destructive re-editing).
        if (! $submission->photo_original_path && $submission->photo_path) {
            $submission->update(['photo_original_path' => $submission->photo_path]);
        }

        $ext = $info[2] === IMAGETYPE_PNG ? 'png' : ($info[2] === IMAGETYPE_WEBP ? 'webp' : 'jpg');
        $dir = public_path('intake-media/photos');
        if (! is_dir($dir)) mkdir($dir, 0755, true);
        $name = $submission->id . '-' . now()->format('YmdHis') . '.' . $ext;
        $file->move($dir, $name);

        $submission->update(['photo_path' => 'intake-media/photos/' . $name]);
        $this->regenerate($submission);

        return response()->json([
            'ok'      => true,
            'url'     => $submission->outputUrl() . '?v=' . now()->timestamp,
            'message' => 'Image updated.',
        ]);
    }

    /** Download one slide as a nicely-named PNG. */
    public function download(IntakeSubmission $submission): BinaryFileResponse
    {
        abort_unless($submission->output_path && is_file(public_path($submission->output_path)), 404);
        return response()->download(public_path($submission->output_path), $this->niceName($submission), ['Content-Type' => 'image/png']);
    }

    /** Zip every live slide for this form and stream it. */
    public function bulkDownload(IntakeForm $form): StreamedResponse|JsonResponse
    {
        $subs = $form->submissions()->where('status', 'live')
            ->whereNotNull('output_path')->get()
            ->filter(fn ($s) => is_file(public_path($s->output_path)));

        if ($subs->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'No slides to download yet.'], 422);
        }

        $zipPath = storage_path('app/intake-' . $form->slug . '-' . now()->format('Ymd-His') . '.zip');
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $used = [];
        foreach ($subs as $s) {
            $base = $this->niceName($s);
            $n = $base; $i = 2;
            while (isset($used[$n])) { $n = preg_replace('/\.png$/', '', $base) . "-{$i}.png"; $i++; }
            $used[$n] = true;
            $zip->addFile(public_path($s->output_path), $n);
        }
        $zip->close();

        return response()->streamDownload(function () use ($zipPath) {
            readfile($zipPath);
            @unlink($zipPath);
        }, $form->slug . '-slides-' . now()->format('Y-m-d') . '.zip', [
            'Content-Type' => 'application/zip',
        ]);
    }

    private function niceName(IntakeSubmission $s): string
    {
        $name = Str::slug($s->displayName()) ?: ('slide-' . $s->id);
        return $name . '.png';
    }
}
