<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\ServiceTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * /admin/services — the home page's "Our service schedule" section.
 *
 * Two things live on one screen because they are one thing to the person
 * editing: the section's words (a Page row, slug `schedule-intro`, exactly the
 * pattern `slider-intro` already uses for a landing block with no route of its
 * own) and the cards themselves (ServiceTime rows). Splitting them across two
 * rooms would mean changing "Our service schedule." in one place and the times
 * in another.
 */
class AdminServicesController extends Controller
{
    public function index(): View
    {
        return view('admin.services', [
            'services' => ServiceTime::orderBy('sort_order')->orderBy('id')->get(),
            'intro'    => Page::firstOrCreate(
                ['slug' => 'schedule-intro'],
                ['title' => 'Our service schedule.', 'eyebrow' => 'Each week', 'body_md' => '']
            ),
            'living'   => AppSetting::get('living_schedule', '1') === '1',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) (ServiceTime::max('sort_order') + 1);

        $service = ServiceTime::create($data);

        AuditLog::record(
            event: 'service_created',
            userId: $request->user()->id,
            description: $request->user()->name . ' added the service "' . $service->name . '" (' . $service->when_label . ')'
        );

        return back()->with('status', 'Added — it is on the home page now.');
    }

    public function update(Request $request, ServiceTime $service): RedirectResponse
    {
        $service->update($this->validated($request));

        AuditLog::record(
            event: 'service_updated',
            userId: $request->user()->id,
            description: $request->user()->name . ' edited the service "' . $service->name . '"'
        );

        return back()->with('status', 'Saved.');
    }

    public function destroy(Request $request, ServiceTime $service): RedirectResponse
    {
        $service->delete();

        AuditLog::record(
            event: 'service_deleted',
            userId: $request->user()->id,
            description: $request->user()->name . ' removed the service "' . $service->name . '"'
        );

        return back()->with('status', 'Removed from the home page.');
    }

    public function restore(Request $request, int $serviceId): RedirectResponse
    {
        $service = ServiceTime::withTrashed()->findOrFail($serviceId);
        $service->restore();

        AuditLog::record(
            event: 'service_restored',
            userId: $request->user()->id,
            description: $request->user()->name . ' restored the service "' . $service->name . '"'
        );

        return back()->with('status', 'Back on the home page.');
    }

    /** Save the section's heading and blurb (the Page row). */
    public function updateIntro(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'title'   => ['required', 'string', 'max:120'],
            'body_md' => ['nullable', 'string', 'max:400'],
        ]);

        $page = Page::firstOrNew(['slug' => 'schedule-intro']);
        $page->fill($data)->save();

        AuditLog::record(
            event: 'page_edited',
            userId: $request->user()->id,
            description: $request->user()->name . ' edited the service-schedule heading'
        );

        return back()->with('status', 'Heading saved.');
    }

    /** Reorder the cards; the home page renders in this order. */
    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer', 'exists:service_times,id'],
        ])['order'];

        foreach ($ids as $i => $id) {
            ServiceTime::whereKey($id)->update(['sort_order' => $i]);
        }

        return back()->with('status', 'Order saved.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:80'],
            'when_label'  => ['required', 'string', 'max:60'],
            'where_label' => ['required', 'string', 'max:40'],
            'zoom_url'    => ['nullable', 'url', 'max:500'],
            'days'        => ['required', 'array', 'min:1'],
            'days.*'      => ['integer', 'between:0,6'],
            'live_from'   => ['nullable', 'date_format:H:i'],
            'live_until'  => ['nullable', 'date_format:H:i'],
            'is_published'=> ['nullable', 'boolean'],
        ]);

        // A half-set window would silently never fire. Treat it as "no window"
        // rather than storing something that looks configured but is not.
        if (empty($data['live_from']) || empty($data['live_until'])) {
            $data['live_from'] = $data['live_until'] = null;
        }

        $data['is_published'] = $request->boolean('is_published');
        $data['days'] = array_values(array_unique(array_map('intval', $data['days'])));

        return $data;
    }
}
