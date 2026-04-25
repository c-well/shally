<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MagicLinkToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class MagicLinkController extends Controller
{
    /** GET /auth/magic-link — request form. */
    public function showRequest(): View
    {
        return view('auth.magic-link-request');
    }

    /** POST /auth/magic-link — send a magic link to an existing user's email. */
    public function sendLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
        ]);
        $email = strtolower(trim($data['email']));

        // Rate limits — per email and per IP
        $emailKey = 'magic-email:' . sha1($email);
        $ipKey    = 'magic-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($emailKey, 3) || RateLimiter::tooManyAttempts($ipKey, 10)) {
            return back()->withInput()->with('status', 'Too many attempts. Please try again in an hour.');
        }
        RateLimiter::hit($emailKey, 3600); // 1h
        RateLimiter::hit($ipKey, 3600);

        // Look up user. We DON'T leak whether the email exists — same UX either way.
        $user = User::where('email', $email)->first();

        // Audit-log every request, whether the email matched or not (helps detect probing).
        \App\Models\AuditLog::record(
            event: 'magic_link_request',
            userId: $user?->id,
            description: 'Magic link requested for: ' . $email . ($user ? '' : ' (no match)'),
            meta: ['email' => $email, 'matched' => (bool) $user],
        );

        if ($user) {
            [$plainToken] = MagicLinkToken::issueFor(
                $user,
                $request->ip(),
                (string) $request->userAgent()
            );
            $url = url('/auth/magic-link/' . $plainToken);

            try {
                // Super-admins get the dark "command-center" version of the email.
                // Everyone else (clerks + congregation) gets the clean white default.
                $template = ($user->role === 'super_admin')
                    ? 'emails.magic-link-dark'
                    : 'emails.magic-link';

                Mail::send($template, [
                    'name' => $user->name ?: 'there',
                    'url'  => $url,
                ], function ($m) use ($email) {
                    $m->to($email)->subject('Sign in to The Church of Peace');
                });
            } catch (\Throwable $e) {
                \Log::warning('Magic link email failed', ['email' => $email, 'error' => $e->getMessage()]);
                // still flash success so we don't leak existence
            }
        }

        // Always return the same message — prevents email enumeration
        return back()->with('status', 'If that email matches an account, a sign-in link is on its way. Check your inbox in the next minute or two.');
    }

    /** GET /auth/magic-link/{token} — consume a magic link and sign the user in. */
    public function consume(Request $request, string $token): RedirectResponse
    {
        $magic = MagicLinkToken::findValid($token);
        if (! $magic) {
            return redirect()->route('login')->with('status', 'That sign-in link is invalid, expired, or already used. Request a new one below.');
        }

        $user = $magic->user;
        if (! $user) {
            return redirect()->route('login')->with('status', 'Account not found.');
        }

        $magic->consume();
        Auth::login($user, $remember = true);
        $request->session()->regenerate();

        \App\Models\AuditLog::record(
            event: 'magic_link_consume',
            userId: $user->id,
            description: $user->name . ' signed in via magic link',
        );

        return redirect()->intended('/');
    }
}
