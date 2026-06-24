<?php
namespace App\Concerns;

use App\Models\Revision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Drop-in version history for an editable model.
 *
 * A model `use`s this trait and declares which fields to track via
 * revisionFields(). On every Eloquent update, if any tracked field actually
 * changed value, we record the PRIOR values as a Revision. Restoring a
 * revision writes those values back; that write is itself an update, so it
 * captures a revision of the current state first — making the restore undoable.
 *
 * Capture lives at the model layer (the `updating` event) on purpose: it fires
 * no matter which controller saves — the live bulletin builder, the pages
 * form, the lessons form all flow through Eloquent, so none of them need to
 * know revisions exist.
 */
trait HasRevisions
{
    public static function bootHasRevisions(): void
    {
        static::updating(function (Model $model) {
            $tracked = method_exists($model, 'revisionFields')
                ? $model->revisionFields()
                : array_keys($model->getAttributes());

            // Only fields that are tracked AND whose value really changed.
            $changed = [];
            foreach ($tracked as $field) {
                if (! array_key_exists($field, $model->getDirty())) continue;
                if ($model->getOriginal($field) === $model->getAttribute($field)) continue;
                $changed[] = $field;
            }
            if (empty($changed)) return;

            $before = [];
            foreach ($changed as $field) {
                $before[$field] = $model->getOriginal($field);
            }

            Revision::create([
                'revisionable_type' => $model->getMorphClass(),
                'revisionable_id'   => $model->getKey(),
                'user_id'           => auth()->id(),
                'label'             => $model->revisionLabel(),
                'summary'           => 'Edited ' . implode(', ', array_map(
                    fn ($f) => str_replace('_', ' ', $f), $changed
                )),
                'snapshot'          => $before,
            ]);
        });
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable')->latest('id');
    }

    /** Human name of this record for the history list. Override per model. */
    public function revisionLabel(): string
    {
        return class_basename($this) . ' #' . $this->getKey();
    }

    /** Write a prior version's values back onto this record. */
    public function restoreRevision(Revision $rev): array
    {
        $tracked = method_exists($this, 'revisionFields')
            ? $this->revisionFields()
            : array_keys((array) $rev->snapshot);

        $apply = array_intersect_key((array) $rev->snapshot, array_flip($tracked));
        if (empty($apply)) return [];

        $this->forceFill($apply)->save();   // triggers a fresh revision of the now-replaced state
        return array_keys($apply);
    }
}
