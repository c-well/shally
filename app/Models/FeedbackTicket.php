<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackTicket extends Model
{
    /**
     * Mass-assignable: only fields the user/system populates at creation time.
     * Status transitions (status, closed_*) flow through markClosed()/markReopened()
     * via direct property access — they're explicitly NOT mass-assignable.
     */
    protected $fillable = [
        'user_id', 'tag', 'message', 'claude_reply', 'attachments',
    ];

    /** @var list<string> */
    protected $guarded = ['id', 'status', 'closed_at', 'closed_note', 'closed_by'];
    protected $casts = [
        'closed_at' => 'datetime',
        'attachments' => 'array',
    ];

    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function closer(): BelongsTo    { return $this->belongsTo(User::class, 'closed_by'); }

    public function markClosed(User $closer, ?string $note = null): void
    {
        $this->status    = 'closed';
        $this->closed_at = now();
        $this->closed_by = $closer->id;
        $this->closed_note = $note;
        $this->save();
    }

    public function markReopened(): void
    {
        $this->status    = 'pending';
        $this->closed_at = null;
        $this->closed_by = null;
        $this->closed_note = null;
        $this->save();
    }

    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeClosed($q)   { return $q->where('status', 'closed'); }
}
