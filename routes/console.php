<?php

use App\Models\Announcement;
use App\Models\Bulletin;
use App\Models\BulletinLine;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('flyers:prune', function () {
    $cutoff = now()->subDays(7);
    $events = Event::whereNotNull('flyer_path')
        ->where('start_at', '<', $cutoff)
        ->get();

    $deleted = 0;
    foreach ($events as $event) {
        $abs = public_path($event->flyer_path);
        if (is_file($abs)) @unlink($abs);
        $event->flyer_path = null;
        $event->save();
        $deleted++;
    }

    $this->info("Pruned {$deleted} flyer(s) (events older than 7 days).");
})->purpose('Delete flyer images for events that ended more than 7 days ago');

Artisan::command('flyers:warn', function () {
    // Events whose start_at is between 7 and 6 days ago — pruned tomorrow.
    $events = Event::whereNotNull('flyer_path')
        ->whereBetween('start_at', [now()->subDays(7), now()->subDays(6)])
        ->orderBy('start_at')
        ->get();

    if ($events->isEmpty()) {
        $this->info('No flyers up for pruning in the next 24h.');
        return;
    }

    $admins = User::where('role', 'super_admin')->pluck('email')->all();
    if (empty($admins)) {
        $this->warn('No super_admin recipients found.');
        return;
    }

    $lines = $events->map(fn($e) => "- {$e->title} ({$e->start_at->format('M j, Y')}) — /{$e->flyer_path}")->implode("\n");
    $body = "These event flyers will be deleted in ~24 hours (1 week after the event date):\n\n{$lines}\n\nApp: " . config('app.url');

    Mail::raw($body, function ($msg) use ($admins) {
        $msg->to($admins)
            ->cc('contact@c-wellpics.com')
            ->subject('[Church of Peace] Flyers scheduled for deletion in 24h');
    });

    $this->info('Warning email sent for ' . $events->count() . ' flyer(s).');
})->purpose('Email super-admins 24h before flyers are pruned');

Artisan::command('bulletins:prune', function () {
    // Bulletins with service_date more than 24h in the past: drop heavy ephemeral content
    // (free-text body, PDF file, snapshot). KEEP lines (names + songs as a record) and
    // announcements (Andre wants those to persist until explicitly deleted).
    $cutoff = now()->subDay()->toDateString();
    $bulletins = Bulletin::where('service_date', '<', $cutoff)->get();

    $cleaned = 0;
    foreach ($bulletins as $b) {
        $hadContent = $b->pdf_path || $b->body || $b->published_snapshot;
        if (! $hadContent) continue;

        if ($b->pdf_path) {
            $abs = public_path($b->pdf_path);
            if (is_file($abs)) @unlink($abs);
        }

        // PRUNE_NARROWED — original intent was to reclaim DISK SPACE (heavy PDF files),
        // not to nuke DB columns. Body text + JSON snapshot are tiny; nulling the snapshot
        // broke URL navigation to past bulletins (Andre saw empty pages after May 18).
        // Now we only null pdf_path (the file itself is unlinked above).
        $b->update([
            'pdf_path' => null,
        ]);
        $cleaned++;
    }

    $this->info("Pruned heavy content from {$cleaned} bulletin(s) — lines and announcements retained.");
})->purpose('Clear body/pdf/snapshot for bulletins more than 24h past their service_date (keep lines + announcements)');

Schedule::command('flyers:prune')->dailyAt('03:15');
Schedule::command('flyers:warn')->dailyAt('03:00');
Schedule::command('bulletins:prune')->dailyAt('03:30');
Schedule::command('sermons:refresh-latest')->dailyAt('04:10');            // home-page latest-service failsafe
Schedule::command('sermons:refresh-latest')->saturdays()->at('21:00');     // catch the new Sabbath upload
Schedule::command('bulletins:purge-old')->dailyAt('03:45');  // hard-delete 14+ day old bulletins (PurgeOldBulletins)
Schedule::command('feedback:purge-old')->dailyAt('03:50');   // hard-delete closed tickets > 14 days (PurgeOldTickets)
Schedule::command('audit:purge-old')->dailyAt('03:55');      // hard-delete audit log entries > 40 days (PurgeOldAuditLogs)
Schedule::command('bulletins:clear-stale-previous-snapshots')->hourly();  // reclaim previous_published_* cols 8+h after supersession
// The handout heartbeat. Morning slot on purpose: a "still needed?" push at
// 9am gets answered; one at 4am is gone by the time anybody looks.
Schedule::command('handouts:nudge')->dailyAt('09:10')->timezone('America/New_York');

