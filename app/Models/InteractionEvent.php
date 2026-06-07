<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractionEvent extends Model
{
    public $timestamps = false; // we manage created_at explicitly

    protected $fillable = [
        'event_type', 'path', 'session_id', 'ip_hash', 'country', 'device',
        'viewport_w', 'viewport_h', 'x', 'y', 'scroll_pct',
        'element_selector', 'element_text', 'meta', 'is_bot', 'created_at',
    ];

    protected $casts = [
        'meta'        => 'array',
        'is_bot'      => 'boolean',
        'created_at'  => 'datetime',
        'x'           => 'integer',
        'y'           => 'integer',
        'viewport_w'  => 'integer',
        'viewport_h'  => 'integer',
        'scroll_pct'  => 'integer',
    ];
}
