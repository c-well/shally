<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Music day — Admin · The Church of Peace</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top .meta { font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 760px; margin: 0 auto; padding: clamp(40px, 7vh, 72px) clamp(20px, 5vw, 32px) 96px; }
  .lede { margin-top: 14px; font-size: 15px; line-height: 1.6; color: var(--ink-soft); max-width: 560px; }

  /* Days — a row of pills that scrolls by itself rather than widening the page. */
  .days { margin-top: 30px; display: flex; gap: 8px; overflow-x: auto; overscroll-behavior-x: contain; scrollbar-width: none; padding-bottom: 2px; }
  .days::-webkit-scrollbar { display: none; }
  .day { flex-shrink: 0; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--line); background: #fff; color: var(--ink); text-decoration: none; font-size: 13px; font-weight: 600; white-space: nowrap; }
  .day small { font-weight: 500; color: var(--ink-soft); margin-left: 6px; }
  .day.on { background: var(--ink); color: var(--parchment); border-color: var(--ink); }
  .day.on small { color: inherit; opacity: .7; }
  .day.new { border-style: dashed; color: var(--teal); cursor: pointer; font: inherit; font-size: 13px; font-weight: 600; }

  .newform { display: none; margin-top: 12px; padding: 16px; background: #fff; border: 1px solid var(--line); border-radius: 10px; gap: 10px; flex-wrap: wrap; align-items: end; }
  .newform.open { display: flex; }
  .newform label { display: flex; flex-direction: column; gap: 6px; flex: 1 1 180px; }
  .newform input { font: inherit; font-size: 16px; padding: 11px 12px; border: 1px solid var(--line); border-radius: 8px; background: var(--parchment); color: var(--ink); width: 100%; }

  .btn { font: inherit; font-size: 12px; font-weight: 600; letter-spacing: 0.04em; padding: 11px 15px; border-radius: 8px; border: 1px solid var(--line); background: #fff; color: var(--teal); cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
  .btn:hover { border-color: var(--teal); }
  .btn.solid { background: var(--teal); border-color: var(--teal); color: #fff; }
  .btn.quiet { color: var(--ink-soft); }
  .btn svg { width: 15px; height: 15px; flex-shrink: 0; }

  /* The link — the one thing André needs to get out to people. */
  .linkcard { margin-top: 26px; padding: 18px 18px 16px; background: #fff; border: 1px solid var(--line); border-radius: 12px; }
  .linkhead { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
  .linkhead b { font-size: 15px; }
  .state { font-size: 12px; font-weight: 600; padding: 3px 9px; border-radius: 999px; }
  .state.open { background: color-mix(in srgb, var(--teal) 12%, transparent); color: var(--teal); }
  .state.shut { background: rgba(26,35,50,.08); color: var(--ink-soft); }
  .url { margin-top: 12px; padding: 11px 12px; background: var(--parchment); border-radius: 8px; font-size: 13px; color: var(--ink-soft); overflow-wrap: anywhere; user-select: all; }
  .linkacts { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }

  .summary { margin-top: 30px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
  .counts { display: flex; gap: 16px; flex-wrap: wrap; font-size: 13px; color: var(--ink-soft); }
  .counts b { color: var(--ink); font-variant-numeric: tabular-nums; font-size: 15px; margin-right: 4px; }

  .list { margin-top: 14px; display: grid; gap: 10px; }
  .card { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 14px 14px 14px 12px; display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 4px 12px; }
  .pos { grid-row: 1 / span 3; width: 30px; height: 30px; border-radius: 8px; background: var(--parchment); display: grid; place-items: center; font-size: 13px; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--ink-soft); }
  .who { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; min-width: 0; }
  .who .nm { font-size: 13px; font-weight: 600; }
  .who .dur { font-size: 12px; color: var(--ink-soft); font-variant-numeric: tabular-nums; }
  /* A textarea, not an input — a long hymn title has to wrap, not get cut off. */
  /* min-height 0 beats admin.css, whose phone rule gives every textarea 7.5rem for message boxes. */
  .song { min-height: 0; resize: none; overflow: hidden; field-sizing: content; display: block; font-size: 17px; line-height: 1.3; border: 1px solid transparent; border-radius: 6px; padding: 2px 6px; margin-left: -6px; background: transparent; color: var(--ink); width: calc(100% + 6px); font-family: inherit; }
  .song:hover { border-color: var(--line); }
  .song:focus { outline: none; border-color: var(--teal); background: var(--parchment); }
  .row2 { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
  .pill { font-size: 11px; font-weight: 700; letter-spacing: 0.04em; padding: 4px 9px; border-radius: 999px; white-space: nowrap; }
  .pill.ready { background: color-mix(in srgb, #2d8659 13%, transparent); color: #2d8659; }
  .pill.queued, .pill.fetching { background: color-mix(in srgb, var(--teal) 11%, transparent); color: var(--teal); }
  .pill.bringing { background: color-mix(in srgb, #b08d3c 16%, transparent); color: #8c6f2e; }
  .pill.failed { background: rgba(168,42,31,.1); color: #a82a1f; }
  .why { font-size: 12.5px; color: #a82a1f; flex-basis: 100%; }
  .card audio { grid-column: 2; width: 100%; height: 36px; margin-top: 8px; }
  .acts { grid-column: 2; display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
  .ico { width: 40px; height: 40px; border-radius: 8px; border: 1px solid var(--line); background: #fff; color: var(--ink-soft); display: inline-grid; place-items: center; cursor: pointer; text-decoration: none; }
  .ico:hover { color: var(--teal); border-color: var(--teal); }
  .ico.danger:hover { color: #a82a1f; border-color: #a82a1f; }
  .ico:disabled { opacity: .3; cursor: default; }
  .ico svg { width: 16px; height: 16px; }
  .acts .grow { flex: 1; }

  .empty { margin-top: 18px; padding: 40px 20px; text-align: center; border: 1px dashed var(--line); border-radius: 12px; color: var(--ink-soft); font-size: 14.5px; line-height: 1.6; }
  .toast { position: fixed; left: 50%; bottom: calc(24px + env(safe-area-inset-bottom)); transform: translateX(-50%) translateY(10px); background: var(--ink); color: var(--parchment); padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; opacity: 0; transition: opacity .2s, transform .2s; pointer-events: none; z-index: 50; }
  .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')
<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">music day</span>
</header>

<main>
  <h1>Music day.</h1>
  <p class="lede">Make a day, send the link to the singers, and watch the songs come in. Every track can come home in one download, numbered in running order.</p>

  <div class="days">
    @foreach ($days as $d)
      <a class="day @if ($day && $day->id === $d->id) on @endif" href="{{ route('admin.music', ['day' => $d->id]) }}">{{ $d->service_date->format('M j') }}<small>{{ $d->entries_count }}</small></a>
    @endforeach
    <button type="button" class="day new" id="newDay">+ New music day</button>
  </div>
  <form class="newform @if (! $day) open @endif" id="newForm" method="POST" action="{{ route('admin.music.store') }}">
    @csrf
    <label><span class="label">Date</span><input type="date" name="service_date" required value="{{ now('America/New_York')->next('Saturday')->toDateString() }}"></label>
    <label><span class="label">Name <small>optional</small></span><input type="text" name="title" maxlength="120" placeholder="Music Day"></label>
    <button class="btn solid" type="submit">Create</button>
  </form>

  @if ($day)
    @php
      $c = [
        'all'      => $entries->count(),
        'ready'    => $entries->where('status', 'ready')->count(),
        'bringing' => $entries->where('status', 'bringing')->count(),
        'coming'   => $entries->whereIn('status', ['queued', 'fetching'])->count(),
        'failed'   => $entries->where('status', 'failed')->count(),
      ];
    @endphp

    <div class="linkcard" data-day="{{ $day->id }}">
      <div class="linkhead">
        <b>{{ $day->label() }}</b>
        <span class="state {{ $day->is_open ? 'open' : 'shut' }}" id="dayState">{{ $day->is_open ? 'Taking songs' : 'Closed' }}</span>
      </div>
      <div class="url" id="dayUrl">{{ $day->link() }}</div>
      <div class="linkacts">
        <button class="btn solid" type="button" id="copyLink">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>Copy link</button>
        <a class="btn" href="sms:?&body={{ rawurlencode('Hi! Please send me your song for ' . $day->title . ' (' . $day->service_date->format('M j') . ') here: ' . $day->link()) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Text it</a>
        <a class="btn quiet" href="{{ $day->link() }}" target="_blank" rel="noopener">See what they see</a>
        <button class="btn quiet" type="button" id="toggleDay">{{ $day->is_open ? 'Stop taking songs' : 'Take songs again' }}</button>
      </div>
    </div>

    <div class="summary">
      <div class="counts">
        <span><b>{{ $c['all'] }}</b>{{ $c['all'] === 1 ? 'singer' : 'singers' }}</span>
        <span><b>{{ $c['ready'] }}</b>tracks in</span>
        @if ($c['bringing'])<span><b>{{ $c['bringing'] }}</b>bringing it</span>@endif
        @if ($c['coming'])<span><b>{{ $c['coming'] }}</b>still coming</span>@endif
        @if ($c['failed'])<span><b>{{ $c['failed'] }}</b>need a look</span>@endif
      </div>
      @if ($c['ready'])
        <a class="btn" href="{{ route('admin.music.zip', $day) }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>Download all tracks</a>
      @endif
    </div>

    @if ($entries->isEmpty())
      <div class="empty">No songs yet. Copy the link above and send it to the singers.<br>Each one appears here as it comes in.</div>
    @else
      <div class="list" id="list">
        @foreach ($entries as $i => $e)
          <div class="card" data-id="{{ $e->id }}" data-status="{{ $e->status }}">
            <div class="pos">{{ $i + 1 }}</div>
            <div class="who"><span class="nm">{{ $e->name }}</span><span class="dur">{{ $e->durationLabel() }}</span></div>
            <textarea class="song" rows="1" placeholder="Song title" maxlength="200" aria-label="Song title for {{ $e->name }}">{{ $e->song_title }}</textarea>
            <div class="row2" style="grid-column:2">
              <span class="pill {{ $e->status }}">{{ $e->statusLabel() }}</span>
              @if ($e->source_url)<a href="{{ $e->source_url }}" target="_blank" rel="noopener" style="font-size:12.5px;color:var(--ink-soft)">YouTube link</a>@endif
              @if ($e->error && $e->status !== 'ready')<span class="why">{{ $e->error }}</span>@endif
            </div>
            @if ($e->hasTrack())
              <audio controls preload="none" src="{{ route('admin.music.track', $e) }}"></audio>
            @endif
            <div class="acts">
              <button class="ico" type="button" data-move="-1" aria-label="Move up" @disabled($i === 0)><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></button>
              <button class="ico" type="button" data-move="1" aria-label="Move down" @disabled($i === $entries->count() - 1)><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
              <span class="grow"></span>
              @if ($e->method === 'bringing')
                <button class="btn quiet" type="button" data-received>{{ $e->status === 'ready' ? 'Not received' : 'Got it' }}</button>
              @endif
              @if ($e->status === 'failed')
                <button class="btn quiet" type="button" data-retry>Try again</button>
              @endif
              @if ($e->hasTrack())
                <a class="ico" href="{{ route('admin.music.download', $e) }}" aria-label="Download"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg></a>
              @endif
              <button class="ico danger" type="button" data-remove aria-label="Remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg></button>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  @endif
</main>
<div class="toast" id="toast"></div>
@include('partials._confirm')

<script>
(function () {
  var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
  function api(method, url, body) {
    return fetch(url, { method: method, credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
      body: body ? JSON.stringify(body) : undefined
    }).then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, d: d }; }); });
  }
  var toastEl = document.getElementById('toast'), toastT;
  function toast(msg) { toastEl.textContent = msg; toastEl.classList.add('show'); clearTimeout(toastT); toastT = setTimeout(function () { toastEl.classList.remove('show'); }, 2200); }
  function ask(msg, label) { return window.shConfirm ? window.shConfirm(msg, { okLabel: label, danger: true }) : Promise.resolve(confirm(msg)); }

  var nd = document.getElementById('newDay');
  if (nd) nd.addEventListener('click', function () { document.getElementById('newForm').classList.toggle('open'); });

  var card = document.querySelector('.linkcard');
  if (!card) return;
  var DAY = card.getAttribute('data-day');

  document.getElementById('copyLink').addEventListener('click', function () {
    var url = document.getElementById('dayUrl').textContent.trim();
    (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () { toast('Link copied'); }, function () {
      var r = document.createRange(); r.selectNodeContents(document.getElementById('dayUrl'));
      var s = getSelection(); s.removeAllRanges(); s.addRange(r); toast('Selected — copy it');
    });
  });

  document.getElementById('toggleDay').addEventListener('click', function (e) {
    var btn = e.currentTarget;
    api('POST', '/admin/music/days/' + DAY + '/toggle').then(function (res) {
      if (!res.ok) return toast('Could not change that');
      var open = res.d.is_open, st = document.getElementById('dayState');
      st.textContent = open ? 'Taking songs' : 'Closed'; st.className = 'state ' + (open ? 'open' : 'shut');
      btn.textContent = open ? 'Stop taking songs' : 'Take songs again';
      toast(open ? 'Taking songs again' : 'No more songs will be accepted');
    });
  });

  var list = document.getElementById('list');
  if (!list) return;

  function renumber() {
    var cards = list.querySelectorAll('.card');
    cards.forEach(function (c, i) {
      c.querySelector('.pos').textContent = i + 1;
      c.querySelector('[data-move="-1"]').disabled = i === 0;
      c.querySelector('[data-move="1"]').disabled = i === cards.length - 1;
    });
  }
  function saveOrder() {
    var ids = Array.prototype.map.call(list.querySelectorAll('.card'), function (c) { return +c.getAttribute('data-id'); });
    api('POST', '/admin/music/days/' + DAY + '/order', { ids: ids }).then(function (r) { if (!r.ok) toast('Order not saved — try again'); });
  }

  list.addEventListener('click', function (e) {
    var c = e.target.closest('.card'); if (!c) return;
    var id = c.getAttribute('data-id');
    var mv = e.target.closest('[data-move]');
    if (mv) {
      var dir = +mv.getAttribute('data-move');
      var sib = dir < 0 ? c.previousElementSibling : c.nextElementSibling;
      if (!sib) return;
      if (dir < 0) list.insertBefore(c, sib); else list.insertBefore(sib, c);
      renumber(); saveOrder(); mv.focus();
      return;
    }
    if (e.target.closest('[data-remove]')) {
      var nm = c.querySelector('.nm').textContent;
      ask('Remove ' + nm + "'s song? The track file goes too.", 'Remove').then(function (ok) {
        if (!ok) return;
        api('DELETE', '/admin/music/entries/' + id).then(function (r) { if (r.ok) { c.remove(); renumber(); toast('Removed'); } });
      });
      return;
    }
    if (e.target.closest('[data-retry]')) {
      api('POST', '/admin/music/entries/' + id + '/retry').then(function (r) { if (r.ok) location.reload(); });
      return;
    }
    var rc = e.target.closest('[data-received]');
    if (rc) {
      api('POST', '/admin/music/entries/' + id + '/received').then(function (r) {
        if (!r.ok) return;
        var p = c.querySelector('.pill');
        p.className = 'pill ' + (r.d.status === 'ready' ? 'ready' : 'bringing');
        p.textContent = r.d.status === 'ready' ? 'Received' : r.d.label;
        rc.textContent = r.d.status === 'ready' ? 'Not received' : 'Got it';
      });
    }
  });

  /* Size each title to its text, and let Enter finish the edit instead of adding a line. */
  function fit(t) { t.style.height = 'auto'; t.style.height = (t.scrollHeight + t.offsetHeight - t.clientHeight) + 'px'; }
  list.querySelectorAll('.song').forEach(fit);
  list.addEventListener('input', function (e) { if (e.target.classList.contains('song')) fit(e.target); });
  list.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.classList.contains('song')) { e.preventDefault(); e.target.blur(); }
  });

  list.addEventListener('change', function (e) {
    if (!e.target.classList.contains('song')) return;
    var id = e.target.closest('.card').getAttribute('data-id');
    api('PATCH', '/admin/music/entries/' + id, { song_title: e.target.value.trim() }).then(function (r) { toast(r.ok ? 'Saved' : 'Not saved'); });
  });

  /* While YouTube links are being pulled, check back so the card turns over by itself. */
  function pending() { return list.querySelector('.card[data-status=queued], .card[data-status=fetching]'); }
  if (pending()) {
    var poll = setInterval(function () {
      api('GET', '/admin/music/days/' + DAY + '/state').then(function (r) {
        if (!r.ok) return;
        var changed = r.d.entries.some(function (e) {
          var c = list.querySelector('.card[data-id="' + e.id + '"]');
          return c && c.getAttribute('data-status') !== e.status;
        });
        if (changed) { clearInterval(poll); location.reload(); }
      });
    }, 8000);
  }
})();
</script>
</body>
</html>
