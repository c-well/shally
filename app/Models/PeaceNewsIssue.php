<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One week of Peace Notes News, drafted from that Sabbath's PeaceSermon.
 * draft → approved (first approval wins) → sent at send_after (Sunday 8 AM ET).
 * held = an approver said not this week; nothing sends.
 */
class PeaceNewsIssue extends Model
{
    protected $fillable = [
        'sermon_id', 'slug', 'status', 'questions',
        'opening_summary', 'opening_quote', 'opening_seconds',
        'closing_summary', 'closing_quote', 'closing_seconds',
        'checks', 'review_token', 'review_sent_at',
        'approved_by', 'approved_at', 'send_after', 'sent_at', 'sent_count',
    ];

    protected $casts = [
        'questions'      => 'array',
        'checks'         => 'array',
        'review_sent_at' => 'datetime',
        'approved_at'    => 'datetime',
        'send_after'     => 'datetime',
        'sent_at'        => 'datetime',
    ];

    public function sermon(): BelongsTo { return $this->belongsTo(PeaceSermon::class, 'sermon_id'); }
    public function answers(): HasMany  { return $this->hasMany(PeaceNewsAnswer::class, 'issue_id'); }

    public function isPublic(): bool { return $this->status === 'sent'; }

    /** The issue before this one that went out, for "You said, last week". */
    public function previous(): ?self
    {
        return self::where('status', 'sent')->where('send_after', '<', $this->send_after)
            ->orderByDesc('send_after')->first();
    }

    /** Answers from the previous issue that may be quoted here. */
    public function youSaid(int $limit = 3)
    {
        $prev = $this->previous();
        if (! $prev) return collect();
        return $prev->answers()->where('status', 'approved')->where('share_ok', true)
            ->orderByDesc('reviewed_at')->limit($limit)->get();
    }

    public function youtubeAt(?int $seconds): ?string
    {
        $vid = $this->sermon?->youtube_video_id;
        if (! $vid) return null;
        return 'https://www.youtube.com/watch?v=' . $vid . ($seconds ? '&t=' . $seconds . 's' : '');
    }

    public static function clock(?int $s): string
    {
        if ($s === null) return '';
        return sprintf('%d:%02d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60);
    }
}
