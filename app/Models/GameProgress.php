<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GameProgress extends Model
{
    protected $table = 'game_progress';
    protected $fillable = ['game_player_id','game_level_id','state','best_score','stars','completed_at'];
    protected $casts = ['state' => 'array', 'completed_at' => 'datetime'];
    public function player() { return $this->belongsTo(GamePlayer::class, 'game_player_id'); }
    public function level() { return $this->belongsTo(GameLevel::class, 'game_level_id'); }
}
