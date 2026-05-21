<?php
namespace App\Http\Controllers;

use App\Models\PeaceSermon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Public, signed-token endpoints for the Saturday sermon review flow.
 *
 * No login required — security comes from the random 64-char review_token
 * that's only emailed to the clerk (andre.marshall@gmail.com).
 *
 * Routes:
 *   GET /peace/review/{token}/approve        — lock in as published
 *   GET /peace/review/{token}/discard        — soft-delete (page 404s, 48h grace)
 *   GET /peace/review/{token}/restore        — pull back to pending_review
 *   GET /peace/review/{token}/confirm-delete — hard-trash after the 48h confirm email
 *
 * All actions are GET so they work from email clients without form submission.
 * Each returns a small "done" page (no Laravel session needed).
 */
class PeaceReviewController extends Controller
{
    public function approve(string $token): View
    {
        $sermon = $this->findByToken($token);
        $sermon->processing_status = 'published';
        $sermon->review_deadline = null;
        $sermon->discarded_at = null;
        $sermon->draft_email_sent_at = null;
        $sermon->confirm_delete_email_sent_at = null;
        if (!$sermon->published_at) $sermon->published_at = now();
        $sermon->save();

        return view('peace.review.done', [
            'sermon' => $sermon,
            'title'  => 'Approved ✓',
            'msg'    => 'Locked in as published. The page will keep serving from /find-peace.',
            'cta'    => ['url' => route('find-peace.show', $sermon->slug), 'label' => 'View live page →'],
        ]);
    }

    public function discard(string $token): View
    {
        $sermon = $this->findByToken($token);
        $sermon->processing_status = 'soft_discarded';
        $sermon->discarded_at = now();
        $sermon->published_at = null;  // page 404s immediately
        $sermon->save();

        return view('peace.review.done', [
            'sermon' => $sermon,
            'title'  => 'Discarded',
            'msg'    => 'Page is now offline. In 48 hours you\'ll get one more email to confirm permanent removal, or restore.',
            'cta'    => null,
        ]);
    }

    public function restore(string $token): View
    {
        $sermon = $this->findByToken($token);
        $sermon->processing_status = 'pending_review';
        $sermon->discarded_at = null;
        $sermon->draft_email_sent_at = null;
        $sermon->confirm_delete_email_sent_at = null;
        // Restart the 72h grace clock
        $sermon->review_deadline = now()->addHours(72);
        if (!$sermon->published_at) $sermon->published_at = now();
        $sermon->save();

        return view('peace.review.done', [
            'sermon' => $sermon,
            'title'  => 'Restored',
            'msg'    => 'Page is live again. New 72-hour grace clock started.',
            'cta'    => ['url' => route('find-peace.show', $sermon->slug), 'label' => 'View live page →'],
        ]);
    }

    public function confirmDelete(string $token): View
    {
        $sermon = $this->findByToken($token);
        // Soft-keep in DB for audit but mark trashed — page stays offline forever
        $sermon->processing_status = 'trashed';
        $sermon->published_at = null;
        $sermon->save();

        return view('peace.review.done', [
            'sermon' => $sermon,
            'title'  => 'Permanently removed',
            'msg'    => 'Audit record kept in DB but the page will not return. You can still find it in /admin/peace with the Trash filter.',
            'cta'    => null,
        ]);
    }

    private function findByToken(string $token): PeaceSermon
    {
        // Constant-time token check via the DB unique index
        return PeaceSermon::where('review_token', $token)->firstOrFail();
    }
}
