<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class LittleLightRun extends Model
{
    use MassPrunable;

    protected $guarded = ['id'];
    protected $hidden = ['token', 'browser_hash'];
    protected $casts = ['is_test' => 'boolean', 'last_seen_at' => 'datetime', 'last_active_at' => 'datetime', 'completed_at' => 'datetime'];

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(90));
    }

    public function scopeEngaged($query)
    {
        return $query->where('active_seconds', '>=', 10)->where(fn ($q) => $q->where('furthest_percent', '>=', 5)->orWhere('scrolls', '>', 0)->orWhere('lights', '>', 0));
    }
}
