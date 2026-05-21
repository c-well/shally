<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PeaceTopic extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function sermons(): BelongsToMany
    {
        return $this->belongsToMany(PeaceSermon::class, 'peace_sermon_topic', 'topic_id', 'sermon_id');
    }

    /** Find or create a topic from a free-text name (Claude returns topic strings). */
    public static function fromName(string $name): self
    {
        $name = trim(strtolower($name));
        return static::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        );
    }
}
