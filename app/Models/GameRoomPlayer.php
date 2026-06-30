<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A player in an Undercover room. role is crook|cop|citizen (secret, concealed
 * by the app). codename is the public mask. The player is never asked to lie —
 * the Crook and Cop stay hidden only by staying silent.
 */
class GameRoomPlayer extends Model
{
    protected $fillable = [
        'game_room_id', 'name', 'token', 'codename', 'role', 'score', 'crook_vote',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }
}
