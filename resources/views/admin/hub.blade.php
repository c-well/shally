<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Admin — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 1080px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(48px, 7vw, 72px); font-weight: 500; letter-spacing: -0.035em; line-height: 1; color: var(--ink); }
  .lede { margin-top: 22px; font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 540px; }

  .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; margin-top: 56px; }
  .card {
    background: #fff; border: 1px solid var(--line); border-radius: 8px;
    padding: 26px 24px;
    display: flex; flex-direction: column; gap: 8px;
    text-decoration: none; color: var(--ink);
    transition: border-color 0.15s, transform 0.12s, box-shadow 0.15s;
  }
  .card:hover {
    border-color: var(--teal);
    transform: translateY(-2px);
    box-shadow: 0 12px 28px -16px rgba(3,97,122,0.4);
  }
  .card-eyebrow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; font-weight: 700; letter-spacing: 0.24em;
    text-transform: uppercase; color: var(--teal);
  }
  .card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px; font-weight: 500; letter-spacing: -0.01em;
    color: var(--ink); margin-top: 6px;
  }
  .card-sub {
    font-size: 13px; line-height: 1.5; color: var(--ink-soft);
    margin-top: 4px;
  }
  .card-arrow {
    margin-top: 14px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px;
    font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--teal);
  }
</style>
@include('admin.partials._typography')
</head>
<body>

@include('partials.site-menu')

<header class="top">
  <a href="/">← Back to bulletin</a>
  <span class="meta">admin · {{ auth()->user()->name }}</span>
</header>

