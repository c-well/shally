<?php
namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Bulletin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Bulletin editor v2 — a frictionless, drill-through editor for the order of
 * service. Pure front door onto the existing BulletinController AJAX endpoints
 * (lines, announcements, meta, publish) — no new write paths, so v1 (the inline
 * editor on the home page) keeps working untouched. Which editor an admin lands
 * on is a preference toggle ("bulletin_editor": v1|v2), flippable any time.
 */
class AdminBulletinController extends Controller
{
    public function index(Request $request): View
    {
        $bulletin = $request->filled('b')
            ? Bulletin::find($request->input('b'))
            : (Bulletin::activeForNow() ?? Bulletin::orderByDesc('service_date')->first());

        $bulletin?->load([
            'lines'         => fn ($q) => $q->orderBy('sort_order'),
            'announcements' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $bulletins = Bulletin::orderByDesc('service_date')->limit(24)
            ->get(['id', 'title', 'service_date', 'service_time']);

        // Last-used-wins: opening v2 makes it this admin's default Bulletin link.
        AppSetting::set('bulletin_editor', 'v2');

        return view('admin.bulletin', [
            'bulletin'  => $bulletin,
            'bulletins' => $bulletins,
        ]);
    }

    /** Set which editor the "Bulletin" menu link takes an admin to. */
    public function prefer(Request $request): RedirectResponse
    {
        $v = $request->input('version') === 'v2' ? 'v2' : 'v1';
        AppSetting::set('bulletin_editor', $v);
        return $v === 'v2'
            ? redirect()->route('admin.bulletin')
            : redirect()->to('/welcome');
    }
}
