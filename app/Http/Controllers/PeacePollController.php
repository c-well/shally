<?php
namespace App\Http\Controllers;

use App\Models\PeacePoll;
use App\Models\PeacePollOption;
use App\Models\PeacePollResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Anonymous poll submit endpoint for /find-peace.
 *
 * Defenses:
 *  - honeypot (hidden field "hp" must be empty)
 *  - 24-hour single-vote window per IP+poll (ip_hash check)
 *  - rate-limit via Laravel throttle middleware (recommended in route)
 *  - response_count is denormalized for fast read
 */
class PeacePollController extends Controller
{
    public function submit(Request $request)
    {
        // Honeypot — silent reject
        if (filled($request->input('hp'))) {
            return response()->json(['ok' => true, 'silent' => true]);
        }

        $data = $request->validate([
            'poll_id'   => 'required|integer|exists:peace_polls,id',
            'option_id' => 'required|integer|exists:peace_poll_options,id',
        ]);

        $poll = PeacePoll::with('options')->findOrFail($data['poll_id']);
        $option = $poll->options->firstWhere('id', $data['option_id']);
        if (! $option) {
            return response()->json(['ok' => false, 'error' => 'Option does not belong to this poll.'], 422);
        }
        if (! $poll->is_active) {
            return response()->json(['ok' => false, 'error' => 'This poll is closed.'], 422);
        }

        // Hash the IP — never store raw
        $ipHash = hash('sha256', $request->ip() . config('app.key'));
        $uaHash = hash('sha256', (string) $request->userAgent() . config('app.key'));

        // Has this IP voted on THIS poll in last 24h?
        $alreadyVoted = PeacePollResponse::where('poll_id', $poll->id)
            ->where('ip_hash', $ipHash)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if (! $alreadyVoted) {
            DB::transaction(function () use ($poll, $option, $ipHash, $uaHash) {
                PeacePollResponse::create([
                    'poll_id'    => $poll->id,
                    'option_id'  => $option->id,
                    'ip_hash'    => $ipHash,
                    'ua_hash'    => $uaHash,
                    'created_at' => now(),
                ]);
                $option->increment('response_count');
            });
        }

        // Always return the canonical answer + scripture explainer + result distribution
        $poll->refresh()->load('options');
        $total = max(1, $poll->options->sum('response_count'));
        $results = $poll->options->map(fn($o) => [
            'id'           => $o->id,
            'text'         => $o->option_text,
            'count'        => $o->response_count,
            'percent'      => round(($o->response_count / $total) * 100),
            'is_canonical' => $o->is_canonical,
        ])->values();

        return response()->json([
            'ok'                  => true,
            'already_voted'       => $alreadyVoted,
            'scripture_ref'       => $poll->scripture_ref,
            'scripture_explainer' => $poll->scripture_explainer,
            'canonical_option_id' => optional($poll->options->firstWhere('is_canonical', true))->id,
            'results'             => $results,
            'total'               => $poll->options->sum('response_count'),
        ]);
    }
}
