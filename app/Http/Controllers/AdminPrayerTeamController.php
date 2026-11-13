<?php

namespace App\Http\Controllers;

use App\Models\Intercessor;
use App\Services\Intake\TwilioNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin: who is on the prayer team. Add people, text them a sign-in link, pause or remove them. */
class AdminPrayerTeamController extends Controller
{
    public function index(TwilioNotifier $twilio): View
    {
        $team = Intercessor::orderByDesc('active')->orderByDesc('role')->orderBy('name')->get();
        return view('admin.prayer-team', ['team' => $team, 'canText' => $twilio->configured()]);
    }

    public function store(Request $request, TwilioNotifier $twilio): RedirectResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:80',
            'phone' => 'required|string|max:30',
            'role'  => ['required', Rule::in(['regular', 'head'])],
        ]);
        $phone = Intercessor::normalizePhone($data['phone']);
        if (strlen(preg_replace('/\D/', '', $phone)) < 11) {
            return back()->withInput()->withErrors(['phone' => 'That phone number looks short. Use all 10 digits.']);
        }
        if (Intercessor::where('phone', $phone)->exists()) {
            return back()->withInput()->withErrors(['phone' => 'Someone on the list already has that number.']);
        }

        $i = Intercessor::create([
            'name' => trim($data['name']), 'phone' => $phone, 'role' => $data['role'],
            'pin_hash' => '', 'active' => true, 'added_by_user_id' => $request->user()->id,
        ]);

        $sent = $request->boolean('text_now') && $this->sendLink($i, $twilio, welcome: true);
        return back()->with('ok', $i->name . ' is on the prayer team.' . ($sent ? ' A sign-in link is on its way to their phone.' : ''));
    }

    public function update(Request $request, Intercessor $intercessor, TwilioNotifier $twilio): RedirectResponse
    {
        $action = $request->input('action');
        if ($action === 'text') {
            $ok = $this->sendLink($intercessor, $twilio, welcome: false);
            return back()->with('ok', $ok ? 'Sign-in link sent to ' . $intercessor->name . '.' : 'The text did not go through. Check the number and try again.');
        }
        if ($action === 'pause' || $action === 'resume') {
            $intercessor->forceFill(['active' => $action === 'resume'])->save();
            if ($action === 'pause') $intercessor->sessions()->delete();
            return back()->with('ok', $intercessor->name . ($action === 'pause' ? ' is paused and signed out. They get no texts.' : ' is back on the team.'));
        }
        if ($action === 'role') {
            $intercessor->forceFill(['role' => $intercessor->role === 'head' ? 'regular' : 'head'])->save();
            return back()->with('ok', $intercessor->name . ' is now ' . ($intercessor->role === 'head' ? 'a team lead.' : 'a team member.'));
        }
        abort(422);
    }

    private function sendLink(Intercessor $i, TwilioNotifier $twilio, bool $welcome): bool
    {
        $intro = $welcome
            ? "Shalom prayer team: you've been added to the prayer team. When someone sends a prayer request, you'll get a text. Tap to sign in on this phone: "
            : 'Shalom prayer team: tap to sign in on this phone. ';
        return $twilio->send($i->phone, $intro . $i->signInLink(60 * 24 * 7) . ' (works once, for 7 days)');
    }
}
