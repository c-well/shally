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

        $b->update([
            'body' => null,
            'pdf_path' => null,
            'published_snapshot' => null,
        ]);
        $cleaned++;
    }

    $this->info("Pruned heavy content from {$cleaned} bulletin(s) — lines and announcements retained.");
})->purpose('Clear body/pdf/snapshot for bulletins more than 24h past their service_date (keep lines + announcements)');

Schedule::command('flyers:prune')->dailyAt('03:15');
Schedule::command('flyers:warn')->dailyAt('03:00');
Schedule::command('bulletins:prune')->dailyAt('03:30');
Schedule::command('bulletins:purge-old')->dailyAt('03:45');  // hard-delete 14+ day old bulletins (PurgeOldBulletins)
Schedule::command('feedback:purge-old')->dailyAt('03:50');   // hard-delete closed tickets > 14 days (PurgeOldTickets)
Schedule::command('audit:purge-old')->dailyAt('03:55');      // hard-delete audit log entries > 40 days (PurgeOldAuditLogs)
Schedule::command('bulletins:clear-stale-previous-snapshots')->hourly();  // reclaim previous_published_* cols 8+h after supersession

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
