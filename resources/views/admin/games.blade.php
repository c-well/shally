<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Scripture games — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 18px clamp(18px,5vw,36px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-size: 11px; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'IBM Plex Sans'; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); }
  main { max-width: 820px; margin: 0 auto; padding: clamp(28px,5vh,52px) clamp(18px,5vw,32px) 100px; }
  h1 { font-size: clamp(28px,4vw,40px); font-weight: 700; letter-spacing: -0.02em; }
  .lede { margin-top: 12px; font-size: 14.5px; color: var(--ink-soft); max-width: 560px; line-height: 1.55; }
  .flash { margin-top: 18px; padding: 11px 15px; background: color-mix(in srgb, var(--teal) 9%, transparent); border-left: 3px solid var(--teal); border-radius: 0 6px 6px 0; font-size: 14px; }

  label { display: block; font-size: 10px; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 5px; }
  input, select, textarea { font: inherit; font-size: 14px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 7px; background: #fff; color: var(--ink); width: 100%; }
  textarea { min-height: 56px; resize: vertical; line-height: 1.5; }
  input:focus, select:focus, textarea:focus { border-color: var(--teal); outline: none; box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }

  .add { margin-top: 28px; background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 22px; }
  .add h2 { font-family: 'IBM Plex Serif'; font-size: 22px; font-weight: 500; margin-bottom: 16px; }
  .row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px; }
  @media (max-width:640px){ .row { grid-template-columns: 1fr 1fr; } }
  .btn { font: inherit; font-size: 13px; font-weight: 600; padding: 12px 22px; border: 0; border-radius: 8px; background: var(--teal); color: #fff; cursor: pointer; }
  .btn:hover { background: var(--teal-dark); }

  .sec-lbl { margin: 36px 0 12px; font-size: 11px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); }
  .lvl { background: #fff; border: 1px solid var(--line); border-radius: 11px; padding: 16px 18px; margin-bottom: 11px; }
  .lvl.off { opacity: .55; }
  .lvl .hd { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 10px; }
  .badge { font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; padding: 3px 8px; border-radius: 4px; background: color-mix(in srgb, var(--teal) 12%, transparent); color: var(--teal-dark); }
  .lvl .hd .sp { flex: 1; }
  .mini { font: inherit; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; padding: 6px 10px; border: 1px solid var(--line); border-radius: 6px; background: transparent; color: var(--ink-soft); cursor: pointer; }
  .mini.on { background: color-mix(in srgb, var(--green) 14%, transparent); color: var(--green); border-color: transparent; }
  .mini.danger:hover { color: var(--red); border-color: var(--red); }
  .lvl .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .lvl .full { margin-top: 10px; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<div class="top"><a href="{{ route('admin.hub') }}">← Admin</a><span class="meta">SCRIPTURE GAMES</span></div>
<main>
  <h1>Scripture games.</h1>
  <p class="lede">Each level is a real verse. Add as many as you like — pick a book, a game, and an age, and it shows up for the kids at <b>/kids</b>. Edits save as you type.</p>
  @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif

  <form class="add" method="POST" action="{{ route('admin.games.store') }}">
    @csrf
    <h2>Add a level</h2>
    <div class="row">
      <div><label>Game</label><select name="game_type"><option value="word_search">Word search</option><option value="verse_tetris">Verse Tetris</option><option value="memory_match">Memory match</option><option value="hidden_words">Hidden words</option></select></div>
      <div><label>Age</label><select name="age_band"><option value="little">Little (4–6)</option><option value="older">Kids (7–9)</option><option value="teens">Teens</option></select></div>
      <div><label>Book</label><input name="book" placeholder="e.g. John" required></div>
    </div>
    <div class="row">
      <div><label>Reference</label><input name="reference" placeholder="e.g. John 3:16" required></div>
      <div><label>Title <span style="opacity:.5">(optional)</span></label><input name="title" placeholder="e.g. For God So Loved"></div>
      <div><label>Difficulty 1–5</label><input name="difficulty" type="number" min="1" max="5" value="1"></div>
    </div>
    <div style="margin-bottom:14px"><label>Verse text (KJV / public domain)</label><textarea name="verse_text" placeholder="Type the verse exactly as it should be learned…" required></textarea></div>
    <button class="btn" type="submit">+ Add level</button>
  </form>

  <div class="sec-lbl">{{ $levels->count() }} levels</div>
  @foreach ($levels as $l)
    <div class="lvl {{ $l->is_active ? '' : 'off' }}" data-id="{{ $l->id }}">
      <div class="hd">
        <span class="badge">{{ ['word_search'=>'Word search','verse_tetris'=>'Verse Tetris','memory_match'=>'Memory','hidden_words'=>'Hidden words'][$l->game_type] }}</span>
        <span class="badge">{{ ['little'=>'4–6','older'=>'7–9','teens'=>'Teens'][$l->age_band] ?? $l->age_band }}</span>
        <span class="sp"></span>
        <button class="mini {{ $l->is_active ? 'on' : '' }}" data-toggle="{{ route('admin.games.toggle', $l) }}" data-on="{{ $l->is_active ? 1 : 0 }}">{{ $l->is_active ? 'Live' : 'Hidden' }}</button>
        <button class="mini danger" data-delete="{{ route('admin.games.destroy', $l) }}" data-confirm="Delete this level ({{ $l->reference }})?">Delete</button>
      </div>
      <div class="grid2">
        <div><label>Book</label><input data-f="book" data-url="{{ route('admin.games.update', $l) }}" value="{{ $l->book }}"></div>
        <div><label>Reference</label><input data-f="reference" data-url="{{ route('admin.games.update', $l) }}" value="{{ $l->reference }}"></div>
      </div>
      <div class="full"><label>Title</label><input data-f="title" data-url="{{ route('admin.games.update', $l) }}" value="{{ $l->title }}"></div>
      <div class="full"><label>Verse</label><textarea data-f="verse_text" data-url="{{ route('admin.games.update', $l) }}">{{ $l->verse_text }}</textarea></div>
    </div>
  @endforeach
</main>

@include('partials._confirm')
<script>
(function () {
  var token = document.querySelector('meta[name=csrf-token]').content;
  function send(url, method, body) { return fetch(url, { method: method, headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: body ? JSON.stringify(body) : null }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); }); }
  var t = {};
  document.addEventListener('input', function (e) {
    var f = e.target.closest('[data-f]'); if (!f) return;
    var id = f.closest('[data-id]').getAttribute('data-id'), field = f.getAttribute('data-f'), url = f.getAttribute('data-url');
    clearTimeout(t[id + field]); t[id + field] = setTimeout(function () { var b = {}; b[field] = f.value; send(url, 'PATCH', b).then(function(){ window.shToast && window.shToast('Saved'); }); }, 550);
  });
  document.addEventListener('click', function (e) {
    var tg = e.target.closest('[data-toggle]');
    if (tg) { send(tg.getAttribute('data-toggle'), 'POST').then(function (res) { if (res.ok && res.d.ok) { var on = res.d.is_active; tg.setAttribute('data-on', on?1:0); tg.textContent = on ? 'Live' : 'Hidden'; tg.classList.toggle('on', on); tg.closest('[data-id]').classList.toggle('off', !on); } }); return; }
    var del = e.target.closest('[data-delete]');
    if (del) { window.shConfirm(del.getAttribute('data-confirm'), { okLabel: 'Delete', danger: true }).then(function (ok) { if (!ok) return; send(del.getAttribute('data-delete'), 'DELETE').then(function (res) { if (res.ok && res.d.ok) { var row = del.closest('[data-id]'); row.style.opacity = 0; setTimeout(function(){ row.remove(); }, 200); } }); }); return; }
  });
})();
</script>
</body>
</html>
