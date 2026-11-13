<?php
namespace App\Http\Controllers;

use App\Http\Middleware\EnsureIntercessor;
use App\Models\Intercessor;
use App\Models\IntercessorPrayerPrayed;
use App\Models\IntercessorPrayerView;
use App\Models\IntercessorSession;
use App\Models\PrayerRequest;
use App\Services\Intake\TwilioNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IntercessorController extends Controller
{
    /**
     * The prayer team's door. No names are shown: a member types the phone number
     * the church has on file and gets a sign-in link by text. Everyone else sees
     * what the page is for and how to send a prayer request.
     */
    public function signIn(Request $request): View
    {
        return view('intercessors.sign-in');
    }

    /** Text a sign-in link to a team member's phone. Same answer whether or not the number is on file. */
    public function sendLink(Request $request, TwilioNotifier $twilio): RedirectResponse
    {
        $done = back()->with('link_sent', true);
        if (filled($request->input('website'))) return $done;

        $data = $request->validate(['phone' => 'required|string|max:30']);
        $rlKey = 'intercessor-link:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rlKey, 5)) return $done;
        RateLimiter::hit($rlKey, 900);

        $phone = Intercessor::normalizePhone($data['phone']);
        $i = Intercessor::where('active', true)->where('phone', $phone)->first();
        if ($i) {
            $phoneKey = 'intercessor-link-phone:' . $i->id;
            if (! RateLimiter::tooManyAttempts($phoneKey, 3)) {
                RateLimiter::hit($phoneKey, 3600);
                $twilio->send($i->phone, 'Shalom prayer team: tap to sign in on this phone. ' . $i->signInLink(30) . ' (works once, for 30 minutes)');
            }
        }
        return $done;
    }

    /** The link from the text lands here. A button, not an automatic sign-in, so message previews cannot use it up. */
    public function linkLanding(string $token): View
    {
        $i = Intercessor::fromLinkToken($token, consume: false);
        return view('intercessors.link', ['intercessor' => $i, 'token' => $token]);
    }

    public function linkSignIn(Request $request, string $token): RedirectResponse
    {
        $i = Intercessor::fromLinkToken($token, consume: true);
        if (! $i) return redirect()->route('intercessors.signIn')->withErrors(['phone' => 'That link has been used or has expired. Ask for a new one below.']);

        $i->forceFill(['last_seen_at' => now(), 'last_ip' => $request->ip()])->save();
        $rawToken = bin2hex(random_bytes(32));
        IntercessorSession::create([
            'intercessor_id' => $i->id,
            'token_hash'     => IntercessorSession::hashToken($rawToken),
            'last_ip'        => $request->ip(),
            'user_agent'     => Str::limit((string) $request->userAgent(), 400, ''),
            'last_seen_at'   => now(),
            'expires_at'     => now()->addMonths(6),
        ]);

        return redirect()->route('intercessors.dashboard')
            ->withCookie(cookie(EnsureIntercessor::COOKIE, $rawToken, minutes: 60 * 24 * 180, secure: true, httpOnly: true, sameSite: 'lax'));
    }

    /** Main dashboard — list of prayer requests, newest first. */
    public function dashboard(Request $request): View
    {
        $me = $request->attributes->get('intercessor');

        $requests = PrayerRequest::orderByDesc('created_at')
            ->limit(60)
            ->get();

        // Mark them all as viewed by me (batch upsert)
        $now = now();
        foreach ($requests as $r) {
            IntercessorPrayerView::firstOrCreate(
                ['intercessor_id' => $me->id, 'prayer_request_id' => $r->id],
                ['viewed_at' => $now]
            );
        }

        // Prefetch: who else has seen / prayed for each
        $ids = $requests->pluck('id');
        $viewsByReq  = DB::table('intercessor_prayer_views')
            ->join('intercessors', 'intercessor_id', '=', 'intercessors.id')
            ->whereIn('prayer_request_id', $ids)
            ->select('prayer_request_id', 'intercessors.name', 'viewed_at')
            ->get()->groupBy('prayer_request_id');
        $prayedByReq = DB::table('intercessor_prayer_prayed')
            ->join('intercessors', 'intercessor_id', '=', 'intercessors.id')
            ->whereIn('prayer_request_id', $ids)
            ->select('prayer_request_id', 'intercessors.name', 'prayed_at', 'intercessor_id')
            ->get()->groupBy('prayer_request_id');

        return view('intercessors.dashboard', [
            'me'          => $me,
            'requests'    => $requests,
            'viewsByReq'  => $viewsByReq,
            'prayedByReq' => $prayedByReq,
        ]);
    }

    /** Toggle "I prayed for this." */
    public function togglePrayed(Request $request, PrayerRequest $prayer): RedirectResponse
    {
        $me = $request->attributes->get('intercessor');
        $existing = IntercessorPrayerPrayed::where('intercessor_id', $me->id)->where('prayer_request_id', $prayer->id)->first();
        if ($existing) {
            $existing->delete();
        } else {
            IntercessorPrayerPrayed::create([
                'intercessor_id'    => $me->id,
                'prayer_request_id' => $prayer->id,
                'prayed_at'         => now(),
            ]);
        }
        return back();
    }

    public function signOut(Request $request): RedirectResponse
    {
        $raw = $request->cookie(EnsureIntercessor::COOKIE);
        if ($raw) {
            IntercessorSession::where('token_hash', IntercessorSession::hashToken($raw))->delete();
        }
        return redirect()->route('intercessors.signIn')->withCookie(cookie()->forget(EnsureIntercessor::COOKIE));
    }
}
