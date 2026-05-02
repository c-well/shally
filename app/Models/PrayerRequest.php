<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerRequest extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'body',
        'want_followup', 'keep_private',
        'ip', 'user_agent',
    ];

    protected $casts = [
        'want_followup' => 'boolean',
        'keep_private'  => 'boolean',
    ];
}
