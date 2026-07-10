<?php
namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\GuestFollowup;
use App\Services\Intake\TwilioNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** Guests admin: the follow-up queue. Engine drafts, humans personalize. */
class AdminGuestsController extends Controller
{
    public function index(): View
    {
        return view('admin.guests', ['guests' => Guest::with('followups')->orderByDesc('created_at')->limit(200)->get()]);
    }

    public function updateFollowup(Request $request, GuestFollowup $followup): JsonResponse
    {
        $data = $request->validate(['body' => 'sometimes|nullable|string|max:800', 'status' => 'sometimes|in:skipped,pending']);
        if (array_key_exists('body', $data)) $data['body'] = trim((string) $data['body']) ?: null;
        $followup->update($data);
        return response()->json(['ok' => true]);
    }

    public function sendFollowup(GuestFollowup $followup, TwilioNotifier $sms): JsonResponse
    {
        abort_unless($followup->status === 'pending', 422);
        $g = $followup->guest;
        $body = trim((string) ($followup->body ?: $followup->defaultBody()));
        $ok = false; $channel = null;
        if ($g->phone && $sms->configured()) { $ok = $sms->send($g->phone, $body); $channel = 'sms'; }
        if (! $ok && $g->email) {
            try {
                Mail::raw($body . "\n\n— The Church of Peace · thechurchofpeace.org",
                    fn ($m) => $m->to($g->email)->cc('contact@c-wellpics.com')->subject('From the Shalom family'));
                $ok = true; $channel = 'email';
            } catch (\Throwable $e) { $ok = false; }
        }
        $followup->update(['status' => $ok ? 'sent' : 'failed', 'channel' => $channel, 'sent_at' => $ok ? now() : null]);
        return response()->json(['ok' => $ok, 'message' => $ok ? null : 'No reachable channel — check phone/email']);
    }
}
