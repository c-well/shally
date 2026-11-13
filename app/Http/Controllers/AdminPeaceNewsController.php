<?php

namespace App\Http\Controllers;

use App\Console\Commands\PeaceNewsDraftCommand;
use App\Models\PeaceNewsAnswer;
use App\Models\PeaceNewsIssue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin for Peace Notes News: every issue, edit before it goes out, approve or
 * hold, and approve readers' answers. First approval sends.
 */
class AdminPeaceNewsController extends Controller
{
    public function index(): View
    {
        return view('admin.peace-news.index', [
            'issues'  => PeaceNewsIssue::with('sermon')->withCount(['answers as approved_count' => fn ($q) => $q->where('status', 'approved')])
                ->orderByDesc('send_after')->limit(30)->get(),
            'pending' => PeaceNewsAnswer::with('issue.sermon')->where('status', 'pending')->orderBy('created_at')->get(),
            'subscribers' => \App\Models\PeaceSubscriber::whereNotNull('email_verified_at')->where('subscribed_newsletter', true)->count(),
        ]);
    }

    public function edit(PeaceNewsIssue $issue): View
    {
        $issue->load('sermon');
        return view('admin.peace-news.edit', ['issue' => $issue, 'sermon' => $issue->sermon]);
    }

    public function update(Request $req, PeaceNewsIssue $issue): RedirectResponse
    {
        abort_if($issue->status === 'sent', 422, 'This issue has already gone out.');
        $d = $req->validate([
            'questions'       => 'required|array|min:1|max:3',
            'questions.*'     => 'nullable|string|max:300',
            'opening_summary' => 'nullable|string|max:600',
            'opening_quote'   => 'nullable|string|max:380',
            'closing_summary' => 'nullable|string|max:600',
            'closing_quote'   => 'nullable|string|max:380',
        ]);
        $d['questions'] = array_values(array_filter(array_map('trim', $d['questions'])));
        $issue->fill($d)->save();
        \App\Models\AuditLog::record(event: 'peace_news_edited', description: auth()->user()->name . ' edited Peace Notes News: ' . $issue->sermon->title);
        return back()->with('ok', 'Saved.');
    }

    public function status(Request $req, PeaceNewsIssue $issue): RedirectResponse
    {
        $action = $req->input('action');
        $by = auth()->user()->name;
        if ($action === 'approve' && in_array($issue->status, ['draft', 'held'], true)) {
            $issue->forceFill(['status' => 'approved', 'approved_by' => $by, 'approved_at' => now()])->save();
            \App\Models\AuditLog::record(event: 'peace_news_approved', description: "Peace Notes News approved by {$by}: {$issue->sermon->title}");
        } elseif ($action === 'hold' && in_array($issue->status, ['draft', 'approved'], true)) {
            $issue->forceFill(['status' => 'held'])->save();
            \App\Models\AuditLog::record(event: 'peace_news_held', description: "Peace Notes News held by {$by}: {$issue->sermon->title}");
        } elseif ($action === 'resend-review' && $issue->status !== 'sent') {
            PeaceNewsDraftCommand::sendReview($issue);
        }
        return back()->with('ok', 'Done.');
    }

    public function answer(Request $req, PeaceNewsAnswer $answer): RedirectResponse
    {
        $action = $req->input('action');
        if (in_array($action, ['approve', 'reject'], true)) {
            $answer->forceFill([
                'status'      => $action === 'approve' ? 'approved' : 'rejected',
                'reviewed_by' => auth()->user()->name,
                'reviewed_at' => now(),
            ])->save();
        }
        return back()->with('ok', $action === 'approve' ? 'Answer approved.' : 'Answer hidden.');
    }
}
