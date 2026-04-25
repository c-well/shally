<?php
namespace App\Models;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A single sermon — title, audio file, optional summary in markdown.
 *
 * Slugs auto-generate from titles on creation (with collision suffix).
 * The description_html cache is regenerated automatically on save via the
 * booted() observer — same pattern as App\Models\Page.
 */
class Sermon extends Model
{
    protected $fillable = [
        'slug', 'title', 'header_text', 'speaker', 'preached_on', 'scripture_ref',
        'description_md', 'description_html', 'audio_path', 'audio_size_bytes',
        'video_url', 'cover_path',
        'updated_by_user_id',
    ];

    protected $casts = ['preached_on' => 'date'];

    protected static function booted(): void
    {
        static::saving(function (Sermon $s) {
            // Auto-slug if not set or if the title changed
            if (empty($s->slug) || ($s->isDirty('title') && empty($s->getOriginal('slug')))) {
                $s->slug = static::uniqueSlug($s->title, $s->id);
            }
            // Re-render markdown → HTML when source changes
            if ($s->isDirty('description_md')) {
                $s->description_html = $s->description_md
                    ? Page::renderMarkdown($s->description_md)
                    : null;
            }
        });
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by_user_id');
    }

    /** Public URL for the audio file. */
    public function audioUrl(): ?string
    {
        return $this->audio_path ? '/' . ltrim($this->audio_path, '/') : null;
    }

    /** Public URL for the cover image (if set). */
    public function coverUrl(): ?string
    {
        return $this->cover_path ? '/' . ltrim($this->cover_path, '/') : null;
    }

    /** Pretty file size for display ("4.2 MB"). */
    public function audioSizeHuman(): string
    {
        $b = $this->audio_size_bytes;
        if ($b < 1024) return $b . ' B';
        if ($b < 1024 * 1024) return round($b / 1024, 1) . ' KB';
        return round($b / 1024 / 1024, 1) . ' MB';
    }

    /** Generate a unique slug from a title, appending -2, -3 if needed. */
    public static function uniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title) ?: 'sermon';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
