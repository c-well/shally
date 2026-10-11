<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A saved snapshot of what a normal week looks like — or, for kind='undo',
 * what one bulletin looked like just before a restore.
 *
 * Append-only by design. Nothing here is ever updated in place, so the history
 * is the undo: the newest 'default' row is live, and the one before it is what
 * you go back to if the save was a mistake.
 */
class BulletinDefault extends Model
{
    protected $fillable = ['kind', 'bulletin_id', 'payload', 'note', 'saved_by'];
    protected $casts    = ['payload' => 'array'];

    public function savedBy() { return $this->belongsTo(User::class, 'saved_by'); }

    /** The active default, or null if nobody has saved one yet. */
    public static function active(): ?self
    {
        return static::where('kind', 'default')->orderByDesc('id')->first();
    }

    /** The default before the active one — what "undo the save" restores. */
    public static function previous(): ?self
    {
        return static::where('kind', 'default')->orderByDesc('id')->skip(1)->first();
    }

    /** Most recent pre-restore copy for one bulletin. */
    public static function undoFor(int $bulletinId): ?self
    {
        return static::where('kind', 'undo')->where('bulletin_id', $bulletinId)
            ->orderByDesc('id')->first();
    }

    public function lineCount(): int { return count($this->payload['lines'] ?? []); }
    public function annCount(): int  { return count($this->payload['announcements'] ?? []); }
}
