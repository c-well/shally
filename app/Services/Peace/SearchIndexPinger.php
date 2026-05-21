<?php
namespace App\Services\Peace;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Notifies search engines that a URL is new/updated. Called after a sermon
 * goes pending_review so Google + Bing can index within minutes instead of
 * waiting for their next sitemap crawl.
 *
 * Bing/Yandex/Seznam: IndexNow protocol (free, just a key file).
 *   https://www.indexnow.org/documentation
 *
 * Google: Indexing API. Requires a Google Cloud service account.
 *   https://developers.google.com/search/apis/indexing-api/v3/quickstart
 *   Service account JSON expected at ~/secrets/google-indexing.json (chmod 600).
 *   Until that file exists, Google ping is a no-op (logged).
 */
class SearchIndexPinger
{
    private const INDEXNOW_KEY_PATH       = '/home/shalom/secrets/indexnow-key.txt';
    private const GOOGLE_CREDS_PATH       = '/home/shalom/secrets/google-indexing.json';
    private const SITE_HOST               = 'thechurchofpeace.org';

    public function pingAll(string $url): array
    {
        return [
            'indexnow' => $this->pingIndexNow($url),
            'google'   => $this->pingGoogle($url),
        ];
    }

    public function pingIndexNow(string $url): array
    {
        if (! is_readable(self::INDEXNOW_KEY_PATH)) {
            return ['ok' => false, 'reason' => 'no indexnow key file'];
        }
        $key = trim((string) @file_get_contents(self::INDEXNOW_KEY_PATH));
        if ($key === '') return ['ok' => false, 'reason' => 'empty key'];

        try {
            $resp = Http::timeout(8)->post('https://api.indexnow.org/IndexNow', [
                'host'        => self::SITE_HOST,
                'key'         => $key,
                'keyLocation' => "https://" . self::SITE_HOST . "/{$key}.txt",
                'urlList'     => [$url],
            ]);
            return [
                'ok'     => $resp->successful() || $resp->status() === 202,
                'status' => $resp->status(),
                'body'   => mb_substr((string) $resp->body(), 0, 200),
            ];
        } catch (\Throwable $e) {
            Log::warning('IndexNow ping failed', ['url' => $url, 'err' => $e->getMessage()]);
            return ['ok' => false, 'reason' => $e->getMessage()];
        }
    }

    public function pingGoogle(string $url): array
    {
        if (! is_readable(self::GOOGLE_CREDS_PATH)) {
            return ['ok' => false, 'reason' => 'no google service-account creds yet'];
        }

        try {
            $token = $this->googleAccessToken();
            if (! $token) return ['ok' => false, 'reason' => 'could not mint google access token'];

            $resp = Http::withToken($token)
                ->timeout(8)
                ->post('https://indexing.googleapis.com/v3/urlNotifications:publish', [
                    'url'  => $url,
                    'type' => 'URL_UPDATED',
                ]);
            return [
                'ok'     => $resp->successful(),
                'status' => $resp->status(),
                'body'   => mb_substr((string) $resp->body(), 0, 200),
            ];
        } catch (\Throwable $e) {
            Log::warning('Google Indexing API ping failed', ['url' => $url, 'err' => $e->getMessage()]);
            return ['ok' => false, 'reason' => $e->getMessage()];
        }
    }

    /**
     * Mint an OAuth access token from the service account JSON (RS256 JWT → token endpoint).
     * Cached in apc/file for 50 minutes since the token's good for 60.
     */
    private function googleAccessToken(): ?string
    {
        $cacheFile = '/home/shalom/tmp/peace-work/google-indexing.token';
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < 3000) {
            $cached = @file_get_contents($cacheFile);
            if ($cached) return trim($cached);
        }

        $creds = json_decode((string) @file_get_contents(self::GOOGLE_CREDS_PATH), true);
        if (!is_array($creds) || empty($creds['client_email']) || empty($creds['private_key'])) {
            return null;
        }
        $now    = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss'   => $creds['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];
        $b64 = fn($d) => rtrim(strtr(base64_encode(json_encode($d)), '+/', '-_'), '=');
        $signingInput = $b64($header) . '.' . $b64($claims);
        $sig = '';
        openssl_sign($signingInput, $sig, $creds['private_key'], 'sha256WithRSAEncryption');
        $jwt = $signingInput . '.' . rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

        $resp = Http::asForm()->timeout(8)->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);
        if (!$resp->successful()) return null;
        $token = $resp->json('access_token');
        if ($token) {
            @file_put_contents($cacheFile, $token);
            @chmod($cacheFile, 0600);
        }
        return $token ?: null;
    }
}
