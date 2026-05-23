<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@include('partials.seo-head', [
  'title'       => 'Privacy — The Church of Peace',
  'description' => 'How Shalom SDA Church handles your data: emails for sign-in, anonymized telemetry, no third-party trackers. Plain English.',
  'path'        => '/privacy',
])
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Cormorant Garamond', serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 720px; margin: 0 auto; padding: clamp(36px, 7vh, 64px) clamp(20px, 5vw, 32px) 80px; }
  .head { text-align: center; margin-bottom: 36px; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px, 5vw, 52px); font-weight: 500; line-height: 1.05; letter-spacing: -0.025em; color: var(--ink); }
  .updated { margin-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--brass); }

  .prose { font-size: 18px; line-height: 1.7; color: var(--ink); }
  .prose p { margin-bottom: 1.2em; }
  .prose h2 { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 500; color: var(--teal); margin: 1.8em 0 0.5em; letter-spacing: -0.01em; }
  .prose ul { margin: 0 0 1.2em 1.2em; }
  .prose li { margin-bottom: 0.5em; }
  .prose strong { font-weight: 600; }
  .prose a { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }
  .prose code { font-family: 'JetBrains Mono', monospace; font-size: 0.9em; background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 3px; }
  html.dark .prose code { background: rgba(255,255,255,0.06); }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="head">
    <div class="eyebrow">Plain English</div>
    <h1>How we handle your data.</h1>
    <div class="updated">Last updated: {{ now()->setTimezone('America/New_York')->format('F j, Y') }}</div>
  </div>

  <div class="prose">

    <p>This site is run by Shalom Seventh-day Adventist Church / The Church of Peace. We're a small congregation in the Bronx. We collect the minimum data we need to keep the site working and to know what's helping the community. No tracking pixels. No analytics vendors. No data sold or shared.</p>

    <h2>What we collect</h2>
    <ul>
      <li><strong>Email address</strong> — only if you sign in (admins, clerks). We use it to send you a magic-link login. It's stored in our database and never shared.</li>
      <li><strong>Hashed IP address</strong> — for traffic analytics. We hash your IP with a one-way SHA-256 function and our app key, so the original IP is never stored. We see "this hashed visitor came back twice" but never "12.34.56.78 visited."</li>
      <li><strong>Session cookie</strong> — a standard encrypted cookie that keeps you signed in if you're an admin. Expires after 30 days of inactivity.</li>
      <li><strong>Page view path + timestamp</strong> — which pages get visited, when. Anonymized via the hashed IP above.</li>
      <li><strong>Browser + device info</strong> — your User-Agent string (e.g. "iPhone, Safari"). We bucket it as device-class only ("mobile" / "desktop"). We don't fingerprint.</li>
      <li><strong>Country</strong> — derived from Cloudflare's `CF-IPCountry` header when available. We see "visitor from US" but not city/region.</li>
      <li><strong>Form submissions</strong> — if you submit a feedback ticket or contact form, we keep what you wrote and your name/email if you provided them.</li>
    </ul>

    <h2>What we don't collect</h2>
    <ul>
      <li>No Google Analytics. No Meta pixel. No third-party trackers of any kind.</li>
      <li>No precise location.</li>
      <li>No payment info — donations go through Adventist Giving on their own platform.</li>
      <li>No video/audio recording. No microphone or camera access.</li>
    </ul>

    <h2>How long we keep it</h2>
    <ul>
      <li><strong>Page-view telemetry</strong> — 90 days, then auto-deleted by a daily cron job.</li>
      <li><strong>Audit log</strong> — 40 days for admin sign-in / publish / edit events.</li>
      <li><strong>Feedback tickets</strong> — kept until closed, then 14 days.</li>
      <li><strong>Database backups</strong> — last 14 nightly dumps, plus one off-site snapshot.</li>
      <li><strong>Email accounts</strong> — kept while you're an admin / clerk; deleted on request.</li>
    </ul>

    <h2>Cookies we set</h2>
    <ul>
      <li><code>church-of-peace-session</code> — your encrypted login session (admins only). HttpOnly, Secure, SameSite=Lax.</li>
      <li><code>XSRF-TOKEN</code> — security token for form submissions. Standard Laravel.</li>
      <li><code>cop_telemetry_session</code> — random token (not tied to identity) to count "this is a returning visitor." Resets after 30 days.</li>
    </ul>
    <p>These are functional cookies needed for the site to work and stay secure. We don't set marketing or analytics cookies.</p>

    <h2>Your rights</h2>
    <p>You can ask us to:</p>
    <ul>
      <li>Tell you what data we have on you (we'll send a copy).</li>
      <li>Delete it (we'll wipe your account, page-view records, and form submissions).</li>
      <li>Correct anything that's wrong.</li>
    </ul>
    <p>Email <a href="mailto:contact@thechurchofpeace.org">contact@thechurchofpeace.org</a> for any of those. We aim to respond within 7 days.</p>

    <h2>Children</h2>
    <p>The site isn't directed at children under 13. If a child signs up by mistake, email us and we'll delete the record.</p>

    <h2>Changes</h2>
    <p>If we change this policy, we'll update the date at the top. For material changes (new data we collect, new third-party recipient), we'll post a banner on the home page for 30 days.</p>

    <h2>Contact</h2>
    <p>
      Shalom Seventh-day Adventist Church<br>
      3323 White Plains Rd, Bronx, NY 10467<br>
      <a href="mailto:contact@thechurchofpeace.org">contact@thechurchofpeace.org</a>
    </p>

  </div>
</main>

</body>
</html>
