<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One captured prior state of a content record (see HasRevisions).
 * snapshot = the values of the changed fields as they were BEFORE the edit
 * that created this row. Restoring writes them back (and, because that save is
 * itself an Eloquent update, a fresh revision of the current state is captured
 * first — so a restore is undoable, same philosophy as system checkpoints).
 */
class Revision extends Model
{
    protected $fillable = [
        'revisionable_type', 'revisionable_id', 'user_id', 'label', 'summary', 'snapshot',
    ];

    protected $casts = ['snapshot' => 'array'];

    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
