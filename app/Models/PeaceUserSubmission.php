<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeaceUserSubmission extends Model
{
    protected $table = 'peace_user_submissions';

    protected $fillable = [
        'subscriber_id', 'type', 'body',
        'related_sermon_id', 'related_topic_id',
        'attribution', 'attribution_display', 'status',
        'pastor_reply', 'pastor_replied_at', 'pastor_replied_by',
        'ip_hash', 'ua_hash',
    ];

    protected $casts = [
        'pastor_replied_at' => 'datetime',
    ];

    public function subscriber(): BelongsTo  { return $this->belongsTo(PeaceSubscriber::class, 'subscriber_id'); }
    public function sermon(): BelongsTo      { return $this->belongsTo(PeaceSermon::class, 'related_sermon_id'); }
    public function topic(): BelongsTo       { return $this->belongsTo(PeaceTopic::class, 'related_topic_id'); }
    public function replier(): BelongsTo     { return $this->belongsTo(User::class, 'pastor_replied_by'); }
}
