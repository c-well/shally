<?php
namespace App\Http\Controllers;

use App\Models\IntakeForm;
use App\Models\IntakeSubmission;
use App\Services\Intake\GradCardRenderer;
use App\Services\Intake\TwilioNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public intake forms — thechurchofpeace.org/intake/{slug}.
 *
 * One controller drives every form. Fields come from the form's schema;
 * validation, conditional visibility, the generated artifact (e.g. the
 * graduation slide), and notifications are all derived from the definition.
 */
class IntakeController extends Controller
{
    public function show(IntakeForm $form): View
    {
        abort_unless($form->is_active, 404);

        return view('intake.show', [
            'form'        => $form,
            'renderToken' => Crypt::encryptString((string) time()),
        ]);
    }

    public function submit(Request $request, IntakeForm $form): RedirectResponse
    {
        abort_unless($form->is_active, 404);

        try {
            $rendered = (int) Crypt::decryptString((string) $request->input('rendered_at'));
            if (time() - $rendered < 2) return back()->with('sent', true);
        } catch (\Throwable $e) { /* missing/garbage token — validation handles it */ }

        [$data, $files] = $this->validateAgainstSchema($request, $form);

        // Photos: the field literally named "photo" is the slide photo; any other
        // photo field (childhood photo, etc.) is kept and its path stored in data.
        $photoPath = null;
        $primary = array_key_exists('photo', $files) ? 'photo' : (array_key_first($files) ?: null);
        foreach ($files as $key => $file) {
            if (! $file) continue;
            $dir = public_path('intake-media/photos');
            if (! is_dir($dir)) mkdir($dir, 0755, true);
            $name = Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($dir, $name);
            $rel = 'intake-media/photos/' . $name;
            if ($key === $primary) $photoPath = $rel;
            else $data[$key . '_path'] = $rel;
        }

        $sub = IntakeSubmission::create([
            'intake_form_id'  => $form->id,
            'data'            => $data,
            'photo_path'      => $photoPath,
            'submitter_name'  => $data['name'] ?? null,
            'submitter_email' => $data['submitter_email'] ?? ($data['email'] ?? null),
            'ip'              => $request->ip(),
        ]);

        if ($form->output_type === 'graduation') {
            try {
                $style = $form->setting('slide_style', 'sans');
                $sub->update(['output_path' => (new GradCardRenderer($style))->render($sub)]);
            } catch (\Throwable $e) {
                Log::error('Grad card render failed', ['submission' => $sub->id, 'error' => $e->getMessage()]);
            }
        }

        $this->notify($form, $sub);

        return back()->with('intake_done', $form->setting('thank_you')
            ?: 'Thank you — your submission has been received.');
    }

    /* ───────────────────────── validation ───────────────────────── */

