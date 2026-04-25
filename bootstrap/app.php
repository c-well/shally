<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'     => \App\Http\Middleware\EnsureRole::class,
            'honeypot' => \App\Http\Middleware\Honeypot::class,
        ]);
        // Append global security headers to every response
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Telemetry — log a row to page_views per public successful GET (skips admin/api/bots)
        $middleware->append(\App\Http\Middleware\TrackPageView::class);
        // Trust the cPanel reverse proxy for client-IP / scheme detection
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Audit log: record every reportable exception (5xx and notable failures) into audit_logs.
        // Wrapped in try/catch so the audit-write itself never escalates the original error.
        $exceptions->reportable(function (\Throwable $e) {
            try {
                $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                if ($code < 500 && !in_array($code, [403, 419, 429], true)) {
                    return; // skip routine 4xx noise (404s, validation, etc.)
                }
                \App\Models\AuditLog::record(
                    event: 'error_' . $code,
                    userId: auth()->id(),
                    description: get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 500),
                    meta: [
                        'code' => $code,
                        'file' => $e->getFile() . ':' . $e->getLine(),
                    ],
                );
            } catch (\Throwable $ignored) {} // never let audit logging cascade
        });
    })->create();
