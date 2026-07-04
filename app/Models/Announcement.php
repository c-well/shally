<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $casts = ['is_web_only' => 'boolean'];
    protected $fillable = ['bulletin_id', 'title', 'detail', 'image_path', 'video_url', 'sort_order', 'is_web_only'];
    public function bulletin() { return $this->belongsTo(Bulletin::class); }
}
