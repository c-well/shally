<?php
namespace App\Http\Controllers;

use App\Models\PeaceQaPair;
use App\Services\Peace\ResendMailer;
use App\Services\Peace\SeekerAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Public, no-login-needed auth endpoints for /find-peace seekers.
 *
 *   POST  /peace/auth/request           — email submitted, magic link sent
 *   GET   /peace/auth/verify/{token}    — consumed on click → cookie set → redirect
 *   POST  /peace/auth/logout            — clears cookie, redirects home
 *
 * Bot defenses layered into the request flow:
 *   - honeypot field "website" (silent reject if filled)
 *   - time-trap: if hidden form_opened_at < 2 sec ago, silent reject
 *   - per-IP throttle: 3 requests / 60 min
 *   - per-email throttle: 3 requests / 60 min
 *   - app-level BlockAbusiveIps middleware (existing)
 */
class PeaceAuthController extends Controller
{
    public function __construct(
        private SeekerAuth $auth,
        private ResendMailer $mailer,
    ) {}

    public function request(Request $req): JsonResponse|RedirectResponse
    {
        // Honeypot
        if (filled($req->input('website'))) {
            \App\Models\AuditLog::record(
                event: 'peace_seeker_honeypot',
                description: 'Honeypot tripped in seeker signup form',
            );
            return $this->okResponse($req, 'Check your inbox. If that email matches anything, a link is on its way.');
        }

        // Time-trap: forms opened less than 2 sec ago are bots.
        $openedAt = (int) $req->input('form_opened_at', 0);
        if ($openedAt > 0 && (time() * 1000 - $openedAt) < 2000) {
            \App\Models\AuditLog::record(
                event: 'peace_seeker_timetrap',
                description: 'Form submitted in <2 sec — bot',
            );
            return $this->okResponse($req, 'Check your inbox. If that email matches anything, a link is on its way.');
        }

        $data = $req->validate([
            'email'      => 'required|email|max:255',
            'intent'     => 'nullable|array',
            'intent.type'  => 'nullable|string|max:40',
            'intent.qa_id' => 'nullable|integer|exists:peace_qa_pairs,id',
            'source_url' => 'nullable|url|max:500',
        ]);

        $email = mb_strtolower(trim($data['email']));

        // Rate limits
        $emailKey = 'peace-email:' . sha1($email);
        $ipKey    = 'peace-ip:' . $req->ip();
        if (RateLimiter::tooManyAttempts($emailKey, 3) || RateLimiter::tooManyAttempts($ipKey, 3)) {
            return $this->okResponse($req, "We've sent links recently. Please check your inbox or wait an hour.");
        }
        RateLimiter::hit($emailKey, 3600);
        RateLimiter::hit($ipKey, 3600);

        $rawToken = $this->auth->issueLink($email, $data['intent'] ?? null, $data['source_url'] ?? null, $req);

        $url = url('/peace/auth/verify/' . $rawToken);
        $intent = $data['intent'] ?? null;
        $heading = match ($intent['type'] ?? null) {
            'save_qa' => 'Save your spot.',
            default   => 'Tap to come back.',
        };

        $this->mailer->sendView(
            to: $email,
            subject: 'One-tap sign-in to Finding Peace',
            view: 'peace.emails.seeker.magic-link',
            viewData: [
                'email'   => $email,
                'url'     => $url,
                'heading' => $heading,
            ]
        );

        \App\Models\AuditLog::record(
            event: 'peace_seeker_link_sent',
            description: 'Magic link sent to ' . $email . ' (intent: ' . ($intent['type'] ?? 'direct') . ')',
        );

        return $this->okResponse($req, 'Check your inbox. The link expires in 30 minutes.');
    }

    public function verify(Request $req, string $token): RedirectResponse
    {
        $result = $this->auth->consume($token);
        if (!$result) {
            return redirect('/find-peace')->with('peace_flash', 'That link expired or was already used. Drop your email again.');
        }

        $sub = $result['subscriber'];

        // Perform intent (e.g., save_qa)
        $intent = $result['intent'] ?? null;
        if ($intent && ($intent['type'] ?? null) === 'save_qa' && !empty($intent['qa_id'])) {
            DB::table('peace_saved_qas')->updateOrInsert(
                ['subscriber_id' => $sub->id, 'qa_id' => (int) $intent['qa_id']],
                ['saved_at' => now()]
            );
        }

        \App\Models\AuditLog::record(
            event: 'peace_seeker_signin',
            description: 'Seeker signed in: ' . $sub->email,
        );

        $cookie = Cookie::make(
            name: $this->auth->cookieName(),
            value: $this->auth->mintCookieValue($sub->id),
            minutes: $this->auth->cookieDays() * 24 * 60,
            path: '/',
            domain: null,
            secure: $req->isSecure(),
            httpOnly: true,
            sameSite: 'lax',
        );

        $redirect = $result['source_url'] ?: '/find-peace/saved';
        // Trust source_url only if it's same-origin
        if (!str_starts_with($redirect, '/') && !str_starts_with($redirect, url('/'))) {
            $redirect = '/find-peace/saved';
        }
        return redirect($redirect)->withCookie($cookie)->with('peace_flash', 'Welcome back.');
    }

    public function logout(Request $req): RedirectResponse
    {
        return redirect('/find-peace')->withCookie(Cookie::forget($this->auth->cookieName()));
    }

    private function okResponse(Request $req, string $msg): JsonResponse|RedirectResponse
    {
        if ($req->expectsJson() || $req->ajax()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }
        return back()->with('peace_flash', $msg);
    }
}
