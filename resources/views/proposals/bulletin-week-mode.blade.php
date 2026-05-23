<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="shortcut icon" href="/favicon.ico">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Proposal: Bulletin week-mode — Shalom</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Cormorant Garamond', serif; min-height: 100dvh; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 1100px; margin: 0 auto; padding: clamp(36px, 7vh, 64px) clamp(20px, 5vw, 32px) 80px; }

  .eyebrow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.32em; text-transform: uppercase;
    color: var(--brass);
    margin-bottom: 16px;
  }
  h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(36px, 5vw, 52px); font-weight: 500;
    letter-spacing: -0.025em; line-height: 1.05;
    color: var(--ink); margin-bottom: 18px;
  }
  .lede {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(19px, 2vw, 22px); line-height: 1.55;
    color: var(--ink-soft); max-width: 760px;
    margin-bottom: 36px;
  }

  .proposal-card {
    background: #fff; border: 1px solid var(--line);
    border-radius: 12px; padding: 28px 32px;
    margin-bottom: 28px;
  }
  .proposal-card h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px; font-weight: 500;
    color: var(--teal-dark); margin-bottom: 12px;
  }
  .proposal-card p {
    font-size: 17px; line-height: 1.6; color: var(--ink);
    margin-bottom: 12px;
  }
  .proposal-card ul {
    list-style: none; margin: 8px 0 8px 0; padding: 0;
  }
  .proposal-card ul li {
    font-size: 17px; line-height: 1.6; color: var(--ink);
    padding: 4px 0 4px 20px; position: relative;
  }
  .proposal-card ul li::before {
    content: '→'; position: absolute; left: 0; color: var(--teal); font-weight: bold;
  }

  .compare {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 32px 0;
  }
  @media (max-width: 800px) { .compare { grid-template-columns: 1fr; } }

  .compare-side h3 {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--ink-soft); margin-bottom: 12px;
  }
  .compare-side h3 .badge {
    display: inline-block; margin-left: 8px; padding: 2px 8px;
    border-radius: 999px; font-size: 9px; font-weight: 700;
    background: color-mix(in srgb, var(--brass) 15%, transparent); color: var(--brass);
    letter-spacing: 0.18em;
  }
  .compare-side h3.proposed .badge { background: color-mix(in srgb, var(--teal) 15%, transparent); color: var(--teal); }
  .frame-wrap {
    border: 1px solid var(--line);
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    height: 720px;
    position: relative;
  }
  .frame-wrap iframe {
    width: 100%; height: 100%;
    border: 0;
    transform-origin: top left;
  }
  .frame-label {
    position: absolute; top: 8px; right: 8px;
    background: color-mix(in srgb, var(--ink) 85%, transparent); color: #fff;
    font-family: 'JetBrains Mono', monospace;
    font-size: 9px; letter-spacing: 0.12em;
    padding: 4px 8px; border-radius: 4px;
    pointer-events: none; z-index: 5;
  }

  .decision {
    background: var(--ink); color: #fff;
    padding: 36px 40px;
    border-radius: 14px;
    margin-top: 48px;
  }
  .decision h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px; color: #fff; margin-bottom: 14px;
  }
  .decision p {
    font-size: 17px; line-height: 1.55; color: rgba(255,255,255,0.85);
    margin-bottom: 22px; max-width: 640px;
  }
  .vote {
    display: flex; gap: 12px; flex-wrap: wrap;
  }
  .vote button, .vote a {
    padding: 14px 24px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.20);
    border-radius: 8px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.20em; text-transform: uppercase;
    color: #fff; cursor: pointer;
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s, transform 0.1s;
  }
  .vote button:hover, .vote a:hover { background: rgba(255,255,255,0.14); border-color: rgba(255,255,255,0.40); }
  .vote button.yes { background: #2d8659; border-color: #2d8659; }
  .vote button.yes:hover { background: #1f6843; border-color: #1f6843; }
  .vote button.no { background: rgba(192,57,43,0.15); border-color: rgba(192,57,43,0.40); }
  .vote button.no:hover { background: rgba(192,57,43,0.30); }

  .vote-status {
    margin-top: 22px;
    padding: 14px 18px;
    background: rgba(255,255,255,0.08);
    border-radius: 8px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 13px;
    color: #fff;
    display: none;
  }
  .vote-status.show { display: block; }

  .meta-info {
    margin-top: 36px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.10em; color: var(--ink-soft); opacity: 0.6;
  }
  .meta-info strong { color: var(--ink); }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<header class="top">
  <a href="{{ url('/welcome') }}">← Bulletin</a>
  <span class="meta">proposal · bulletin week-mode</span>
</header>

<main>
  <div class="eyebrow">Proposal · for review by Andr&eacute;</div>
  <h1>Hide the bulletin during the week.</h1>
  <p class="lede">
    Right now the order of service is visible to the public seven days a week. A Tuesday visitor sees Sabbath's hymn list, scripture, and sermon plan — content meant for the moment of worship, not for a weekday browse. The proposal: subtract it.
  </p>

  <div class="proposal-card">
    <h2>What changes</h2>
    <p>For public visitors on <code>/welcome</code> Monday through Friday:</p>
    <ul>
      <li>The order of service section is hidden</li>
      <li>The announcements section stays visible</li>
      <li>A small "Worship at 11&nbsp;AM this Sabbath" reminder appears at top</li>
    </ul>
    <p>On Sabbath morning the full bulletin reappears automatically.</p>
    <p style="color: var(--brass); font-weight: 500; margin-top: 16px;">
      Karlon, Andr&eacute;, and Rosharde always see &amp; edit the full bulletin when signed in &mdash; this only changes what guests see.
    </p>
  </div>

  <div class="proposal-card">
    <h2>The escape hatches (this is the part Karlon flagged)</h2>
    <p>
      We don't want this auto-hide to fight us during Revival, Week of Prayer,
      VBS, camp meeting, or any week where each night has its own bulletin.
      Three guardrails &mdash; all automatic except the third:
    </p>
    <ul>
      <li><strong>Event-kind bulletins always show.</strong> If <code>kind = 'event'</code>, week-mode never hides it. Revival nights, prayer week, VBS &mdash; full bulletin visible every day.</li>
      <li><strong>Active event window overrides.</strong> If today falls inside any bulletin's <code>[service_date &hellip; event_ends_at]</code> range, that bulletin renders fully regardless of weekday.</li>
      <li><strong>Manual toggle.</strong> A new "Always show during the week" checkbox in the bulletin editor for the rare case where automatic detection misses (e.g., a special camp-meeting week we forgot to flag as an event).</li>
    </ul>
    <p style="color: var(--brass); font-weight: 500; margin-top: 14px;">
      The default Sabbath-only rule fires only when no event is active and the bulletin's kind is a regular service.
    </p>
  </div>

  <div class="proposal-card">
    <h2>Why</h2>
    <p>
      Steve Jobs's question: <em>does the user need this?</em> A visitor on Tuesday browsing the church site doesn't need the order of service &mdash; they need to know <strong>when service is</strong> and <strong>what's happening this week</strong>. The order of service is for the moment of worship; outside that moment it's noise.
    </p>
    <p>
      Subtracting it during the week means a calmer page Mon&ndash;Fri and a focused, present-tense bulletin Sabbath morning. The announcements (which evolve through the week) stay where guests can find them.
    </p>
  </div>

  <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: var(--ink); margin: 40px 0 16px;">See it live.</h2>
  <p style="font-size: 16px; color: var(--ink-soft); margin-bottom: 16px;">
    Both panes below are real renders of the actual bulletin page. Scroll inside each to compare. Left = today. Right = the proposal.
  </p>

  <div class="compare">
    <div class="compare-side">
      <h3>Now <span class="badge">CURRENT</span></h3>
      <div class="frame-wrap">
        <span class="frame-label">/welcome</span>
        <iframe src="/welcome?preview=1" title="Current bulletin (full order of service visible to guests)"></iframe>
      </div>
    </div>
    <div class="compare-side">
      <h3 class="proposed">After <span class="badge">PROPOSED</span></h3>
      <div class="frame-wrap">
        <span class="frame-label">/welcome (Mon-Fri)</span>
        <iframe src="/welcome?preview=1&preview-week=1" title="Proposed week-mode (announcements only, plus Sabbath reminder)"></iframe>
      </div>
    </div>
  </div>

  <div class="decision">
    <h2>Your call.</h2>
    <p>
      Tap a button. We'll see the result on our end and won't ship anything until we hear back. If you want to talk it through instead, "Discuss" pings Karlon and we'll hop on a call.
    </p>
    <div class="vote">
      <button type="button" class="yes" data-vote="yes">✓ Ship it</button>
      <button type="button" class="no" data-vote="no">✗ Don't ship</button>
      <button type="button" data-vote="discuss">Let's discuss</button>
    </div>
    <div class="vote-status" id="vote-status">Thanks &mdash; recorded. Karlon will see this.</div>
  </div>

  <div class="meta-info">
    <strong>Proposal:</strong> bulletin-week-mode &middot;
    <strong>Created:</strong> {{ now()->format('M j, Y · g:i A') }} &middot;
    <strong>By:</strong> Karlon (with Claude pairing)
  </div>
</main>

<script>
  // Vote handler — POSTs to a small endpoint that audit-logs the response
  document.querySelectorAll('.vote button').forEach(btn => {
    btn.addEventListener('click', async () => {
      const vote = btn.dataset.vote;
      const status = document.getElementById('vote-status');
      try {
        await fetch(@json(route('proposals.bulletin-week-mode.vote')), {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ vote }),
        });
        status.textContent = vote === 'yes'
          ? '✓ Got it — Karlon will see this. We\'ll ship the change.'
          : vote === 'no'
          ? '✗ Got it — we won\'t ship. Karlon will reach out if he wants to dig in.'
          : 'Got it — Karlon will be in touch to talk it through.';
        status.classList.add('show');
        document.querySelectorAll('.vote button').forEach(b => { b.disabled = true; b.style.opacity = 0.4; });
      } catch (e) {
        status.textContent = 'Network glitch — please try again or text Karlon directly.';
        status.classList.add('show');
      }
    });
  });
</script>

</body>
</html>
