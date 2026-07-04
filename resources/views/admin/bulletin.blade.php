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
  .top .tools { display: flex; align-items: center; gap: 16px; margin-left: auto; }
  .top .actions { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
  /* Mobile: the editor's main home (Karlon: 90% of edits happen here).
     Row 1 = ← Admin + Published + Go Live. Row 2 = the document tools. */
  @media (max-width: 700px) {
    .top { flex-wrap: wrap; row-gap: 11px; padding: 12px 16px; }
    .top .backlnk { order: 0; }
    .top .actions { order: 1; margin-left: auto; }
    .top .tools { order: 2; margin-left: 0; width: 100%; justify-content: space-between; border-top: 1px dashed var(--line); padding-top: 10px; }
    .golive { padding: 10px 18px; }
  }
  .status { font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; padding: 6px 12px; border-radius: 7px; }
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

  .sec-toggle { cursor: pointer; user-select: none; }
  .sec-toggle:hover .sec-chevron { color: var(--teal); }
  .sec-chevron { font-size: 10px; letter-spacing: 0.14em; color: var(--ink-faint, #9aa0aa); border: 1px solid var(--line); border-radius: 6px; padding: 4px 10px; background: #fff; }
  /* Collapsed: the whole order-of-service body folds to nothing; label becomes the reopen bar */
  #oosBody { overflow: hidden; transition: max-height .28s ease, opacity .22s ease; }
  #oosBody.folded { max-height: 0 !important; opacity: 0; }
  .sec-label { margin: 34px 0 12px; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); display: flex; align-items: center; justify-content: space-between; }

  /* items */
  .items { display: flex; flex-direction: column; gap: 8px; }
  .item { display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--line); border-radius: 9px; padding: 10px 10px 10px 12px; }
  .item.section { background: color-mix(in srgb, var(--teal) 6%, #fff); border-color: color-mix(in srgb, var(--teal) 22%, var(--line)); }
  .item .fields { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; min-width: 0; }
  /* Grid items refuse to shrink below content width by default — a long one-line
     nowrap textarea (2000px+ intrinsic) was inflating the whole page to 500px on
     phones, squishing the header off-screen (Karlon 2026-07-04). */
  .item .fields > * { min-width: 0; }
  .item.section .fields { grid-template-columns: 1fr; }
  @media (max-width: 560px) { .item .fields { grid-template-columns: 1fr; } }
  .item input { font: inherit; font-size: 15px; padding: 9px 11px; border: 1px solid transparent; border-radius: 6px; background: var(--parchment); color: var(--ink); width: 100%; }
  .item.section input { font-weight: 600; font-size: 15px; letter-spacing: 0.02em; background: transparent; }
  .item input::placeholder { color: var(--ink-faint, #9aa0aa); }
  .item input:focus { outline: none; border-color: var(--teal); background: #fff; }
  .ctrls { display: flex; gap: 2px; flex-shrink: 0; }
  /* Drag-to-reorder handle (announcements). touch-action:none only here, so the page
     still scrolls from anywhere else on the row — safe on mobile. */
  .drag { flex-shrink: 0; width: 24px; user-select: none; -webkit-user-select: none; -webkit-touch-callout: none; align-self: stretch; display: flex; align-items: center; justify-content: center; color: var(--ink-faint, #9aa0aa); cursor: grab; touch-action: none; border-radius: 6px; margin-left: -6px; }
  .drag svg { width: 17px; height: 17px; }
  .drag:hover { color: var(--teal); background: var(--parchment); }
  .ann-wrap.dragging { opacity: .6; }
  .ann-wrap.dragging .item { border-color: var(--teal); box-shadow: 0 8px 22px rgba(3,97,122,.2); }
  /* Focus mode: rows stay slim until she taps into Detail — then the row opens into a
     writing space (full-width auto-growing textarea) and folds back on blur. */
  .item textarea { font: inherit; font-size: 15px; line-height: 1.5; padding: 9px 11px; border: 1px solid transparent; border-radius: 6px; background: var(--parchment); color: var(--ink); width: 100%; resize: none; overflow: hidden; display: block; height: 40px; white-space: nowrap; text-overflow: ellipsis; }
  .item textarea::placeholder { color: var(--ink-faint, #9aa0aa); }
  .item textarea:focus { outline: none; border-color: var(--teal); background: #fff; }
  .ann-wrap.editing .item { align-items: flex-start; border-color: var(--teal); box-shadow: 0 6px 20px rgba(3,97,122,.12); }
  .ann-wrap.editing .fields { grid-template-columns: 1fr !important; }
  .ann-wrap.editing textarea[data-af="detail"] { min-height: 120px; white-space: pre-wrap; }
  .ann-wrap.editing .drag, .ann-wrap.editing .ctrls { padding-top: 6px; }
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
  .saved-pip.err { color: #fff; background: #a33d3d; padding: 6px 14px; border-radius: 7px; font-weight: 600; }
  .nobull { max-width: 560px; margin: 80px auto; text-align: center; }
  .nobull .big { font-family: 'IBM Plex Serif'; font-size: 26px; color: var(--ink); margin-bottom: 14px; }

  /* Autocomplete (person field) */
  .ac { position: fixed; z-index: 200; background: #fff; border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 14px 32px -14px rgba(0,0,0,.28); overflow-y: auto; max-height: 264px; display: none; }
  .ac.show { display: block; }
  .ac-item { padding: 10px 13px; font-size: 14px; color: var(--ink); cursor: pointer; white-space: nowrap; }
  .ac-item.active { background: color-mix(in srgb, var(--teal) 12%, #fff); color: var(--teal-dark); }

  /* Announcement media */
  .ann-wrap { background: #fff; border: 1px solid var(--line); border-radius: 9px; overflow: hidden; }
  /* The print/digital cut line: rows above it print; rows below live on /announcements
     (reached by the QR on the printed bulletin). Moving a row across it flips the flag. */
  .print-divider { display: flex; align-items: center; gap: 12px; margin: 4px 0; }
  .print-divider::before, .print-divider::after { content: ''; flex: 1; border-top: 2px dashed color-mix(in srgb, var(--teal) 45%, var(--line)); }
  .print-divider span { font-size: 10px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--teal); white-space: nowrap; }
  .ann-wrap.webonly { opacity: .82; background: color-mix(in srgb, var(--teal) 3%, #fff); }
  .ann-wrap.webonly .item::after { content: 'WEB'; font-size: 8px; font-weight: 800; letter-spacing: .1em; color: var(--teal); border: 1px solid color-mix(in srgb, var(--teal) 40%, transparent); border-radius: 4px; padding: 2px 5px; margin-left: 4px; align-self: center; }
  /* Blank title = child bullets of the announcement above (folds under it on the PDF).
     Visually: indent + arrow, shrink the title box out of the way; focusing it restores
     full width so a title can still be added (which promotes the row to a parent). */
  .ann-wrap.child { margin-left: 30px; position: relative; border-style: dashed; }
  .ann-wrap.child::before { content: '↳'; position: absolute; left: -22px; top: 12px; color: var(--teal); font-size: 15px; font-weight: 700; }
  .ann-wrap.child .fields { grid-template-columns: 72px 1fr !important; }
  .ann-wrap.child [data-af="title"]:not(:focus) { opacity: .5; }
  .ann-wrap .item { border: 0; border-radius: 0; }
  .ic.on { color: var(--teal); background: color-mix(in srgb, var(--teal) 12%, transparent); }
  .ann-media { display: none; flex-direction: column; gap: 9px; padding: 12px 14px; border-top: 1px solid var(--line); background: color-mix(in srgb, var(--teal) 3%, #fff); }
  .ann-media.open { display: flex; }
  .ann-photo { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: var(--teal); cursor: pointer; border: 1px dashed var(--line); border-radius: 6px; padding: 9px 12px; background: #fff; align-self: flex-start; }
  .ann-photo:hover { border-color: var(--teal); }
  .ann-photo input { display: none; }
  .ann-thumb { max-height: 96px; border-radius: 5px; align-self: flex-start; }
  .ann-img-remove { align-self: flex-start; font: inherit; font-size: 11px; color: var(--ink-soft); background: none; border: 0; cursor: pointer; text-decoration: underline; }
  .ann-vid-lbl { display: flex; flex-direction: column; gap: 5px; font-size: 10px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-soft); }
  .ann-vid-lbl input { font: inherit; font-size: 14px; padding: 8px 10px; border: 1px solid var(--line); border-radius: 5px; background: #fff; color: var(--ink); text-transform: none; letter-spacing: 0; }
</style>
@include('partials.theme-vars')
{{-- Deliberately NOT including admin _typography here — bulletin v2 is held to
     the public "Considered" house style (IBM Plex Sans), like the intake form. --}}
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a class="backlnk" href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <div class="tools">
    <form method="POST" action="{{ route('admin.bulletin.prefer') }}" style="display:inline;">@csrf<input type="hidden" name="version" value="v1"><button class="lnk" type="submit">Classic editor ⇄</button></form>
    @if ($bulletin)
      <a class="lnk" href="{{ route('bulletins.pdf', $bulletin) }}" target="_blank" rel="noopener">PDF ↓</a>
      <a class="lnk" href="{{ route('bulletins.pdf', $bulletin) }}?layout=2up" target="_blank" rel="noopener" title="One landscape sheet, bulletin twice — print 2-sided, cut in half">2-UP ↓</a>
    @endif
  </div>
  @if ($bulletin)
    <div class="actions">
      <span class="status {{ $bulletin->is_published ? 'live' : 'draft' }}" id="status">{{ $bulletin->is_published ? 'Published' : 'Draft' }}</span>
      <button class="golive" id="golive" data-url="{{ route('bulletins.publish', $bulletin) }}">Go Live</button>
    </div>
  @endif
</div>

@if (! $bulletin)
  <div class="nobull">
    <div class="big">No bulletin yet.</div>
    <p style="color:var(--ink-soft)">Create this week's bulletin from the classic editor, then come back here to drill through it.</p>
    <a class="addbtn" style="display:inline-block;margin-top:18px" href="/welcome">Open classic editor @include('partials._ar')</a>
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

  <div class="sec-label sec-toggle" id="oosToggle" role="button" tabindex="0" aria-expanded="true"><span>Order of service</span><span class="sec-chevron">Collapse</span></div>
  <div id="oosBody">
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
  </div>

  <div class="sec-label"><span>Announcements</span></div>
  <div class="items" id="anns">
    @php $printedAnns = $bulletin->announcements->where('is_web_only', false); $webAnns = $bulletin->announcements->where('is_web_only', true); @endphp
    @foreach ($printedAnns as $a)
      @include('admin.partials._ann-row', ['a' => $a, 'bulletin' => $bulletin])
    @endforeach
    <div class="print-divider" id="printDivider"><span>Printed bulletin ends here · QR leads people to the rest</span></div>
    @foreach ($webAnns as $a)
      @include('admin.partials._ann-row', ['a' => $a, 'bulletin' => $bulletin])
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
  function savedPip() { pip.textContent = 'Saved'; pip.classList.remove('err'); pip.classList.add('show'); clearTimeout(pip._t); pip._t = setTimeout(function(){ pip.classList.remove('show'); }, 1100); }
  function errPip(msg) { pip.textContent = msg || 'Not saved — try again'; pip.classList.add('err', 'show'); clearTimeout(pip._t); pip._t = setTimeout(function(){ pip.classList.remove('show', 'err'); pip.textContent = 'Saved'; }, 3200); }
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

  // ── announcement reorder (mirror of line reorder) ──
  var anns = document.getElementById('anns');
  function sendAnnOrder() {
    var ids = [].slice.call(anns.querySelectorAll('[data-aid]')).map(function (r) { return parseInt(r.getAttribute('data-aid'), 10); });
    return api('PATCH', '/bulletins/' + BID + '/announcements/reorder', { ids: ids }).then(savedPip);
  }
  // parent/child affordance: blank title = child row (indented, folds under parent on PDF)
  function refreshChild(wrap) {
    var t = wrap.querySelector('[data-af="title"]'); if (!t) return;
    wrap.classList.toggle('child', t.value.trim() === '' && document.activeElement !== t);
  }
  if (anns) {
    anns.addEventListener('focusin',  function (e) { var w = e.target.closest('[data-aid]'); if (w && e.target.matches('[data-af="title"]')) w.classList.remove('child'); });
    anns.addEventListener('focusout', function (e) { var w = e.target.closest('[data-aid]'); if (w) refreshChild(w); });
    anns.addEventListener('input',    function (e) { var w = e.target.closest('[data-aid]'); if (w && e.target.matches('[data-af="title"]') && e.target.value.trim() !== '') w.classList.remove('child'); });
  }

  // Moving across the print-divider flips physical/web-only. Above = printed; below = /announcements only.
  function syncWebFlag(wrap) {
    var divider = document.getElementById('printDivider'); if (!divider) return;
    var isWeb = !!(wrap.compareDocumentPosition(divider) & Node.DOCUMENT_POSITION_PRECEDING);
    if (wrap.classList.contains('webonly') !== isWeb) {
      wrap.classList.toggle('webonly', isWeb);
      api('PATCH', '/bulletins/' + BID + '/announcements/' + wrap.getAttribute('data-aid'), { is_web_only: isWeb ? 1 : 0 });
    }
  }
  // ── collapse the bulletin while working in announcements (Karlon: hush the busy) ──
  var oosT = document.getElementById('oosToggle'), oosB = document.getElementById('oosBody');
  function setOos(open, compensateFrom) {
    if (!oosT || !oosB) return;
    var before = compensateFrom ? compensateFrom.getBoundingClientRect().top : null;
    oosB.style.maxHeight = oosB.scrollHeight + 'px';   // measured start/end point for the fold
    requestAnimationFrame(function () {
      oosB.classList.toggle('folded', !open);
      if (open) setTimeout(function () { oosB.style.maxHeight = ''; }, 300);
      oosT.setAttribute('aria-expanded', open ? 'true' : 'false');
      oosT.querySelector('.sec-chevron').textContent = open ? 'Collapse' : 'Show \u2014 hidden while you work on announcements';
      if (before !== null) {
        setTimeout(function () {
          var after = compensateFrom.getBoundingClientRect().top;
          window.scrollBy(0, after - before);   // keep her row pinned in place under the cursor
        }, 300);
      }
    });
  }
  if (oosT) {
    oosT.addEventListener('click', function () { setOos(oosB.classList.contains('folded')); });
    oosT.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); oosT.click(); } });
  }
  var oosAutoFolded = false;
  if (anns) anns.addEventListener('focusin', function (e) {
    if (oosAutoFolded || !e.target.matches('input[data-af], textarea[data-af]')) return;
    if (oosB && !oosB.classList.contains('folded')) { oosAutoFolded = true; setOos(false, e.target.closest('[data-aid]') || e.target); }
  });

  // ── focus-mode + autogrow for announcement detail ──
  function growTa(ta) { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight + 2) + 'px'; }
  if (anns) {
    anns.addEventListener('input', function (e) { if (e.target.matches('textarea[data-af="detail"]')) growTa(e.target); });
    anns.addEventListener('focusin', function (e) {
      if (!e.target.matches('textarea[data-af="detail"]')) return;
      var w = e.target.closest('[data-aid]'); if (w) { w.classList.add('editing'); growTa(e.target); }
    });
    anns.addEventListener('focusout', function (e) {
      if (!e.target.matches('textarea[data-af="detail"]')) return;
      var w = e.target.closest('[data-aid]'); if (w) { w.classList.remove('editing'); e.target.style.height = ''; }
    });
  }

  // ── drag to reorder (pointer events → works with mouse AND touch) ──
  var dragEl = null;
  if (anns) {
    anns.addEventListener('pointerdown', function (e) {
      var h = e.target.closest('.drag'); if (!h) return;
      var wrap = h.closest('[data-aid]'); if (!wrap) return;
      e.preventDefault();
      dragEl = wrap; wrap.classList.add('dragging');
      try { h.setPointerCapture(e.pointerId); } catch (err) {}
    });
    anns.addEventListener('pointermove', function (e) {
      if (!dragEl) return;
      e.preventDefault();
      if (e.clientY < 90) window.scrollBy(0, -9); else if (e.clientY > window.innerHeight - 90) window.scrollBy(0, 9);
      var el = document.elementFromPoint(e.clientX, e.clientY);
      var over = el && el.closest ? el.closest('#anns > *') : null;
      if (!over || over === dragEl) return;
      var r = over.getBoundingClientRect();
      anns.insertBefore(dragEl, (e.clientY < r.top + r.height / 2) ? over : over.nextSibling);
    });
    var endDrag = function () {
      if (!dragEl) return;
      dragEl.classList.remove('dragging');
      syncWebFlag(dragEl); sendAnnOrder();
      dragEl = null;
    };
    anns.addEventListener('pointerup', endDrag);
    anns.addEventListener('pointercancel', endDrag);
  }
  if (anns) anns.addEventListener('click', function (e) {
    var wrap = e.target.closest('[data-aid]'); if (!wrap) return;
    if (e.target.closest('.aup'))   { var p = wrap.previousElementSibling; if (p) { anns.insertBefore(wrap, p); syncWebFlag(wrap); sendAnnOrder(); } return; }
    if (e.target.closest('.adown')) { var n = wrap.nextElementSibling;     if (n) { anns.insertBefore(n, wrap); syncWebFlag(wrap); sendAnnOrder(); } return; }
  });

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

  // ── announcements (title/detail/video autosave; image upload; media panel) ──
  var anns = document.getElementById('anns');
  anns.addEventListener('input', function (e) {
    var inp = e.target.closest('input[data-af], textarea[data-af]'); if (!inp) return;
    var row = inp.closest('[data-aid]'); var id = row.getAttribute('data-aid'); var field = inp.getAttribute('data-af');
    debounce('ann' + id + field, function () { api('PATCH', '/bulletins/' + BID + '/announcements/' + id, (function(o){o[field]=inp.value;return o;})({})).then(function (res) { (res.ok && res.d && res.d.ok) ? savedPip() : errPip(res.d && res.d.message ? res.d.message : null); }); });
  });
  anns.addEventListener('change', function (e) {
    var fi = e.target.closest('.ann-img'); if (!fi) return;
    var wrap = fi.closest('[data-aid]'), id = wrap.getAttribute('data-aid');
    var f = fi.files && fi.files[0]; if (!f) return;
    var fd = new FormData(); fd.append('image', f);
    var state = wrap.querySelector('.ann-photo-state'); if (state) state.textContent = 'Uploading…';
    fetch('/bulletins/' + BID + '/announcements/' + id + '/image', { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: fd })
      .then(function (r) { return r.json(); }).then(function (d) {
        if (d.ok) {
          if (state) state.textContent = 'Image attached — tap to replace';
          var media = wrap.querySelector('.ann-media'), thumb = media.querySelector('.ann-thumb');
          if (!thumb) { thumb = document.createElement('img'); thumb.className = 'ann-thumb'; media.insertBefore(thumb, media.querySelector('.ann-img-remove')); }
          thumb.src = d.image_url + '?v=' + Date.now();
          var rm = media.querySelector('.ann-img-remove'); if (rm) rm.style.display = '';
          wrap.querySelector('.ann-media-toggle').classList.add('on'); savedPip();
        } else if (state) { state.textContent = 'Could not add that image'; }
      }).catch(function () { if (state) state.textContent = 'Upload error'; });
  });
  anns.addEventListener('click', function (e) {
    var wrap = e.target.closest('[data-aid]'); if (!wrap) return;
    if (e.target.closest('.ann-media-toggle')) { wrap.querySelector('.ann-media').classList.toggle('open'); return; }
    if (e.target.closest('.ann-img-remove')) {
      var rm = e.target.closest('.ann-img-remove');
      api('DELETE', rm.getAttribute('data-url')).then(function () {
        var t = wrap.querySelector('.ann-thumb'); if (t) t.remove(); rm.style.display = 'none';
        var st = wrap.querySelector('.ann-photo-state'); if (st) st.textContent = 'Add an image';
        if (!wrap.querySelector('[data-af="video_url"]').value) wrap.querySelector('.ann-media-toggle').classList.remove('on');
        savedPip();
      });
      return;
    }
    if (e.target.closest('.adel')) {
      window.shConfirm('Delete this announcement?', { okLabel: 'Delete', danger: true }).then(function (ok) {
        if (!ok) return; api('DELETE', '/bulletins/' + BID + '/announcements/' + wrap.getAttribute('data-aid')).then(function () { wrap.remove(); savedPip(); });
      });
      return;
    }
  });
  document.getElementById('add-ann').addEventListener('click', function () {
    api('POST', '/bulletins/' + BID + '/announcements', { title: '', is_web_only: 1 }).then(function (res) {
      if (res.ok && res.d.ok) {
        var a = res.d.announcement, div = document.createElement('div'); div.className = 'ann-wrap webonly'; div.setAttribute('data-aid', a.id);
        div.innerHTML =
          '<div class="item"><span class="drag" title="Drag to reorder"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg></span>' +
          '<div class="fields" style="grid-template-columns:1fr 1.6fr"><input data-af="title" value="" placeholder="Title"><textarea data-af="detail" rows="1" placeholder="Detail (Enter = new line \u2192 its own bullet)"></textarea></div>' +
          '<div class="ctrls"><button class="ic aup" title="Move up"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>' +
          '<button class="ic adown" title="Move down"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>' +
          '<button class="ic ann-media-toggle" title="Image / video"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></button>' +
          '<button class="ic adel" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg></button></div></div>' +
          '<div class="ann-media"><label class="ann-photo"><span class="ann-photo-state">Add an image</span><input type="file" class="ann-img" accept="image/*"></label>' +
          '<button type="button" class="ann-img-remove" data-url="/bulletins/' + BID + '/announcements/' + a.id + '/image" style="display:none">Remove image</button>' +
          '<label class="ann-vid-lbl">Video link <input data-af="video_url" value="" placeholder="YouTube or Vimeo URL"></label></div>';
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
<script>
/* Person-field autocomplete — names (and hymns / Bible books) from past bulletins.
   Solid by design: 150ms debounce, AbortController cancels stale requests,
   keyboard nav, selection re-fires the autosave, closes cleanly on blur/scroll. */
(function () {
  if (!document.querySelector('main[data-bid]')) return;
  var box = document.createElement('div'); box.className = 'ac'; document.body.appendChild(box);
  var cur = null, items = [], active = -1, ctrl = null, timer = null, suppress = false;

  function scopeFor(part) {
    var p = (part || '').toLowerCase();
    if (/\b(hymn|song)\b/.test(p)) return 'hymn';
    if (/\b(scripture|reading|responsive|text)\b/.test(p)) return 'bible';
    return 'person';
  }
  function esc(s) { return String(s).replace(/[<>&"]/g, function (c) { return { '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[c]; }); }
  function close() { box.classList.remove('show'); items = []; active = -1; cur = null; }
  function place(inp) { var r = inp.getBoundingClientRect(); box.style.left = r.left + 'px'; box.style.top = (r.bottom + 4) + 'px'; box.style.minWidth = r.width + 'px'; }
  function render() {
    box.innerHTML = items.map(function (it, i) { return '<div class="ac-item' + (i === active ? ' active' : '') + '" data-i="' + i + '">' + esc(it) + '</div>'; }).join('');
    box.classList.toggle('show', items.length > 0);
  }
  function norm(v) { return typeof v === 'string' ? v : (v && (v.label || v.name || v.book || v.title)) || ''; }

  function lookup(inp) {
    var q = inp.value.trim();
    if (q.length < 1) { close(); return; }
    var row = inp.closest('[data-id]');
    var partInp = row ? row.querySelector('input[data-f="part"]') : null;
    var scope = scopeFor(partInp ? partInp.value : '');
    if (ctrl) ctrl.abort();
    ctrl = new AbortController();
    fetch('/api/suggestions?q=' + encodeURIComponent(q) + '&scope=' + scope, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, signal: ctrl.signal })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var src = scope === 'hymn' ? d.hymns : (scope === 'bible' ? d.books : d.people);
        items = (src || []).map(norm).filter(Boolean).slice(0, 8);
        active = -1;
        if (cur === inp) { place(inp); render(); }
      })
      .catch(function () { /* aborted or network — ignore */ });
  }

  document.addEventListener('input', function (e) {
    var inp = e.target.closest('input[data-f="person"]');
    if (!inp) return;
    if (suppress) { suppress = false; return; }
    cur = inp; clearTimeout(timer);
    timer = setTimeout(function () { if (cur === inp) lookup(inp); }, 150);
  });
  document.addEventListener('keydown', function (e) {
    if (!box.classList.contains('show') || !cur) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); active = Math.min(items.length - 1, active + 1); render(); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); active = Math.max(0, active - 1); render(); }
    else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); pick(active); }
    else if (e.key === 'Escape') { close(); }
  });
  box.addEventListener('mousedown', function (e) { var it = e.target.closest('.ac-item'); if (it) { e.preventDefault(); pick(parseInt(it.getAttribute('data-i'), 10)); } });
  function pick(i) {
    if (!cur || !items[i]) return;
    var inp = cur;
    inp.value = items[i];
    close();
    suppress = true;                    // don't reopen on the synthetic input
    inp.dispatchEvent(new Event('input', { bubbles: true })); // fires the autosave
    inp.focus();
  }
  document.addEventListener('focusout', function () { setTimeout(function () { if (!box.contains(document.activeElement)) close(); }, 120); });
  window.addEventListener('scroll', close, true);
  window.addEventListener('resize', close);
})();
</script>
</body>
</html>
