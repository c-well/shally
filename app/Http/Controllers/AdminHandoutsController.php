<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Handout;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * /admin/handouts — the clerk's side.
 *
 * The wizard IS the product here. Whoever is making a handout is standing in
 * the foyer on a phone with someone waiting, so the flow is: pick a shape,
 * answer three or four questions, choose how long it lives, get a link and a
 * QR. Everything else (theme, image, editing copy) is available afterwards and
 * out of the way until then.
 */
class AdminHandoutsController extends Controller
{
    public function index(Request $request)
    {
        $handouts = Handout::withTrashed()
            ->with('creator')
            ->orderByRaw('deleted_at IS NOT NULL')   // destroyed sink to the bottom
            ->orderByDesc('created_at')
            ->get();

        return view('admin.handouts', [
            'handouts' => $handouts,
            'live'     => $handouts->filter(fn ($h) => $h->isLive()),
            'dueNudge' => $handouts->filter(fn ($h) => $h->isNudgeDue()),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $meta = Handout::TEMPLATES[$data['template']];

        $handout = Handout::create([
            'token'      => Handout::mintToken(),
            'template'   => $data['template'],
            'title'      => $data['title'],
            'eyebrow'    => $data['eyebrow'] ?: $meta['eyebrow'],
            'body'       => $data['body'] ?? null,
            'link_url'   => $data['link_url'] ?? null,
            'link_label' => $data['link_label'] ?: $meta['label'],
            'theme'      => $data['theme'] ?? $meta['theme'],
            'happens_at' => $data['happens_at'] ?? null,
            'location'   => $data['location'] ?? null,
            'mode'       => $data['mode'],
            // A date is only meaningful on the expiring mode; storing one on an
            // 'open' handout would leave a live grenade if the mode ever flips.
            'expires_at' => $data['mode'] === 'expires' ? $data['expires_at'] : null,
            'nudge_every_days' => $data['nudge_every_days'] ?? 30,
            'created_by' => $request->user()->id,
        ]);

        if ($request->hasFile('image')) {
            $this->storeImage($request, $handout);
        }

        AuditLog::record(
            event: 'handout_created',
            userId: $request->user()->id,
            description: $request->user()->name . ' made a handout: "' . $handout->title . '" (' . $handout->lifespanLabel() . ')'
        );

        return redirect()->route('admin.handouts')->with('minted', $handout->token);
    }

    public function update(Request $request, Handout $handout)
    {
        $data = $this->validated($request);

        $handout->fill([
            'title'      => $data['title'],
            'eyebrow'    => $data['eyebrow'] ?: $handout->eyebrow,
            'body'       => $data['body'] ?? null,
            'link_url'   => $data['link_url'] ?? null,
            'link_label' => $data['link_label'] ?: $handout->link_label,
            'theme'      => $data['theme'] ?? $handout->theme,
            'happens_at' => $data['happens_at'] ?? null,
            'location'   => $data['location'] ?? null,
            'mode'       => $data['mode'],
            'expires_at' => $data['mode'] === 'expires' ? $data['expires_at'] : null,
            'nudge_every_days' => $data['nudge_every_days'] ?? $handout->nudge_every_days,
        ])->save();

        if ($request->hasFile('image')) {
            $this->storeImage($request, $handout);
        }

        AuditLog::record(
            event: 'handout_updated',
            userId: $request->user()->id,
            description: $request->user()->name . ' edited the handout "' . $handout->title . '"'
        );

        return back()->with('status', 'Saved.');
    }

    /**
     * Destruct. Soft-delete, so the view counts and the audit trail survive —
     * the LINK is what dies, and it dies immediately: HandoutController treats
     * trashed and missing identically.
     */
    public function destroy(Request $request, Handout $handout)
    {
        $handout->delete();

        AuditLog::record(
            event: 'handout_destroyed',
            userId: $request->user()->id,
            description: $request->user()->name . ' destroyed the handout "' . $handout->title . '" after ' . $handout->ageInDays() . ' days and ' . $handout->uniques . ' people'
        );

        return back()->with('status', 'Destroyed. The link is dead.');
    }

    public function restore(Request $request, int $id)
    {
        $handout = Handout::withTrashed()->findOrFail($id);
        $handout->restore();

        AuditLog::record(
            event: 'handout_restored',
            userId: $request->user()->id,
            description: $request->user()->name . ' brought back the handout "' . $handout->title . '"'
        );

        return back()->with('status', 'Back up — same link as before.');
    }

    /**
     * The answer to a nudge. "Keep" resets the heartbeat so it asks again in
     * another cycle; it never converts the handout into something permanent.
     */
    public function keep(Request $request, Handout $handout)
    {
        $days = (int) $request->input('days', $handout->nudge_every_days);
        $days = max(7, min(365, $days));

        if ($handout->mode === 'expires' && $handout->expires_at) {
            $handout->expires_at = now()->addDays($days);
        }
        $handout->nudged_at = now();
        $handout->nudge_every_days = $days;
        $handout->save();

        AuditLog::record(
            event: 'handout_kept',
            userId: $request->user()->id,
            description: $request->user()->name . ' kept "' . $handout->title . '" for another ' . $days . ' days'
        );

        return back()->with('status', "Kept — asking again in {$days} days.");
    }

    /** A QR big enough to print on a bulletin insert without going fuzzy. */
    public function qr(Handout $handout): Response
    {
        $png = Builder::create()
            ->writer(new PngWriter())
            ->data($handout->url())
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(900)
            ->margin(24)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        return response($png->getString(), 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="handout-' . $handout->token . '.png"',
            'Cache-Control'       => 'private, max-age=3600',
        ]);
    }

    private function storeImage(Request $request, Handout $handout): void
    {
        $path = $request->file('image')->store('handouts', 'public');
        if ($handout->image_path) {
            Storage::disk('public')->delete($handout->image_path);
        }
        $handout->update(['image_path' => $path]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'template'   => ['required', 'string', 'in:' . implode(',', array_keys(Handout::TEMPLATES))],
            'title'      => ['required', 'string', 'max:160'],
            'eyebrow'    => ['nullable', 'string', 'max:80'],
            'body'       => ['nullable', 'string', 'max:4000'],
            'link_url'   => ['nullable', 'url', 'max:500'],
            'link_label' => ['nullable', 'string', 'max:60'],
            'theme'      => ['nullable', 'string', 'in:' . implode(',', array_keys(Handout::THEMES))],
            'happens_at' => ['nullable', 'date'],
            'location'   => ['nullable', 'string', 'max:160'],
            'mode'       => ['required', 'in:expires,open'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'nudge_every_days' => ['nullable', 'integer', 'min:7', 'max:365'],
            'image'      => ['nullable', 'image', 'max:6144'],
        ]);

        // The one rule, enforced at the door: an expiring handout without a
        // date would be permanent by accident, which is the exact failure this
        // whole feature exists to prevent.
        if ($data['mode'] === 'expires' && empty($data['expires_at'])) {
            $data['expires_at'] = now()->addDays(Handout::TEMPLATES[$data['template']]['days']);
        }

        $data['eyebrow']    ??= null;
        $data['link_label'] ??= null;

        return $data;
    }
}
