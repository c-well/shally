<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Sends web-push to every subscribed clerk device. Payload carries title/body/
 * url/badge-count; the service worker shows the banner and stamps the icon.
 * Dead endpoints (410/404) are pruned automatically.
 */
class PushService
{
    public function configured(): bool
    {
        return (bool) (config('services.vapid.public') && config('services.vapid.private'));
    }

    public function toClerks(string $title, string $body, string $url = '/admin'): int
    {
        if (! $this->configured()) return 0;
        $subs = DB::table('push_subscriptions')->get();
        if ($subs->isEmpty()) return 0;

        $badge = \App\Models\ContactMessage::whereNull('read_at')->count()
               + \App\Models\PrayerRequest::whereNull('read_at')->count();

        $webPush = new WebPush(['VAPID' => [
            'subject'    => config('services.vapid.subject'),
            'publicKey'  => config('services.vapid.public'),
            'privateKey' => config('services.vapid.private'),
        ]]);
        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url, 'badge' => $badge]);

        foreach ($subs as $s) {
            $webPush->queueNotification(Subscription::create([
                'endpoint' => $s->endpoint,
                'keys' => ['p256dh' => $s->p256dh, 'auth' => $s->auth],
            ]), $payload);
        }
        $sent = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) { $sent++; continue; }
            if (in_array(optional($report->getResponse())->getStatusCode(), [404, 410], true)) {
                DB::table('push_subscriptions')->where('endpoint_hash', hash('sha256', $report->getEndpoint()))->delete();
            } else {
                Log::warning('push failed', ['reason' => $report->getReason()]);
            }
        }
        return $sent;
    }
}