Schedule::command('lesson:ensure-current')->dailyAt('04:20')->timezone('America/New_York'); // auto-roll the Sabbath School quarter (Q3 2026 was missed manually — never again)

// Rotate the markdown feedback log when it grows past 5MB so the tail-read in ClaudeAssistant
// stays fast and the file doesn't run away with disk quota on the cPanel account.
Schedule::call(function () {
    $path = '/home/shalom/feedback-log.md';
    if (is_file($path) && filesize($path) > 5_000_000) {
        @rename($path, $path . '.' . date('Y-m-d-His') . '.bak');
    }
})->weeklyOn(0, '02:00')->name('feedback-log-rotate');

// Telemetry — prune page_views older than 90 days so the table never balloons.
// Runs daily at 04:00 (after the rest of the cleanup batch).
Schedule::call(function () {
    \DB::table("page_views")->where("viewed_at", "<", now()->subDays(90))->delete();
})->dailyAt("04:00")->name("page-views-prune");

// Hourly state-machine tick — flips pending_review → draft after 72h,
// and sends the 48h confirm-delete reminder.
Schedule::command('peace:review-tick')
    ->hourly()
    ->onOneServer()
    ->name('peace-review-tick');


// SERMON SCANNER — 4 knocks/week (Karlon 2026-07-16: "realistically how many do we
// need... 3x should be enough"). Captions for a 3h stream land 2-6h after it ends, so
// the old 3pm/4:30 knocks were always early. Sat 5pm (early catch) + Sat 9pm (typical)
// + Sun 7am (overnight) + Wed 7am (last chance before the 6pm Whisper fallback).
// Each scan is a free no-op unless a new captioned stream exists.
Schedule::command('peace:scan-channel')
    ->saturdays()->at('17:00')
    ->timezone('America/New_York')
    ->onOneServer()
    ->emailOutputOnFailure('contact@c-wellpics.com')
    ->name('peace-scan-channel-sat-5pm');

Schedule::command('peace:scan-channel')
    ->saturdays()->at('21:00')
    ->timezone('America/New_York')
    ->onOneServer()
    ->emailOutputOnFailure('contact@c-wellpics.com')
    ->name('peace-scan-channel-retry-9pm');

Schedule::command('peace:scan-channel')
    ->sundays()->at('07:00')
    ->timezone('America/New_York')
    ->onOneServer()
    ->emailOutputOnFailure('contact@c-wellpics.com')
    ->name('peace-scan-channel-retry-sun-7am');
