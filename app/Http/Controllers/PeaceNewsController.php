<?php

namespace App\Http\Controllers;

use App\Models\PeaceNewsAnswer;
use App\Models\PeaceNewsIssue;
use App\Models\PeaceSubscriber;
use App\Services\Peace\ResendMailer;
use App\Services\Peace\SeekerAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Public side of Peace Notes News: the issues, "What spoke to you?" answers,
 * the approve-from-email page, and unsubscribe. No pop-ups anywhere: every
 * ask sits in the page.
 */
class PeaceNewsController extends Controller
{
    public function index(): View
    {
        $issues = PeaceNewsIssue::with('sermon')->where('status', 'sent')->orderByDesc('send_after')->paginate(12);
        return view('peace-news.index', compact('issues'));
    }

    public function show(Request $req, string $slug): View
    {
        $issue = PeaceNewsIssue::with('sermon.scriptures')->where('slug', $slug)->firstOrFail();
        // Drafts are visible to signed-in staff only, as a preview.
        if (! $issue->isPublic() && ! $req->user()) abort(404);
        return view('peace-news.show', [
            'issue'   => $issue,
            'sermon'  => $issue->sermon,
            'youSaid' => $issue->youSaid(),
            'seeker'  => $req->attributes->get('seeker'),
            'preview' => ! $issue->isPublic(),
        ]);
    }

    public function answer(Request $req, string $slug, SeekerAuth $auth, ResendMailer $mailer): RedirectResponse
    {
        $issue = PeaceNewsIssue::where('slug', $slug)->where('status', 'sent')->firstOrFail();
        $back = route('peace.news.show', $issue->slug) . '#spoke';

        // Bots: honeypot and a form filled in under 3 seconds get a quiet "thanks".
        $opened = (int) $req->input('form_opened_at', 0);
        if (filled($req->input('website')) || ($opened > 0 && time() * 1000 - $opened < 3000)) {
            return redirect($back)->with('news_flash', 'Thank you. Your answer is in.');
        }

        $seeker = $req->attributes->get('seeker');
        $data = $req->validate([
            'body'       => 'required|string|min:3|max:2000',
            'first_name' => 'nullable|string|max:40',
            'email'      => ($seeker ? 'nullable' : 'required') . '|email|max:255',
            'share_ok'   => 'nullable|boolean',
        ]);

        $ipKey = 'news-answer:' . $req->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            return redirect($back)->with('news_flash', 'You have sent several answers this hour. Please try again a little later.');
        }
        RateLimiter::hit($ipKey, 3600);

        $answer = PeaceNewsAnswer::create([
            'issue_id'      => $issue->id,
            'subscriber_id' => $seeker?->id,
            'first_name'    => trim((string) ($data['first_name'] ?? '')) ?: null,
            'body'          => trim($data['body']),
            'share_ok'      => $req->boolean('share_ok'),
            'status'        => $seeker ? 'pending' : 'unverified',
            'ip_hash'       => hash('sha256', $req->ip() . config('app.key')),
        ]);

        if ($seeker) {
            return redirect($back)->with('news_flash', 'Thank you. Your answer will show after a quick read by the church.');
        }

        // Not signed in: one email link confirms the address and posts the answer.
        $email = mb_strtolower(trim($data['email']));
        $emailKey = 'peace-email:' . sha1($email);
        if (! RateLimiter::tooManyAttempts($emailKey, 3)) {
            RateLimiter::hit($emailKey, 3600);
            $raw = $auth->issueLink($email, ['type' => 'news_answer', 'answer_id' => $answer->id], $back, $req);
            $mailer->sendView(
                to: $email,
                subject: 'Confirm your answer on Peace Notes News',
                view: 'peace.emails.seeker.magic-link',
                viewData: ['email' => $email, 'url' => url('/peace/auth/verify/' . $raw), 'heading' => 'Tap to post your answer.'],
            );
        }
        return redirect($back)->with('news_flash', 'Almost there. We emailed you a link: tap it to post your answer. No password.');
    }

    /* ── Approve from the review email. A page with buttons, never a bare
          link, so mail scanners that open links cannot approve by accident. ── */

    public function review(string $token): View
    {
        $issue = PeaceNewsIssue::with('sermon')->where('review_token', $token)->firstOrFail();
        return view('peace-news.review', ['issue' => $issue, 'sermon' => $issue->sermon, 'token' => $token]);
    }

    public function reviewAct(Request $req, string $token): RedirectResponse
    {
        $issue = PeaceNewsIssue::with('sermon')->where('review_token', $token)->firstOrFail();
        $action = $req->input('action');
        $by = trim((string) $req->input('by', '')) ?: 'review email';

        if ($action === 'approve' && in_array($issue->status, ['draft', 'held'], true)) {
            $issue->forceFill(['status' => 'approved', 'approved_by' => mb_substr($by, 0, 120), 'approved_at' => now()])->save();
            \App\Models\AuditLog::record(event: 'peace_news_approved', description: "Peace Notes News approved by {$by}: {$issue->sermon->title}");
            \Illuminate\Support\Facades\Mail::raw(
                "Peace Notes News \"{$issue->sermon->title}\" was approved by {$by}.\n\nIt goes out " .
                ($issue->send_after->isPast() ? 'within the next few minutes.' : $issue->send_after->timezone('America/New_York')->format('l, M j \a\t g:i A') . '.') .
                "\n\nPreview: " . route('peace.news.show', $issue->slug) . "\n",
                fn ($m) => $m->to(\App\Console\Commands\PeaceNewsDraftCommand::REVIEW_TO)->cc(\App\Console\Commands\PeaceNewsDraftCommand::REVIEW_CC)->subject('Approved: Peace Notes News')
            );
        } elseif ($action === 'hold' && in_array($issue->status, ['draft', 'approved'], true)) {
            $issue->forceFill(['status' => 'held'])->save();
            \App\Models\AuditLog::record(event: 'peace_news_held', description: "Peace Notes News held by {$by}: {$issue->sermon->title}");
        }
        return redirect()->route('peace.news.review', $token);
    }

    /* ── Unsubscribe: signed link from the email, then one button. ── */

    public function unsubscribe(Request $req, PeaceSubscriber $subscriber): View
    {
        return view('peace-news.unsubscribe', ['subscriber' => $subscriber, 'done' => false, 'action' => $req->fullUrl()]);
    }

    public function unsubscribeConfirm(Request $req, PeaceSubscriber $subscriber): View
    {
        $subscriber->forceFill(['subscribed_newsletter' => false])->save();
        \App\Models\AuditLog::record(event: 'peace_news_unsubscribed', description: 'Unsubscribed from Peace Notes News: ' . $subscriber->email);
        return view('peace-news.unsubscribe', ['subscriber' => $subscriber, 'done' => true, 'action' => null]);
    }
}
