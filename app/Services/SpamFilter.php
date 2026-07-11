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
        // marketing-solicitation family (added 2026-07-11 — "Karry Mackay"
        // free-traffic-booster slipped through: 1 URL + 34 words was legal)
        'traffic booster', 'boost traffic', 'traffic to your',
        'promote your site', 'promote your website', 'promote your business',
        'classified sites', 'exposure tools', 'free exposure',
        'noticed your business', 'noticed your website', 'noticed your site',
        'came across your site', 'came across your website',
        'seo services', 'seo package', 'digital marketing', 'marketing proposal',
        'more clients', 'more leads', 'grow your business',
        'web design services', 'redesign your website',
        // pharma
        'viagra', 'cialis', 'pharmacy online',
        // casino
        'online casino', 'free spins', 'slot machine',
        // greeting-then-url pattern (#1)
        'hi admin', 'hello admin', 'dear admin', 'dear webmaster',
        // phishing / account-scare (added 2026-06-18 after "Steerwors" slipped
        // through — "account inactive 364 days, claim your funds, withdraw in 24h")
        'account has been inactive', 'inactive for', 'avoid deletion',
        'claim your funds', 'claim your reward', 'request a withdrawal',
        'within 24 hours', 'within 48 hours', 'verify your account',
        'suspended', 'unusual activity', 'confirm your identity',
        'you have won', 'you have been selected', 'gift card',
    ];

    /**
     * URL-shortener hosts. Legitimate church contacts virtually never send a
     * shortened link — they're the calling card of phishing/spam that wants to
     * hide the destination. A single shortener link is treated as spam outright.
     */
    private const LINK_SHORTENERS = [
        'tinyurl.com', 'bit.ly', 'goo.gl', 'ow.ly', 't.co', 'is.gd',
        'buff.ly', 'rebrand.ly', 'cutt.ly', 'shorturl', 'rb.gy',
        'tiny.cc', 'lnkd.in', 'snip.ly', 'shorte.st', 'adf.ly',
    ];

    /**
     * Free-hosting platforms. Real correspondents link to real domains;
     * throwaway spam landers live on these (boost-traffic.netlify.app, ...).
     * Any URL on one of them = reject.
     */
    private const FREE_HOSTS = [
        'netlify.app', 'vercel.app', 'pages.dev', 'web.app', 'firebaseapp.com',
        'herokuapp.com', 'github.io', 'glitch.me', 'repl.co', 'surge.sh',
        'weebly.com', 'wixsite.com', 'blogspot.', 'neocities.org', '000webhost',
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

        // ── 1b. Link shortener anywhere = hard spam flag. Real visitors don't
        //       hide their destination behind tinyurl/bit.ly/etc.
        foreach (self::LINK_SHORTENERS as $host) {
            if (str_contains($combinedLower, $host)) {
                return 'link-shortener:' . $host;
            }
        }

        // ── 1c. Any URL on a throwaway free-host platform ⇒ spam lander.
        if (preg_match('~https?://[^\s)>]*~i', $combined, $mm)) {
            foreach (self::FREE_HOSTS as $host) {
                if (stripos($combined, $host) !== false) {
                    return 'free-host-url:' . $host;
                }
            }
        }

        // ── 1d. Our own domain inside a FOREIGN url's path — the calling card
        //       of "personalized" mass spam (boost-x.example/thechurchofpeace.org).
        if (preg_match('~https?://(?![^/\s]*thechurchofpeace\.org)[^\s)>]+thechurchofpeace\.org~i', $combined)) {
            return 'own-domain-in-foreign-url';
        }

        // ── 1e. Marketing opener + any link = solicitation, not a parishioner.
        if (preg_match('~https?://~i', $combined)
            && (preg_match('/\b(noticed|came across|found|stumbled upon) your (business|website|site|page|church online)\b/i', $body)
                || preg_match('/^\s*quick (note|question)\b/i', $body))) {
            return 'marketing-opener-with-url';
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
            if ($wordCount < 50) {   // raised 30→50 (2026-07-11) — spam grew wordier
                return "url-with-too-few-words.$wordCount";
            }
        }

        // ── 4. High-confidence spam phrase match
        foreach (self::SPAM_PHRASES as $phrase) {
            if (str_contains($combinedLower, $phrase)) {
                return 'phrase:' . preg_replace('/[^a-z0-9]+/i', '-', $phrase);
            }
        }

        // ── 4b. Bot-generator name signature (added 2026-07-04 after the
        //       "LandStormNederlandtef" AI-story spam — 2nd of its family).
        //       Real people put spaces in a name field; spam generators mash
        //       CamelCase words: LandStormNederlandtef, SteerworsKapiton, etc.
        //       ≥3 capital humps + no space + long ⇒ machine-made.
        if (preg_match('/^(?:[A-Z][a-z]+){3,}$/', $name) && strlen($name) >= 12) {
            return 'bot-name-camelcase';
        }
        //       The XRumer family's favorite suffix. Nobody at church is named ...tef.
        if (preg_match('/tef$/i', trim($name))) {
            return 'bot-name-tef-suffix';
        }

        // ── 4c. Placeholder email locals — a person reaching a church does
        //       not write from test@ / example@ (2026-07-04, same incident).
        if (preg_match('/^(test\d*|tester|testing|example|sample|noreply|no-reply|asdf+)@/i', $email)) {
            return 'placeholder-email';
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
