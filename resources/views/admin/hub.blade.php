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

  main { max-width: 1080px; margin: 0 auto; padding: clamp(40px, 7vh, 70px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(48px, 7vw, 72px); font-weight: 500; letter-spacing: -0.035em; line-height: 1; color: var(--ink); }
  .lede { margin-top: 18px; font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 540px; }

  /* ── The top latch: view choice + search in one quiet bar ── */
  .viewbar { margin: 26px 0 6px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; padding: 12px 16px; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .viewbar-label { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-soft); white-space: nowrap; }
  /* Buttons follow the site spec: small radius (6-8px), never pills. */
  .viewseg { display: inline-flex; background: var(--parchment); border: 1px solid var(--line); border-radius: var(--r-btn, 8px); padding: 3px; gap: 2px; }
  .viewseg button {
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
    border: 0; background: transparent; color: var(--ink-soft); border-radius: calc(var(--r-btn, 8px) - 2px);
    padding: 8px 16px; cursor: pointer; transition: background .15s, color .15s; white-space: nowrap;
  }
  .viewseg button.on { background: var(--teal); color: #fff; }
  .viewseg button:disabled { opacity: .45; cursor: default; }
  .hub-search-wrap { position: relative; flex: 1; min-width: 220px; }
  .hub-search-wrap svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--ink-soft); pointer-events: none; }
  #hub-search { width: 100%; font: inherit; font-size: 14px; padding: 10px 14px 10px 38px; background: var(--parchment); color: var(--ink); border: 1px solid var(--line); border-radius: var(--r-field, 8px); }
  #hub-search:focus { border-color: var(--teal); outline: none; box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }

  .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; margin-top: 34px; }
  .card {
    position: relative;
    background: #fff; border: 1px solid var(--line); border-radius: 8px;
    padding: 26px 24px;
    display: flex; flex-direction: column; gap: 8px;
    text-decoration: none; color: var(--ink);
    transition: border-color 0.15s, transform 0.12s, box-shadow 0.15s;
  }
  .card:hover { border-color: var(--teal); transform: translateY(-2px); box-shadow: 0 12px 28px -16px color-mix(in srgb, var(--teal) 40%, transparent); }
  .card-eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); }
  .card-title { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 500; letter-spacing: -0.01em; color: var(--ink); margin-top: 6px; }
  .card-sub { font-size: 13px; line-height: 1.5; color: var(--ink-soft); margin-top: 4px; }
  .card-arrow { margin-top: 14px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); }
  .card-badge {
    position: absolute; top: 18px; right: 18px;
    min-width: 22px; height: 22px; padding: 0 7px; display: inline-flex; align-items: center; justify-content: center;
    background: #d12b1f; color: #fff; border-radius: 999px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700;
  }
  .card-badge.pulse { animation: hubBadgePulse 2.4s ease-in-out infinite; }
  @keyframes hubBadgePulse { 0%,100% { box-shadow: 0 0 0 0 rgba(209,43,31,0.55); } 50% { box-shadow: 0 0 0 7px rgba(209,43,31,0); } }

  .sec-h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px, 5vw, 52px); font-weight: 500; letter-spacing: -0.02em; margin: 72px 0 8px; color: var(--ink); }

  /* ── Group latches (Groups + Smart views) ── */
  .latch { margin-top: 16px; background: #fff; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
  .latch-head { width: 100%; display: flex; align-items: center; gap: 14px; padding: 20px 22px; background: transparent; border: 0; cursor: pointer; text-align: left; color: var(--ink); }
  .latch-head:hover .latch-title { color: var(--teal); }
  .latch-title { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 500; letter-spacing: -0.01em; white-space: nowrap; transition: color .15s; }
  .latch-count { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; color: var(--ink-soft); background: color-mix(in srgb, var(--ink) 6%, transparent); border-radius: 999px; padding: 3px 9px; letter-spacing: 0.08em; }
  .latch-peek { flex: 1; min-width: 0; font-size: 12px; color: var(--ink-soft); opacity: .7; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .latch-badge { min-width: 20px; height: 20px; padding: 0 6px; display: inline-flex; align-items: center; justify-content: center; background: #d12b1f; color: #fff; border-radius: 999px; font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; }
  .latch-chev { flex-shrink: 0; color: var(--teal); transition: transform .25s ease; }
  .latch.open .latch-chev { transform: rotate(180deg); }
  .latch-body { display: none; padding: 0 22px 24px; }
  .latch.open .latch-body { display: block; }
  .latch .grid { margin-top: 0; gap: 14px; }
  .latch .card { background: var(--parchment); padding: 20px 20px 18px; }
  .latch.smart { border-color: color-mix(in srgb, var(--teal) 45%, var(--line)); }
  .latch.smart .latch-title::after { content: '★'; font-size: 14px; color: var(--brass, #b08d3c); margin-left: 8px; vertical-align: 6px; }

  /* ── View switching: body[data-hubview] shows exactly one mode ── */
  .hub-mode { display: none; }
  body[data-hubview="default"] #mode-default { display: block; }
  body[data-hubview="groups"]  #mode-groups  { display: block; }
  body[data-hubview="smart"]   #mode-smart   { display: block; }

  /* ── Search mode: reveal matches inside whichever view is active ── */
  main.searching .card { display: none; }
  main.searching .card.match { display: flex; }
  main.searching .latch { display: none; }
  main.searching .latch.has-match { display: block; }
  main.searching .latch.has-match .latch-body { display: block; }
  main.searching .sec-h2, main.searching .copy-note { display: none; }
  .no-results { display: none; margin-top: 26px; padding: 28px; text-align: center; color: var(--ink-soft); background: #fff; border: 1px dashed var(--line); border-radius: 12px; font-size: 14px; }
  main.searching .no-results.show { display: block; }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>

@php
  // ── Data: one source of truth for every hub destination ──
  $_unreadPrayer  = \App\Models\PrayerRequest::whereNull('read_at')->count();
  $_unreadContact = \App\Models\ContactMessage::whereNull('read_at')->count();
  $_unreadTotal   = $_unreadPrayer + $_unreadContact;

  $HUB = [
    'messages'  => ['Inbox', 'Messages.', 'Prayer requests & contact-form messages from visitors.', 'Open →', route('admin.messages')],
    'users'     => ['People', 'Users.', 'Add Andre, the elders, members. Set their PIN.', 'Manage →', route('admin.users')],
    'logs'      => ['Activity', 'Audit log.', 'Every sign-in, magic link, and error from the last 40 days.', 'View →', route('admin.logs')],
    'changes'   => ['Undo', 'Edit history.', 'Every content edit — bulletins, pages, lessons — with a one-click undo for each.', 'Open →', route('admin.changes')],
    'bulletin'  => ['New', 'Bulletin editor.', 'The drill-through editor — every item inline, autosaves, works on phone and tablet.', 'Open →', route('admin.bulletin')],
    'events'    => ['Calendar', 'Events.', 'Add an event in seconds — name, date, flyer. Live as you fill it in.', 'Open →', route('admin.events')],
    'games'     => ['Kids', 'Scripture games.', 'Build the verse games — pick a book, a game, an age. They appear at /kids.', 'Open →', route('admin.games')],
    'mystery'   => ['Teens', 'Undercover.', 'The question bank for the youth-night mystery game.', 'Open →', route('admin.mystery')],
    'intake'    => ['Forms', 'Intake.', 'Graduation slides, sign-ups — shareable links, every submission in its gallery.', 'Open →', route('admin.intake.index')],
    'changelog' => ['Dev notes', 'Changelog.', 'Plain-English log of every site change. When something feels off, look here first.', 'Open →', route('admin.changelog')],
    'spend'     => ['Cost', 'API spend.', "Live tally of Shalom's Anthropic API calls · per-source + per-model.", 'Open →', route('admin.anthropic-usage')],
    'names'     => ['Cleanup', 'Bulletin names.', "Hide typos or names you don't want appearing in autocomplete.", 'Tidy →', route('admin.names')],
    'schedule'  => ['Departments', 'Schedule.', "Who's serving on Sabbath: ushers, deacons, music, platform.", 'Open →', route('schedule.index')],
    'lessons'   => ['Sabbath school', 'Lessons.', 'Quarterly PDF + readings — now rolls over automatically each quarter.', 'Manage →', route('admin.lessons')],
    'sermons'   => ['Peace Notes', 'Sermons.', 'Upload audio sermons to the public archive · listen, edit, delete.', 'Manage →', route('admin.sermons.index')],
    'peace'     => ['Ministry', 'Finding Peace.', 'Edit auto-processed sermons · tweak Q&As, scriptures, topics.', 'Manage →', route('admin.peace.index')],
    'slides'    => ['Home page', 'Hero slides.', 'Photos that rotate on the home page · upload, reorder, retire.', 'Manage →', route('admin.slides.index')],
    'media'     => ['Library', 'Media pool.', 'All uploaded images and audio · pick, copy URL, delete.', 'Open →', route('admin.media.index')],
    'analytics' => ['Telemetry', 'Analytics.', 'First-party page views, top paths, devices, referrers · privacy-first.', 'View →', route('admin.analytics')],
    'buginbox'  => ['Bug reports', 'Inbox.', 'Read incoming bug reports + feedback. Close when handled.', 'Open →', route('admin.inbox')],
    'pg_landing'=> ['Front door', 'Home / landing.', 'Hero, schedule, this-week cards, latest sermon, donate band.', 'Preview ↗', route('admin.pages.edit', 'landing')],
    'pg_about'  => ['Our story', 'About.', 'Who Shalom is, the culture, what to expect from a Sabbath here.', 'Preview ↗', route('admin.pages.edit', 'about')],
    'pg_visit'  => ['First-timer', 'Visit us.', 'Address, map, parking, dress, FAQ.', 'Preview ↗', route('admin.pages.edit', 'visit')],
    'pg_beliefs'=> ['Doctrine', 'What we believe.', '12 of the 28 SDA Fundamentals in plain language.', 'Preview ↗', route('admin.pages.edit', 'beliefs')],
    'pg_notes'  => ['Sermon archive', 'Peace Notes.', 'Latest sermon embedded · titles list · YouTube channel link.', 'Preview ↗', route('admin.pages.edit', 'peace-notes')],
    'pg_contact'=> ['Get in touch', 'Contact form.', 'Public form · sends to contact@ with CC to c-wellpics.', 'Preview ↗', route('admin.pages.edit', 'contact')],
  ];

  $TOOL_KEYS = ['messages','users','logs','changes','bulletin','events','games','mystery','intake','changelog','spend','names','schedule','lessons','sermons','peace','slides','media','analytics','buginbox'];
  $PAGE_KEYS = ['pg_landing','pg_about','pg_visit','pg_beliefs','pg_notes','pg_contact'];

  $GROUPS = [
    'week'    => ['This week', ['bulletin', 'events', 'schedule', 'slides', 'lessons', 'names']],
    'people'  => ['People & inbox', ['messages', 'users', 'intake', 'buginbox']],
    'ministry'=> ['Ministries, games & site', ['peace', 'sermons', 'games', 'mystery', 'media', 'pg_landing', 'pg_about', 'pg_visit', 'pg_beliefs', 'pg_notes', 'pg_contact']],
    'system'  => ['System & insights', ['analytics', 'logs', 'changes', 'changelog', 'spend']],
  ];

  // ── Usage learning + saved view preference ──
  $_uid = auth()->id();
  try {
      \DB::table('admin_hub_usage')->where(['user_id' => $_uid, 'item_key' => '__visits'])->exists()
          ? \DB::table('admin_hub_usage')->where(['user_id' => $_uid, 'item_key' => '__visits'])->update(['clicks' => \DB::raw('clicks + 1'), 'updated_at' => now()])
          : \DB::table('admin_hub_usage')->insert(['user_id' => $_uid, 'item_key' => '__visits', 'clicks' => 1, 'created_at' => now(), 'updated_at' => now()]);
      $_visits = (int) \DB::table('admin_hub_usage')->where(['user_id' => $_uid, 'item_key' => '__visits'])->value('clicks');
      $_top = \DB::table('admin_hub_usage')->where('user_id', $_uid)->where('item_key', 'not like', '\_\_%')
                ->orderByDesc('clicks')->limit(6)->pluck('clicks', 'item_key');
      $_viewRow = \DB::table('admin_hub_usage')->where('user_id', $_uid)->where('item_key', 'like', '__view:%')->value('item_key');
  } catch (\Throwable $e) { $_visits = 0; $_top = collect(); $_viewRow = null; }

  $_smartReady = $_visits >= 7;
  $_smart = $_smartReady ? $_top->keys()->filter(fn ($k) => isset($HUB[$k]))->values() : collect();
  $_view = $_viewRow ? substr($_viewRow, 7) : 'default';
  if (! in_array($_view, ['default', 'groups', 'smart'], true)) $_view = 'default';
  if ($_view === 'smart' && $_smart->isEmpty()) $_view = 'default';
@endphp

<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}" data-hubview="{{ $_view }}">

@include('partials.site-menu')

<header class="top">
  <a href="/">← Back to bulletin</a>
  <span class="meta">admin · {{ auth()->user()->name }}</span>
</header>

<main id="hub-main">
  <h1>Admin.</h1>

  {{-- CRON_WATCHDOG_BANNER — scheduler heartbeat stale = backups & jobs stopped --}}
  @php
    $_beat = \App\Models\AppSetting::get('cron_heartbeat_at');
    $_beatAge = $_beat ? (int) now()->diffInMinutes(\Carbon\Carbon::parse($_beat)) : null;
  @endphp
  @if ($_beatAge !== null && $_beatAge > 60)
    <div style="background:#fff;border:2px solid #a82a1f;border-left:6px solid #a82a1f;border-radius:6px;padding:16px 22px;margin:18px 0 6px;display:flex;align-items:center;gap:18px;">
      <div style="font-size:28px;line-height:1;">⚠</div>
      <div style="flex:1;min-width:0;">
        <div style="font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:#a82a1f;margin-bottom:4px;">Cron appears to be down</div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--ink);line-height:1.3;">The scheduler heartbeat is <strong>{{ $_beatAge }} minutes</strong> old — backups and scheduled jobs are not running. An alert email has been sent.</div>
      </div>
    </div>
  @endif

  {{-- EXPENSIVE_CALL_BANNER — red flag if any Anthropic call in last 24h exceeded the threshold --}}
  @php
    $expensiveCall = \App\Models\AnthropicUsageLog::where('created_at', '>=', now()->subDay())
                       ->where('cost_usd', '>=', config('anthropic_pricing.alert_threshold_usd', 1.00))
                       ->orderByDesc('created_at')->first();
  @endphp
  @if ($expensiveCall)
    <div style="background:#fff;border:2px solid #a82a1f;border-left:6px solid #a82a1f;border-radius:6px;padding:16px 22px;margin:18px 0 6px;display:flex;align-items:center;gap:18px;">
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

  {{-- SITE_THEME_PICKER --}}
  <div id="site-theme-picker"
       data-update-url="{{ route('admin.settings.theme') }}"
       data-current="{{ \App\Models\AppSetting::get('site_theme', 'default') }}"
       style="display:flex; align-items:center; gap:14px; flex-wrap:wrap; margin: 18px 0 0; padding: 12px 16px; background:#fff; border:1px solid var(--line); border-radius:6px;">
    <span style="font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.2em; text-transform:uppercase; color:var(--ink-soft); white-space:nowrap;">Site theme</span>
    @foreach (['default'=>['Default','#fefcef','#03617A'], 'communion'=>['Communion','#f1ebf9','#6b4d8a'], 'easter'=>['Easter','#eaf6ed','#3a8e63'], 'christmas'=>['Christmas','#f7eaea','#8b3a4b'], 'mothers'=>["Mother's Day",'#fbe8ee','#b1657a'], 'thanksgiving'=>['Thanksgiving','#f7ecdb','#8a5a2c']] as $key => $info)
      <button type="button" class="site-theme-swatch" data-theme="{{ $key }}" title="{{ $info[0] }}"
              style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px 4px 5px; background:#fff; border:1px solid color-mix(in srgb, var(--ink) 12%, transparent); border-radius:18px; cursor:pointer;">
        <span style="width:18px; height:18px; border-radius:50%; background: {{ $info[1] }}; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;"><span style="width:7px; height:7px; border-radius:50%; background: {{ $info[2] }};"></span></span>
        <span style="font-family:'Poppins',sans-serif; font-size:11px; color:var(--ink);">{{ $info[0] }}</span>
      </button>
    @endforeach
  </div>

  {{-- ── THE TOP LATCH: pick how this hub looks (per admin, remembered) + search ── --}}
  <div class="viewbar">
    <span class="viewbar-label">View</span>
    <div class="viewseg" role="group" aria-label="Hub view">
      <button type="button" data-view="default" @if($_view==='default') class="on" @endif>Default</button>
      <button type="button" data-view="groups" @if($_view==='groups') class="on" @endif>Groups</button>
      <button type="button" data-view="smart" @if($_view==='smart') class="on" @endif @if(!$_smartReady) disabled title="Unlocks after your 7th visit — {{ $_visits }}/7" @endif>Smart ★@if(!$_smartReady) · {{ min($_visits,7) }}/7 @endif</button>
    </div>
    <div class="hub-search-wrap">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input id="hub-search" type="search" placeholder="Find anything — bulletin, slides, games, spend…" autocomplete="off">
    </div>
  </div>

  {{-- ═══ MODE: DEFAULT — every card out, the familiar look ═══ --}}
  <section class="hub-mode" id="mode-default">
    <div class="grid">
      @foreach ($TOOL_KEYS as $k)
        @php [$eyebrow, $title, $sub, $arrow, $url] = $HUB[$k]; @endphp
        <a href="{{ $url }}" class="card" data-track="{{ $k }}" data-search="{{ strtolower($eyebrow . ' ' . $title . ' ' . $sub) }}">
          <span class="card-eyebrow">{{ $eyebrow }}</span>
          <span class="card-title">{{ $title }}</span>
          <span class="card-sub">{{ $sub }}</span>
          <span class="card-arrow">{{ $arrow }}</span>
          @if ($k === 'messages' && $_unreadTotal > 0)
            <span class="card-badge @if($_unreadPrayer > 0) pulse @endif">{{ $_unreadTotal }}</span>
          @endif
        </a>
      @endforeach
    </div>

    <h2 class="sec-h2">Site pages.</h2>
    <p class="lede copy-note" style="margin-top:8px;margin-bottom:4px;">The public face of the church. Click any to preview as a visitor would see it.</p>
    <div class="grid">
      @foreach ($PAGE_KEYS as $k)
        @php [$eyebrow, $title, $sub, $arrow, $url] = $HUB[$k]; @endphp
        <a href="{{ $url }}" class="card" data-track="{{ $k }}" data-search="{{ strtolower($eyebrow . ' ' . $title . ' ' . $sub) }}">
          <span class="card-eyebrow">{{ $eyebrow }}</span>
          <span class="card-title">{{ $title }}</span>
          <span class="card-sub">{{ $sub }}</span>
          <span class="card-arrow">{{ $arrow }}</span>
        </a>
      @endforeach
    </div>
  </section>

  {{-- ═══ MODE: GROUPS — four hush latches ═══ --}}
  <section class="hub-mode" id="mode-groups">
    @foreach ($GROUPS as $gkey => [$glabel, $gitems])
      <section class="latch" data-latch="{{ $gkey }}">
        <button class="latch-head" type="button" aria-expanded="false">
          <span class="latch-title">{{ $glabel }}</span>
          <span class="latch-count">{{ count($gitems) }}</span>
          <span class="latch-peek">{{ collect($gitems)->map(fn ($k) => rtrim($HUB[$k][1], '.'))->join(' · ') }}</span>
          @if ($gkey === 'people' && $_unreadTotal > 0)
            <span class="latch-badge">{{ $_unreadTotal }}</span>
          @endif
          <svg class="latch-chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="latch-body"><div class="grid">
          @foreach ($gitems as $k)
            @php [$eyebrow, $title, $sub, $arrow, $url] = $HUB[$k]; @endphp
            <a href="{{ $url }}" class="card" data-track="{{ $k }}" data-search="{{ strtolower($eyebrow . ' ' . $title . ' ' . $sub) }}">
              <span class="card-eyebrow">{{ $eyebrow }}</span>
              <span class="card-title">{{ $title }}</span>
              <span class="card-sub">{{ $sub }}</span>
              <span class="card-arrow">{{ $arrow }}</span>
              @if ($k === 'messages' && $_unreadTotal > 0)
                <span class="card-badge @if($_unreadPrayer > 0) pulse @endif">{{ $_unreadTotal }}</span>
              @endif
            </a>
          @endforeach
        </div></div>
      </section>
    @endforeach
  </section>

  {{-- ═══ MODE: SMART — your most-used first, everything else behind the latches ═══ --}}
  <section class="hub-mode" id="mode-smart">
    @if ($_smart->isNotEmpty())
      <section class="latch smart open">
        <button class="latch-head" type="button" aria-expanded="true">
          <span class="latch-title">Your most used</span>
          <span class="latch-count">{{ $_smart->count() }}</span>
          <span class="latch-peek">learned from your last {{ $_visits }} visits</span>
          <svg class="latch-chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="latch-body"><div class="grid">
          @foreach ($_smart as $k)
            @php [$eyebrow, $title, $sub, $arrow, $url] = $HUB[$k]; @endphp
            <a href="{{ $url }}" class="card" data-track="{{ $k }}" data-search="{{ strtolower($eyebrow . ' ' . $title . ' ' . $sub) }}">
              <span class="card-eyebrow">{{ $eyebrow }}</span>
              <span class="card-title">{{ $title }}</span>
              <span class="card-sub">{{ $sub }}</span>
              <span class="card-arrow">{{ $arrow }}</span>
              @if ($k === 'messages' && $_unreadTotal > 0)
                <span class="card-badge @if($_unreadPrayer > 0) pulse @endif">{{ $_unreadTotal }}</span>
              @endif
            </a>
          @endforeach
        </div></div>
      </section>
    @endif

    @foreach ($GROUPS as $gkey => [$glabel, $gitems])
      <section class="latch" data-latch="smart-{{ $gkey }}">
        <button class="latch-head" type="button" aria-expanded="false">
          <span class="latch-title">{{ $glabel }}</span>
          <span class="latch-count">{{ count($gitems) }}</span>
          <span class="latch-peek">{{ collect($gitems)->map(fn ($k) => rtrim($HUB[$k][1], '.'))->join(' · ') }}</span>
          @if ($gkey === 'people' && $_unreadTotal > 0)
            <span class="latch-badge">{{ $_unreadTotal }}</span>
          @endif
          <svg class="latch-chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="latch-body"><div class="grid">
          @foreach ($gitems as $k)
            @php [$eyebrow, $title, $sub, $arrow, $url] = $HUB[$k]; @endphp
            <a href="{{ $url }}" class="card" data-track="{{ $k }}" data-search="{{ strtolower($eyebrow . ' ' . $title . ' ' . $sub) }}">
              <span class="card-eyebrow">{{ $eyebrow }}</span>
              <span class="card-title">{{ $title }}</span>
              <span class="card-sub">{{ $sub }}</span>
              <span class="card-arrow">{{ $arrow }}</span>
            </a>
          @endforeach
        </div></div>
      </section>
    @endforeach
  </section>

  <div class="no-results" id="hub-no-results">Nothing matches — try another word.</div>

  <p class="copy-note" style="margin-top:48px;font-size:13px;color:var(--ink-soft);opacity:0.7;line-height:1.6;max-width:640px;">
    <strong style="color:var(--ink);">Editing copy:</strong> page text currently lives in the Blade templates &mdash; ping Karlon for now.
    A click-to-edit admin like the bulletin will land in a follow-up if you want it.
  </p>

</main>
@include('partials._confirm')

<script>
(function () {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const TRACK_URL = '{{ route('admin.hub.track') }}';
  function track(key) {
    try {
      fetch(TRACK_URL, {
        method: 'POST', keepalive: true,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ key }), credentials: 'same-origin',
      });
    } catch (e) {}
  }

  // ── Theme picker ──
  const pick = document.getElementById('site-theme-picker');
  if (pick) {
    const url = pick.dataset.updateUrl;
    const current = pick.dataset.current || 'default';
    pick.querySelectorAll('.site-theme-swatch').forEach(sw => {
      if (sw.dataset.theme === current) { sw.style.borderColor = 'var(--teal)'; sw.style.background = 'color-mix(in srgb, var(--teal) 6%, transparent)'; }
      sw.addEventListener('click', async () => {
        try {
          const r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify({ theme: sw.dataset.theme }), credentials: 'same-origin' });
          if (!r.ok) throw new Error('save failed');
          setTimeout(() => location.reload(), 350);
        } catch (e) { window.shToast && window.shToast('Theme save failed: ' + e.message); }
      });
    });
  }

  // ── The top latch: switch views, remember per admin ──
  document.querySelectorAll('.viewseg button[data-view]').forEach(b => {
    b.addEventListener('click', () => {
      if (b.disabled) return;
      document.body.dataset.hubview = b.dataset.view;
      document.querySelectorAll('.viewseg button').forEach(x => x.classList.toggle('on', x === b));
      track('__view:' + b.dataset.view);
    });
  });

  // ── Group latches ──
  document.querySelectorAll('.latch-head').forEach(h => {
    h.addEventListener('click', () => {
      const latch = h.closest('.latch');
      const open = latch.classList.toggle('open');
      h.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  // ── Search: reveal matches inside the ACTIVE view only ──
  const main = document.getElementById('hub-main');
  const input = document.getElementById('hub-search');
  const noRes = document.getElementById('hub-no-results');
  input.addEventListener('input', () => {
    const q = input.value.trim().toLowerCase();
    const mode = document.getElementById('mode-' + document.body.dataset.hubview);
    const cards = mode ? Array.from(mode.querySelectorAll('.card[data-search]')) : [];
    if (!q) {
      main.classList.remove('searching');
      document.querySelectorAll('.card.match').forEach(c => c.classList.remove('match'));
      document.querySelectorAll('.latch.has-match').forEach(l => l.classList.remove('has-match'));
      noRes.classList.remove('show');
      return;
    }
    main.classList.add('searching');
    document.querySelectorAll('.card.match').forEach(c => c.classList.remove('match'));
    document.querySelectorAll('.latch.has-match').forEach(l => l.classList.remove('has-match'));
    let any = false;
    cards.forEach(c => {
      if (c.dataset.search.includes(q)) {
        c.classList.add('match'); any = true;
        const latch = c.closest('.latch');
        if (latch) latch.classList.add('has-match');
      }
    });
    noRes.classList.toggle('show', !any);
  });
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      const mode = document.getElementById('mode-' + document.body.dataset.hubview);
      const first = mode && mode.querySelector('.card.match');
      if (first) first.click();
    }
    if (e.key === 'Escape') { input.value = ''; input.dispatchEvent(new Event('input')); }
  });

  // ── Usage learning: record card clicks (fetch keepalive survives navigation) ──
  document.addEventListener('click', e => {
    const card = e.target.closest('.card[data-track]');
    if (card) track(card.dataset.track);
  });
})();
</script>
</body>
</html>
