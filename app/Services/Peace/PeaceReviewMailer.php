<?php
namespace App\Services\Peace;

use App\Models\PeaceSermon;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the three review-flow emails:
 *   - newReview()    : fired right after cron publishes a draft sermon
 *   - pulledOffline(): fired when peace:review-tick drops it to draft after 72h silence
 *   - confirmDelete(): fired 48h after user clicked Discard
 *
 * All three go to andre.marshall@gmail.com (super_admin) + CC contact@c-wellpics.com.
 */
class PeaceReviewMailer
{
    private const TO_ADDR = 'andre.marshall@gmail.com';
    private const CC_ADDR = 'contact@c-wellpics.com';

    public function newReview(PeaceSermon $sermon): void
    {
        $this->send($sermon, 'new', 'New sermon ready for review: ' . $sermon->title);
    }

    public function pulledOffline(PeaceSermon $sermon): void
    {
        $this->send($sermon, 'pulled', 'Sermon pulled offline — still want it? (' . $sermon->title . ')');
    }

    public function confirmDelete(PeaceSermon $sermon): void
    {
        $this->send($sermon, 'confirm-delete', 'About to permanently remove: ' . $sermon->title);
    }

    private function send(PeaceSermon $sermon, string $template, string $subject): void
    {
        $approveUrl       = route('peace.review.approve',        $sermon->review_token);
        $discardUrl       = route('peace.review.discard',        $sermon->review_token);
        $restoreUrl       = route('peace.review.restore',        $sermon->review_token);
        $confirmDeleteUrl = route('peace.review.confirm-delete', $sermon->review_token);
        $publicUrl        = route('find-peace.show',             $sermon->slug);
        $editUrl          = route('admin.peace.edit',            $sermon->slug);

        Mail::send("peace.emails.review-{$template}", [
            'sermon'           => $sermon,
            'approveUrl'       => $approveUrl,
            'discardUrl'       => $discardUrl,
            'restoreUrl'       => $restoreUrl,
            'confirmDeleteUrl' => $confirmDeleteUrl,
            'publicUrl'        => $publicUrl,
            'editUrl'          => $editUrl,
        ], function ($m) use ($subject) {
            $m->to(self::TO_ADDR)
              ->cc(self::CC_ADDR)
              ->subject($subject);
        });
    }
}
