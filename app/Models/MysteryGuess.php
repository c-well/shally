<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A "who is who" guess: this codename is that person. Scored at the reveal. */
class MysteryGuess extends Model
{
    protected $fillable = ['game_room_id', 'guesser_player_id', 'target_codename', 'guessed_player_id', 'is_correct'];
    protected $casts = ['is_correct' => 'boolean'];
}