    /** @return array{0:array<string,mixed>,1:array<string,?\Illuminate\Http\UploadedFile>} */
    private function validateAgainstSchema(Request $request, IntakeForm $form): array
    {
        $rules = []; $attributes = []; $photoFields = [];
        $input = $request->all();

        foreach ($form->fields() as $f) {
            $key  = $f['key'];
            $type = $f['type'] ?? 'text';
            $vis  = $this->showIf($f['show_if'] ?? null, $input);
            $req  = ($f['required'] ?? false) && $vis;
            $attributes[$key] = strtolower($f['label'] ?? $key);

            switch ($type) {
                case 'photo':
                    $photoFields[] = $key;
                    $rules[$key] = array_filter([$req ? 'required' : 'nullable', 'file', 'extensions:jpg,jpeg,png,webp,gif,heic,heif', 'max:12288']);
                    break;
                case 'checkbox': // single consent
                    $rules[$key] = $req ? ['accepted'] : ['nullable'];
                    break;
                case 'checkboxes': // multi
                    $rules[$key] = $vis && $req ? ['required', 'array'] : ['nullable', 'array'];
                    if (! empty($f['options'])) $rules[$key . '.*'] = ['in:' . implode(',', $f['options'])];
                    break;
                case 'email':    $rules[$key] = [$req ? 'required' : 'nullable', 'email', 'max:200']; break;
                case 'tel':      $rules[$key] = [$req ? 'required' : 'nullable', 'string', 'max:40']; break;
                case 'date':     $rules[$key] = [$req ? 'required' : 'nullable', 'date']; break;
                case 'textarea': $rules[$key] = $vis ? [$req ? 'required' : 'nullable', 'string', 'max:5000'] : ['nullable', 'string', 'max:5000']; break;
                case 'select':
                    $r = [$req ? 'required' : 'nullable'];
                    if (! empty($f['options'])) $r[] = 'in:' . implode(',', $f['options']);
                    $rules[$key] = $vis ? $r : ['nullable', 'string', 'max:200'];
                    break;
                default:         $rules[$key] = $vis ? [$req ? 'required' : 'nullable', 'string', 'max:500'] : ['nullable', 'string', 'max:500'];
            }
        }

        $validated = Validator::make($input, $rules, [], $attributes)->validate();

        // Normalize single checkboxes to booleans; pull files out separately.
        foreach ($form->fields() as $f) {
            if (($f['type'] ?? '') === 'checkbox') {
                $validated[$f['key']] = (bool) ($input[$f['key']] ?? false);
            }
        }
        $files = [];
        foreach ($photoFields as $pf) { $files[$pf] = $request->file($pf); unset($validated[$pf]); }

        return [$validated, $files];
    }

    private function showIf(?array $cond, array $data): bool
    {
        if (! $cond) return true;
        $val = $data[$cond['field'] ?? ''] ?? null;
        if (! empty($cond['not_empty'])) return filled($val);
        if (isset($cond['in']))          return in_array($val, $cond['in'], true);
        if (isset($cond['equals']))      return $val === $cond['equals'];
        return true;
    }

    /* ───────────────────────── notifications ───────────────────────── */

    private function notify(IntakeForm $form, IntakeSubmission $sub): void
    {
        $emails = (array) $form->setting('notify_emails', []);
        $name   = $sub->displayName();

        if ($emails) {
            $body = "New submission to \"{$form->title}\" on thechurchofpeace.org\n" . str_repeat('-', 60) . "\n\n";
            foreach ($sub->data as $k => $v) {
                if ($v === null || $v === '' || $v === false) continue;
                if (str_ends_with($k, '_path')) { $body .= "Photo (" . str_replace('_path', '', $k) . "): https://thechurchofpeace.org/{$v}\n"; continue; }
                $label = $form->field($k)['label'] ?? $k;
                $body .= "{$label}: " . (is_array($v) ? implode(', ', $v) : ($v === true ? 'Yes' : $v)) . "\n";
            }
            if ($sub->photo_path)  $body .= "\nGraduation photo: https://thechurchofpeace.org/{$sub->photo_path}\n";
            if ($sub->output_path) $body .= "Slide attached (1920x1080 PNG).\n";
            $body .= "Manage: https://thechurchofpeace.org/admin/intake/{$form->slug}\n";

            try {
                Mail::raw($body, function ($m) use ($emails, $sub, $form, $name) {
                    $m->to($emails)->cc('contact@c-wellpics.com')->subject("New {$form->title} — {$name}");
                    if ($sub->submitter_email) $m->replyTo($sub->submitter_email, $name);
                    if ($sub->output_path && is_file(public_path($sub->output_path))) $m->attach(public_path($sub->output_path));
                });
            } catch (\Throwable $e) {
                Log::error('Intake notify email failed', ['submission' => $sub->id, 'error' => $e->getMessage()]);
            }
        }

        if ($smsTo = $form->setting('sms_to')) {
            app(TwilioNotifier::class)->send($smsTo, "New {$form->title}: {$name}. See thechurchofpeace.org/admin/intake/{$form->slug}");
        }
    }
}
