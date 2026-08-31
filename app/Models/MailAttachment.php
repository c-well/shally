<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A copy of a file that arrived by mail.
 *
 * The word to hold on to is *copy*. The original stays in the mailbox where
 * Dovecot put it, untouched, so whatever mail app Andre or the treasurer use
 * still shows the attachment exactly as before. Nothing here removes anything
 * from anybody's inbox.
 *
 * That also means our copy is disposable: if the cache fills and this one is
 * evicted, the file is not gone — it is re-read from the original message.
 */
class MailAttachment extends Model
{
    protected $guarded = [];

    protected $casts = ['cached_at' => 'datetime'];

    /** Opened in the reading pane rather than pushed straight to disk. */
    private const PREVIEWABLE = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/heic',
        'text/plain',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MailMessage::class, 'mail_message_id');
    }

    public function getPathAttribute(): string
    {
        return storage_path('app/mail-attachments/'.$this->stored_name);
    }

    public function getCachedAttribute(): bool
    {
        return $this->cached_at !== null && is_file($this->path);
    }

    /**
     * Everything else downloads. An archive or anything executable must never
     * be handed to a renderer — the browser is the wrong place to find out
     * what is inside it.
     */
    public function getPreviewableAttribute(): bool
    {
        return in_array(strtolower((string) $this->mime), self::PREVIEWABLE, true);
    }

    public function getSizeLabelAttribute(): string
    {
        $b = (int) $this->bytes;

        if ($b >= 1048576) {
            return round($b / 1048576, 1).' MB';
        }
        if ($b >= 1024) {
            return round($b / 1024).' KB';
        }

        return $b.' B';
    }
}
