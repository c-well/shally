<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Proposals — a private staging surface where Karlon and admins can preview
 * proposed changes before shipping. Each proposal is a single Blade view + a
 * vote endpoint that audit-logs the response.
 *
 * The first proposal: bulletin-week-mode (hide order of service Mon-Fri).
 * Future proposals can live here too — keeps the work-in-flight discoverable.
 */
class ProposalsController extends Controller
{
    public function bulletinWeekMode(): View
    {
        return view('proposals.bulletin-week-mode');
    }

    public function voteBulletinWeekMode(Request $request): JsonResponse
    {
        $vote = $request->input('vote');
        if (! in_array($vote, ['yes', 'no', 'discuss'], true)) {
            return response()->json(['ok' => false, 'error' => 'Invalid vote'], 422);
        }

        AuditLog::record(
            event: 'proposal_vote',
            userId: $request->user()?->id,
            description: ($request->user()?->name ?? 'guest')
                . " voted {$vote} on proposal: bulletin-week-mode",
            meta: [
                'proposal' => 'bulletin-week-mode',
                'vote'     => $vote,
            ],
        );

        return response()->json(['ok' => true, 'vote' => $vote]);
    }
}
