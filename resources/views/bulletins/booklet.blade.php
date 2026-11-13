{{-- The booklet you can flip through on the site (2026-11-13). Same pages as the folded
     print (bulletins.pdf-booklet), both built by App\Support\BookletLayout.
     Two leaves turn on a centre spine:
       leaf 1: front = cover,          back = order of service (left page)
       leaf 2: front = order, cont'd,  back = announcements (back page)
     Computer: three spreads (closed cover, open inside, closed back).
     Phone: one page at a time; the book slides to the page and turns between spreads. --}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
<link rel="shortcut icon" href="/favicon.ico?v=2">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $L['title'] }} · {{ $L['date']->format('M j') }} — The Church of Peace</title>
<meta name="description" content="The program for {{ $L['title'] }}, {{ $L['date']->format('l, F j, Y') }}, at Shalom Seventh-day Adventist Church. Flip through it or print it.">
<meta property="og:title" content="{{ $L['title'] }} · {{ $L['date']->format('F j') }}">
<meta property="og:description" content="Flip through the program, or print it and fold it in half.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,500;0,600;0,700;0,800;1,500&family=Cormorant+Garamond:wght@500;600&family=Instrument+Sans:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
<style>
  /* ── Looks. Everything below takes its colour and type from these tokens. ──
     default:    the site's season colours.
     pathfinder: the pages are light uniform khaki on the site's own ground; gold scarf accent, emblem red. Set with cover.look. */
  body.look-default {
    --bk-ground: var(--parchment); --bk-ink: var(--ink); --bk-soft: var(--ink-soft); --bk-line: var(--line);
    --bk-paper: #fff; --bk-paper-ink: #1a2332; --bk-paper-soft: #5f6876;
    --bk-accent: var(--teal); --bk-accent-2: var(--teal);
    --bk-display: 'Cormorant Garamond', Georgia, serif; --bk-display-weight: 600; --bk-display-track: -0.01em;
  }
  body.look-pathfinder {
    --bk-ground: var(--parchment); --bk-ink: var(--ink); --bk-soft: var(--ink-soft); --bk-line: var(--line);
    --bk-paper: #e4d3b0; --bk-paper-ink: #2a2014; --bk-paper-soft: #64543a;
    --bk-accent: #8a5e17; --bk-accent-2: #9a2c23;
    --bk-display: 'Archivo', 'Instrument Sans', system-ui, sans-serif; --bk-display-weight: 800; --bk-display-track: -0.025em;
  }
  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; overflow-x: clip; overscroll-behavior-x: none; }
  body { background: var(--bk-ground); color: var(--bk-ink); font-family: 'Instrument Sans', system-ui, sans-serif; -webkit-font-smoothing: antialiased; min-height: 100dvh; }
  /* White wall behind the khaki pages, so lilac never sits against the tan. Light mode only. */
  html:not(.dark):has(body.look-pathfinder) { background: #fff !important; }
  html:not(.dark) body.look-pathfinder { --bk-ground: #fff; background: #fff; }

  /* Khaki paper with a faint grain, like card stock. */
  .look-pathfinder .bk-face {
    background-image:
      radial-gradient(130% 80% at 50% 0%, rgba(255, 248, 230, 0.45), rgba(255, 248, 230, 0) 65%),
      url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.3 0 0 0 0 0.22 0 0 0 0 0.1 0 0 0 0.12 0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  }

  .bk-top { max-width: 1100px; margin: 0 auto; padding: 14px calc(18px + env(safe-area-inset-right)) 0 calc(18px + env(safe-area-inset-left)); }
  .bk-top a { display: inline-flex; align-items: center; gap: 6px; color: var(--bk-soft); text-decoration: none; font-size: 15px; font-weight: 500; }
  .bk-top a:hover { color: var(--bk-accent); }

  /* ── Sizing: one page is 5.5 × 8.5 in. --pw is a page's width on screen. ── */
  .bk-stage {
    --pw: min(calc(100vw - 36px), 460px);
    position: relative; width: 100%; height: calc(var(--pw) * 8.5 / 5.5);
    margin: 22px 0 0; overflow: clip;
    touch-action: pan-y; user-select: none; -webkit-user-select: none; cursor: pointer;
    animation: bk-rise 0.8s cubic-bezier(0.2, 0.7, 0.2, 1) both;
  }
  @media (min-width: 900px) and (min-height: 740px) {
    .bk-stage { --pw: min(calc((100vw - 120px) / 2), calc((100dvh - 230px) * 5.5 / 8.5), 520px); margin-top: 30px; overflow: visible; }
  }
  /* Whole-pixel page widths, so the book never sits on a half pixel and text stays sharp. */
  @supports (width: round(down, 10.5px, 2px)) {
    .bk-stage { --pw: round(down, min(calc(100vw - 36px), 460px), 2px); }
    @media (min-width: 900px) and (min-height: 740px) {
      .bk-stage { --pw: round(down, min(calc((100vw - 120px) / 2), calc((100dvh - 230px) * 5.5 / 8.5), 520px), 2px); }
    }
  }
  @supports (left: round(down, 50%, 1px)) {
    .bk-stage .bk-book { left: round(down, 50%, 1px); }
  }
  @keyframes bk-rise { from { opacity: 0; transform: translateY(18px); } }
  /* Perspective only while a page is turning: flat pages at rest stay crisp. */
  .bk-stage.moving { perspective: 2600px; }
  .bk-book {
    position: absolute; top: 0; left: 50%; width: calc(var(--pw) * 2); height: 100%;
    margin-left: calc(var(--pw) * -1);
    transform-style: preserve-3d;
    transform: translateX(-25%);
    transition: transform 0.85s cubic-bezier(0.32, 0.08, 0.2, 1);
  }
  .bk-leaf {
    position: absolute; top: 0; left: 50%; width: 50%; height: 100%;
    transform-style: preserve-3d; transform-origin: left center;
    transition: transform 0.85s cubic-bezier(0.32, 0.08, 0.2, 1);
  }
  .bk-leaf.turned { transform: rotateY(-180deg); }
  .bk-face {
    position: absolute; inset: 0; overflow: hidden;
    backface-visibility: hidden; -webkit-backface-visibility: hidden;
    background: var(--bk-paper); color: var(--bk-paper-ink);
    box-shadow: 0 28px 54px -30px rgba(60, 42, 14, 0.5), 0 2px 6px rgba(60, 42, 14, 0.08);
    container-type: size;
  }
  .bk-face.back { transform: rotateY(180deg); }

  /* The fold: a soft shadow along the spine edge of each page. */
  .bk-face::after { content: ''; position: absolute; inset: 0; pointer-events: none; }
  .bk-face.front::after { background: linear-gradient(to right, rgba(60,42,14,0.11), rgba(60,42,14,0) 7%); }
  .bk-face.back::after  { background: linear-gradient(to left,  rgba(60,42,14,0.11), rgba(60,42,14,0) 7%); }
  .bk-face.front { border-radius: 0 4px 4px 0; }
  .bk-face.back  { border-radius: 4px 0 0 4px; }

  /* ── Page content. --u is one printed point, so 12pt type is calc(var(--u) * 12). ── */
  .pg-in { --u: min(100cqw / 360, 100cqh / 556); position: relative; height: 100%; padding: calc(var(--u) * 32) calc(var(--u) * 34); }

  .bk-cover { text-align: center; }
  /* Pathfinder cover: a gold band across the top, like the scarf. */
  .look-pathfinder .bk-cover::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: calc(var(--u) * 7); background: linear-gradient(90deg, #b8842a, #d9a944 50%, #b8842a); }
  /* The church's own "shalom" wordmark (the site's Xtreem face), in black. */
  @font-face { font-family: 'Xtreem'; src: url('{{ asset('fonts/XtreemMedium.ttf') }}') format('truetype'); font-weight: 500; font-display: swap; }
  .bk-logo { font-family: 'Xtreem', 'Archivo', sans-serif; font-weight: 500; font-size: calc(var(--u) * 40); line-height: 0.8; letter-spacing: -0.02em; color: #14100a; padding-top: calc(var(--u) * 12); }
  .bk-emblems { display: flex; justify-content: center; align-items: center; gap: calc(var(--u) * 22); margin-top: calc(var(--u) * 28); }
  .bk-emblems img { height: calc(var(--u) * 100); width: auto; pointer-events: none; filter: drop-shadow(0 calc(var(--u) * 4) calc(var(--u) * 6) rgba(60, 42, 14, 0.18)); animation: bk-pop 0.7s 0.35s cubic-bezier(0.2, 0.8, 0.2, 1) both; }
  .bk-emblems img + img { animation-delay: 0.48s; }
  @keyframes bk-pop { from { opacity: 0; transform: translateY(calc(var(--u) * 10)) scale(0.94); } }
  .bk-kicker { margin-top: calc(var(--u) * 28); font-family: var(--bk-display); font-weight: 600; font-size: calc(var(--u) * 14); color: var(--bk-accent-2); letter-spacing: -0.005em; }
  .look-default .bk-kicker { font-style: italic; font-weight: 500; font-size: calc(var(--u) * 19); }
  .bk-title { margin-top: calc(var(--u) * 6); font-family: var(--bk-display); font-weight: var(--bk-display-weight); font-size: calc(var(--u) * 33); line-height: 1.02; letter-spacing: var(--bk-display-track); text-wrap: balance; }
  .bk-date { margin-top: calc(var(--u) * 12); font-size: calc(var(--u) * 11.5); font-weight: 500; color: var(--bk-paper-soft); }
  .bk-rule { width: calc(var(--u) * 46); height: calc(var(--u) * 3); margin: calc(var(--u) * 24) auto calc(var(--u) * 20); border-radius: 99px; background: var(--bk-accent); }
  .bk-theme { font-family: var(--bk-display); font-style: italic; font-weight: 500; font-size: calc(var(--u) * 15.5); line-height: 1.35; padding: 0 calc(var(--u) * 26); text-wrap: balance; letter-spacing: -0.01em; }
  .look-default .bk-theme { font-size: calc(var(--u) * 17); line-height: 1.3; }
  .bk-ref { margin-top: calc(var(--u) * 8); font-size: calc(var(--u) * 10); font-weight: 600; color: var(--bk-accent); }
  .bk-addr { position: absolute; left: 0; right: 0; bottom: calc(var(--u) * 30); font-size: calc(var(--u) * 8.5); line-height: 1.5; color: var(--bk-paper-soft); }

  .bk-program { font-size: calc(var(--u) * var(--pt) * 0.88); }
  .bk-ph { text-align: center; font-family: var(--bk-display); font-weight: var(--bk-display-weight); letter-spacing: var(--bk-display-track); font-size: calc(var(--u) * 18); line-height: 1.2; }
  .bk-ph-sub { text-align: center; font-size: calc(var(--u) * 9); color: var(--bk-paper-soft); margin: calc(var(--u) * 4) 0 calc(var(--u) * 16); }
  .bk-row { display: flex; align-items: baseline; gap: calc(var(--u) * 5); margin: calc(var(--u) * var(--gap)) 0; line-height: 1.3; }
  .bk-part { font-weight: 600; white-space: nowrap; }
  .bk-dots { flex: 1 1 auto; min-width: calc(var(--u) * 10); border-bottom: calc(var(--u) * 1.2) dotted color-mix(in srgb, var(--bk-accent) 55%, transparent); transform: translateY(calc(var(--u) * -3)); }
  .bk-who { text-align: right; min-width: 0; color: var(--bk-paper-ink); }
  .bk-who.grp { font-style: italic; }
  .bk-under { text-align: center; font-style: italic; color: var(--bk-paper-soft); margin: calc(var(--u) * (3 - var(--gap))) 0 calc(var(--u) * var(--gap)); }

  .bk-back { font-size: calc(var(--u) * 9); line-height: 1.35; }
  .bk-back-title { text-align: center; font-family: var(--bk-display); font-weight: var(--bk-display-weight); letter-spacing: var(--bk-display-track); font-size: calc(var(--u) * 19); margin-bottom: calc(var(--u) * 10); }
  .bk-sec { font-weight: 600; font-size: calc(var(--u) * 10.5); margin-bottom: calc(var(--u) * 2); color: var(--bk-paper-ink); }
  .bk-ann { margin-bottom: calc(var(--u) * 7); }
  .bk-ann ul { margin: 0; padding-left: calc(var(--u) * 16); list-style: circle; }
  .bk-ann li { margin: calc(var(--u) * 0.6) 0; }
  .bk-ann li::marker { color: var(--bk-accent); }
  .bk-ann li.bf { list-style: disc; }
  .bk-mission { text-align: center; margin-bottom: calc(var(--u) * 10); }
  .bk-mission p { margin: 0; color: var(--bk-paper-soft); }
  .bk-offer { margin: calc(var(--u) * 6) 0; font-weight: 500; }
  .bk-pleasant { text-align: center; margin-top: calc(var(--u) * 12); font-family: var(--bk-display); font-weight: 600; font-size: calc(var(--u) * 11); color: var(--bk-accent-2); }

  /* ── Phone: paper proportions would make the type too small to read, so the pages
     use reading sizes (program about 15px) and grow as tall as the longest page. ── */
  @media not all and (min-width: 900px) and (min-height: 740px) {
    .bk-stage .pg-in { --u: clamp(1.15px, 100cqw / 260, 1.45px); height: auto; min-height: 100%; padding: calc(var(--u) * 24) calc(var(--u) * 16) calc(var(--u) * 64); }
    .bk-stage .bk-back { font-size: calc(var(--u) * 10); }
    .bk-stage .bk-title { font-size: calc(var(--u) * 30); }
    .bk-stage .bk-row { margin: calc(var(--u) * min(var(--gap), 12)) 0; }
    .bk-stage .bk-under { margin: calc(var(--u) * (3 - min(var(--gap), 12))) 0 calc(var(--u) * min(var(--gap), 12)); }
    .bk-stage .bk-addr { bottom: calc(var(--u) * 22); }
  }

  /* ── Controls ── */
  /* Phone: the arrows stay pinned to the bottom while you read; they let go once you pass the booklet. */
  @media not all and (min-width: 900px) and (min-height: 740px) {
    .bk-reader .bk-nav {
      position: sticky; bottom: 0; z-index: 5; margin-top: 14px;
      padding: 10px 18px calc(10px + env(safe-area-inset-bottom));
      background: color-mix(in srgb, var(--bk-ground) 82%, transparent);
      -webkit-backdrop-filter: blur(14px) saturate(160%); backdrop-filter: blur(14px) saturate(160%);
      border-top: 1px solid var(--bk-line);
    }
  }
  .bk-nav { display: flex; align-items: center; justify-content: center; gap: 18px; margin: 26px auto 0; padding: 0 18px; }
  .bk-arrow { width: 44px; height: 44px; display: inline-grid; place-items: center; border-radius: 999px; border: 1px solid var(--bk-line); background: color-mix(in srgb, #fff 55%, var(--bk-ground)); color: var(--bk-soft); cursor: pointer; transition: color 0.15s, border-color 0.15s, opacity 0.15s, transform 0.1s; }
  .bk-arrow:hover:not(:disabled) { color: var(--bk-accent); border-color: var(--bk-accent); }
  .bk-arrow:active:not(:disabled) { transform: scale(0.94); }
  .bk-arrow:disabled { opacity: 0.35; cursor: default; }
  .bk-arrow:focus-visible { outline: 2px solid var(--bk-accent); outline-offset: 2px; }
  .bk-where { min-width: 190px; text-align: center; font-size: 15px; font-weight: 500; color: var(--bk-soft); }
  .bk-foot { text-align: center; margin: 18px 0 calc(40px + env(safe-area-inset-bottom)); padding: 0 18px; }
  .bk-foot a { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 16px; border-radius: 6px; border: 1px solid var(--bk-line); color: var(--bk-ink); text-decoration: none; font-size: 14px; font-weight: 500; }
  .bk-foot a:hover { border-color: var(--bk-accent); color: var(--bk-accent); }
  .bk-foot p { margin: 10px 0 0; font-size: 13px; color: var(--bk-soft); }

  /* ── Your Sabbath ── */
  .sb { max-width: 560px; margin: 8px auto calc(56px + env(safe-area-inset-bottom)); padding: 30px calc(18px + env(safe-area-inset-right)) 0 calc(18px + env(safe-area-inset-left)); border-top: 1px solid var(--bk-line); }
  .sb h2 { margin: 0; font-family: 'Archivo', 'Instrument Sans', sans-serif; font-weight: 800; font-size: 28px; letter-spacing: -0.02em; color: var(--bk-ink); }
  .sb-lede { margin: 6px 0 22px; color: var(--bk-soft); font-size: 16px; line-height: 1.5; }
  .sb fieldset { border: 0; margin: 0 0 24px; padding: 0; min-width: 0; }
  .sb legend { padding: 0; font-weight: 600; font-size: 18px; color: var(--bk-ink); }
  .sb-hint { margin: 2px 0 12px; font-size: 14px; color: var(--bk-soft); }
  .sb-chips { display: flex; flex-wrap: wrap; gap: 10px; }
  .sb-chip { position: relative; display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 16px; border-radius: 8px; border: 1px solid var(--bk-line); background: color-mix(in srgb, #fff 60%, var(--bk-ground)); color: var(--bk-ink); font-size: 16px; cursor: pointer; transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.1s; -webkit-tap-highlight-color: transparent; }
  .sb-chip:active { transform: scale(0.97); }
  .sb-chip input { position: absolute; opacity: 0; width: 1px; height: 1px; }
  .sb-tick { display: none; }
  .sb-chip:has(input:checked) { background: #e4d3b0; border-color: #8a5e17; color: #2a2014; }
  .sb-chip:has(input:checked) .sb-tick { display: block; color: #8a5e17; }
  .sb-chip:has(input:focus-visible) { outline: 2px solid var(--bk-accent); outline-offset: 2px; }
  .sb-label { display: block; margin: 4px 0 8px; font-weight: 600; font-size: 18px; color: var(--bk-ink); }
  .sb-label span { font-weight: 400; font-size: 15px; color: var(--bk-soft); }
  .sb textarea, .sb input[name="first_name"] { width: 100%; font: 400 16px 'Instrument Sans', system-ui, sans-serif; color: var(--bk-ink); background: color-mix(in srgb, #fff 75%, var(--bk-ground)); border: 1px solid var(--bk-line); border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; }
  .sb textarea { resize: vertical; line-height: 1.5; }
  .sb textarea:focus, .sb input[name="first_name"]:focus { outline: 2px solid var(--bk-accent); outline-offset: 1px; }
  .sb-send { min-height: 46px; padding: 0 28px; border: 0; border-radius: 6px; background: #2a2014; color: #fff; font: 600 16px 'Instrument Sans', system-ui, sans-serif; cursor: pointer; }
  .sb-send:hover { background: #8a5e17; }
  .sb-hp { position: absolute; left: -9999px; width: 1px; height: 1px; }
  .sb-done { padding: 16px 18px; border-radius: 8px; background: #e4d3b0; color: #2a2014; font-size: 16px; }
  .sb-err { padding: 12px 14px; border-radius: 8px; background: #fdecea; color: #8a1f17; font-size: 15px; }

  @media (prefers-reduced-motion: reduce) {
    .bk-book, .bk-leaf { transition: none; }
    .bk-stage, .bk-emblems img { animation: none; }
  }
</style>
@include('partials.theme-vars')
</head>
@php $look = ($L['cover']['look'] ?? '') === 'pathfinder' ? 'pathfinder' : 'default'; @endphp
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}" class="look-{{ $look }}">

<div class="bk-top"><a href="{{ route('welcome') }}">@include('partials._arl') This week’s bulletin</a></div>

<main>
  <h1 style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">{{ $L['title'] }}, {{ $L['date']->format('F j, Y') }}</h1>

  <div class="bk-reader">
  <div class="bk-stage" id="bk-stage" aria-roledescription="booklet">
    <div class="bk-book" id="bk-book">
      <div class="bk-leaf" data-leaf="0" style="z-index: 2;">
        <div class="bk-face front">@include('bulletins._booklet-page', ['page' => 'cover'])</div>
        <div class="bk-face back">@include('bulletins._booklet-page', ['page' => 'left'])</div>
      </div>
      <div class="bk-leaf" data-leaf="1" style="z-index: 1;">
        <div class="bk-face front">@include('bulletins._booklet-page', ['page' => 'right'])</div>
        <div class="bk-face back">@include('bulletins._booklet-page', ['page' => 'back'])</div>
      </div>
    </div>
  </div>

  <nav class="bk-nav" aria-label="Turn pages">
    <button type="button" class="bk-arrow" id="bk-prev" aria-label="Previous page">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <span class="bk-where" id="bk-where" aria-live="polite">Cover</span>
    <button type="button" class="bk-arrow" id="bk-next" aria-label="Next page">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
    </button>
  </nav>
  </div>

  <div class="bk-foot">
    <a href="{{ route('bulletins.pdf', $bulletin) }}?layout=booklet">Print it</a>
    <p>Print both sides, flip on the short edge, then fold in half.</p>
  </div>

  {{-- How was your Sabbath: two questions to tick, one to write. Private to the church; no email asked. --}}
  <section class="sb" id="sabbath" aria-labelledby="sb-h">
    <h2 id="sb-h">How was your Sabbath?</h2>
    <p class="sb-lede">Three quick questions. Only the church reads your answers.</p>

    @if (session('sabbath_flash'))
      <p class="sb-done" role="status">{{ session('sabbath_flash') }}</p>
    @else
      @if (session('sabbath_error'))<p class="sb-err" role="alert">{{ session('sabbath_error') }}</p>@endif
      <form method="post" action="{{ route('bulletins.booklet.respond', $bulletin) }}">
        @csrf
        <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="sb-hp">
        <input type="hidden" name="form_opened_at" id="sb-opened" value="0">

        @foreach (['spoke', 'joined'] as $k)
          <fieldset>
            <legend>{{ $Q[$k]['q'] }}</legend>
            <p class="sb-hint">Tick any that fit.</p>
            <div class="sb-chips">
              @foreach ($Q[$k]['options'] as $o)
                <label class="sb-chip">
                  <input type="checkbox" name="{{ $k }}[]" value="{{ $o }}" @checked(in_array($o, (array) old($k, []), true))>
                  <svg class="sb-tick" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                  <span>{{ $o }}</span>
                </label>
              @endforeach
            </div>
          </fieldset>
        @endforeach

        <label class="sb-label" for="sb-story">{{ $Q['story'] }}</label>
        <textarea id="sb-story" name="story" rows="4" maxlength="2000" placeholder="A moment, a song, a word that stayed with you">{{ old('story') }}</textarea>

        <label class="sb-label" for="sb-name">Your first name <span>(optional)</span></label>
        <input id="sb-name" name="first_name" maxlength="40" autocomplete="given-name" value="{{ old('first_name') }}">

        <button type="submit" class="sb-send">Send</button>
      </form>
    @endif
  </section>

</main>

<script>
(() => {
  const stage = document.getElementById('bk-stage');
  const book  = document.getElementById('bk-book');
  const leaves = [...book.querySelectorAll('.bk-leaf')];
  const where = document.getElementById('bk-where');
  const prev  = document.getElementById('bk-prev');
  const next  = document.getElementById('bk-next');
  const wide  = matchMedia('(min-width: 900px) and (min-height: 740px)');

  /* A view = which spread is open (0 closed cover, 1 inside, 2 closed back) and where the book sits. */
  const VIEWS = {
    wide:  [ { s: 0, x: -25, name: 'Cover' }, { s: 1, x: 0, name: 'Order of service' }, { s: 2, x: 25, name: 'Announcements' } ],
    phone: [ { s: 0, x: -25, name: 'Cover' }, { s: 1, x: 25, name: 'Order of service' }, { s: 1, x: -25, name: 'Order of service, continued' }, { s: 2, x: 25, name: 'Announcements' } ],
  };
  const list = () => VIEWS[wide.matches ? 'wide' : 'phone'];
  let i = 0, spread = 0, rest = 0;

  /* Unturned leaves stack first-on-top; turned leaves stack last-on-top. A leaf in motion rides above both. */
  const restZ = (n, turned) => turned ? n + 1 : leaves.length - n;

  function show(to) {
    const views = list();
    i = Math.max(0, Math.min(views.length - 1, to));
    const v = views[i];
    leaves.forEach((leaf, n) => {
      leaf.style.transform = '';
      const turned = v.s > n;
      if (leaf.classList.contains('turned') !== turned) {
        stage.classList.add('moving'); clearTimeout(rest); rest = setTimeout(() => stage.classList.remove('moving'), 950);
        leaf.style.zIndex = leaves.length + 1;
        leaf.classList.toggle('turned', turned);
        const settle = (e) => { if (e.target !== leaf) return; leaf.style.zIndex = restZ(n, leaf.classList.contains('turned')); leaf.removeEventListener('transitionend', settle); };
        leaf.addEventListener('transitionend', settle);
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) leaf.style.zIndex = restZ(n, turned);
      }
    });
    spread = v.s;
    book.style.transform = `translateX(${v.x}%)`;
    where.textContent = v.name;
    prev.disabled = i === 0;
    next.disabled = i === views.length - 1;
  }

  prev.addEventListener('click', () => show(i - 1));
  next.addEventListener('click', () => show(i + 1));
  document.addEventListener('keydown', (e) => {
    if (e.target.closest && e.target.closest('input, textarea')) return;
    if (e.key === 'ArrowRight') show(i + 1);
    if (e.key === 'ArrowLeft')  show(i - 1);
  });

  /* Tap the right side to go on, the left side to go back. Swipe works too. */
  let x0 = null, swiped = false;
  stage.addEventListener('pointerdown', (e) => { x0 = e.clientX; swiped = false; });
  stage.addEventListener('pointerup', (e) => {
    if (x0 === null) return;
    const dx = e.clientX - x0; x0 = null;
    if (Math.abs(dx) > 40) { swiped = true; show(i + (dx < 0 ? 1 : -1)); }
  });
  stage.addEventListener('click', (e) => {
    if (swiped) { swiped = false; return; }
    const r = stage.getBoundingClientRect();
    show(i + (e.clientX > r.left + r.width / 2 ? 1 : -1));
  });

  /* Phone: the stage is as tall as the longest page; computer: paper size from CSS. */
  const pages = [...book.querySelectorAll('.pg-in')];
  function fit() {
    if (wide.matches) { stage.style.height = ''; return; }
    stage.style.height = '0px';
    stage.style.height = Math.ceil(Math.max(...pages.map(p => p.scrollHeight))) + 'px';
  }
  fit();
  addEventListener('resize', fit);
  if (document.fonts) document.fonts.ready.then(fit);
  addEventListener('load', fit);

  const opened = document.getElementById('sb-opened');
  if (opened) opened.value = Date.now();
  /* Taps inside the form never turn pages. */
  /* Crossing the phone/computer width keeps you on the same spread. */
  /* Once, after a moment: lift the cover a little so it reads as something that turns. */
  let touched = false;
  ['pointerdown', 'keydown'].forEach(t => addEventListener(t, () => { touched = true; }, { once: true }));
  if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
    setTimeout(() => {
      if (touched || i !== 0) return;
      stage.classList.add('moving');
      leaves[0].style.transform = 'rotateY(-18deg)';
      setTimeout(() => { leaves[0].style.transform = ''; }, 700);
      setTimeout(() => { if (!touched) stage.classList.remove('moving'); }, 1600);
    }, 1900);
  }

  wide.addEventListener('change', () => show(list().findIndex(v => v.s === spread)));
  show(0);
})();
</script>

@include('partials._event-tracker')
</body>
</html>
