<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class AuditLog extends Model
{
    public $timestamps = false;  // we only have created_at
    protected $guarded = ['id'];
    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /**
     * Single entry-point for recording a log line. Pulls IP/UA/URL from the current request
     * automatically (or accepts overrides for cases where there's no request — e.g. console).
     */
    public static function record(
        string $event,
        ?int $userId = null,
        ?string $description = null,
        array $meta = [],
        ?Request $request = null,
    ): self {
        $req = $request ?? (function_exists('request') ? request() : null);

        return static::create([
            'user_id'     => $userId,
            'event'       => mb_substr($event, 0, 60),
            'description' => $description ? mb_substr($description, 0, 65535) : null,
            'ip_address'  => $req?->ip(),
            'user_agent'  => $req?->userAgent() ? mb_substr($req->userAgent(), 0, 255) : null,
            'url'         => $req?->fullUrl() ? mb_substr($req->fullUrl(), 0, 500) : null,
            'meta'        => $meta ?: null,
        ]);
    }
}
