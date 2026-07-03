<?php
namespace App\Services\Intake;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal Twilio SMS sender — no SDK, just the REST API.
 *
 * Stays dormant until TWILIO_SID / TWILIO_TOKEN / TWILIO_FROM are set in .env,
 * so the rest of the intake system works with or without texting configured.
 */
class TwilioNotifier
{
    public function configured(): bool
    {
        return config('services.twilio.sid')
            && config('services.twilio.token')
            && config('services.twilio.from');
    }

    public function send(string $to, string $body): bool
    {
        if (! $this->configured()) return false;

        $sid = config('services.twilio.sid');
        try {
            $resp = Http::asForm()
                ->timeout(15)
                ->withBasicAuth($sid, config('services.twilio.token'))
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To'   => $to,
                    'From' => config('services.twilio.from'),
                    'Body' => $body,
                ]);
            if (! $resp->successful()) {
                Log::warning('Twilio send failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            }
            return $resp->successful();
        } catch (\Throwable $e) {
            Log::warning('Twilio send threw', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
