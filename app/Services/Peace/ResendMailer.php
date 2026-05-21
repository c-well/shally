<?php
namespace App\Services\Peace;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin client for Resend.com's email API — used for the seeker-facing
 * channel (welcome / magic link / newsletter). Admin emails continue
 * using the local sendmail mailer.
 *
 * Single send method. View name and view data are how we keep the
 * templates colocated with the rest of the Blade views.
 */
class ResendMailer
{
    public function sendView(string $to, string $subject, string $view, array $viewData = [], array $opts = []): bool
    {
        $apiKey = config('services.resend.key') ?: env('RESEND_API_KEY');
        if (!$apiKey) {
            Log::warning('ResendMailer: no API key set — skipping send', ['to' => $to, 'subject' => $subject]);
            return false;
        }

        try {
            $html = view($view, $viewData)->render();
        } catch (\Throwable $e) {
            Log::error('ResendMailer: view render failed', ['view' => $view, 'err' => $e->getMessage()]);
            return false;
        }

        $payload = [
            'from'     => env('RESEND_FROM_NAME', 'Finding Peace') . ' <' . env('RESEND_FROM', 'hello@thechurchofpeace.org') . '>',
            'to'       => [$to],
            'subject'  => $subject,
            'html'     => $html,
            'reply_to' => env('RESEND_REPLY_TO', 'app@thechurchofpeace.org'),
        ];
        foreach (['tags', 'headers'] as $k) {
            if (!empty($opts[$k])) $payload[$k] = $opts[$k];
        }

        try {
            $resp = Http::withToken($apiKey)
                ->timeout(15)
                ->acceptJson()
                ->post('https://api.resend.com/emails', $payload);

            if ($resp->successful()) {
                Log::info('ResendMailer: sent', [
                    'to' => $to, 'subject' => $subject,
                    'resend_id' => $resp->json('id'),
                ]);
                return true;
            }
            Log::warning('ResendMailer: API rejected send', [
                'to' => $to, 'status' => $resp->status(), 'body' => substr((string) $resp->body(), 0, 500),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('ResendMailer: HTTP error', ['to' => $to, 'err' => $e->getMessage()]);
            return false;
        }
    }
}
