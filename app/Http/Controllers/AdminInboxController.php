<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FeedbackTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * /admin/inbox — read incoming feedback / bug reports from users.
 *
 * Submitted via the public /feedback form (or admin chrome). Every ticket
 * lands here with status='pending' until an admin closes it from this UI.
 *
 * No reply capability tonight — just read + close. A future iteration could
 * add an email-back-to-reporter flow, but right now the goal is "Karlon and
 * André can see what's been reported without SSHing in."
 */
class AdminInboxController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'open');

        $q = FeedbackTicket::query()->with('user')->latest();
        if ($filter === 'open')   $q->where('status', '!=', 'closed');
        if ($filter === 'closed') $q->where('status', 'closed');

        $tickets = $q->limit(200)->get();

        // Counts for filter pills
        $counts = [
            'open'   => FeedbackTicket::where('status', '!=', 'closed')->count(),
            'closed' => FeedbackTicket::where('status', 'closed')->count(),
            'all'    => FeedbackTicket::count(),
        ];

        return view('admin.inbox', compact('tickets', 'filter', 'counts'));
    }

    public function close(Request $request, FeedbackTicket $ticket): RedirectResponse
    {
        $note = trim((string) $request->input('note', ''));

        $ticket->update([
            'status'      => 'closed',
            'closed_at'   => now(),
            'closed_by'   => $request->user()->id,
            'closed_note' => $note ?: 'Closed via /admin/inbox.',
        ]);

        AuditLog::record(
            event: 'feedback_closed',
            userId: $request->user()->id,
            description: $request->user()->name . " closed feedback ticket #{$ticket->id}",
        );

        return back()->with('status', "Closed ticket #{$ticket->id}.");
    }
}
