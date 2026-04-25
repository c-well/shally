<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    protected $fillable = [
        "kind", "path", "original_name", "mime", "width", "height",
        "size_bytes", "uploaded_by_user_id", "source_tag",
    ];

    public function url(): string
    {
        return "/" . ltrim($this->path, "/");
    }

    public function sizeHuman(): string
    {
        $b = $this->size_bytes;
        if ($b < 1024) return $b . " B";
        if ($b < 1048576) return round($b / 1024, 1) . " KB";
        return round($b / 1048576, 1) . " MB";
    }

    /** Where is this media currently in use? Returns short list of human-readable references. */
    public function usedIn(): array
    {
        $hits = [];
        $url = $this->url();
        // Sermons (cover_path)
        \App\Models\Sermon::where("cover_path", $this->path)->each(function ($s) use (&$hits) {
            $hits[] = "Sermon: " . $s->title;
        });
        // Slides
        if (class_exists(\App\Models\Slide::class)) {
            \App\Models\Slide::where("image_path", $this->path)->each(function ($s) use (&$hits) {
                $hits[] = "Slide: " . ($s->caption ?: "(no caption)");
            });
        }
        // Events (flyer_path)
        \App\Models\Event::where("flyer_path", $this->path)->each(function ($e) use (&$hits) {
            $hits[] = "Event: " . $e->title;
        });
        // Sermon descriptions referencing image markdown — slow, only when explicitly checked
        return $hits;
    }
}