<main>
  <h1>Admin.</h1>
  <p class="lede">Everything that runs the app — log of who's signed in, the people allowed to sign in, the names that appear on bulletin autocomplete, and the schedule.</p>

  <div class="grid">

    <a href="{{ route('admin.users') }}" class="card">
      <span class="card-eyebrow">People</span>
      <span class="card-title">Users.</span>
      <span class="card-sub">Add Andre, the elders, members. Set their PIN.</span>
      <span class="card-arrow">Manage →</span>
    </a>

    <a href="{{ route('admin.logs') }}" class="card">
      <span class="card-eyebrow">Activity</span>
      <span class="card-title">Audit log.</span>
      <span class="card-sub">Every sign-in, magic link, and error from the last 40 days.</span>
      <span class="card-arrow">View →</span>
    </a>

    <a href="{{ route('admin.names') }}" class="card">
      <span class="card-eyebrow">Cleanup</span>
      <span class="card-title">Bulletin names.</span>
      <span class="card-sub">Hide typos or names you don't want appearing in autocomplete.</span>
      <span class="card-arrow">Tidy →</span>
    </a>

    <a href="{{ route('schedule.index') }}" class="card">
      <span class="card-eyebrow">Departments</span>
      <span class="card-title">Schedule.</span>
      <span class="card-sub">Who's serving on Sabbath: ushers, deacons, music, platform.</span>
      <span class="card-arrow">Open →</span>
    </a>

    <a href="{{ route('admin.lessons') }}" class="card">
      <span class="card-eyebrow">Sabbath school</span>
      <span class="card-title">Lessons.</span>
      <span class="card-sub">Upload the quarterly PDF · update once per quarter.</span>
      <span class="card-arrow">Manage →</span>
    </a>

    <a href="{{ route('admin.sermons.index') }}" class="card">
      <span class="card-eyebrow">Peace Notes</span>
      <span class="card-title">Sermons.</span>
      <span class="card-sub">Upload audio sermons to the public archive · listen, edit, delete.</span>
      <span class="card-arrow">Manage →</span>
    </a>

    <a href="{{ route('admin.slides.index') }}" class="card">
      <span class="card-eyebrow">Home page</span>
      <span class="card-title">Hero slides.</span>
      <span class="card-sub">Photos that rotate on the home page · upload, reorder, retire.</span>
      <span class="card-arrow">Manage →</span>
    </a>

    <a href="{{ route('admin.media.index') }}" class="card">
      <span class="card-eyebrow">Library</span>
      <span class="card-title">Media pool.</span>
      <span class="card-sub">All uploaded images and audio in one place · pick, copy URL, delete.</span>
      <span class="card-arrow">Open →</span>
    </a>

    <a href="{{ route('admin.analytics') }}" class="card">
      <span class="card-eyebrow">Telemetry</span>
      <span class="card-title">Analytics.</span>
      <span class="card-sub">First-party page views, top paths, devices, referrers · privacy-first.</span>
      <span class="card-arrow">View →</span>
    </a>

    <a href="{{ route('admin.inbox') }}" class="card">
      <span class="card-eyebrow">Bug reports</span>
      <span class="card-title">Inbox.</span>
      <span class="card-sub">Read incoming bug reports + feedback. Close when handled.</span>
      <span class="card-arrow">Open →</span>
    </a>

  </div>

  {{-- ── Public pages section — preview & links ── --}}
  <h2 style="font-family:'Cormorant Garamond',serif;font-size:clamp(36px,5vw,52px);font-weight:500;letter-spacing:-0.02em;margin:88px 0 16px;color:var(--ink);">Site pages.</h2>
  <p class="lede" style="margin-bottom:36px;">The public face of the church. Click any to preview as a visitor would see it.</p>

  <div class="grid">
    <a href="{{ route('admin.pages.edit', 'landing') }}" class="card">
      <span class="card-eyebrow">Front door</span>
      <span class="card-title">Home / landing.</span>
      <span class="card-sub">Hero, schedule, this-week cards, latest sermon, donate band.</span>
      <span class="card-arrow">Preview ↗</span>
    </a>

    <a href="{{ route('admin.pages.edit', 'about') }}" class="card">
      <span class="card-eyebrow">Our story</span>
      <span class="card-title">About.</span>
      <span class="card-sub">Who Shalom is, the culture, what to expect from a Sabbath here.</span>
      <span class="card-arrow">Preview ↗</span>
    </a>

    <a href="{{ route('admin.pages.edit', 'visit') }}" class="card">
      <span class="card-eyebrow">First-timer</span>
      <span class="card-title">Visit us.</span>
      <span class="card-sub">Address, map, parking, dress, FAQ — everything a visitor asks before showing up.</span>
      <span class="card-arrow">Preview ↗</span>
    </a>

    <a href="{{ route('admin.pages.edit', 'beliefs') }}" class="card">
      <span class="card-eyebrow">Doctrine</span>
      <span class="card-title">What we believe.</span>
      <span class="card-sub">12 of the 28 SDA Fundamentals in plain language, with link to the full statement.</span>
      <span class="card-arrow">Preview ↗</span>
    </a>

    <a href="{{ route('admin.pages.edit', 'peace-notes') }}" class="card">
      <span class="card-eyebrow">Sermon archive</span>
      <span class="card-title">Peace Notes.</span>
      <span class="card-sub">Latest sermon embedded · titles list · link out to full YouTube channel.</span>
      <span class="card-arrow">Preview ↗</span>
    </a>

    <a href="{{ route('admin.pages.edit', 'contact') }}" class="card">
      <span class="card-eyebrow">Get in touch</span>
      <span class="card-title">Contact form.</span>
      <span class="card-sub">Public form · sends to contact@thechurchofpeace.org with CC to c-wellpics.</span>
      <span class="card-arrow">Preview ↗</span>
    </a>
  </div>

  <p style="margin-top:48px;font-size:13px;color:var(--ink-soft);opacity:0.7;line-height:1.6;max-width:640px;">
    <strong style="color:var(--ink);">Editing copy:</strong> page text currently lives in the Blade templates &mdash; ping Karlon for now.
    A click-to-edit admin like the bulletin will land in a follow-up if you want it.
  </p>
</main>

</body>
</html>
