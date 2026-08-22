<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One landing on a handout. Deliberately thin: a daily-salted hash instead of
 * an IP, a referrer, a timestamp. Enough to tell the clerk "31 people opened
 * it, 4 of them today" — not enough to follow anybody anywhere.
 */
class HandoutVisit extends Model
{
    public $timestamps = false;

    protected $fillable = ['handout_id', 'visitor_hash', 'referrer', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function handout()
    {
        return $this->belongsTo(Handout::class);
    }
}
