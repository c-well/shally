{{-- ============================================================
     Site header + full-screen ☰ menu — single source of truth.
     @include('partials.site-menu') on every public/admin page.

     Renders:
       - Sticky brand + ☰ trigger
       - Full-screen overlay menu with accordion sections
       - Public nav for everyone
       - Admin section conditional on auth + role

     CSS + JS are inline so this partial is fully self-contained.
     ============================================================ --}}
{{-- The menu is included on every page — it must not depend on the host page's
     font loads (menu rendered bold-fallback on pages without Cormorant, caught by
     Karlon 2026-07-04). Browsers dedupe identical stylesheet links. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Poppins:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  /* ─── Header ─── */
  .site-menu-header {
    position: sticky; top: 0; z-index: 50;
    background: var(--parchment, #fefcef);
    border-bottom: 1px solid color-mix(in srgb, var(--teal) 12%, transparent);
  }
  .site-menu-header-inner {
    max-width: 1100px; margin: 0 auto;
    padding: 18px 22px 14px;
    display: flex; align-items: center; justify-content: space-between;
    min-height: 60px;
  }
  /* Site brand — "shalom" in Xtreem teal, lowercase, matches original */
  @font-face {
    font-family: 'Xtreem';
    src: url('/fonts/XtreemMedium.ttf') format('truetype');
    font-weight: 500; font-style: normal; font-display: swap;
  }
  .site-menu-brand {
    text-decoration: none; line-height: 1;
    display: inline-flex; align-items: center;
  }
  .site-menu-brand em {
    font-family: 'Xtreem', 'Cormorant Garamond', serif;
    font-style: normal; font-weight: 500;
    color: var(--teal, #03617A);
    text-transform: lowercase;
    font-size: 56px; letter-spacing: -0.02em; line-height: 0.8;
    display: inline-block;
  }
  @media (max-width: 600px) {
    .site-menu-brand em { font-size: 42px; }
  }
  .site-menu-trigger {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent; color: var(--ink, #1a2332);
    border: 0; padding: 6px 2px;
    cursor: pointer; transition: color 0.15s;
  }
  .site-menu-trigger:hover { color: var(--teal, #03617A); }
  .site-menu-trigger:focus-visible { outline: none; color: var(--teal, #03617A); }
  .site-menu-trigger-label {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px; font-weight: 600; line-height: 1;
    letter-spacing: 0.16em; text-transform: uppercase;
  }
  .site-menu-trigger svg { display: block; }

  /* ─── Full-screen menu ─── */
  .site-menu-overlay {
    position: fixed; inset: 0; z-index: 200;
    background: var(--parchment, #fefcef);
    display: none; overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }
  .site-menu-overlay.open { display: block; animation: site-menu-fade 0.2s ease-out; }
  @keyframes site-menu-fade { from { opacity: 0; } to { opacity: 1; } }

  .site-menu-overlay-header {
    position: sticky; top: 0;
    padding: 14px 22px;
    display: flex; align-items: center; justify-content: space-between;
    background: var(--parchment, #fefcef);
  }
  .site-menu-overlay-header .site-menu-brand em { font-size: 38px; }
  @media (max-width: 600px) {
    .site-menu-overlay-header .site-menu-brand em { font-size: 32px; }
  }
  .site-menu-close {
    width: 44px; height: 44px;
    background: transparent; border: 0;
    color: var(--ink, #1a2332); cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 999px; transition: background 0.15s;
  }
  .site-menu-close:hover { background: rgba(0,0,0,0.06); }
  .site-menu-close svg { width: 20px; height: 20px; }

  .site-menu-nav { max-width: 560px; margin: 0 auto; padding: 36px 28px 80px; }

  /* Top-level rows (direct links + accordion toggles share the same shell) */
  .site-menu-link, .site-menu-section-toggle {
    display: flex; align-items: center; justify-content: space-between;
    width: 100%;
    padding: 20px 0;
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px; font-weight: 600; line-height: 1;
    letter-spacing: -0.005em;
    color: var(--ink, #1a2332);
    text-decoration: none;
    background: transparent; border: 0;
    border-bottom: 1px solid color-mix(in srgb, var(--ink) 12%, transparent);
    text-align: left; cursor: pointer;
    transition: color 0.15s;
  }
  .site-menu-link:hover, .site-menu-link:focus-visible,
  .site-menu-section-toggle:hover, .site-menu-section-toggle:focus-visible {
    color: var(--teal, #03617A);
  }
  .site-menu-link .arrow {
    opacity: 0.4; font-size: 18px; font-weight: 400;
    transition: opacity 0.15s, transform 0.15s;
  }
  .site-menu-link:hover .arrow { opacity: 0.85; transform: translateX(2px); }
  .site-menu-link-admin { color: var(--teal, #03617A); }
  .site-menu-link-signout { color: #a82a1f; }
  .site-menu-link-signout:hover { color: #c0392b; }

  .site-menu-section { border-bottom: 1px solid color-mix(in srgb, var(--ink) 12%, transparent); }
  .site-menu-section-toggle { border-bottom: 0; }
  .site-menu-section-toggle .chev {
    width: 22px; height: 22px;
    color: var(--teal, #03617A);   /* brand teal — one accent everywhere */
    opacity: 0.85;
    transition: transform 0.25s ease, opacity 0.15s;
  }
  .site-menu-section-toggle:hover .chev { opacity: 1; }
  .site-menu-section.open .site-menu-section-toggle .chev {
    transform: rotate(180deg); opacity: 1;
  }
  .site-menu-section-body {
    max-height: 0; overflow: hidden;
    transition: max-height 0.3s ease;
  }
  .site-menu-section.open .site-menu-section-body { max-height: 800px; }
  .site-menu-sub-list {
    padding: 4px 0 22px 4px;
    display: flex; flex-direction: column; gap: 2px;
  }
  .site-menu-sub-link {
    display: block;
    padding: 12px 0;
    font-family: 'Poppins', sans-serif;
    font-size: 17px; font-weight: 500; line-height: 1.2;
    color: var(--ink, #1a2332);
    text-decoration: none;
    transition: color 0.15s, transform 0.15s;
  }
  .site-menu-sub-link:hover { color: var(--teal, #03617A); transform: translateX(2px); }
  .site-menu-sub-link-form { all: unset; cursor: pointer; display: block; }
  /* ── menu engine styles (four templates share the base) ── */
  .a2hs { width: 100%; text-align: left; background: none; border: 0; cursor: pointer; font: inherit; }
  .a2hs-card { position: fixed; inset: 0; z-index: 200; display: none; align-items: flex-end; justify-content: center; background: rgba(26,35,50,.45); }
  .a2hs-card.open { display: flex; }
  .a2hs-inner { background: var(--parchment, #fefcef); border-radius: 18px 18px 0 0; padding: 26px 24px 40px; max-width: 430px; width: 100%; font-family: 'Instrument Sans', sans-serif; }
  .a2hs-inner h3 { font-size: 17px; font-weight: 700; color: var(--ink, #1a2332); }
  .a2hs-step { display: flex; align-items: center; gap: 13px; margin-top: 16px; font-size: 14.5px; color: var(--ink-soft, #4a5568); line-height: 1.5; }
  .a2hs-num { flex-shrink: 0; width: 28px; height: 28px; border-radius: 50%; background: color-mix(in srgb, var(--teal, #03617A) 10%, #fff); border: 1px solid color-mix(in srgb, var(--teal, #03617A) 35%, transparent); color: var(--teal, #03617A); font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; }
  .a2hs-glyph { display: inline-block; vertical-align: -3px; }
  .a2hs-close { margin-top: 22px; width: 100%; font: 700 12px 'Instrument Sans'; letter-spacing: .12em; text-transform: uppercase; color: var(--teal, #03617A); background: #fff; border: 1px solid var(--line, rgba(26,35,50,.14)); border-radius: 9px; padding: 13px; cursor: pointer; }
  .mn-badge { font: 700 10px 'Instrument Sans', sans-serif; letter-spacing: .12em; color: #fff; background: var(--teal, #03617A); border-radius: 5px; padding: 3px 8px; vertical-align: middle; }
  .mn-grouplab { font: 700 10.5px 'Instrument Sans', sans-serif; letter-spacing: .22em; text-transform: uppercase; color: var(--brass, #8a6c26); margin: 26px 0 4px; }
  .mn-butter { font-size: clamp(24px, 5.4vw, 30px) !important; padding: 15px 4px !important; }
  .mn-tiles { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 6px 0 10px; }
  .mn-tile { position: relative; background: #fff; border: 1px solid var(--line, rgba(26,35,50,.12)); border-radius: 12px; padding: 18px 16px 14px; text-decoration: none; color: var(--ink, #1a2332); min-height: 104px; display: flex; flex-direction: column; }
  .mn-tile .t { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; line-height: 1.12; }
  .mn-tile .arr { margin-top: auto; color: var(--teal, #03617A); }
  .mn-tile.hero { background: var(--teal, #03617A); border-color: var(--teal, #03617A); color: #fff; }
  .mn-tile.hero .arr { color: #fff; }
  .mn-tile .mn-badge { position: absolute; top: 10px; right: 10px; background: var(--brass, #8a6c26); }
  .mn-row { display: block; padding: 13px 4px; font-size: 17px; }
  .mn-today { background: var(--teal, #03617A); color: #fff; border-radius: 14px; padding: 18px; margin: 8px 0 6px; }
  .mn-today .lab { font: 700 10px 'Instrument Sans', sans-serif; letter-spacing: .2em; text-transform: uppercase; opacity: .8; }
  .mn-today .big { font-family: 'Instrument Sans', sans-serif; font-size: 17px; font-weight: 600; margin-top: 6px; line-height: 1.4; font-variant-numeric: tabular-nums; }
  .mn-live { display: inline-block; margin-top: 12px; color: #fff; border: 1px solid rgba(255,255,255,.45); border-radius: 7px; padding: 8px 12px; font: 700 11px 'Instrument Sans', sans-serif; letter-spacing: .1em; text-transform: uppercase; text-decoration: none; }


  /* Inline admin quick-access dropdown (paired with MENU when signed in) */
  .site-menu-actions { display: inline-flex; align-items: center; gap: 18px; }
  .site-admin-dropdown { position: relative; }
  .site-menu-admin-pill {
    display: inline-flex; align-items: center; gap: 6px;
    background: transparent; border: 0; padding: 6px 2px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px; font-weight: 600; line-height: 1;
    letter-spacing: 0.16em; text-transform: uppercase;
    color: var(--ink, #1a2332);
    text-decoration: none; cursor: pointer; white-space: nowrap;
    transition: color 0.15s;
  }
  .site-menu-admin-pill:hover, .site-menu-admin-pill:focus-visible {
    color: var(--teal, #03617A); outline: none;
  }
  .site-menu-admin-pill .chev { opacity: 0.7; transition: transform 0.2s; }
  .site-menu-admin-pill[aria-expanded="true"] .chev { transform: rotate(180deg); opacity: 1; }
  .site-admin-panel {
    display: none;
    position: absolute; top: calc(100% + 10px); right: 0;
    min-width: 240px;
    background: var(--parchment, #fefcef);
    border: 1px solid color-mix(in srgb, var(--ink) 12%, transparent);
    border-radius: 12px;
    box-shadow: 0 18px 40px -10px color-mix(in srgb, var(--ink) 22%, transparent);
    padding: 8px;
    z-index: 250;
  }
  .site-admin-panel.open { display: block; }
  .site-admin-item {
    display: block; width: 100%;
    padding: 10px 14px;
    background: transparent; border: 0; border-radius: 8px;
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px; font-weight: 500; line-height: 1.2;
    color: var(--ink, #1a2332);
    text-decoration: none; text-align: left; cursor: pointer;
    transition: color 0.12s, background 0.12s;
  }
  .site-admin-item:hover, .site-admin-item:focus-visible {
    color: var(--teal, #03617A); background: color-mix(in srgb, var(--teal) 5%, transparent); outline: none;
  }
  .site-admin-item-danger { color: #a82a1f; }
  .site-admin-item-danger:hover { color: #c0392b; background: rgba(168,42,31,0.05); }
  .site-admin-divider { height: 1px; background: color-mix(in srgb, var(--ink) 10%, transparent); margin: 6px 4px; }
  .site-admin-user {
    padding: 6px 14px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--ink, #1a2332); opacity: 0.55;
  }
  @media (max-width: 480px) {
    .site-admin-panel { right: -8px; min-width: 200px; }
  }

  /* Theme picker show/hide */
  #theme-pick-pop.theme-pop { display: none; }
  #theme-pick-pop.theme-pop.open { display: block; }

  /* Admin unread badge — pulses when a prayer request is waiting (PowerBook sleep light) */
  .site-admin-pill-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 18px; height: 18px;
    padding: 0 5px; margin-left: 4px;
    background: #d12b1f; color: #fff;
    border-radius: 999px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; font-weight: 700; letter-spacing: 0;
    line-height: 1;
  }
  .site-admin-pill-badge.pulse {
    animation: siteAdminPulse 2.4s ease-in-out infinite;
  }
  @keyframes siteAdminPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(209, 43, 31, 0.55); transform: scale(1); }
    50%      { box-shadow: 0 0 0 6px rgba(209, 43, 31, 0);    transform: scale(1.05); }
  }

  /* Quick links list inside admin dropdown */
  .site-admin-item-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
  .site-admin-item-count {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
    color: #fff; background: #d12b1f;
    border-radius: 999px; padding: 2px 7px; min-width: 18px; text-align: center;
  }

  /* ── instant search modal (opens from the search icon/pill, no page hop) ── */
  .sm-ov { position: fixed; inset: 0; z-index: 300; background: rgba(26,35,50,.5); display: none; align-items: flex-start; justify-content: center; padding: clamp(20px,8vh,90px) 16px 20px; backdrop-filter: blur(3px); }
  .sm-ov.open { display: flex; }
  .sm-box { background: var(--parchment, #fefcef); width: 100%; max-width: 560px; border-radius: 16px; box-shadow: 0 30px 70px rgba(26,35,50,.35); overflow: hidden; font-family: 'Instrument Sans', sans-serif; }
  .sm-box input { width: 100%; font: 500 17px 'Instrument Sans', sans-serif; padding: 18px 20px; border: 0; border-bottom: 1px solid rgba(26,35,50,.12); background: #fff; color: var(--ink, #1a2332); outline: none; }
  .sm-res { max-height: min(52vh, 430px); overflow-y: auto; padding: 8px; }
  .sm-hit { display: block; padding: 12px 14px; border-radius: 10px; text-decoration: none; }
  .sm-hit:hover, .sm-hit.sel { background: color-mix(in srgb, var(--teal, #03617A) 8%, #fff); }
  .sm-hit .k { font: 700 9px 'Instrument Sans'; letter-spacing: .14em; text-transform: uppercase; color: var(--brass, #8a6c26); }
  .sm-hit .t { font-size: 15px; font-weight: 600; color: var(--ink, #1a2332); margin-top: 2px; }
  .sm-hit .d { font-size: 12.5px; color: var(--ink-soft, #4a5568); margin-top: 2px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
  .sm-empty { text-align: center; color: var(--ink-soft, #4a5568); font-size: 13.5px; padding: 26px 10px; }
  .sm-foot { border-top: 1px solid rgba(26,35,50,.1); padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--ink-soft, #6b7280); }
  .sm-foot a { color: var(--teal, #03617A); text-decoration: none; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
  .site-menu-search-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px;
    color: var(--ink, #1a2332);
    text-decoration: none;
    border-radius: 6px;
    transition: color 0.15s, background 0.15s;
  }
  .site-menu-search-icon:hover, .site-menu-search-icon:focus-visible {
    color: var(--teal, #03617A); background: rgba(0,0,0,0.04); outline: none;
  }
  .site-menu-search-icon svg { display: block; }
</style>

<header class="site-menu-header">
  <div class="site-menu-header-inner">
    <a href="{{ url('/') }}" class="site-menu-brand"><em>Shalom</em></a>
    <div class="site-menu-actions">
      @auth
        @if (in_array(auth()->user()->role ?? null, ['clerk', 'super_admin'], true))
          <div class="site-admin-dropdown">
            <button class="site-menu-admin-pill" id="site-admin-trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="site-admin-panel">
              <span>Admin</span>
              @php
                $unreadPrayersCount  = \App\Models\PrayerRequest::whereNull('read_at')->count();
                $unreadContactsCount = \App\Models\ContactMessage::whereNull('read_at')->count();
                $unreadTotal         = $unreadPrayersCount + $unreadContactsCount;
              @endphp
              @if ($unreadTotal > 0)
                <span class="site-admin-pill-badge @if($unreadPrayersCount > 0) pulse @endif">{{ $unreadTotal }}</span>
              @endif
              <svg class="chev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="site-admin-panel" id="site-admin-panel" role="menu" aria-hidden="true">
              <a class="site-admin-item" href="{{ route('admin.messages') }}" role="menuitem">
                <span class="site-admin-item-row">
                  <span>Messages</span>
                  @if ($unreadTotal > 0)
                    <span class="site-admin-item-count">{{ $unreadTotal }}</span>
                  @endif
                </span>
              </a>
              <a class="site-admin-item" href="{{ url('/welcome') }}" role="menuitem">Bulletin</a>
              <a class="site-admin-item" href="{{ route('guide') }}" role="menuitem">Field Guide</a>
              <a class="site-admin-item" href="{{ route('schedule.index') }}" role="menuitem">Department schedule</a>
              <a class="site-admin-item" href="{{ route('admin.hub') }}" role="menuitem">Admin hub</a>
              <div class="site-admin-divider"></div>
              <div class="site-admin-user">{{ auth()->user()->name }}</div>
              <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="site-admin-item site-admin-item-danger" role="menuitem">Sign out</button>
              </form>
            </div>
          </div>
        @endif
      @endauth
      <a href="{{ route('search') }}" class="site-menu-search-icon" aria-label="Search Shalom" title="Search Shalom">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </a>
      <button class="site-menu-trigger" id="site-menu-btn" type="button" aria-label="Open menu" aria-expanded="false">
      <span class="site-menu-trigger-label">Menu</span>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>
    </div>
  </div>
</header>

<div class="site-menu-overlay" id="site-menu-overlay" role="dialog" aria-modal="true" aria-label="Site menu">
  <div class="site-menu-overlay-header">
    <a href="{{ url('/') }}" class="site-menu-brand"><em>Shalom</em></a>
    <button class="site-menu-close" id="site-menu-close" type="button" aria-label="Close menu">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
        <line x1="5" y1="5" x2="19" y2="19"/>
        <line x1="19" y1="5" x2="5" y2="19"/>
      </svg>
    </button>
  </div>

  <nav class="site-menu-nav">
@include('partials.menu-nav')

    <button type="button" class="site-menu-link a2hs" id="a2hsBtn" hidden>📲 Get the app <span class="arrow">@include('partials._ar')</span></button>

    @foreach (\Illuminate\Support\Facades\Cache::remember('intake_menu_forms', 300, fn() => \App\Models\IntakeForm::menuForms()) as $mf)
      <a class="site-menu-link" href="{{ url('/intake/' . $mf->slug) }}">{{ $mf->menuLabel() }} <span class="arrow">@include('partials._ar')</span></a>
    @endforeach

    @auth
      @if ((auth()->user()->role === 'super_admin' || auth()->user()->role === 'clerk') && ($bulletin ?? null))
        {{-- Bulletin tools — only on the bulletin page when admin is editing --}}
        <div class="site-menu-section">
          <button class="site-menu-section-toggle" type="button" aria-expanded="false">
            Bulletin tools
            <svg class="chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="site-menu-section-body">
            <div class="site-menu-sub-list">
              <button class="site-menu-sub-link site-menu-sub-link-form" data-standard-url="{{ route('bulletins.load-standard', $bulletin) }}" type="button">{{ $bulletin->lines->isEmpty() ? 'Load standard order' : 'Reset to default' }}</button>
              <button class="site-menu-sub-link site-menu-sub-link-form" data-next-week-url="{{ route('bulletins.next-week') }}" type="button">Next Sabbath</button>
              <button class="site-menu-sub-link site-menu-sub-link-form" id="new-event-series-btn" type="button">Event series</button>
              <a class="site-menu-sub-link" href="{{ route('bulletins.pdf', $bulletin) }}" target="_blank" rel="noopener">Download PDF</a>
              <a class="site-menu-sub-link" href="{{ route('bulletins.pdf', $bulletin) }}?layout=2up" target="_blank" rel="noopener">Download 2-up (print 2-sided, cut in half)</a>
              @if ($bulletin->hasAvailablePreviousVersion())
                <a class="site-menu-sub-link" href="{{ route('bulletins.pdf', $bulletin) }}?version=previous" target="_blank" rel="noopener">Previous PDF</a>
              @endif
              <a class="site-menu-sub-link" href="/?preview=1">Preview as public</a>
{{-- THEME_PICKER_MOVED — moved to welcome.blade.php (visible row below the bulletin toolbar). 2026-05-22 --}}

              @if (auth()->user()->role === 'super_admin')
                <label class="site-menu-sub-link" style="display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer;">
                  <span>Show all week (override)</span>
                  <input type="checkbox" id="always-show-toggle" data-update-url="{{ route('bulletins.update', $bulletin) }}" @if(($bulletin->always_show_during_week ?? false)) checked @endif style="transform:scale(1.2); accent-color:var(--teal);">
                </label>
                <button class="site-menu-sub-link site-menu-sub-link-form" id="bulletin-delete-btn" type="button"
                  style="color:#a82a1f;"
                  data-delete-url="{{ route('bulletins.destroy', $bulletin) }}"
                  data-bulletin-title="{{ $bulletin->title ?? optional($bulletin->service_date)->format('M j, Y') }}"
                  data-service-date="{{ $bulletin->service_date?->toDateString() }}"
                  data-service-date-formatted="{{ $bulletin->service_date?->format('l, F j') }}">Delete this bulletin</button>
              @endif
            </div>
          </div>
        </div>
      @endif

      @if (auth()->user()->role === 'super_admin' || auth()->user()->role === 'clerk')
        {{-- Admin tools — available on every page when signed in as admin --}}
        <div class="site-menu-section">
          <button class="site-menu-section-toggle" type="button" aria-expanded="false">
            Admin tools
            <svg class="chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="site-menu-section-body">
            <div class="site-menu-sub-list">
              <button class="site-menu-sub-link site-menu-sub-link-form" id="search-btn-mobile" type="button" data-action="search">Search</button>
              <a class="site-menu-sub-link" href="{{ route('schedule.index') }}">Department schedule</a>
              <button class="site-menu-sub-link site-menu-sub-link-form" id="manage-names-btn" type="button">Manage names</button>
              <a class="site-menu-sub-link" href="{{ route('admin.hub') }}">Admin hub</a>
            </div>
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="site-menu-link site-menu-link-signout site-menu-sub-link-form">Sign out <span class="arrow">@include('partials._ar')</span></button>
      </form>
    @else
      <a class="site-menu-link" href="{{ route('login') }}">Sign in <span class="arrow">@include('partials._ar')</span></a>
    @endauth
  </nav>
</div>

<script>
  (function() {
    // Admin dropdown
    const aBtn = document.getElementById('site-admin-trigger');
    const aPanel = document.getElementById('site-admin-panel');
    if (aBtn && aPanel) {
      const aOpen = () => { aPanel.classList.add('open'); aBtn.setAttribute('aria-expanded','true'); aPanel.setAttribute('aria-hidden','false'); };
      const aClose = () => { aPanel.classList.remove('open'); aBtn.setAttribute('aria-expanded','false'); aPanel.setAttribute('aria-hidden','true'); };
      aBtn.addEventListener('click', e => { e.stopPropagation(); aPanel.classList.contains('open') ? aClose() : aOpen(); });
      document.addEventListener('click', e => { if (!aPanel.contains(e.target) && !aBtn.contains(e.target)) aClose(); });
      document.addEventListener('keydown', e => { if (e.key === 'Escape') aClose(); });
    }

    const btn = document.getElementById('site-menu-btn');
    const overlay = document.getElementById('site-menu-overlay');
    const closeBtn = document.getElementById('site-menu-close');
    if (!btn || !overlay) return;

    const open = () => {
      overlay.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    };
    const close = () => {
      overlay.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
      overlay.querySelectorAll('.site-menu-section.open').forEach(s => s.classList.remove('open'));
    };

    btn.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

    // Accordion: only one section open at a time
    overlay.querySelectorAll('.site-menu-section-toggle').forEach(t => {
      t.addEventListener('click', () => {
        const section = t.closest('.site-menu-section');
        const wasOpen = section.classList.contains('open');
        overlay.querySelectorAll('.site-menu-section.open').forEach(s => s.classList.remove('open'));
        if (!wasOpen) {
          section.classList.add('open');
          t.setAttribute('aria-expanded', 'true');
        } else {
          t.setAttribute('aria-expanded', 'false');
        }
      });
    });
  })();

// ── PWA icon badge (clerks): unread inbox count → home-screen icon.
//    Sets on every open; persists after close. Live-while-closed needs push (future).
@auth
@if (in_array(auth()->user()->role, ['super_admin','clerk']))
if ('setAppBadge' in navigator) {
  fetch('{{ route('admin.badge-count') }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => d.n > 0 ? navigator.setAppBadge(d.n) : navigator.clearAppBadge())
    .catch(() => {});
}
@endif
@endauth

// ── First-open notification offer (installed app, clerks, once) ──
@auth
@if (in_array(auth()->user()->role, ['super_admin','clerk']))
(function () {
  const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
  if (!standalone || !('PushManager' in window) || !('serviceWorker' in navigator)) return;
  if (localStorage.getItem('pushPromptDone')) return;
  if (Notification.permission === 'denied') return;
  navigator.serviceWorker.register('/sw.js').then(reg => reg.pushManager.getSubscription()).then(sub => {
    if (sub) { localStorage.setItem('pushPromptDone', '1'); return; }
    const card = document.createElement('div');
    card.id = 'pushPrompt';
    card.innerHTML = '<style>#pushPrompt{position:fixed;left:14px;right:14px;bottom:16px;z-index:120;background:#fff;border:1px solid rgba(26,35,50,.14);border-radius:14px;padding:18px;box-shadow:0 18px 44px rgba(26,35,50,.18);font-family:\'Instrument Sans\',sans-serif;max-width:430px;margin:0 auto;animation:ppUp .3s ease}@keyframes ppUp{from{transform:translateY(16px);opacity:0}to{transform:none;opacity:1}}#pushPrompt .t{font-weight:700;font-size:15px;color:#1a2332}#pushPrompt .d{font-size:13px;color:#4a5568;margin-top:5px;line-height:1.55}#pushPrompt .row{display:flex;gap:8px;margin-top:14px}#pushPrompt button{flex:1;font:700 11px \'Instrument Sans\';letter-spacing:.1em;text-transform:uppercase;border-radius:8px;padding:12px;cursor:pointer}#pushPrompt .yes{background:#03617A;border:0;color:#fff}#pushPrompt .no{background:#fff;border:1px solid rgba(26,35,50,.14);color:#4a5568}</style>'
      + '<div class="t">🔔 Know the moment someone reaches out</div>'
      + '<div class="d">Get a buzz when a prayer request or message arrives — even with the app closed.</div>'
      + '<div class="row"><button class="yes" type="button">Turn on</button><button class="no" type="button">Not now</button></div>';
    document.body.appendChild(card);
    const done = () => { localStorage.setItem('pushPromptDone', '1'); card.remove(); };
    card.querySelector('.no').addEventListener('click', done);
    card.querySelector('.yes').addEventListener('click', async () => {
      try {
        const perm = await Notification.requestPermission();
        if (perm !== 'granted') { done(); return; }
        const b64 = @json(config('services.vapid.public'));
        const pad = '='.repeat((4 - b64.length % 4) % 4);
        const raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
        const key = Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
        const sub = await reg_.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: key });
        await fetch(@json(route('admin.push.subscribe')), { method: 'POST', headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(sub.toJSON()) });
        card.querySelector('.t').textContent = '🔔 You\'re on the list';
        card.querySelector('.d').textContent = 'Prayer requests and messages will find you now.';
        card.querySelector('.row').remove();
        setTimeout(done, 2600);
      } catch (e) { done(); }
    });
    let reg_; navigator.serviceWorker.ready.then(r => reg_ = r);
  }).catch(() => {});
})();
@endif
@endauth

// ── "Get the app": real install prompt on Android, guided card on iOS ──
(function () {
  const btn = document.getElementById('a2hsBtn');
  if (!btn) return;
  const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
  if (standalone) return;                       // already installed
  let deferred = null;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault(); deferred = e; btn.hidden = false;
  });
  const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
  if (isIOS) btn.hidden = false;
  btn.addEventListener('click', async () => {
    if (deferred) { deferred.prompt(); deferred = null; return; }   // Android: 2 taps total
    // iOS: show the two-tap map
    let card = document.getElementById('a2hsCard');
    if (!card) {
      card = document.createElement('div');
      card.id = 'a2hsCard'; card.className = 'a2hs-card';
      card.innerHTML = '<div class="a2hs-inner">'
        + '<h3>📲 Put Shalom on your home screen</h3>'
        + '<div class="a2hs-step"><span class="a2hs-num">1</span><span>Tap the <b>Share</b> button <svg class="a2hs-glyph" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#03617A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg> at the bottom of Safari</span></div>'
        + '<div class="a2hs-step"><span class="a2hs-num">2</span><span>Scroll a little and tap <b>Add to Home Screen</b></span></div>'
        + '<button type="button" class="a2hs-close">Got it</button></div>';
      document.body.appendChild(card);
      card.addEventListener('click', (e) => { if (e.target === card || e.target.classList.contains('a2hs-close')) card.classList.remove('open'); });
    }
    card.classList.add('open');
  });
})();

// ── Instant search modal: the icon/pill opens it; /search stays as fallback ──
(function () {
  let ov, input, res, ms = null, corpus = null, sel = -1, hits = [];
  const KINDS = { page: 'Page', 'peace-note': 'Peace note', bulletin: 'Bulletin', hymn: 'Hymn', event: 'Event', message: 'Message' };
  function build() {
    if (ov) return;
    ov = document.createElement('div');
    ov.className = 'sm-ov';
    ov.innerHTML = '<div class="sm-box" role="dialog" aria-label="Search Shalom">'
      + '<input type="search" placeholder="Search Shalom — pages, hymns, bulletins…" autocomplete="off">'
      + '<div class="sm-res"><div class="sm-empty">Type to search the whole site.</div></div>'
      + '<div class="sm-foot"><span>↑↓ choose · Enter opens · Esc closes</span><a href="{{ route('search') }}">Full page</a></div></div>';
    document.body.appendChild(ov);
    input = ov.querySelector('input'); res = ov.querySelector('.sm-res');
    ov.addEventListener('click', e => { if (e.target === ov) close(); });
    input.addEventListener('input', run);
    input.addEventListener('keydown', e => {
      if (e.key === 'Escape') { close(); }
      if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
      if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
      if (e.key === 'Enter' && hits[Math.max(sel, 0)]) location.href = hits[Math.max(sel, 0)].url;
    });
  }
  function move(d) {
    sel = Math.min(hits.length - 1, Math.max(0, sel + d));
    [...res.querySelectorAll('.sm-hit')].forEach((el, i) => el.classList.toggle('sel', i === sel));
  }
  async function ensureEngine() {
    if (ms) return;
    if (!window.MiniSearch) await new Promise((ok, no) => {
      const sc = document.createElement('script');
      sc.src = 'https://cdn.jsdelivr.net/npm/minisearch@7.1.1/dist/umd/index.min.js';
      sc.onload = ok; sc.onerror = no; document.head.appendChild(sc);
    });
    corpus = await (await fetch('/api/search-corpus', { headers: { 'Accept': 'application/json' } })).json();
    ms = new MiniSearch({ fields: ['title', 'desc'], storeFields: ['kind', 'title', 'desc', 'url'],
      searchOptions: { boost: { title: 3 }, prefix: true, fuzzy: 0.2 } });
    ms.addAll(corpus);
  }
  function run() {
    const q = input.value.trim(); sel = -1;
    if (q.length < 2) { res.innerHTML = '<div class="sm-empty">Type to search the whole site.</div>'; hits = []; return; }
    if (!ms) { res.innerHTML = '<div class="sm-empty">Loading the index…</div>'; return; }
    hits = ms.search(q).slice(0, 10);
    res.innerHTML = hits.length ? hits.map(h =>
      '<a class="sm-hit" href="' + h.url + '"><div class="k">' + (KINDS[h.kind] || h.kind) + '</div><div class="t">' + h.title.replace(/</g, '&lt;') + '</div><div class="d">' + (h.desc || '').replace(/</g, '&lt;') + '</div></a>'
    ).join('') : '<div class="sm-empty">Nothing found — try simpler words.</div>';
  }
  function open(e) {
    if (e) e.preventDefault();
    build(); ov.classList.add('open');
    setTimeout(() => input.focus(), 50);
    ensureEngine().then(run).catch(() => { res.innerHTML = '<div class="sm-empty">Search is napping — <a href="{{ route('search') }}">use the full page</a>.</div>'; });
  }
  function close() { ov.classList.remove('open'); }
  document.addEventListener('click', (e) => {
    const t = e.target.closest('.site-menu-search-icon, #search-btn-mobile, [data-action="search"], .search-float, #search-float-link, a[href$="/search"]');
    if (t) open(e);
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && ov) close(); });
})();
</script>
