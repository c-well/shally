<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MusicEntry extends Model
{
    public const METHODS  = ['upload', 'youtube', 'bringing'];
    public const MEDIUMS  = ['cd', 'usb'];
    public const AUDIO_EXT = ['mp3', 'm4a', 'aac', 'wav', 'flac', 'ogg', 'aif', 'aiff'];

    protected $fillable = [
        'music_day_id', 'name', 'song_title', 'method', 'medium', 'source_url',
        'file_path', 'original_name', 'duration_seconds', 'size_bytes',
        'status', 'error', 'attempts', 'sort_order', 'ip_hash',
    ];

    protected static function booted(): void
    {
        /* Whatever removes an entry, the track goes with it. */
        static::deleting(function (self $e) {
            if ($e->file_path && is_file($e->file_path)) @unlink($e->file_path);
        });
    }

    public function day()
    {
        return $this->belongsTo(MusicDay::class, 'music_day_id');
    }

    public function hasTrack(): bool
    {
        return $this->status === 'ready' && $this->file_path && is_file($this->file_path);
    }

    public function durationLabel(): ?string
    {
        if (! $this->duration_seconds) return null;
        return intdiv($this->duration_seconds, 60) . ':' . str_pad((string) ($this->duration_seconds % 60), 2, '0', STR_PAD_LEFT);
    }

    /** What André sees on the card. Plain words, not status codes. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'ready'    => 'Track in',
            'queued', 'fetching' => 'Getting it from YouTube',
            'failed'   => 'Could not get it',
            'bringing' => 'Bringing it on ' . strtoupper($this->medium ?: 'CD/USB'),
            default    => ucfirst($this->status),
        };
    }

    /** Filename for the day's download — sorts in running order on any USB stick. */
    public function exportName(int $position): string
    {
        $clean = fn ($s) => trim(preg_replace('/[^\pL\pN \'&,.()-]+/u', '', (string) $s)) ?: 'Untitled';
        $ext   = pathinfo((string) $this->file_path, PATHINFO_EXTENSION) ?: 'mp3';
        return sprintf('%02d - %s - %s.%s', $position, $clean($this->name), $clean($this->song_title ?: 'Song'), strtolower($ext));
    }
}
