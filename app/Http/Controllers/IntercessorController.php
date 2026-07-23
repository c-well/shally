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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IntercessorController extends Controller
{
    /** Sign-in door — tap your name, then enter PIN. Public but no-index. */
    public function signIn(Request $request): View
    {
        $intercessors = Intercessor::where('active', true)
            ->orderBy('role', 'desc')   // heads first, then regulars
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('intercessors.sign-in', compact('intercessors'));
    }

    /** PIN check + issue cookie. Honeypot + rate limit. */
    public function attemptSignIn(Request $request): RedirectResponse
    {
        // Honeypot
        if (filled($request->input('form_meta_field')) || filled($request->input('website'))) {
            return back()->with('sent', true);
        }

        $data = $request->validate([
            'intercessor_id' => 'required|integer|exists:intercessors,id',
            'pin'            => 'required|string|size:6',
        ]);

        // Rate limit: 6 wrong PINs / IP / 10 min
        $rlKey = 'intercessor-pin:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rlKey, 6)) {
            return back()->withErrors(['pin' => 'Too many tries — wait a few minutes and try again.']);
        }

        $intercessor = Intercessor::find($data['intercessor_id']);
        if (! $intercessor || ! $intercessor->active) {
            return back()->withErrors(['pin' => 'This name is no longer active.']);
        }

        // Per-intercessor lockout
        if ($intercessor->pin_locked_until && $intercessor->pin_locked_until->isFuture()) {
            return back()->withErrors(['pin' => 'This account is temporarily locked. Try again later.']);
        }

        if (! $intercessor->checkPin($data['pin'])) {
            RateLimiter::hit($rlKey, 600);
            $intercessor->increment('pin_wrong_count');
            if ($intercessor->pin_wrong_count >= 10) {
                $intercessor->pin_locked_until = now()->addMinutes(15);
                $intercessor->pin_wrong_count  = 0;
                $intercessor->save();
            }
            return back()->withErrors(['pin' => 'That PIN is not right. Try again, or use "Text me my PIN" below.']);
        }

        // Success: clear counters, mint session
        $intercessor->pin_wrong_count = 0;
        $intercessor->pin_locked_until = null;
        $intercessor->last_seen_at = now();
        $intercessor->last_ip = $request->ip();
        $intercessor->save();

        $rawToken = bin2hex(random_bytes(32));   // 64-char hex
        IntercessorSession::create([
            'intercessor_id' => $intercessor->id,
            'token_hash'     => IntercessorSession::hashToken($rawToken),
            'last_ip'        => $request->ip(),
            'user_agent'     => Str::limit((string) $request->userAgent(), 400, ''),
            'last_seen_at'   => now(),
            'expires_at'     => now()->addMonths(6),
        ]);

        return redirect()->route('intercessors.dashboard')
            ->withCookie(cookie(EnsureIntercessor::COOKIE, $rawToken, minutes: 60 * 24 * 180, secure: true, httpOnly: true, sameSite: 'lax'));
    }

    /** "I forgot my PIN" — texts the CURRENT PIN to the number on file. */
    public function forgotPin(Request $request, TwilioNotifier $twilio): RedirectResponse
    {
        if (filled($request->input('form_meta_field'))) return back()->with('sent', true);

        $data = $request->validate(['intercessor_id' => 'required|integer|exists:intercessors,id']);

        $rlKey = 'intercessor-forgot:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rlKey, 4)) {
            return back()->with('pin_texted', true);
        }
        RateLimiter::hit($rlKey, 900);

        $i = Intercessor::find($data['intercessor_id']);
        if (! $i || ! $i->active) return back()->with('pin_texted', true);   // silent OK either way

        // The stored pin_hash is one-way — we can't recover the PIN. We rotate to a
        // fresh 6-digit code and text THAT. The requester's ruling: "sending them
        // the pin is fine" — the PIN is a friction gate, not a secret.
        $newPin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $i->pin_hash = Hash::make($newPin);
        $i->pin_wrong_count = 0;
        $i->pin_locked_until = null;
        $i->save();

        $twilio->send($i->phone, "Shalom prayer team: your PIN is $newPin. Sign in at https://thechurchofpeace.org/intercessors");

        return back()->with('pin_texted', true);
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
