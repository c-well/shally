<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MusicDay extends Model
{
    protected $fillable = ['title', 'service_date', 'token', 'is_open', 'note', 'files_purged_at'];
    protected $casts    = ['service_date' => 'date', 'is_open' => 'boolean', 'files_purged_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $d) {
            $d->token ??= Str::random(32);
        });

        /*
         * The rows cascade in the database, but the files do not. Without this
         * a deleted day leaves its MP3s on disk forever, invisible to everyone.
         */
        static::deleting(function (self $d) {
            foreach (glob($d->dir() . "/*") ?: [] as $f) @unlink($f);
            @rmdir($d->dir());
            foreach (glob(public_path('og/music/' . $d->id . '-*.png')) ?: [] as $f) @unlink($f);
        });
    }

    public function entries()
    {
        return $this->hasMany(MusicEntry::class)->orderBy('sort_order')->orderBy('id');
    }

    public function link(): string
    {
        return route('music.show', $this->token);
    }

    /** Where this day's tracks live. Outside the web root on purpose. */
    public function dir(): string
    {
        return storage_path('app/music/' . $this->id);
    }

    public function label(): string
    {
        return $this->title . ' · ' . $this->service_date->format('D j M Y');
    }
}
