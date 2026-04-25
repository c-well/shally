<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;
    protected $fillable = [
        "path", "referrer", "user_agent", "ip_hash", "country",
        "session_id", "device", "browser", "viewed_at",
    ];
    protected $casts = ["viewed_at" => "datetime"];
}
