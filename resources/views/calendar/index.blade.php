<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.seo-head', [
  'title'       => 'Calendar — The Church of Peace',
  'description' => "What's happening at Shalom — services, sermons, and events, all in one place.",
  'path'        => '/calendar',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Instrument Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 4px; }
  a { color: inherit; text-decoration: none; }
  button { font: inherit; cursor: pointer; }

  main { max-width: 1220px; margin: 0 auto; padding: clamp(22px,4vh,38px) clamp(14px,4vw,40px) 80px; }

  .cal-bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
  .cal-nav { display: flex; align-items: center; gap: 8px; }
  .rnd { width: 38px; height: 38px; border-radius: 9px; border: 1px solid var(--line); background: #fff; color: var(--ink-soft); font-size: 17px; display: inline-flex; align-items: center; justify-content: center; }
  .rnd:hover { border-color: var(--teal); color: var(--teal); }
  .today-btn { font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; border: 1px solid var(--line); background: #fff; color: var(--teal); border-radius: 9px; padding: 9px 15px; }
  .today-btn:hover { border-color: var(--teal); }
  .cal-title { font-size: clamp(24px,4vw,34px); font-weight: 600; letter-spacing: -0.02em; text-align: center; flex: 1; min-width: 200px; }
  .cal-title .dim { color: var(--ink-faint); }
  .seg { display: inline-flex; background: #fff; border: 1px solid var(--line); border-radius: 9px; padding: 3px; gap: 2px; }
  .seg button { font-size: 12px; font-weight: 600; border: 0; background: transparent; border-radius: 6px; padding: 8px 14px; color: var(--ink-soft); }
  .seg button.on { background: var(--teal); color: #fff; }
  .seg button.soon { color: var(--ink-faint); cursor: default; }

  .legend { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
  .lg { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-soft); border: 1px solid var(--line); background: #fff; border-radius: 8px; padding: 8px 13px; display: inline-flex; align-items: center; gap: 7px; }
  .dot { width: 8px; height: 8px; border-radius: 999px; flex-shrink: 0; }
  .dot.service { background: var(--teal); } .dot.sermon { background: var(--brass); } .dot.event { background: #2d8659; }

  /* ── MONTH ── */
  .gridwrap { background: #fff; border: 1px solid var(--line); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(26,35,50,.04); }
  .dow { display: grid; grid-template-columns: repeat(7,1fr); background: color-mix(in srgb, var(--cream) 55%, #fff); border-bottom: 1px solid var(--line); }
  .dow div { padding: 12px 0; text-align: center; font-size: 11px; font-weight: 700; letter-spacing: 0.16em; color: var(--ink-soft); }
  .mgrid { display: grid; grid-template-columns: repeat(7,1fr); }
  .cell { border-right: 1px solid var(--line); border-bottom: 1px solid var(--line); padding: 8px 8px 7px; min-height: 108px; min-width: 0; cursor: pointer; transition: background .12s; }
  .cell:nth-child(7n) { border-right: 0; }
  .cell:hover { background: color-mix(in srgb, var(--teal) 4%, #fff); }
  .cell.out { background: color-mix(in srgb, var(--cream) 20%, #fff); }
  .cell.out:hover { background: color-mix(in srgb, var(--cream) 40%, #fff); }
  .dn { font-size: 14px; font-weight: 600; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; }
  .cell.out .dn { color: var(--ink-faint); font-weight: 500; }
  .dn.today { background: var(--teal); color: #fff; }
  .ev { display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 500; line-height: 1.25; padding: 3px 7px; border-radius: 6px; margin-top: 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .ev .dot { width: 6px; height: 6px; }
  .ev.service { background: var(--teal-light, #e6f0f3); color: var(--teal-dark); }
  .ev.sermon  { background: #f5ecd6; color: #7a5f22; }
  .ev.event   { background: #e3f0e8; color: #1f6843; }
  .more { font-size: 11px; color: var(--ink-faint); font-weight: 600; margin-top: 4px; padding-left: 7px; }

  /* ── WEEK ── */
  .week { display: grid; grid-template-columns: repeat(7,1fr); gap: 10px; }
  .wday { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 12px 11px; min-height: 340px; cursor: pointer; transition: border-color .12s; }
  .wday:hover { border-color: var(--teal); }
  .wday.wtoday { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 10%, transparent); }
  .whead { display: flex; align-items: baseline; gap: 8px; padding-bottom: 9px; border-bottom: 1px solid var(--line); margin-bottom: 9px; }
  .wdow { font-size: 10px; font-weight: 700; letter-spacing: 0.14em; color: var(--ink-soft); }
  .wdom { font-size: 21px; font-weight: 600; }
  .wtoday .wdom { color: var(--teal); }
  .wev { border-left: 3px solid; border-radius: 4px; padding: 6px 8px; margin-bottom: 7px; font-size: 12px; line-height: 1.35; }
  .wev.service { border-color: var(--teal); background: var(--teal-light, #e6f0f3); color: var(--teal-dark); }
  .wev.sermon  { border-color: var(--brass); background: #f5ecd6; color: #7a5f22; }
  .wev.event   { border-color: #2d8659; background: #e3f0e8; color: #1f6843; }
  .wev .t { font-weight: 600; } .wev .m { font-size: 11px; opacity: .8; margin-top: 1px; }

  /* ── DAY ── */
  .dayview { max-width: 640px; margin: 0 auto; }
  .dv-head { text-align: center; margin-bottom: 20px; }
  .dv-dow { font-size: 12px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-soft); }
  .dv-date { font-size: 30px; font-weight: 600; letter-spacing: -0.02em; margin-top: 4px; }
  .dv-card { background: #fff; border: 1px solid var(--line); border-left: 4px solid; border-radius: 12px; padding: 16px 18px; margin-bottom: 12px; display: block; }
  .dv-card.service { border-left-color: var(--teal); }
  .dv-card.sermon  { border-left-color: var(--brass); }
  .dv-card.event   { border-left-color: #2d8659; }
  .dv-type { font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; }
  .dv-card.service .dv-type { color: var(--teal); } .dv-card.sermon .dv-type { color: var(--brass); } .dv-card.event .dv-type { color: #1f6843; }
  .dv-name { font-size: 17px; font-weight: 600; margin-top: 4px; }
  .dv-meta { font-size: 13px; color: var(--ink-soft); margin-top: 3px; }
  .dv-empty { text-align: center; color: var(--ink-soft); padding: 44px 0; background: #fff; border: 1px dashed var(--line); border-radius: 12px; }

  /* ── Day sheet (tap a day in month/week) ── */
  .sheet-ov { position: fixed; inset: 0; background: rgba(20,25,35,.44); display: none; align-items: flex-end; justify-content: center; z-index: 130; }
  .sheet-ov.open { display: flex; }
  .sheet { background: var(--parchment); width: 100%; max-width: 560px; max-height: 78vh; overflow-y: auto; border-radius: 16px 16px 0 0; padding: 20px 22px calc(24px + env(safe-area-inset-bottom)); box-shadow: 0 -18px 60px -20px rgba(0,0,0,.4); }
  @media (min-width: 720px) { .sheet-ov { align-items: center; } .sheet { border-radius: 16px; } }
  .sheet-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
  .sheet-title { font-size: 18px; font-weight: 600; }
  .sheet-x { width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--line); background: #fff; color: var(--ink-soft); }

  @media (max-width: 760px) {
    .dow div { font-size: 9px; letter-spacing: 0.05em; }
    .cell { min-height: 74px; padding: 5px 4px; }
    .dn { font-size: 12px; width: 22px; height: 22px; }
    .ev { font-size: 0; padding: 0; margin-top: 4px; gap: 3px; background: transparent !important; display: inline-flex; }
    .ev .dot { width: 7px; height: 7px; display: inline-block; }
    .week { grid-template-columns: 1fr; }
    .wday { min-height: 0; }
    .cal-title { order: -1; width: 100%; flex-basis: 100%; }
  }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<main>
  <div class="cal-bar">
    <div class="cal-nav">
      <button class="rnd" id="prev" aria-label="Previous">‹</button>
      <button class="rnd" id="next" aria-label="Next">›</button>
      <button class="today-btn" id="today">Today</button>
    </div>
    <div class="cal-title" id="title"></div>
    <div class="seg" role="tablist" aria-label="Calendar view">
      <button data-view="day">Day</button>
      <button data-view="week">Week</button>
      <button data-view="month" class="on">Month</button>
      <button class="soon" title="Coming soon">Year</button>
    </div>
  </div>

  <div class="legend">
    <span class="lg"><span class="dot service"></span> Services</span>
    <span class="lg"><span class="dot sermon"></span> Sermons</span>
    <span class="lg"><span class="dot event"></span> Events</span>
  </div>

  <div id="stage"></div>
</main>

<div class="sheet-ov" id="sheetOv">
  <div class="sheet">
    <div class="sheet-head">
      <div class="sheet-title" id="sheetTitle"></div>
      <button class="sheet-x" id="sheetX" aria-label="Close">✕</button>
    </div>
    <div id="sheetBody"></div>
  </div>
</div>

<script id="cal-data" type="application/json">{!! json_encode($payload, JSON_UNESCAPED_SLASHES) !!}</script>
<script>
(function () {
  const DATA = JSON.parse(document.getElementById('cal-data').textContent);
  const ENTRIES = DATA.entries || {};
  const TODAY = DATA.today;
  const esc = s => (s == null ? '' : String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])));
  const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const DOWS = ['SUN','MON','TUE','WED','THU','FRI','SAT'];
  const TYPE_LABEL = { service: 'Service', sermon: 'Sermon', event: 'Event' };

  // state — like genesis: everything local, views are pure renders.
  // Deep-linkable: /calendar?v=week&d=2026-07-04 opens that exact view (share/copy-link ready).
  const q = new URLSearchParams(location.search);
  let view = ['day','week','month'].includes(q.get('v')) ? q.get('v') : 'month';
  let anchor = /^\d{4}-\d{2}-\d{2}$/.test(q.get('d') || '') ? q.get('d') : TODAY;

  const stage = document.getElementById('stage');
  const title = document.getElementById('title');

  const d2 = n => String(n).padStart(2, '0');
  const iso = d => d.getFullYear() + '-' + d2(d.getMonth() + 1) + '-' + d2(d.getDate());
  const parse = s => new Date(s + 'T12:00:00');
  const addDays = (s, n) => { const d = parse(s); d.setDate(d.getDate() + n); return iso(d); };
  const entriesOf = s => ENTRIES[s] || [];

  function render() {
    history.replaceState(null, '', '?v=' + view + '&d=' + anchor);
    document.querySelectorAll('.seg [data-view]').forEach(b => b.classList.toggle('on', b.dataset.view === view));
    if (view === 'month') renderMonth();
    else if (view === 'week') renderWeek();
    else renderDay();
  }

  // ── MONTH ──
  function renderMonth() {
    const a = parse(anchor);
    const y = a.getFullYear(), m = a.getMonth();
    title.innerHTML = MONTHS[m] + ' <span class="dim">' + y + '</span>';
    const first = new Date(y, m, 1);
    let cur = new Date(first); cur.setDate(1 - first.getDay());
    let html = '<div class="gridwrap"><div class="dow">' + DOWS.map(d => '<div>' + d + '</div>').join('') + '</div><div class="mgrid">';
    for (let i = 0; i < 42; i++) {
      const ds = iso(cur);
      const inM = cur.getMonth() === m;
      const es = entriesOf(ds);
      html += '<div class="cell ' + (inM ? '' : 'out') + '" data-day="' + ds + '">'
            + '<span class="dn ' + (ds === TODAY ? 'today' : '') + '">' + cur.getDate() + '</span>';
      es.slice(0, 3).forEach(e => {
        html += '<div class="ev ' + e.t + '" title="' + esc(e.n) + '"><span class="dot ' + e.t + '"></span>' + esc(e.n) + '</div>';
      });
      if (es.length > 3) html += '<div class="more">+' + (es.length - 3) + ' more</div>';
      html += '</div>';
      cur.setDate(cur.getDate() + 1);
    }
    stage.innerHTML = html + '</div></div>';
  }

  // ── WEEK ──
  function renderWeek() {
    const a = parse(anchor);
    const start = addDays(anchor, -a.getDay());
    const end = addDays(start, 6);
    const s = parse(start), e = parse(end);
    title.innerHTML = MONTHS[s.getMonth()].slice(0,3) + ' ' + s.getDate() + ' – '
      + (s.getMonth() === e.getMonth() ? '' : MONTHS[e.getMonth()].slice(0,3) + ' ') + e.getDate()
      + ' <span class="dim">' + e.getFullYear() + '</span>';
    let html = '<div class="week">';
    for (let i = 0; i < 7; i++) {
      const ds = addDays(start, i);
      const d = parse(ds);
      const es = entriesOf(ds);
      html += '<div class="wday ' + (ds === TODAY ? 'wtoday' : '') + '" data-day="' + ds + '">'
            + '<div class="whead"><span class="wdow">' + DOWS[i] + '</span><span class="wdom">' + d.getDate() + '</span></div>';
      es.forEach(ev => {
        html += '<div class="wev ' + ev.t + '"><div class="t">' + esc(ev.n) + '</div>'
              + (ev.time || ev.loc ? '<div class="m">' + esc([ev.time, ev.loc].filter(Boolean).join(' · ')) + '</div>' : '')
              + '</div>';
      });
      html += '</div>';
    }
    stage.innerHTML = html + '</div>';
  }

  // ── DAY ──
  function renderDay() {
    const d = parse(anchor);
    title.innerHTML = MONTHS[d.getMonth()] + ' ' + d.getDate() + ' <span class="dim">' + d.getFullYear() + '</span>';
    const es = entriesOf(anchor);
    let html = '<div class="dayview"><div class="dv-head"><div class="dv-dow">'
      + ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][d.getDay()]
      + (anchor === TODAY ? ' · Today' : '') + '</div>'
      + '<div class="dv-date">' + MONTHS[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear() + '</div></div>';
    if (!es.length) {
      html += '<div class="dv-empty">Nothing on the calendar this day.</div>';
    } else {
      es.forEach(e => { html += dayCard(e); });
    }
    stage.innerHTML = html + '</div>';
  }

  function dayCard(e) {
    const tag = e.url ? 'a' : 'div';
    return '<' + tag + (e.url ? ' href="' + esc(e.url) + '"' : '') + ' class="dv-card ' + e.t + '">'
      + '<div class="dv-type">' + TYPE_LABEL[e.t] + (e.dept ? ' · ' + esc(e.dept) : '') + '</div>'
      + '<div class="dv-name">' + esc(e.n) + '</div>'
      + (e.sub ? '<div class="dv-meta">' + esc(e.sub) + '</div>' : '')
      + ((e.time || e.loc) ? '<div class="dv-meta">' + esc([e.time, e.loc].filter(Boolean).join(' · ')) + '</div>' : '')
      + '</' + tag + '>';
  }

  // ── Day sheet (tap a day in month/week) ──
  const sheetOv = document.getElementById('sheetOv');
  function openSheet(ds) {
    const d = parse(ds);
    document.getElementById('sheetTitle').textContent =
      ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][d.getDay()] + ', ' + MONTHS[d.getMonth()] + ' ' + d.getDate();
    const es = entriesOf(ds);
    document.getElementById('sheetBody').innerHTML = es.length
      ? es.map(dayCard).join('')
      : '<div class="dv-empty">Nothing on the calendar this day.</div>';
    sheetOv.classList.add('open');
  }
  document.getElementById('sheetX').addEventListener('click', () => sheetOv.classList.remove('open'));
  sheetOv.addEventListener('click', e => { if (e.target === sheetOv) sheetOv.classList.remove('open'); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') sheetOv.classList.remove('open'); });

  stage.addEventListener('click', e => {
    const cell = e.target.closest('[data-day]');
    if (cell) openSheet(cell.dataset.day);
  });

  // ── nav ──
  function shift(dir) {
    if (view === 'month') {
      const a = parse(anchor); a.setDate(1); a.setMonth(a.getMonth() + dir); anchor = iso(a);
    } else if (view === 'week') anchor = addDays(anchor, dir * 7);
    else anchor = addDays(anchor, dir);
    render();
  }
  document.getElementById('prev').addEventListener('click', () => shift(-1));
  document.getElementById('next').addEventListener('click', () => shift(1));
  document.getElementById('today').addEventListener('click', () => { anchor = TODAY; render(); });
  document.querySelectorAll('.seg [data-view]').forEach(b =>
    b.addEventListener('click', () => { view = b.dataset.view; render(); }));
  document.addEventListener('keydown', e => {
    if (e.target.matches('input,textarea')) return;
    if (e.key === 'ArrowLeft') shift(-1);
    if (e.key === 'ArrowRight') shift(1);
    if (e.key.toLowerCase() === 't') { anchor = TODAY; render(); }
  });

  render();
})();
</script>
</body>
</html>
