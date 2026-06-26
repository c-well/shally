<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Admin — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
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
    box-shadow: 0 12px 28px -16px color-mix(in srgb, var(--teal) 40%, transparent);
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
@keyframes hubBadgePulse { 0%,100% { box-shadow: 0 0 0 0 rgba(209,43,31,0.55); } 50% { box-shadow: 0 0 0 7px rgba(209,43,31,0); } }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<header class="top">
  <a href="/">← Back to bulletin</a>
  <span class="meta">admin · {{ auth()->user()->name }}</span>
</header>

<main>
  <h1>Admin.</h1>

  {{-- EXPENSIVE_CALL_BANNER — red flag if any Anthropic call in last 24h exceeded the threshold --}}
  @php
    $expensiveCall = \App\Models\AnthropicUsageLog::where('created_at', '>=', now()->subDay())
                       ->where('cost_usd', '>=', config('anthropic_pricing.alert_threshold_usd', 1.00))
                       ->orderByDesc('created_at')->first();
  @endphp
  @if ($expensiveCall)
    <div style="background:#fff;border:2px solid #a82a1f;border-left:6px solid #a82a1f;border-radius:6px;padding:16px 22px;margin:18px 0 24px;display:flex;align-items:center;gap:18px;">
      <div style="font-size:28px;line-height:1;">⚠</div>
      <div style="flex:1;min-width:0;">
        <div style="font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:#a82a1f;margin-bottom:4px;">
          Anthropic cost spike — last 24h
        </div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;color:var(--ink);line-height:1.25;">
          One call cost <strong>${{ number_format($expensiveCall->cost_usd, 4) }}</strong>
          from <code style="font-family:'JetBrains Mono',monospace;font-size:13px;background:rgba(168,42,31,0.08);padding:1px 6px;border-radius:3px;">{{ $expensiveCall->source }}</code>
          using {{ $expensiveCall->model }} — {{ $expensiveCall->created_at->diffForHumans() }}.
        </div>
      </div>
      <a href="{{ route('admin.anthropic-usage') }}" style="flex-shrink:0;padding:10px 18px;background:#a82a1f;color:#fff;text-decoration:none;border-radius:5px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;">Investigate →</a>
    </div>
  @endif
  <p class="lede">Everything that runs the app — log of who's signed in, the people allowed to sign in, the names that appear on bulletin autocomplete, and the schedule.</p>

  {{-- SITE_THEME_PICKER — Option A: site-wide theme. Small inline row at top of /admin so it's one glance away. --}}
  <div id="site-theme-picker"
       data-update-url="{{ route('admin.settings.theme') }}"
       data-current="{{ \App\Models\AppSetting::get('site_theme', 'default') }}"
       style="display:flex; align-items:center; gap:14px; flex-wrap:wrap; margin: 18px 0 28px; padding: 12px 16px; background:#fff; border:1px solid var(--line); border-radius:6px;">
    <span style="font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:var(--ink-soft); white-space:nowrap;">Site theme</span>
    @foreach (['default'=>['Default','#fefcef','#03617A'], 'communion'=>['Communion','#f1ebf9','#6b4d8a'], 'easter'=>['Easter','#eaf6ed','#3a8e63'], 'christmas'=>['Christmas','#f7eaea','#8b3a4b'], 'mothers'=>["Mother's Day",'#fbe8ee','#b1657a'], 'thanksgiving'=>['Thanksgiving','#f7ecdb','#8a5a2c']] as $key => $info)
      <button type="button" class="site-theme-swatch" data-theme="{{ $key }}" title="{{ $info[0] }}"
              style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px 4px 5px; background:#fff; border:1px solid color-mix(in srgb, var(--ink) 12%, transparent); border-radius:18px; cursor:pointer;">
        <span style="width:18px; height:18px; border-radius:50%; background: {{ $info[1] }}; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;"><span style="width:7px; height:7px; border-radius:50%; background: {{ $info[2] }};"></span></span>
        <span style="font-family:'Poppins',sans-serif; font-size:11px; color:var(--ink);">{{ $info[0] }}</span>
      </button>
    @endforeach
  </div>

  <script>
  (function () {
    const pick = document.getElementById('site-theme-picker');
    if (!pick) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const url  = pick.dataset.updateUrl;
    const current = pick.dataset.current || 'default';
    pick.querySelectorAll('.site-theme-swatch').forEach(sw => {
      if (sw.dataset.theme === current) { sw.style.borderColor = 'var(--teal)'; sw.style.background = 'color-mix(in srgb, var(--teal) 6%, transparent)'; }
      sw.addEventListener('click', async () => {
        const theme = sw.dataset.theme;
        try {
          const r = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ theme }),
            credentials: 'same-origin',
          });
          if (!r.ok) throw new Error('save failed');
          pick.querySelectorAll('.site-theme-swatch').forEach(s => {
            s.style.borderColor = (s === sw ? 'var(--teal)' : 'color-mix(in srgb, var(--ink) 12%, transparent)');
            s.style.background = (s === sw ? 'color-mix(in srgb, var(--teal) 6%, transparent)' : '#fff');
          });
          setTimeout(() => location.reload(), 400);
        } catch (e) { shToast('Theme save failed: ' + e.message); }
      });
    });
  })();
  </script>

  <div class="grid">

    @php
      $_unreadPrayer  = \App\Models\PrayerRequest::whereNull('read_at')->count();
      $_unreadContact = \App\Models\ContactMessage::whereNull('read_at')->count();
      $_unreadTotal   = $_unreadPrayer + $_unreadContact;
    @endphp
    <a href="{{ route('admin.messages') }}" class="card" style="position:relative;">
      <span class="card-eyebrow">Inbox</span>
      <span class="card-title">Messages.</span>
      <span class="card-sub">Prayer requests &amp; contact-form messages from visitors.</span>
      <span class="card-arrow">Open →</span>
      @if ($_unreadTotal > 0)
        <span style="position:absolute; top:18px; right:18px; min-width:22px; height:22px; padding:0 7px; display:inline-flex; align-items:center; justify-content:center; background:#d12b1f; color:#fff; border-radius:999px; font-family:'Instrument Sans',sans-serif; font-size:11px; font-weight:700; @if($_unreadPrayer > 0) animation: hubBadgePulse 2.4s ease-in-out infinite; @endif">{{ $_unreadTotal }}</span>
      @endif
    </a>

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

        <a href="{{ route('admin.changes') }}" class="card">
      <span class="card-eyebrow">Undo</span>
      <span class="card-title">Edit history.</span>
      <span class="card-sub">Every content edit — bulletins, pages, lessons — with a one-click undo for each. The quick fix for a typo or a wrong change.</span>
      <span class="card-arrow">Open &rarr;</span>
    </a>

            <a href="{{ route('admin.bulletin') }}" class="card">
      <span class="card-eyebrow">New</span>
      <span class="card-title">Bulletin editor.</span>
      <span class="card-sub">The new drill-through editor — every item inline, autosaves, works on phone and tablet. Switch back to classic anytime.</span>
      <span class="card-arrow">Open &rarr;</span>
    </a>

