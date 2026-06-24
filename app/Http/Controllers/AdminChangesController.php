<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Revision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * /admin/changes — the content edit history + per-record undo.
 *
 * This is the granular companion to the system-checkpoint rollback in
 * AdminChangelogController. Where a checkpoint restore is the heavy "undo one
 * major structural change" (full DB + composer), this surface undoes a SINGLE
 * content edit — one bulletin tweak, one page paragraph, one lesson theme —
 * without touching anything else. The "50 quick text edits, one at a time" tool.
 *
 * Revisions are captured automatically at the model layer (App\Concerns\HasRevisions).
 */
class AdminChangesController extends Controller
{
    public function index(Request $request): View
    {
        $q = Revision::with('user')->latest('id');

        // Optional filter to one record (used by the inline "see full history" link).
        if ($request->filled('type') && $request->filled('id')) {
            $q->where('revisionable_type', (string) $request->input('type'))
              ->where('revisionable_id', (int) $request->input('id'));
        }

        return view('admin.changes', [
            'revisions' => $q->paginate(40)->withQueryString(),
            'filtered'  => $request->filled('type') && $request->filled('id'),
        ]);
    }

    /** POST /admin/changes/{revision}/restore — revert one record to a prior version. */
    public function restore(Request $request, Revision $revision): JsonResponse
    {
        $cls = $revision->revisionable_type;
        if (! class_exists($cls)) {
            return response()->json(['ok' => false, 'message' => 'Unknown record type.'], 422);
        }

        // Resolve the record, including soft-deleted ones (a deleted bulletin
        // line can't be sensibly text-reverted, but we want a clear message).
        $model = method_exists($cls, 'withTrashed')
            ? $cls::withTrashed()->find($revision->revisionable_id)
            : $cls::find($revision->revisionable_id);

        if (! $model) {
            return response()->json(['ok' => false, 'message' => 'That record no longer exists — nothing to restore.'], 422);
        }
        if (method_exists($model, 'trashed') && $model->trashed()) {
            return response()->json(['ok' => false, 'message' => 'That record is in the trash. Restore it first, then revert its text.'], 422);
        }
        if (! method_exists($model, 'restoreRevision')) {
            return response()->json(['ok' => false, 'message' => 'This record type does not keep history.'], 422);
        }

        $applied = $model->restoreRevision($revision);
        if (empty($applied)) {
            return response()->json(['ok' => false, 'message' => 'Nothing to restore from that version.'], 422);
        }

        AuditLog::record(
            event: 'content_reverted',
            userId: $request->user()?->id,
            description: ($request->user()?->name ?? 'Someone') . ' reverted ' . $revision->label
                . ' to a version from ' . $revision->created_at->diffForHumans()
                . ' (' . implode(', ', $applied) . ')',
        );

        return response()->json([
            'ok'      => true,
            'message' => 'Restored ' . $revision->label . '. (This restore is itself undoable.)',
        ]);
    }
}
