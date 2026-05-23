<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Tracks every Anthropic API call made from the Shalom app.
 * Cost is computed at log-time from config('anthropic_pricing') so the snapshot
 * survives even if Anthropic changes pricing later.
 *
 * Use ::track(...) to record a call instead of constructing manually — it
 * computes cost and handles missing fields cleanly.
 */
class AnthropicUsageLog extends Model
{
    protected $table = 'anthropic_usage_logs';
    protected $fillable = [
        'source', 'model',
        'input_tokens', 'output_tokens',
        'cache_read_tokens', 'cache_write_tokens',
        'cost_usd', 'request_id',
        'outcome', 'error_message',
    ];
    protected $casts = [
        'input_tokens'       => 'integer',
        'output_tokens'      => 'integer',
        'cache_read_tokens'  => 'integer',
        'cache_write_tokens' => 'integer',
        'cost_usd'           => 'decimal:6',
    ];

    /**
     * Record an Anthropic call. Computes cost from pricing config.
     * Safe to call inside the try/catch of a service — never throws.
     */
    public static function track(array $data): ?self
    {
        try {
            $model = $data['model'] ?? 'unknown';
            $pricing = config("anthropic_pricing.models.$model", config('anthropic_pricing.default'));

            $in   = (int) ($data['input_tokens']  ?? 0);
            $out  = (int) ($data['output_tokens'] ?? 0);
            $cr   = (int) ($data['cache_read_tokens']  ?? 0);
            $cw   = (int) ($data['cache_write_tokens'] ?? 0);

            // Cost in USD — per million tokens, so divide by 1,000,000
            $cost = (
                ($in  * $pricing['input'])
              + ($out * $pricing['output'])
              + ($cr  * ($pricing['cache_read']  ?? $pricing['input']))
              + ($cw  * ($pricing['cache_write'] ?? $pricing['input']))
            ) / 1_000_000;

            $row = static::create([
                'source'             => $data['source'] ?? 'unknown',
                'model'              => $model,
                'input_tokens'       => $in,
                'output_tokens'      => $out,
                'cache_read_tokens'  => $cr,
                'cache_write_tokens' => $cw,
                'cost_usd'           => round($cost, 6),
                'request_id'         => $data['request_id'] ?? null,
                'outcome'            => $data['outcome'] ?? 'ok',
                'error_message'      => $data['error_message'] ?? null,
            ]);

            // EXPENSIVE_CALL_ALERT — circuit breaker. One call crossing threshold
            // fires an immediate alert email + audit log entry. Configurable
            // in config/anthropic_pricing.php (default $1.00).
            $threshold = (float) config('anthropic_pricing.alert_threshold_usd', 1.00);
            if ($cost >= $threshold) {
                try {
                    $body = "⚠ Anthropic single-call spike
";
                    $body .= str_repeat('─', 50) . "

";
                    $body .= 'Cost:        $' . number_format($cost, 4) . "
";
                    $body .= 'Threshold:   $' . number_format($threshold, 2) . "
";
                    $body .= 'Source:      ' . ($data['source'] ?? 'unknown') . "
";
                    $body .= 'Model:       ' . $model . "
";
                    $body .= 'Input tok:   ' . number_format($in)  . "
";
                    $body .= 'Output tok:  ' . number_format($out) . "
";
                    $body .= 'Request ID:  ' . ($data['request_id'] ?? 'n/a') . "
";
                    $body .= 'When:        ' . now()->toDayDateTimeString() . " " . config('app.timezone') . "

";
                    $body .= 'This is a single call. The Church of Peace Sonnet pipeline normally costs ~$0.07 per sermon — a $' . number_format($cost, 2) . ' single call suggests a runaway loop, accidental Opus, or a massive context. Investigate.

';
                    $body .= 'Dashboard: ' . url('/admin/anthropic-usage') . "
";
                    \Illuminate\Support\Facades\Mail::raw($body, function ($m) use ($cost) {
                        $m->to('contact@c-wellpics.com')
                          ->subject(sprintf('⚠ [The Church of Peace] Single Anthropic call spent $%.2f', $cost));
                    });
                    \App\Models\AuditLog::record(
                        event: 'anthropic_expensive_call',
                        description: sprintf('Single call cost $%.4f (source=%s, model=%s)', $cost, $data['source'] ?? '?', $model),
                    );
                } catch (\Throwable $e) { /* swallow — never block the actual API flow */ }
            }

            return $row;
        } catch (\Throwable $e) {
            // Never break a real API call because logging failed
            Log::warning('AnthropicUsageLog::track failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /** Helper: total spend in $ for a given time window. */
    public static function totalSpend(\Carbon\Carbon $since): float
    {
        return (float) static::where('created_at', '>=', $since)->sum('cost_usd');
    }

    /** Helper: group spend by source for a window. */
    public static function spendBySource(\Carbon\Carbon $since): array
    {
        return static::where('created_at', '>=', $since)
            ->selectRaw('source, SUM(cost_usd) as total, COUNT(*) as calls, SUM(input_tokens) as input, SUM(output_tokens) as output')
            ->groupBy('source')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    /** Helper: group spend by model for a window. */
    public static function spendByModel(\Carbon\Carbon $since): array
    {
        return static::where('created_at', '>=', $since)
            ->selectRaw('model, SUM(cost_usd) as total, COUNT(*) as calls')
            ->groupBy('model')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }
}
