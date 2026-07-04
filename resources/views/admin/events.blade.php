<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Events — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 900px; margin: 0 auto; padding: clamp(34px, 6vh, 60px) clamp(20px, 5vw, 32px) 90px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(24px, 3.4vw, 32px); font-weight: 500; letter-spacing: 0.02em; text-transform: uppercase; }
  .lede { margin-top: 12px; font-size: 14.5px; line-height: 1.6; color: var(--ink-soft); max-width: 600px; }

  /* Quick add */
  .qa { margin-top: 30px; background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 24px; display: grid; grid-template-columns: 1fr 300px; gap: 26px; }
  @media (max-width: 720px) { .qa { grid-template-columns: 1fr; gap: 20px; } }
  .qa-head { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
  .qa-head h2 { font-family: 'Instrument Sans', sans-serif; font-size: 26px; font-weight: 500; }
  .pill { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; padding: 6px 12px; border-radius: 999px; background: color-mix(in srgb, var(--ink-soft) 12%, transparent); color: var(--ink-soft); white-space: nowrap; }
  .pill.live { background: color-mix(in srgb, var(--teal) 15%, transparent); color: var(--teal-dark); }
  .field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 16px; }
  .field label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-soft); }
  .field input { font: inherit; font-size: 15px; padding: 12px 14px; border: 1px solid var(--line); border-radius: 7px; background: #fff; color: var(--ink); width: 100%; }
  .field input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); outline: none; }
  .row2 { display: grid; grid-template-columns: 1.3fr 1fr; gap: 12px; }
  .flyer-zone { border: 1.5px dashed var(--line); border-radius: 8px; padding: 16px; text-align: center; cursor: pointer; font-size: 13px; color: var(--ink-soft); transition: border-color .15s, background .15s; }
  .flyer-zone:hover { border-color: var(--teal); background: color-mix(in srgb, var(--teal) 3%, #fff); }
  .flyer-zone.disabled { opacity: .5; cursor: not-allowed; }
  .flyer-zone input { display: none; }
  .flyer-zone b { color: var(--teal); }
  .flyer-zone img { max-height: 120px; border-radius: 6px; margin: 0 auto 8px; display: block; }

  /* Live preview */
  .preview-wrap { display: flex; flex-direction: column; gap: 10px; }
  .preview-lbl { font-family: 'Instrument Sans', sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-soft); }
  .pv-card { border: 1px solid var(--line); border-radius: 10px; overflow: hidden; background: var(--parchment); }
  .pv-tile { background: var(--teal); color: #fff; text-align: center; padding: 12px 0; }
  .pv-tile .m { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.2em; }
  .pv-tile .d { font-family: 'Instrument Sans', sans-serif; font-size: 46px; line-height: 1; font-weight: 500; }
  .pv-tile .w { font-family: 'Instrument Sans', sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0.2em; opacity: .85; }
  .pv-body { padding: 14px 16px; }
  .pv-title { font-family: 'Instrument Sans', sans-serif; font-size: 22px; font-weight: 500; color: var(--ink); }
  .pv-time { font-size: 12.5px; color: var(--ink-soft); margin-top: 3px; }
  .pv-flyer { width: 100%; border-top: 1px solid var(--line); display: block; }
  .pv-empty { padding: 28px 16px; text-align: center; font-size: 12.5px; color: var(--ink-soft); font-style: italic; }

  .qa-actions { grid-column: 1 / -1; display: flex; gap: 12px; align-items: center; }
  .btn { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; border-radius: 7px; padding: 12px 20px; cursor: pointer; border: 1px solid var(--line); background: transparent; color: var(--teal); }
  .btn:hover { border-color: var(--teal); }
  .btn-solid { background: var(--teal); color: #fff; border-color: var(--teal); }
  .btn-solid:hover { background: var(--teal-dark); }
  .hint { font-size: 12.5px; color: var(--ink-soft); }

  /* Event list */
  .sec-lbl { margin: 40px 0 14px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); }
  .ev { display: flex; align-items: stretch; gap: 0; background: #fff; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; margin-bottom: 12px; }
  .ev.hidden-ev { opacity: .62; }
  .ev-tile { flex-shrink: 0; width: 84px; background: color-mix(in srgb, var(--teal) 8%, #fff); border-right: 1px solid var(--line); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 8px 0; }
  .ev-tile .m { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.16em; color: var(--teal-dark); }
  .ev-tile .d { font-family: 'Instrument Sans', sans-serif; font-size: 34px; line-height: 1; color: var(--ink); }
  .ev-tile .w { font-family: 'Instrument Sans', sans-serif; font-size: 8px; font-weight: 700; letter-spacing: 0.16em; color: var(--ink-soft); }
  .ev-main { flex: 1; padding: 14px 16px; min-width: 0; }
  .ev-title { font-family: 'Instrument Sans', sans-serif; font-size: 22px; font-weight: 500; color: var(--ink); }
  .ev-meta { font-size: 12.5px; color: var(--ink-soft); margin-top: 2px; }
  .ev-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; align-items: center; }
  .mini { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; border-radius: 6px; padding: 7px 11px; cursor: pointer; border: 1px solid var(--line); background: transparent; color: var(--ink-soft); }
  .mini:hover { border-color: var(--teal); color: var(--teal); }
  .mini.on { background: color-mix(in srgb, var(--teal) 14%, transparent); color: var(--teal-dark); border-color: transparent; }
  .mini.danger:hover { border-color: #c0392b; color: #c0392b; }
  .ev-thumb { flex-shrink: 0; width: 96px; border-left: 1px solid var(--line); background: var(--parchment) center/cover no-repeat; }
  .empty { padding: 30px; text-align: center; background: #fff; border: 1px dashed var(--line); border-radius: 10px; color: var(--ink-soft); font-style: italic; }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<div class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">EVENTS</span>
</div>
<main>
  <h1>Events.</h1>
  <p class="lede">Add an event in seconds — a name, a date, a flyer if you have one. It goes on the website the moment you fill in the basics. You can take anything off the site anytime.</p>

  {{-- Quick add --}}
  <div class="qa" id="qa">
    <div class="qa-head">
      <h2>New event</h2>
      <span class="pill" id="status">Start with a name &amp; date</span>
    </div>
    <div>
      <div class="field">
        <label for="ev-name">Event name</label>
        <input type="text" id="ev-name" maxlength="180" placeholder="e.g. Vacation Bible School" autocomplete="off">
      </div>
      <div class="row2">
        <div class="field">
          <label for="ev-date">Date</label>
          <input type="date" id="ev-date">
        </div>
        <div class="field">
          <label for="ev-time">Time <span style="opacity:.6;font-weight:500">(optional)</span></label>
          <input type="time" id="ev-time">
        </div>
      </div>
      <div class="field">
        <label>Flyer <span style="opacity:.6;font-weight:500">(optional)</span></label>
        <label class="flyer-zone disabled" id="flyer-zone">
          <span id="flyer-text"><b>Add the basics first</b>, then tap to add a flyer</span>
          <input type="file" id="ev-flyer" accept="image/*,application/pdf">
        </label>
      </div>
    </div>
    <div class="preview-wrap">
      <span class="preview-lbl">Live preview</span>
      <div class="pv-card" id="pv-card">
        <div class="pv-empty" id="pv-empty">Your event will appear here as you type.</div>
        <div id="pv-real" style="display:none">
          <div class="pv-tile"><div class="m" id="pv-m">JUN</div><div class="d" id="pv-d">1</div><div class="w" id="pv-w">SAT</div></div>
          <div class="pv-body"><div class="pv-title" id="pv-title">Event name</div><div class="pv-time" id="pv-time"></div></div>
          <img class="pv-flyer" id="pv-flyer" style="display:none" alt="">
        </div>
      </div>
    </div>
    <div class="qa-actions">
      <button class="btn btn-solid" id="add-another" type="button" style="display:none">✓ Done — add another</button>
      <span class="hint" id="hint">Fill in a name and date and it saves automatically.</span>
    </div>
  </div>

  {{-- Upcoming --}}
  <div class="sec-lbl">Upcoming</div>
  <div id="upcoming-list">
    @forelse ($upcoming as $e)
      @include('admin.partials._event-row', ['e' => $e])
    @empty
      <div class="empty">No upcoming events yet. Add one above.</div>
    @endforelse
  </div>

  @if ($past->isNotEmpty())
    <div class="sec-lbl">Recently past</div>
    <div id="past-list">
      @foreach ($past as $e)
        @include('admin.partials._event-row', ['e' => $e])
      @endforeach
    </div>
  @endif
</main>

@include('partials._confirm')
<script>
(function () {
  var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
  var MONTHS = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
  var DOWS = ['SUN','MON','TUE','WED','THU','FRI','SAT'];

  function api(method, url, body, isForm) {
    var opts = { method: method, headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } };
    if (body && !isForm) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    if (body && isForm) { opts.body = body; }
    return fetch(url, opts).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); });
  }

  // ── Quick add ───────────────────────────────────────────────────────
  var nameEl = document.getElementById('ev-name');
  var dateEl = document.getElementById('ev-date');
  var timeEl = document.getElementById('ev-time');
  var flyerInput = document.getElementById('ev-flyer');
  var flyerZone = document.getElementById('flyer-zone');
  var statusEl = document.getElementById('status');
  var addAnother = document.getElementById('add-another');
  var currentId = null, saveTimer = null;

  function startAt() {
    if (!dateEl.value) return null;
    return dateEl.value + 'T' + (timeEl.value || '00:00') + ':00';
  }
  function updatePreview() {
    var hasAny = nameEl.value.trim() || dateEl.value;
    document.getElementById('pv-empty').style.display = hasAny ? 'none' : '';
    document.getElementById('pv-real').style.display = hasAny ? '' : 'none';
    document.getElementById('pv-title').textContent = nameEl.value.trim() || 'Event name';
    if (dateEl.value) {
      var d = new Date(dateEl.value + 'T00:00:00');
      document.getElementById('pv-m').textContent = MONTHS[d.getMonth()];
      document.getElementById('pv-d').textContent = d.getDate();
      document.getElementById('pv-w').textContent = DOWS[d.getDay()];
    }
    var t = '';
    if (timeEl.value) { var hp = timeEl.value.split(':'); var h = +hp[0], ap = h >= 12 ? 'PM' : 'AM'; var h12 = (h % 12) || 12; t = h12 + ':' + hp[1] + ' ' + ap; }
    document.getElementById('pv-time').textContent = t;
  }
  function setStatus(live) {
    if (live) { statusEl.textContent = 'On the website ✓'; statusEl.classList.add('live'); addAnother.style.display = ''; flyerZone.classList.remove('disabled'); document.getElementById('flyer-text').innerHTML = '<b>Tap to add a flyer</b> — photo or PDF'; }
    else { statusEl.textContent = 'Start with a name & date'; statusEl.classList.remove('live'); }
  }
  function autosave() {
    var title = nameEl.value.trim(), sa = startAt();
    if (!title || !sa) return;
    if (!currentId) {
      api('POST', '/events', { title: title, start_at: sa }).then(function (res) {
        if (res.ok && res.d.ok) { currentId = res.d.event.id; setStatus(true); }
      });
    } else {
      api('PATCH', '/events/' + currentId, { title: title, start_at: sa });
    }
  }
  function queueSave() { clearTimeout(saveTimer); saveTimer = setTimeout(autosave, 650); updatePreview(); }
  nameEl.addEventListener('input', queueSave);
  dateEl.addEventListener('change', queueSave);
  timeEl.addEventListener('change', queueSave);

  flyerZone.addEventListener('click', function (e) { if (flyerZone.classList.contains('disabled')) { e.preventDefault(); window.shToast('Add a name and date first.'); } });
  flyerInput.addEventListener('change', function () {
    if (!currentId) return;
    var f = flyerInput.files && flyerInput.files[0]; if (!f) return;
    var fd = new FormData(); fd.append('flyer', f);
    document.getElementById('flyer-text').textContent = 'Uploading…';
    api('POST', '/events/' + currentId + '/flyer', fd, true).then(function (res) {
      if (res.ok && res.d.ok) {
        var img = document.getElementById('pv-flyer'); img.src = res.d.flyer_url; img.style.display = '';
        document.getElementById('flyer-text').innerHTML = '<b>Flyer added ✓</b> — tap to replace';
        window.shToast('Flyer added.');
      } else { document.getElementById('flyer-text').textContent = 'Could not add that file.'; }
    });
  });

  addAnother.addEventListener('click', function () { window.location.reload(); });

  // ── Event list actions (delegated) ──────────────────────────────────
  document.addEventListener('click', function (e) {
    var row = e.target.closest('[data-ev-id]');
    // toggle live/hidden
    var tog = e.target.closest('[data-toggle-live]');
    if (tog && row) {
      var id = row.getAttribute('data-ev-id');
      var makeLive = tog.getAttribute('data-toggle-live') === '1';
      api('PATCH', '/events/' + id, { is_public: makeLive }).then(function (res) {
        if (res.ok && res.d.ok) {
          row.classList.toggle('hidden-ev', !makeLive);
          tog.setAttribute('data-toggle-live', makeLive ? '0' : '1');
          tog.textContent = makeLive ? 'On the website' : 'Hidden — tap to show';
          tog.classList.toggle('on', makeLive);
          window.shToast(makeLive ? 'Back on the website.' : 'Taken off the website.');
        }
      });
      return;
    }
    // delete (with undo)
    var del = e.target.closest('[data-delete]');
    if (del && row) {
      var id2 = row.getAttribute('data-ev-id');
      window.shConfirm('Delete "' + del.getAttribute('data-title') + '"? You can undo right after.', { okLabel: 'Delete', danger: true }).then(function (ok) {
        if (!ok) return;
        api('DELETE', '/events/' + id2).then(function (res) {
          if (res.ok && res.d.ok) {
            row.innerHTML = '<div style="padding:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%">' +
              '<span style="color:var(--ink-soft);font-size:13px">Deleted.</span>' +
              '<button class="mini" data-undo="' + id2 + '">↺ Undo</button></div>';
            row.classList.add('hidden-ev');
          }
        });
      });
      return;
    }
    // undo delete
    var undo = e.target.closest('[data-undo]');
    if (undo) {
      api('POST', '/events/' + undo.getAttribute('data-undo') + '/restore').then(function (res) {
        if (res.ok && res.d.ok) { window.shToast('Event restored. Reloading…'); setTimeout(function(){ location.reload(); }, 600); }
      });
      return;
    }
    // remove flyer
    var rf = e.target.closest('[data-remove-flyer]');
    if (rf && row) {
      api('DELETE', '/events/' + row.getAttribute('data-ev-id') + '/flyer').then(function (res) {
        if (res.ok && res.d.ok) { var th = row.querySelector('.ev-thumb'); if (th) th.remove(); rf.remove(); window.shToast('Flyer removed.'); }
      });
      return;
    }
  });
  updatePreview();
})();
</script>
</body>
</html>
