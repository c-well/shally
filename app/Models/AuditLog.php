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

        $row = static::create([
            'user_id'     => $userId,
            'event'       => mb_substr($event, 0, 60),
            'description' => $description ? mb_substr($description, 0, 65535) : null,
            'ip_address'  => $req?->ip(),
            'user_agent'  => $req?->userAgent() ? mb_substr($req->userAgent(), 0, 255) : null,
            'url'         => $req?->fullUrl() ? mb_substr($req->fullUrl(), 0, 500) : null,
            'meta'        => $meta ?: null,
            'created_at'  => now(),  // explicit — MySQL's useCurrent() default uses server BST tz
        ]);

        // Auto-block IPs that pile up auth failures fast.
        // Events that count toward the threshold:
        $failureEvents = ['magic_link_request', 'login_failed', 'magic_link_honeypot', 'pin_login_failed'];
        if ($req && in_array($event, $failureEvents, true)
            && ($event !== 'magic_link_request' || ($meta['matched'] ?? true) === false)
        ) {
            $ip = $req->ip();
            if (!\DB::table('blocked_ips')->where('ip_address', $ip)->exists()) {
                $count = static::where('ip_address', $ip)
                    ->whereIn('event', $failureEvents)
                    ->where('created_at', '>=', now()->subDay())
                    ->count();
                if ($count > 20) {
                    \DB::table('blocked_ips')->insert([
                        'ip_address'   => $ip,
                        'reason'       => $event . ' (>20 failures/24h)',
                        'blocked_at'   => now(),
                        'expires_at'   => now()->addDay(),
                        'hits_at_block'=> $count,
                    ]);
                    static::create([
                        'event'       => 'ip_auto_blocked',
                        'description' => 'Auto-blocked IP ' . $ip . ' for 24h after ' . $count . ' failures',
                        'ip_address'  => $ip,
                        'meta'        => ['count' => $count, 'triggering_event' => $event],
                    ]);
                }
            }
        }

        return $row;
    }
}
