<?php
namespace App\Http\Controllers;

use App\Models\PrayerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Public prayer request form. Submissions stored in DB and emailed
 * to the prayer team. Never publicly displayed.
 *
 * Spam defense mirrors ContactController:
 *   honeypot → encrypted timestamp → length → english-word check
 *   then throttle:3,60 set on the route.
 */
class PrayerController extends Controller
{
    public function show(): View
    {
        return view('prayer', [
            'sent' => session('sent', false),
            'renderToken' => Crypt::encryptString((string) time()),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $silent = fn(string $reason) => tap(
            back()->with('sent', true),
            fn() => Log::info('prayer rejected', [
                'reason' => $reason,
                'ip'     => $request->ip(),
                'ua'     => substr($request->userAgent() ?? '', 0, 120),
            ])
        );

        if (filled($request->input('website'))) return $silent('honeypot');

        try {
            $renderedAt = (int) Crypt::decryptString($request->input('rendered_at', ''));
            $elapsed = time() - $renderedAt;
            if ($elapsed < 3 || $elapsed > 7200) return $silent("timing.$elapsed");
        } catch (\Throwable $e) { return $silent('token.invalid'); }

        $data = $request->validate([
            'name'          => 'nullable|string|max:120',
            'email'         => 'nullable|email|max:200',
            'phone'         => 'nullable|string|max:40',
            'body'          => 'required|string|min:8|max:5000',
            'want_followup' => 'sometimes|boolean',
            'keep_private'  => 'sometimes|boolean',
        ]);

        if (! preg_match('/[a-zA-Z]{3,}/', $data['body'])) return $silent('no-english');

        $pr = PrayerRequest::create([
            'name'          => $data['name'] ?? null,
            'email'         => $data['email'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'body'          => $data['body'],
            'want_followup' => (bool) ($data['want_followup'] ?? false),
            'keep_private'  => (bool) ($data['keep_private'] ?? true),
            'ip'            => $request->ip(),
            'user_agent'    => substr($request->userAgent() ?? '', 0, 250),
        ]);

        $lines  = "New prayer request from thechurchofpeace.org\n";
        $lines .= str_repeat('-', 60) . "\n";
        $lines .= 'From:           ' . ($pr->name  ?: '— anonymous —') . "\n";
        $lines .= 'Email:          ' . ($pr->email ?: '— not given —')  . "\n";
        $lines .= 'Phone:          ' . ($pr->phone ?: '— not given —')  . "\n";
        $lines .= 'Wants follow-up: ' . ($pr->want_followup ? 'YES' : 'no') . "\n";
        $lines .= 'Keep private:   ' . ($pr->keep_private  ? 'YES (prayer team only)' : 'no — okay to share with congregation') . "\n";
        $lines .= 'IP / UA:        ' . $pr->ip . ' · ' . substr($pr->user_agent ?? '', 0, 80) . "\n";
        $lines .= str_repeat('-', 60) . "\n\n";
        $lines .= $pr->body . "\n";

        Mail::raw($lines, function ($m) use ($pr) {
            $m->to('contact@thechurchofpeace.org')
              ->cc('contact@c-wellpics.com')
              ->subject('Prayer request' . ($pr->name ? ' — ' . $pr->name : ' (anonymous)'));
            if ($pr->email) $m->replyTo($pr->email, $pr->name ?: null);
        });

        // Auto-acknowledge to the requester so they know the request landed.
        if ($pr->email) {
            $ackBody  = ($pr->name ? "Hi {$pr->name},

" : "Hello,

");
            $ackBody .= "Thank you for sharing your prayer request with us. The prayer team at The Church of Peace has received it and will be lifting your need before God.

";
            if ($pr->want_followup) {
                $ackBody .= "Since you asked for follow-up, someone from the church will reach out within 24 hours.

";
            }
            $ackBody .= '"The Lord is near to all who call on him." — Psalm 145:18' . "

";
            $ackBody .= "In peace,
The Church of Peace · Bronx, NY
https://thechurchofpeace.org
";
            try {
                Mail::raw($ackBody, function ($m) use ($pr) {
                    $m->to($pr->email, $pr->name ?: null)
                      ->subject('We received your prayer request');
                });
            } catch (\Throwable $e) {
                Log::warning('prayer ack failed', ['email' => $pr->email, 'err' => $e->getMessage()]);
            }
        }

        return redirect()->route('prayer.show')->with('sent', true);
    }
}
