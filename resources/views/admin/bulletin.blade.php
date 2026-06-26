<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Bulletin editor — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 18px clamp(18px,5vw,36px); display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); position: sticky; top: 0; background: color-mix(in srgb, var(--parchment) 94%, transparent); backdrop-filter: blur(6px); z-index: 10; }
  .top a, .top .lnk { font-size: 11px; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); cursor: pointer; background: none; border: 0; }
  .top a:hover, .top .lnk:hover { color: var(--teal); }
  .top .right { display: flex; align-items: center; gap: 16px; }
  .status { font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; padding: 6px 12px; border-radius: 999px; }
  .status.live { background: color-mix(in srgb, var(--teal) 15%, transparent); color: var(--teal-dark); }
  .status.draft { background: color-mix(in srgb, #b08d57 16%, transparent); color: #8a6d3b; }
  .golive { font-family: inherit; font-size: 12px; font-weight: 600; letter-spacing: 0.06em; padding: 11px 22px; border-radius: 7px; border: 0; background: var(--teal); color: #fff; cursor: pointer; }
  .golive:hover { background: var(--teal-dark); }
  .golive.clean { opacity: .5; }

  main { max-width: 820px; margin: 0 auto; padding: clamp(28px,5vh,48px) clamp(18px,5vw,32px) 140px; }

  /* meta */
  .meta { display: grid; grid-template-columns: 1fr; gap: 18px; margin-bottom: 16px; }
  .titlein { font-family: 'IBM Plex Sans'; font-size: clamp(28px,5vw,42px); font-weight: 700; letter-spacing: -0.02em; color: var(--ink); border: 0; background: transparent; width: 100%; padding: 4px 0; }
  .titlein:focus { outline: none; box-shadow: 0 2px 0 var(--teal); }
  .metarow { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
  .metarow label { font-size: 10px; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); margin-right: 7px; }
  .metarow input, .metarow select { font: inherit; font-size: 14px; padding: 9px 12px; border: 1px solid var(--line); border-radius: 6px; background: #fff; color: var(--ink); }
  .switcher { margin-left: auto; }

  .sec-label { margin: 34px 0 12px; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); display: flex; align-items: center; justify-content: space-between; }

  /* items */
  .items { display: flex; flex-direction: column; gap: 8px; }
  .item { display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--line); border-radius: 9px; padding: 10px 10px 10px 12px; }
  .item.section { background: color-mix(in srgb, var(--teal) 6%, #fff); border-color: color-mix(in srgb, var(--teal) 22%, var(--line)); }
  .item .fields { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; min-width: 0; }
  .item.section .fields { grid-template-columns: 1fr; }
  @media (max-width: 560px) { .item .fields { grid-template-columns: 1fr; } }
  .item input { font: inherit; font-size: 15px; padding: 9px 11px; border: 1px solid transparent; border-radius: 6px; background: var(--parchment); color: var(--ink); width: 100%; }
  .item.section input { font-weight: 600; font-size: 15px; letter-spacing: 0.02em; background: transparent; }
  .item input::placeholder { color: var(--ink-faint, #9aa0aa); }
  .item input:focus { outline: none; border-color: var(--teal); background: #fff; }
  .ctrls { display: flex; gap: 2px; flex-shrink: 0; }
  .ic { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 0; background: transparent; color: var(--ink-soft); border-radius: 6px; cursor: pointer; }
  .ic:hover { background: var(--parchment); color: var(--teal); }
  .ic.del:hover { color: #b23b2e; }
  .ic svg { width: 17px; height: 17px; }

  .addrow { display: flex; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
  .addbtn { font-family: inherit; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; padding: 11px 16px; border: 1px dashed var(--line); border-radius: 7px; background: transparent; color: var(--teal); cursor: pointer; }
  .addbtn:hover { border-color: var(--teal); border-style: solid; }
  .addbtn.ghost { color: var(--ink-soft); }

  .empty { padding: 26px; text-align: center; border: 1px dashed var(--line); border-radius: 9px; color: var(--ink-soft); }
  .saved-pip { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); font-size: 12px; color: var(--ink-soft); opacity: 0; transition: opacity .2s; pointer-events: none; }
  .saved-pip.show { opacity: 1; }
  .nobull { max-width: 560px; margin: 80px auto; text-align: center; }
  .nobull .big { font-family: 'IBM Plex Serif'; font-size: 26px; color: var(--ink); margin-bottom: 14px; }
</style>
@include('partials.theme-vars')
{{-- Deliberately NOT including admin _typography here — bulletin v2 is held to
     the public "Considered" house style (IBM Plex Sans), like the intake form. --}}
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <div class="right">
    <form method="POST" action="{{ route('admin.bulletin.prefer') }}" style="display:inline;">@csrf<input type="hidden" name="version" value="v1"><button class="lnk" type="submit">Classic editor ⇄</button></form>
    @if ($bulletin)
      <a class="lnk" href="{{ route('bulletins.pdf', $bulletin) }}" target="_blank" rel="noopener">PDF ↓</a>
      <span class="status {{ $bulletin->is_published ? 'live' : 'draft' }}" id="status">{{ $bulletin->is_published ? 'Published' : 'Draft' }}</span>
      <button class="golive" id="golive" data-url="{{ route('bulletins.publish', $bulletin) }}">Go Live</button>
    @endif
  </div>
</div>

@if (! $bulletin)
  <div class="nobull">
    <div class="big">No bulletin yet.</div>
    <p style="color:var(--ink-soft)">Create this week's bulletin from the classic editor, then come back here to drill through it.</p>
    <a class="addbtn" style="display:inline-block;margin-top:18px" href="/welcome">Open classic editor →</a>
  </div>
@else
<main data-bid="{{ $bulletin->id }}">
  <div class="meta">
    <input class="titlein" id="b-title" value="{{ $bulletin->title }}" placeholder="Bulletin title" maxlength="180">
    <div class="metarow">
      <span><label>Date</label><input type="date" id="b-date" value="{{ optional($bulletin->service_date)->format('Y-m-d') }}"></span>
      <span><label>Theme</label>
        <select id="b-theme">
          @foreach (['default'=>'Default','communion'=>'Communion','easter'=>'Easter','christmas'=>'Christmas','mothers'=>"Mother's Day",'thanksgiving'=>'Thanksgiving'] as $v=>$lbl)
            <option value="{{ $v }}" @selected(($bulletin->theme ?? 'default')===$v)>{{ $lbl }}</option>
          @endforeach
        </select>
      </span>
      <span class="switcher"><label>Bulletin</label>
        <select id="b-switch">
          @foreach ($bulletins as $bb)
            <option value="{{ $bb->id }}" @selected($bb->id===$bulletin->id)>{{ optional($bb->service_date)->format('M j, Y') }} {{ $bb->service_time ? '· '.ucfirst($bb->service_time) : '' }} — {{ \Illuminate\Support\Str::limit($bb->title, 24) }}</option>
          @endforeach
        </select>
      </span>
    </div>
  </div>

  <div class="sec-label"><span>Order of service</span></div>
  <div class="items" id="items">
    @forelse ($bulletin->lines as $line)
      <div class="item {{ $line->kind === 'section_header' ? 'section' : '' }}" data-id="{{ $line->id }}" data-kind="{{ $line->kind }}">
        <div class="fields">
          @if ($line->kind === 'section_header')
            <input data-f="section" value="{{ $line->section }}" placeholder="Section heading…" list="sections">
          @else
            <input data-f="part" value="{{ $line->part }}" placeholder="What (e.g. Opening Hymn)">
            <input data-f="person" value="{{ $line->person }}" placeholder="Who (e.g. Sis. Jones)">
          @endif
        </div>
        <div class="ctrls">
          <button class="ic up" title="Move up" aria-label="Move up"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg></button>
          <button class="ic down" title="Move down" aria-label="Move down"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></button>
          <button class="ic del" title="Delete" aria-label="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button>
        </div>
      </div>
    @empty
      <div class="empty" id="emptyItems">Nothing here yet. Add an item, or load the standard order of service below.</div>
    @endforelse
  </div>
  <datalist id="sections">
    @foreach (['Pastoral Greetings','Praise & Worship','Service of Worship',"Children's Story",'Mission Story','Special Music','Sermon','Tithes & Offerings','Communion','Foot Washing','Closing','Benediction'] as $s)<option value="{{ $s }}">@endforeach
  </datalist>

  <div class="addrow">
    <button class="addbtn" id="add-line">+ Add item</button>
    <button class="addbtn" id="add-section">+ Add section heading</button>
    @if ($bulletin->lines->isEmpty())
      <button class="addbtn ghost" id="load-standard" data-url="{{ route('bulletins.load-standard', $bulletin) }}">Load standard order</button>
    @endif
  </div>

  <div class="sec-label"><span>Announcements</span></div>
  <div class="items" id="anns">
    @foreach ($bulletin->announcements as $a)
      <div class="item" data-aid="{{ $a->id }}">
        <div class="fields" style="grid-template-columns:1fr 1.6fr">
          <input data-af="title" value="{{ $a->title }}" placeholder="Title">
          <input data-af="detail" value="{{ $a->detail }}" placeholder="Detail">
        </div>
        <div class="ctrls"><button class="ic adel" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button></div>
      </div>
    @endforeach
  </div>
  <div class="addrow"><button class="addbtn" id="add-ann">+ Add announcement</button></div>
</main>
@endif

<div class="saved-pip" id="pip">Saved</div>
@include('partials._confirm')
<script>
(function () {
  var main = document.querySelector('main[data-bid]'); if (!main) return;
  var BID = main.getAttribute('data-bid');
  var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
  var pip = document.getElementById('pip');
  function savedPip() { pip.classList.add('show'); clearTimeout(pip._t); pip._t = setTimeout(function(){ pip.classList.remove('show'); }, 1100); }
  function api(method, url, body) {
    return fetch(url, { method: method, headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: body ? JSON.stringify(body) : null })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); });
  }
  var debTimers = {};
  function debounce(key, fn) { clearTimeout(debTimers[key]); debTimers[key] = setTimeout(fn, 550); }

  // ── meta ──
  document.getElementById('b-title').addEventListener('input', function (e) { debounce('title', function () { api('PATCH', '/bulletins/' + BID, { title: e.target.value }).then(savedPip); }); });
  document.getElementById('b-date').addEventListener('change', function (e) { api('PATCH', '/bulletins/' + BID, { service_date: e.target.value }).then(savedPip); });
  document.getElementById('b-theme').addEventListener('change', function (e) { api('PATCH', '/bulletins/' + BID, { theme: e.target.value }).then(savedPip); });
  document.getElementById('b-switch').addEventListener('change', function (e) { window.location = '?b=' + e.target.value; });

  // ── line field autosave ──
  var items = document.getElementById('items');
  items.addEventListener('input', function (e) {
    var inp = e.target.closest('input[data-f]'); if (!inp) return;
    var row = inp.closest('[data-id]'); var id = row.getAttribute('data-id');
    var field = inp.getAttribute('data-f');
    debounce('line' + id + field, function () { api('PATCH', '/bulletins/' + BID + '/lines/' + id, (function(o){o[field]=inp.value;return o;})({})).then(savedPip); });
  });

  function sendOrder() {
    var ids = [].slice.call(items.querySelectorAll('[data-id]')).map(function (r) { return parseInt(r.getAttribute('data-id'), 10); });
    return api('PATCH', '/bulletins/' + BID + '/lines/reorder', { ids: ids }).then(savedPip);
  }

  items.addEventListener('click', function (e) {
    var row = e.target.closest('[data-id]'); if (!row) return;
    if (e.target.closest('.up')) { var p = row.previousElementSibling; if (p && p.hasAttribute('data-id')) { items.insertBefore(row, p); sendOrder(); } return; }
    if (e.target.closest('.down')) { var n = row.nextElementSibling; if (n && n.hasAttribute('data-id')) { items.insertBefore(n, row); sendOrder(); } return; }
    if (e.target.closest('.del')) {
      var id = row.getAttribute('data-id');
      var label = (row.querySelector('input') || {}).value || 'this item';
      window.shConfirm('Delete "' + label + '"?', { okLabel: 'Delete', danger: true }).then(function (ok) {
        if (!ok) return;
        api('DELETE', '/bulletins/' + BID + '/lines/' + id).then(function () { row.remove(); savedPip(); });
      });
      return;
    }
  });

  function makeRow(line) {
    var div = document.createElement('div');
    div.className = 'item' + (line.kind === 'section_header' ? ' section' : '');
    div.setAttribute('data-id', line.id); div.setAttribute('data-kind', line.kind);
    var fields = line.kind === 'section_header'
      ? '<input data-f="section" value="" placeholder="Section heading…" list="sections">'
      : '<input data-f="part" value="" placeholder="What (e.g. Opening Hymn)"><input data-f="person" value="" placeholder="Who (e.g. Sis. Jones)">';
    div.innerHTML = '<div class="fields">' + fields + '</div><div class="ctrls">' +
      '<button class="ic up" title="Move up"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg></button>' +
      '<button class="ic down" title="Move down"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></button>' +
      '<button class="ic del" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button></div>';
    return div;
  }
  function addItem(kind) {
    var empty = document.getElementById('emptyItems'); if (empty) empty.remove();
    api('POST', '/bulletins/' + BID + '/lines', { kind: kind }).then(function (res) {
      if (res.ok && res.d.ok) { var row = makeRow(res.d.line); items.appendChild(row); var f = row.querySelector('input'); if (f) f.focus(); savedPip(); }
    });
  }
  document.getElementById('add-line').addEventListener('click', function () { addItem('line'); });
  document.getElementById('add-section').addEventListener('click', function () { addItem('section_header'); });
  var ls = document.getElementById('load-standard');
  if (ls) ls.addEventListener('click', function () {
    window.shConfirm('Load the standard order of service? This fills the bulletin with the usual sections.', { okLabel: 'Load' }).then(function (ok) {
      if (!ok) return; api('POST', ls.getAttribute('data-url')).then(function () { window.location.reload(); });
    });
  });

  // ── announcements ──
  var anns = document.getElementById('anns');
  anns.addEventListener('input', function (e) {
    var inp = e.target.closest('input[data-af]'); if (!inp) return;
    var row = inp.closest('[data-aid]'); var id = row.getAttribute('data-aid'); var field = inp.getAttribute('data-af');
    debounce('ann' + id + field, function () { api('PATCH', '/bulletins/' + BID + '/announcements/' + id, (function(o){o[field]=inp.value;return o;})({})).then(savedPip); });
  });
  anns.addEventListener('click', function (e) {
    var row = e.target.closest('[data-aid]'); if (!row || !e.target.closest('.adel')) return;
    window.shConfirm('Delete this announcement?', { okLabel: 'Delete', danger: true }).then(function (ok) {
      if (!ok) return; api('DELETE', '/bulletins/' + BID + '/announcements/' + row.getAttribute('data-aid')).then(function () { row.remove(); savedPip(); });
    });
  });
  document.getElementById('add-ann').addEventListener('click', function () {
    api('POST', '/bulletins/' + BID + '/announcements', { title: '' }).then(function (res) {
      if (res.ok && res.d.ok) {
        var a = res.d.announcement; var div = document.createElement('div'); div.className = 'item'; div.setAttribute('data-aid', a.id);
        div.innerHTML = '<div class="fields" style="grid-template-columns:1fr 1.6fr"><input data-af="title" value="" placeholder="Title"><input data-af="detail" value="" placeholder="Detail"></div><div class="ctrls"><button class="ic adel" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button></div>';
        anns.appendChild(div); div.querySelector('input').focus(); savedPip();
      }
    });
  });

  // ── publish ──
  document.getElementById('golive').addEventListener('click', function () {
    var btn = this;
    window.shConfirm('Publish this bulletin to the website right now?', { okLabel: 'Go Live' }).then(function (ok) {
      if (!ok) return; btn.disabled = true;
      api('POST', btn.getAttribute('data-url')).then(function (res) {
        btn.disabled = false;
        if (res.ok && res.d.ok) { var s = document.getElementById('status'); s.textContent = 'Published'; s.className = 'status live'; window.shToast('Published — live on the website now.'); }
        else window.shToast('Could not publish — try again.');
      });
    });
  });
})();
</script>
</body>
</html>
