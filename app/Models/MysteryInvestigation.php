<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A Cop's private "is this codename the Crook?" check. */
class MysteryInvestigation extends Model
{
    protected $fillable = ['game_room_id', 'cop_player_id', 'target_codename', 'result', 'round_no'];
    protected $casts = ['result' => 'boolean'];
}
