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
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Instrument Sans', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
  main { max-width: 760px; margin: 0 auto; padding: clamp(48px,8vh,80px) clamp(18px,5vw,28px) 120px; }

  .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px,8vw,54px); font-weight: 500; letter-spacing: -0.02em; text-align: center; margin-top: 12px; line-height: 1.05; }
  .lede { text-align: center; font-size: 15px; color: var(--ink-soft); margin: 16px auto 0; max-width: 480px; line-height: 1.6; }

  .zone { margin-top: clamp(44px,7vh,64px); }
  .zone-head { display: flex; align-items: baseline; gap: 12px; margin-bottom: 6px; }
  .zone-head h2 { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 500; letter-spacing: -0.01em; }
  .zone-head .where { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-faint); }
  .zone-head a.where { color: var(--teal); text-decoration: none; border-bottom: 1px solid color-mix(in srgb, var(--teal) 35%, transparent); }
  .zone > p { font-size: 14px; color: var(--ink-soft); margin-bottom: 18px; line-height: 1.6; }

  .trick { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px 22px; margin-top: 14px; box-shadow: 0 1px 3px rgba(26,35,50,.04); }
  .trick h3 { font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 9px; }
  .trick h3 .n { flex-shrink: 0; width: 24px; height: 24px; border-radius: 999px; background: color-mix(in srgb, var(--teal) 10%, #fff); border: 1px solid color-mix(in srgb, var(--teal) 35%, transparent); color: var(--teal); font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
  .trick p { font-size: 14.5px; line-height: 1.65; color: var(--ink); margin-top: 8px; }
  .trick .try { margin-top: 10px; font-size: 12px; font-weight: 600; color: var(--teal); background: color-mix(in srgb, var(--teal) 6%, #fff); border-left: 3px solid var(--teal); border-radius: 0 8px 8px 0; padding: 9px 13px; line-height: 1.55; }
  .trick .try b { letter-spacing: 0.1em; font-size: 10px; text-transform: uppercase; display: block; margin-bottom: 2px; }
  kbd, .key { font: 600 12px 'Instrument Sans'; background: var(--parchment); border: 1px solid var(--line); border-bottom-width: 2px; border-radius: 5px; padding: 2px 7px; white-space: nowrap; }
  .dot-demo { display: inline-block; width: 8px; height: 8px; border-radius: 999px; vertical-align: middle; margin: 0 2px; }

  .close { text-align: center; margin-top: 70px; font-family: 'Cormorant Garamond', serif; font-size: 21px; color: var(--ink-soft); }
  .close b { color: var(--teal); font-weight: 500; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<main>
  <div class="eyebrow">For Rosharde &amp; Andre</div>
  <h1>The Field Guide.</h1>
  <p class="lede">Everything on this site is connected — the bulletin feeds the calendar, the paper carries a QR to the web, and one edit updates every page. These are the tricks that don't announce themselves.</p>

  {{-- ═══════════ BULLETIN EDITOR ═══════════ --}}
  <section class="zone">
    <div class="zone-head"><h2>The bulletin editor.</h2><a class="where" href="{{ route('admin.bulletin') }}">open it @include('partials._ar')</a></div>
    <p>Everything autosaves as you type — the little <b>"Saved"</b> blip at the bottom confirms it. If it ever turns red saying "Not saved," it's telling the truth: try again.</p>

    <div class="trick"><h3><span class="n">1</span> Drag the ⋮ dots to reorder</h3>
      <p>Every announcement has three dots on its far left. Grab and drag — works with a finger on your phone too. The ↑ ↓ arrows are still there when you want precision.</p></div>

    <div class="trick"><h3><span class="n">2</span> A blank title makes bullets for the section above</h3>
      <p>Want "Upcoming Events:" with several items under it? Add an announcement, leave its <b>title empty</b>, write the item in the detail. It tucks itself under the announcement above — the editor shows it indented with a ↳.</p>
      <div class="try"><b>Try it</b>Add announcement → skip the title → type "12/6 – Retro Night, 4pm" → look at the PDF.</div></div>

    <div class="trick"><h3><span class="n">3</span> Enter makes a new line — and every line becomes its own bullet</h3>
      <p>Tap into any detail box and it opens into a big writing space. Press <span class="key">Enter</span> for a new line; on the printed bulletin and the web, each line prints as its own bullet.</p></div>

    <div class="trick"><h3><span class="n">4</span> Start a line with a dash for a black bullet</h3>
      <p>A plain line prints with a clear circle ○. Start the line with <span class="key">-</span> and it prints a solid black bullet ●. (Old Word-style "o" prefixes are cleaned up automatically — paste freely.)</p></div>

    <div class="trick"><h3><span class="n">5</span> The dashed teal line is where the paper ends</h3>
      <p>Announcements <b>above</b> the line print on the bulletin. Announcements <b>below</b> it are web-only — they live at <b>/announcements</b>, which people reach by scanning the QR code printed on the paper. Drag a row across the line to switch it. New announcements are born below the line, so the paper never grows by accident.</p>
      <div class="try"><b>Why it matters</b>The paper stays one sheet no matter how much is happening — add 10 web announcements and the print doesn't move.</div></div>

    <div class="trick"><h3><span class="n">6</span> The bulletin folds away while you work on announcements</h3>
      <p>Tap into any announcement and the Order of Service collapses to one quiet bar so the screen stays calm. Tap the bar to bring it back — it stays open once you do.</p></div>

    <div class="trick"><h3><span class="n">7</span> Two PDFs: regular and 2-UP</h3>
      <p><b>PDF ↓</b> is the classic portrait bulletin. <b>2-UP ↓</b> puts the bulletin twice on one landscape sheet — print it two-sided, cut down the middle, and one sheet makes two bulletins. Half the paper.</p></div>
  </section>

  {{-- ═══════════ EVENTS ═══════════ --}}
  <section class="zone">
    <div class="zone-head"><h2>Events.</h2><a class="where" href="{{ url('/welcome') }}#events">on the bulletin page @include('partials._ar')</a></div>

    <div class="trick"><h3><span class="n">1</span> A series is described once — the calendar unrolls it</h3>
      <p>For something like the Crusade (nightly except Mondays &amp; Thursdays), open the event's pencil and use the <b>Repeats</b> section: an end date plus a time box for each day of the week. Fill it once and every single night appears on the calendar by itself.</p></div>

    <div class="trick"><h3><span class="n">2</span> ✨ Smart fill reads your Notes</h3>
      <p>Even easier: paste the full wording into <b>Notes</b> — "7:30pm nightly except Mondays &amp; Thursdays; Saturdays 9:30am &amp; 6pm, through July 25" — and tap <b>✨ Smart fill from Notes</b>. The schedule grid fills itself. <b>Always look it over before saving</b> — it drafts, you decide.</p></div>

    <div class="trick"><h3><span class="n">3</span> The Watch link turns on a "tune in" button</h3>
      <p>Paste a YouTube link into <b>Watch link</b> and whenever that event is live (from 30 minutes before start), the homepage grows a gold <b>tune in</b> button pointing there — perfect for events streamed on someone else's channel.</p></div>
  </section>

  {{-- ═══════════ CALENDAR ═══════════ --}}
  <section class="zone">
    <div class="zone-head"><h2>The calendar.</h2><a class="where" href="{{ route('calendar') }}">open it @include('partials._ar')</a></div>
    <p>It doesn't have its own data — it <i>reflects</i> the bulletin, the sermon archive, and events. Fix something at the source (or right on the calendar) and every view agrees.</p>

    <div class="trick"><h3><span class="n">1</span> The legend chips are filters</h3>
      <p>Tap <span class="dot-demo" style="background:var(--teal)"></span> Services, <span class="dot-demo" style="background:var(--brass,#b08d3c)"></span> Sermons, or <span class="dot-demo" style="background:#2d8659"></span> Events to show or hide each. Sermons-only turns the calendar into a preaching history for Pastor.</p></div>

    <div class="trick"><h3><span class="n">2</span> Every view is a link you can text</h3>
      <p>The web address updates as you navigate — <b>?v=week&amp;d=2026-07-04</b> — so copy it from the address bar and text it. Whoever opens it lands on exactly what you're seeing, filters included.</p></div>

    <div class="trick"><h3><span class="n">3</span> Edit mode — tap Edit, then tap a day</h3>
      <p>The <b>Edit</b> button beside Today (only you see it) makes the day panel touchable: fix the service time, correct who preached, adjust an event, or add one to that day. Saves write back to the bulletin itself — <b>one edit, updated everywhere</b>.</p>
      <div class="try"><b>Note</b>A series' times show as locked in the day panel — change those on the event's card on the bulletin page.</div></div>

    <div class="trick"><h3><span class="n">4</span> Keyboard: <span class="key">←</span> <span class="key">→</span> move, <span class="key">T</span> jumps to today</h3>
      <p>On a computer, arrow keys page through days, weeks, or months. <span class="key">T</span> snaps home.</p></div>
  </section>

  {{-- ═══════════ THE LOOP ═══════════ --}}
  <section class="zone">
    <div class="zone-head"><h2>How it all connects.</h2></div>
    <div class="trick">
      <p style="margin-top:0">You type the bulletin once. From that single source: the <b>web bulletin</b> renders it, the <b>PDFs</b> print it, the <b>QR code</b> carries people to <b>/announcements</b> for everything that didn't fit on paper, and the <b>calendar</b> reads the service date and the Sermon line to know what happened when. Nothing is entered twice — so nothing can disagree.</p>
    </div>
  </section>

  <p class="close">Now go make Sabbath easy. <b>:)</b></p>
</main>
</body>
</html>
