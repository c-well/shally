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

        // Time-since-render gate (same trick as the contact form).
        try {
            $rendered = (int) Crypt::decryptString((string) $request->input('rendered_at'));
            if (time() - $rendered < 2) {
                return back()->with('sent', true); // too fast → silently accept-and-drop
            }
        } catch (\Throwable $e) { /* missing/garbage token — let validation handle it */ }

        [$data, $photo] = $this->validateAgainstSchema($request, $form);

        // Persist the photo (mirror the events flyer pattern: straight into public/).
        $photoPath = null;
        if ($photo) {
            $dir = public_path('intake-media/photos');
            if (! is_dir($dir)) mkdir($dir, 0755, true);
            $name = Str::uuid() . '.' . strtolower($photo->getClientOriginalExtension());
            $photo->move($dir, $name);
            $photoPath = 'intake-media/photos/' . $name;
        }

        $sub = IntakeSubmission::create([
            'intake_form_id'  => $form->id,
            'data'            => $data,
            'photo_path'      => $photoPath,
            'submitter_name'  => $data['name'] ?? null,
            'submitter_email' => $data['submitter_email'] ?? ($data['email'] ?? null),
            'ip'              => $request->ip(),
        ]);

        // Build the artifact for this form type.
        if ($form->output_type === 'graduation') {
            try {
                $sub->update(['output_path' => app(GradCardRenderer::class)->render($sub)]);
            } catch (\Throwable $e) {
                Log::error('Grad card render failed', ['submission' => $sub->id, 'error' => $e->getMessage()]);
            }
        }

        $this->notify($form, $sub);

        return back()->with('intake_done', $form->setting('thank_you')
            ?: 'Thank you — your submission has been received.');
    }

    /* ───────────────────────── validation ───────────────────────── */

    /** @return array{0:array<string,mixed>,1:?\Illuminate\Http\UploadedFile} */
    private function validateAgainstSchema(Request $request, IntakeForm $form): array
    {
        $rules = []; $attributes = []; $photoField = null;
        $input = $request->all();

        foreach ($form->fields() as $f) {
            $key = $f['key'];
            $visible = $this->showIf($f['show_if'] ?? null, $input);
            $required = ($f['required'] ?? false) && $visible;
            $attributes[$key] = strtolower($f['label'] ?? $key);

            if (($f['type'] ?? 'text') === 'photo') {
                $photoField = $key;
                $rules[$key] = array_filter([
                    $required ? 'required' : 'nullable',
                    'file', 'extensions:jpg,jpeg,png,webp,gif,heic,heif', 'max:12288',
                ]);
                continue;
            }

            $r = [$required ? 'required' : 'nullable'];
            switch ($f['type'] ?? 'text') {
                case 'email':    $r[] = 'email'; $r[] = 'max:200'; break;
                case 'textarea': $r[] = 'string'; $r[] = 'max:5000'; break;
                case 'select':
                    if (! empty($f['options'])) $r[] = 'in:' . implode(',', $f['options']);
                    break;
                default:         $r[] = 'string'; $r[] = 'max:500';
            }
            // Only constrain hidden fields if a value somehow arrived.
            $rules[$key] = $visible ? $r : ['nullable', 'string', 'max:5000'];
        }

        $validated = Validator::make($input, $rules, [], $attributes)->validate();

        // Pull the file out separately; keep only scalar field values in data.
        $photo = $photoField ? $request->file($photoField) : null;
        unset($validated[$photoField]);

        return [$validated, $photo];
    }

    /** Evaluate a show_if condition against the submitted/known data. */
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
            $body  = "New submission to \"{$form->title}\" on thechurchofpeace.org\n";
            $body .= str_repeat('-', 60) . "\n\n";
            foreach ($sub->data as $k => $v) {
                if ($v === null || $v === '') continue;
                $label = $form->field($k)['label'] ?? $k;
                $body .= "{$label}: {$v}\n";
            }
            if ($sub->photo_path)  $body .= "\nPhoto: https://thechurchofpeace.org/" . $sub->photo_path . "\n";
            if ($sub->output_path) $body .= "Slide attached (1920x1080 PNG). Also at the admin gallery:\n";
            $body .= "Manage: https://thechurchofpeace.org/admin/intake/{$form->slug}\n";

            try {
                Mail::raw($body, function ($m) use ($emails, $sub, $form, $name) {
                    $m->to($emails)->cc('contact@c-wellpics.com')
                      ->subject("New {$form->title} — {$name}");
                    if ($sub->submitter_email) $m->replyTo($sub->submitter_email, $name);
                    if ($sub->output_path && is_file(public_path($sub->output_path))) {
                        $m->attach(public_path($sub->output_path));
                    }
                });
            } catch (\Throwable $e) {
                Log::error('Intake notify email failed', ['submission' => $sub->id, 'error' => $e->getMessage()]);
            }
        }

        if ($smsTo = $form->setting('sms_to')) {
            app(TwilioNotifier::class)->send(
                $smsTo,
                "New {$form->title}: {$name}. See thechurchofpeace.org/admin/intake/{$form->slug}"
            );
        }
    }
}
