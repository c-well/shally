<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class GameRun extends Model
{
    use MassPrunable;

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(90));
    }

    protected $guarded = ['id'];

    protected $hidden = ['token'];

    protected $casts = ['challenge' => 'array', 'state' => 'array', 'qualifies' => 'boolean', 'sequence' => 'integer', 'actions' => 'integer', 'active_seconds' => 'integer', 'invalid_actions' => 'integer', 'last_action_at' => 'datetime', 'completed_at' => 'datetime'];

    public function level()
    {
        return $this->belongsTo(GameLevel::class, 'game_level_id');
    }
}
