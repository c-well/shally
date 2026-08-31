<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Console\Events\ScheduledTaskFailed;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Failed as AuthFailed;
use Illuminate\Auth\Events\Login as AuthLogin;
use Illuminate\Auth\Events\Logout as AuthLogout;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SCHEDULED_TASK_FAILURES (2026-08-31, Karlon): every scheduled task
        // used to fail silently. peace:scan-channel sat broken for weeks that
        // way -- it exited non-zero every run and nothing anywhere noticed.
        //
        // Five tasks carried emailOutputOnFailure; the other thirty-odd carried
        // nothing. Rather than bolt a hook onto each line, listen once for the
        // event Laravel already fires for all of them, and keep a rolling record
        // the hub can read. This is the last rung: the pipeline repairs what it
        // can on its own first, so anything landing here genuinely wants a
        // person to look at it.
        Event::listen(function (ScheduledTaskFailed $event) {
            try {
                $name = $event->task->command
                    ? trim(str_replace([PHP_BINARY, "'"], '', $event->task->command))
                    : ($event->task->description ?: 'closure');

                // Keep the command itself, not the full php binary path.
                if (preg_match('/artisan\s+(\S+)/', $name, $m)) $name = $m[1];

                $log = json_decode(AppSetting::get('scheduled_failures_json') ?? '[]', true);
                if (! is_array($log)) $log = [];

                array_unshift($log, [
                    'task'    => mb_substr($name, 0, 120),
                    'at'      => now()->toDateTimeString(),
                    'message' => mb_substr((string) ($event->exception->getMessage() ?? ''), 0, 300),
                ]);

                // Rolling window -- enough to spot a pattern, not enough to bloat.
                AppSetting::set('scheduled_failures_json', json_encode(array_slice($log, 0, 40)));
            } catch (\Throwable $ignored) {
                // Recording a failure must never itself become a failure.
            }
        });

        // CMS-stored page HTML may contain text arrows typed by editors (→ ↗ ←).
        // Swap them for the site icon partials at render time so DB content stays
        // portable and the one-file arrow restyle (partials/_ar*) covers everything.
        \Illuminate\Support\Str::macro('arrowize', function (?string $html): string {
            if ($html === null || $html === '') return '';
            static $ar = null, $arup = null, $arl = null;
            $ar   ??= trim(view('partials._ar')->render());
            $arup ??= trim(view('partials._arup')->render());
            $arl  ??= trim(view('partials._arl')->render());
            return str_replace(["→", "↗", "←"], [$ar, $arup, $arl], $html);
        });

        Event::listen(MessageSending::class, \App\Listeners\AddGlobalCcToMessages::class);

        // ─── Audit log: capture every auth event. We swallow exceptions so
        // an audit-write failure never blocks the underlying flow.

        Event::listen(AuthLogin::class, function (AuthLogin $e) {
            try {
                AuditLog::record(
                    event: 'login_success',
                    userId: $e->user->getAuthIdentifier(),
                    description: ($e->user->name ?? '?') . ' signed in (guard: ' . $e->guard . ')',
                    meta: ['guard' => $e->guard, 'remember' => (bool) $e->remember],
                );
            } catch (\Throwable $ex) { \Log::warning('audit:login_success failed: ' . $ex->getMessage()); }
        });

        Event::listen(AuthFailed::class, function (AuthFailed $e) {
            try {
                AuditLog::record(
                    event: 'login_failed',
                    userId: $e->user?->getAuthIdentifier(),
                    description: 'Failed sign-in for: ' . ($e->credentials['email'] ?? 'unknown'),
                    meta: ['guard' => $e->guard, 'email' => $e->credentials['email'] ?? null],
                );
            } catch (\Throwable $ex) { \Log::warning('audit:login_failed failed: ' . $ex->getMessage()); }
        });

        Event::listen(AuthLogout::class, function (AuthLogout $e) {
            try {
                AuditLog::record(
                    event: 'logout',
                    userId: $e->user?->getAuthIdentifier(),
                    description: ($e->user?->name ?? '?') . ' signed out',
                    meta: ['guard' => $e->guard],
                );
            } catch (\Throwable $ex) { \Log::warning('audit:logout failed: ' . $ex->getMessage()); }
        });
    }
}
