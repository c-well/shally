<?php

namespace App\Providers;

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
