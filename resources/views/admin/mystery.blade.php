<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Undercover — question bank — Admin</title>
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
  .lede { margin-top: 12px; font-size: 14.5px; color: var(--ink-soft); max-width: 600px; line-height: 1.55; }
  .lede b { color: var(--ink); }
  .note { margin-top: 14px; padding: 12px 15px; background: color-mix(in srgb, var(--brass) 9%, transparent); border-left: 3px solid var(--brass, #b08d35); border-radius: 0 6px 6px 0; font-size: 13.5px; line-height: 1.55; color: var(--ink-soft); }
  .flash { margin-top: 18px; padding: 11px 15px; background: color-mix(in srgb, var(--teal) 9%, transparent); border-left: 3px solid var(--teal); border-radius: 0 6px 6px 0; font-size: 14px; }

  label { display: block; font-size: 10px; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 5px; }
  input, select, textarea { font: inherit; font-size: 14px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 7px; background: #fff; color: var(--ink); width: 100%; }
  textarea { min-height: 52px; resize: vertical; line-height: 1.5; }
  input:focus, select:focus, textarea:focus { border-color: var(--teal); outline: none; box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }
  .check { display: flex; align-items: center; gap: 8px; }
  .check input { width: auto; }
  .check label { margin: 0; letter-spacing: 0.04em; text-transform: none; font-size: 13px; font-weight: 500; color: var(--ink); }

  .add { margin-top: 26px; background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 22px; }
  .add h2 { font-family: 'IBM Plex Serif'; font-size: 22px; font-weight: 500; margin-bottom: 16px; }
  .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
  @media (max-width:640px){ .row { grid-template-columns: 1fr; } }
  .btn { font: inherit; font-size: 13px; font-weight: 600; padding: 12px 22px; border: 0; border-radius: 8px; background: var(--teal); color: #fff; cursor: pointer; }
  .btn:hover { background: var(--teal-dark); }
  .hint { font-size: 11px; color: var(--ink-soft); margin-top: 5px; }

  .sec-lbl { margin: 34px 0 12px; font-size: 11px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); }
  .q { background: #fff; border: 1px solid var(--line); border-radius: 11px; padding: 16px 18px; margin-bottom: 11px; }
  .q.off { opacity: .55; }
  .q .hd { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 10px; }
  .badge { font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; padding: 3px 8px; border-radius: 4px; background: color-mix(in srgb, var(--teal) 12%, transparent); color: var(--teal-dark); }
  .badge.muted { background: color-mix(in srgb, var(--ink) 8%, transparent); color: var(--ink-soft); }
  .q .hd .sp { flex: 1; }
  .mini { font: inherit; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; padding: 6px 10px; border: 1px solid var(--line); border-radius: 6px; background: transparent; color: var(--ink-soft); cursor: pointer; }
  .mini.on { background: color-mix(in srgb, var(--green) 14%, transparent); color: var(--green); border-color: transparent; }
  .mini.danger:hover { color: var(--red); border-color: var(--red); }
  .q .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  @media (max-width:640px){ .q .grid2 { grid-template-columns: 1fr; } }
  .q .full { margin-top: 10px; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<div class="top"><a href="{{ route('admin.hub') }}">← Admin</a><span class="meta">UNDERCOVER · QUESTION BANK</span></div>
<main>
  <h1>Undercover — question bank.</h1>
  <p class="lede">These are the questions the game asks each teen privately. A <b>clueable</b> answer can surface to the room (anonymized) as a clue to who's who. Pick a kind, write the prompt, list the options — it's ready for the next game night. Edits save as you type.</p>
  <p class="note">The game never asks anyone to lie. The Crook and Cop stay hidden simply by <b>staying silent</b> — the app does the concealing. Write questions that help kids know one another; keep anything about Scripture or faith true and gentle. (You own the content — the app won't invent it.)</p>
  @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif

  <form class="add" method="POST" action="{{ route('admin.mystery.store') }}">
    @csrf
    <h2>Add a question</h2>
    <div style="margin-bottom:12px"><label>Prompt</label><textarea name="prompt" placeholder="e.g. Where do you fall in your family?" required></textarea></div>
    <div class="row">
      <div><label>Kind</label><select name="kind"><option value="getknow">Get-to-know-you</option><option value="value">Value / would-you-rather</option><option value="scripture">Scripture / faith</option></select></div>
      <div class="check" style="align-self:end;padding-bottom:10px"><input type="checkbox" id="add_clue" name="clueable" value="1" checked><label for="add_clue">Can become a clue</label></div>
    </div>
    <div style="margin-bottom:14px"><label>Options</label><textarea name="options" placeholder="One per line — e.g.&#10;Oldest&#10;Middle&#10;Youngest&#10;Only child"></textarea><div class="hint">One choice per line. Leave blank for a short-answer question.</div></div>
    <button class="btn" type="submit">+ Add question</button>
  </form>

  <div class="sec-lbl">{{ $questions->count() }} questions</div>
  @foreach ($questions as $q)
    <div class="q {{ $q->is_active ? '' : 'off' }}" data-id="{{ $q->id }}">
      <div class="hd">
        <span class="badge">{{ ['getknow'=>'Get-to-know','value'=>'Value','scripture'=>'Scripture'][$q->kind] ?? $q->kind }}</span>
        <span class="badge muted">{{ is_array($q->options) ? count($q->options).' options' : 'short answer' }}</span>
        @if (!$q->clueable)<span class="badge muted">not a clue</span>@endif
        <span class="sp"></span>
        <button class="mini {{ $q->is_active ? 'on' : '' }}" data-toggle="{{ route('admin.mystery.toggle', $q) }}">{{ $q->is_active ? 'Live' : 'Hidden' }}</button>
        <button class="mini danger" data-delete="{{ route('admin.mystery.destroy', $q) }}" data-confirm="Delete this question?">Delete</button>
      </div>
      <div class="full"><label>Prompt</label><textarea data-f="prompt" data-url="{{ route('admin.mystery.update', $q) }}">{{ $q->prompt }}</textarea></div>
      <div class="grid2" style="margin-top:10px">
        <div><label>Kind</label><select data-f="kind" data-url="{{ route('admin.mystery.update', $q) }}">
          <option value="getknow" @selected($q->kind==='getknow')>Get-to-know-you</option>
          <option value="value" @selected($q->kind==='value')>Value / would-you-rather</option>
          <option value="scripture" @selected($q->kind==='scripture')>Scripture / faith</option>
        </select></div>
        <div class="check" style="align-self:end;padding-bottom:10px"><input type="checkbox" data-f="clueable" data-url="{{ route('admin.mystery.update', $q) }}" @checked($q->clueable)><label>Can become a clue</label></div>
      </div>
      <div class="full"><label>Options (one per line, blank = short answer)</label><textarea data-f="options" data-url="{{ route('admin.mystery.update', $q) }}">{{ is_array($q->options) ? implode("\n", $q->options) : '' }}</textarea></div>
    </div>
  @endforeach
</main>

@include('partials._confirm')
<script>
(function () {
  var token = document.querySelector('meta[name=csrf-token]').content;
  function send(url, method, body) { return fetch(url, { method: method, headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: body ? JSON.stringify(body) : null }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); }); }
  var t = {};
  function save(f) {
    var id = f.closest('[data-id]').getAttribute('data-id'), field = f.getAttribute('data-f'), url = f.getAttribute('data-url');
    var val = f.type === 'checkbox' ? (f.checked ? 1 : 0) : f.value;
    clearTimeout(t[id + field]); t[id + field] = setTimeout(function () { var b = {}; b[field] = val; send(url, 'PATCH', b).then(function(){ window.shToast && window.shToast('Saved'); }); }, f.type === 'checkbox' || f.tagName === 'SELECT' ? 0 : 500);
  }
  document.addEventListener('input', function (e) { var f = e.target.closest('[data-f]'); if (f) save(f); });
  document.addEventListener('change', function (e) { var f = e.target.closest('[data-f]'); if (f && (f.type === 'checkbox' || f.tagName === 'SELECT')) save(f); });
  document.addEventListener('click', function (e) {
    var tg = e.target.closest('[data-toggle]');
    if (tg) { send(tg.getAttribute('data-toggle'), 'POST').then(function (res) { if (res.ok && res.d.ok) { var on = res.d.is_active; tg.textContent = on ? 'Live' : 'Hidden'; tg.classList.toggle('on', on); tg.closest('[data-id]').classList.toggle('off', !on); } }); return; }
    var del = e.target.closest('[data-delete]');
    if (del) { window.shConfirm(del.getAttribute('data-confirm'), { okLabel: 'Delete', danger: true }).then(function (ok) { if (!ok) return; send(del.getAttribute('data-delete'), 'DELETE').then(function (res) { if (res.ok && res.d.ok) { var row = del.closest('[data-id]'); row.style.opacity = 0; setTimeout(function(){ row.remove(); }, 200); } }); }); return; }
  });
})();
</script>
</body>
</html>
