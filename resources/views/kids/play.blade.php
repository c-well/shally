<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $level->reference }} — Scripture Games</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; -webkit-tap-highlight-color: transparent; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .top { padding: 16px clamp(16px,4vw,28px); display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); }
  .top a { font-size: 12px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; }
  .top a:hover { color: var(--teal); }
  .who { font-size: 13px; color: var(--ink-soft); }
  .who b { color: var(--teal); }
  .star { color: var(--brass); }

  main { max-width: 720px; margin: 0 auto; padding: clamp(20px,4vh,40px) clamp(16px,4vw,28px) 80px; text-align: center; }
  .ref { font-size: 11px; font-weight: 600; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); }
  h1 { margin-top: 8px; font-family: 'IBM Plex Serif', serif; font-size: clamp(26px,5vw,38px); font-weight: 500; }
  .hint { margin-top: 10px; font-size: 15px; color: var(--ink-soft); }

  /* Word search */
  .ws { margin: 26px auto 0; display: inline-block; touch-action: none; user-select: none; }
  .ws-grid { display: grid; gap: 3px; }
  .cell { width: var(--cs); height: var(--cs); display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--line); border-radius: 7px; font-weight: 600; font-size: calc(var(--cs) * 0.46); color: var(--ink); cursor: pointer; transition: background .1s, color .1s, transform .1s; }
  .cell.sel { background: color-mix(in srgb, var(--teal) 22%, #fff); }
  .cell.found { background: var(--teal); color: #fff; border-color: var(--teal); }
  .words { margin: 22px auto 0; display: flex; flex-wrap: wrap; gap: 8px 12px; justify-content: center; max-width: 560px; }
  .word { font-size: 15px; font-weight: 600; color: var(--ink-soft); padding: 6px 12px; background: #fff; border: 1px solid var(--line); border-radius: 999px; transition: all .2s; }
  .word.got { color: #fff; background: var(--teal); border-color: var(--teal); text-decoration: line-through; }

  /* Name gate + win overlay */
  .ov { position: fixed; inset: 0; background: color-mix(in srgb, var(--ink) 55%, transparent); display: flex; align-items: center; justify-content: center; padding: 24px; z-index: 50; opacity: 0; pointer-events: none; transition: opacity .25s; }
  .ov.show { opacity: 1; pointer-events: auto; }
  .card { background: var(--parchment); border-radius: 18px; padding: 34px 30px; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 30px 70px -30px rgba(0,0,0,.5); transform: translateY(10px) scale(.98); transition: transform .28s; }
  .ov.show .card { transform: none; }
  .card h2 { font-family: 'IBM Plex Serif', serif; font-size: 28px; font-weight: 500; }
  .card p { margin-top: 12px; color: var(--ink-soft); line-height: 1.55; }
  .card .verse { margin-top: 18px; font-family: 'IBM Plex Serif', serif; font-style: italic; font-size: 19px; line-height: 1.5; color: var(--ink); }
  .card input { margin-top: 18px; width: 100%; font: inherit; font-size: 17px; padding: 13px 16px; border: 1px solid var(--line); border-radius: 10px; text-align: center; }
  .btn { margin-top: 18px; font: inherit; font-size: 15px; font-weight: 600; padding: 14px 30px; border: 0; border-radius: 10px; background: var(--teal); color: #fff; cursor: pointer; }
  .btn:hover { background: var(--teal-dark); }
  .btn-ghost { background: transparent; color: var(--teal); }
  .stars-big { font-size: 40px; letter-spacing: 6px; margin-top: 14px; }
  .pop { animation: pop .5s ease; }
  @keyframes pop { 0%{transform:scale(.6);opacity:0} 60%{transform:scale(1.12)} 100%{transform:scale(1)} }
  .soon { margin-top: 40px; padding: 30px; background: #fff; border: 1px dashed var(--line); border-radius: 14px; color: var(--ink-soft); }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a href="{{ route('kids') }}">← Games</a>
  <div class="who" id="who"></div>
</div>

<main>
  <div class="ref">{{ $level->reference }}</div>
  <h1>{{ $level->title ?: $level->book }}</h1>

  @if ($level->game_type === 'word_search')
    <p class="hint">Find every word hidden in the puzzle — then you'll see the whole verse.</p>
    <div class="ws"><div class="ws-grid" id="grid"></div></div>
    <div class="words" id="wordlist"></div>
  @else
    <div class="soon">This game is being built — check back soon.</div>
  @endif
</main>

{{-- Name gate --}}
<div class="ov" id="nameGate">
  <div class="card">
    <h2>Hi there! 👋</h2>
    <p>What should we call you? Your stars are saved so you can keep growing.</p>
    <input id="nameInput" type="text" maxlength="60" placeholder="Your name" autocomplete="off">
    <div><button class="btn" id="nameGo">Let's go →</button></div>
  </div>
</div>

{{-- Win --}}
<div class="ov" id="winOv">
  <div class="card">
    <div class="stars-big pop" id="winStars">⭐️⭐️⭐️</div>
    <h2>You did it!</h2>
    <p>You learned <b>{{ $level->reference }}</b></p>
    <div class="verse">“{{ $level->verse_text }}”</div>
    <div><a class="btn" href="{{ route('kids') }}">More games →</a></div>
    <div><button class="btn btn-ghost" onclick="location.reload()">Play again</button></div>
  </div>
</div>

@php $levelData = ['id' => $level->id, 'type' => $level->game_type, 'band' => $level->age_band, 'reference' => $level->reference]; @endphp
<script>
const LEVEL = @json($levelData);
const WORDS = @json($keywords);
const TOKEN = document.querySelector('meta[name=csrf-token]').content;

// ── Player identity (localStorage) ──────────────────────────────────
let player = null;
try { player = JSON.parse(localStorage.getItem('cop_kid') || 'null'); } catch (e) {}
function paintWho() { document.getElementById('who').innerHTML = player ? ('<b>' + player.name + '</b> · <span class="star">★</span> ' + (player.total_stars || 0)) : ''; }
function gate() {
  if (player && player.token) { paintWho(); start(); return; }
  document.getElementById('nameGate').classList.add('show');
  setTimeout(function () { document.getElementById('nameInput').focus(); }, 200);
}
document.getElementById('nameGo').addEventListener('click', register);
document.getElementById('nameInput').addEventListener('keydown', function (e) { if (e.key === 'Enter') register(); });
function register() {
  const name = document.getElementById('nameInput').value.trim(); if (!name) return;
  fetch('{{ route('kids.register') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ name: name }) })
    .then(r => r.json()).then(d => {
      if (d.ok) { player = { token: d.token, name: d.name, total_stars: 0 }; localStorage.setItem('cop_kid', JSON.stringify(player)); document.getElementById('nameGate').classList.remove('show'); paintWho(); start(); }
    });
}
function save(state, completed, stars) {
  if (!player) return Promise.resolve();
  return fetch('{{ route('kids.save') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ token: player.token, level_id: LEVEL.id, state: state, completed: !!completed, stars: stars || 0, score: (state && state.found ? state.found.length : 0) }) })
    .then(r => r.json()).then(d => { if (d.ok) { player.total_stars = d.total_stars; localStorage.setItem('cop_kid', JSON.stringify(player)); paintWho(); } });
}

// ── Word search ─────────────────────────────────────────────────────
function start() {
  if (LEVEL.type !== 'word_search') return;
  const size = LEVEL.band === 'teens' ? 13 : 11;
  const dirs = LEVEL.band === 'teens' ? [[0,1],[1,0],[1,1],[-1,1],[0,-1],[1,-1]] : [[0,1],[1,0],[1,1]];
  const words = WORDS.filter(w => w.length <= size);
  const built = buildGrid(words, size, dirs);
  renderGrid(built.grid, size);
  renderWords(built.placed);
  bindSelection(built, size);
}
function buildGrid(words, size, dirs) {
  const g = Array.from({length: size}, () => Array(size).fill(null));
  const placed = [];
  words.forEach(function (w) {
    for (let t = 0; t < 250; t++) {
      const d = dirs[(Math.random() * dirs.length) | 0];
      const r = (Math.random() * size) | 0, c = (Math.random() * size) | 0;
      const er = r + d[0] * (w.length - 1), ec = c + d[1] * (w.length - 1);
      if (er < 0 || er >= size || ec < 0 || ec >= size) continue;
      let ok = true;
      for (let i = 0; i < w.length; i++) { const cur = g[r + d[0]*i][c + d[1]*i]; if (cur !== null && cur !== w[i]) { ok = false; break; } }
      if (!ok) continue;
      for (let i = 0; i < w.length; i++) g[r + d[0]*i][c + d[1]*i] = w[i];
      placed.push(w); break;
    }
  });
  const A = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  for (let r = 0; r < size; r++) for (let c = 0; c < size; c++) if (g[r][c] === null) g[r][c] = A[(Math.random()*26)|0];
  return { grid: g, placed: placed };
}
function renderGrid(g, size) {
  const el = document.getElementById('grid');
  el.style.gridTemplateColumns = 'repeat(' + size + ', 1fr)';
  el.style.setProperty('--cs', Math.min(40, Math.floor((Math.min(window.innerWidth - 40, 560)) / size) - 3) + 'px');
  el.innerHTML = '';
  for (let r = 0; r < size; r++) for (let c = 0; c < size; c++) {
    const d = document.createElement('div'); d.className = 'cell'; d.textContent = g[r][c]; d.dataset.r = r; d.dataset.c = c; el.appendChild(d);
  }
}
function renderWords(placed) {
  const el = document.getElementById('wordlist'); el.innerHTML = '';
  placed.forEach(function (w) { const s = document.createElement('span'); s.className = 'word'; s.dataset.w = w; s.textContent = w; el.appendChild(s); });
}
function bindSelection(built, size) {
  const grid = document.getElementById('grid');
  const found = new Set();
  let selecting = false, startCell = null, path = [];
  function cellAt(x, y) { const e = document.elementFromPoint(x, y); return e && e.classList.contains('cell') ? e : null; }
  function clearSel() { path.forEach(c => c.classList.remove('sel')); path = []; }
  function lineFrom(a, b) {
    const r1 = +a.dataset.r, c1 = +a.dataset.c, r2 = +b.dataset.r, c2 = +b.dataset.c;
    const dr = Math.sign(r2 - r1), dc = Math.sign(c2 - c1);
    if (!(r1 === r2 || c1 === c2 || Math.abs(r2 - r1) === Math.abs(c2 - c1))) return null;
    const len = Math.max(Math.abs(r2 - r1), Math.abs(c2 - c1)) + 1, cells = [];
    for (let i = 0; i < len; i++) { const cell = grid.querySelector('.cell[data-r="' + (r1 + dr*i) + '"][data-c="' + (c1 + dc*i) + '"]'); if (!cell) return null; cells.push(cell); }
    return cells;
  }
  function down(x, y) { const c = cellAt(x, y); if (!c) return; selecting = true; startCell = c; clearSel(); path = [c]; c.classList.add('sel'); }
  function move(x, y) { if (!selecting) return; const c = cellAt(x, y); if (!c) return; const ln = lineFrom(startCell, c); if (!ln) return; clearSel(); path = ln; ln.forEach(z => z.classList.add('sel')); }
  function up() {
    if (!selecting) return; selecting = false;
    const word = path.map(c => c.textContent).join('');
    const rev = word.split('').reverse().join('');
    const target = path.map(c => c.textContent).join('');
    const matchW = built.placed.find(w => (w === word || w === rev) && !found.has(w));
    if (matchW) {
      found.add(matchW);
      path.forEach(c => { c.classList.remove('sel'); c.classList.add('found'); });
      const chip = document.querySelector('.word[data-w="' + matchW + '"]'); if (chip) chip.classList.add('got');
      save({ found: Array.from(found) }, found.size === built.placed.length, 3);
      if (found.size === built.placed.length) win();
    } else { clearSel(); }
  }
  grid.addEventListener('pointerdown', e => { e.preventDefault(); down(e.clientX, e.clientY); });
  window.addEventListener('pointermove', e => move(e.clientX, e.clientY));
  window.addEventListener('pointerup', up);
}
function win() {
  setTimeout(function () { document.getElementById('winOv').classList.add('show'); }, 450);
}
window.addEventListener('resize', function () { /* keep grid stable; no rebuild */ });
gate();
</script>
</body>
</html>
