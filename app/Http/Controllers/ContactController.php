<?php
namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Public contact form for the landing page.
 *
 * Spam defense (in order of how each layer fires):
 *   1. Honeypot field   — bots that fill every input get silently dropped
 *   2. Time-since-render — humans take >3s; bots POST in milliseconds
 *   3. Min-character     — empty / 1-char submissions dropped
 *   4. English sanity    — message must contain at least one a-z run of >=3
 *                          chars (kills foreign-language probe bots like the
 *                          Hawaiian / Indonesian first-contact spam)
 *   5. Per-IP rate limit — set on the route (throttle:contact)
 */
class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact', [
            'sent' => session('sent', false),
            'page' => \App\Models\Page::bySlug('contact'),
            'renderToken' => Crypt::encryptString((string) time()),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        // Pretend-success response shared by every silent rejection so bots
        // can't tell which layer caught them.
        $silent = fn(string $reason) => tap(
            back()->with('sent', true),
            fn() => Log::info('contact rejected', [
                'reason' => $reason,
                'ip'     => $request->ip(),
                'ua'     => substr($request->userAgent() ?? '', 0, 120),
            ])
        );

        // ── 1. Honeypot ────────────────────────────────────────────────
        if (filled($request->input('form_meta_field')) || filled($request->input('website'))) {
            return $silent('honeypot');
        }

        // ── 2. Time-since-render ───────────────────────────────────────
        try {
            $renderedAt = (int) Crypt::decryptString($request->input('rendered_at', ''));
            $elapsed = time() - $renderedAt;
            if ($elapsed < 3 || $elapsed > 7200) {
                return $silent("timing.$elapsed");
            }
        } catch (\Throwable $e) {
            return $silent('token.invalid');
        }

        // ── 3. Validate ────────────────────────────────────────────────
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:200',
            'message' => 'required|string|min:8|max:5000',
        ]);

        // ── 4. English-word sanity check on the message body ──────────
        // At least one run of 3+ ASCII a-z chars. Rejects pure-foreign probes.
        if (! preg_match('/[a-zA-Z]{3,}/', $data['message'])) {
            return $silent('no-english');
        }

        // ── 5. Content-level spam filter (added 2026-06-11 after marketing/crypto bypass) ──
        if ($reason = app(\App\Services\SpamFilter::class)->detect($data["name"], $data["email"], $data["message"])) {
            return $silent("spam:$reason");
        }

        // ── Send ───────────────────────────────────────────────────────
        // Persist for the admin inbox
        \App\Models\ContactMessage::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'message'    => $data['message'],
            'ip'         => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 250),
        ]);

        try { app(\App\Services\PushService::class)->toClerks('✉️ New message', $data['name'] . ' wrote through the contact page.', '/admin/messages'); } catch (\Throwable $e) {}

        $body  = "From: {$data['name']} <{$data['email']}>\n";
        $body .= "Sent via the public contact form on thechurchofpeace.org\n";
        $body .= 'IP: ' . $request->ip() . ' · UA: ' . substr($request->userAgent() ?? '', 0, 200) . "\n";
        $body .= str_repeat('-', 60) . "\n\n";
        $body .= $data['message'];

        Mail::raw($body, function ($m) use ($data) {
            $m->to('contact@thechurchofpeace.org')
              ->cc('contact@c-wellpics.com')
              ->replyTo($data['email'], $data['name'])
              ->subject('Contact form — ' . $data['name']);
        });

        return redirect()->route('contact.show')->with('sent', true);
    }
}
