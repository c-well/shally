<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A hosted "Undercover" room. Identified publicly by a short code (players join)
 * and controlled privately by a host_token (the leader's screen). State lives
 * here so a dropped phone can rejoin by code and resume.
 */
class GameRoom extends Model
{
    protected $fillable = [
        'code', 'host_token', 'status', 'round_no', 'rounds_total',
        'current_question_id', 'current_question_started_at', 'settings',
    ];

    protected $casts = [
        'settings'                    => 'array',
        'current_question_started_at' => 'datetime',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(GameRoomPlayer::class);
    }

    public function question()
    {
        return $this->belongsTo(MysteryQuestion::class, 'current_question_id');
    }
}
