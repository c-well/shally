<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    @include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Sabbath school lesson — The Church of Peace</title>
<meta name="theme-color" content="#fefcef">
<link rel="manifest" href="/lesson-manifest.webmanifest">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  @font-face { font-family: 'Xtreem'; src: url('/fonts/XtreemMedium.ttf') format('truetype'); font-display: swap; }
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  a { color: inherit; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; display: flex; align-items: center; gap: 10px; }
  .offline-pill { display: none; padding: 3px 8px; border-radius: 999px; background: rgba(176,141,60,0.15); color: var(--brass); font-weight: 700; letter-spacing: 0.2em; }
  .offline-pill.on { display: inline-block; }

  main { max-width: 760px; margin: 0 auto; padding: clamp(36px, 7vh, 64px) clamp(20px, 5vw, 32px) 80px; }
  .head { text-align: center; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1.theme { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px, 6vw, 56px); font-weight: 500; line-height: 1.05; letter-spacing: -0.025em; color: var(--ink); max-width: 620px; margin: 0 auto; }
  .quarter-line { margin-top: 14px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.28em; text-transform: uppercase; color: var(--brass); }

  /* Week heading */
  .week { margin-top: 48px; text-align: center; }
  .week-eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 6px; }
  .week-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(38px, 6vw, 60px); font-weight: 500; line-height: 1.05; letter-spacing: -0.025em; color: var(--ink); }
  .week-dates { margin-top: 10px; font-size: 13px; color: var(--ink-soft); letter-spacing: 0.04em; }

  .week-nav { margin-top: 22px; display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; }
  .week-nav a, .week-nav span { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; text-decoration: none; padding: 8px 14px; border: 1px solid var(--line); border-radius: 999px; color: var(--ink-soft); transition: border-color 0.15s, color 0.15s; }
  .week-nav a:hover { color: var(--teal); border-color: var(--teal); }
  .week-nav .disabled { opacity: 0.35; pointer-events: none; }
  .week-nav .now { background: var(--teal); color: #fff; border-color: var(--teal); }

  /* 7-day strip — now a tab switcher, no network on click */
  .day-strip { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; margin-top: 32px; }
  .day-cell {
    display: flex; flex-direction: column; align-items: center;
    padding: 14px 6px;
    border: 1px solid var(--line); border-radius: 6px;
    background: rgba(251,244,224,0.6);
    color: var(--ink-soft); cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s, transform 0.1s;
    font: inherit;
  }
  .day-cell:hover { border-color: var(--teal); color: var(--teal); }
  .day-cell .dow { font-family: 'Instrument Sans', sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; margin-bottom: 4px; }
  .day-cell .dnum { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; line-height: 1; color: var(--ink); letter-spacing: -0.015em; }
  .day-cell.active { background: var(--teal); border-color: var(--teal); color: #fff; box-shadow: 0 6px 14px -6px rgba(3,97,122,0.4); }
  .day-cell.active .dnum { color: #fff; }
  .day-cell.active .dow { color: rgba(255,255,255,0.85); }
  .day-cell.today:not(.active) { border-color: var(--brass); }
  .day-cell.today:not(.active) .dnum { color: var(--brass); }

  /* Day reading panel */
  .day-panel { display: none; margin-top: 38px; }
  .day-panel.active { display: block; }
  .day-eyebrow { font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--teal); text-align: center; margin-bottom: 14px; }
  h2.day-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(32px, 5vw, 48px); font-weight: 500; line-height: 1.1; letter-spacing: -0.02em; color: var(--ink); text-align: center; margin-bottom: 36px; }

  .prose { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.7; color: var(--ink); }
  .prose p { margin-bottom: 1.1em; }
  .prose h2, .prose h3 { font-family: 'Cormorant Garamond', serif; font-weight: 600; letter-spacing: -0.015em; margin: 1.4em 0 0.5em; color: var(--ink); }
  .prose h2 { font-size: 1.4em; }
  .prose h3 { font-size: 1.15em; }
  .prose blockquote { margin: 1.2em 0; padding: 14px 22px; border-left: 3px solid var(--teal); background: rgba(3,97,122,0.05); font-style: italic; color: var(--ink-soft); }
  /* Memory Text — the headline verse for the week, set apart with teal card + monospace label. */
  .prose blockquote.memory-verse {
    margin: 2em 0;
    padding: 26px 28px 28px;
    background: rgba(3,97,122,0.07);
    border: 1px solid rgba(3,97,122,0.25);
    border-left: 4px solid var(--teal);
    border-radius: 8px;
    font-style: normal; color: var(--ink);
    box-shadow: 0 6px 20px -12px rgba(3,97,122,0.35);
  }
  .prose blockquote.memory-verse > p:first-child {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; font-weight: 500;
    letter-spacing: 0.32em; text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 14px;
    font-style: normal;
  }
  .prose blockquote.memory-verse a.verse { background: rgba(3,97,122,0.16); }
  html.font-large  .prose blockquote.memory-verse > p:first-child { font-size: 12px; }
  html.font-xlarge .prose blockquote.memory-verse > p:first-child { font-size: 13px; }
  .prose ul, .prose ol { margin: 1em 0 1em 1.4em; }
  .prose li { margin-bottom: 0.5em; }
  .prose a.verse {
    display: inline; padding: 1px 5px; margin: 0 1px;
    border-radius: 3px; background: rgba(3,97,122,0.08);
    color: var(--teal); text-decoration: none; font-weight: 500;
    font-size: 0.92em; cursor: pointer;
    transition: background 0.12s;
  }
  .prose a.verse:hover { background: rgba(3,97,122,0.18); }
  .prose a:not(.verse) { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }

  .day-foot-nav { margin-top: 50px; display: flex; justify-content: space-between; gap: 14px; padding-top: 26px; border-top: 1px solid var(--line); }
  .day-foot-nav button { font: inherit; flex: 1; padding: 14px 16px; background: transparent; border: 1px solid var(--line); border-radius: 6px; color: var(--ink-soft); cursor: pointer; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; transition: border-color 0.15s, color 0.15s, background 0.15s; }
  .day-foot-nav button:hover:not(:disabled) { border-color: var(--teal); color: var(--teal); }
  .day-foot-nav button:disabled { opacity: 0.35; cursor: default; }

  .actions { margin-top: 60px; display: flex; flex-direction: column; gap: 12px; align-items: center; }
  .btn-primary, .btn-ghost { display: inline-flex; align-items: center; justify-content: center; min-width: 280px; padding: 15px 32px; border-radius: 5px; font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; text-decoration: none; cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s; }
  .btn-primary { background: var(--teal); color: #fff; border: 1px solid var(--teal); }
  .btn-primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .btn-ghost { background: transparent; color: var(--ink-soft); border: 1px solid var(--line); }
  .btn-ghost:hover { color: var(--teal); border-color: var(--teal); }

  .empty { margin-top: 60px; font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 22px; color: var(--ink-soft); text-align: center; }
  .empty .lede { margin-top: 16px; font-size: 16px; color: var(--ink-soft); font-style: normal; font-family: 'Poppins', sans-serif; }

  footer { padding: 22px clamp(20px, 5vw, 40px) 40px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }
  footer .copyright { margin-top: 14px; max-width: 540px; margin-left: auto; margin-right: auto; opacity: 0.7; line-height: 1.6; letter-spacing: 0.04em; }

  /* Verse modal */
  .verse-modal {
    position: fixed; inset: 0; background: rgba(26,35,50,0.55);
    display: none; align-items: flex-end; justify-content: center;
    z-index: 200; padding: 0;
  }
  .verse-modal.open { display: flex; }
  .verse-modal-card {
    background: var(--parchment); width: 100%; max-width: 640px;
    border-radius: 14px 14px 0 0;
    padding: 26px 26px 30px;
    max-height: 80vh; overflow-y: auto;
    box-shadow: 0 -10px 40px -10px rgba(0,0,0,0.3);
    animation: slideUp 0.22s cubic-bezier(0.2, 0, 0.2, 1);
  }
  @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
  .verse-modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--line); }
  .verse-modal-head .label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.28em; text-transform: uppercase; color: var(--teal); }
  .verse-modal-head .close { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); background: none; border: none; cursor: pointer; padding: 4px 8px; }
  .verse-modal-head .close:hover { color: var(--teal); }
  .verse-modal-body { font-family: 'Cormorant Garamond', serif; font-size: 18px; line-height: 1.6; color: var(--ink); }
  .verse-modal-body h2 { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: var(--teal); margin: 18px 0 8px; }
  .verse-modal-body h2:first-child { margin-top: 0; }
  .verse-modal-body sup { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; color: var(--brass); margin-right: 3px; vertical-align: super; }
  @media (min-width: 700px) {
    .verse-modal { align-items: center; padding: 20px; }
    .verse-modal-card { border-radius: 14px; }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  }

  @media (max-width: 600px) {
    h1.theme { font-size: 32px; }
    .week-title { font-size: 32px; }
    h2.day-title { font-size: 28px; }
    .actions .btn-primary, .actions .btn-ghost { min-width: 0; width: 100%; }
    .day-strip { gap: 5px; }
    .day-cell { padding: 11px 3px; }
    .day-cell .dow { font-size: 8px; letter-spacing: 0.14em; }
    .day-cell .dnum { font-size: 18px; }
    .prose { font-size: 18px; }
  }

  /* Font sizer — ONLY scales the lesson reading body + scripture modal.
     Page header (theme, week title, day title, day strip) stays fixed so
     the chrome doesn't push the actual lesson off-screen on small phones. */
  html.font-large  .prose { font-size: 22px; line-height: 1.78; }
  html.font-large  .verse-modal-body { font-size: 21px; line-height: 1.7; }
  html.font-xlarge .prose { font-size: 26px; line-height: 1.85; }
  html.font-xlarge .verse-modal-body { font-size: 24px; line-height: 1.75; }

  .font-toggle { position: fixed; bottom: 22px; right: 22px; display: inline-flex; align-items: center; gap: 6px; padding: 10px 14px; background: #fff; border: 1px solid var(--line); border-radius: 999px; box-shadow: 0 6px 18px -8px rgba(26,35,50,0.25); cursor: pointer; user-select: none; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); transition: border-color 0.15s, color 0.15s, transform 0.1s; z-index: 100; }
  .font-toggle:hover { border-color: var(--teal); color: var(--teal); }
  .font-toggle:active { transform: translateY(1px); }
  .font-toggle .aa { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 500; letter-spacing: -0.5px; line-height: 1; }
  .font-toggle .aa-large { font-size: 22px; }
  .font-toggle .aa-xlarge { font-size: 26px; }
  @media (max-width: 480px) {
    .font-toggle { bottom: 14px; right: 14px; padding: 8px 12px; font-size: 10px; }
  }
</style>
</head>
<body>
@include("partials.admin-badge")

<header class="top">
  <a href="/">← Back to bulletin</a>
  <span class="meta">
    <span id="offline-pill" class="offline-pill">offline</span>
    sabbath school
  </span>
</header>

<main>

@if (! $quarter || ! $lessonNum)
  {{-- Empty quarter — admin hasn't seeded one yet --}}
  <div class="head">
    <div class="eyebrow">Sabbath school</div>
    <h1 class="theme">Coming soon.</h1>
    <p class="empty">No quarter is loaded yet — Karlon will post one shortly.</p>
  </div>

@else
  {{-- Quarter header --}}
  <div class="head">
    <div class="eyebrow">{{ $quarter->quarterLabel() }} · Adult Bible Study Guide</div>
    <h1 class="theme">{{ $quarter->theme }}</h1>
    <div class="quarter-line">{{ $quarter->starts_on->format('M j') }} – {{ $quarter->ends_on->format('M j, Y') }}</div>
  </div>

  {{-- Week heading + nav --}}
  <section class="week">
    <div class="week-eyebrow">Lesson {{ $lessonNum }}</div>
    <h2 class="week-title">
      @if ($lessonData)
        {{ $lessonData['lesson']['title'] ?? 'Untitled lesson' }}
      @else
        Not synced yet
      @endif
    </h2>
    @if ($weekStart && $weekEnd)
      <div class="week-dates">{{ $weekStart->format('l, M j') }} – {{ $weekEnd->format('l, M j') }}</div>
    @endif

    <nav class="week-nav" aria-label="Week navigation">
      <a href="{{ route('lesson.week', ['lesson' => max(1, $lessonNum - 1)]) }}"
         class="{{ $lessonNum <= 1 ? 'disabled' : '' }}">← Prev</a>
      @if ($range && $range['lesson'] !== $lessonNum)
        <a href="{{ route('lesson.show') }}" class="now">This week</a>
      @endif
      <a href="{{ route('lesson.week', ['lesson' => min(13, $lessonNum + 1)]) }}"
         class="{{ $lessonNum >= 13 ? 'disabled' : '' }}">Next →</a>
    </nav>
  </section>

  @if (! $lessonData || ! count($days))
    {{-- Week is in DB but content not synced yet --}}
    <p class="empty">
      The reading text isn't synced yet for Lesson {{ $lessonNum }}.<br>
      <span class="lede">Run <code style="font-family:'JetBrains Mono',monospace; background:rgba(0,0,0,0.05); padding:2px 6px; border-radius:3px;">php artisan lesson:sync</code> on the server, or open the PDF below.</span>
    </p>
    @if ($quarter->downloadUrl())
      <div class="actions">
        <a href="{{ $quarter->downloadUrl() }}" class="btn-primary" target="_blank" rel="noopener">Download the PDF ↓</a>
      </div>
    @endif

  @else
    {{-- 7-day tab strip --}}
    @php
      // Build day metadata. Adventech dates are dd/mm/YYYY.
      $dayMeta = collect($days)->map(function ($d, $i) use ($weekStart) {
        $date = null;
        if (! empty($d['date'])) {
          try { $date = \Carbon\Carbon::createFromFormat('d/m/Y', $d['date']); } catch (\Throwable $e) {}
        }
        if (! $date && $weekStart) $date = $weekStart->copy()->addDays($i);
        return [
          'id'    => (int) ($d['id'] ?? ($i + 1)),
          'title' => $d['title'] ?? 'Untitled',
          'date'  => $date,
        ];
      })->values();

      $today = now()->setTimezone('America/New_York')->startOfDay();
    @endphp

    <div class="day-strip" role="tablist">
      @foreach ($dayMeta as $i => $dm)
        @php
          // Cast id to int — JSON has zero-padded strings ("01") while $activeDay is an int.
          // is_numeric guard means the special-id rows (hope-ss, inside-story, etc.) just
          // never match $activeDay, which is the desired behavior.
          $isActive = is_numeric($dm['id']) && (int) $dm['id'] === $activeDay;
          $isToday  = $dm['date'] && $dm['date']->isSameDay($today);
          $cls = ($isActive ? 'active ' : '') . ($isToday ? 'today' : '');
        @endphp
        <button type="button"
                class="day-cell {{ $cls }}"
                data-day="{{ $dm['id'] }}"
                role="tab"
                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                aria-controls="day-{{ $dm['id'] }}"
                title="{{ $dm['date']?->format('l, F j') ?? '' }}">
          <span class="dow">{{ $dm['date']?->format('D') ?? '' }}</span>
          <span class="dnum">{{ $dm['date']?->format('j') ?? $dm['id'] }}</span>
        </button>
      @endforeach
    </div>

    {{-- 7 day panels — only one visible at a time, switching is local --}}
    @foreach ($days as $i => $d)
      @php
        $did   = (int) ($d['id'] ?? ($i + 1));
        $dmeta = $dayMeta->firstWhere('id', $did);
        $isActive = $did === $activeDay;
      @endphp
      <article id="day-{{ $did }}"
               class="day-panel {{ $isActive ? 'active' : '' }}"
               role="tabpanel"
               data-day="{{ $did }}"
               data-bible='@json($d['bible'] ?? [])'>
        <div class="day-eyebrow">
          @if ($dmeta && $dmeta['date'])
            {{ $dmeta['date']->format('l · F j') }}
          @endif
        </div>
        <h2 class="day-title">{{ $d['title'] ?? 'Untitled' }}</h2>
        <div class="prose">
          {{-- Adventech HTML is trusted — we cache it ourselves from a known source --}}
          {!! $d['content'] ?? '' !!}
        </div>

        <nav class="day-foot-nav" aria-label="Day navigation">
          <button type="button" class="day-prev" data-target="{{ max(1, $did - 1) }}" {{ $did === 1 ? 'disabled' : '' }}>← Previous day</button>
          <button type="button" class="day-next" data-target="{{ min(7, $did + 1) }}" {{ $did === 7 ? 'disabled' : '' }}>Next day →</button>
        </nav>
      </article>
    @endforeach

    <div class="actions">
      @if ($quarter->downloadUrl())
        <a href="{{ $quarter->downloadUrl() }}" class="btn-ghost" target="_blank" rel="noopener">Download the PDF ↓</a>
      @endif
    </div>
  @endif

@endif

</main>

<footer>
  The Church of Peace · Shalom SDA · Bronx, NY
  <div class="copyright">
    Adult Bible Study Guide &copy; General Conference of Seventh-day Adventists&reg; · Sabbath School Personal Ministries.<br>
    Reproduced for the use of Shalom SDA Church. All rights reserved.
  </div>
</footer>

{{-- Verse modal — populated client-side from the active panel's data-bible JSON --}}
<div class="verse-modal" id="verse-modal" role="dialog" aria-modal="true" aria-labelledby="verse-modal-label">
  <div class="verse-modal-card">
    <div class="verse-modal-head">
      <span class="label" id="verse-modal-label">Scripture</span>
      <button type="button" class="close" id="verse-modal-close" aria-label="Close">Close ×</button>
    </div>
    <div class="verse-modal-body" id="verse-modal-body"></div>
  </div>
</div>

{{-- Font sizer --}}
<button type="button" class="font-toggle" id="font-toggle" aria-label="Change text size">
  <span class="aa" id="font-toggle-icon">A</span>
  <span id="font-toggle-label">Text size</span>
</button>

<script>
(function () {
  // ── Font sizer (persists across pages) ─────────────────────────────
  const html  = document.documentElement;
  const ftBtn = document.getElementById('font-toggle');
  const icon  = document.getElementById('font-toggle-icon');
  const label = document.getElementById('font-toggle-label');
  const states = ['normal', 'large', 'xlarge'];
  const labels = { normal: 'Text size', large: 'Larger', xlarge: 'Largest' };
  const iconClasses = { normal: 'aa', large: 'aa aa-large', xlarge: 'aa aa-xlarge' };
  function applyFont(state) {
    html.classList.remove('font-normal', 'font-large', 'font-xlarge');
    html.classList.add('font-' + state);
    icon.className = iconClasses[state];
    label.textContent = labels[state];
    try { localStorage.setItem('cop_font_size', state); } catch (e) {}
  }
  let cur = 'normal';
  try { cur = localStorage.getItem('cop_font_size') || 'normal'; } catch (e) {}
  applyFont(cur);
  ftBtn.addEventListener('click', () => {
    const idx = states.indexOf(cur);
    cur = states[(idx + 1) % states.length];
    applyFont(cur);
  });

  // ── Day tab switching ───────────────────────────────────────────────
  const tabs   = document.querySelectorAll('.day-cell[data-day]');
  const panels = document.querySelectorAll('.day-panel[data-day]');
  function switchDay(dayId, scrollTo = true) {
    dayId = parseInt(dayId, 10);
    tabs.forEach(t => {
      const on = parseInt(t.dataset.day, 10) === dayId;
      t.classList.toggle('active', on);
      t.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    panels.forEach(p => p.classList.toggle('active', parseInt(p.dataset.day, 10) === dayId));
    if (scrollTo) {
      // Smooth scroll to the panel top so the reading starts from its title.
      const panel = document.getElementById('day-' + dayId);
      if (panel) {
        const y = panel.getBoundingClientRect().top + window.scrollY - 12;
        window.scrollTo({ top: y, behavior: 'smooth' });
      }
    }
    // URL hash so reload + share keeps the day
    try { history.replaceState(null, '', '#day-' + dayId); } catch (e) {}
  }
  tabs.forEach(t => t.addEventListener('click', () => switchDay(t.dataset.day)));

  // Prev/next day buttons inside each panel
  document.querySelectorAll('.day-prev, .day-next').forEach(b => {
    b.addEventListener('click', () => switchDay(b.dataset.target));
  });

  // Honor #day-N on initial load
  if (location.hash && /^#day-[1-7]$/.test(location.hash)) {
    const id = parseInt(location.hash.replace('#day-', ''), 10);
    switchDay(id, false);
  }

  // ── Inline verse links → modal ──────────────────────────────────────
  const modal     = document.getElementById('verse-modal');
  const modalBody = document.getElementById('verse-modal-body');
  const modalClose= document.getElementById('verse-modal-close');
  function openVerse(verseRef, panel) {
    let bible = {};
    try { bible = JSON.parse(panel.dataset.bible || '[]'); } catch (e) {}
    // The bible field is an array of translations; default to NASB (the only one Adventech ships here)
    const tx = Array.isArray(bible) && bible[0] ? bible[0] : null;
    const verses = tx?.verses || {};
    const ref = (verseRef || '').trim();

    // Adventech keys this dict by the FULL comma-joined ref string (e.g. "Lam322-Lam323,2Tim315-2Tim317,...")
    // not by individual verses. Try the whole string first; only fall back to per-key splitting.
    let html = verses[ref];
    if (!html) {
      const keys = ref.split(',').map(s => s.trim()).filter(Boolean);
      html = keys.map(k => verses[k] || '').filter(Boolean)
                 .join('<hr style="margin:20px 0; border:none; border-top:1px solid rgba(26,35,50,0.1);">');
    }
    if (!html) html = '<p><em>Scripture not available — try opening the PDF.</em></p>';

    modalBody.innerHTML = html;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeVerse() {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }
  modalClose.addEventListener('click', closeVerse);
  modal.addEventListener('click', e => { if (e.target === modal) closeVerse(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) closeVerse(); });

  // Delegate clicks on inline verse anchors
  document.querySelectorAll('.day-panel').forEach(panel => {
    panel.addEventListener('click', e => {
      const a = e.target.closest('a.verse');
      if (!a) return;
      e.preventDefault();
      const ref = a.getAttribute('verse') || a.dataset.verse || '';
      openVerse(ref, panel);
    });
  });

  // Tag any blockquote whose first <p> is "Memory Text:" so CSS can dress it up.
  document.querySelectorAll('.day-panel .prose blockquote').forEach(bq => {
    const firstP = bq.querySelector('p');
    if (firstP && /^\s*memory\s*text/i.test(firstP.textContent)) {
      bq.classList.add('memory-verse');
    }
  });

  // ── Service worker — cache /lesson pages so they work offline ───────
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/lesson-sw.js', { scope: '/lesson' }).catch(() => {});
    });
  }
  function syncOfflinePill() {
    const pill = document.getElementById('offline-pill');
    if (!pill) return;
    pill.classList.toggle('on', !navigator.onLine);
  }
  window.addEventListener('online',  syncOfflinePill);
  window.addEventListener('offline', syncOfflinePill);
  syncOfflinePill();
})();
</script>

@include('partials.search-float')
</body>
</html>
