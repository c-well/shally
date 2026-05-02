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
}
