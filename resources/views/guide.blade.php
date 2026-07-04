<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Field Guide — The Church of Peace</title>
<meta name="robots" content="noindex">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-snap-type: y mandatory; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Instrument Sans', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }

  /* ── Each lesson owns the viewport ── */
  .slide { min-height: 100svh; scroll-snap-align: start; scroll-snap-stop: always; display: flex; align-items: center; justify-content: center; padding: 76px clamp(18px,5vw,48px) 64px; position: relative; }
  .slide-inner { width: 100%; max-width: 900px; display: grid; grid-template-columns: minmax(0,5fr) minmax(0,6fr); gap: clamp(24px,5vw,56px); align-items: center; }
  @media (max-width: 820px) { html { scroll-snap-type: y proximity; } .slide-inner { grid-template-columns: 1fr; gap: 22px; } }

  .zone-tag { font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--brass, #b08d3c); margin-bottom: 12px; }
  .slide h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(27px,4vw,38px); font-weight: 500; letter-spacing: -0.01em; line-height: 1.1; }
  .slide .copy { font-size: 15.5px; line-height: 1.7; color: var(--ink-soft); margin-top: 14px; }
  .slide .copy b { color: var(--ink); }
  .try { margin-top: 14px; font-size: 12.5px; font-weight: 600; color: var(--teal); background: color-mix(in srgb, var(--teal) 6%, #fff); border-left: 3px solid var(--teal); border-radius: 0 8px 8px 0; padding: 10px 14px; line-height: 1.55; }
  .try b { letter-spacing: 0.1em; font-size: 10px; text-transform: uppercase; display: block; margin-bottom: 2px; }
  .key { font: 600 12px 'Instrument Sans'; background: #fff; border: 1px solid var(--line); border-bottom-width: 2px; border-radius: 5px; padding: 2px 7px; white-space: nowrap; }

  /* ── The demo panel: a miniature of the real UI ── */
  .demo { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: clamp(16px,2.5vw,26px); box-shadow: 0 14px 40px rgba(26,35,50,.08); }
  .demo-cap { font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-faint); margin-bottom: 14px; }

  /* mock announcement rows (mirrors the real editor) */
  .m-row { display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--line); border-radius: 9px; padding: 9px 10px; margin-top: 8px; }
  .m-row:first-of-type { margin-top: 0; }
  .m-grip { color: #9aa0aa; font-size: 15px; letter-spacing: -1px; flex-shrink: 0; width: 14px; text-align: center; }
  .m-field { flex: 1; background: var(--parchment); border-radius: 6px; padding: 8px 10px; font-size: 13px; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; }
  .m-field.ghost { color: #b6bcc6; }
  .m-ic { color: var(--ink-soft); font-size: 12px; flex-shrink: 0; }
  .m-row.lifted { border-color: var(--teal); box-shadow: 0 10px 24px rgba(3,97,122,.22); transform: rotate(-1.3deg) translateY(-3px); }
  .m-row.child { margin-left: 26px; border-style: dashed; position: relative; }
  .m-row.child::before { content: '↳'; position: absolute; left: -19px; color: var(--teal); font-weight: 700; }
  .m-row.web { opacity: .82; background: color-mix(in srgb, var(--teal) 3%, #fff); }
  .m-chip { font-size: 8px; font-weight: 800; letter-spacing: .1em; color: var(--teal); border: 1px solid color-mix(in srgb, var(--teal) 40%, transparent); border-radius: 4px; padding: 2px 5px; flex-shrink: 0; }
  .m-divider { display: flex; align-items: center; gap: 10px; margin: 12px 0; }
  .m-divider::before, .m-divider::after { content: ''; flex: 1; border-top: 2px dashed color-mix(in srgb, var(--teal) 45%, var(--line)); }
  .m-divider span { font-size: 8.5px; font-weight: 700; letter-spacing: 0.13em; color: var(--teal); white-space: nowrap; }

  /* expanded textarea mock */
  .m-open { border: 1px solid var(--teal); border-radius: 9px; padding: 10px; box-shadow: 0 6px 20px rgba(3,97,122,.12); margin-top: 8px; }
  .m-open .m-field { margin-bottom: 8px; }
  .m-ta { background: #fff; border: 1px solid var(--teal); border-radius: 6px; padding: 10px 11px; font-size: 13px; line-height: 1.6; color: var(--ink); min-height: 92px; }
  .m-caret { display: inline-block; width: 1.5px; height: 14px; background: var(--teal); vertical-align: -2px; animation: blink 1.1s steps(1) infinite; }
  @keyframes blink { 50% { opacity: 0; } }

  /* print preview mock */
  .m-paper { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 14px 16px; font-size: 12.5px; line-height: 1.75; color: #222; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
  .m-paper .h { font-weight: 700; font-size: 12px; }
  .m-paper .o::before { content: '○'; margin-right: 7px; font-size: 10px; }
  .m-paper .f::before { content: '●'; margin-right: 7px; font-size: 10px; }
  .m-arrow { text-align: center; color: var(--ink-faint); font-size: 18px; margin: 10px 0; }

  /* 2-up sheet mock */
  .m-sheet { aspect-ratio: 11/8.5; background: #fff; border: 1px solid #ddd; border-radius: 4px; display: flex; box-shadow: 0 2px 8px rgba(0,0,0,.07); position: relative; }
  .m-half { flex: 1; padding: 10px; }
  .m-half + .m-half { border-left: 1.5px dashed #c4c4c4; }
  .m-lines > div { height: 5px; background: #e9e6de; border-radius: 3px; margin: 6px 0; }
  .m-lines > .t { height: 8px; width: 55%; margin: 0 auto 8px; background: #d8d4c8; }
  .m-scissors { position: absolute; left: 50%; top: -11px; transform: translateX(-50%); font-size: 14px; background: var(--parchment); padding: 0 4px; }

  /* day-grid mock */
  .m-days { display: grid; grid-template-columns: repeat(7,1fr); gap: 5px; }
  .m-day span { display: block; font-size: 8px; font-weight: 700; letter-spacing: .08em; color: var(--ink-soft); text-align: center; margin-bottom: 3px; }
  .m-day div { font-size: 9.5px; text-align: center; padding: 7px 2px; border: 1px solid var(--line); border-radius: 6px; background: var(--parchment); color: var(--ink); min-height: 30px; line-height: 1.3; }
  .m-day div.on { border-color: color-mix(in srgb, var(--teal) 45%, var(--line)); background: color-mix(in srgb, var(--teal) 7%, #fff); font-weight: 600; }
  .m-day div.off { color: #c2c7cf; }

  /* chips / buttons mocks */
  .m-chips { display: flex; gap: 8px; flex-wrap: wrap; }
  .m-lg { font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; border: 1px solid var(--line); background: #fff; border-radius: 8px; padding: 8px 13px; display: inline-flex; align-items: center; gap: 7px; color: var(--ink-soft); }
  .m-lg i { width: 8px; height: 8px; border-radius: 999px; }
  .m-lg.off { opacity: .42; border-style: dashed; } .m-lg.off i { background: #9aa0aa !important; }
  .m-gold { display: inline-flex; align-items: center; gap: 9px; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #fff; background: var(--brass, #b08d3c); border-radius: 9px; padding: 13px 22px; }
  .m-gold i { width: 8px; height: 8px; border-radius: 999px; background: #fff; animation: blink 2.2s ease-in-out infinite; }
  .m-url { font: 500 12.5px ui-monospace, monospace; background: #fff; border: 1px solid var(--line); border-radius: 999px; padding: 10px 16px; color: var(--ink-soft); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .m-url b { color: var(--teal); font-weight: 700; }

  /* edit-sheet card mock */
  .m-card { border: 1px solid var(--line); border-left: 4px solid var(--brass, #b08d3c); border-radius: 12px; padding: 13px 15px; }
  .m-card .t { font-size: 9px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: var(--brass, #b08d3c); }
  .m-input { background: var(--parchment); border: 1px solid var(--line); border-radius: 7px; padding: 9px 11px; font-size: 13px; margin-top: 8px; }
  .m-save { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: .1em; color: #fff; background: var(--teal); border-radius: 7px; padding: 8px 15px; margin-top: 9px; }
  .m-note { font-size: 10.5px; color: var(--ink-faint); font-style: italic; margin-top: 7px; }

  /* smart fill mock */
  .m-notes { background: var(--parchment); border: 1px solid var(--line); border-radius: 7px; padding: 10px 12px; font-size: 12px; line-height: 1.55; color: var(--ink-soft); }
  .m-smart { display: inline-block; font-size: 11px; font-weight: 700; color: var(--teal); background: color-mix(in srgb, var(--teal) 7%, #fff); border: 1px solid color-mix(in srgb, var(--teal) 30%, var(--line)); border-radius: 7px; padding: 6px 12px; margin: 10px 0; }

  /* loop diagram */
  .m-loop { display: grid; grid-template-columns: 1fr; gap: 8px; text-align: center; }
  .m-node { border: 1px solid var(--line); background: #fff; border-radius: 10px; padding: 10px; font-size: 12px; font-weight: 600; }
  .m-node.src { border-color: var(--teal); background: color-mix(in srgb, var(--teal) 6%, #fff); color: var(--teal-dark, var(--teal)); }
  .m-fan { display: grid; grid-template-columns: repeat(2,1fr); gap: 8px; }
  .m-down { color: var(--ink-faint); font-size: 15px; }

  /* hero + nav */
  .hero { text-align: center; }
  .hero .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); }
  .hero h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(44px,9vw,64px); font-weight: 500; letter-spacing: -0.02em; margin-top: 12px; }
  .hero p { font-size: 15px; color: var(--ink-soft); max-width: 460px; margin: 16px auto 0; line-height: 1.65; }
  .hero .scroll-hint { margin-top: 38px; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-faint); }
  .hero .scroll-hint .v { display: block; font-size: 20px; color: var(--teal); animation: nudge 1.8s ease-in-out infinite; }
  @keyframes nudge { 0%,100% { transform: translateY(0); } 50% { transform: translateY(6px); } }

  .stepnum { position: absolute; top: 30px; left: 50%; transform: translateX(-50%); font-size: 11px; font-weight: 700; letter-spacing: 0.2em; color: var(--ink-faint); }
  .fin { text-align: center; }
  .fin h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(34px,6vw,48px); font-weight: 500; }
  .fin p { color: var(--ink-soft); margin-top: 12px; }
  .fin b { color: var(--teal); font-weight: 500; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

{{-- ════════ HERO ════════ --}}
<section class="slide"><div class="hero">
  <div class="eyebrow">For Rosharde &amp; Andre</div>
  <h1>The Field Guide.</h1>
  <p>One screen per trick, with a picture of the exact thing you'll see. Scroll at your own pace — two minutes, and the site has no secrets left.</p>
  <div class="scroll-hint">Scroll<span class="v">↓</span></div>
</div></section>

{{-- ════════ 1 · DRAG ════════ --}}
<section class="slide"><div class="stepnum">1 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Bulletin editor</div>
    <h2>Drag the dots to reorder.</h2>
    <p class="copy">Every announcement has <b>⋮ dots on its far left</b>. Grab, drag, drop — finger or mouse. The ↑ ↓ arrows are still there when you want one careful step.</p>
  </div>
  <div class="demo"><div class="demo-cap">What you'll see</div>
    <div class="m-row"><span class="m-grip">⋮</span><span class="m-field">Prayer Ministry:</span><span class="m-ic">↑ ↓</span></div>
    <div class="m-row lifted"><span class="m-grip">⋮</span><span class="m-field">Ushers:</span><span class="m-ic">↑ ↓</span></div>
    <div class="m-row"><span class="m-grip">⋮</span><span class="m-field">Weekly Offering:</span><span class="m-ic">↑ ↓</span></div>
  </div>
</div></section>

{{-- ════════ 2 · BLANK TITLE ════════ --}}
<section class="slide"><div class="stepnum">2 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Bulletin editor</div>
    <h2>A blank title tucks under the row above.</h2>
    <p class="copy">Leave the <b>title empty</b> and that row becomes a bullet belonging to the announcement above it. The editor indents it with a ↳ so you can't miss it.</p>
    <div class="try"><b>Try it</b>Add announcement → skip the title → type "12/6 – Retro Night, 4pm" → open the PDF.</div>
  </div>
  <div class="demo"><div class="demo-cap">What you'll see</div>
    <div class="m-row"><span class="m-grip">⋮</span><span class="m-field">Upcoming Events:</span><span class="m-field">7/4 - 7/25 Evangelistic series…</span></div>
    <div class="m-row child"><span class="m-grip">⋮</span><span class="m-field ghost">Title (blank = bullets above)</span><span class="m-field">12/6 – Retro Night, 4pm</span></div>
  </div>
</div></section>

{{-- ════════ 3 · WRITING SPACE ════════ --}}
<section class="slide"><div class="stepnum">3 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Bulletin editor</div>
    <h2>Tap a detail — it opens into a writing space.</h2>
    <p class="copy">Rows stay slim until you tap in. Then the box grows so you can <b>write freely</b>. Press <span class="key">Enter</span> for a new line — <b>each line prints as its own bullet</b>. Click away and it folds back.</p>
  </div>
  <div class="demo"><div class="demo-cap">What you'll see</div>
    <div class="m-open">
      <div class="m-field" style="max-width:180px">Prayer Ministry:</div>
      <div class="m-ta">Join us for prayer at 5AM Monday to Friday<br>- Wednesdays 7pm on Zoom<br>Meeting ID: 830 0296 7327<span class="m-caret"></span></div>
    </div>
  </div>
</div></section>

{{-- ════════ 4 · BULLETS ════════ --}}
<section class="slide"><div class="stepnum">4 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Bulletin editor</div>
    <h2>Dash means black bullet.</h2>
    <p class="copy">A plain line prints with a <b>clear circle ○</b>. Start a line with <span class="key">-</span> and it prints a <b>solid black bullet ●</b>. Word-style "o" prefixes are cleaned automatically — paste from anywhere.</p>
  </div>
  <div class="demo"><div class="demo-cap">You type → the paper prints</div>
    <div class="m-notes">Fellowship lunch after service<br>- Bring a dish to share</div>
    <div class="m-arrow">↓</div>
    <div class="m-paper">
      <div class="h">Community Services:</div>
      <div class="o">Fellowship lunch after service</div>
      <div class="f">Bring a dish to share</div>
    </div>
  </div>
</div></section>

{{-- ════════ 5 · DIVIDER ════════ --}}
<section class="slide"><div class="stepnum">5 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Bulletin editor</div>
    <h2>The dashed line is where the paper ends.</h2>
    <p class="copy">Rows <b>above</b> the teal dashed line print on the bulletin. Rows <b>below</b> are web-only — people reach them by scanning the <b>QR code printed on the paper</b>, which opens <b>/announcements</b>. Drag across the line to switch. New announcements are born below it, so the paper never grows by accident.</p>
  </div>
  <div class="demo"><div class="demo-cap">What you'll see</div>
    <div class="m-row"><span class="m-grip">⋮</span><span class="m-field">Weekly Offering:</span></div>
    <div class="m-divider"><span>PRINTED BULLETIN ENDS HERE · QR LEADS PEOPLE TO THE REST</span></div>
    <div class="m-row web"><span class="m-grip">⋮</span><span class="m-field">Youth choir practice moved…</span><span class="m-chip">WEB</span></div>
    <div class="m-row web"><span class="m-grip">⋮</span><span class="m-field">Extra parking on Sabbath…</span><span class="m-chip">WEB</span></div>
  </div>
</div></section>

{{-- ════════ 6 · FOCUS FOLD ════════ --}}
<section class="slide"><div class="stepnum">6 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Bulletin editor</div>
    <h2>The bulletin steps aside while you work.</h2>
    <p class="copy">The moment you tap into announcements, the Order of Service <b>folds itself into one quiet bar</b> so the screen stays calm. Tap the bar to bring it back — it stays open once you do.</p>
  </div>
  <div class="demo"><div class="demo-cap">What you'll see</div>
    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:14px;">
      <span style="font-size:10px; font-weight:700; letter-spacing:.16em; color:var(--ink-soft);">ORDER OF SERVICE</span>
      <span style="font-size:9px; font-weight:700; letter-spacing:.1em; color:#9aa0aa; border:1px solid var(--line); border-radius:6px; padding:4px 9px; background:#fff;">SHOW — HIDDEN WHILE YOU WORK</span>
    </div>
    <div style="font-size:10px; font-weight:700; letter-spacing:.16em; color:var(--ink-soft); margin-bottom:8px;">ANNOUNCEMENTS</div>
    <div class="m-row"><span class="m-grip">⋮</span><span class="m-field">Mission Statement:</span></div>
    <div class="m-row"><span class="m-grip">⋮</span><span class="m-field">Upcoming Events:</span></div>
  </div>
</div></section>

{{-- ════════ 7 · 2-UP ════════ --}}
<section class="slide"><div class="stepnum">7 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Bulletin editor</div>
    <h2>2-UP: one sheet, two bulletins.</h2>
    <p class="copy"><b>PDF ↓</b> is the classic portrait bulletin. <b>2-UP ↓</b> prints the bulletin <b>twice on one landscape sheet</b> — print two-sided, cut down the middle, and every sheet makes two. Half the paper, same bulletin.</p>
  </div>
  <div class="demo"><div class="demo-cap">The 2-up sheet</div>
    <div class="m-sheet">
      <span class="m-scissors">✂</span>
      <div class="m-half"><div class="m-lines"><div class="t"></div><div></div><div></div><div style="width:80%"></div><div></div><div style="width:65%"></div></div></div>
      <div class="m-half"><div class="m-lines"><div class="t"></div><div></div><div></div><div style="width:80%"></div><div></div><div style="width:65%"></div></div></div>
    </div>
  </div>
</div></section>

{{-- ════════ 8 · SERIES ════════ --}}
<section class="slide"><div class="stepnum">8 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Events · bulletin page</div>
    <h2>Describe a series once — the calendar unrolls it.</h2>
    <p class="copy">Open an event's ✏️ pencil and find <b>Repeats</b>: an end date and a time box for each weekday. Fill it once — every night appears on the calendar by itself. Blank box = no service that day.</p>
  </div>
  <div class="demo"><div class="demo-cap">The Crusade, described once</div>
    <div class="m-days">
      <div class="m-day"><span>SUN</span><div class="on">7:30 pm</div></div>
      <div class="m-day"><span>MON</span><div class="off">—</div></div>
      <div class="m-day"><span>TUE</span><div class="on">7:30 pm</div></div>
      <div class="m-day"><span>WED</span><div class="on">7:30 pm</div></div>
      <div class="m-day"><span>THU</span><div class="off">—</div></div>
      <div class="m-day"><span>FRI</span><div class="on">7:30 pm</div></div>
      <div class="m-day"><span>SAT</span><div class="on">9:30a 6p</div></div>
    </div>
    <div style="font-size:11px; color:var(--ink-soft); margin-top:10px;">Repeats until <b style="color:var(--ink)">July 25</b> → 16 nights on the calendar, zero re-typing.</div>
  </div>
</div></section>

{{-- ════════ 9 · SMART FILL ════════ --}}
<section class="slide"><div class="stepnum">9 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Events · bulletin page</div>
    <h2>✨ Smart fill reads your Notes.</h2>
    <p class="copy">Even easier: paste the wording you already have into <b>Notes</b> and tap <b>✨ Smart fill</b>. The grid fills itself. <b>Always look it over before saving</b> — it drafts, you decide.</p>
  </div>
  <div class="demo"><div class="demo-cap">Paste → tap → filled</div>
    <div class="m-notes">"7:30pm nightly except Mondays &amp; Thursdays; Saturdays 9:30am &amp; 6pm, through July 25"</div>
    <div style="text-align:center"><span class="m-smart">✨ Smart fill from Notes</span></div>
    <div class="m-days">
      <div class="m-day"><span>SUN</span><div class="on">7:30 pm</div></div>
      <div class="m-day"><span>MON</span><div class="off">—</div></div>
      <div class="m-day"><span>TUE</span><div class="on">7:30 pm</div></div>
      <div class="m-day"><span>WED</span><div class="on">7:30 pm</div></div>
      <div class="m-day"><span>THU</span><div class="off">—</div></div>
      <div class="m-day"><span>FRI</span><div class="on">7:30 pm</div></div>
      <div class="m-day"><span>SAT</span><div class="on">9:30a 6p</div></div>
    </div>
  </div>
</div></section>

{{-- ════════ 10 · TUNE IN ════════ --}}
<section class="slide"><div class="stepnum">10 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Events · bulletin page</div>
    <h2>The Watch link lights a "tune in" button.</h2>
    <p class="copy">Paste a YouTube link into <b>Watch link</b> and whenever the event is live (from 30 minutes before start), the <b>homepage grows a gold button</b> sending people straight there — made for events streamed on someone else's channel.</p>
  </div>
  <div class="demo"><div class="demo-cap">On the homepage, while it's live</div>
    <div style="text-align:center; padding: 18px 0;">
      <span class="m-gold"><i></i>Crusade: Hope for the World · Tune in</span>
    </div>
  </div>
</div></section>

{{-- ════════ 11 · FILTERS ════════ --}}
<section class="slide"><div class="stepnum">11 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Calendar</div>
    <h2>The legend chips are filters.</h2>
    <p class="copy">Tap <b>Services</b>, <b>Sermons</b>, or <b>Events</b> to show or hide each. Off-chips dim to dashed. Sermons-only turns the calendar into a <b>preaching history</b> — Pastor's birds-eye view in one tap.</p>
  </div>
  <div class="demo"><div class="demo-cap">Sermons only</div>
    <div class="m-chips">
      <span class="m-lg off"><i style="background:var(--teal)"></i>Services</span>
      <span class="m-lg"><i style="background:var(--brass,#b08d3c)"></i>Sermons</span>
      <span class="m-lg off"><i style="background:#2d8659"></i>Events</span>
    </div>
    <div style="margin-top:14px; font-size:12px; color:var(--ink-soft); line-height:1.8;">
      <div>Jul 4 &nbsp;<span style="color:var(--brass,#b08d3c)">●</span> Pastor Kevin Brown preached</div>
      <div>Jun 27 <span style="color:var(--brass,#b08d3c)">●</span> Collis Glasgow preached</div>
      <div>Jun 20 <span style="color:var(--brass,#b08d3c)">●</span> Dennis Williams preached</div>
    </div>
  </div>
</div></section>

{{-- ════════ 12 · SHARE URL ════════ --}}
<section class="slide"><div class="stepnum">12 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Calendar</div>
    <h2>Every view is a link you can text.</h2>
    <p class="copy">The address bar updates as you move. <b>Copy it, text it</b> — whoever opens it lands on the exact week, day, and filters you were looking at.</p>
  </div>
  <div class="demo"><div class="demo-cap">Straight from the address bar</div>
    <div class="m-url">thechurchofpeace.org/calendar?<b>v=week</b>&amp;<b>d=2026-07-04</b>&amp;<b>f=sermon</b></div>
    <div style="font-size:11.5px; color:var(--ink-soft); margin-top:10px;">…opens Week view, July 4, sermons only. Same picture, any phone.</div>
  </div>
</div></section>

{{-- ════════ 13 · EDIT MODE ════════ --}}
<section class="slide"><div class="stepnum">13 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">Calendar</div>
    <h2>Edit mode: tap Edit, then tap a day.</h2>
    <p class="copy">The <b>Edit</b> button beside Today (only signed-in clerks see it) makes the day panel touchable — fix a time, correct who preached, add an event to that day. <b>Saves write back to the bulletin itself</b>: one edit, updated everywhere. Series times stay locked here — change those on the event card.</p>
  </div>
  <div class="demo"><div class="demo-cap">The day panel, editing</div>
    <div class="m-card">
      <div class="t">Sermon · editing</div>
      <div class="m-input">Pastor Kevin Brown</div>
      <span class="m-save">SAVE</span>
      <div class="m-note">Writes to the bulletin's Sermon line.</div>
    </div>
  </div>
</div></section>

{{-- ════════ 14 · THE LOOP ════════ --}}
<section class="slide"><div class="stepnum">14 · 14</div><div class="slide-inner">
  <div>
    <div class="zone-tag">The big picture</div>
    <h2>Typed once. True everywhere.</h2>
    <p class="copy">You enter the bulletin <b>one time</b>. The web page renders it, the PDFs print it, the QR carries the overflow to /announcements, and the calendar reads the date and Sermon line to know what happened when. <b>Nothing is entered twice — so nothing can disagree.</b></p>
  </div>
  <div class="demo"><div class="demo-cap">One source, four faces</div>
    <div class="m-loop">
      <div class="m-node src">THE BULLETIN — typed once</div>
      <div class="m-down">↓ &nbsp; ↓ &nbsp; ↓ &nbsp; ↓</div>
      <div class="m-fan">
        <div class="m-node">Web bulletin</div>
        <div class="m-node">PDF &amp; 2-UP + QR</div>
        <div class="m-node">/announcements</div>
        <div class="m-node">Calendar</div>
      </div>
    </div>
  </div>
</div></section>

{{-- ════════ FIN ════════ --}}
<section class="slide"><div class="fin">
  <h2>That's every secret.</h2>
  <p>Forget one? This page lives in the Admin menu — <b>Field Guide</b>.</p>
  <p style="font-family:'Cormorant Garamond',serif; font-size:22px; margin-top:26px;">Now go make Sabbath easy <b>:)</b></p>
</div></section>

</body>
</html>
