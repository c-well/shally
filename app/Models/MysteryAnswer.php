<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One player's answer to one round's question (or a deliberate silence). */
class MysteryAnswer extends Model
{
    protected $fillable = ['game_room_id', 'round_no', 'player_id', 'question_id', 'answer', 'stayed_silent'];
    protected $casts = ['stayed_silent' => 'boolean'];
}
