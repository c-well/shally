<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntercessorPrayerPrayed extends Model
{
    protected $table = 'intercessor_prayer_prayed';
    public $timestamps = false;

    protected $fillable = ['intercessor_id', 'prayer_request_id', 'prayed_at'];

    protected $casts = ['prayed_at' => 'datetime'];
}
