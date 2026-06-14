<?php
namespace App\Console\Commands;

use App\Models\AnthropicUsageLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAnthropicWeeklyReport extends Command
{
    protected $signature = 'anthropic:weekly-report';
    protected $description = 'Email Karlon last 7 days of Anthropic API spend';

    public function handle(): int
    {
        $since = Carbon::now('America/New_York')->subDays(7)->setTimezone('UTC');
        $total = AnthropicUsageLog::totalSpend($since);
        $calls = AnthropicUsageLog::where('created_at', '>=', $since)->count();
        $bySource = AnthropicUsageLog::spendBySource($since);
        $byModel  = AnthropicUsageLog::spendByModel($since);

        $body  = "The Church of Peace · Anthropic API spend — last 7 days\n";
        $body .= str_repeat('─', 56) . "\n\n";
        $body .= "Total:  $" . number_format($total, 4) . "  ({$calls} call" . ($calls === 1 ? '' : 's') . ")\n\n";

        $body .= "By source:\n";
        if (empty($bySource)) {
            $body .= "  (no calls)\n";
        } else {
            foreach ($bySource as $r) {
                $body .= sprintf("  %-32s %5d calls   \$%9.4f\n", $r['source'], $r['calls'], $r['total']);
            }
        }

        $body .= "\nBy model:\n";
        if (empty($byModel)) {
            $body .= "  (no calls)\n";
        } else {
            foreach ($byModel as $r) {
                $body .= sprintf("  %-32s %5d calls   \$%9.4f\n", $r['model'], $r['calls'], $r['total']);
            }
        }

        // OpenAI Whisper section — tallies all peace_whisper_transcribed audit events.
        // The Whisper key is shared with the IIG adminkc account, so usage tracked here
        // is the Shalom-pipeline portion only.
        $whisperEvents = \App\Models\AuditLog::where('event', 'peace_whisper_transcribed')
            ->where('created_at', '>=', $since)
            ->get();
        $whisperTotal  = 0;
        $whisperSeconds = 0;
        foreach ($whisperEvents as $ev) {
            $whisperTotal   += (float) ($ev->meta['cost_usd'] ?? 0);
            $whisperSeconds += (int)   ($ev->meta['duration_seconds'] ?? 0);
        }
        $body .= "\nOpenAI Whisper (shared IIG key, tracked locally):\n";
        if ($whisperEvents->isEmpty()) {
            $body .= "  (no Whisper fallbacks fired this week)\n";
        } else {
            $body .= sprintf("  %d transcription%s · %s of audio · \$%.4f\n",
                $whisperEvents->count(),
                $whisperEvents->count() === 1 ? '' : 's',
                gmdate('H:i:s', $whisperSeconds),
                $whisperTotal
            );
        }

        $body .= "\n" . str_repeat('─', 56) . "\n";
        $body .= "Live dashboard:  " . url('/admin/anthropic-usage') . "\n";
        $body .= "Pricing config:  config/anthropic_pricing.php  (edit when Anthropic changes rates)\n\n";
        $body .= "If you see unexpected activity here, the source column tells you which feature in The Church of Peace did it.\n";
        $body .= "Calls from elsewhere (Claude Code, skills, etc.) do NOT appear in this report — check the Anthropic Console for those.\n";
        $body .= "Whisper rows come from the audit_log (event=peace_whisper_transcribed). The OpenAI key is shared with adminkc/IIG.\n";

        $combined = $total + $whisperTotal;
        try {
            Mail::raw($body, function ($m) use ($combined) {
                $m->to('contact@c-wellpics.com')
                  ->subject(sprintf('[The Church of Peace] Weekly API spend — $%.2f', $combined));
            });
            $this->info("Weekly report emailed. Combined total: \$" . number_format($combined, 4));
        } catch (\Throwable $e) {
            $this->error("Email send failed: " . $e->getMessage());
            return self::FAILURE;
        }

        \App\Models\AuditLog::record(
            event: 'anthropic_weekly_report_sent',
            description: sprintf('Sent — last 7d total $%.4f across %d calls', $total, $calls),
        );

        return self::SUCCESS;
    }
}
