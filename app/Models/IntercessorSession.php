<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntercessorSession extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'intercessor_id', 'token_hash', 'last_ip', 'user_agent',
        'last_seen_at', 'expires_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function intercessor(): BelongsTo { return $this->belongsTo(Intercessor::class); }

    public static function hashToken(string $raw): string {
        return hash('sha256', $raw);
    }
}
