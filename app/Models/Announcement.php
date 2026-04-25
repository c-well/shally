<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['bulletin_id', 'title', 'detail', 'sort_order'];
    public function bulletin() { return $this->belongsTo(Bulletin::class); }
}
