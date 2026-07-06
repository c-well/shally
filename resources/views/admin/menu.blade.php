<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Menu studio — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment, #fefcef); color: var(--ink, #1a2332); font-family: 'Instrument Sans', system-ui, sans-serif; }
  .top { padding: 24px 28px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line, rgba(26,35,50,.12)); }
  .top a { font-size: 13.5px; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft, #4a5568); padding: 10px 12px; margin: -10px -12px; }
  .top a:hover { color: var(--teal, #03617A); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 12.5px; color: var(--ink-soft); opacity: .65; }
  main { max-width: 860px; margin: 0 auto; padding: 34px 22px 120px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: 24px; font-weight: 500; }
  .lede { color: var(--ink-soft, #4a5568); font-size: 14px; margin-top: 8px; line-height: 1.6; }

  .seclab { font-size: 11px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-soft); margin: 34px 0 12px; }
  .styles { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
  .style-card { border: 2px solid var(--line, rgba(26,35,50,.12)); border-radius: 12px; background: #fff; padding: 14px; cursor: pointer; text-align: left; font-family: inherit; }
  .style-card.on { border-color: var(--teal, #03617A); box-shadow: 0 0 0 3px rgba(3,97,122,.12); }
  .style-card .nm { font-weight: 700; font-size: 13px; }
  .style-card .ds { font-size: 11.5px; color: var(--ink-soft); margin-top: 4px; line-height: 1.5; }
  .thumb { margin-top: 10px; border: 1px solid var(--line); border-radius: 7px; padding: 8px; background: var(--parchment, #fefcef); height: 84px; overflow: hidden; }
  .th-row { height: 7px; background: rgba(26,35,50,.14); border-radius: 3px; margin: 6px 0; }
  .th-row.short { width: 55%; } .th-lab { height: 4px; width: 34%; background: #8a6c26; opacity: .6; border-radius: 2px; margin: 8px 0 4px; }
  .th-tiles { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
  .th-tile { height: 26px; border-radius: 4px; background: rgba(26,35,50,.12); } .th-tile.hero { background: #03617A; }
  .th-card { height: 30px; border-radius: 5px; background: #03617A; margin-bottom: 6px; }

  .groups { margin-top: 4px; }
  .grp { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; margin-top: 14px; }
  .grp-head { display: flex; align-items: center; gap: 10px; }
  .grp-head input { font: 700 11px 'Instrument Sans'; letter-spacing: .18em; text-transform: uppercase; color: var(--brass, #8a6c26); border: 1px solid transparent; background: transparent; border-radius: 6px; padding: 6px 8px; width: 220px; }
  .grp-head input:focus { outline: none; border-color: var(--teal); background: var(--parchment); }
  .it { display: flex; align-items: center; gap: 9px; padding: 7px 0; border-top: 1px dashed var(--line); }
  .it:first-of-type { border-top: 0; }
  .it .grip { color: #6b7280; cursor: grab; touch-action: none; user-select: none; -webkit-user-select: none; flex-shrink: 0; width: 20px; text-align: center; font-size: 15px; letter-spacing: -1px; }
  .it input.lbl { flex: 1; font: 500 15px 'Instrument Sans'; color: var(--ink); border: 1px solid transparent; background: transparent; border-radius: 6px; padding: 8px 10px; min-width: 0; }
  .it input.lbl:focus { outline: none; border-color: var(--teal); background: var(--parchment); }
  .it .dest { font-family: 'JetBrains Mono', monospace; font-size: 10.5px; color: var(--ink-soft); opacity: .7; flex-shrink: 0; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .it .hide-t { flex-shrink: 0; font: 700 10px 'Instrument Sans'; letter-spacing: .1em; border: 1px solid var(--line); background: #fff; color: var(--ink-soft); border-radius: 6px; padding: 7px 10px; cursor: pointer; }
  .it.off input.lbl { opacity: .4; text-decoration: line-through; }
  .it.off .hide-t { background: var(--parchment); color: var(--brass, #8a6c26); border-color: var(--brass, #8a6c26); }
  .it.dragging { opacity: .55; }

  .bar { position: fixed; bottom: 0; left: 0; right: 0; background: color-mix(in srgb, var(--parchment, #fefcef) 92%, transparent); backdrop-filter: blur(6px); border-top: 1px solid var(--line); padding: 14px 22px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
  .btn { font: 700 12px 'Instrument Sans'; letter-spacing: .12em; text-transform: uppercase; border-radius: 8px; padding: 13px 22px; cursor: pointer; border: 1px solid var(--line); background: #fff; color: var(--teal, #03617A); }
  .btn.primary { background: var(--teal, #03617A); border-color: var(--teal, #03617A); color: #fff; }
  .btn:disabled { opacity: .5; cursor: wait; }
  .pip { position: fixed; bottom: 84px; left: 50%; transform: translateX(-50%); font-size: 12px; font-weight: 600; color: #fff; background: var(--teal, #03617A); padding: 8px 16px; border-radius: 8px; opacity: 0; transition: opacity .2s; pointer-events: none; }
  .pip.show { opacity: 1; } .pip.err { background: #a33d3d; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">menu studio</span>
</header>

<main>
  <h1>Menu studio.</h1>
  <p class="lede">Pick a template, drag items where they belong, rename or hide anything. Changes save automatically and go live on the next page-load — the menu itself can never break from here (unknown links simply don't render).</p>

  <div class="seclab">Template</div>
  <div class="styles" id="styles">
    <button type="button" class="style-card" data-style="classic">
      <span class="nm">Classic</span>
      <span class="ds">The original — collapsible sections.</span>
      <div class="thumb"><div class="th-row"></div><div class="th-row short"></div><div class="th-row"></div><div class="th-row short"></div></div>
    </button>
    <button type="button" class="style-card" data-style="grouped">
      <span class="nm">Grouped</span>
      <span class="ds">Everything visible, butter rows under quiet labels. <b>Recommended by the click data.</b></span>
      <div class="thumb"><div class="th-lab"></div><div class="th-row"></div><div class="th-row"></div><div class="th-lab"></div><div class="th-row"></div></div>
    </button>
    <button type="button" class="style-card" data-style="tiles">
      <span class="nm">Tiles</span>
      <span class="ds">First four items become big Sabbath cards.</span>
      <div class="thumb"><div class="th-tiles"><div class="th-tile hero"></div><div class="th-tile"></div><div class="th-tile"></div><div class="th-tile"></div></div><div class="th-row short"></div></div>
    </button>
    <button type="button" class="style-card" data-style="today">
      <span class="nm">Today card</span>
      <span class="ds">A living card with today's service &amp; live events, then rows.</span>
      <div class="thumb"><div class="th-card"></div><div class="th-lab"></div><div class="th-row"></div><div class="th-row short"></div></div>
    </button>
  </div>

  <div class="seclab">Items — drag ⋮ to reorder (across groups too) · tap Hide to bench one</div>
  <div class="groups" id="groups"></div>
</main>

<div class="bar">
  <button type="button" class="btn" id="applyRec">Use the recommended layout</button>
  <button type="button" class="btn" id="applyDef">Back to the original</button>
  <a class="btn primary" href="/?x={{ time() }}" target="_blank" rel="noopener">Open the site &amp; try it →</a>
</div>
<div class="pip" id="pip">Saved</div>

<script id="cfg-data" type="application/json">{!! json_encode($config, JSON_UNESCAPED_SLASHES) !!}</script>
<script id="rec-data" type="application/json">{!! json_encode($recommended, JSON_UNESCAPED_SLASHES) !!}</script>
<script id="def-data" type="application/json">{!! json_encode($defaultCfg, JSON_UNESCAPED_SLASHES) !!}</script>
<script>
(function () {
  let cfg = JSON.parse(document.getElementById('cfg-data').textContent);
  const REC = JSON.parse(document.getElementById('rec-data').textContent);
  const DEF = JSON.parse(document.getElementById('def-data').textContent);
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  const groupsEl = document.getElementById('groups');
  const pip = document.getElementById('pip');
  let t = null;

  function pipMsg(m, err) {
    pip.textContent = m; pip.classList.toggle('err', !!err); pip.classList.add('show');
    clearTimeout(pip._t); pip._t = setTimeout(() => pip.classList.remove('show'), err ? 2800 : 1100);
  }
  function save() {
    clearTimeout(t);
    t = setTimeout(async () => {
      try {
        const r = await fetch(@json(route('admin.menu.save')), {
          method: 'PATCH',
          headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(cfg),
        });
        (await r.json()).ok ? pipMsg('Saved — live on next page-load') : pipMsg('Not saved — try again', true);
      } catch (e) { pipMsg('Not saved — try again', true); }
    }, 450);
  }
  const esc = s => String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  function paintStyles() {
    document.querySelectorAll('.style-card').forEach(c => c.classList.toggle('on', c.dataset.style === cfg.style));
  }
  document.getElementById('styles').addEventListener('click', e => {
    const c = e.target.closest('.style-card'); if (!c) return;
    cfg.style = c.dataset.style; paintStyles(); save();
  });

  function render() {
    groupsEl.innerHTML = cfg.groups.map((g, gi) => `
      <div class="grp" data-gi="${gi}">
        <div class="grp-head"><input value="${esc(g.label || '')}" placeholder="(no group label)" data-glabel="${gi}"></div>
        ${(g.items || []).map((i, ii) => `
          <div class="it ${i.hidden ? 'off' : ''}" data-gi="${gi}" data-ii="${ii}">
            <span class="grip">⋮</span>
            <input class="lbl" value="${esc(i.label)}" data-lbl>
            <span class="dest">${esc(i.route || i.url || '')}</span>
            <button type="button" class="hide-t" data-hide>${i.hidden ? 'Show' : 'Hide'}</button>
          </div>`).join('')}
      </div>`).join('');
  }

  groupsEl.addEventListener('input', e => {
    if (e.target.matches('[data-glabel]')) { cfg.groups[+e.target.dataset.glabel].label = e.target.value.trim() || null; save(); }
    if (e.target.matches('[data-lbl]')) {
      const it = e.target.closest('.it');
      cfg.groups[+it.dataset.gi].items[+it.dataset.ii].label = e.target.value; save();
    }
  });
  groupsEl.addEventListener('click', e => {
    if (!e.target.matches('[data-hide]')) return;
    const it = e.target.closest('.it');
    const item = cfg.groups[+it.dataset.gi].items[+it.dataset.ii];
    item.hidden = !item.hidden; render(); save();
  });

  // pointer drag across groups
  let drag = null;
  groupsEl.addEventListener('pointerdown', e => {
    const grip = e.target.closest('.grip'); if (!grip) return;
    const it = grip.closest('.it'); e.preventDefault();
    drag = { gi: +it.dataset.gi, ii: +it.dataset.ii, el: it };
    it.classList.add('dragging');
    try { grip.setPointerCapture(e.pointerId); } catch (err) {}
  });
  groupsEl.addEventListener('pointermove', e => {
    if (!drag) return; e.preventDefault();
    const over = document.elementFromPoint(e.clientX, e.clientY)?.closest('.it, .grp');
    if (!over) return;
    const item = cfg.groups[drag.gi].items.splice(drag.ii, 1)[0];
    let tgi, tii;
    if (over.classList.contains('it') && over !== drag.el) {
      tgi = +over.dataset.gi; tii = +over.dataset.ii;
      const r = over.getBoundingClientRect();
      if (e.clientY > r.top + r.height / 2) tii++;
    } else if (over.classList.contains('grp')) {
      tgi = +over.dataset.gi; tii = cfg.groups[tgi].items.length;
    } else { cfg.groups[drag.gi].items.splice(drag.ii, 0, item); return; }
    cfg.groups[tgi].items.splice(tii, 0, item);
    drag.gi = tgi; drag.ii = tii;
    render();
    drag.el = groupsEl.querySelector(`.it[data-gi="${tgi}"][data-ii="${tii}"]`);
    drag.el?.classList.add('dragging');
  });
  const endDrag = () => { if (!drag) return; drag.el?.classList.remove('dragging'); drag = null; render(); save(); };
  groupsEl.addEventListener('pointerup', endDrag);
  groupsEl.addEventListener('pointercancel', endDrag);

  document.getElementById('applyRec').addEventListener('click', () => { cfg = JSON.parse(JSON.stringify(REC)); paintStyles(); render(); save(); pipMsg('Recommended layout applied'); });
  document.getElementById('applyDef').addEventListener('click', () => { cfg = JSON.parse(JSON.stringify(DEF)); paintStyles(); render(); save(); pipMsg('Original layout restored'); });

  paintStyles(); render();
})();
</script>
@include('partials._event-tracker')
</body>
</html>
