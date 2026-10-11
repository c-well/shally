<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Bulletin;
use App\Models\BulletinDefault;
use App\Services\BulletinDefaults;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * "Our normal week" — saving it, putting it back, and undoing either.
 *
 * Every route here sits behind role:clerk, which EnsureRole already widens to
 * include super_admin. That covers André and Karlon as super admins, Rosharde
 * as clerk, and anyone given the clerk role later — which is exactly who
 * Karlon said should hold this (2026-10-11).
 */
class AdminBulletinDefaultController extends Controller
{
    public function __construct(private BulletinDefaults $defaults) {}

    /** GET — what the default currently holds, for the panel and the save dialog. */
    public function show(Bulletin $bulletin): JsonResponse
    {
        $active = BulletinDefault::active();

        return response()->json([
            'has_default' => (bool) $active,
            'saved_at'    => $active?->created_at?->toIso8601String(),
            'saved_from'  => $active?->payload['meta']['saved_from'] ?? null,
            'saved_by'    => $active?->savedBy?->name,
            'lines'       => $active?->lineCount() ?? count($this->defaults->seedPayload()['lines']),
            'anns'        => $active?->annCount() ?? 0,
            'can_rollback'=> (bool) BulletinDefault::previous(),
            'can_undo'    => (bool) BulletinDefault::undoFor($bulletin->id),
            // For the tick list in the save dialog.
            'current_announcements' => $bulletin->announcements()->orderBy('sort_order')
                ->get(['id', 'title'])
                ->map(fn ($a) => ['id' => $a->id, 'title' => $a->title ?: '(untitled)'])->all(),
        ]);
    }

    /** POST — make this bulletin the new normal week. */
    public function save(Request $request, Bulletin $bulletin): JsonResponse
    {
        $data = $request->validate([
            'announcement_ids'   => ['present', 'array'],
            'announcement_ids.*' => ['integer'],
            'note'               => ['nullable', 'string', 'max:160'],
        ]);

        // Only ids that really belong to this bulletin — never trust the list.
        $ids = $bulletin->announcements()->whereIn('id', $data['announcement_ids'])->pluck('id')->all();

        $saved = $this->defaults->saveDefault($bulletin, $ids, $request->user()->id, $data['note'] ?? null);

        AuditLog::record(
            event: 'bulletin_default_saved',
            userId: $request->user()->id,
            description: 'Normal week saved from ' . (optional($bulletin->service_date)->toDateString() ?: "#{$bulletin->id}")
                . " — {$saved->lineCount()} lines, {$saved->annCount()} announcements",
        );

        return response()->json([
            'ok' => true, 'lines' => $saved->lineCount(), 'anns' => $saved->annCount(),
        ]);
    }

    /** POST — put the normal week back onto this bulletin. */
    public function restore(Request $request, Bulletin $bulletin): JsonResponse
    {
        $data = $request->validate([
            'scope' => ['required', 'in:' . BulletinDefaults::SCOPE_ORDER . ',' . BulletinDefaults::SCOPE_FULL],
        ]);

        $payload = $this->defaults->activePayload();
        $this->defaults->restore($bulletin, $payload, $data['scope'], $request->user()->id);

        AuditLog::record(
            event: 'bulletin_default_restored',
            userId: $request->user()->id,
            description: "Bulletin #{$bulletin->id} reset to the normal week ({$data['scope']})",
        );

        return response()->json(['ok' => true, 'can_undo' => true]);
    }

    /** POST — undo the last restore on this bulletin. */
    public function undo(Request $request, Bulletin $bulletin): JsonResponse
    {
        $undo = BulletinDefault::undoFor($bulletin->id);
        if (! $undo) {
            return response()->json(['error' => 'nothing to undo'], 404);
        }

        $this->defaults->applyUndo($bulletin, $undo);

        AuditLog::record(
            event: 'bulletin_default_restore_undone',
            userId: $request->user()->id,
            description: "Bulletin #{$bulletin->id} restore undone",
        );

        return response()->json(['ok' => true]);
    }

    /**
     * POST — roll the DEFAULT ITSELF back one version. For when the week that
     * got saved as normal turns out not to have been normal.
     */
    public function rollback(Request $request): JsonResponse
    {
        $prev = BulletinDefault::previous();
        if (! $prev) {
            return response()->json(['error' => 'no earlier default'], 404);
        }

        // Append rather than delete, so the history stays honest and this is
        // itself undoable.
        $restored = BulletinDefault::create([
            'kind'     => 'default',
            'payload'  => $prev->payload,
            'note'     => 'rolled back to the default saved ' . $prev->created_at->toDateString(),
            'saved_by' => $request->user()->id,
        ]);

        AuditLog::record(
            event: 'bulletin_default_rolled_back',
            userId: $request->user()->id,
            description: 'Normal week rolled back to the version saved ' . $prev->created_at->toDateString(),
        );

        return response()->json(['ok' => true, 'lines' => $restored->lineCount(), 'anns' => $restored->annCount()]);
    }
}
