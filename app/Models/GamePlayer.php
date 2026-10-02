<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    protected $fillable = ['name', 'token', 'total_stars', 'review_status'];

    protected $hidden = ['token'];

    public function runs()
    {
        return $this->hasMany(GameRun::class);
    }

    public function progress()
    {
        return $this->hasMany(GameProgress::class);
    }
}
