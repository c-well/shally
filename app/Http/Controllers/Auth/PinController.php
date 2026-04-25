<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PinController extends Controller
{
    /** GET /pin — login form. */
    public function showLogin(): View
    {
        return view('auth.pin-login');
    }

    /** POST /pin — verify name + PIN, sign user in. */
    public function attempt(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'pin'  => 'required|string|min:4|max:8|regex:/^\d+$/',
        ]);
        $namePrefix = trim($data['name']);
        $pin        = $data['pin'];

        // Rate limits — per IP and per name (separate counters)
        $ipKey   = 'pin-ip:' . sha1($request->ip());
        $nameKey = 'pin-name:' . sha1(strtolower($namePrefix));
        if (RateLimiter::tooManyAttempts($ipKey, 10) || RateLimiter::tooManyAttempts($nameKey, 5)) {
            AuditLog::record(
                event: 'pin_login_blocked',
                description: "Rate-limit hit for PIN attempt: name='{$namePrefix}'",
            );
            return back()->withInput()->withErrors([
                'pin' => "Too many attempts. Try again in a bit.",
            ]);
        }

        // Look up candidates by name prefix (case-insensitive). Fast lookup, narrow pool.
        $candidates = User::where('name', 'like', $namePrefix . '%')
            ->whereNotNull('pin_hash')
            ->limit(20)
            ->get();

        // Verify PIN against each candidate. Bcrypt is slow on purpose; verifying ~20 hashes
        // worst-case is still <500ms which is fine for an interactive login.
        $matched = $candidates->first(fn ($u) => Hash::check($pin, $u->pin_hash));

        if (! $matched) {
            RateLimiter::hit($ipKey, 900);   // 15 min
            RateLimiter::hit($nameKey, 3600); // 1 hour
            AuditLog::record(
                event: 'pin_login_failed',
                description: "Failed PIN attempt for name: '{$namePrefix}'",
                meta: ['name_prefix' => $namePrefix],
            );
            return back()->withInput()->withErrors([
                'pin' => "That name and PIN don't match. Try again or ask Karlon for help.",
            ]);
        }

        Auth::login($matched, $remember = true);
        $request->session()->regenerate();
        RateLimiter::clear($ipKey);
        RateLimiter::clear($nameKey);

        AuditLog::record(
            event: 'pin_login_success',
            userId: $matched->id,
            description: $matched->name . ' signed in via PIN',
        );

        return redirect()->intended('/');
    }
}
