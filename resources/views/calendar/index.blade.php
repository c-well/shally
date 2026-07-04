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
  .lg.off { opacity: .42; border-style: dashed; }
  .lg.off .dot { background: var(--ink-faint, #9aa0aa) !important; }
  .lg:hover { border-color: var(--teal); }
  button.lg { cursor: pointer; font-family: inherit; }
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
  .dn.today, .dn.sel { background: var(--teal); color: #fff; }
  .cell, .wband, .tg-col, .adcell, .seg button, .navbtn { -webkit-tap-highlight-color: transparent; }
  .ev { display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 500; line-height: 1.25; padding: 3px 7px; border-radius: 6px; margin-top: 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .ev .dot { width: 6px; height: 6px; }
  .ev.service { background: var(--teal-light, #e6f0f3); color: var(--teal-dark); }
  .ev.sermon  { background: #f5ecd6; color: #7a5f22; }
  .ev.event   { background: #e3f0e8; color: #1f6843; }
  .more { font-size: 11px; color: var(--ink-faint); font-weight: 600; margin-top: 4px; padding-left: 7px; }

  /* ── WEEK (desktop): clean iCal-style time grid — day columns, trimmed hour axis,
        all-day shelf on top. Mobile keeps the agenda bands below. ── */
  .tgrid { background: #fff; border: 1px solid var(--line); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(26,35,50,.04); }
  .tg-head { display: grid; grid-template-columns: 64px repeat(7,1fr); border-bottom: 1px solid var(--line); background: color-mix(in srgb, var(--cream) 45%, #fff); }
  .tg-head .th { padding: 11px 0 9px; text-align: center; }
  .tg-head .th .d { font-size: 10px; font-weight: 700; letter-spacing: 0.14em; color: var(--ink-soft); }
  .tg-head .th .n { font-size: 19px; font-weight: 600; line-height: 1.15; }
  .tg-head .th.today .n { color: var(--teal); }
  .tg-allday { display: grid; grid-template-columns: 64px repeat(7,1fr); border-bottom: 1px solid var(--line); min-height: 40px; }
  .tg-allday .lbl { font-size: 9px; font-weight: 700; letter-spacing: 0.12em; color: var(--ink-faint); text-transform: uppercase; display: flex; align-items: center; justify-content: flex-end; padding-right: 8px; }
  .tg-allday .adcell { border-left: 1px solid var(--line); padding: 5px 6px; cursor: pointer; min-width: 0; }
  .adchip { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--ink); padding: 3px 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .adchip .wdot { width: 7px; height: 7px; border-radius: 999px; flex-shrink: 0; }
  .tg-body { display: grid; grid-template-columns: 64px repeat(7,1fr); position: relative; }
  .tg-rail { position: relative; }
  .tg-rail .hr { height: var(--hh); position: relative; }
  .tg-rail .hr span { position: absolute; top: -7px; right: 8px; font-size: 10px; font-weight: 600; letter-spacing: 0.04em; color: var(--ink-faint); }
  .tg-col { border-left: 1px solid var(--line); position: relative; cursor: pointer; }
  .tg-col.today { background: color-mix(in srgb, var(--teal) 3%, #fff); }
  .tg-col .hline { height: var(--hh); border-bottom: 1px solid color-mix(in srgb, var(--ink) 5%, transparent); }
  .tg-col .hline:last-child { border-bottom: 0; }
  .tblock { position: absolute; left: 5px; right: 5px; border-radius: 7px; padding: 6px 9px 6px 11px; overflow: hidden; font-size: 12.5px; font-weight: 600; line-height: 1.3; color: var(--ink); }
  .tblock::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; border-radius: 3px 0 0 3px; }
  .tblock .tt { font-size: 10.5px; font-weight: 500; color: var(--ink-soft); margin-top: 1px; }
  .tblock.service { background: color-mix(in srgb, var(--teal) 8%, #fff); } .tblock.service::before { background: var(--teal); }
  .tblock.sermon  { background: color-mix(in srgb, var(--brass) 10%, #fff); } .tblock.sermon::before { background: var(--brass); }
  .tblock.event   { background: color-mix(in srgb, #2d8659 9%, #fff); } .tblock.event::before { background: #2d8659; }
  .weekwrap-mobile { display: none; }
  @media (max-width: 760px) { .tgrid { display: none; } .weekwrap-mobile { display: block; } }

  /* ── WEEK (mobile agenda bands) ── */
  .week { display: flex; flex-direction: column; gap: 10px; }
  .wband { display: flex; gap: 16px; background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; cursor: pointer; transition: border-color .12s; align-items: center; }
  .wband:hover { border-color: var(--teal); }
  .wband.wtoday { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 10%, transparent); }
  .wband.wempty { padding: 9px 16px; align-items: center; }
  .wrail { flex: 0 0 74px; display: flex; align-items: baseline; gap: 7px; }
  .wdow { font-size: 10px; font-weight: 700; letter-spacing: 0.14em; color: var(--ink-soft); }
  .wdom { font-size: 22px; font-weight: 600; line-height: 1; }
  .wtoday .wdom { color: var(--teal); }
  .wcards { flex: 1; display: flex; flex-wrap: wrap; gap: 8px; min-width: 0; }
  /* Entries are TYPE, not boxes: title only, 16px/600, a small color dot carries the
     category. Time/location live in the tap-open day sheet — the band stays calm. */
  .wev { display: inline-flex; align-items: center; gap: 9px; font-size: 16px; font-weight: 600; letter-spacing: -0.01em; color: var(--ink); line-height: 1.3; padding: 3px 0; margin-right: 26px; max-width: 100%; }
  .wev .wdot { width: 8px; height: 8px; border-radius: 999px; flex-shrink: 0; }
  .wev.service .wdot { background: var(--teal); }
  .wev.sermon  .wdot { background: var(--brass); }
  .wev.event   .wdot { background: #2d8659; }
  .wev.sermon { color: var(--ink-soft); font-weight: 500; }
  .wnone { font-size: 12px; color: var(--ink-faint); }

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
    .wrail { flex-basis: 56px; }
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

  <div class="legend" id="legend">
    <button class="lg" data-t="service" aria-pressed="true"><span class="dot service"></span> Services</button>
    <button class="lg" data-t="sermon" aria-pressed="true"><span class="dot sermon"></span> Sermons</button>
    <button class="lg" data-t="event" aria-pressed="true"><span class="dot event"></span> Events</button>
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
  const ALL_TYPES = ['service', 'sermon', 'event'];
  const fParam = (q.get('f') || '').split(',').filter(t => ALL_TYPES.includes(t));
  const show = new Set(fParam.length ? fParam : ALL_TYPES);
  const entriesOf = s => (ENTRIES[s] || []).filter(e => show.has(e.t));

  const legend = document.getElementById('legend');
  function paintLegend() {
    legend.querySelectorAll('[data-t]').forEach(b => {
      const on = show.has(b.dataset.t);
      b.classList.toggle('off', !on); b.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }
  legend.addEventListener('click', e => {
    const b = e.target.closest('[data-t]'); if (!b) return;
    const t = b.dataset.t;
    if (show.has(t)) { if (show.size > 1) show.delete(t); }  // never filter down to nothing
    else show.add(t);
    paintLegend(); render();
  });
  paintLegend();

  function render() {
    history.replaceState(null, '', '?v=' + view + '&d=' + anchor + (show.size === ALL_TYPES.length ? '' : '&f=' + ALL_TYPES.filter(t => show.has(t)).join(',')));
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
    const mins = t => {                     // "10:30 am" -> minutes, else null (all-day)
      const m = /^(\d{1,2}):(\d{2})\s*(am|pm)$/i.exec((t || '').trim());
      if (!m) return null;
      let h = parseInt(m[1], 10) % 12; if (/pm/i.test(m[3])) h += 12;
      return h * 60 + parseInt(m[2], 10);
    };
    // hour range: trimmed to church life, expanded by actual data
    let lo = 8 * 60, hi = 21 * 60;
    const days = [];
    for (let i = 0; i < 7; i++) {
      const ds = addDays(start, i);
      const es = entriesOf(ds);
      const timed = [], allday = [];
      es.forEach(ev => { const mm = mins(ev.time); (mm === null ? allday : timed).push({ ...ev, mm }); });
      timed.forEach(ev => { lo = Math.min(lo, Math.floor(ev.mm / 60) * 60); hi = Math.max(hi, ev.mm + 120); });
      days.push({ ds, timed, allday });
    }
    lo = Math.max(6 * 60, lo); hi = Math.min(23 * 60, Math.max(hi, lo + 6 * 60));
    const HH = 52, hours = [];
    for (let m = lo; m < hi; m += 60) hours.push(m);
    const hlabel = m => { const h = Math.floor(m / 60); return h === 12 ? 'Noon' : (h % 12 || 12) + (h < 12 ? ' AM' : ' PM'); };

    let html = '<div class="tgrid" style="--hh:' + HH + 'px">';
    html += '<div class="tg-head"><div></div>' + days.map((d, i) => {
      const dt = parse(d.ds);
      return '<div class="th ' + (d.ds === TODAY ? 'today' : '') + '"><div class="d">' + DOWS[i] + '</div><div class="n">' + dt.getDate() + '</div></div>';
    }).join('') + '</div>';
    html += '<div class="tg-allday"><div class="lbl">all-day</div>' + days.map(d =>
      '<div class="adcell" data-day="' + d.ds + '">' + d.allday.map(ev =>
        '<div class="adchip"><span class="wdot ' + '"></span>'.replace('wdot ', 'wdot" style="background:' + (ev.t === 'service' ? 'var(--teal)' : ev.t === 'sermon' ? 'var(--brass)' : '#2d8659') + ';') + esc(ev.n) + '</div>'
      ).join('') + '</div>').join('') + '</div>';
    html += '<div class="tg-body"><div class="tg-rail">' + hours.map(m => '<div class="hr"><span>' + (m === lo ? '' : hlabel(m)) + '</span></div>').join('') + '</div>';
    days.forEach(d => {
      html += '<div class="tg-col ' + (d.ds === TODAY ? 'today' : '') + '" data-day="' + d.ds + '">'
            + hours.map(() => '<div class="hline"></div>').join('');
      const placed = [];
      d.timed.forEach(ev => {
        const top = (ev.mm - lo) / 60 * HH;
        const height = Math.max(34, 1.5 * HH - 6);
        const clash = placed.some(p => Math.abs(p - top) < height);
        html += '<div class="tblock ' + ev.t + '" style="top:' + (top + 1) + 'px;height:' + height + 'px;'
              + (clash ? 'left:50%;' : '') + '">' + esc(ev.n)
              + '<div class="tt">' + esc(ev.time) + '</div></div>';
        placed.push(top);
      });
      html += '</div>';
    });
    html += '</div></div>';

    // mobile: agenda bands (same data, stacked)
    html += '<div class="weekwrap-mobile"><div class="week">';
    days.forEach((d, i) => {
      const dt = parse(d.ds);
      const es = entriesOf(d.ds);
      html += '<div class="wband ' + (d.ds === TODAY ? 'wtoday' : '') + (es.length ? '' : ' wempty') + '" data-day="' + d.ds + '">'
            + '<div class="wrail"><span class="wdow">' + DOWS[i] + '</span><span class="wdom">' + dt.getDate() + '</span></div>'
            + '<div class="wcards">'
            + (es.length ? es.map(ev => '<div class="wev ' + ev.t + '"><span class="wdot"></span>' + esc(ev.n) + '</div>').join('') : '<span class="wnone">—</span>')
            + '</div></div>';
    });
    html += '</div></div>';
    stage.innerHTML = html;
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
    document.querySelectorAll('.dn.sel').forEach(el => el.classList.remove('sel'));
    const cell = document.querySelector('.cell[data-day="' + ds + '"] .dn');
    if (cell) cell.classList.add('sel');
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
