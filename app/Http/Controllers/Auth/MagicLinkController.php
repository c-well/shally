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
        // Honeypot: bots fill every visible field. Real users leave 'website' empty.
        if (filled($request->input('website'))) {
            \App\Models\AuditLog::record(
                event: 'magic_link_honeypot',
                description: 'Honeypot tripped by bot from IP ' . $request->ip(),
                meta: ['ua' => substr((string) $request->userAgent(), 0, 200)],
            );
            $this->maybeBlockIp($request->ip(), 'honeypot');
            return back()->with('status', 'If that email matches an account, a sign-in link is on its way. Check your inbox in the next minute or two.');
        }

        $data = $request->validate([
            'email' => 'required|email|max:255',
        ]);
        $email = strtolower(trim($data['email']));

        // Rate limits — per email and per IP
        $emailKey = 'magic-email:' . sha1($email);
        $ipKey    = 'magic-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($emailKey, 3) || RateLimiter::tooManyAttempts($ipKey, 5)) {
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
        if (!$user) $this->maybeBlockIp($request->ip(), 'magic_link_no_match');

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

    /**
     * Count this IP's failure-type audit log entries in the last 24h.
     * If >20, insert into blocked_ips with 24h expiry. Middleware then 429s subsequent requests.
     */
    private function maybeBlockIp(string $ip, string $reason): void
    {
        if (\DB::table('blocked_ips')->where('ip_address', $ip)->exists()) return; // already blocked
        $threshold = 20;
        $count = \DB::table('audit_logs')
            ->where('ip_address', $ip)
            ->whereIn('event', ['magic_link_request', 'login_failed', 'magic_link_honeypot'])
            ->where('created_at', '>=', now()->subDay())
            ->count();
        if ($count > $threshold) {
            \DB::table('blocked_ips')->insert([
                'ip_address' => $ip,
                'reason' => $reason . ' (>'. $threshold .' failures/24h)',
                'blocked_at' => now(),
                'expires_at' => now()->addDay(),
                'hits_at_block' => $count,
            ]);
            \App\Models\AuditLog::record(
                event: 'ip_auto_blocked',
                description: 'Auto-blocked IP ' . $ip . ' for 24h after ' . $count . ' failures',
                meta: ['ip' => $ip, 'count' => $count, 'reason' => $reason],
            );
        }
    }
}
