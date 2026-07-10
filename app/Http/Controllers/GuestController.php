<?php
namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * The digital guestbook (/connect). A first-time guest leaves their name and
 * contact; the follow-up ENGINE takes it from there (day-1 thanks, day-3
 * questions, birthdays) — the system remembers so nobody has to.
 * Spam defense mirrors prayer/contact: honeypot middleware + timing token.
 */
class GuestController extends Controller
{
    public function show(): View
    {
        return view('connect', ['renderToken' => Crypt::encryptString((string) time())]);
    }

    public function store(Request $request): RedirectResponse
    {
        $silent = fn (string $reason) => tap(back()->with('sent', true), fn () =>
            Log::info('guest card rejected', ['reason' => $reason, 'ip' => $request->ip()]));

        try {
            $elapsed = time() - (int) Crypt::decryptString($request->input('rendered_at', ''));
            if ($elapsed < 3 || $elapsed > 7200) return $silent("timing.$elapsed");
        } catch (\Throwable $e) { return $silent('token.invalid'); }

        $data = $request->validate([
            'name'            => 'required|string|max:120',
            'phone'           => 'nullable|string|max:40',
            'email'           => 'nullable|email|max:200',
            'birthday_month'  => 'nullable|integer|min:1|max:12',
            'birthday_day'    => 'nullable|integer|min:1|max:31',
            'wants_updates'   => 'sometimes|boolean',
            'wants_volunteer' => 'sometimes|boolean',
        ]);
        if (empty($data['phone']) && empty($data['email'])) {
            return back()->withErrors(['phone' => 'Leave a phone number or an email so we can say hello.'])->withInput();
        }
        if ($reason = app(\App\Services\SpamFilter::class)->detect($data['name'], $data['email'] ?? null, $data['name'])) {
            return $silent("spam:$reason");
        }

        $guest = Guest::create($data + [
            'visited_on' => now('America/New_York')->toDateString(),
            'ip_hash'    => hash('sha256', (string) $request->ip()),
        ]);

        // The engine's memory: two scheduled touches. Birthdays are computed, not stored.
        $guest->followups()->create(['kind' => 'thanks',    'due_on' => now('America/New_York')->addDay()->toDateString()]);
        $guest->followups()->create(['kind' => 'questions', 'due_on' => now('America/New_York')->addDays(3)->toDateString()]);

        return redirect()->route('connect')->with('sent', true);
    }
}
