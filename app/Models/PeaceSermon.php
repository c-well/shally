<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeaceSermon extends Model
{
    protected $fillable = [
        'youtube_video_id', 'slug', 'title', 'speaker', 'sermon_date',
        'heart_line', 'summary_paragraphs',
        'audio_url', 'audio_duration_seconds', 'audio_status',
        'transcript_raw',
        'sermon_start_seconds', 'sermon_end_seconds',
        'boundary_confidence', 'boundary_reason', 'boundary_source',
        'processing_status', 'processing_attempts', 'last_error',
        'published_at',
    ];

    protected $casts = [
        'sermon_date'             => 'date',
        'summary_paragraphs'      => 'array',
        'sermon_start_seconds'    => 'integer',
        'sermon_end_seconds'      => 'integer',
        'boundary_confidence'     => 'float',
        'audio_duration_seconds'  => 'integer',
        'processing_attempts'     => 'integer',
        'published_at'            => 'datetime',
    ];

    public function qaPairs(): HasMany       { return $this->hasMany(PeaceQaPair::class, 'sermon_id'); }
    public function scriptures(): HasMany    { return $this->hasMany(PeaceScripture::class, 'sermon_id'); }
    public function topics(): BelongsToMany  { return $this->belongsToMany(PeaceTopic::class, 'peace_sermon_topic', 'sermon_id', 'topic_id'); }

    public function isPublished(): bool { return $this->published_at !== null; }
    public function needsReview(): bool { return $this->processing_status === 'needs_review'; }
}
