<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntercessorPrayerView extends Model
{
    public $timestamps = false;

    protected $fillable = ['intercessor_id', 'prayer_request_id', 'viewed_at'];

    protected $casts = ['viewed_at' => 'datetime'];
}
