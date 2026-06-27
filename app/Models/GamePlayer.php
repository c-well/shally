<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GamePlayer extends Model
{
    protected $fillable = ['name','token','total_stars'];
    public function progress() { return $this->hasMany(GameProgress::class); }
}