<a href="{{ route('admin.events') }}" class="card">
      <span class="card-eyebrow">Calendar</span>
      <span class="card-title">Events.</span>
      <span class="card-sub">Add an event in seconds — name, date, flyer. It goes live as you fill it in; take anything off the site anytime.</span>
      <span class="card-arrow">Open &rarr;</span>
    </a>

<a href="{{ route('admin.intake.index') }}" class="card">
      <span class="card-eyebrow">Forms</span>
      <span class="card-title">Intake.</span>
      <span class="card-sub">Graduation slides, event sign-ups, and more — shareable form links, with every submission in its gallery.</span>
      <span class="card-arrow">Open &rarr;</span>
    </a>

<a href="{{ route('admin.changelog') }}" class="card">
      <span class="card-eyebrow">Dev notes</span>
      <span class="card-title">Changelog.</span>
      <span class="card-sub">Plain-English log of every site change. When something feels off, check here first.</span>
      <span class="card-arrow">Open →</span>
    </a>

    <a href="{{ route('admin.anthropic-usage') }}" class="card">
      <span class="card-eyebrow">Cost</span>
      <span class="card-title">API spend.</span>
      <span class="card-sub">Live tally of Shalom's Anthropic API calls. Per-source + per-model breakdown, recent calls.</span>
      <span class="card-arrow">Open →</span>
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

    <a href="{{ route('admin.peace.index') }}" class="card">
      <span class="card-eyebrow">Ministry</span>
      <span class="card-title">Finding Peace.</span>
      <span class="card-sub">Edit auto-processed sermons · tweak Q&amp;As, scriptures, topics · flag offsite/no-sermon.</span>
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
@include('partials._confirm')

</body>
</html>
