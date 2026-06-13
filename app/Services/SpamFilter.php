<?php
namespace App\Services;

/**
 * SpamFilter — content-level checks layered on top of honeypot + timing.
 *
 * Added 2026-06-11 after three real spam submissions slipped through the
 * existing honeypot + time-token + min-length + english-word defenses:
 *
 *   #1  Hi <http://thechurchofpeace.org/fekal0911> Admin    — URL injected
 *                                                            into name field
 *   #2  "Stop wasting budget on clicks..." (Wealth Filter)  — marketing scam
 *   #3  "Withdraw Your 1.3426 BTC and Reinvent <url>"       — crypto scam
 *
 * Three new layers:
 *   - URL in NAME / SUBJECT field  → reject (catches #1)
 *   - 2+ URLs in body              → reject (catches #2 #3 and most link spam)
 *   - High-confidence spam phrases → reject (catches #2 #3 directly)
 *
 * Plus a fourth optional layer enforced at the controller level: a JS-set
 * nonce that pure-bot submissions fail because they don't run JavaScript.
 *
 * Returns a string rejection reason (for logging) or null when content
 * passes. All checks are conservative — false-positive rate should be very
 * low on legitimate church traffic, which doesn't generally include URLs.
 */
class SpamFilter
{
    /**
     * Phrases that virtually never appear in legitimate church-contact
     * traffic but are very common in modern scrape-and-fill spam. Kept
     * lowercase; matches are case-insensitive.
     */
    private const SPAM_PHRASES = [
        // crypto / financial scams
        'withdraw your btc', 'withdraw your bitcoin', 'wallet.dat',
        'crypto profit', 'crypto investment', 'forex trading',
        'binary options', 'reinvent', '0.5 btc', '1.3 btc', '$5000 daily',
        // marketing / lead-gen spam
        'wealth filter', 'low-income traffic', 'lead list',
        'sign for big jobs', 'premium services', 'flat rate',
        'wasting budget on clicks', 'mass email', 'bulk email',
        // SEO / backlink spam
        'guest post', 'guest posting', 'backlinks', 'link building',
        'high da', 'domain authority', 'pbn', 'web 2.0',
        'rank in google', 'first page of google', 'serp',
        // generic spam tells
        'increase your sales', 'boost your traffic', 'work from home',
        'make money online', '$100 per day', 'passive income',
        'click here to claim', 'claim your prize',
        // pharma
        'viagra', 'cialis', 'pharmacy online',
        // casino
        'online casino', 'free spins', 'slot machine',
        // greeting-then-url pattern (#1)
        'hi admin', 'hello admin', 'dear admin', 'dear webmaster',
    ];

    /**
     * Detect spam in the submitted content. Returns reason string for
     * the controller to log, or null when the content is acceptable.
     */
    public function detect(?string $name, ?string $email, string $body): ?string
    {
        $name  = (string) $name;
        $email = (string) $email;
        $combined = $name . ' ' . $email . ' ' . $body;
        $combinedLower = mb_strtolower($combined);

        // ── 1. URL in name field (real names don't contain http://)
        if (preg_match('~https?://|www\.~i', $name)) {
            return 'url-in-name';
        }

        // ── 2. URL count in body — legitimate church-contact traffic
        //      almost never contains MORE than one link.
        $urlCount = preg_match_all('~https?://[^\s)>]+~i', $body, $m);
        if ($urlCount >= 2) {
            return "urls-in-body.$urlCount";
        }

        // ── 3. Single URL allowed only if the message also has at least
        //      30 words of plausible English context (not just a one-liner
        //      "Hi here is my link" pattern)
        if ($urlCount === 1) {
            $wordCount = str_word_count($body);
            if ($wordCount < 30) {
                return "url-with-too-few-words.$wordCount";
            }
        }

        // ── 4. High-confidence spam phrase match
        foreach (self::SPAM_PHRASES as $phrase) {
            if (str_contains($combinedLower, $phrase)) {
                return 'phrase:' . preg_replace('/[^a-z0-9]+/i', '-', $phrase);
            }
        }

        // ── 5. Email pattern: pure-digit-suffix random handle ("jpmg130390@")
        //      is a common spam-generator pattern. We accept it on its own
        //      but flag if combined with one of the other warning signs.
        //      (Conservative — single-signal is allowed because legit
        //      users sometimes have number-heavy email handles.)
        if ($email !== '' && preg_match('/^[a-z]{3,8}\d{4,}@/i', $email)) {
            // Only reject if message ALSO contains "Hi" / "Hello" greeting
            // and is very short — the "drive-by greet + see-you-later" spam.
            if (preg_match('/^\s*(hi|hello|hey)\b/i', $body) && str_word_count($body) < 40) {
                return 'random-email-with-greet';
            }
        }

        return null;
    }
}