// Sunday 12pm ET sanity check — verify a sermon was actually processed yesterday.
// If no peace_sermons row was created since Saturday 00:00 ET, alert Karlon.
// Silent on success (no alert spam).
Schedule::call(function () {
    $sat = \Carbon\Carbon::now('America/New_York')->subDay()->startOfDay()->setTimezone('UTC');
    $latest = \App\Models\PeaceSermon::where('created_at', '>=', $sat)
        ->orderByDesc('created_at')->first();
    if (! $latest) {
        $body  = "Sanity check failed: no peace_sermons row was created since " .
                 $sat->setTimezone('America/New_York')->format('D M j g:i A T') . ".\n\n";
        $body .= "Either the auto-scan failed both fires (Sat 3pm + Sat 4:30pm) OR there\'s no Sabbath sermon on YouTube yet.\n\n";
        $body .= "Manual trigger: https://thechurchofpeace.org/admin/peace/schedule\n";
        $body .= "Audit log:      https://thechurchofpeace.org/admin/logs\n";
        try {
            \Illuminate\Support\Facades\Mail::raw($body, function ($m) {
                $m->to('contact@c-wellpics.com')
                  ->cc('andre.marshall@gmail.com')
                  ->subject('[Shalom] Sabbath sermon NOT processed — needs attention');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('peace sanity-check: alert email failed', ['err' => $e->getMessage()]);
        }
        \App\Models\AuditLog::record(
            event: 'peace_sanity_check_failed',
            description: 'Sunday noon sanity check: no sermon processed since ' . $sat->toIso8601String(),
        );
    } else {
        \App\Models\AuditLog::record(
            event: 'peace_sanity_check_passed',
            description: 'Sunday noon sanity check: sermon #' . $latest->id . ' (' . $latest->title . ') processed at ' . $latest->created_at->toIso8601String(),
        );
    }
})->sundays()->at('12:00')->timezone('America/New_York')->name('peace-sunday-sanity-check')->onOneServer();

// Anthropic API spend weekly summary email (Sunday 9am ET)
Schedule::command('anthropic:weekly-report')
    ->sundays()
    ->at('09:00')
    ->timezone('America/New_York')
    ->name('anthropic-weekly-report')
    ->onOneServer();

// LIVE_DETECTOR — polls YouTube, caches is_live to AppSetting; hero CTA reads only cache.
// Cadence RULED by Karlon (2026-07-17 final): Sabbath 10:55 + 11:15 go-live, 3:00 ending.
// Crusade (self-expires Jul 25): Sat 10am morning session; evenings 6:50 + 7:15 on
// Sun/Tue/Wed/Fri + Sat; 8pm ending; 10:15pm sweep ON CRUSADE NIGHTS ONLY —
// "don't check nightly outside of a crusade night." No other polling exists.
Schedule::command('peace:check-live')
    ->cron('55 10 * * 6')
    ->timezone('America/New_York')
    ->onOneServer()
    ->name('peace-check-live-sabbath-1055');

Schedule::command('peace:check-live')
    ->cron('15 11 * * 6')
    ->timezone('America/New_York')
    ->onOneServer()
    ->name('peace-check-live-sabbath-1115');

Schedule::command('peace:check-live')
    ->cron('0 15 * * 6')
    ->timezone('America/New_York')
    ->onOneServer()
    ->name('peace-check-live-sabbath-3pm-ending');

Schedule::command('peace:check-live')
    ->cron('0 19 * * 6')
    ->timezone('America/New_York')
    ->onOneServer()
    ->name('peace-check-live-sabbath-7pm');

$crusade = fn () => now('America/New_York')->lte(\Carbon\Carbon::parse('2026-07-25 23:59', 'America/New_York'));

Schedule::command('peace:check-live')
    ->cron('0 10 * * 6')
    ->timezone('America/New_York')->when($crusade)->onOneServer()
    ->name('peace-check-live-crusade-sat-10am');

Schedule::command('peace:check-live')
    ->cron('50 18 * * 0,2,3,5,6')
    ->timezone('America/New_York')->when($crusade)->onOneServer()
    ->name('peace-check-live-crusade-650pm');

Schedule::command('peace:check-live')
    ->cron('15 19 * * 0,2,3,5,6')
    ->timezone('America/New_York')->when($crusade)->onOneServer()
    ->name('peace-check-live-crusade-715pm');

Schedule::command('peace:check-live')
    ->cron('0 20 * * 0,2,3,5,6')
    ->timezone('America/New_York')->when($crusade)->onOneServer()
    ->name('peace-check-live-crusade-8pm-ending');

Schedule::command('peace:check-live')
    ->cron('15 22 * * 0,2,3,5,6')
    ->timezone('America/New_York')->when($crusade)->onOneServer()
    ->name('peace-check-live-crusade-night-sweep');


// CRON_HEARTBEAT (2026-05-27) — every minute, write a sentinel timestamp to
// AppSetting so the audit can verify "is cron itself firing?". If this row
// goes stale, schedule:run has stopped, OS cron broke, or something
// fundamental is wrong.
Schedule::call(function () {
    \App\Models\AppSetting::set('cron_heartbeat_at', now()->toIso8601String());
})->everyMinute()->name('cron-heartbeat')->onOneServer();

// DAILY_SPEND_CAP (2026-05-27) — hourly check that today and last-7-days
// Anthropic spend stay within budget. Per-call cap already enforces $1.
// This catches "many small calls → big total" scenarios.
Schedule::call(function () {
    $dailyMax = 5.00;
    $weeklyMax = 30.00;
    $today = \App\Models\AnthropicUsageLog::totalSpend(now()->startOfDay());
    $week  = \App\Models\AnthropicUsageLog::totalSpend(now()->subDays(7));

    $lastAlert = \App\Models\AppSetting::get('spend_cap_last_alert_at', '1970-01-01T00:00:00Z');
    $cooldown = \Carbon\Carbon::parse($lastAlert)->lt(now()->subHours(6));
    if (! $cooldown) return;

    $breach = null;
    if ($today > $dailyMax)      $breach = ['DAILY', $today, $dailyMax];
    elseif ($week  > $weeklyMax) $breach = ['7-DAY', $week,  $weeklyMax];
    if (! $breach) return;

    $body = sprintf(
        "%s Anthropic spend cap tripped.\n\nCurrent: \$%.2f\nCap:     \$%.2f\n\nDashboard: %s\n",
        $breach[0], $breach[1], $breach[2], url('/admin/anthropic-usage')
    );
    \Illuminate\Support\Facades\Mail::raw($body, function ($m) use ($breach) {
        $m->to('contact@c-wellpics.com')
          ->subject(sprintf('⚠ [The Church of Peace] %s spend cap tripped: $%.2f', $breach[0], $breach[1]));
    });
    \App\Models\AppSetting::set('spend_cap_last_alert_at', now()->toIso8601String());
})->hourly()->name('anthropic-spend-cap')->onOneServer();

// INTERACTION_ROLLUP — daily aggregation of clicks/hovers/scroll into
// heatmap_summaries + scroll_summaries; deletes raw rows older than 30 days.
// Runs at 03:30 ET (after DB backup at 03:00).
Schedule::command("analytics:rollup")->dailyAt("03:30")->timezone("America/New_York")->name("analytics-rollup")->onOneServer();

// MIDWEEK_CAPTION_RETRIES (2026-06-14) — YouTube auto-captions sometimes
// land days after the Sabbath stream ends. Cache TTL gives up at 4 days,
// so Wednesday is the last realistic window to catch a late-arriving caption.
// Two attempts spread across the day in case the morning probe is too early.
Schedule::command('peace:scan-channel')
    ->wednesdays()->at('07:00')
    ->timezone('America/New_York')
    ->onOneServer()
    ->emailOutputOnFailure('contact@c-wellpics.com')
    ->name('peace-scan-channel-wed-7am');

// WHISPER_FALLBACK (added 2026-06-14) — Wednesday 6 PM ET, fires only if no
// sermon has been processed for the current week. Last-ditch fallback when
// YouTube never generates captions for the Sabbath stream. Auto-picks the
// most recent caption-less video, downloads + chunks + Whisper-transcribes
// + invokes peace:process. Cost ~$1.10 per 3-hour service.
Schedule::command('peace:whisper-transcribe')
    ->wednesdays()->at('18:00')
    ->timezone('America/New_York')
    ->onOneServer()
    ->emailOutputOnFailure('contact@c-wellpics.com')
    ->name('peace-whisper-wed-6pm');

// SPAM_AUTO_SWEEP (added 2026-06-18) — daily 04:15 ET, soft-deletes obvious
// contact-form spam (conservative: contact messages only, never prayers,
// recoverable for 30 days, then hard-pruned). Logs to audit_log.
Schedule::command('messages:sweep-spam --prune-days=30')
    ->dailyAt('04:15')
    ->timezone('America/New_York')
    ->name('messages-sweep-spam')
    ->onOneServer();

// SELF_UPDATE (2026-06-24) — "claudeless" weekly security maintenance.
// Sunday 5 AM ET. --security-only: does nothing unless composer audit finds an
// advisory; when it does, it checkpoints (git+lock+DB), updates within-major,
// migrates, health-checks, and auto-rolls-back on any regression. Emails result.
Schedule::command("system:self-update --security-only")
    ->sundays()->at("05:00")
    ->timezone("America/New_York")
    ->name("system-self-update")
    ->onOneServer();

Schedule::command("system:check-updates")->dailyAt("05:30");

Schedule::command("followups:process")->dailyAt("10:00")->timezone("America/New_York");
