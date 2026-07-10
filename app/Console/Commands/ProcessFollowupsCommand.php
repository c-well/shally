<?php
namespace App\Console\Commands;

use App\Models\Guest;
use App\Models\GuestFollowup;
use App\Services\Intake\TwilioNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * The follow-up engine (daily 10:00 AM NY via scheduler).
 * Sends due followups — SMS first, email fallback — and mints birthday
 * touches on the day. Personalized bodies (set by a human in /admin/guests)
 * are sent verbatim; otherwise the warm defaults go out.
 */
class ProcessFollowupsCommand extends Command
{
    protected $signature = 'followups:process {--dry : Report without sending}';
    protected $description = 'Send due guest followups + today\'s birthdays';

    public function handle(TwilioNotifier $sms): int
    {
        $today = now('America/New_York')->toDateString();

        // Birthdays → mint a followup row for today (idempotent per year)
        $m = (int) now('America/New_York')->format('n');
        $d = (int) now('America/New_York')->format('j');
        foreach (Guest::where('birthday_month', $m)->where('birthday_day', $d)->get() as $g) {
            $already = $g->followups()->where('kind', 'birthday')->whereYear('due_on', now()->year)->exists();
            if (! $already) $g->followups()->create(['kind' => 'birthday', 'due_on' => $today]);
        }

        $due = GuestFollowup::with('guest')->where('status', 'pending')->where('due_on', '<=', $today)->get();
        $sent = 0; $failed = 0;
        foreach ($due as $f) {
            $g = $f->guest;
            if (! $g) { $f->update(['status' => 'skipped']); continue; }
            $body = trim((string) ($f->body ?: $f->defaultBody()));
            if ($this->option('dry')) { $this->line("DRY {$g->name} [{$f->kind}]: " . mb_substr($body, 0, 60)); continue; }

            $ok = false; $channel = null;
            if ($g->phone && $sms->configured()) {
                $ok = $sms->send($g->phone, $body); $channel = 'sms';
            }
            if (! $ok && $g->email) {
                try {
                    Mail::raw($body . "\n\n— The Church of Peace · thechurchofpeace.org", function ($mail) use ($g) {
                        $mail->to($g->email)->cc('contact@c-wellpics.com')
                             ->subject('From the Shalom family');
                    });
                    $ok = true; $channel = 'email';
                } catch (\Throwable $e) { $ok = false; }
            }
            $f->update(['status' => $ok ? 'sent' : 'failed', 'channel' => $channel, 'sent_at' => $ok ? now() : null]);
            $ok ? $sent++ : $failed++;
        }
        $this->info("due={$due->count()} sent={$sent} failed={$failed}");
        return self::SUCCESS;
    }
}
