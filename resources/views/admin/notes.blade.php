<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Notes &amp; Keys — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment, #fefcef); color: var(--ink, #1a2332); font-family: 'Instrument Sans', system-ui, sans-serif; }
  .top { padding: 24px 28px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line, rgba(26,35,50,.12)); }
  .top a { font-size: 13.5px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft, #4a5568); padding: 10px 12px; margin: -10px -12px; }
  .top a:hover { color: var(--teal, #03617A); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 12.5px; color: var(--ink-soft); opacity: .65; }
  main { max-width: 720px; margin: 0 auto; padding: 34px 22px 120px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: 24px; font-weight: 500; }
  .lede { color: var(--ink-soft, #4a5568); font-size: 14px; margin-top: 8px; line-height: 1.6; }
  .addbtn { margin-top: 22px; font: 700 12px 'Instrument Sans'; letter-spacing: .12em; text-transform: uppercase; color: var(--teal, #03617A); background: #fff; border: 1px dashed var(--line, rgba(26,35,50,.2)); border-radius: 10px; padding: 15px 22px; width: 100%; cursor: pointer; }
  .addbtn:hover { border-color: var(--teal, #03617A); }
  .note { background: #fff; border: 1px solid var(--line, rgba(26,35,50,.12)); border-radius: 12px; padding: 16px 18px; margin-top: 14px; }
  .note input.ti { width: 100%; font: 700 16px 'Instrument Sans'; color: var(--ink); border: 1px solid transparent; background: transparent; border-radius: 7px; padding: 8px 10px; }
  .note textarea { width: 100%; font: 400 14px 'JetBrains Mono', monospace; line-height: 1.7; color: var(--ink); border: 1px solid transparent; background: var(--parchment, #fefcef); border-radius: 8px; padding: 12px 14px; margin-top: 8px; resize: vertical; min-height: 88px; }
  .note input.ti:focus, .note textarea:focus { outline: none; border-color: var(--teal, #03617A); background: #fff; }
  .note-foot { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
  .note-meta { font-size: 11px; color: var(--ink-soft); }
  .del { font: 700 10px 'Instrument Sans'; letter-spacing: .1em; text-transform: uppercase; color: #a33d3d; background: none; border: 1px solid var(--line); border-radius: 6px; padding: 7px 12px; cursor: pointer; }
  .del:hover { border-color: #a33d3d; }
  .pip { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); font-size: 12px; font-weight: 600; color: #fff; background: var(--teal, #03617A); padding: 8px 16px; border-radius: 8px; opacity: 0; transition: opacity .2s; pointer-events: none; }
  .pip.show { opacity: 1; } .pip.err { background: #a33d3d; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">notes &amp; keys · encrypted at rest</span>
</header>

<main>
  <h1>Notes &amp; Keys.</h1>
  <p class="lede">The private drawer — passwords, account details, anything the team needs but the public never sees. Bodies are encrypted in the database; only this signed-in page can read them. Autosaves as you type.</p>

  <button type="button" class="addbtn" id="add">+ New note</button>
  <div id="list">
    @foreach ($notes as $n)
      <div class="note" data-id="{{ $n->id }}" data-update="{{ route('admin.notes.update', $n) }}" data-delete="{{ route('admin.notes.destroy', $n) }}">
        <input class="ti" value="{{ $n->title }}" placeholder="Title">
        <textarea placeholder="The details…">{{ $n->body }}</textarea>
        <div class="note-foot">
          <span class="note-meta">{{ $n->author->name ?? '' }} · updated {{ $n->updated_at->diffForHumans() }}</span>
          <button type="button" class="del">Delete</button>
        </div>
      </div>
    @endforeach
  </div>
</main>
<div class="pip" id="pip">Saved</div>
@include('partials._confirm')
<script>
(function () {
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  const list = document.getElementById('list');
  const pip = document.getElementById('pip');
  const timers = {};
  function pipMsg(m, err) { pip.textContent = m; pip.classList.toggle('err', !!err); pip.classList.add('show'); clearTimeout(pip._t); pip._t = setTimeout(() => pip.classList.remove('show'), err ? 2800 : 1100); }
  async function api(method, url, body) {
    const r = await fetch(url, { method, headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) });
    return r.json();
  }
  list.addEventListener('input', e => {
    const note = e.target.closest('.note'); if (!note) return;
    const id = note.dataset.id;
    clearTimeout(timers[id]);
    timers[id] = setTimeout(async () => {
      try {
        const d = await api('PATCH', note.dataset.update, { title: note.querySelector('.ti').value, body: note.querySelector('textarea').value });
        d.ok ? pipMsg('Saved') : pipMsg('Not saved — try again', true);
      } catch (err) { pipMsg('Not saved — try again', true); }
    }, 500);
  });
  list.addEventListener('click', async e => {
    if (!e.target.matches('.del')) return;
    const note = e.target.closest('.note');
    if (!await window.shConfirm('Delete this note?', { okLabel: 'Delete', danger: true })) return;
    const d = await api('DELETE', note.dataset.delete, {});
    if (d.ok) { note.remove(); pipMsg('Deleted'); }
  });
  document.getElementById('add').addEventListener('click', async () => {
    const d = await api('POST', @json(route('admin.notes.store')), { title: 'New note', body: '—' });
    if (d.ok) location.reload();
  });
})();
</script>
</body>
</html>
