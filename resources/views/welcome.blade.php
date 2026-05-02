<!DOCTYPE html>
<html lang="en">
<head>
{{-- Dark mode FOUC prevention — applied before body paints. Theme toggle UI
     lives in the Admin ▾ menu next to the user name (super_admin only). --}}
<script>
  (function () {
    try {
      if (localStorage.getItem('cop_admin_theme') === 'dark') {
        document.documentElement.classList.add('dark');
      }
    } catch (e) {}
  })();
</script>
<meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    @include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Shalom SDA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Poppins:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Instrument+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/minisearch@7.1.0/dist/umd/index.min.js" defer></script>
<style>
  :root {
    --parchment: #fefcef;
    --ink: #1a2332;
    --ink-soft: #334455;  /* darkened from #3d4a5f for AA contrast on parchment */
    --brass: #b08d3c;
    --teal: #03617A;
    --teal-dark: #024357;
    --teal-light: #e6f0f3;
    --cream: #fefcef;
    --line: rgba(26, 35, 50, 0.12);
    --container: 1080px;
    --reading: 760px;
    --gutter: 32px;
  }
  /* SPECIAL DAY THEMES — pastel palettes that tint the whole homepage. Toggle via clerk-only theme picker. */
  body[data-theme="communion"]    { --parchment: #f5f0fb; --cream: #f1ebf9; --teal: #6b4d8a; --teal-dark: #553a72; --teal-light: #ece2f5; --brass: #8a6dab; }
  body[data-theme="easter"]       { --parchment: #f0faf3; --cream: #eaf6ed; --teal: #3a8e63; --teal-dark: #2c714d; --teal-light: #dff0e3; --brass: #c2a652; }
  body[data-theme="christmas"]    { --parchment: #fbf2f2; --cream: #f7eaea; --teal: #8b3a4b; --teal-dark: #6e2d3b; --teal-light: #f3dcdf; --brass: #b08d3c; }
  body[data-theme="mothers"]      { --parchment: #fdf1f5; --cream: #fbe8ee; --teal: #b1657a; --teal-dark: #8d4d61; --teal-light: #f6dde3; --brass: #c8916b; }
  body[data-theme="thanksgiving"] { --parchment: #fbf3e9; --cream: #f7ecdb; --teal: #8a5a2c; --teal-dark: #6b4622; --teal-light: #f0e0c8; --brass: #b08d3c; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); }
  body {
    font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
    color: var(--ink);
    font-size: 16px;
    line-height: 1.65;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
    font-feature-settings: "kern" 1, "liga" 1, "calt" 1;
  }
  h1, h2, h3, h4, .brand, .hero .greeting, .bulletin-head h1 {
    text-rendering: geometricPrecision;
    font-feature-settings: "kern" 1, "liga" 1, "dlig" 1;
  }
  a { color: inherit; }

  .container { max-width: var(--container); margin: 0 auto; padding: 0 var(--gutter); }
  .reading { max-width: var(--reading); margin: 0 auto; }

  /* HEADER — full-width with edge padding (matches landing/main-website positioning) */
  .site-header {
    padding: 28px 0; border-bottom: 1px solid var(--line);
    position: sticky; top: 0; z-index: 10;
    background: color-mix(in srgb, var(--parchment) 92%, transparent);
    backdrop-filter: blur(8px);
  }
  /* Override container max-width inside the header — brand should hug the left edge
     like the landing/main page (clamp 20-48px from edge), not be inside the 1080px box. */
  .site-header > .container.inner {
    max-width: none;
    padding: 0 clamp(20px, 5vw, 48px);
    display: flex; align-items: center; justify-content: space-between; gap: 24px;
  }
  @font-face {
    font-family: 'Xtreem';
    src: url('/fonts/XtreemMedium.ttf') format('truetype');
    font-weight: 500; font-style: normal; font-display: swap;
  }
  /* Brand: just "Shalom" in Xtreem teal — no inline tail, no animations.
     Formal name lives in the footer. Cleanest possible header brand. */
  .brand {
    text-decoration: none;
    line-height: 1;
    display: inline-flex;
    align-items: center;
  }
  .brand em {
    font-family: 'Xtreem', 'Cormorant Garamond', serif;
    font-style: normal;
    font-weight: 500;
    color: var(--teal);
    text-transform: lowercase;
    font-size: 88px;
    letter-spacing: -0.02em;
    line-height: 0.8;
    display: inline-block;
    transform: translateY(2px);
  }
  .btn {
    font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.25em;
    text-transform: uppercase; padding: 10px 18px; border-radius: 2px;
    background: transparent; border: 1px solid var(--line); color: var(--ink-soft);
    text-decoration: none; cursor: pointer; transition: all 0.2s; white-space: nowrap;
  }
  .btn:hover { color: var(--teal); border-color: var(--teal); }
  .btn.primary { background: var(--teal); color: #fff; border-color: var(--teal); }
  .btn.primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); color: #fff; }

  /* HERO */
  .hero { padding: 120px 0 96px; text-align: center; }
  .hero .greeting { font-family: 'Cormorant Garamond', serif; font-size: clamp(3rem, 8vw, 5.5rem); line-height: 1; letter-spacing: -0.035em; color: var(--teal); font-weight: 500; }
  .hero .date { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.3em; color: var(--ink-soft); margin-top: 26px; text-transform: uppercase; }
  .hero .lede { font-family: 'Poppins', sans-serif; font-size: 17px; color: var(--ink-soft); max-width: 560px; margin: 26px auto 0; line-height: 1.6; }
  .hero .lede-link { color: var(--teal); text-decoration: none; border-bottom: 1px dotted var(--teal); transition: border-style 0.15s, color 0.15s; }
  .hero .lede-link:hover { color: var(--teal-dark); border-bottom-style: solid; }

  /* SECTIONS */
  .section { padding: 88px 0; border-top: 1px solid var(--line); }
  .eyebrow { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.3em; text-transform: uppercase; color: var(--ink-soft); display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; }
  .chip { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--teal); }
  .chip.draft { color: var(--brass); }
  .chip::before { content: ''; display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: currentColor; margin-right: 6px; vertical-align: middle; }
  .draft-banner {
    display: none;
    background: var(--brass); color: #fff;
    padding: 16px 22px; border: 0; border-radius: 6px;
    margin-bottom: 18px;
    font-family: 'Instrument Sans', sans-serif; font-size: 13px;
    letter-spacing: 0.14em; text-transform: uppercase; font-weight: 700;
    text-align: center;
    box-shadow: 0 6px 18px -6px rgba(176, 141, 60, 0.55);
    line-height: 1.5;
  }
  .draft-banner strong {
    font-weight: 800; padding: 3px 9px;
    background: rgba(255,255,255,0.22); border-radius: 3px;
    margin: 0 4px; letter-spacing: 0.18em;
  }
  body.bulletin-dirty .draft-banner { display: block; }

  /* ── Form error message — adequate contrast on parchment + brass-warning color framing ── */
  .err {
    color: var(--ink);
    background: rgba(192, 57, 43, 0.08);
    border-left: 3px solid #c0392b;
    padding: 6px 12px;
    font-size: 13px; line-height: 1.4;
    border-radius: 0 4px 4px 0;
    margin-top: 6px;
  }

  /* ── Event masthead (top of an event-night bulletin) — sequential fade between two greetings ── */
  .event-masthead {
    position: relative;
    height: 44px;
    margin-bottom: 14px;
    text-align: center;
  }
  .rotator-event-msg {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.25rem, 2vw, 1.5rem);
    font-weight: 500;
    color: var(--teal-dark);
    letter-spacing: 0.005em;
    line-height: 1.1;
  }
  .event-masthead .msg-blessed {
    animation: eventBlessedFade 12s ease-in-out infinite;
  }
  .event-masthead .msg-welcome {
    opacity: 0;
    animation: eventWelcomeFade 12s ease-in-out infinite;
  }
  @keyframes eventBlessedFade {
    0%, 35%   { opacity: 1; }
    40%, 94%  { opacity: 0; }
    100%      { opacity: 1; }
  }
  @keyframes eventWelcomeFade {
    0%, 44%   { opacity: 0; }
    49%, 85%  { opacity: 1; }
    90%, 100% { opacity: 0; }
  }
  @media (prefers-reduced-motion: reduce) {
    .event-masthead { height: auto; }
    .rotator-event-msg { position: static; opacity: 1; animation: none; }
    .rotator-event-msg + .rotator-event-msg { margin-top: 6px; }
  }

  /* Event night tag under the service-date */
  .event-night-tag {
    margin-top: 8px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    font-weight: 700;
    color: var(--brass);
  }

  /* ── Event series creation modal — site DNA (Cormorant + Instrument Sans + brass/teal) ── */
  .series-modal {
    position: fixed; inset: 0;
    background: rgba(26, 35, 50, 0.62);
    z-index: 250;
    display: none;
    align-items: center; justify-content: center;
    padding: 24px;
    backdrop-filter: blur(2px);
  }
  .series-modal.open { display: flex; }
  .series-card {
    background: var(--parchment);
    border-radius: 10px;
    width: 100%; max-width: 520px; max-height: 92vh;
    overflow-y: auto;
    box-shadow: 0 24px 56px -12px rgba(0, 0, 0, 0.35);
    position: relative;
  }
  .series-head {
    padding: 28px 30px 20px;
    border-bottom: 1px solid var(--line);
    position: relative;
  }
  .series-eyebrow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.32em; text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 10px;
  }
  .series-head h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px; font-weight: 500;
    letter-spacing: -0.01em;
    color: var(--ink);
    line-height: 1.1;
    margin-bottom: 10px;
  }
  .series-sub {
    font-size: 13px; line-height: 1.55;
    color: var(--ink-soft);
    max-width: 400px;
  }
  .series-close {
    position: absolute;
    top: 16px; right: 18px;
    background: transparent; border: 0;
    font-size: 22px; line-height: 1;
    color: var(--ink-soft); cursor: pointer;
    width: 32px; height: 32px;
    border-radius: 50%;
    transition: background 0.15s, color 0.15s;
  }
  .series-close:hover { background: rgba(26, 35, 50, 0.06); color: var(--ink); }

  .series-form {
    padding: 24px 30px 28px;
    display: flex; flex-direction: column; gap: 20px;
  }
  .series-row { display: flex; flex-direction: column; gap: 7px; }
  .series-lbl {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.24em; text-transform: uppercase;
    color: var(--ink-soft);
  }
  .series-input {
    font: inherit; font-size: 15px;
    padding: 11px 14px;
    border: 1px solid var(--line);
    border-radius: 5px;
    background: #fff;
    color: var(--ink);
    width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .series-input:focus {
    outline: 0;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(3, 97, 122, 0.12);
  }
  .series-row-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  .series-toggle {
    display: flex; gap: 12px; align-items: flex-start;
    cursor: pointer;
    padding: 14px 16px;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 5px;
  }
  .series-toggle:hover { border-color: var(--teal); }
  .series-toggle input[type="checkbox"] {
    width: 16px; height: 16px;
    margin-top: 2px;
    accent-color: var(--teal);
    cursor: pointer;
    flex-shrink: 0;
  }
  .series-toggle-title {
    display: block;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.16em; text-transform: uppercase;
    color: var(--ink);
    margin-bottom: 4px;
  }
  .series-toggle-hint {
    display: block;
    font-size: 12px; line-height: 1.5;
    color: var(--ink-soft);
  }
  .series-preview {
    padding: 12px 16px;
    background: rgba(176, 141, 60, 0.08);
    border-left: 3px solid var(--brass);
    border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px; font-weight: 600;
    letter-spacing: 0.08em;
    color: var(--brass);
    line-height: 1.5;
  }
  .series-actions {
    display: flex; gap: 10px; justify-content: flex-end;
    margin-top: 4px;
    padding-top: 20px;
    border-top: 1px solid var(--line);
  }
  .series-btn-cancel, .series-btn-primary {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    padding: 11px 22px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.08s;
  }
  .series-btn-cancel {
    background: transparent;
    border: 1px solid var(--line);
    color: var(--ink-soft);
  }
  .series-btn-cancel:hover { border-color: var(--ink); color: var(--ink); }
  .series-btn-primary {
    background: var(--teal);
    border: 1px solid var(--teal);
    color: #fff;
  }
  .series-btn-primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .series-btn-primary:active { transform: translateY(1px); }
  .series-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
  @media (max-width: 540px) {
    .series-modal { padding: 0; align-items: flex-end; }
    .series-card { max-width: 100%; max-height: 94vh; border-radius: 12px 12px 0 0; }
    .series-head { padding: 24px 22px 18px; }
    .series-head h3 { font-size: 24px; }
    .series-form { padding: 20px 22px 24px; }
    .series-row-grid { grid-template-columns: 1fr; gap: 14px; }
  }

  /* ── Global focus ring (keyboard accessibility — every interactive element shows it) ── */
  *:focus-visible {
    outline: 2px solid var(--teal);
    outline-offset: 2px;
    border-radius: 3px;
  }
  /* Inputs already have their own focus ring via box-shadow — don't double it */
  input:focus-visible, textarea:focus-visible, select:focus-visible {
    outline: 0;
  }

  /* ── Bulletin picker overlay (long-press "Latest") ── */
  .picker-overlay {
    position: fixed; inset: 0;
    background: rgba(26,35,50,0.65);
    z-index: 200;
    display: none;
    align-items: center; justify-content: center;
    padding: 20px;
  }
  .picker-overlay.open { display: flex; }
  .picker-card {
    background: #fff; border-radius: 10px;
    width: 100%; max-width: 780px; max-height: 86vh;
    overflow: hidden; display: flex; flex-direction: column;
    box-shadow: 0 24px 48px -12px rgba(0,0,0,0.4);
  }
  .picker-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px 14px; border-bottom: 1px solid var(--line);
  }
  .picker-head h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px; font-weight: 500; color: var(--ink);
  }
  .picker-close {
    background: transparent; border: 0; font-size: 26px; line-height: 1;
    color: var(--ink-soft); cursor: pointer; padding: 4px 10px;
  }
  .picker-close:hover { color: var(--ink); }
  .picker-hint {
    padding: 10px 22px; font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--ink-soft); background: var(--parchment);
    border-bottom: 1px solid var(--line);
  }
  .picker-grid {
    flex: 1 1 auto; overflow-y: auto; padding: 18px 22px 22px;
    display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
  }
  .picker-thumb {
    display: flex; flex-direction: column; gap: 4px;
    padding: 14px 12px; border: 1px solid var(--line); border-radius: 6px;
    background: #fff; color: var(--ink);
    text-align: center; text-decoration: none; cursor: pointer;
    transition: border-color 0.15s, transform 0.15s, box-shadow 0.15s;
    min-height: 120px; justify-content: center;
  }
  .picker-thumb:hover {
    border-color: var(--teal);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px -2px rgba(3,97,122,0.15);
  }
  .picker-thumb.is-current {
    border-color: var(--teal);
    background: rgba(3,97,122,0.06);
  }
  .picker-thumb.is-event { border-left: 3px solid var(--brass); }
  .picker-thumb .p-month {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; letter-spacing: 0.22em; text-transform: uppercase;
    font-weight: 600; color: var(--ink-soft);
  }
  .picker-thumb .p-day {
    font-family: 'Cormorant Garamond', serif;
    font-size: 38px; font-weight: 500; line-height: 1;
    color: var(--ink); letter-spacing: -0.02em;
  }
  .picker-thumb .p-weekday {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase;
    color: var(--ink-soft); font-weight: 500;
  }
  .picker-thumb .p-kind {
    margin-top: 6px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 9px; letter-spacing: 0.16em; text-transform: uppercase;
    font-weight: 700;
  }
  .picker-thumb.is-sabbath .p-kind { color: var(--teal); }
  .picker-thumb.is-event   .p-kind { color: var(--brass); }
  .picker-loading {
    text-align: center; color: var(--ink-soft); padding: 30px 0;
    font-family: 'Instrument Sans', sans-serif; letter-spacing: 0.18em;
    text-transform: uppercase; font-size: 11px;
  }
  @media (max-width: 540px) {
    .picker-overlay { padding: 0; align-items: flex-end; }
    .picker-card { max-width: 100%; max-height: 90vh; border-radius: 12px 12px 0 0; }
    .picker-grid { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; padding: 14px 16px; }
    .picker-thumb { min-height: 110px; padding: 12px 8px; }
    .picker-thumb .p-day { font-size: 32px; }
  }
  /* (Old #publish-btn pulse animation removed — Go Live is now a panel item, not a standalone
     button. The dirty signal is now a green Admin trigger, defined further down with the
     admin-trigger styles.) */

  /* BULLETIN CARD */
  .bulletin-card {
    background: #fff; border: 1px solid var(--line); border-radius: 4px;
    padding: 56px 64px;
    box-shadow: 0 1px 2px rgba(26,35,50,0.04), 0 4px 24px -12px rgba(26,35,50,0.08);
  }
  .bulletin-head { margin-bottom: 36px; padding-bottom: 28px; border-bottom: 1px solid var(--line); position: relative; }
  .bulletin-nav { display: flex; gap: 6px; align-items: center; justify-content: center; margin-bottom: 16px; }
  .bulletin-nav-arrow {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 50%; border: 1px solid var(--line);
    color: var(--ink-soft); background: #fff; text-decoration: none;
    transition: all 0.15s;
  }
  .bulletin-nav-arrow:hover { color: var(--teal); border-color: var(--teal); }
  .bulletin-nav-arrow.disabled { opacity: 0.28; pointer-events: none; }
  .bulletin-nav-latest {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase; font-weight: 600;
    padding: 7px 14px 6px; border-radius: 4px; border: 1px solid var(--line);
    color: var(--ink-soft); background: #fff; text-decoration: none; transition: all 0.15s;
    display: inline-flex; flex-direction: column; align-items: center; line-height: 1.1; gap: 2px;
  }
  .bulletin-nav-latest:hover { color: var(--teal); border-color: var(--teal); }
  .bulletin-nav-latest .latest-hint {
    font-size: 7px; letter-spacing: 0.18em; opacity: 0.6; font-weight: 500;
  }
  .bulletin-head h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(1.875rem, 3.2vw, 2.5rem); font-weight: 500; line-height: 1.1; letter-spacing: -0.025em; color: var(--ink); }
  .service-date { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.25em; color: var(--ink-soft); text-transform: uppercase; margin-top: 14px; }

  /* ORDER LINES — the mockup's dotted leader pattern */
  .order-list { position: relative; }
  .drag-handle {
    display: none;
    color: var(--ink-soft); opacity: 0.35;
    cursor: grab; user-select: none;
    font-family: monospace; font-size: 14px; line-height: 1;
    padding: 0 8px 0 0; letter-spacing: -2px;
    transition: opacity 0.15s, color 0.15s;
  }
  body.edit-mode .drag-handle { display: inline-block; }
  body.edit-mode .order-line:hover .drag-handle,
  body.edit-mode .section-header:hover .drag-handle { opacity: 0.85; color: var(--teal); }
  .drag-handle:active { cursor: grabbing; }
  .sortable-ghost { opacity: 0.35; background: rgba(3, 97, 122, 0.08); }
  .sortable-chosen { background: rgba(3, 97, 122, 0.04); }
  .undo-toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: var(--ink); color: #fff;
    padding: 12px 16px 12px 20px; border-radius: 6px;
    box-shadow: 0 12px 28px -6px rgba(0,0,0,0.35);
    display: flex; align-items: center; gap: 14px;
    font-family: 'Instrument Sans', sans-serif; font-size: 13px; letter-spacing: 0.04em;
    z-index: 100; transition: opacity 0.18s, transform 0.22s ease;
    max-width: calc(100vw - 32px);
    opacity: 0; pointer-events: none; visibility: hidden;
  }
  .undo-toast.show { opacity: 1; pointer-events: auto; visibility: visible; transform: translateX(-50%) translateY(0); }
  .undo-toast button {
    background: var(--gold, #b89a4a); color: #fff; border: 0;
    padding: 7px 14px; border-radius: 4px; cursor: pointer;
    font-family: inherit; font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600;
  }
  .undo-toast button:hover { background: #a78939; }
  /* Undo/Redo icon buttons in header — WCAG AA visibility (3:1 non-text contrast, 44px tap target).
     Disabled state stays visible enough to read (0.55 opacity → still ~3.5:1 against parchment). */
  .icon-btn {
    padding: 0 !important;
    width: 44px; height: 44px; min-width: 44px;
    font-size: 22px !important;
    background: #fff !important;
    border: 1px solid var(--ink-soft) !important;
    border-radius: 8px !important;
    color: var(--ink) !important;
    display: inline-flex; align-items: center; justify-content: center;
    line-height: 1;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
  }
  .icon-btn:hover { background: var(--teal); border-color: var(--teal); color: #fff !important; }
  .icon-btn:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .icon-btn:disabled { opacity: 0.55; cursor: not-allowed; background: transparent !important; }
  .icon-btn:disabled:hover { background: transparent !important; border-color: var(--ink-soft) !important; color: var(--ink) !important; }
  /* ── Header layout ──────────────────────────────────────── */
  .site-header.clerk .container.inner { gap: 14px; }
  .header-quick-actions { display: inline-flex; gap: 6px; align-items: center; }
  .header-right { position: relative; display: inline-flex; align-items: center; gap: 10px; }

  /* ── Admin trigger + dropdown panel (Anthropic-style) ────────
     Single rounded button "Admin ▾" replaces the old 7-button row + hamburger.
     Categorized panel with section labels in monospace. WCAG AA tap targets (≥44px). */
  .admin-trigger {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 26px;
    background: var(--ink);
    color: #fff;
    border: 1px solid var(--ink);
    border-radius: 8px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 600;
    letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    min-height: 44px;
  }
  /* Subtle hover — slight tonal shift, no color swap (Anthropic pattern: hover stays in
     the same family, doesn't jump to a different brand color). */
  .admin-trigger:hover { background: #2a3340; border-color: #2a3340; }
  .admin-trigger:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .admin-trigger .chev { transition: transform 0.2s; opacity: 0.85; }
  .admin-trigger[aria-expanded="true"] { background: #2a3340; border-color: #2a3340; }
  .admin-trigger[aria-expanded="true"] .chev { transform: rotate(180deg); }
  /* Go Live as a panel item — same typographic treatment as other items, just GREEN
     so it stands out without becoming a chunky button. Karlon's call: "but of course not as a button". */
  .admin-panel-item-go-live {
    color: #2d8659;
    font-weight: 600;
    margin-bottom: 6px;
  }
  .admin-panel-item-go-live:hover { color: #1f6843; }
  .admin-panel-item-go-live svg { color: #2d8659; opacity: 0.95; }
  .admin-panel-item-go-live:hover svg { color: #1f6843; opacity: 1; }
  /* When bulletin has unpublished changes, the Go Live trigger goes green AND pulses.
     The hover state stays green (NOT a different shade) — that fixes the previous
     inconsistency where Admin hovered to teal but dirty Go Live hovered to dark green.
     Now: not dirty → both triggers identical (ink → teal hover).
          dirty       → Go Live alone is green + pulse, hover stays green. */
  /* PowerBook sleep-light breath — slow 3.6s sinusoidal swell on the button itself.
     No expanding ring, no ricochet. The button surface gently brightens and dims
     like the tiny white LED on a closed PowerBook G3/G4. The inset highlight at
     peak gives it the optical quality of a real LED behind frosted plastic.
     prefers-reduced-motion: animation suppressed (still keeps the green resting state). */
  body.bulletin-dirty .admin-trigger-go-live,
  body.bulletin-dirty .admin-trigger-go-live:hover,
  body.bulletin-dirty .admin-trigger-go-live[aria-expanded="true"] {
    background: #2d8659;
    border-color: #2d8659;
    color: #fff;
    animation: breathe-go-live 3.6s ease-in-out infinite;
    will-change: background-color, box-shadow;
  }
  @keyframes breathe-go-live {
    0%, 100% {
      background-color: #25744a;
      box-shadow: 0 0 6px rgba(45,134,89,0.20),
                  inset 0 0 0 rgba(255,255,255,0);
    }
    50% {
      background-color: #3aa470;
      box-shadow: 0 0 18px rgba(60,180,110,0.55),
                  inset 0 0 14px rgba(255,255,255,0.10);
    }
  }
  @media (prefers-reduced-motion: reduce) {
    body.bulletin-dirty .admin-trigger-go-live,
    body.bulletin-dirty .admin-trigger-go-live:hover,
    body.bulletin-dirty .admin-trigger-go-live[aria-expanded="true"] {
      animation: none !important;
    }
  }

  /* Panel typography modeled on Anthropic's dropdown — serif body for items, soft sans-serif
     section labels, generous spacing. WCAG AA: 18px+ body, 4.5:1 contrast (ink #1a2332 on
     parchment #fefcef = ~13.5:1), 44px+ tap targets. */
  .admin-panel {
    display: none;
    position: absolute;
    top: calc(100% + 14px);
    right: 0;
    min-width: 320px;
    max-width: 380px;
    background: var(--parchment);
    border: 1px solid var(--line);
    border-radius: 18px;
    box-shadow: 0 18px 56px -10px rgba(26,35,50,0.25);
    padding: 24px 20px;
    z-index: 200;
  }
  .admin-panel.open { display: block; }

  .admin-panel-section + .admin-panel-section {
    margin-top: 22px;
    padding-top: 22px;
    border-top: 1px solid var(--line);
  }
  .admin-panel-label {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 13px; font-weight: 500;
    letter-spacing: 0.02em;
    color: var(--ink-soft);
    opacity: 0.7;
    margin: 0 4px 8px;
  }
  .admin-panel-item {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px;
    width: 100%;
    padding: 8px 4px;
    background: transparent;
    border: 0;
    border-radius: 6px;
    font-family: 'Cormorant Garamond', 'Palatino Linotype', Georgia, serif;
    font-size: 21px; font-weight: 500;
    letter-spacing: -0.005em;
    line-height: 1.25;
    color: var(--ink);
    text-align: left; text-decoration: none;
    cursor: pointer;
    transition: color 0.12s;
    min-height: 44px;
  }
  .admin-panel-item:hover { color: var(--teal); }
  .admin-panel-item:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .admin-panel-item:disabled { opacity: 0.35; cursor: not-allowed; }
  .admin-panel-item:disabled:hover { color: var(--ink); }
  .admin-panel-item svg {
    width: 14px; height: 14px; flex: 0 0 14px;
    color: var(--ink-soft); opacity: 0.55;
    transition: color 0.12s, opacity 0.12s;
  }
  .admin-panel-item:hover svg { color: var(--teal); opacity: 1; }
  .admin-panel-item-danger { color: #a82a1f; }
  .admin-panel-item-danger:hover { color: #c0392b; }
  .admin-panel-item-danger svg { color: #c0392b; opacity: 0.7; }
  .admin-panel-user {
    padding: 4px 4px 12px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 13px;
    color: var(--ink-soft);
    opacity: 0.7;
  }
  .ham-item-form { margin: 0; padding: 0; }

  /* Touch-device rule — covers portrait phones, landscape phones, and
     tablets where the desktop absolute-positioned panel would overflow
     the viewport with no way to scroll. Triggers on:
       - small portrait widths (<= 600px)
       - any coarse-pointer device (iPhone landscape, iPad)
     Panel becomes a bottom-fixed bottom sheet that scrolls internally. */
  @media (max-width: 600px), (pointer: coarse) {
    .admin-panel {
      position: fixed !important;
      top: auto !important;
      bottom: 16px;
      left: 16px;
      right: 16px;
      max-width: none;
      max-height: 70dvh;                        /* hard cap, leaves room for safe area */
      overflow-y: auto !important;              /* force scroll capability */
      -webkit-overflow-scrolling: touch;        /* momentum scroll on iOS */
      overscroll-behavior: contain;             /* don't bubble to body */
      padding: 22px 18px;
      /* Force GPU accel so iOS Safari treats this as scrollable */
      transform: translateZ(0);
      will-change: scroll-position;
    }
    .admin-panel-item { font-size: 19px; min-height: 44px; }
    .admin-trigger { padding: 10px 16px; font-size: 10px; gap: 6px; }
    .admin-trigger-label { line-height: 1; }
    .admin-trigger .chev { width: 11px; height: 11px; }
    /* Touch-friendly tap interception */
    .admin-trigger, .admin-panel-item { touch-action: manipulation; }

    /* Andre's ticket #9 fix:
       The theme-picker nested popover normally pops LEFT of the main panel
       (right: calc(100% + 6px)). On mobile that's off-screen. Instead, render
       it INLINE below the trigger so it fits the panel's full width. */
    .theme-pop {
      position: static !important;
      top: auto !important; right: auto !important; left: auto !important;
      width: 100%;
      margin-top: 8px;
      box-shadow: none;
      border: 1px dashed var(--line);
      padding: 12px;
    }
    .theme-pop.open { display: block; }
    .theme-swatches { gap: 6px !important; }
  }
  /* Theme picker now lives inside the hamburger as a nested popover */
  .theme-picker { position: relative; }
  .theme-pick-trigger {
    display: flex; align-items: center; gap: 10px;
    background: transparent; border: 0; padding: 0;
    color: inherit; cursor: pointer; width: 100%; text-align: left;
    font: inherit; letter-spacing: inherit;
  }
  .theme-pop {
    position: absolute; top: 0; right: calc(100% + 6px);
    background: #fff; border: 1px solid var(--line); border-radius: 6px;
    padding: 14px 14px 10px; min-width: 220px;
    box-shadow: 0 12px 28px -8px rgba(26,35,50,0.22);
    z-index: 100; display: none;
  }
  .theme-pop.open { display: block; }
  /* ── Event card actions + modal editor ── */
  .event { position: relative; }
  .event-body { min-width: 0; }
  .event-time {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 600;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--teal);
  }
  .event-loc { font-style: italic; color: var(--ink-soft); }
  .event-notes { color: var(--ink-soft); }
  .event-card-actions {
    position: absolute; top: 12px; right: 12px;
    display: flex; gap: 6px; z-index: 5;
  }
  .event-edit-btn, .event-delete-btn {
    background: #fff; border: 1px solid var(--line); border-radius: 50%;
    width: 36px; height: 36px;
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--ink-soft); cursor: pointer;
    box-shadow: 0 2px 4px rgba(26,35,50,0.06);
    transition: all 0.15s;
  }
  .event-edit-btn:hover { color: var(--teal); border-color: var(--teal); transform: scale(1.05); }
  .event-delete-btn:hover { color: #c0392b; border-color: rgba(192,57,43,0.5); transform: scale(1.05); }
  .event.has-flyer .event-card-actions { top: 10px; right: 10px; }

  /* ── Calendar event modal — same site DNA as the series modal ── */
  .event-modal {
    position: fixed; inset: 0;
    background: rgba(26, 35, 50, 0.62);
    z-index: 200; display: none;
    align-items: center; justify-content: center;
    padding: 24px;
    backdrop-filter: blur(2px);
  }
  .event-modal.open { display: flex; }
  .event-modal-card {
    background: var(--parchment);
    border-radius: 10px;
    width: 100%; max-width: 520px; max-height: 92vh;
    overflow-y: auto;
    box-shadow: 0 24px 56px -12px rgba(0, 0, 0, 0.35);
    position: relative;
  }
  .event-modal-head {
    padding: 28px 30px 18px;
    border-bottom: 1px solid var(--line);
    position: relative;
  }
  .event-modal-head h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px; font-weight: 500;
    letter-spacing: -0.01em;
    color: var(--ink); line-height: 1.1;
  }
  .event-modal-head h3::before {
    content: 'Calendar event';
    display: block;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.32em; text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 10px;
  }
  .event-modal-close {
    position: absolute;
    top: 16px; right: 18px;
    background: transparent; border: 0;
    font-size: 22px; line-height: 1;
    color: var(--ink-soft); cursor: pointer;
    width: 32px; height: 32px;
    border-radius: 50%;
    transition: background 0.15s, color 0.15s;
    display: inline-flex; align-items: center; justify-content: center;
  }
  .event-modal-close:hover { background: rgba(26, 35, 50, 0.06); color: var(--ink); }

  #event-form {
    padding: 24px 30px 28px;
    display: flex; flex-direction: column; gap: 18px;
  }
  .ev-row { display: flex; flex-direction: column; gap: 7px; }
  .ev-row > span {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.24em; text-transform: uppercase;
    color: var(--ink-soft);
  }
  .ev-row input, .ev-row textarea, .ev-row select {
    font: inherit; font-size: 15px;
    padding: 11px 14px;
    border: 1px solid var(--line); border-radius: 5px;
    background: #fff; color: var(--ink); width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .ev-row input:focus, .ev-row textarea:focus, .ev-row select:focus {
    outline: 0;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(3, 97, 122, 0.12);
  }
  .ev-row textarea { font-family: 'Poppins', sans-serif; line-height: 1.5; }
  .ev-row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .event-modal-actions {
    display: flex; gap: 10px; justify-content: flex-end;
    margin-top: 4px; padding-top: 20px;
    border-top: 1px solid var(--line);
  }
  .event-modal-actions .btn,
  .event-modal-actions .event-modal-cancel {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    padding: 11px 22px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.08s;
    background: transparent;
    border: 1px solid var(--line);
    color: var(--ink-soft);
  }
  .event-modal-actions .event-modal-cancel:hover {
    border-color: var(--ink); color: var(--ink);
  }
  .event-modal-actions .btn.primary {
    background: var(--teal);
    border-color: var(--teal);
    color: #fff;
  }
  .event-modal-actions .btn.primary:hover {
    background: var(--teal-dark); border-color: var(--teal-dark);
  }
  .event-modal-actions .btn.primary:active { transform: translateY(1px); }
  .event-modal-delete {
    margin-right: auto;
    color: #c0392b !important;
    border-color: rgba(192, 57, 43, 0.4) !important;
  }
  .event-modal-delete:hover {
    background: #c0392b !important; color: #fff !important; border-color: #c0392b !important;
  }
  @media (max-width: 540px) {
    .event-modal { padding: 0; align-items: flex-end; }
    .event-modal-card { max-width: 100%; max-height: 94vh; border-radius: 12px 12px 0 0; }
    .event-modal-head { padding: 24px 22px 16px; }
    .event-modal-head h3 { font-size: 24px; }
    #event-form { padding: 20px 22px 24px; }
    .ev-row-grid { grid-template-columns: 1fr; gap: 14px; }
  }

  /* ── Manage Names modal ── */
  .names-modal {
    position: fixed; inset: 0; background: rgba(26,35,50,0.55);
    z-index: 200; display: none; align-items: center; justify-content: center;
    padding: 24px;
  }
  .names-modal.open { display: flex; }
  .names-modal-card {
    background: #fff; border-radius: 8px; padding: 22px 22px 18px;
    max-width: 520px; width: 100%; max-height: 80vh; overflow-y: auto;
    box-shadow: 0 16px 36px -10px rgba(0,0,0,0.4);
  }
  .names-modal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
  .names-modal-head h3 { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 500; }
  .names-modal-head button {
    background: transparent; border: 0; font-size: 26px; color: var(--ink-soft);
    cursor: pointer; padding: 4px 10px; line-height: 1;
  }
  .names-modal-help { font-size: 13px; color: var(--ink-soft); margin-bottom: 14px; }
  .names-list { display: flex; flex-direction: column; gap: 4px; }
  .names-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 9px 12px; border-radius: 4px; background: var(--cream);
    font-size: 14px;
  }
  .names-row.blocked { opacity: 0.55; }
  .names-row .name-text { font-weight: 500; }
  .names-row .name-actions { display: flex; gap: 6px; }
  .names-row button {
    background: transparent; border: 1px solid var(--line); border-radius: 4px;
    padding: 4px 10px; font-family: 'Instrument Sans', sans-serif;
    font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600;
    color: var(--ink-soft); cursor: pointer; transition: all 0.15s;
  }
  .names-row button:hover { color: var(--teal); border-color: var(--teal); }
  .names-row button.danger:hover { color: #c0392b; border-color: #c0392b; }
  /* ── Preview-as-public banner ── */
  .preview-banner {
    background: var(--brass); color: #fff;
    padding: 10px 18px; display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; letter-spacing: 0.18em;
    text-transform: uppercase; font-weight: 600;
  }
  .preview-exit {
    background: rgba(255,255,255,0.18); color: #fff; text-decoration: none;
    padding: 6px 14px; border-radius: 4px; transition: background 0.15s;
  }
  .preview-exit:hover { background: rgba(255,255,255,0.32); }
  /* Mobile: header stays single-row; the Admin panel is the consolidation. */
  @media (max-width: 720px) {
    .theme-pop { right: 0; top: calc(100% + 6px); position: absolute; }
  }
  .theme-pop-label {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--ink-soft); font-weight: 600; margin-bottom: 10px;
  }
  .theme-swatches { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
  .theme-swatch {
    background: transparent; border: 1px solid transparent; cursor: pointer;
    padding: 6px 4px 8px; border-radius: 4px;
    display: flex; flex-direction: column; align-items: center; gap: 5px;
    transition: all 0.15s;
  }
  .theme-swatch:hover { background: rgba(3,97,122,0.05); }
  .theme-swatch.active { border-color: var(--teal); background: var(--teal-light); }
  .sw-bg { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(0,0,0,0.06); }
  .sw-dot { width: 14px; height: 14px; border-radius: 50%; }
  .sw-name {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 9px; letter-spacing: 0.06em;
    color: var(--ink-soft); font-weight: 600; line-height: 1.1;
  }
  .theme-swatch.active .sw-name { color: var(--teal); }
  .order-line { display: flex; align-items: baseline; gap: 10px; padding: 9px 0; font-size: 15px; }
  .order-line .part { font-family: 'Cormorant Garamond', serif; font-size: 18px; color: var(--ink); font-weight: 500; flex-shrink: 0; min-width: 150px; letter-spacing: -0.01em; }
  .order-line .leader { flex: 1; border-bottom: 1px dotted rgba(26, 35, 50, 0.22); height: 1px; transform: translateY(-4px); }
  .order-line .person { color: var(--ink-soft); font-style: italic; text-align: right; flex-shrink: 1; }
  .section-header {
    font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 17px;
    text-align: center; color: var(--teal-dark);
    padding: 10px 0; margin: 18px 0 8px;
    border-top: 1px solid rgba(3, 97, 122, 0.18); border-bottom: 1px solid rgba(3, 97, 122, 0.18);
    background: rgba(3, 97, 122, 0.03); border-radius: 3px;
  }

  /* ANNOUNCEMENTS */
  .announcements { margin-top: 44px; padding-top: 28px; border-top: 1px solid var(--line); }
  .announcements h3 { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 500; color: var(--teal-dark); margin-bottom: 16px; letter-spacing: -0.02em; }
  .announcement { padding: 14px 0; border-bottom: 1px dotted var(--line); }
  .announcement:last-child { border-bottom: 0; }
  .announcement .a-title { font-weight: 500; color: var(--ink); }
  .announcement .a-detail { font-size: 14px; color: var(--ink-soft); margin-top: 4px; line-height: 1.55; white-space: pre-line; }

  /* EVENTS — wider than bulletin column on desktop, then collapse */
  .events-container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 var(--gutter);
  }
  .events-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1px;
    background: var(--line);
    border: 1px solid var(--line);
    border-radius: 2px;
    overflow: hidden;
  }
  .event {
    background: var(--parchment);
    padding: 40px 40px;
    display: grid;
    grid-template-columns: 170px minmax(0, 1fr);
    gap: 32px;
    align-items: center;
    min-width: 0;
  }
  .event.has-flyer { grid-template-columns: 170px minmax(0, 1fr) 150px; }
  .event-flyer {
    display: flex; flex-direction: column;
    align-items: center; gap: 8px;
  }
  .event-flyer-thumb {
    width: 140px;
    background: #fff; border: 1px solid var(--line); border-radius: 4px;
    overflow: hidden; cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    box-shadow: 0 2px 8px -2px rgba(26, 35, 50, 0.12);
  }
  .event-flyer-thumb:hover { transform: scale(1.03); box-shadow: 0 8px 18px -4px rgba(26, 35, 50, 0.22); }
  .event-flyer-thumb img { width: 100%; height: auto; object-fit: contain; display: block; }
  .event-flyer-caption {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 9px; letter-spacing: 0.2em; text-transform: uppercase;
    color: var(--ink-soft); font-weight: 500;
    text-align: center; line-height: 1.4;
  }
  .event-flyer-upload {
    width: 140px; height: 140px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 6px;
    background: rgba(3, 97, 122, 0.04);
    border: 1.5px dashed rgba(3, 97, 122, 0.3);
    border-radius: 4px;
    cursor: pointer;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--teal); font-weight: 600;
    transition: all 0.15s;
  }
  .event-flyer-upload:hover { background: rgba(3, 97, 122, 0.08); border-color: var(--teal); }
  .event-flyer-upload svg { width: 24px; height: 24px; opacity: 0.7; }
  .event-flyer-remove {
    margin-top: 4px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase;
    color: var(--ink-soft); background: transparent; border: none; cursor: pointer;
    padding: 4px 8px;
  }
  .event-flyer-remove:hover { color: #c0392b; }

  /* Image lightbox — shares backdrop style with reader lightbox */
  .img-lightbox {
    position: fixed; inset: 0;
    background: rgba(26, 35, 50, 0.92);
    z-index: 350;
    display: none;
    align-items: center; justify-content: center;
    padding: 40px 24px;
  }
  .img-lightbox.open { display: flex; }
  .img-lightbox img {
    max-width: 100%; max-height: 100%;
    border-radius: 4px;
    box-shadow: 0 20px 60px -8px rgba(0, 0, 0, 0.8);
  }
  .img-lightbox-close {
    position: absolute; top: 20px; right: 20px;
    background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(6px);
    border: none; cursor: pointer;
    color: #fff; font-size: 20px;
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
  }
  .img-lightbox-close:hover { background: rgba(255, 255, 255, 0.25); }
  .event > * { min-width: 0; }

  /* Calendar tile — mini desk-calendar look */
  .cal-tile {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 4px;
    text-align: center;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(26,35,50,0.04);
    display: flex;
    flex-direction: column;
  }
  .cal-tile .month {
    background: var(--teal);
    color: #fff;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    padding: 8px 0 7px;
  }
  .cal-tile .day {
    font-family: 'Cormorant Garamond', serif;
    font-size: 72px;
    font-weight: 500;
    color: var(--ink);
    line-height: 1;
    letter-spacing: -0.04em;
    padding: 14px 0 8px;
  }
  .cal-tile .dow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ink-soft);
    padding: 0 0 12px;
  }

  .event-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px;
    font-weight: 500;
    color: var(--ink);
    line-height: 1.18;
    letter-spacing: -0.02em;
    overflow-wrap: anywhere;
  }
  .event-meta {
    font-size: 14px;
    color: var(--ink-soft);
    margin-top: 10px;
    line-height: 1.6;
    overflow-wrap: anywhere;
  }
  .event-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: 10px; vertical-align: middle; }

  /* DEPARTMENT CALENDAR (member duty view) */
  .dept-pills { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 28px; }
  .dept-pill {
    padding: 9px 18px; border-radius: 24px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px;
    letter-spacing: 0.12em; text-transform: uppercase; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
    background: rgba(26, 35, 50, 0.06); color: var(--ink-soft);
    border: 1px solid transparent; user-select: none;
  }
  .dept-pill:hover { background: rgba(26, 35, 50, 0.1); }
  .dept-pill.active { color: #fff; background: var(--dept-color, var(--ink)); border-color: var(--dept-color, var(--ink)); }
  .duty-list { display: flex; flex-direction: column; gap: 10px; }
  .duty-row {
    display: grid;
    grid-template-columns: 84px 1fr;
    align-items: center;
    gap: 20px;
    padding: 18px 22px;
    background: #fff;
    border: 1px solid var(--line);
    border-left: 4px solid;
    border-radius: 4px;
    transition: opacity 0.15s, transform 0.15s;
  }
  .duty-row.hidden { display: none; }
  .duty-date { font-family: 'Instrument Sans', sans-serif; font-size: 10px; letter-spacing: 0.18em; color: var(--teal); text-transform: uppercase; text-align: center; padding-top: 2px; font-weight: 600; }
  .duty-date .day { font-family: 'Cormorant Garamond', serif; font-size: 32px; color: var(--ink); line-height: 1; display: block; margin-top: 2px; letter-spacing: -0.02em; font-weight: 500; }
  .duty-who { font-family: 'Cormorant Garamond', serif; font-size: 21px; color: var(--ink); font-weight: 500; letter-spacing: -0.01em; line-height: 1.2; }
  .duty-what { font-size: 14px; color: var(--ink-soft); margin-top: 6px; line-height: 1.5; }
  @media (max-width: 600px) {
    .duty-row { grid-template-columns: 64px 1fr; gap: 14px; padding: 14px 16px; }
    .duty-date .day { font-size: 26px; }
    .duty-who { font-size: 18px; }
    .duty-what { font-size: 13px; }
  }

  /* DEPARTMENT CALENDAR (member view) */
  .dept-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 32px; }
  .dept-pill {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; letter-spacing: 0.18em;
    text-transform: uppercase;
    padding: 8px 16px; border-radius: 20px; cursor: pointer;
    background: rgba(26, 35, 50, 0.06);
    color: var(--ink-soft);
    border: 1px solid transparent;
    transition: all 0.18s;
    font-weight: 500;
  }
  .dept-pill:hover { color: var(--ink); }
  .dept-pill.active { background: var(--dept-color, var(--ink)); color: #fff; }
  .duty-list { display: flex; flex-direction: column; gap: 8px; }
  .duty-row {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr);
    gap: 18px;
    align-items: center;
    background: #fff;
    border-left: 3px solid var(--ink-soft);
    padding: 18px 22px;
    border-radius: 0 4px 4px 0;
  }
  .duty-row.hidden { display: none; }
  .duty-date {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.18em;
    color: var(--teal); text-transform: uppercase;
    text-align: center; line-height: 1.1;
  }
  .duty-date .day {
    font-family: 'Cormorant Garamond', serif;
    font-size: 30px; color: var(--ink);
    letter-spacing: -0.02em; display: block; margin-top: 2px; line-height: 1;
  }
  .duty-who { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 500; color: var(--ink); letter-spacing: -0.01em; line-height: 1.2; }
  .duty-what { font-size: 13px; color: var(--ink-soft); margin-top: 4px; }

  /* CLOSING — quiet benediction, generous air */
  .closing { text-align: center; padding: 140px 0 120px; }
  .closing .pleasant {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.375rem, 2vw, 1.625rem);
    color: var(--teal-dark);
    letter-spacing: 0.01em;
    font-weight: 400;
    line-height: 1.5;
    opacity: 0.85;
    max-width: 420px;
    margin: 0 auto;
  }
  .closing .pleasant::before,
  .closing .pleasant::after {
    content: '';
    display: block;
    width: 28px;
    height: 1px;
    background: var(--line);
    margin: 22px auto;
  }
  .closing address {
    font-style: normal;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: var(--ink-soft);
    margin-top: 40px;
    line-height: 1.85;
    letter-spacing: 0.01em;
  }
  .closing a { color: var(--teal); text-decoration: none; }
  .closing a:hover { text-decoration: underline; }

  /* ── Closing rotator ───────────────────────────────────────────────────
     Two items share the same vertical space. The MAIN greeting (Sabbath line
     or peace verse) is the anchor — bigger, more confident. The Daily Psalm
     is the passing thought — smaller, italic, with citation. They sequentially
     fade (one fully out → gap → next fully in), not cross-fade.

     Cycle: 22s — generous enough to actually read each one. ─────────────── */
  .closing .closing-rotator {
    position: relative;
    min-height: 200px;        /* tall enough for the main greeting at full size */
    max-width: 620px;
    margin: 0 auto;
  }
  .closing .closing-rotator .rotator-item {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;       /* center vertically in the same box */
    justify-content: center;
    flex-direction: column;
    text-align: center;
  }

  /* Kill the brass divider lines inside the rotator — they were inconsistent
     between Sabbath text (just ascender lines around it) and the peace verse
     (ascender lines + cite + visual weight). Simpler is steadier. */
  .closing .closing-rotator .pleasant::before,
  .closing .closing-rotator .pleasant::after { display: none; }

  /* MAIN GREETING — the verse, in dignified all-caps Cormorant. */
  .closing .closing-rotator .pleasant {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.375rem, 2.2vw, 1.75rem);
    line-height: 1.45;
    color: var(--teal-dark);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    opacity: 1;
    max-width: 580px;
    animation: pleasantFade 22s ease-in-out infinite;
  }
  /* John 14:27 cite under the peace verse — brass tracking caps */
  .closing .greeting-cite {
    display: block;
    margin-top: 18px;
    font-family: 'Instrument Sans', sans-serif;
    font-style: normal;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: var(--brass);
  }

  /* BRAND MARK — second rotator slot. Same wordmark as email/login: big blue serif. */
  .closing .closing-rotator .brand-mark {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2.25rem, 4.5vw, 3.25rem);
    font-weight: 500;
    letter-spacing: -0.025em;
    line-height: 1.05;
    color: var(--teal);
    opacity: 0;
    animation: brandFade 22s ease-in-out infinite;
  }

  /* Sequential fade — slower than before. 22s cycle:
       A visible 0-32%  (~7s)
       A fade out 32-40%  (~1.7s)
       gap 40-42%
       B fade in 42-50%  (~1.7s)
       B visible 50-80%  (~6.6s)
       B fade out 80-88%  (~1.7s)
       gap 88-92%
       A fade in 92-100% */
  @keyframes pleasantFade {
    0%, 32%   { opacity: 1; }
    40%, 92%  { opacity: 0; }
    100%      { opacity: 1; }
  }
  @keyframes brandFade {
    0%, 42%   { opacity: 0; }
    50%, 80%  { opacity: 1; }
    88%, 100% { opacity: 0; }
  }
  @media (prefers-reduced-motion: reduce) {
    .closing .closing-rotator { min-height: 0; }
    .closing .closing-rotator .rotator-item { animation: none; opacity: 1; position: static; margin-bottom: 24px; }
  }
  @media (max-width: 600px) {
    .closing .closing-rotator { min-height: 220px; }
    .closing .closing-rotator .pleasant { font-size: 1.25rem; letter-spacing: 0.05em; }
    .closing .closing-rotator .brand-mark { font-size: 2.25rem; }
  }

  footer.site {
    padding: 32px 24px 64px;
    text-align: center;
    font-size: 13px; color: var(--ink-soft);
    border-top: 1px solid var(--line);
    font-family: 'Instrument Sans', sans-serif;
    letter-spacing: 0.08em; font-weight: 500;
    text-transform: uppercase;
  }
  footer.site .copyright { margin-bottom: 18px; }
  footer.site address {
    font-style: normal;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.85;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--ink-soft);
  }
  footer.site address a {
    color: var(--ink-soft);
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: color 0.15s, border-color 0.15s;
  }
  footer.site address a:hover { color: var(--teal); border-bottom-color: var(--teal); }
  @media (max-width: 600px) {
    footer.site { padding: 28px 20px 56px; }
  }
  footer.site .esv-attr {
    display: block; max-width: 720px; margin: 12px auto 0;
    font-family: 'JetBrains Mono', monospace;
    font-size: 9px; letter-spacing: 0.04em; line-height: 1.6;
    text-transform: none; color: var(--ink-soft); opacity: 0.6;
  }

  /* ═══ SEARCH OVERLAY + LIGHTBOX ═══ */
  .search-overlay {
    position: fixed; inset: 0;
    background: color-mix(in srgb, var(--parchment) 97%, transparent);
    backdrop-filter: blur(10px);
    z-index: 200;
    display: none;
    overflow-y: auto;
  }
  .search-overlay.open { display: block; }
  .search-overlay .inner {
    max-width: 760px;
    margin: 0 auto;
    padding: 80px 24px 40px;
  }
  .search-close {
    position: absolute;
    top: 24px; right: 32px;
    background: none; border: none; cursor: pointer;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--ink-soft);
    padding: 10px 18px;
    border: 1px solid var(--line);
    border-radius: 2px;
  }
  .search-close:hover { color: var(--teal); border-color: var(--teal); }
  .search-input-wrap {
    border-bottom: 2px solid var(--ink);
    padding: 0 0 12px;
    margin-bottom: 28px;
    display: flex; align-items: center; gap: 14px;
  }
  .search-input-wrap svg { color: var(--ink); flex-shrink: 0; }
  .search-input {
    flex: 1;
    border: none; background: transparent; outline: none;
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(28px, 4vw, 40px);
    font-weight: 500;
    color: var(--ink);
    letter-spacing: -0.02em;
    padding: 4px 0;
  }
  .search-input::placeholder { color: rgba(26, 35, 50, 0.3); }
  .search-tabs {
    display: flex; gap: 4px;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--line);
  }
  .search-tab {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    font-weight: 600;
    padding: 12px 20px;
    background: transparent; border: none; cursor: pointer;
    color: var(--ink-soft);
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
  }
  .search-tab.active { color: var(--teal); border-bottom-color: var(--teal); }
  .search-tab:hover { color: var(--teal-dark); }
  .search-tab .count { opacity: 0.5; font-weight: 500; margin-left: 6px; }

  .search-hint {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 13px; color: var(--ink-soft);
    padding: 14px 0; font-style: italic;
  }
  .search-results { }
  .search-result {
    display: block; width: 100%;
    text-align: left; padding: 16px 4px;
    background: transparent; border: none;
    border-bottom: 1px solid var(--line);
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: background 0.15s;
  }
  .search-result:hover { background: rgba(3, 97, 122, 0.04); }
  .search-result .ref {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--teal);
    font-weight: 600;
    margin-bottom: 6px;
  }
  .search-result .preview {
    font-family: 'Cormorant Garamond', serif;
    font-size: 19px;
    color: var(--ink);
    line-height: 1.4;
    letter-spacing: -0.005em;
  }
  .search-result mark {
    background: rgba(3, 97, 122, 0.15);
    color: var(--teal-dark);
    font-weight: 500;
    padding: 0 2px;
    border-radius: 2px;
  }
  .search-loading {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px; color: var(--ink-soft);
    letter-spacing: 0.2em; text-transform: uppercase;
    padding: 28px 0; text-align: center;
  }

  /* Lightbox reader — modal card with fixed header, scrolling body inside */
  .lightbox {
    position: fixed; inset: 0;
    background: rgba(251, 244, 224, 0.94);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    z-index: 300;
    display: none;
    padding: 24px;
    overflow: hidden; /* page doesn't scroll, only body inside card does */
  }
  .lightbox.open { display: flex; align-items: stretch; justify-content: center; }
  .lightbox-card {
    background: var(--cream);
    width: 100%; max-width: 720px;
    height: 100%; max-height: calc(100vh - 48px);
    border-radius: 8px;
    box-shadow: 0 40px 80px -20px rgba(26, 35, 50, 0.35);
    display: flex; flex-direction: column;
    overflow: hidden;
    border: 1px solid var(--line);
  }
  .lightbox-head {
    padding: 28px 36px 22px;
    border-bottom: 1px solid var(--line);
    flex-shrink: 0;
    position: relative;
    background: var(--cream);
  }
  .lightbox-close {
    position: absolute;
    top: 14px; right: 14px;
    background: transparent; border: none; cursor: pointer;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 18px; color: var(--ink-soft);
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    transition: all 0.15s;
  }
  .lightbox-close:hover { color: var(--teal); background: rgba(3, 97, 122, 0.08); }
  .lightbox-ref {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--teal);
    font-weight: 600;
    margin-bottom: 8px;
    padding-right: 40px;
  }
  .lightbox-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(26px, 4vw, 36px);
    font-weight: 500;
    color: var(--ink);
    letter-spacing: -0.025em;
    line-height: 1.1;
    padding-right: 40px;
  }
  /* Lightbox action row — unified pill/button sizing */
  .lightbox-actions {
    display: flex; gap: 8px; flex-wrap: wrap; align-items: stretch;
    margin-top: 16px;
  }
  .lightbox-actions > * { height: 36px; }
  .lightbox-translation-toggle {
    display: inline-flex;
    border: 1px solid var(--line);
    border-radius: 2px;
    overflow: hidden;
  }
  .lightbox-translation-toggle button {
    background: transparent; border: none; cursor: pointer;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase;
    font-weight: 600;
    padding: 0 18px;
    color: var(--ink-soft);
    display: inline-flex; align-items: center;
    line-height: 1;
  }
  .lightbox-translation-toggle button.active { background: var(--teal); color: #fff; }
  .lightbox-share {
    background: transparent; cursor: pointer;
    border: 1px solid var(--line); border-radius: 2px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase;
    font-weight: 600;
    padding: 0 16px;
    color: var(--ink-soft);
    display: inline-flex; align-items: center; gap: 8px;
    line-height: 1;
    transition: all 0.15s;
  }
  .lightbox-share:hover { color: var(--teal); border-color: var(--teal); }
  .lightbox-share svg { width: 13px; height: 13px; flex-shrink: 0; }
  .share-toast {
    position: fixed;
    bottom: 32px; left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: var(--ink); color: var(--parchment);
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; letter-spacing: 0.2em; text-transform: uppercase; font-weight: 600;
    padding: 12px 22px; border-radius: 2px;
    opacity: 0; transition: all 0.25s;
    z-index: 400; pointer-events: none;
    box-shadow: 0 10px 30px -6px rgba(26, 35, 50, 0.4);
  }
  .share-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
  .lightbox-body-wrap {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 28px 36px 40px;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
  }
  .lightbox-body {
    font-family: 'Cormorant Garamond', serif;
    font-size: 20px;
    line-height: 1.55;
    color: var(--ink);
    letter-spacing: -0.005em;
  }
  .lightbox-body p { margin: 12px 0; }
  .lightbox-body .verse-num {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px;
    font-weight: 600;
    color: var(--teal);
    vertical-align: super;
    margin-right: 4px;
  }
  .lightbox-body p {
    cursor: pointer;
    padding: 6px 10px;
    margin: 2px -10px;
    border-radius: 4px;
    transition: background 0.15s;
  }
  .lightbox-body p:hover { background: rgba(3, 97, 122, 0.05); }
  .lightbox-body p.selected { background: rgba(3, 97, 122, 0.14); }
  .lightbox-body p.selected .verse-num { color: var(--teal-dark); font-size: 13px; }
  /* Hymn-number tap targets in the bulletin order — open lightbox with title + lyrics.
     Padding + min sizing ensures the touch target is iOS-friendly (>= 44×44 px). */
  .hymn-tap {
    display: inline-flex;
    align-items: center; justify-content: center;
    background: none; border: 0;
    padding: 8px 12px;
    min-height: 44px; min-width: 44px;
    margin: -8px -2px;  /* negative margin so the larger tap area doesn't push surrounding text */
    font: inherit; color: var(--teal); cursor: pointer;
    text-decoration: underline;
    text-decoration-style: dotted;
    text-decoration-thickness: 1px;
    text-underline-offset: 3px;
    border-radius: 4px;
  }
  .hymn-tap:hover { color: var(--teal-dark); text-decoration-style: solid; }
  .hymn-tap:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }

  .lightbox-body .hymn-verse-num {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px;
    font-weight: 600;
    color: var(--teal);
    letter-spacing: 0.18em;
    text-transform: uppercase;
    display: block;
    margin: 22px 0 8px;
  }
  .lightbox-body .hymn-verse-num:first-child { margin-top: 0; }

  @media (max-width: 700px) {
    .search-overlay .inner { padding: 60px 18px 32px; }
    .search-close { top: 14px; right: 14px; padding: 7px 12px; font-size: 10px; }
    .search-input { font-size: 26px; }
    .search-tab { padding: 10px 14px; font-size: 10px; letter-spacing: 0.16em; }
    /* Header search button — icon only */
    .search-btn-label { display: none; }
    .search-btn { padding: 8px 12px; }
    /* Lightbox goes edge-to-edge on phones */
    .lightbox { padding: 0; }
    .lightbox-card { max-width: none; max-height: 100vh; height: 100vh; border-radius: 0; border: none; }
    .lightbox-head { padding: 22px 20px 16px; }
    .lightbox-body-wrap { padding: 20px 20px 32px; }
    .lightbox-body { font-size: 18px; }
  }

  /* ─── EDIT MODE (only applied when clerk is logged in) ─── */
  .editable {
    cursor: pointer;
    transition: background 0.15s, outline 0.15s;
    border-radius: 3px;
    outline: 1px dashed transparent;
    outline-offset: 4px;
  }
  .editable:hover { outline-color: rgba(3, 97, 122, 0.35); background: rgba(3, 97, 122, 0.04); }
  .editable.editing { outline: 2px solid var(--teal); outline-offset: 2px; background: rgba(3, 97, 122, 0.06); }
  /* Distinguish the two columns of an order line: PART (left, "what") tints teal,
     PERSON (right, "who") tints brass — so Andre stops typing hymn entries into
     the person column. Also nudges him to the right field on first hover. */
  .order-line .part.editable:hover { outline-color: rgba(3, 97, 122, 0.45); background: rgba(3, 97, 122, 0.06); }
  .order-line .person.editable:hover { outline-color: rgba(176, 141, 60, 0.45); background: rgba(176, 141, 60, 0.06); }
  .order-line .person.editable.editing { outline-color: var(--brass); background: rgba(176, 141, 60, 0.07); }
  /* Edit-mode hint banner — explains the two fields + points to Manage Names */
  body.edit-mode .order-list .order-list-hint {
    font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em;
    color: var(--ink-soft); padding: 10px 14px; margin-bottom: 14px;
    background: rgba(3,97,122,0.04); border-left: 3px solid var(--teal);
    border-radius: 2px; line-height: 1.7;
  }
  body.edit-mode .order-list .order-list-hint .swatch-part { color: var(--teal); font-weight: 600; }
  body.edit-mode .order-list .order-list-hint .swatch-person { color: var(--brass); font-weight: 600; }
  body.edit-mode .order-list .order-list-hint a { color: var(--teal); border-bottom: 1px dotted var(--teal); text-decoration: none; }
  body:not(.edit-mode) .order-list-hint { display: none; }
  /* Empty fields: invisible to public (line is hidden if all empty),
     still clickable in clerk mode — a dashed area marks where content would go. */
  .editable.empty-field {
    min-width: 80px; min-height: 1em;
    display: inline-block;
    outline: 1px dashed rgba(3, 97, 122, 0.3);
    outline-offset: 2px;
    opacity: 0.6;
  }
  .editable.empty-field:hover { opacity: 1; outline-style: solid; }
  /* Empty field content in public view — invisible */
  body:not(.edit-mode) .empty-field { visibility: hidden; }
  .editable input, .editable[contenteditable] { font: inherit; color: inherit; background: transparent; border: 0; outline: 0; width: 100%; padding: 0; }
  .save-indicator { position: fixed; top: 84px; right: 24px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--teal); background: #fff; padding: 8px 14px; border: 1px solid var(--line); border-radius: 2px; opacity: 0; transition: opacity 0.3s; z-index: 100; }
  .save-indicator.show { opacity: 1; }
  /* Inline publish bar — sits right below the bulletin card */
  .publish-bar { margin-top: 20px; background: #fff; border: 1px solid var(--line); border-radius: 4px; box-shadow: 0 2px 12px -4px rgba(26,35,50,0.08); padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
  .publish-bar .status { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-soft); }
  .publish-bar .status.unpub { color: var(--brass); }
  @media (max-width: 500px) {
    .publish-bar { flex-direction: column; align-items: stretch; gap: 10px; text-align: center; }
  }
  /* (admin-actions row removed — replaced by single Admin trigger + dropdown panel) */
  .row-actions { display: inline-flex; gap: 6px; margin-left: 10px; opacity: 0.45; transition: opacity 0.2s; }
  .order-line:hover .row-actions, .announcement:hover .row-actions, .section-header:hover .row-actions, .event:hover .row-actions { opacity: 1; }
  .row-actions button { background: transparent; border: 1px solid transparent; cursor: pointer; color: var(--ink-soft); font-size: 14px; padding: 4px 8px; border-radius: 3px; line-height: 1; }
  .row-actions button:hover { color: #c0392b; border-color: rgba(192, 57, 43, 0.3); }
  /* Touch devices: row-actions need bigger tap targets + visible affordance.
     Apple recommends 44×44 minimum; we go ~36-40px tall (bulletin rows are dense),
     plus a subtle red tint so the × button reads as "tappable to delete". */
  @media (hover: none) and (pointer: coarse) {
    .row-actions { opacity: 1; }
    .row-actions button {
      padding: 9px 14px !important;
      font-size: 16px !important;
      min-width: 38px;
      background: rgba(192, 57, 43, 0.07) !important;
      border-color: rgba(192, 57, 43, 0.22) !important;
      color: #c0392b !important;
    }
    .row-actions button:active {
      background: rgba(192, 57, 43, 0.18) !important;
    }
  }
  .add-btn { display: inline-flex; align-items: center; gap: 6px; margin-top: 12px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.25em; text-transform: uppercase; color: var(--teal); background: transparent; border: 1px dashed var(--teal); border-radius: 2px; padding: 8px 14px; cursor: pointer; }
  .add-btn:hover { background: rgba(3,97,122,0.06); }

  /* Flatpickr — big parchment-themed calendar. Grid forces exact 1/7 columns. */
  .flatpickr-calendar {
    font-family: 'Instrument Sans', sans-serif !important;
    width: 440px !important;
    padding: 14px !important;
    background: var(--cream) !important;
    border: 1px solid var(--line) !important;
    border-radius: 6px !important;
    box-shadow: 0 20px 48px -12px rgba(26, 35, 50, 0.25) !important;
    box-sizing: border-box !important;
  }
  .flatpickr-calendar.arrowTop:before, .flatpickr-calendar.arrowTop:after { border-bottom-color: var(--cream) !important; }
  .flatpickr-months { padding: 4px 0 10px !important; }
  .flatpickr-months .flatpickr-month { height: 42px !important; color: var(--ink) !important; overflow: visible !important; }
  .flatpickr-current-month { font-size: 20px !important; font-weight: 600 !important; padding: 6px 0 !important; color: var(--teal-dark) !important; }
  .flatpickr-current-month .cur-year { font-weight: 500 !important; color: var(--ink) !important; }
  .flatpickr-monthDropdown-months { font-size: 20px !important; font-weight: 600 !important; color: var(--teal-dark) !important; background: var(--cream) !important; }
  .flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month { fill: var(--teal) !important; padding: 10px !important; }
  .flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg { fill: var(--teal-dark) !important; }

  /* Align weekday row + day grid perfectly — force every intermediate container
     to the same full width, then both inner grids share identical geometry. */
  .flatpickr-innerContainer,
  .flatpickr-rContainer,
  .flatpickr-days,
  .flatpickr-weekdays {
    width: 100% !important;
    min-width: 0 !important;
    max-width: none !important;
    box-sizing: border-box !important;
    display: block !important;
  }
  .flatpickr-weekdays { background: transparent !important; }
  .flatpickr-weekdaycontainer,
  .dayContainer {
    width: 100% !important;
    min-width: 0 !important;
    max-width: none !important;
    display: grid !important;
    grid-template-columns: repeat(7, 1fr) !important;
    gap: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
  }
  .dayContainer { padding: 4px 0 !important; }
  .flatpickr-weekday {
    font-size: 11px !important; font-weight: 600 !important;
    color: var(--ink-soft) !important; letter-spacing: 0.1em !important;
    text-transform: uppercase !important;
    height: 32px !important; line-height: 32px !important;
    text-align: center !important;
    width: 100% !important; min-width: 0 !important; max-width: none !important;
    flex: none !important;
    margin: 0 !important; padding: 0 !important;
    box-sizing: border-box !important;
  }
  .flatpickr-day {
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
    flex: none !important;
    height: 50px !important;
    line-height: 50px !important;
    font-size: 16px !important;
    font-weight: 500 !important;
    color: var(--ink) !important;
    border-radius: 4px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin: 1px 0 !important;
    box-sizing: border-box !important;
  }
  .flatpickr-day.prevMonthDay, .flatpickr-day.nextMonthDay { color: rgba(26, 35, 50, 0.3) !important; }
  .flatpickr-day:hover { background: var(--teal-light) !important; border-color: var(--teal-light) !important; }
  .flatpickr-day.selected, .flatpickr-day.selected:hover { background: var(--teal) !important; border-color: var(--teal) !important; color: #fff !important; }
  .flatpickr-day.today { border-color: var(--brass) !important; }
  .flatpickr-day.today:hover { background: var(--brass) !important; color: #fff !important; }

  .flatpickr-time { border-top: 1px solid var(--line) !important; padding-top: 8px !important; height: 48px !important; }
  .flatpickr-time input { font-size: 17px !important; color: var(--ink) !important; font-family: 'Instrument Sans', sans-serif !important; font-weight: 600 !important; }
  .flatpickr-time .flatpickr-time-separator, .flatpickr-time .flatpickr-am-pm { font-size: 17px !important; color: var(--ink) !important; }

  @media (max-width: 500px) {
    .flatpickr-calendar { width: calc(100vw - 24px) !important; max-width: 380px; }
    .flatpickr-day { height: 42px !important; line-height: 42px !important; font-size: 15px !important; }
  }

  /* Suggest dropdown */
  .suggest { position: absolute; background: #fff; border: 1px solid var(--teal); border-radius: 4px; box-shadow: 0 8px 20px -6px rgba(3,97,122,0.25); min-width: 220px; max-height: 240px; overflow-y: auto; z-index: 60; font-family: 'Poppins', sans-serif; }
  .suggest-item { padding: 8px 12px; font-size: 13px; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.04); }
  .suggest-item:last-child { border-bottom: 0; }
  .suggest-item:hover, .suggest-item.keyed { background: var(--teal-light); }

  /* RESPONSIVE */
  @media (max-width: 1100px) {
    .event { padding: 32px 28px; grid-template-columns: 140px minmax(0, 1fr); gap: 24px; }
    .event.has-flyer { grid-template-columns: 140px minmax(0, 1fr) 130px; }
    .event-flyer-thumb { width: 120px; }
    .event-flyer-upload { width: 120px; height: 120px; }
    .cal-tile .day { font-size: 60px; padding: 10px 0 6px; }
    .event-title { font-size: 24px; }
  }
  @media (max-width: 900px) {
    .events-grid { grid-template-columns: minmax(0, 1fr); }
    .event { padding: 28px 24px; grid-template-columns: 128px minmax(0, 1fr); gap: 20px; }
    .event.has-flyer { grid-template-columns: 110px minmax(0, 1fr) 110px; gap: 16px; padding: 24px 20px; }
    .event-flyer-thumb { width: 100px; }
    .event-flyer-upload { width: 100px; height: 100px; }
    .cal-tile .day { font-size: 52px; }
    .event-title { font-size: 22px; }
    .hero { padding: 80px 0 64px; }
    .section { padding: 64px 0; }
    .bulletin-card { padding: 44px 40px; }
    .closing { padding: 72px 0 56px; }
  }
  @media (max-width: 600px) {
    :root { --gutter: 18px; }
    body { font-size: 15px; }
    .site-header { padding: 16px 0; }
    .brand { font-size: 46px; letter-spacing: -0.02em; }
    .brand-sub { font-size: 8px; letter-spacing: 0.24em; margin-top: 4px; }
    .btn { padding: 8px 14px; font-size: 9px; }
    .hero { padding: 56px 0 40px; }
    .hero .lede { font-size: 16px; }
    .bulletin-card { padding: 32px 22px; }
    .order-line .part { min-width: 110px; font-size: 16px; }
    .order-line { font-size: 14px; }
    .event { grid-template-columns: 96px minmax(0, 1fr); gap: 16px; padding: 22px 18px; }
    .event.has-flyer {
      grid-template-columns: 80px minmax(0, 1fr);
      grid-template-areas: "date title" "flyer flyer";
      gap: 14px;
    }
    .event.has-flyer .cal-tile { grid-area: date; }
    .event.has-flyer > div:nth-child(2) { grid-area: title; }
    .event.has-flyer .event-flyer {
      grid-area: flyer;
      width: 100%;
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }
    .event.has-flyer .event-flyer-thumb {
      width: 100%;
      height: auto;
      aspect-ratio: auto;
    }
    .event.has-flyer .event-flyer-thumb img {
      width: 100%;
      height: auto;
      object-fit: contain;
    }
    .event.has-flyer .event-flyer-upload {
      width: 100%;
      height: 180px;
    }
    .cal-tile .month { font-size: 10px; padding: 6px 0 5px; }
    .cal-tile .day { font-size: 40px; padding: 8px 0 4px; }
    .cal-tile .dow { font-size: 8px; padding: 0 0 8px; }
    .event-title { font-size: 19px; }
    .event-meta { font-size: 13px; }
  }

  /* ── Theme-toggle pill (sun + moon) — sits inside the Admin ▾ menu ── */
  .admin-panel-user-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; padding: 4px 4px 12px;
  }
  .admin-panel-user-row .admin-panel-user { padding: 0; }
  .theme-toggle {
    display: inline-flex; align-items: center; gap: 0;
    padding: 3px;
    background: rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.10);
    border-radius: 999px;
    line-height: 1;
  }
  .theme-toggle button {
    width: 26px; height: 26px;
    display: inline-flex; align-items: center; justify-content: center;
    background: transparent; border: 0;
    border-radius: 999px;
    color: var(--ink-soft);
    cursor: pointer;
    padding: 0;
    transition: background 0.15s, color 0.15s;
  }
  .theme-toggle button:hover { color: var(--ink); background: rgba(0,0,0,0.04); }
  .theme-toggle button[aria-pressed="true"] {
    background: #fff; color: var(--ink);
    box-shadow: 0 1px 3px rgba(0,0,0,0.10);
  }
  .theme-toggle button:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .theme-toggle svg { width: 13px; height: 13px; display: block; }

  /* ── Dark mode — overrides every bulletin theme variant by remapping CSS vars ── */
  html.dark body,
  html.dark body[data-theme="default"],
  html.dark body[data-theme="communion"],
  html.dark body[data-theme="easter"],
  html.dark body[data-theme="christmas"],
  html.dark body[data-theme="mothers"],
  html.dark body[data-theme="thanksgiving"] {
    --parchment: #191919;
    --cream:     #222222;
    --ink:       #ededed;
    --ink-soft:  #9e9e9e;
    --teal:      #4fb8d4;
    --teal-dark: #6cc8e0;
    --teal-light: rgba(79,184,212,0.14);
    --line:      rgba(255,255,255,0.10);
    --brass:     #d4a85a;
  }
  /* ── Dark mode (chrome only — bulletin theme variants TBD) ── */
  html.dark { color-scheme: dark; }
  html.dark, html.dark body { background: #191919 !important; color: #ededed !important; }
  html.dark .admin-panel { background: #222222 !important; border-color: rgba(255,255,255,0.10) !important; box-shadow: 0 12px 36px rgba(0,0,0,0.55) !important; }
  html.dark .admin-panel-label { color: #9e9e9e !important; }
  html.dark .admin-panel-item { color: #ededed !important; }
  html.dark .admin-panel-item:hover { color: #4fb8d4 !important; }
  html.dark .admin-panel-item svg { color: #9e9e9e !important; }
  html.dark .admin-panel-user { color: #9e9e9e !important; }
  html.dark .admin-panel-section + .admin-panel-section { border-top-color: rgba(255,255,255,0.10) !important; }
  html.dark .admin-trigger { background: #2a2a2a !important; color: #ededed !important; border-color: #2a2a2a !important; }
  html.dark .admin-trigger:hover, html.dark .admin-trigger[aria-expanded="true"] { background: #333333 !important; border-color: #333333 !important; }
  html.dark .theme-toggle { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.12); }
  html.dark .theme-toggle button { color: #9e9e9e; }
  html.dark .theme-toggle button:hover { color: #ededed; background: rgba(255,255,255,0.06); }
  html.dark .theme-toggle button[aria-pressed="true"] { background: #ededed; color: #191919; box-shadow: none; }
  html.dark .btn, html.dark button.btn { background: #2a2a2a !important; color: #ededed !important; border-color: rgba(255,255,255,0.14) !important; }
  html.dark .btn:hover { background: #333333 !important; border-color: #4fb8d4 !important; color: #4fb8d4 !important; }
  html.dark .btn.primary { background: #4fb8d4 !important; color: #0c1e23 !important; border-color: #4fb8d4 !important; }
  html.dark .btn.primary:hover { background: #6cc8e0 !important; border-color: #6cc8e0 !important; }
  html.dark input, html.dark select, html.dark textarea { background: #2a2a2a !important; color: #ededed !important; border-color: rgba(255,255,255,0.14) !important; }
  html.dark input::placeholder, html.dark textarea::placeholder { color: #6e6e6e !important; }


  /* Guest navigation menu — bulletin page only.
     Hamburger button + dropdown panel. Visible on desktop AND mobile because
     the bulletin header is already crowded with Search / Sign-in / etc. */
  .guest-bar { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; }
  .guest-nav-toggle {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px;
    padding: 0 !important;
    background: transparent;
    border: 1px solid var(--line);
    border-radius: 8px;
    color: var(--ink);
    cursor: pointer;
    transition: color 0.15s, border-color 0.15s, background 0.15s;
  }
  .guest-nav-toggle:hover {
    border-color: var(--teal);
    color: var(--teal);
  }
  .guest-nav-toggle[aria-expanded="true"] {
    background: var(--ink);
    border-color: var(--ink);
    color: #fff;
  }
  html.dark .guest-nav-toggle { color: #ededed; border-color: rgba(255,255,255,0.18); }
  html.dark .guest-nav-toggle:hover { color: #4fb8d4; border-color: #4fb8d4; }
  html.dark .guest-nav-toggle[aria-expanded="true"] { background: #4fb8d4; color: #0c1e23; border-color: #4fb8d4; }

  /* Dropdown panel (Anthropic-style — same vibe as the Admin ▾ panel) */
  .guest-nav {
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    min-width: 240px;
    max-width: 300px;
    padding: 18px 12px;
    background: var(--parchment);
    border: 1px solid var(--line);
    border-radius: 14px;
    box-shadow: 0 18px 56px -10px rgba(26,35,50,0.25);
    display: none;
    flex-direction: column;
    gap: 2px;
    z-index: 220;
  }
  .guest-nav.open { display: flex; }
  html.dark .guest-nav { background: #222222; border-color: rgba(255,255,255,0.10); box-shadow: 0 18px 56px -10px rgba(0,0,0,0.55); }

  .guest-nav-label {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 600;
    letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--ink-soft);
    opacity: 0.7;
    padding: 0 12px 8px;
  }
  html.dark .guest-nav-label { color: #9e9e9e; }

  .guest-nav a {
    display: block;
    padding: 10px 14px;
    font-family: 'Cormorant Garamond', serif;
    font-size: 19px; font-weight: 500;
    color: var(--ink);
    text-decoration: none;
    border-radius: 6px;
    line-height: 1.25;
    min-height: 44px;
    display: flex; align-items: center;
    transition: color 0.12s, background 0.12s;
  }
  .guest-nav a:hover { color: var(--teal); background: rgba(3,97,122,0.06); }
  .guest-nav a:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  html.dark .guest-nav a { color: #ededed; }
  html.dark .guest-nav a:hover { color: #4fb8d4; background: rgba(255,255,255,0.04); }


  /* Accordion drawer styling */
  .guest-nav-group { padding: 0; margin: 0; border-radius: 6px; }
  .guest-nav-group > summary {
    list-style: none;
    cursor: pointer;
    padding: 12px 14px;
    font-family: 'Cormorant Garamond', serif;
    font-size: 19px; font-weight: 500;
    line-height: 1.25;
    color: var(--ink);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: space-between;
    min-height: 44px;
    transition: color 0.12s, background 0.12s;
  }
  .guest-nav-group > summary::-webkit-details-marker { display: none; }
  .guest-nav-group > summary::after {
    content: '▾';
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px;
    color: var(--ink-soft);
    transition: transform 0.18s ease;
  }
  .guest-nav-group[open] > summary::after { transform: rotate(180deg); }
  .guest-nav-group > summary:hover { color: var(--teal); background: rgba(3,97,122,0.06); }
  .guest-nav-group { display: block; }
  .guest-nav-group[open] > a,
  .guest-nav-group > a {
    display: block !important;
    width: 100%;
    padding: 10px 14px 10px 28px !important;
    font-size: 16px !important;
    color: var(--ink-soft) !important;
  }
  .guest-nav-group > a:hover { color: var(--teal) !important; background: rgba(3,97,122,0.06); }
  html.dark .guest-nav-group > summary { color: #ededed; }
  html.dark .guest-nav-group > summary:hover { color: #4fb8d4; background: rgba(255,255,255,0.04); }
  html.dark .guest-nav-group > summary::after { color: #9e9e9e; }
  html.dark .guest-nav-group > a { color: #9e9e9e !important; }
  html.dark .guest-nav-group > a:hover { color: #4fb8d4 !important; }

  .guest-nav-top {
    /* Top-level non-accordion items inherit the same padding as summaries */
    font-size: 19px !important;
    padding: 12px 14px !important;
  }

    .guest-nav-donate {
    margin-top: 8px;
    padding: 12px 14px !important;
    background: var(--teal);
    color: #fff !important;
    border-radius: 6px;
    justify-content: space-between !important;
  }
  .guest-nav-donate:hover { background: var(--teal-dark) !important; color: #fff !important; }
  html.dark .guest-nav-donate { background: #4fb8d4; color: #0c1e23 !important; }
  html.dark .guest-nav-donate:hover { background: #6cc8e0 !important; color: #0c1e23 !important; }

  @media (max-width: 600px) {
    .guest-nav { right: -8px; min-width: 220px; }
  }

  /* ─── Bulletin toolbar (sticky above bulletin card when admin editing) ─── */
  .bulletin-toolbar {
    display: flex; align-items: center; gap: 6px;
    max-width: 1100px; margin: 0 auto; padding: 10px 22px;
    background: #f4f1e3;
    border-bottom: 1px solid rgba(3,97,122,0.12);
    position: sticky; top: 69px; z-index: 40;
    box-shadow: 0 2px 0 rgba(0,0,0,0); transition: box-shadow 0.2s;
  }
  .bulletin-toolbar.stuck { box-shadow: 0 6px 14px -8px rgba(0,0,0,0.18); }
  .bulletin-toolbar .bt-spacer { flex: 1; }
  .bt-icon-btn {
    width: 40px; height: 40px; padding: 0;
    background: transparent; color: var(--ink, #1a2332);
    border: 0; border-radius: 10px; cursor: pointer;
    opacity: 0.3; display: inline-flex; align-items: center; justify-content: center;
    transition: background 0.15s, opacity 0.15s;
  }
  .bt-icon-btn svg { width: 20px; height: 20px; display: block; }
  .bt-icon-btn:not([disabled]) { opacity: 0.75; }
  .bt-icon-btn:not([disabled]):hover { background: rgba(0,0,0,0.05); opacity: 1; }
  .bt-icon-btn:not([disabled]):active { background: rgba(0,0,0,0.08); }
  .go-live-pill {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 18px;
    background: var(--ink, #1a2332); color: #fff;
    border: 0; border-radius: 8px;
    font: 600 13px/1 'Poppins', sans-serif;
    cursor: pointer; transition: background 0.15s;
  }
  .go-live-pill:hover { background: #2a3340; }
  .go-live-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #36c980;
    box-shadow: 0 0 0 0 rgba(54,201,128,0.6);
  }
  body.bulletin-dirty .go-live-dot { animation: dotpulse 2s infinite; }
  @keyframes dotpulse {
    0% { box-shadow: 0 0 0 0 rgba(54,201,128,0.6); }
    70% { box-shadow: 0 0 0 8px rgba(54,201,128,0); }
    100% { box-shadow: 0 0 0 0 rgba(54,201,128,0); }
  }
  body:not(.bulletin-dirty) .go-live-pill { opacity: 0.65; }
  body:not(.bulletin-dirty) .go-live-dot { background: rgba(255,255,255,0.35); animation: none; }
</style>
</head>
<body @class(['edit-mode' => $canEdit, 'bulletin-dirty' => $canEdit && $bulletin && (! $bulletin->published_at || ($bulletin->published_snapshot && $bulletin->hasUnpublishedChanges()))]) data-theme="{{ $bulletin?->theme ?? 'default' }}">

@include('partials.site-menu')

@if ($canEdit && $bulletin)
  <div class="bulletin-toolbar" aria-label="Editing controls">
    <button class="bt-icon-btn" id="undo-btn" type="button" disabled aria-label="Undo" title="Undo (⌘Z)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/></svg></button>
    <button class="bt-icon-btn" id="redo-btn" type="button" disabled aria-label="Redo" title="Redo (⌘⇧Z)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m15 14 5-5-5-5"/><path d="M20 9H9.5A5.5 5.5 0 0 0 4 14.5v0A5.5 5.5 0 0 0 9.5 20H13"/></svg></button>
    <span class="bt-spacer"></span>
    <button id="publish-btn" class="go-live-pill" type="button" data-publish-url="{{ route('bulletins.publish', $bulletin) }}" aria-label="Publish bulletin">
      <span class="go-live-dot"></span><span>Go Live</span>
    </button>
  </div>
@endif

{{-- Week-mode preview banner — only shown when ?preview-week=1 is set OR
     when (future) auto-detection hides the order of service. --}}
@if (($weekMode ?? false) && ! $canEdit)
  <div class="week-mode-notice" style="max-width: 720px; margin: 28px auto 0; padding: 22px 28px; background: rgba(3,97,122,0.06); border-left: 3px solid var(--teal); border-radius: 6px; text-align: center;">
    <div style="font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); margin-bottom: 8px;">This Sabbath</div>
    <div style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: var(--ink); margin-bottom: 6px;">Worship at 11 AM</div>
    <div style="font-family: 'Cormorant Garamond', serif; font-size: 16px; color: var(--ink-soft); font-style: normal;">3323 White Plains Rd · Bronx · everyone welcome</div>
    <div style="margin-top: 14px; font-family: 'Instrument Sans', sans-serif; font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6;">Order of service appears Sabbath morning</div>
  </div>
@endif



@php
  // ─── Sabbath-aware greeting (Bronx, NY sunset) ───────────────────
  $lat = 40.8448; $lon = -73.8648;
  $tz  = new \DateTimeZone('America/New_York');
  $now = new \DateTime('now', $tz);
  $dow = (int) $now->format('w');            // 0=Sun … 6=Sat

  // Find this Sabbath's Friday anchor (midnight local)
  $friday = clone $now; $friday->setTime(0, 0);
  if ($dow === 6)       $friday->modify('-1 day');      // already Sat → Fri was yesterday
  elseif ($dow === 5)   {}                              // it's Fri today
  else                  $friday->modify('next friday'); // Sun–Thu → upcoming Fri

  $saturday = clone $friday; $saturday->modify('+1 day');

  $fridaySunsetTs   = date_sun_info($friday->getTimestamp(),   $lat, $lon)['sunset'];
  $saturdaySunsetTs = date_sun_info($saturday->getTimestamp(), $lat, $lon)['sunset'];
  $nowTs            = $now->getTimestamp();

  $isSabbath = $nowTs >= $fridaySunsetTs && $nowTs <= $saturdaySunsetTs;
  $sabbathDate = \Illuminate\Support\Carbon::instance($saturday);
@endphp

<section class="hero">
  <div class="container">
    <div class="greeting">{{ $isSabbath ? 'Happy Sabbath' : 'Have a Blessed Week' }}</div>
    <div class="date">
      @if ($isSabbath)
        Sabbath · {{ strtoupper($sabbathDate->format('F j, Y')) }}
      @else
        Next Sabbath · {{ strtoupper($sabbathDate->format('F j, Y')) }}
      @endif
    </div>
    <p class="lede">The weekly bulletin, upcoming events, <a href="{{ route('lesson.show') }}" class="lede-link">Sabbath school lesson</a>, and service calendar for the Shalom Seventh-day Adventist Church in the Bronx.</p>
  </div>
</section>

@if (! ($weekMode ?? false) || $canEdit)
<section class="section">
  <div class="container reading">
    @php
      // Pick the source: clerk sees live DB rows; public sees published_snapshot
      if ($canEdit && $bulletin) {
          $title = $bulletin->title;
          $serviceDate = $bulletin->service_date;
          $lines = $bulletin->lines->map(fn($l) => [
              'id' => $l->id, 'section' => $l->section, 'part' => $l->part,
              'person' => $l->person, 'kind' => $l->kind,
          ])->all();
          $anns = $bulletin->announcements->map(fn($a) => [
              'id' => $a->id, 'title' => $a->title, 'detail' => $a->detail,
          ])->all();
      } elseif ($bulletin && $bulletin->published_snapshot) {
          $snap = $bulletin->published_snapshot;
          $title = $snap['title'] ?? $bulletin->title;
          $serviceDate = \Carbon\Carbon::parse($snap['service_date'] ?? $bulletin->service_date);
          $lines = $snap['lines'] ?? [];
          $anns  = $snap['announcements'] ?? [];
      } else {
          $title = null; $serviceDate = null; $lines = []; $anns = [];
      }
    @endphp

    @php
      $isDirty = $canEdit && $bulletin && (! $bulletin->published_at || ($bulletin->published_snapshot && $bulletin->hasUnpublishedChanges()));
      $chipLabel = $bulletin && $bulletin->published_at ? ($isDirty ? 'Unsaved changes' : 'Live') : 'Draft';
    @endphp
    <div class="eyebrow">
      <span>This Sabbath's Bulletin</span>
      @if ($bulletin)
        <span class="chip @if($isDirty) draft @endif">{{ $chipLabel }}</span>
      @endif
    </div>

    @if ($bulletin)
      <div class="draft-banner">
        ● {{ $bulletin?->published_at ? 'Unpublished edits' : 'Draft' }} — viewers still see the OLD version. Open the <strong>Admin</strong> menu and hit <strong>Go Live</strong> when ready.
      </div>
      <article class="bulletin-card">
        <header class="bulletin-head">
          @if ($canEdit && (!empty($prevBulletinId) || !empty($nextBulletinId)))
            <div class="bulletin-nav">
              @if (!empty($prevBulletinId))
                <a class="bulletin-nav-arrow" href="?bulletin={{ $prevBulletinId }}" title="Previous Sabbath">
                  <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
              @else
                <span class="bulletin-nav-arrow disabled" aria-hidden="true">
                  <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
              @endif
              <a class="bulletin-nav-latest" href="/" id="latest-btn"
                 title="Tap = jump to latest · Long-press = pick any bulletin"
                 aria-label="Latest bulletin (long-press for picker)">
                Latest
                <span class="latest-hint" aria-hidden="true">hold for picker</span>
              </a>
              @if (!empty($nextBulletinId))
                <a class="bulletin-nav-arrow" href="?bulletin={{ $nextBulletinId }}" title="Next Sabbath">
                  <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
              @else
                <span class="bulletin-nav-arrow disabled" aria-hidden="true">
                  <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
              @endif
            </div>
          @endif
          @if ($bulletin->kind === 'event_night' && $bulletin->event_name)
            <div class="event-masthead">
              <div class="rotator-event-msg msg-blessed">Have a blessed Week</div>
              <div class="rotator-event-msg msg-welcome">Welcome to our {{ $bulletin->event_name }}</div>
            </div>
          @endif
          <h1 class="@if($canEdit) editable @endif"
              @if($canEdit) data-edit-url="{{ route('bulletins.update', $bulletin) }}" data-field="title" @endif>{{ $title }}</h1>
          <div class="service-date @if($canEdit) editable @endif"
               @if($canEdit) data-edit-url="{{ route('bulletins.update', $bulletin) }}" data-field="service_date" data-type="date" data-raw="{{ $serviceDate->toDateString() }}" @endif>
            {{ $serviceDate->format('l, F j, Y') }}
          </div>
          @if ($bulletin->kind === 'event_night' && $bulletin->event_night_number)
            <div class="event-night-tag">
              {{ $bulletin->event_name }} · Night {{ $bulletin->event_night_number }}@if ($bulletin->event_total_nights) of {{ $bulletin->event_total_nights }}@endif
            </div>
          @endif
        </header>

        {{-- Order of service lines --}}
        @if ($canEdit && $bulletin)
        <div class="order-list" id="bulletin-order-list"
             data-reorder-url="{{ route('bulletins.lines.reorder', $bulletin) }}"
             data-restore-url="{{ route('bulletins.lines.restore', $bulletin) }}">
          <div class="order-list-hint">
            Tap the <span class="swatch-part">left field</span> (the program item, e.g. "Opening Hymn") to edit what's happening — autocompletes hymns when relevant.
            Tap the <span class="swatch-person">right field</span> (italic) to enter who's leading.
            Need to clean up old name entries? <a href="#" id="manage-names-from-hint">Open Manage names ↗</a>
          </div>
        @endif
        @php
          $prevSection = null;
          $prevPart = '';
          // Precompute the set of section names that have an EXPLICIT section_header row.
          // Public view should not auto-emit a section header for those (the explicit one
          // is already in the loop) — that prevents the "duplicate section header" bug.
          $explicitSectionNames = [];
          foreach ($lines as $_l) {
              if (($_l['kind'] ?? 'line') === 'section_header' && !empty($_l['section'])) {
                  $explicitSectionNames[$_l['section']] = true;
              }
          }
        @endphp
        @foreach ($lines as $line)
          @php $isHeader = ($line['kind'] ?? 'line') === 'section_header'; @endphp
          @if (!$canEdit && !$isHeader && isset($line['section']) && $line['section'] !== $prevSection && empty($explicitSectionNames[$line['section']] ?? null))
            @if ($line['section'])<div class="section-header">{{ $line['section'] }}</div>@endif
            @php $prevSection = $line['section']; @endphp
          @endif

          @if ($isHeader)
            @php $sec = $line['section'] ?? ''; @endphp
            @if ($sec !== '' || $canEdit)
              <div class="section-header @if($canEdit) editable @endif"
                   @if($canEdit && isset($line['id']))
                     data-line-id="{{ $line['id'] }}"
                     data-section="{{ $sec }}"
                     data-kind="section_header"
                     data-edit-url="{{ route('bulletins.lines.update', [$bulletin, $line['id']]) }}"
                     data-field="section"
                   @endif>
                @if($canEdit && isset($line['id']))<span class="drag-handle" title="Drag to reorder">⋮⋮</span>@endif
                {{ $sec }}
                @if ($canEdit && isset($line['id']))
                  <span class="row-actions"><button data-delete-url="{{ route('bulletins.lines.destroy', [$bulletin, $line['id']]) }}" class="del">×</button></span>
                @endif
              </div>
              @php if (!$canEdit) $prevSection = $sec; @endphp
            @endif
          @else
            @php
              $hasPart = !empty($line['part']);
              $hasPerson = !empty($line['person']);
              // Karlon's canonical autocomplete rules — applied to BOTH part AND person fields:
              //   • line context contains "song" or "hymn"   → scope=hymn   (numbers from hymnal)
              //   • line context contains "scripture"        → scope=bible  (Bible book names)
              //   • everything else                          → scope=person (names from past bulletins)
              // Both columns get the same scope so Andre can type the value into either field
              // without the wrong-suggestions confusion (he kept typing hymns into the person column).
              $haystack = strtolower(($line['part'] ?? '') . ' ' . ($line['section'] ?? '') . ' ' . $prevPart);
              if (str_contains($haystack, 'scripture')) {
                  $lineScope = 'bible';
              } elseif (str_contains($haystack, 'song') || str_contains($haystack, 'hymn')) {
                  $lineScope = 'hymn';
              } else {
                  $lineScope = 'person';
              }
            @endphp
            @if ($hasPart || $hasPerson || $canEdit)
              <div class="order-line"
                   @if($canEdit && isset($line['id']))
                     data-line-id="{{ $line['id'] }}"
                     data-section="{{ $line['section'] ?? '' }}"
                     data-part="{{ $line['part'] ?? '' }}"
                     data-person="{{ $line['person'] ?? '' }}"
                     data-kind="line"
                   @endif>
                @if($canEdit && isset($line['id']))<span class="drag-handle" title="Drag to reorder">⋮⋮</span>@endif
                @php
                  // Hymn-tap: in non-edit (view) mode, render parts that start with #NNN as a tappable
                  // button showing only the number — opens lightbox with title + lyrics.
                  $partText = $line['part'] ?? '';
                  $hymnNum = (!$canEdit && $partText !== '' && preg_match('/^#(\d{1,3})\b/', $partText, $hm)) ? $hm[1] : null;
                @endphp
                <span class="part @if($canEdit) editable @endif @if(!$hasPart) empty-field @endif"
                      @if($canEdit && isset($line['id'])) data-edit-url="{{ route('bulletins.lines.update', [$bulletin, $line['id']]) }}" data-field="part" data-autocomplete="{{ $lineScope }}" @endif>@if ($hymnNum)<button type="button" class="hymn-tap" data-hymn="{{ $hymnNum }}" title="Tap to see hymn title and lyrics">#{{ $hymnNum }}</button>@else{{ $partText }}@endif</span>
                <span class="leader"></span>
                <span class="person @if($canEdit) editable @endif @if(!$hasPerson) empty-field @endif"
                      @if($canEdit && isset($line['id'])) data-edit-url="{{ route('bulletins.lines.update', [$bulletin, $line['id']]) }}" data-field="person" data-autocomplete="{{ $lineScope }}" @endif>{{ $line['person'] ?? '' }}</span>
                @if ($canEdit && isset($line['id']))
                  <span class="row-actions"><button data-delete-url="{{ route('bulletins.lines.destroy', [$bulletin, $line['id']]) }}" class="del">×</button></span>
                @endif
              </div>
            @endif
          @endif
          @php $prevPart = $line['part'] ?? ''; @endphp
        @endforeach
        @if ($canEdit && $bulletin)
        </div>
        @endif

        @if ($canEdit)
          <button class="add-btn" data-add-line-url="{{ route('bulletins.lines.store', $bulletin) }}">+ Add line</button>
          <button class="add-btn" data-add-section-url="{{ route('bulletins.lines.store', $bulletin) }}">+ Add section header</button>
          <span class="add-hint" title="To add a hymn # below an Opening/Closing Hymn row: click + Add line and start the text with # (e.g. #15 My Maker and My King). It will render centered and italic.">Tip: hymn titles ↑ start with #</span>
        @endif

        {{-- Announcements --}}
        @if (count($anns) > 0 || $canEdit)
          <div class="announcements">
            <h3>Announcements</h3>
            @foreach ($anns as $a)
              @php
                $hasTitle = !empty($a['title']);
                $hasDetail = !empty($a['detail']);
              @endphp
              @if ($hasTitle || $hasDetail || $canEdit)
                <div class="announcement">
                  <div class="a-title @if($canEdit) editable @endif @if(!$hasTitle) empty-field @endif"
                       @if($canEdit && isset($a['id'])) data-edit-url="{{ route('bulletins.announcements.update', [$bulletin, $a['id']]) }}" data-field="title" @endif>{{ $a['title'] ?? '' }}</div>
                  @if ($hasDetail || $canEdit)
                    <div class="a-detail @if($canEdit) editable @endif @if(!$hasDetail) empty-field @endif"
                         @if($canEdit && isset($a['id'])) data-edit-url="{{ route('bulletins.announcements.update', [$bulletin, $a['id']]) }}" data-field="detail" data-type="textarea" @endif>{{ $a['detail'] ?? '' }}</div>
                  @endif
                  @if ($canEdit && isset($a['id']))
                    <span class="row-actions"><button data-delete-url="{{ route('bulletins.announcements.destroy', [$bulletin, $a['id']]) }}" class="del">Delete</button></span>
                  @endif
                </div>
              @endif
            @endforeach
            @if ($canEdit)
              <button class="add-btn" data-add-ann-url="{{ route('bulletins.announcements.store', $bulletin) }}">+ Add announcement</button>
            @endif
          </div>
        @endif
      </article>

      @if ($canEdit)
        <div class="publish-bar">
          <span id="publish-status" class="status">
            @if ($bulletin->published_at)
              Published {{ $bulletin->published_at->diffForHumans() }}
            @else
              Never published
            @endif
          </span>
          <span style="font-family:'Instrument Sans',sans-serif; font-size:10px; letter-spacing:0.18em; text-transform:uppercase; color:var(--ink-soft); font-weight:600;">Use the menu above to publish, reset, or undo.</span>
        </div>
      @endif
    @else
      <div class="bulletin-card" style="text-align:center; color:var(--ink-soft);">
        No bulletin yet.
      </div>
    @endif
  </div>
</section>
@endif

<section class="section">
  <div class="events-container">
    <div class="eyebrow">
      <span>Upcoming</span>
      @if ($canEdit)
        <button class="btn" id="event-add-btn" type="button">+ Add event</button>
      @endif
    </div>
    @if ($upcomingEvents->count())
      <div class="events-grid">
        @foreach ($upcomingEvents as $e)
          <div class="event @if($e->flyer_path) has-flyer @endif">
            <div class="cal-tile">
              <div class="month">{{ strtoupper($e->start_at->format('M')) }}</div>
              <div class="day">{{ $e->start_at->format('j') }}</div>
              <div class="dow">{{ strtoupper($e->start_at->format('D')) }}</div>
            </div>
            <div class="event-body">
              <div class="event-title">
                @if ($e->department)
                  <span class="event-dot" style="background: {{ $e->department->color_hex }};"></span>
                @endif
                <span class="event-title-text">{{ $e->title }}</span>
              </div>
              <div class="event-meta">
                @if ($e->start_at->format('H:i') !== '00:00')
                  <span class="event-time">{{ $e->start_at->format('g:i A') }}</span>
                @endif
                @if ($e->location)<span class="event-loc">@if ($e->start_at->format('H:i') !== '00:00') · @endif{{ $e->location }}</span>@endif
                @if ($e->notes)<br><span class="event-notes">{{ $e->notes }}</span>@endif
              </div>
            </div>
            @if ($canEdit)
              <div class="event-card-actions">
                <button class="event-edit-btn" type="button"
                        data-id="{{ $e->id }}"
                        data-title="{{ $e->title }}"
                        data-start-date="{{ $e->start_at->format('Y-m-d') }}"
                        data-start-time="{{ $e->start_at->format('H:i') }}"
                        data-location="{{ $e->location ?? '' }}"
                        data-notes="{{ $e->notes ?? '' }}"
                        data-department-id="{{ $e->department_id ?? '' }}"
                        data-update-url="{{ route('events.update', $e) }}"
                        data-delete-url="{{ route('events.destroy', $e) }}"
                        aria-label="Edit event" title="Edit">
                  <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="event-delete-btn del" type="button"
                        data-delete-url="{{ route('events.destroy', $e) }}"
                        data-event-title="{{ $e->title }}"
                        aria-label="Delete event" title="Delete">
                  <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </div>
            @endif
            @if ($e->flyer_path || $canEdit)
              <div class="event-flyer">
                @if ($e->flyer_path)
                  <div class="event-flyer-thumb" data-flyer-url="/{{ $e->flyer_path }}"
                       data-flyer-title="{{ $e->title }}" role="button" aria-label="Enlarge flyer">
                    <img src="/{{ $e->flyer_path }}" alt="Flyer for {{ $e->title }}" loading="lazy">
                  </div>
                  <div class="event-flyer-caption">Click to enlarge</div>
                  @if ($canEdit)
                    <button class="event-flyer-remove" data-flyer-remove-url="{{ route('events.flyer.remove', $e) }}">Remove flyer</button>
                  @endif
                @elseif ($canEdit)
                  <label class="event-flyer-upload" data-flyer-upload-url="{{ route('events.flyer.upload', $e) }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span>Add flyer</span>
                    <input type="file" accept="image/*,application/pdf" style="display:none;">
                  </label>
                @endif
              </div>
            @endif
          </div>
        @endforeach
      </div>
    @else
      <p class="reading" style="color: var(--ink-soft); font-style: italic;">No events posted yet.</p>
    @endif
  </div>
</section>

@auth
@if (isset($departments) && $departments->count())
<section class="section">
  <div class="container">
    <div class="eyebrow"><span>Sabbath Duty Calendar</span></div>
    <div class="dept-pills">
      <button class="dept-pill active" data-filter="all" style="--dept-color: var(--ink);">All</button>
      @foreach ($departments as $d)
        <button class="dept-pill" data-filter="{{ $d->id }}" style="--dept-color: {{ $d->color_hex }};">{{ $d->name }}</button>
      @endforeach
    </div>
    <div class="duty-list">
      @forelse ($dutyEvents as $e)
        <div class="duty-row" data-dept="{{ $e->department_id ?? 'other' }}" style="border-left-color: {{ $e->department?->color_hex ?? '#9ca3af' }};">
          <div class="duty-date">
            {{ strtoupper($e->start_at->format('M')) }}
            <span class="day">{{ $e->start_at->format('j') }}</span>
          </div>
          <div>
            <div class="duty-who">{{ $e->department?->name ? $e->department->name . ' · ' : '' }}{{ $e->title }}</div>
            <div class="duty-what">
              {{ $e->start_at->format('g:i A') }}@if ($e->location) · {{ $e->location }}@endif
              @if ($e->notes) · {{ $e->notes }}@endif
            </div>
          </div>
        </div>
      @empty
        <p style="color: var(--ink-soft); font-style: italic;">No department duties scheduled.</p>
      @endforelse
    </div>
  </div>
</section>
<script>
  (function(){
    document.querySelectorAll('.dept-pill').forEach(pill => pill.addEventListener('click', () => {
      document.querySelectorAll('.dept-pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      const filter = pill.dataset.filter;
      document.querySelectorAll('.duty-row').forEach(row => {
        row.classList.toggle('hidden', filter !== 'all' && row.dataset.dept !== filter);
      });
    }));
  })();
</script>
@endif
@endauth

@php
  // Greeting text rotates per day of week: Sabbath (Saturday) gets "Have a pleasant Sabbath",
  // every other day gets the peace verse from John 14:27.
  $isSabbath = now()->dayOfWeek === \Carbon\Carbon::SATURDAY;
  $greeting = $isSabbath
      ? ['text' => 'Have a pleasant Sabbath', 'cite' => null]
      : ['text' => 'Peace I leave with you; my peace I give to you.', 'cite' => 'John 14:27'];
@endphp

<section class="closing">
  <div class="container">
    <div class="closing-rotator">
      <div class="rotator-item pleasant">
        {{ $greeting['text'] }}
        @if ($greeting['cite'])
          <cite class="greeting-cite">— {{ $greeting['cite'] }}</cite>
        @endif
      </div>
      <div class="rotator-item brand-mark">The Church of Peace</div>
    </div>
  </div>
</section>

<footer class="site">
  <div class="copyright">Shalom Seventh-day Adventist Church &middot; The Church of Peace</div>
  <address>
    3323 White Plains Rd &middot; Bronx, NY<br>
    <a href="mailto:contact@thechurchofpeace.org">contact@thechurchofpeace.org</a>
  </address>
  <div class="copyright" style="margin-top:14px; opacity:0.6;">&copy; {{ date('Y') }}</div>
</footer>

{{-- ═══ SEARCH OVERLAY ═══ --}}
<div id="search-overlay" class="search-overlay" role="dialog" aria-label="Search">
  <button class="search-close" id="search-close">Close ✕</button>
  <div class="inner">
    <div class="search-input-wrap">
      <svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input id="search-input" class="search-input" type="text" placeholder="Search Scripture or Hymnal…" autocomplete="off" />
    </div>
    <div class="search-tabs">
      <button class="search-tab active" data-scope="all">All <span class="count" id="count-all"></span></button>
      <button class="search-tab" data-scope="hymnal">Hymnal <span class="count" id="count-hymnal"></span></button>
      <button class="search-tab" data-scope="kjv">KJV <span class="count" id="count-kjv"></span></button>
      <button class="search-tab" data-scope="esv">ESV <span class="count" id="count-esv"></span></button>
    </div>
    <div class="search-hint" id="search-hint">Type to search the Hymnal, KJV, or ESV.</div>
    <div class="search-results" id="search-results"></div>
  </div>
</div>

{{-- ═══ IMAGE LIGHTBOX (flyers) ═══ --}}
<div id="img-lightbox" class="img-lightbox" role="dialog" aria-label="Flyer">
  <button class="img-lightbox-close" id="img-lightbox-close" aria-label="Close">✕</button>
  <img id="img-lightbox-img" src="" alt="">
</div>

{{-- ═══ LIGHTBOX READER ═══ --}}
<div id="lightbox" class="lightbox" role="dialog" aria-label="Reader">
  <div class="lightbox-card">
    <div class="lightbox-head">
      <button class="lightbox-close" id="lightbox-close" aria-label="Close">✕</button>
      <div class="lightbox-ref" id="lightbox-ref"></div>
      <div class="lightbox-title" id="lightbox-title"></div>
      <div id="lightbox-controls"></div>
    </div>
    <div class="lightbox-body-wrap" id="lightbox-body-wrap">
      <div class="lightbox-body" id="lightbox-body"></div>
    </div>
  </div>
</div>

<script>
(function() {
  /* ── MiniSearch setup ───────────────────────────────── */
  let indexes = {};    // { hymnal: MiniSearch, kjv: MiniSearch, esv: MiniSearch }
  let docs    = {};    // { hymnal: [...], kjv: [...], esv: [...] } — raw documents for display
  let loading = {};    // { hymnal: Promise, ... }

  async function loadCorpus(name) {
    if (docs[name]) return;
    if (loading[name]) return loading[name];
    loading[name] = (async () => {
      const res = await fetch('/search/' + name + '.json');
      const data = await res.json();
      docs[name] = data;
      // Wait for MiniSearch lib to be available
      await new Promise(r => {
        const check = () => window.MiniSearch ? r() : setTimeout(check, 50);
        check();
      });
      const fields = name === 'hymnal' ? ['title', 'body'] : ['ref', 'text'];
      const storeFields = name === 'hymnal' ? ['number', 'title', 'body'] : ['translation', 'book', 'chapter', 'verse', 'ref', 'text'];
      const mini = new window.MiniSearch({ fields, storeFields, searchOptions: { boost: name === 'hymnal' ? { title: 3 } : { ref: 5 }, prefix: true, fuzzy: 0.15 } });
      mini.addAll(data);
      indexes[name] = mini;
    })();
    return loading[name];
  }

  /* ── UI state ───────────────────────────────────────── */
  const overlay = document.getElementById('search-overlay');
  const input = document.getElementById('search-input');
  const results = document.getElementById('search-results');
  const hint = document.getElementById('search-hint');
  const tabs = document.querySelectorAll('.search-tab');
  let currentScope = 'all';
  let debounceTimer;

  function openSearch() {
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    setTimeout(() => input.focus(), 80);
    // Item #4: load hymnal eagerly (557 KB), defer KJV/ESV (~7.5 MB each)
    // until the user actually clicks those tabs OR after 3 sec idle prefetch.
    loadCorpus('hymnal');
    setTimeout(() => {
      // Background prefetch — only if user hasn't typed anything yet
      // (typing already triggers loadCorpus on demand)
      if (!input.value && !indexes.kjv) loadCorpus('kjv');
      if (!input.value && !indexes.esv) setTimeout(() => loadCorpus('esv'), 800);
    }, 3000);
  }
  function closeSearch() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  document.getElementById('search-btn')?.addEventListener('click', openSearch);
  // open-search auto-open hook — Two signals (URL param + sessionStorage)
  // for resilience: SW caches, browser back-forward, and Safari quirks have
  // all stripped the URL param in different scenarios. sessionStorage survives
  // them. Plus a retry loop so we wait for openSearch to be ready.
  (function () {
    try {
      var p = new URLSearchParams(window.location.search);
      var fromUrl = p.get('open-search') === '1';
      var fromStore = false;
      try { fromStore = sessionStorage.getItem('cop_open_search') === '1'; } catch (e) {}
      if (fromUrl || fromStore) {
        try { sessionStorage.removeItem('cop_open_search'); } catch (e) {}
        var attempts = 0;
        function tryOpen() {
          attempts++;
          if (typeof openSearch === 'function' && document.getElementById('search-overlay')) {
            openSearch();
          } else if (attempts < 30) {
            setTimeout(tryOpen, 50);
          }
        }
        setTimeout(tryOpen, 50);
        if (fromUrl) {
          var url = window.location.pathname + window.location.hash;
          history.replaceState(null, '', url);
        }
      }
    } catch (e) {}
  })();
  document.getElementById('search-btn-mobile')?.addEventListener('click', () => { document.getElementById('ham-menu')?.classList.remove('open'); document.getElementById('ham-btn')?.setAttribute('aria-expanded','false'); openSearch(); });
  document.getElementById('search-close').addEventListener('click', closeSearch);

  // Hymn-tap: any .hymn-tap button on the page opens the lightbox with that hymn's title + lyrics.
  // We lazy-load the hymnal corpus on first tap so guests who never tap pay zero cost.
  async function openHymnByNumber(num) {
    try {
      if (!docs.hymnal) await loadCorpus('hymnal');
      const h = docs.hymnal.find(d => Number(d.number) === Number(num));
      if (h) openReader('hymnal', h.id);
    } catch (e) { console.warn('Hymn lookup failed:', e); }
  }
  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.hymn-tap');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    openHymnByNumber(btn.dataset.hymn);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { closeSearch(); closeLightbox(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openSearch(); }
  });
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closeSearch(); });

  tabs.forEach(t => t.addEventListener('click', () => {
    tabs.forEach(x => x.classList.remove('active'));
    t.classList.add('active');
    currentScope = t.dataset.scope;
    runSearch(input.value);
  }));

  input.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => runSearch(e.target.value), 150);
  });

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function highlight(text, q) {
    if (!q) return text;
    const terms = q.split(/\s+/).filter(t => t.length >= 2);
    let out = text;
    terms.forEach(term => {
      const re = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
      out = out.replace(re, '<mark>$1</mark>');
    });
    return out;
  }

  async function runSearch(q) {
    q = (q || '').trim();
    results.innerHTML = '';
    if (q.length < 2) {
      hint.textContent = 'Type at least 2 characters. Try "Pas", "peace", "Psalm 23", or "619".';
      hint.style.display = 'block';
      document.querySelectorAll('.count').forEach(c => c.textContent = '');
      return;
    }
    hint.style.display = 'none';

    const scopesToLoad = currentScope === 'all'
      ? ['hymnal', 'kjv', 'esv']
      : [currentScope];

    // Show loading state
    const missing = scopesToLoad.filter(s => !indexes[s]);
    if (missing.length) {
      results.innerHTML = '<div class="search-loading">Loading ' + missing.join(' + ') + '…</div>';
      await Promise.all(missing.map(loadCorpus));
      results.innerHTML = '';
    }

    const allResults = [];
    const counts = { hymnal: 0, kjv: 0, esv: 0 };
    scopesToLoad.forEach(name => {
      if (!indexes[name]) return;
      let hits = indexes[name].search(q).slice(0, 250);
      // For a Hymn number query, also match hymn numbers prefix
      if (name === 'hymnal' && /^\d+$/.test(q)) {
        hits = docs.hymnal.filter(h => String(h.number).startsWith(q)).slice(0, 100).map(h => ({ id: h.id, score: 100 }));
      }
      counts[name] = hits.length;
      hits.forEach(h => allResults.push({ scope: name, id: h.id, score: h.score }));
    });

    // Update tab counts
    document.getElementById('count-hymnal').textContent = counts.hymnal ? '(' + counts.hymnal + ')' : '';
    document.getElementById('count-kjv').textContent = counts.kjv ? '(' + counts.kjv + ')' : '';
    document.getElementById('count-esv').textContent = counts.esv ? '(' + counts.esv + ')' : '';
    document.getElementById('count-all').textContent = '(' + (counts.hymnal + counts.kjv + counts.esv) + ')';

    // Sort by score desc
    allResults.sort((a, b) => b.score - a.score);
    const top = allResults.slice(0, 400);

    if (top.length === 0) {
      results.innerHTML = '<div class="search-hint">No matches for &ldquo;' + q + '&rdquo;.</div>';
      return;
    }

    const frag = document.createDocumentFragment();
    top.forEach(r => {
      const doc = docs[r.scope].find(d => d.id === r.id);
      if (!doc) return;
      const btn = document.createElement('button');
      btn.className = 'search-result';
      let ref, preview;
      if (r.scope === 'hymnal') {
        ref = 'HYMN #' + doc.number;
        preview = highlight(doc.title, q);
      } else {
        ref = doc.ref + ' · ' + r.scope.toUpperCase();
        preview = highlight(doc.text.length > 220 ? doc.text.slice(0, 220) + '…' : doc.text, q);
      }
      btn.innerHTML = '<div class="ref">' + ref + '</div><div class="preview">' + preview + '</div>';
      btn.addEventListener('click', () => openReader(r.scope, doc.id));
      frag.appendChild(btn);
    });
    results.appendChild(frag);
  }

  /* ── Lightbox reader ───────────────────────────────── */
  const lightbox = document.getElementById('lightbox');
  const lbRef = document.getElementById('lightbox-ref');
  const lbTitle = document.getElementById('lightbox-title');
  const lbBody = document.getElementById('lightbox-body');
  const lbControls = document.getElementById('lightbox-controls');

  function openReader(scope, id) {
    closeSearch();
    if (scope === 'hymnal') {
      const h = docs.hymnal.find(d => d.id === id);
      if (!h) return;
      lbRef.textContent = 'HYMN #' + h.number;
      lbTitle.textContent = h.title;
      lbControls.innerHTML = '';
      // Render hymn body — verse numbers on their own lines become labels;
      // skip separator rules (====, ----, ____) that come from the source file.
      const lines = h.body.split(/\n/);
      let html = '';
      let prevBlank = true;
      lines.forEach(line => {
        const trimmed = line.trim();
        if (/^[=_\-]{3,}$/.test(trimmed)) return;  // skip horizontal rule lines
        if (/^\d{1,2}$/.test(trimmed)) {
          html += '<span class="hymn-verse-num">Verse ' + trimmed + '</span>';
          prevBlank = false;
          return;
        }
        if (trimmed === '') {
          if (!prevBlank) html += '<br>';
          prevBlank = true;
          return;
        }
        html += escapeHtml(line) + '<br>';
        prevBlank = false;
      });
      lbBody.innerHTML = html;
      document.getElementById('lightbox-body-wrap').scrollTop = 0;
    } else {
      // Bible verse: show the verse + its chapter context, with KJV/ESV toggle
      const doc = docs[scope].find(d => d.id === id);
      if (!doc) return;
      renderBibleVerse(scope, doc.book, doc.chapter, doc.verse);
    }
    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  // Current Bible reading context — kept in sync as user taps verses
  let bibleCtx = null; // { translation, book, chapter, verse }

  async function renderBibleVerse(translation, book, chapter, verse) {
    await Promise.all([loadCorpus('kjv'), loadCorpus('esv')]);
    bibleCtx = { translation, book, chapter, verse };

    function chapterVerses(trans) {
      return docs[trans].filter(d => d.book === book && d.chapter === chapter).sort((a, b) => a.verse - b.verse);
    }

    lbRef.textContent = book.toUpperCase() + ' · CHAPTER ' + chapter;
    lbTitle.textContent = book + ' ' + chapter + ':' + verse;
    lbControls.innerHTML = '<div class="lightbox-actions">' +
        '<div class="lightbox-translation-toggle">' +
          '<button data-trans="kjv">KJV</button>' +
          '<button data-trans="esv">ESV</button>' +
        '</div>' +
        '<button class="lightbox-share" data-action="copy" aria-label="Copy selected verse">' +
          '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>' +
          'Copy</button>' +
        '<button class="lightbox-share" data-action="share" aria-label="Share selected verse">' +
          '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>' +
          'Share</button>' +
      '</div>' +
      '<div style="font-family: \'Instrument Sans\',sans-serif;font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);margin-top:12px;font-weight:500;">Tap any verse to select it</div>';
    lbControls.querySelectorAll('[data-trans]').forEach(b => {
      if (b.dataset.trans === translation) b.classList.add('active');
      b.addEventListener('click', () => renderBibleVerse(b.dataset.trans, book, chapter, bibleCtx.verse));
    });
    lbControls.querySelectorAll('[data-action]').forEach(b => {
      b.addEventListener('click', () => {
        if (!bibleCtx) return;
        shareVerse(b.dataset.action, bibleCtx.translation, bibleCtx.book, bibleCtx.chapter, bibleCtx.verse);
      });
    });

    const verses = chapterVerses(translation);
    let html = '';
    verses.forEach(v => {
      const sel = v.verse === verse ? ' selected' : '';
      html += '<p class="verse-row' + sel + '" data-verse="' + v.verse + '"><span class="verse-num">' + v.verse + '</span>' + escapeHtml(v.text) + '</p>';
    });
    lbBody.innerHTML = html;

    // Tap-to-select
    lbBody.querySelectorAll('p.verse-row').forEach(p => {
      p.addEventListener('click', () => {
        lbBody.querySelectorAll('p.selected').forEach(x => x.classList.remove('selected'));
        p.classList.add('selected');
        const v = parseInt(p.dataset.verse, 10);
        bibleCtx.verse = v;
        lbTitle.textContent = book + ' ' + chapter + ':' + v;
        history.replaceState(null, '', '#verse=' + encodeURIComponent(book) + '-' + chapter + '-' + v + '&trans=' + bibleCtx.translation);
      });
    });

    // Scroll selected verse to vertical CENTER of the body-wrap (not pinned 16px from top).
    // Two RAFs ensures layout has settled (text wrapping, fonts, etc). Critical for shared
    // links — the recipient lands on the verse front-and-center, not pushed off screen.
    function centerSelectedVerse() {
      const wrap = document.getElementById('lightbox-body-wrap');
      const target = lbBody.querySelector('p.selected');
      if (!target || !wrap) return;
      const wrapH = wrap.clientHeight;
      const targetH = target.offsetHeight;
      const center = target.offsetTop - (wrapH / 2) + (targetH / 2);
      wrap.scrollTop = Math.max(0, center);
    }
    requestAnimationFrame(() => requestAnimationFrame(centerSelectedVerse));
    // Run again after webfonts settle so wrapping doesn't shift the verse off-center
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(centerSelectedVerse);
  }

  function closeLightbox() {
    lightbox.classList.remove('open');
    document.body.style.overflow = overlay.classList.contains('open') ? 'hidden' : '';
    // Clear the verse hash so it doesn't reopen on refresh
    if (location.hash.startsWith('#verse')) history.replaceState(null, '', location.pathname);
  }
  document.getElementById('lightbox-close').addEventListener('click', closeLightbox);
  lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });

  /* ── SHARE ─────────────────────────────────────────── */
  function showToast(msg) {
    let t = document.getElementById('share-toast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'share-toast';
      t.className = 'share-toast';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    requestAnimationFrame(() => t.classList.add('show'));
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 2000);
  }

  function shareVerse(action, translation, book, chapter, verse) {
    const doc = (docs[translation] || []).find(d => d.book === book && d.chapter === chapter && d.verse === verse);
    if (!doc) { showToast('Verse not loaded'); return; }
    const ref = book + ' ' + chapter + ':' + verse;
    const label = translation.toUpperCase();
    const url = location.origin + location.pathname + '#verse=' + encodeURIComponent(book) + '-' + chapter + '-' + verse + '&trans=' + translation;
    const textToShare = '"' + doc.text + '"\n— ' + ref + ' (' + label + ')\n\n' + url;

    if (action === 'copy') {
      copyToClipboard(textToShare).then(() => showToast('Copied'));
      return;
    }
    // Share action: Web Share API if available, else fall back to copy
    if (navigator.share) {
      navigator.share({ title: ref + ' · ' + label, text: '"' + doc.text + '" — ' + ref + ' (' + label + ')', url: url })
        .then(() => showToast('Shared'))
        .catch(() => {});  // user cancelled, no toast
    } else {
      copyToClipboard(textToShare).then(() => showToast('Link copied'));
    }
  }

  function copyToClipboard(text) {
    if (navigator.clipboard) return navigator.clipboard.writeText(text);
    // Legacy fallback
    return new Promise(resolve => {
      const ta = document.createElement('textarea');
      ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
      document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy'); } catch {}
      document.body.removeChild(ta); resolve();
    });
  }

  /* ── Image lightbox (flyer enlarge, works for all visitors) ── */
  const imgLb = document.getElementById('img-lightbox');
  const imgLbImg = document.getElementById('img-lightbox-img');
  function openImageLightbox(url, alt) {
    imgLbImg.src = url;
    imgLbImg.alt = alt || '';
    imgLb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeImageLightbox() {
    imgLb.classList.remove('open');
    imgLbImg.src = '';
    document.body.style.overflow = '';
  }
  document.querySelectorAll('.event-flyer-thumb').forEach(thumb => {
    thumb.addEventListener('click', (e) => {
      e.preventDefault();
      openImageLightbox(thumb.dataset.flyerUrl, thumb.dataset.flyerTitle);
    });
  });
  document.getElementById('img-lightbox-close').addEventListener('click', closeImageLightbox);
  imgLb.addEventListener('click', (e) => { if (e.target === imgLb) closeImageLightbox(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && imgLb.classList.contains('open')) closeImageLightbox();
  });

  /* ── DEEP LINK: open verse from URL hash on page load ── */
  async function handleVerseHash() {
    const h = location.hash;
    const m = h.match(/^#verse=([^&]+)-(\d+)-(\d+)(?:&trans=(kjv|esv))?/i);
    if (!m) return;
    const book = decodeURIComponent(m[1]);
    const chapter = parseInt(m[2], 10);
    const verse = parseInt(m[3], 10);
    const trans = (m[4] || 'esv').toLowerCase();
    openSearch();            // warm the UI
    await loadCorpus(trans);
    // Close search overlay since we're jumping straight to a verse
    closeSearch();
    const doc = (docs[trans] || []).find(d => d.book === book && d.chapter === chapter && d.verse === verse);
    if (doc) openReader(trans, doc.id);
    else showToast('Verse not found');
  }
  // Run after MiniSearch lib has had a chance to load
  window.addEventListener('DOMContentLoaded', () => {
    if (location.hash.startsWith('#verse=')) setTimeout(handleVerseHash, 300);
  });
  window.addEventListener('hashchange', handleVerseHash);
})();
</script>

@if ($canEdit)
  {{-- ═══ Bulletin picker overlay (long-press "Latest" to open) ═══ --}}
  <div id="picker-overlay" class="picker-overlay" role="dialog" aria-hidden="true">
    <div class="picker-card">
      <div class="picker-head">
        <h3>Jump to any bulletin</h3>
        <button class="picker-close" type="button" aria-label="Close">×</button>
      </div>
      <div class="picker-hint">Tap any thumbnail to open that bulletin's editor.</div>
      <div id="picker-grid" class="picker-grid">
        <div class="picker-loading">Loading…</div>
      </div>
    </div>
  </div>

  {{-- ═══ Event series (multi-night Revival/Week of Prayer) creation modal ═══ --}}
  <div id="series-modal" class="series-modal" role="dialog" aria-hidden="true">
    <div class="series-card">
      <div class="series-head">
        <div class="series-eyebrow">Create an event series</div>
        <h3>Revival · Week of Prayer · Easter</h3>
        <p class="series-sub">One multi-night event. Each night becomes its own bulletin with the same shared design — only the sermon topic, scripture, and segments change per night.</p>
        <button class="series-close" type="button" aria-label="Close">×</button>
      </div>
      <form id="series-form" action="{{ route('bulletins.event-series') }}" method="POST" class="series-form">
        @csrf
        <div class="series-row">
          <label class="series-lbl" for="series-event-name">Event name</label>
          <input type="text" name="event_name" id="series-event-name" class="series-input" placeholder="e.g. Revival" maxlength="120" required>
        </div>
        <div class="series-row-grid">
          <div class="series-row">
            <label class="series-lbl" for="series-first-date">First service</label>
            <input type="date" name="first_date" id="series-first-date" class="series-input" required>
          </div>
          <div class="series-row">
            <label class="series-lbl" for="series-last-date">Last service</label>
            <input type="date" name="last_date" id="series-last-date" class="series-input" required>
          </div>
        </div>
        <label class="series-toggle">
          <input type="checkbox" name="skip_sabbath_mornings" id="series-skip-sabbath" value="1" checked>
          <span>
            <span class="series-toggle-title">Skip Sabbath mornings</span>
            <span class="series-toggle-hint">Keeps Sabbath morning service separate — don't create an event bulletin for it.</span>
          </span>
        </label>
        <div id="series-preview" class="series-preview" style="display:none;">
          <span id="series-preview-text">—</span>
        </div>
        <div class="series-actions">
          <button type="button" class="series-btn-cancel">Cancel</button>
          <button type="submit" class="series-btn-primary" id="series-submit-btn">Create</button>
        </div>
      </form>
    </div>
  </div>

  {{-- (existing event modal below) --}}
  <div id="event-modal" class="event-modal" role="dialog" aria-hidden="true">
    <div class="event-modal-card">
      <div class="event-modal-head">
        <h3 id="event-modal-title">Add event</h3>
        <button class="event-modal-close" type="button" aria-label="Close">×</button>
      </div>
      <form id="event-form" data-create-url="{{ route('events.store') }}">
        <input type="hidden" name="id" value="">
        <label class="ev-row">
          <span>Title</span>
          <input type="text" name="title" required maxlength="180" placeholder="e.g. Prayer Meeting">
        </label>
        <div class="ev-row-grid">
          <label class="ev-row">
            <span>Date</span>
            <input type="date" name="date" required>
          </label>
          <label class="ev-row">
            <span>Time <small style="font-weight:400; letter-spacing:0; text-transform:none; color:var(--ink-soft);">(optional)</small></span>
            <input type="time" name="time" value="10:30" placeholder="leave blank for all-day">
          </label>
        </div>
        <label class="ev-row">
          <span>Location</span>
          <input type="text" name="location" maxlength="180" placeholder="Optional">
        </label>
        <label class="ev-row">
          <span>Notes</span>
          <textarea name="notes" maxlength="1000" rows="3" placeholder="Optional"></textarea>
        </label>
        @if (isset($departments) && $departments->count())
          <label class="ev-row">
            <span>Department</span>
            <select name="department_id">
              <option value="">— None —</option>
              @foreach ($departments as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
              @endforeach
            </select>
          </label>
        @endif
        <div class="event-modal-actions">
          <button type="button" class="btn event-modal-delete" hidden>Delete</button>
          <button type="button" class="btn event-modal-cancel">Cancel</button>
          <button type="submit" class="btn primary">Save</button>
        </div>
      </form>
    </div>
  </div>
@endif

@if ($canEdit && $bulletin)
  <div id="manage-names-modal" class="names-modal" role="dialog" aria-hidden="true">
    <div class="names-modal-card">
      <div class="names-modal-head">
        <h3>Manage names</h3>
        <button id="manage-names-close" type="button" aria-label="Close">×</button>
      </div>
      <p class="names-modal-help">Names appear here automatically as you assign them on bulletin lines. Click the × to stop a name from showing in autocomplete (e.g. typo, member moved away).</p>
      <div id="names-list" class="names-list">Loading…</div>
    </div>
  </div>
  <div id="save-indicator" class="save-indicator">Saved.</div>
  <div id="undo-toast" class="undo-toast" role="status" aria-live="polite">
    <span id="undo-toast-msg">Reset to default.</span>
    <button id="undo-toast-btn" type="button">Undo</button>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js" defer></script>

  <script>
    (function() {
      const CSRF = document.querySelector('meta[name="csrf-token"]').content;
      const indicator = document.getElementById('save-indicator');
      const publishStatus = document.getElementById('publish-status');

      function showSaved(text = 'Saved.') {
        indicator.textContent = text;
        indicator.classList.add('show');
        clearTimeout(indicator._t);
        indicator._t = setTimeout(() => indicator.classList.remove('show'), 1400);
      }

      async function patch(url, payload) {
        const res = await fetch(url, {
          method: 'PATCH',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          body: JSON.stringify(payload),
        });
        if (!res.ok) throw new Error('Save failed ' + res.status);
        return res.json();
      }
      async function post(url, payload = {}) {
        const res = await fetch(url, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          body: JSON.stringify(payload),
        });
        if (!res.ok) throw new Error('Create failed ' + res.status);
        return res.json();
      }
      async function del(url) {
        const res = await fetch(url, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Delete failed ' + res.status);
        return res.json();
      }

      /* Read displayable text from an editable element, ignoring affordance children
         (delete × button, drag handle, dot indicators). Without this filter, clicking
         the × inside an editable container reads "Title ×" and saves it. */
      function readEditableText(el) {
        if (el.dataset.raw) return el.dataset.raw;
        let txt = '';
        el.childNodes.forEach(node => {
          if (node.nodeType === Node.TEXT_NODE) {
            txt += node.textContent;
          } else if (node.nodeType === Node.ELEMENT_NODE) {
            if (node.classList.contains('row-actions') || node.classList.contains('drag-handle') || node.classList.contains('event-dot')) return;
            txt += node.textContent;
          }
        });
        return txt.trim();
      }

      /* Click-to-edit */
      document.querySelectorAll('.editable').forEach(el => {
        el.addEventListener('click', function(ev) {
          // Don't open edit if click originated on a delete button or drag handle inside this editable
          const t = ev.target;
          if (t.closest && (t.closest('.row-actions') || t.closest('.drag-handle'))) return;
          if (el.classList.contains('editing')) return;
          const type = el.dataset.type || 'text';
          const field = el.dataset.field;
          const url = el.dataset.editUrl;
          const current = readEditableText(el);

          el.classList.add('editing');
          el.innerHTML = '';

          // SECTION HEADERS — render a <select> of standard bulletin sections
          // with a Custom escape, instead of free-form text. Prevents the kind
          // of confused section names that broke bulletin 6 on 2026-04-25.
          let input;
          let isSectionPicker = (field === 'section');
          if (isSectionPicker) {
            const STANDARD_SECTIONS = [
              '',                                         // empty = no section header
              'Pastoral Greetings',
              'Pastoral Greetings / Baby Dedication',
              'Praise & Worship',
              'Service of Worship',
              'Children\'s Story',
              'Mission Story',
              'Special Music',
              'Sermon',
              'Tithes & Offerings',
              'Communion',
              'Foot Washing',
              'Closing',
              'Benediction',
            ];
            input = document.createElement('select');
            input.style.font = 'inherit';
            input.style.color = 'inherit';
            input.style.background = 'transparent';
            input.style.border = '0';
            input.style.outline = '0';
            input.style.width = '100%';
            input.style.padding = '0';
            input.style.cursor = 'pointer';

            const placeholdersForSection = ['New section', ''];
            const currentClean = placeholdersForSection.includes(current) ? '' : (el.dataset.raw || current);

            STANDARD_SECTIONS.forEach(sec => {
              const opt = document.createElement('option');
              opt.value = sec;
              opt.textContent = sec || '— No section header —';
              if (sec === currentClean) opt.selected = true;
              input.appendChild(opt);
            });
            // Custom escape — appears at the end. If selected, swap to text input.
            const customOpt = document.createElement('option');
            customOpt.value = '__custom__';
            customOpt.textContent = '✨ Custom…';
            // If currentClean isn't in standard list, treat it as already-custom and select Custom
            const isAlreadyCustom = currentClean && !STANDARD_SECTIONS.includes(currentClean);
            if (isAlreadyCustom) customOpt.selected = true;
            input.appendChild(customOpt);
            el.appendChild(input);

            // If currentClean is custom, swap to text immediately so user can edit
            if (isAlreadyCustom) {
              setTimeout(() => swapToCustomText(currentClean), 0);
            }

            // When user picks Custom, swap to a text input
            input.addEventListener('change', () => {
              if (input.value === '__custom__') {
                swapToCustomText('');
              }
            });

            function swapToCustomText(initialValue) {
              const txt = document.createElement('input');
              txt.type = 'text';
              txt.placeholder = 'Custom section name…';
              txt.value = initialValue;
              txt.style.font = 'inherit';
              txt.style.color = 'inherit';
              txt.style.background = 'transparent';
              txt.style.border = '0';
              txt.style.outline = '0';
              txt.style.width = '100%';
              txt.style.padding = '0';
              el.replaceChild(txt, input);
              input = txt;
              setTimeout(() => txt.focus(), 0);
              // Re-attach blur/keydown handlers (added below) to the new element
              txt.addEventListener('blur', commitWrap);
              txt.addEventListener('keydown', keydownWrap);
            }

            input.focus();
          } else {
            // Default: text/textarea/date input as before
            input = document.createElement(type === 'textarea' ? 'textarea' : 'input');
            if (type !== 'textarea') input.type = (type === 'date' || type === 'datetime-local') ? 'text' : type;
            const placeholders = ['Part…','Who…','Details…','Announcement title…','New section','Location…','Notes…'];
            input.value = placeholders.includes(current) ? '' : (el.dataset.raw || current);
            el.appendChild(input);
            input.focus();
            try { if (type !== 'textarea' && type !== 'date' && type !== 'datetime-local') input.setSelectionRange(input.value.length, input.value.length); } catch(e) {}
          }

          let fp;
          if ((type === 'date' || type === 'datetime-local') && window.flatpickr) {
            fp = window.flatpickr(input, {
              enableTime: type === 'datetime-local',
              dateFormat: type === 'datetime-local' ? 'Y-m-d\\TH:i' : 'Y-m-d',
              defaultDate: el.dataset.raw || undefined,
              onClose: () => setTimeout(() => input.blur(), 10),
            });
            fp.open();
          }

          let suggestBox;
          if (el.dataset.autocomplete) attachSuggest(input, el, el.dataset.autocomplete);

          async function commit() {
            // For section picker: __custom__ pseudo-value means "still picking" — treat as empty
            let value = input.value;
            if (isSectionPicker && value === '__custom__') value = '';
            const displayValue = value || (field === 'part' ? 'Part…' : field === 'person' ? 'Who…' : field === 'detail' ? 'Details…' : field === 'title' ? 'Announcement title…' : field === 'section' ? 'New section' : '');
            // Snapshot bulletin-line text edits so undo can step through them granularly
            const lineOwner = el.closest('[data-line-id]');
            const inOrderList = orderList?.contains(el);
            if (inOrderList && lineOwner && value !== current) {
              pushHistory('Edit ' + (field || 'field'));
            }
            try {
              await patch(url, { [field]: value });
              // Keep the line div's data-* attrs in sync so the next snapshot reflects the new value
              if (lineOwner && field && ['section', 'part', 'person', 'kind'].includes(field)) {
                lineOwner.dataset[field] = value || '';
              }
              showSaved();
              el.classList.remove('editing');
              if (el.classList.contains('cal-tile') && field === 'start_at' && value && value !== current) {
                // Date actually changed — reload so the events list re-sorts cleanly
                showSaved('Date updated');
                setTimeout(() => location.reload(), 350);
                return;
              } else if (el.classList.contains('cal-tile') && field === 'start_at') {
                // No-op (user opened picker but didn't change the date) — leave tile alone
                el.classList.remove('editing');
                el.dataset.raw = current;
                const d = new Date(current);
                el.innerHTML =
                  '<div class="month">' + d.toLocaleDateString('en-US', { month: 'short' }).toUpperCase() + '</div>' +
                  '<div class="day">' + d.getDate() + '</div>' +
                  '<div class="dow">' + d.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase() + '</div>';
                return;
              } else if (field === 'service_date' && value) {
                el.dataset.raw = value;
                el.textContent = new Date(value + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
              } else {
                if (field === 'start_at' && value) el.dataset.raw = value;
                el.textContent = displayValue;
              }
              markDirty();
            } catch (e) {
              el.classList.remove('editing');
              el.textContent = current;
              showSaved('Error');
            }
            if (suggestBox) suggestBox.remove();
          }

          input.addEventListener('blur', commit);
          input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && type !== 'textarea') { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') { el.classList.remove('editing'); el.textContent = current; if (suggestBox) suggestBox.remove(); }
          });
        });
      });

      /* Scoped autocomplete: scope = 'person' (people only) or 'hymn' (hymnal only). */
      function attachSuggest(input, owner, scope) {
        const box = document.createElement('div');
        box.className = 'suggest';
        box.style.display = 'none';
        document.body.appendChild(box);

        function position() {
          const r = owner.getBoundingClientRect();
          box.style.left = (r.left + window.scrollX) + 'px';
          box.style.top  = (r.bottom + window.scrollY + 4) + 'px';
          box.style.minWidth = Math.max(220, r.width) + 'px';
        }


  /* ── Local-first suggestion lookup (Item #3 from senior eng review) ──────
     Hymn list and 66 Bible books are now resolved locally in the browser.
     People are still hit on the API (the list evolves as bulletins are edited).
     Hymnal data piggybacks on the existing /search/hymnal.json corpus the
     full-search modal already loads — no extra HTTP. */
  const BIBLE_BOOKS_66 = [
    'Genesis','Exodus','Leviticus','Numbers','Deuteronomy',
    'Joshua','Judges','Ruth','1 Samuel','2 Samuel','1 Kings','2 Kings',
    '1 Chronicles','2 Chronicles','Ezra','Nehemiah','Esther','Job',
    'Psalms','Proverbs','Ecclesiastes','Song of Solomon','Isaiah','Jeremiah',
    'Lamentations','Ezekiel','Daniel','Hosea','Joel','Amos','Obadiah',
    'Jonah','Micah','Nahum','Habakkuk','Zephaniah','Haggai','Zechariah','Malachi',
    'Matthew','Mark','Luke','John','Acts','Romans',
    '1 Corinthians','2 Corinthians','Galatians','Ephesians','Philippians','Colossians',
    '1 Thessalonians','2 Thessalonians','1 Timothy','2 Timothy','Titus','Philemon',
    'Hebrews','James','1 Peter','2 Peter','1 John','2 John','3 John','Jude','Revelation'
  ];

  let _hymnList = null;          // lazy-loaded on first hymn-scope query
  let _hymnListPromise = null;
  async function getHymnList() {
    if (_hymnList) return _hymnList;
    if (_hymnListPromise) return _hymnListPromise;
    _hymnListPromise = (async () => {
      // Reuse the same hymnal.json the full-search modal loads
      try {
        const res = await fetch('/search/hymnal.json');
        const data = await res.json();
        _hymnList = data.map(h => ({ number: h.number, title: h.title, label: '#' + h.number + ' ' + h.title }));
        return _hymnList;
      } catch (e) {
        _hymnList = [];
        return _hymnList;
      }
    })();
    return _hymnListPromise;
  }

  function localBibleMatch(q) {
    const needle = q.toLowerCase();
    const out = [];
    for (const book of BIBLE_BOOKS_66) {
      if (book.toLowerCase().includes(needle)) {
        out.push({ label: book, name: book });
        if (out.length >= 10) break;
      }
    }
    return out;
  }

  function localHymnMatch(q, list) {
    const needle = q.toLowerCase();
    const out = [];
    // If query is purely numeric, prefix-match by hymn number
    if (/^\d+$/.test(q)) {
      for (const h of list) {
        if (String(h.number).startsWith(q)) {
          out.push({ label: h.label });
          if (out.length >= 12) break;
        }
      }
      return out;
    }
    // Otherwise substring-match in title
    for (const h of list) {
      if (h.title.toLowerCase().includes(needle)) {
        out.push({ label: h.label });
        if (out.length >= 12) break;
      }
    }
    return out;
  }

  async function getSuggestions(q, scope) {
    // hymn-only scope — local
    if (scope === 'hymn') {
      const list = await getHymnList();
      return { hymns: localHymnMatch(q, list) };
    }
    // bible-only scope — local
    if (scope === 'bible') {
      return { books: localBibleMatch(q) };
    }
    // person-only scope — API still
    if (scope === 'person') {
      try {
        const res = await fetch('/api/suggestions?q=' + encodeURIComponent(q) + '&scope=person', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        return await res.json();
      } catch (e) { return { people: [] }; }
    }
    // 'any' / unset scope — combine local hymn + bible with API people
    const list = await getHymnList();
    let people = [];
    try {
      const res = await fetch('/api/suggestions?q=' + encodeURIComponent(q) + '&scope=person', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      people = data.people || [];
    } catch (e) {}
    return {
      hymns: localHymnMatch(q, list),
      books: localBibleMatch(q),
      people: people,
    };
  }

        let timer;
        input.addEventListener('input', () => {
          clearTimeout(timer);
          timer = setTimeout(async () => {
            const q = input.value.trim();
            if (q.length < 1) { box.style.display = 'none'; return; }
            const data = await getSuggestions(q, scope || '');
            const items = [];
            if (scope === 'person') {
              (data.people || []).forEach(p => items.push({ label: p, value: p, tag: 'Person' }));
            } else if (scope === 'hymn') {
              (data.hymns || []).forEach(h => items.push({ label: h.label, value: h.label, tag: 'Hymn' }));
            } else if (scope === 'bible') {
              // Picking a book inserts "<Book> " (with trailing space) so the user can immediately
              // type the chapter:verse. e.g. autocomplete picks "John" → input shows "John " ready for "3:16".
              (data.books || []).forEach(b => items.push({ label: b.label, value: b.name + ' ', tag: 'Bible' }));
            } else { // 'any' or unset — show all three
              (data.hymns || []).forEach(h => items.push({ label: h.label, value: h.label, tag: 'Hymn' }));
              (data.books || []).forEach(b => items.push({ label: b.label, value: b.name + ' ', tag: 'Bible' }));
              (data.people || []).forEach(p => items.push({ label: p, value: p, tag: 'Person' }));
            }
            if (items.length === 0) { box.style.display = 'none'; return; }
            box.innerHTML = items.map(i => `<div class="suggest-item" data-value="${i.value.replace(/"/g, '&quot;')}">${i.label} <span style="float:right;font-family:'JetBrains Mono',monospace;font-size:9px;letter-spacing:.1em;color:var(--ink-soft);text-transform:uppercase;margin-left:10px;">${i.tag}</span></div>`).join('');
            box.querySelectorAll('.suggest-item').forEach(el => el.addEventListener('mousedown', (e) => { e.preventDefault(); input.value = el.dataset.value; input.blur(); }));
            position();
            box.style.display = 'block';
          }, 160);
        });
        input.addEventListener('blur', () => setTimeout(() => box.remove(), 200));
      }

      /* Dirty flag — flip publish-status chip */
      function markDirty() {
        publishStatus.textContent = 'Unpublished changes';
        publishStatus.classList.add('unpub');
        document.body.classList.add('bulletin-dirty');
      }

      /* Add line / add section / add announcement */
      document.querySelectorAll('[data-add-line-url]').forEach(btn => btn.addEventListener('click', async () => {
        try { await post(btn.dataset.addLineUrl, { kind: 'line' }); markDirty(); location.reload(); } catch (e) { showSaved('Error'); }
      }));
      // (Removed the "+ Add hymn" button — hymn slots already exist on the bulletin
      // (Opening Hymn / Closing Hymn etc.). To add a hymn # under one of those rows,
      // user clicks "+ Add line" and types "#15 ..." which auto-renders centered italic.)
      document.querySelectorAll('[data-add-section-url]').forEach(btn => btn.addEventListener('click', async () => {
        try { await post(btn.dataset.addSectionUrl, { kind: 'section_header', section: 'New section' }); markDirty(); location.reload(); } catch (e) { showSaved('Error'); }
      }));
      document.querySelectorAll('[data-add-ann-url]').forEach(btn => btn.addEventListener('click', async () => {
        try { await post(btn.dataset.addAnnUrl, { title: 'New announcement', detail: '' }); markDirty(); location.reload(); } catch (e) { showSaved('Error'); }
      }));

      /* Delete row — only the explicit × / Delete buttons inside .row-actions (class="del"),
         NOT every element that happens to carry a data-delete-url (e.g. the event edit pencil
         which carries one to pass into the modal). */
      document.querySelectorAll('[data-delete-url].del').forEach(btn => btn.addEventListener('click', async (ev) => {
        ev.stopPropagation(); // don't bubble to .editable parents (corrupts title with "×")
        ev.preventDefault();
        if (!confirm('Delete this row?')) return;
        try {
          const isBulletinLine = btn.closest('[data-line-id]') && orderList?.contains(btn);
          if (isBulletinLine && snapshotLines().length) {
            pushHistory('Delete row');
            queuePendingToast('Row deleted');
          }
          // For event deletions, stash an undo entry that survives the reload
          const eventEl = btn.closest('.event');
          const eventMatch = btn.dataset.deleteUrl.match(/\/events\/(\d+)$/);
          if (eventEl && eventMatch) {
            const titleEl = eventEl.querySelector('.event-title');
            const title = titleEl ? readEditableText(titleEl).slice(0, 80) : 'Event';
            try {
              sessionStorage.setItem('event-undo-pending', JSON.stringify({ id: eventMatch[1], title, ts: Date.now() }));
            } catch (e) {}
          }
          await del(btn.dataset.deleteUrl);
          markDirty();
          location.reload();
        } catch (e) { showSaved('Error'); }
      }));

      /* Publish */
      document.getElementById('publish-btn').addEventListener('click', async () => {
        const url = document.getElementById('publish-btn').dataset.publishUrl;
        try {
          await post(url);
          publishStatus.textContent = 'Published just now';
          publishStatus.classList.remove('unpub');
          showSaved('Live.');
        } catch (e) { showSaved('Error'); }
      });

      /* ───── Bulletin lines: drag-to-reorder + unified undo/redo history ───── */
      const orderList = document.getElementById('bulletin-order-list');
      const undoToast = document.getElementById('undo-toast');
      const undoToastMsg = document.getElementById('undo-toast-msg');
      const undoToastBtn = document.getElementById('undo-toast-btn');
      const undoBtn = document.getElementById('undo-btn');
      const redoBtn = document.getElementById('redo-btn');
      const HISTORY_KEY = 'bulletin-history-' + (orderList?.dataset.reorderUrl || 'x');
      const PENDING_KEY = 'bulletin-pending-toast';
      const HISTORY_MAX = 20;

      function snapshotLines() {
        if (!orderList) return [];
        return Array.from(orderList.querySelectorAll('[data-line-id]')).map(el => ({
          section: el.dataset.section || null,
          part: el.dataset.part || null,
          person: el.dataset.person || null,
          kind: el.dataset.kind || 'line',
        }));
      }

      function loadHistory() {
        try { return JSON.parse(sessionStorage.getItem(HISTORY_KEY)) || { undo: [], redo: [] }; }
        catch (e) { return { undo: [], redo: [] }; }
      }
      function saveHistory(h) {
        try { sessionStorage.setItem(HISTORY_KEY, JSON.stringify(h)); } catch (e) {}
        refreshHistoryButtons();
      }
      function refreshHistoryButtons() {
        const h = loadHistory();
        if (undoBtn) { undoBtn.disabled = h.undo.length === 0; undoBtn.title = h.undo.length ? 'Undo: ' + h.undo[h.undo.length-1].description + ' (⌘Z)' : 'Undo (⌘Z)'; }
        if (redoBtn) { redoBtn.disabled = h.redo.length === 0; redoBtn.title = h.redo.length ? 'Redo: ' + h.redo[h.redo.length-1].description + ' (⌘⇧Z)' : 'Redo (⌘⇧Z)'; }
      }

      // Push a snapshot BEFORE a destructive action. preCapture = lines snapshot to push (defaults to current DOM state).
      function pushHistory(description, preCapture) {
        const h = loadHistory();
        h.undo.push({ description, lines: preCapture || snapshotLines() });
        if (h.undo.length > HISTORY_MAX) h.undo.shift();
        h.redo = [];
        saveHistory(h);
      }
      function queuePendingToast(description) {
        try { sessionStorage.setItem(PENDING_KEY, JSON.stringify({ description, ts: Date.now() })); } catch (e) {}
      }

      let toastTimer = null;
      function showUndoToast(description, durationMs = 15000) {
        clearTimeout(toastTimer);
        undoToastMsg.textContent = description + ' — Undo?';
        undoToast.classList.add('show');
        undoToastBtn.onclick = () => { undoToast.classList.remove('show'); undo(); };
        toastTimer = setTimeout(() => undoToast.classList.remove('show'), durationMs);
      }

      async function applyRestore(lines) {
        if (!orderList || !orderList.dataset.restoreUrl) return;
        await post(orderList.dataset.restoreUrl, { lines });
        location.reload();
      }
      async function undo() {
        const h = loadHistory();
        if (!h.undo.length) return;
        const cur = snapshotLines();
        const prev = h.undo.pop();
        h.redo.push({ description: prev.description, lines: cur });
        if (h.redo.length > HISTORY_MAX) h.redo.shift();
        saveHistory(h);
        await applyRestore(prev.lines);
      }
      async function redo() {
        const h = loadHistory();
        if (!h.redo.length) return;
        const cur = snapshotLines();
        const next = h.redo.pop();
        h.undo.push({ description: next.description, lines: cur });
        if (h.undo.length > HISTORY_MAX) h.undo.shift();
        saveHistory(h);
        await applyRestore(next.lines);
      }

      undoBtn?.addEventListener('click', undo);
      redoBtn?.addEventListener('click', redo);
      document.addEventListener('keydown', e => {
        const t = e.target;
        const inEditable = t && (t.isContentEditable || /^(input|textarea|select)$/i.test(t.tagName));
        if (inEditable) return;
        const k = e.key.toLowerCase();
        if ((e.metaKey || e.ctrlKey) && k === 'z' && !e.shiftKey) { e.preventDefault(); undo(); }
        else if ((e.metaKey || e.ctrlKey) && ((k === 'z' && e.shiftKey) || k === 'y')) { e.preventDefault(); redo(); }
      });

      // Init: don't depend on DOMContentLoaded — IIFE already runs at end of body, DOM is ready.
      refreshHistoryButtons();
      try {
        const pending = sessionStorage.getItem(PENDING_KEY);
        if (pending) {
          sessionStorage.removeItem(PENDING_KEY);
          const { description, ts } = JSON.parse(pending);
          if (Date.now() - ts < 6000) showUndoToast(description);
        }
      } catch (e) {}
      // Event-deletion undo toast
      try {
        const evtPending = sessionStorage.getItem('event-undo-pending');
        if (evtPending) {
          sessionStorage.removeItem('event-undo-pending');
          const { id, title, ts } = JSON.parse(evtPending);
          if (Date.now() - ts < 8000) {
            clearTimeout(toastTimer);
            undoToastMsg.textContent = '"' + title + '" deleted — Undo?';
            undoToast.classList.add('show');
            undoToastBtn.onclick = async () => {
              undoToast.classList.remove('show');
              try { await post('/events/' + id + '/restore'); location.reload(); }
              catch (e) { showSaved('Restore failed'); }
            };
            toastTimer = setTimeout(() => undoToast.classList.remove('show'), 30000);
          }
        }
      } catch (e) {}

      /* Bulletin-deletion undo toast (after redirect from a deleted bulletin) */
      try {
        const bulPending = sessionStorage.getItem('bulletin-undo-pending');
        if (bulPending) {
          sessionStorage.removeItem('bulletin-undo-pending');
          const { id, title, ts } = JSON.parse(bulPending);
          if (Date.now() - ts < 10000) {
            clearTimeout(toastTimer);
            undoToastMsg.textContent = 'Bulletin "' + title + '" deleted — Undo?';
            undoToast.classList.add('show');
            undoToastBtn.onclick = async () => {
              undoToast.classList.remove('show');
              try { await post('/bulletins/' + id + '/restore'); location.href = '/?bulletin=' + id; }
              catch (e) { showSaved('Restore failed'); }
            };
            toastTimer = setTimeout(() => undoToast.classList.remove('show'), 30000);
          }
        }
      } catch (e) {}

      /* Bulletin delete — hamburger menu item (super_admin only).
         Tiered confirm: if the bulletin's service_date is within ±7 days of
         today (i.e. THIS week's live bulletin), require typing DELETE to confirm.
         Otherwise simple confirm. André accidentally deleted today's bulletin
         on 2026-04-25 — this guardrail prevents the next repeat. */
      const bulDelBtn = document.getElementById('bulletin-delete-btn');
      if (bulDelBtn) {
        bulDelBtn.addEventListener('click', async (ev) => {
          ev.preventDefault();
          const title = bulDelBtn.dataset.bulletinTitle || 'this bulletin';
          const sd = bulDelBtn.dataset.serviceDate || '';
          const sdFormatted = bulDelBtn.dataset.serviceDateFormatted || sd;

          // Determine if this is "this week's" bulletin (within ±7 days of today)
          let isCurrentWeek = false;
          if (sd) {
            const sdDate = new Date(sd + 'T12:00:00');
            const today = new Date();
            today.setHours(12, 0, 0, 0);
            const diffDays = Math.abs(Math.round((sdDate - today) / (1000 * 60 * 60 * 24)));
            isCurrentWeek = diffDays <= 7;
          }

          if (isCurrentWeek) {
            // Hard guardrail — type DELETE to confirm
            const warning =
              '⚠️  THIS IS A LIVE BULLETIN  ⚠️\n\n' +
              'Service date: ' + sdFormatted + '\n' +
              'Title: "' + title + '"\n\n' +
              'This bulletin is in the active week and is what guests see right now ' +
              'when they visit the home page.\n\n' +
              'If you delete it, the public site will fall back to a different bulletin ' +
              '(or none at all) until it is restored.\n\n' +
              'To proceed, type DELETE (all caps) below:';
            const typed = prompt(warning);
            if (typed !== 'DELETE') {
              if (typed !== null) alert('Cancelled — you must type DELETE exactly to confirm.');
              return;
            }
          } else {
            // Soft confirm for past/old bulletins
            if (!confirm('Delete the bulletin "' + title + '"?\n\nService date: ' + (sdFormatted || 'unknown') + '\n\nLines, announcements, and snapshot will be hidden but recoverable for 30 seconds.')) return;
          }
          try {
            const res = await fetch(bulDelBtn.dataset.deleteUrl, {
              method: 'DELETE',
              credentials: 'same-origin',
              headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
            });
            if (!res.ok) throw new Error('delete failed: ' + res.status);
            const data = await res.json();
            sessionStorage.setItem('bulletin-undo-pending', JSON.stringify({
              id: data.bulletin_id,
              title: title,
              ts: Date.now(),
            }));
            location.href = '/';
          } catch (e) {
            showSaved('Delete failed');
            console.error(e);
          }
        });
      }

      /* Reset to default order — push history + reload */
      document.querySelectorAll('[data-standard-url]').forEach(btn => btn.addEventListener('click', async () => {
        if (!confirm('Reset bulletin to default order? You can undo this.')) return;
        try {
          if (snapshotLines().length) pushHistory('Reset to default');
          queuePendingToast('Reset to default');
          await post(btn.dataset.standardUrl);
          markDirty();
          location.reload();
        } catch (e) { showSaved('Error'); }
      }));

      /* SortableJS — drag-to-reorder bulletin lines */
      function initSortable() {
        if (!orderList || !window.Sortable) return;
        let preDragSnapshot = null;
        new Sortable(orderList, {
          handle: '.drag-handle',
          draggable: '[data-line-id]',
          animation: 150,
          ghostClass: 'sortable-ghost',
          chosenClass: 'sortable-chosen',
          onStart: () => { preDragSnapshot = snapshotLines(); },
          onEnd: async (evt) => {
            if (evt.oldIndex === evt.newIndex) return;
            const ids = Array.from(orderList.querySelectorAll('[data-line-id]')).map(el => Number(el.dataset.lineId));
            try {
              await fetch(orderList.dataset.reorderUrl, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ ids }),
              });
              if (preDragSnapshot) pushHistory('Reorder', preDragSnapshot);
              markDirty();
              showSaved('Reordered');
              showUndoToast('Order changed');
            } catch (e) { showSaved('Reorder failed'); location.reload(); }
          },
        });
      }
      if (window.Sortable) initSortable();
      else window.addEventListener('load', initSortable);

      /* Start next week's bulletin */
      document.querySelectorAll('[data-next-week-url]').forEach(btn => btn.addEventListener('click', async () => {
        if (!confirm("Start next Sabbath's bulletin?\nThis creates a new one and leaves today's alone.")) return;
        try { const r = await post(btn.dataset.nextWeekUrl); if (r.redirect) location.href = r.redirect; else location.reload(); } catch (e) { showSaved('Error'); }
      }));

      /* ───── Long-press "Latest" → bulletin picker overlay ───── */
      const latestBtn     = document.getElementById('latest-btn');
      const pickerOverlay = document.getElementById('picker-overlay');
      const pickerGrid    = document.getElementById('picker-grid');
      const pickerClose   = pickerOverlay?.querySelector('.picker-close');
      const LONG_PRESS_MS = 500;

      async function loadAndShowPicker() {
        pickerOverlay.classList.add('open');
        pickerGrid.innerHTML = '<div class="picker-loading">Loading…</div>';
        try {
          const res = await fetch('/api/bulletins/list', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
          });
          if (!res.ok) throw new Error('Could not load bulletins');
          const data = await res.json();
          const currentId = {{ $bulletin?->id ?? 'null' }};
          pickerGrid.innerHTML = (data.bulletins || []).map(b => {
            const d = new Date(b.service_date + 'T00:00:00');
            const month = d.toLocaleString('en-US', { month: 'short' }).toUpperCase();
            const day = d.getDate();
            const weekday = d.toLocaleString('en-US', { weekday: 'short' });
            const isEvent = b.kind === 'event_night';
            const isSabbath = b.kind === 'sabbath';
            const kindLabel = isEvent
              ? (b.event_name || 'Event') + ' · N' + (b.event_night_number || '?')
              : 'Sabbath';
            const timeLabel = b.service_time === 'evening' ? ' · PM' : '';
            const currentClass = (b.id === currentId) ? ' is-current' : '';
            const kindClass = isEvent ? 'is-event' : 'is-sabbath';
            return `<a href="/?bulletin=${b.id}" class="picker-thumb ${kindClass}${currentClass}">
              <span class="p-month">${month}</span>
              <span class="p-day">${day}</span>
              <span class="p-weekday">${weekday}${timeLabel}</span>
              <span class="p-kind">${kindLabel}</span>
            </a>`;
          }).join('');
        } catch (e) {
          pickerGrid.innerHTML = '<div class="picker-loading">Could not load. ' + e.message + '</div>';
        }
      }

      function closePicker() {
        pickerOverlay?.classList.remove('open');
      }

      if (latestBtn && pickerOverlay) {
        let pressTimer = null;
        let longPressed = false;

        function startPress(e) {
          longPressed = false;
          pressTimer = setTimeout(() => {
            longPressed = true;
            if (navigator.vibrate) navigator.vibrate(8); // tactile tick on mobile
            loadAndShowPicker();
          }, LONG_PRESS_MS);
        }
        function cancelPress() {
          clearTimeout(pressTimer);
          pressTimer = null;
        }

        latestBtn.addEventListener('pointerdown', startPress);
        latestBtn.addEventListener('pointerup', cancelPress);
        latestBtn.addEventListener('pointerleave', cancelPress);
        latestBtn.addEventListener('pointercancel', cancelPress);

        // Suppress the normal click navigation if user long-pressed
        latestBtn.addEventListener('click', (e) => {
          if (longPressed) { e.preventDefault(); e.stopPropagation(); longPressed = false; }
        });

        // Right-click / context-menu on desktop also opens picker (bonus)
        latestBtn.addEventListener('contextmenu', (e) => {
          e.preventDefault();
          loadAndShowPicker();
        });

        pickerClose?.addEventListener('click', closePicker);
        pickerOverlay.addEventListener('click', (e) => {
          if (e.target === pickerOverlay) closePicker();
        });
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && pickerOverlay.classList.contains('open')) closePicker();
        });
      }

      /* ───── Event series modal (multi-night Revival / Week of Prayer) ───── */
      const seriesModal = document.getElementById('series-modal');
      const seriesForm  = document.getElementById('series-form');
      const seriesBtn   = document.getElementById('new-event-series-btn');
      if (seriesBtn && seriesModal && seriesForm) {
        // Wire the new .series-close button (replacing old onclick handler)
        seriesModal.querySelector('.series-close')?.addEventListener('click', () => seriesModal.classList.remove('open'));
        seriesModal.querySelector('.series-btn-cancel')?.addEventListener('click', () => seriesModal.classList.remove('open'));
        // Click outside card closes
        seriesModal.addEventListener('click', (e) => {
          if (e.target === seriesModal) seriesModal.classList.remove('open');
        });
        const $name      = document.getElementById('series-event-name');
        const $first     = document.getElementById('series-first-date');
        const $last      = document.getElementById('series-last-date');
        const $skip      = document.getElementById('series-skip-sabbath');
        const $preview   = document.getElementById('series-preview');
        const $previewTx = document.getElementById('series-preview-text');
        const $submitBtn = document.getElementById('series-submit-btn');

        function updatePreview() {
          const first = $first.value, last = $last.value;
          if (!first || !last) { $preview.style.display = 'none'; return; }
          const a = new Date(first + 'T00:00:00'), b = new Date(last + 'T00:00:00');
          if (b < a) { $preview.style.display = 'none'; return; }
          let evenings = 0, sabbaths = 0;
          for (const d = new Date(a); d <= b; d.setDate(d.getDate() + 1)) {
            evenings++;
            if (d.getDay() === 6 && !$skip.checked) sabbaths++;
          }
          const total = evenings + sabbaths;
          const skipNote = $skip.checked ? ' (Sabbath mornings skipped)' : ' (includes Sabbath mornings)';
          $previewTx.textContent = total + ' service' + (total === 1 ? '' : 's') + ' will be created · ' + evenings + ' evening' + (evenings === 1 ? '' : 's') + skipNote;
          $preview.style.display = 'block';
          $submitBtn.textContent = 'Create ' + total;
        }

        seriesBtn.addEventListener('click', () => {
          // Default dates: today as first, +6 days as last
          const today = new Date();
          const end   = new Date(); end.setDate(today.getDate() + 6);
          const iso   = d => d.toISOString().slice(0, 10);
          $first.value = iso(today);
          $last.value  = iso(end);
          $skip.checked = true;
          $name.value   = '';
          updatePreview();
          seriesModal.classList.add('open');
          setTimeout(() => $name.focus(), 100);
        });

        [$first, $last, $skip].forEach(el => el.addEventListener('input', updatePreview));
        $skip.addEventListener('change', updatePreview);

        seriesForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          $submitBtn.disabled = true;
          $submitBtn.textContent = 'Creating…';
          try {
            const fd = new FormData(seriesForm);
            // Laravel expects the boolean field present only when checked; normalize to '1' | '0'
            if (!fd.has('skip_sabbath_mornings')) fd.set('skip_sabbath_mornings', '0');
            const res = await fetch(seriesForm.action, {
              method: 'POST', body: fd,
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
              credentials: 'same-origin',
            });
            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.message || 'Create failed');
            if (data.redirect) location.href = data.redirect;
            else location.reload();
          } catch (err) {
            alert('Could not create series: ' + err.message);
            $submitBtn.disabled = false;
            $submitBtn.textContent = 'Create';
          }
        });

        // ESC closes
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && seriesModal.classList.contains('open')) {
            seriesModal.classList.remove('open');
          }
        });
      }

      /* ───── Event modal editor (replaces inline tap-to-edit) ───── */
      const evModal = document.getElementById('event-modal');
      const evForm = document.getElementById('event-form');
      const evModalTitle = document.getElementById('event-modal-title');
      const evDeleteBtn = evModal?.querySelector('.event-modal-delete');
      const evCancelBtn = evModal?.querySelector('.event-modal-cancel');
      const evCloseBtn = evModal?.querySelector('.event-modal-close');
      const evCreateUrl = evForm?.dataset.createUrl;

      function openEventModal(data) {
        if (!evModal || !evForm) return;
        evForm.reset();
        // form.id/form.title shadow HTMLElement properties — use form.elements[name] to write inputs
        evForm.elements.id.value = data.id || '';
        evForm.elements.title.value = data.title || '';
        evForm.elements.date.value = data.date || new Date().toISOString().slice(0,10);
        evForm.elements.time.value = data.time || '10:30';
        evForm.elements.location.value = data.location || '';
        evForm.elements.notes.value = data.notes || '';
        if (evForm.elements.department_id) evForm.elements.department_id.value = data.department_id || '';
        evModalTitle.textContent = data.id ? 'Edit event' : 'Add event';
        if (data.id) {
          evDeleteBtn.hidden = false;
          evDeleteBtn.dataset.deleteUrl = data.deleteUrl;
          evDeleteBtn.dataset.id = data.id;
          evDeleteBtn.dataset.title = data.title;
        } else {
          evDeleteBtn.hidden = true;
        }
        evForm.dataset.updateUrl = data.updateUrl || '';
        evModal.classList.add('open');
        setTimeout(() => evForm.title.focus(), 100);
      }
      function closeEventModal() { evModal?.classList.remove('open'); }

      document.getElementById('event-add-btn')?.addEventListener('click', () => openEventModal({}));
      document.querySelectorAll('.event-edit-btn').forEach(btn => {
        btn.addEventListener('click', () => openEventModal({
          id: btn.dataset.id,
          title: btn.dataset.title,
          date: btn.dataset.startDate,
          time: btn.dataset.startTime,
          location: btn.dataset.location,
          notes: btn.dataset.notes,
          department_id: btn.dataset.departmentId,
          updateUrl: btn.dataset.updateUrl,
          deleteUrl: btn.dataset.deleteUrl,
        }));
      });
      evCancelBtn?.addEventListener('click', closeEventModal);
      evCloseBtn?.addEventListener('click', closeEventModal);
      evModal?.addEventListener('click', (e) => { if (e.target === evModal) closeEventModal(); });

      evForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(evForm);
        const id = fd.get('id');
        const date = fd.get('date');
        const time = fd.get('time') || '00:00'; // empty time = all-day event (display logic hides 00:00)
        const start_at = date + 'T' + time;
        const payload = {
          title: fd.get('title'),
          start_at,
          location: fd.get('location') || null,
          notes: fd.get('notes') || null,
          department_id: fd.get('department_id') || null,
        };
        try {
          if (id) await patch(evForm.dataset.updateUrl, payload);
          else    await post(evCreateUrl, payload);
          closeEventModal();
          location.reload();
        } catch (err) { showSaved('Save failed'); }
      });

      evDeleteBtn?.addEventListener('click', async () => {
        const id = evDeleteBtn.dataset.id;
        const title = evDeleteBtn.dataset.title || 'Event';
        if (!confirm('Delete "' + title + '"?')) return;
        try {
          sessionStorage.setItem('event-undo-pending', JSON.stringify({ id, title, ts: Date.now() }));
          await del(evDeleteBtn.dataset.deleteUrl);
          closeEventModal();
          location.reload();
        } catch (e) { showSaved('Delete failed'); }
      });

      /* Flyer upload (clerk) */
      document.querySelectorAll('[data-flyer-upload-url]').forEach(label => {
        const input = label.querySelector('input[type=file]');
        if (!input) return;
        input.addEventListener('change', async () => {
          const file = input.files[0];
          if (!file) return;
          const url = label.dataset.flyerUploadUrl;
          const fd = new FormData();
          fd.append('flyer', file);
          try {
            const res = await fetch(url, {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
              body: fd,
            });
            if (!res.ok) throw new Error('upload failed ' + res.status);
            showSaved('Flyer uploaded');
            location.reload();
          } catch (e) { showSaved('Upload error'); }
        });
      });

      /* Flyer remove (clerk) */
      document.querySelectorAll('[data-flyer-remove-url]').forEach(btn => btn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (!confirm('Remove this flyer?')) return;
        try { await del(btn.dataset.flyerRemoveUrl); location.reload(); } catch (err) { showSaved('Error'); }
      }));

      /* Manage names modal (clerk only) */
      const nmBtn = document.getElementById('manage-names-btn');
      const nmModal = document.getElementById('manage-names-modal');
      const nmList = document.getElementById('names-list');
      async function loadNames() {
        nmList.textContent = 'Loading…';
        try {
          const res = await fetch('/api/people', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
          const data = await res.json();
          const blocked = new Set(data.blocked || []);
          const all = (data.all || []);
          if (all.length === 0) { nmList.innerHTML = '<p style="color:var(--ink-soft); font-style:italic; text-align:center; padding:18px;">No names yet — they appear here once you assign people on bulletin lines.</p>'; return; }
          nmList.innerHTML = all.map(name => {
            const isBlocked = blocked.has(name);
            const safe = String(name).replace(/"/g, '&quot;').replace(/</g, '&lt;');
            return `<div class="names-row${isBlocked ? ' blocked' : ''}">
              <span class="name-text">${safe}${isBlocked ? ' <small style="color:var(--ink-soft);">(hidden)</small>' : ''}</span>
              <span class="name-actions">
                ${isBlocked
                  ? `<button data-unblock="${safe}">Restore</button>`
                  : `<button class="danger" data-block="${safe}">Hide</button>
                     <button class="danger" data-block-clear="${safe}" title="Hide and clear from past bulletins">Hide + clear</button>`}
              </span>
            </div>`;
          }).join('');
          nmList.querySelectorAll('[data-block]').forEach(b => b.addEventListener('click', () => blockName(b.dataset.block, false)));
          nmList.querySelectorAll('[data-block-clear]').forEach(b => b.addEventListener('click', () => blockName(b.dataset.blockClear, true)));
          nmList.querySelectorAll('[data-unblock]').forEach(b => b.addEventListener('click', () => unblockName(b.dataset.unblock)));
        } catch (e) { nmList.textContent = 'Failed to load.'; }
      }
      async function blockName(person, clearLines) {
        if (!confirm(`Hide "${person}" from autocomplete${clearLines ? ' AND clear it from past bulletin lines' : ''}?`)) return;
        try { await post('/api/people/block', { person, clear_from_lines: !!clearLines }); loadNames(); }
        catch (e) { showSaved('Failed'); }
      }
      async function unblockName(person) {
        try { await post('/api/people/unblock', { person }); loadNames(); }
        catch (e) { showSaved('Failed'); }
      }
      if (nmBtn && nmModal) {
        nmBtn.addEventListener('click', () => {
          document.getElementById('ham-menu')?.classList.remove('open');
          document.getElementById('ham-btn')?.setAttribute('aria-expanded','false');
          nmModal.classList.add('open');
          loadNames();
        });
        document.getElementById('manage-names-close').addEventListener('click', () => nmModal.classList.remove('open'));
        nmModal.addEventListener('click', (e) => { if (e.target === nmModal) nmModal.classList.remove('open'); });
        // Hint-link from inside the order-list also opens Manage Names
        document.getElementById('manage-names-from-hint')?.addEventListener('click', (e) => {
          e.preventDefault();
          nmModal.classList.add('open');
          loadNames();
        });
      }

      /* Two dropdown triggers — Go Live + Admin — share the same hover+click pattern.
         Each has its own panel; opening one closes the other.
         WCAG 2.2 / 1.4.13: dismissible, hoverable, persistent. */
      function wirePanel(triggerId, panelId, otherPanelId) {
        const btn = document.getElementById(triggerId);
        const panel = document.getElementById(panelId);
        const other = otherPanelId ? document.getElementById(otherPanelId) : null;
        const otherBtn = otherPanelId === 'ham-menu' ? document.getElementById('ham-btn')
                       : otherPanelId === 'go-live-panel' ? document.getElementById('go-live-trigger')
                       : null;
        if (!btn || !panel) return;
        let closeTimer;
        const open = () => {
          clearTimeout(closeTimer);
          if (other) { other.classList.remove('open'); otherBtn?.setAttribute('aria-expanded', 'false'); }
          panel.classList.add('open');
          btn.setAttribute('aria-expanded', 'true');
        };
        const close = () => {
          panel.classList.remove('open');
          btn.setAttribute('aria-expanded', 'false');
        };
        const scheduleClose = () => { clearTimeout(closeTimer); closeTimer = setTimeout(close, 200); };
        btn.addEventListener('mouseenter', open);
        btn.addEventListener('mouseleave', scheduleClose);
        panel.addEventListener('mouseenter', () => clearTimeout(closeTimer));
        panel.addEventListener('mouseleave', scheduleClose);
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          if (panel.classList.contains('open')) close(); else open();
        });
        document.addEventListener('click', (e) => {
          if (!panel.contains(e.target) && e.target !== btn && !btn.contains(e.target)) close();
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
      }
      wirePanel('go-live-trigger', 'go-live-panel', 'ham-menu');
      wirePanel('ham-btn', 'ham-menu', 'go-live-panel');

      /* (Brand-fold scroll listener removed — header brand is now just "Shalom" with no
         tail to collapse. Formal name lives in the footer.) */

      /* Theme picker (clerk only) */
      const themeBtn = document.getElementById('theme-pick-btn');
      const themePop = document.getElementById('theme-pick-pop');
      if (themeBtn && themePop) {
        const updateUrl = themePop.dataset.updateUrl;
        const current = themePop.dataset.current || 'default';
        themePop.querySelectorAll('.theme-swatch').forEach(sw => {
          if (sw.dataset.theme === current) sw.classList.add('active');
          sw.addEventListener('click', async () => {
            const theme = sw.dataset.theme;
            try {
              await patch(updateUrl, { theme });
              document.body.dataset.theme = theme;
              themePop.querySelectorAll('.theme-swatch').forEach(s => s.classList.toggle('active', s === sw));
              showSaved('Theme: ' + sw.title);
              themePop.classList.remove('open');
            } catch (e) { showSaved('Theme save failed'); }
          });
        });
        themeBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          themePop.classList.toggle('open');
        });
        document.addEventListener('click', (e) => {
          if (!themePop.contains(e.target) && e.target !== themeBtn) themePop.classList.remove('open');
        });
      }
    })();
  </script>
@endif

@auth
  <a href="{{ route('feedback.index') }}" class="feedback-fab" aria-label="Send feedback to Karlon and Claude" title="Send feedback">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
    </svg>
  </a>
  <style>
    .feedback-fab {
      position: fixed; bottom: 22px; right: 22px;
      width: 46px; height: 46px; border-radius: 50%;
      background: var(--teal, #03617A); color: #fff;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 6px 18px -4px rgba(3,97,122,0.5);
      text-decoration: none; opacity: 0.55; transform: scale(1);
      transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
      z-index: 90;
    }
    .feedback-fab:hover, .feedback-fab:focus-visible {
      opacity: 1; transform: scale(1.06);
      box-shadow: 0 10px 24px -4px rgba(3,97,122,0.6);
      outline: 0;
    }
    @media (max-width: 600px) { .feedback-fab { bottom: 16px; right: 16px; width: 42px; height: 42px; } }
    @media print { .feedback-fab { display: none; } }
  </style>
@endauth


<script>
// Theme toggle wiring — sun/moon pill inside Admin ▾ menu (super_admin only)
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var pill = document.querySelector('.theme-toggle');
    if (!pill) return;
    function applyTheme(mode) {
      var html = document.documentElement;
      if (mode === 'dark') html.classList.add('dark');
      else html.classList.remove('dark');
      pill.querySelectorAll('button').forEach(function (b) {
        b.setAttribute('aria-pressed', b.dataset.theme === mode ? 'true' : 'false');
      });
      try { localStorage.setItem('cop_admin_theme', mode); } catch (e) {}
    }
    var current = 'light';
    try { current = localStorage.getItem('cop_admin_theme') || 'light'; } catch (e) {}
    applyTheme(current);
    pill.addEventListener('click', function (e) {
      var btn = e.target.closest('button[data-theme]');
      if (!btn) return;
      applyTheme(btn.dataset.theme);
    });
  });
})();
</script>

<script>
// Guest nav hamburger — opens dropdown panel with all public-page links.
// Bulletin page only. Visible on desktop and mobile (header is crowded).
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('guest-nav-toggle');
    var nav = document.getElementById('guest-nav');
    if (!btn || !nav) return;

    function open()  { nav.classList.add('open');    btn.setAttribute('aria-expanded', 'true');  nav.setAttribute('aria-hidden', 'false'); }
    function close() { nav.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); nav.setAttribute('aria-hidden', 'true');  }
    function toggle() { nav.classList.contains('open') ? close() : open(); }

    btn.addEventListener('click', function (e) { e.stopPropagation(); toggle(); });
    document.addEventListener('click', function (e) {
      if (!nav.contains(e.target) && !btn.contains(e.target)) close();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('open')) { close(); btn.focus(); }
    });
  });
})();
</script>

<script>
// Bulletin Service Worker — network-first cache fallback so guests on flaky
// church-basement WiFi still see today's program. Admins editing always see
// fresh from network. Scope is '/' so it covers /welcome and /bulletin/{id};
// the SW's fetch handler filters internally to only intercept those paths.
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/bulletin-sw.js', { scope: '/' }).catch(() => {});
  });
}
</script>

<script>
// Welcome page text-size cycler — matches landing nav-font-btn behavior
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('welcome-font-btn');
    if (!btn) return;
    var html = document.documentElement;
    var states = ['normal', 'large', 'xlarge'];
    function applyFont(state) {
      html.classList.remove('font-normal', 'font-large', 'font-xlarge');
      html.classList.add('font-' + state);
      try { localStorage.setItem('cop_font_size', state); } catch (e) {}
    }
    var current = 'normal';
    try { current = localStorage.getItem('cop_font_size') || 'normal'; } catch (e) {}
    applyFont(current);
    btn.addEventListener('click', function () {
      current = states[(states.indexOf(current) + 1) % states.length];
      applyFont(current);
    });
  });
})();
</script>

<script>
// Always-show-during-week toggle — fires PATCH on change.
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var cb = document.getElementById('always-show-toggle');
    if (!cb) return;
    cb.addEventListener('change', async function () {
      var url = cb.dataset.updateUrl;
      try {
        await fetch(url, {
          method: 'PATCH',
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ always_show_during_week: cb.checked ? 1 : 0 }),
        });
        if (typeof showSaved === 'function') showSaved(cb.checked ? 'Always-show ON' : 'Always-show OFF');
      } catch (e) {
        if (typeof showSaved === 'function') showSaved('Save failed');
      }
    });
  });
})();
</script>

<script>
  (function() {
    const tb = document.querySelector('.bulletin-toolbar');
    if (!tb) return;
    window.addEventListener('scroll', () => {
      tb.classList.toggle('stuck', window.scrollY > 8);
    }, {passive: true});
  })();
</script>
</body>
</html>
