<?php
namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\PrayerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMessagesController extends Controller
{
    /** GET /admin/messages — combined inbox of prayer requests + contact messages */
    public function index(): View
    {
        $prayers  = PrayerRequest::orderByDesc('created_at')->limit(100)->get();
        $contacts = ContactMessage::orderByDesc('created_at')->limit(100)->get();

        return view('admin.messages', [
            'prayers'         => $prayers,
            'contacts'        => $contacts,
            'unreadPrayers'   => $prayers->whereNull('read_at')->count(),
            'unreadContacts'  => $contacts->whereNull('read_at')->count(),
        ]);
    }

    /** POST /admin/messages/prayer/{id}/read */
    public function markPrayerRead(int $id): RedirectResponse
    {
        $p = PrayerRequest::findOrFail($id);
        $p->read_at = now();
        $p->save();
        return back();
    }

    /** POST /admin/messages/contact/{id}/read */
    public function markContactRead(int $id): RedirectResponse
    {
        $c = ContactMessage::findOrFail($id);
        $c->read_at = now();
        $c->save();
        return back();
    }

    /** POST /admin/messages/prayer/{id}/delete — soft delete (recoverable 30 days) */
    public function deletePrayer(int $id): RedirectResponse
    {
        PrayerRequest::findOrFail($id)->delete();
        return back()->with('status', 'Prayer request moved to trash (recoverable for 30 days).');
    }

    /** POST /admin/messages/contact/{id}/delete — soft delete (recoverable 30 days) */
    public function deleteContact(int $id): RedirectResponse
    {
        ContactMessage::findOrFail($id)->delete();
        return back()->with('status', 'Message moved to trash (recoverable for 30 days).');
    }

    /** POST /admin/messages/prayer/{id}/restore */
    public function restorePrayer(int $id): RedirectResponse
    {
        PrayerRequest::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('status', 'Prayer request restored.');
    }

    /** POST /admin/messages/contact/{id}/restore */
    public function restoreContact(int $id): RedirectResponse
    {
        ContactMessage::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('status', 'Message restored.');
    }
}
