<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $level->reference }} — Verse Tetris</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; -webkit-tap-highlight-color: transparent; overscroll-behavior: contain; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .top { padding: 12px clamp(14px,4vw,24px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-size: 12px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; }
  .top a:hover { color: var(--teal); }
  .who { font-size: 13px; color: var(--ink-soft); } .who b { color: var(--teal); }

  main { max-width: 760px; margin: 0 auto; padding: 14px clamp(12px,4vw,24px) 30px; }
  .hdr { text-align: center; margin-bottom: 12px; }
  .ref { font-size: 10px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); }
  .hdr h1 { font-family: 'IBM Plex Serif', serif; font-size: clamp(20px,4.5vw,28px); font-weight: 500; }

  .wrap { display: flex; gap: 18px; align-items: flex-start; justify-content: center; flex-wrap: wrap; }
  .board { display: grid; grid-template-columns: repeat(10, 1fr); gap: 3px; width: min(304px, 86vw); aspect-ratio: 10/18; background: linear-gradient(162deg, #fbf6ea, #f2ebd8); padding: 7px; border-radius: 16px; border: 1px solid var(--line); box-shadow: inset 0 2px 12px rgba(26,35,50,.07), 0 14px 36px -20px rgba(26,35,50,.32); touch-action: none; }
  .tc { background: rgba(26,35,50,.04); border-radius: 4px; }
  .tc.c1, .tc.c2, .tc.c3, .tc.c4, .tc.c5, .tc.c6, .tc.c7 {
    background: linear-gradient(150deg, color-mix(in srgb, var(--bc) 80%, #fff), var(--bc) 56%, color-mix(in srgb, var(--bc) 82%, #000));
    box-shadow: inset 0 1.5px 0 rgba(255,255,255,.5), inset 0 -3px 5px rgba(0,0,0,.17), 0 1px 2px rgba(0,0,0,.14);
    border-radius: 5px;
  }
  .tc.c1 { --bc: #2e7e8c; } .tc.c2 { --bc: #c0923f; } .tc.c3 { --bc: #7d6796; } .tc.c4 { --bc: #5f9469; } .tc.c5 { --bc: #bd7a5a; } .tc.c6 { --bc: #5e7aa0; } .tc.c7 { --bc: #a8883d; }
  .tc.ghost { background: transparent !important; box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--bc) 36%, transparent) !important; }
  .tc.clearing { animation: clearFlash .24s ease forwards; }
  @keyframes clearFlash { 0% { background: #fff; box-shadow: 0 0 16px rgba(255,255,255,.95); transform: scale(1); } 100% { background: #fff; opacity: .12; transform: scale(.82); } }

  .side { width: min(300px, 86vw); display: flex; flex-direction: column; gap: 14px; }
  .stats { display: flex; gap: 14px; font-size: 13px; color: var(--ink-soft); }
  .stats b { color: var(--ink); font-weight: 600; }
  .versebox { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 16px; }
  .versebox .lbl { font-size: 10px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 8px; }
  #verse { font-family: 'IBM Plex Serif', serif; font-size: 17px; line-height: 1.7; color: var(--ink-faint); }
  .vw.got { color: var(--ink); font-weight: 500; }
  .morph { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 14px; }
  .morphbtn { width: 100%; font: inherit; font-size: 14px; font-weight: 600; padding: 12px; border: 0; border-radius: 9px; background: var(--teal); color: #fff; cursor: pointer; }
  .morphbtn:disabled { background: color-mix(in srgb, var(--ink) 12%, transparent); color: var(--ink-soft); cursor: default; }
  .picker { display: none; grid-template-columns: repeat(4, 1fr); gap: 7px; margin-top: 10px; }
  .picker.show { display: grid; }
  .shp { aspect-ratio: 1; font: inherit; font-weight: 700; font-size: 16px; border: 1px solid var(--line); border-radius: 8px; background: #fff; color: var(--teal); cursor: pointer; }
  .shp:hover { border-color: var(--teal); }
  .morph .note { font-size: 12px; color: var(--ink-soft); margin-top: 8px; min-height: 16px; }

  /* controls */
  .ctrls { max-width: 360px; margin: 18px auto 0; display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
  .cbtn { aspect-ratio: 1.1; font-size: 22px; border: 1px solid var(--line); border-radius: 12px; background: #fff; color: var(--ink); cursor: pointer; display: flex; align-items: center; justify-content: center; user-select: none; }
  .cbtn:active { background: var(--cream); transform: scale(.96); }

  .ov { position: fixed; inset: 0; background: color-mix(in srgb, var(--ink) 58%, transparent); display: flex; align-items: center; justify-content: center; padding: 22px; z-index: 60; opacity: 0; pointer-events: none; transition: opacity .2s; }
  .ov.show { opacity: 1; pointer-events: auto; }
  .card { background: var(--parchment); border-radius: 18px; padding: 28px 26px; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 30px 70px -30px rgba(0,0,0,.5); }
  .card h2 { font-family: 'IBM Plex Serif', serif; font-size: 26px; font-weight: 500; }
  .qq { font-size: 19px; font-weight: 600; margin-bottom: 16px; line-height: 1.4; }
  .qopts { display: grid; gap: 9px; }
  .qopt { font: inherit; font-size: 15px; padding: 13px 15px; border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink); cursor: pointer; text-align: left; }
  .qopt:hover { border-color: var(--teal); }
  .qopt.right { background: color-mix(in srgb, var(--green) 16%, #fff); border-color: var(--green); color: var(--green); }
  .qopt.wrong { background: color-mix(in srgb, var(--red) 12%, #fff); border-color: var(--red); }
  .qresult { margin-top: 14px; font-size: 14px; line-height: 1.5; min-height: 20px; }
  .qresult.ok { color: var(--green); } .qresult.bad { color: var(--ink-soft); }
  .btn { margin-top: 16px; font: inherit; font-size: 15px; font-weight: 600; padding: 13px 28px; border: 0; border-radius: 10px; background: var(--teal); color: #fff; cursor: pointer; text-decoration: none; display: inline-block; }
  .btn-ghost { background: transparent; color: var(--teal); }
  .verse-final { margin-top: 14px; font-family: 'IBM Plex Serif', serif; font-style: italic; font-size: 19px; line-height: 1.5; }
  .card input { margin-top: 16px; width: 100%; font: inherit; font-size: 17px; padding: 13px 16px; border: 1px solid var(--line); border-radius: 10px; text-align: center; }
  .stars-big { font-size: 38px; letter-spacing: 6px; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<div class="top"><a href="{{ route('kids') }}">← Games</a><div class="who" id="who"></div></div>

<main>
  <div class="hdr"><div class="ref">{{ $level->reference }} · Verse Tetris</div><h1>{{ $level->title ?: $level->book }}</h1></div>
  <div class="wrap">
    <div class="board" id="board"></div>
    <div class="side">
      <div class="stats"><span>Lines <b id="st-lines">0</b></span><span>Score <b id="st-score">0</b></span></div>
      <div class="versebox"><div class="lbl">Build the verse — clear a line, gain a word</div><div id="verse"></div></div>
      <div class="morph">
        <button class="morphbtn" id="morphBtn" disabled>Re-morph ×0</button>
        <div class="picker" id="picker"></div>
        <div class="note" id="morphNote">Answer a question right to earn a re-morph.</div>
      </div>
    </div>
  </div>
  <div class="ctrls">
    <button class="cbtn" data-act="left">◀</button>
    <button class="cbtn" data-act="rotate">⟳</button>
    <button class="cbtn" data-act="right">▶</button>
    <button class="cbtn" data-act="soft">▼</button>
    <button class="cbtn" data-act="hard">⤓</button>
  </div>
</main>

{{-- Question --}}
<div class="ov" id="qOv"><div class="card">
  <div class="ref" style="margin-bottom:10px">A question about Jesus</div>
  <div class="qq" id="qText"></div>
  <div class="qopts" id="qOpts"></div>
  <div class="qresult" id="qResult"></div>
  <button class="btn" id="qCont" style="display:none">Keep playing →</button>
</div></div>

{{-- Win --}}
<div class="ov" id="winOv"><div class="card">
  <div class="stars-big">⭐️⭐️⭐️</div>
  <h2>Verse mastered!</h2>
  <p style="color:var(--ink-soft);margin-top:8px">You built <b>{{ $level->reference }}</b> word by word.</p>
  <div class="verse-final">“{{ $level->verse_text }}”</div>
  <div><a class="btn" href="{{ route('kids') }}">More games →</a></div>
  <div><button class="btn btn-ghost" onclick="location.reload()">Play again</button></div>
</div></div>

{{-- Game over --}}
<div class="ov" id="goOv"><div class="card">
  <h2>Good run!</h2>
  <p style="color:var(--ink-soft);margin-top:8px" id="goMsg"></p>
  <div class="verse-final">“{{ $level->verse_text }}”</div>
  <div><button class="btn" onclick="location.reload()">Try again</button></div>
  <div><a class="btn btn-ghost" href="{{ route('kids') }}">Other games</a></div>
</div></div>

{{-- Name gate --}}
<div class="ov" id="nameGate"><div class="card">
  <h2>Hi there! 👋</h2>
  <p style="color:var(--ink-soft);margin-top:10px">What should we call you? Your stars are saved.</p>
  <input id="nameInput" type="text" maxlength="60" placeholder="Your name" autocomplete="off">
  <div><button class="btn" id="nameGo">Let's go →</button></div>
</div></div>

@php $levelData = ['id' => $level->id, 'reference' => $level->reference, 'verse' => $level->verse_text]; @endphp
<script>
const LEVEL = @json($levelData);
const QUESTIONS = @json($questions);
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// ── player identity + save (shared with the other games) ──
let player = null;
try { player = JSON.parse(localStorage.getItem('cop_kid') || 'null'); } catch (e) {}
function paintWho() { document.getElementById('who').innerHTML = player ? ('<b>' + player.name + '</b> · ★ ' + (player.total_stars || 0)) : ''; }
function gate() { if (player && player.token) { paintWho(); startGame(); return; } document.getElementById('nameGate').classList.add('show'); setTimeout(() => document.getElementById('nameInput').focus(), 200); }
document.getElementById('nameGo').addEventListener('click', register);
document.getElementById('nameInput').addEventListener('keydown', e => { if (e.key === 'Enter') register(); });
function register() {
  const name = document.getElementById('nameInput').value.trim(); if (!name) return;
  fetch('{{ route('kids.register') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ name }) })
    .then(r => r.json()).then(d => { if (d.ok) { player = { token: d.token, name: d.name, total_stars: 0 }; localStorage.setItem('cop_kid', JSON.stringify(player)); document.getElementById('nameGate').classList.remove('show'); paintWho(); startGame(); } });
}
function save(state, completed, stars) {
  if (!player) return;
  fetch('{{ route('kids.save') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ token: player.token, level_id: LEVEL.id, state, completed: !!completed, stars: stars || 0, score: (state && state.score) || 0 }) })
    .then(r => r.json()).then(d => { if (d.ok) { player.total_stars = d.total_stars; localStorage.setItem('cop_kid', JSON.stringify(player)); paintWho(); } });
}

// ── Tetris engine ──
const COLS = 10, ROWS = 18;
const SHAPES = { I:{m:[[1,1,1,1]],c:1}, O:{m:[[1,1],[1,1]],c:2}, T:{m:[[0,1,0],[1,1,1]],c:3}, S:{m:[[0,1,1],[1,1,0]],c:4}, Z:{m:[[1,1,0],[0,1,1]],c:5}, J:{m:[[1,0,0],[1,1,1]],c:6}, L:{m:[[0,0,1],[1,1,1]],c:7} };
const TYPES = Object.keys(SHAPES);
const words = LEVEL.verse.replace(/\s+/g, ' ').trim().split(' ');
let board, cur, nextType, score, lines, revealed, dropMs, penalty, tokens, paused, over, loop, pieceCount, curQ;
const boardEl = document.getElementById('board'), verseEl = document.getElementById('verse');
const qOv = document.getElementById('qOv'), qText = document.getElementById('qText'), qOpts = document.getElementById('qOpts'), qResult = document.getElementById('qResult'), qCont = document.getElementById('qCont');
const morphBtn = document.getElementById('morphBtn'), picker = document.getElementById('picker'), morphNote = document.getElementById('morphNote');

function rand(a) { return a[(Math.random() * a.length) | 0]; }
function rotate(m) { return m[0].map((_, i) => m.map(r => r[i]).reverse()); }
function newBoard() { return Array.from({ length: ROWS }, () => Array(COLS).fill(0)); }
function buildCells() { boardEl.innerHTML = ''; for (let i = 0; i < ROWS * COLS; i++) { const d = document.createElement('div'); d.className = 'tc'; boardEl.appendChild(d); } }
function collide(m, r, x) { for (let i = 0; i < m.length; i++) for (let j = 0; j < m[i].length; j++) { if (!m[i][j]) continue; const R = r + i, X = x + j; if (X < 0 || X >= COLS || R >= ROWS) return true; if (R >= 0 && board[R][X]) return true; } return false; }
function spawn() { const t = nextType; nextType = rand(TYPES); const s = SHAPES[t]; cur = { type: t, m: s.m.map(r => r.slice()), c: s.c, r: 0, x: Math.floor((COLS - s.m[0].length) / 2) }; if (collide(cur.m, cur.r, cur.x)) gameOver(); }
function speed() { const lv = Math.floor(lines / 8); dropMs = Math.max(130, (720 - lv * 55) * penalty); }
function lockPiece() {
  cur.m.forEach((row, i) => row.forEach((v, j) => { if (v) { const R = cur.r + i, X = cur.x + j; if (R >= 0 && R < ROWS) board[R][X] = cur.c; } }));
  cur = null;
  const full = [];
  for (let r = 0; r < ROWS; r++) if (board[r].every(c => c)) full.push(r);
  render();
  if (full.length) animateClear(full); else afterLock();
}
function animateClear(full) {
  paused = true;
  full.forEach(r => { for (let c = 0; c < COLS; c++) boardEl.children[r * COLS + c].classList.add('clearing'); });
  setTimeout(function () {
    board = board.filter((_, r) => full.indexOf(r) === -1);
    while (board.length < ROWS) board.unshift(Array(COLS).fill(0));
    const n = full.length;
    lines += n; score += [0, 40, 100, 300, 1200][n]; revealed = Math.min(words.length, revealed + n);
    renderStats(); renderVerse(); speed(); paused = false; render();
    if (revealed >= words.length) { winVerse(); return; }
    afterLock();
  }, 250);
}
function afterLock() {
  pieceCount++;
  if (!over) { spawn(); if (pieceCount % 5 === 0 && QUESTIONS.length) askQuestion(); }
  render();
}
function render() {
  const disp = board.map(r => r.slice());
  if (cur && !over) {
    let gr = cur.r; while (!collide(cur.m, gr + 1, cur.x)) gr++;                 // ghost landing row
    cur.m.forEach((row, i) => row.forEach((v, j) => { if (v) { const R = gr + i, X = cur.x + j; if (R >= 0 && R < ROWS && X >= 0 && X < COLS && !disp[R][X]) disp[R][X] = -cur.c; } }));
    cur.m.forEach((row, i) => row.forEach((v, j) => { if (v) { const R = cur.r + i, X = cur.x + j; if (R >= 0 && R < ROWS && X >= 0 && X < COLS) disp[R][X] = cur.c; } }));
  }
  const cells = boardEl.children;
  for (let i = 0; i < ROWS * COLS; i++) { const v = disp[(i / COLS) | 0][i % COLS]; cells[i].className = v > 0 ? 'tc c' + v : (v < 0 ? 'tc ghost c' + (-v) : 'tc'); }
}
function renderStats() { document.getElementById('st-lines').textContent = lines; document.getElementById('st-score').textContent = score; }
function renderVerse() { verseEl.innerHTML = words.map((w, i) => i < revealed ? '<span class="vw got">' + w + '</span>' : '<span class="vw">' + w.replace(/[A-Za-z0-9]/g, '_') + '</span>').join(' '); }
function updateTokens() { morphBtn.textContent = 'Re-morph ×' + tokens; morphBtn.disabled = tokens <= 0; }

function move(dx) { if (over || paused) return; if (!collide(cur.m, cur.r, cur.x + dx)) { cur.x += dx; render(); } }
function rotateCur() { if (over || paused) return; const rm = rotate(cur.m); for (const k of [0, -1, 1, -2, 2]) { if (!collide(rm, cur.r, cur.x + k)) { cur.m = rm; cur.x += k; render(); return; } } }
function softDrop() { if (over || paused) return; if (!collide(cur.m, cur.r + 1, cur.x)) { cur.r++; render(); } else lockPiece(); }
function hardDrop() { if (over || paused) return; while (!collide(cur.m, cur.r + 1, cur.x)) cur.r++; lockPiece(); }
function step() { if (paused || over) return; if (collide(cur.m, cur.r + 1, cur.x)) lockPiece(); else { cur.r++; render(); } }
function startLoop() { clearTimeout(loop); loop = setTimeout(() => { step(); startLoop(); }, dropMs); }

// ── questions ──
function askQuestion() {
  paused = true; curQ = rand(QUESTIONS);
  qText.textContent = curQ.question; qOpts.innerHTML = ''; qResult.textContent = ''; qResult.className = 'qresult'; qCont.style.display = 'none';
  curQ.options.forEach((o, i) => { const b = document.createElement('button'); b.className = 'qopt'; b.textContent = o; b.dataset.i = i; qOpts.appendChild(b); });
  qOv.classList.add('show');
}
qOpts.addEventListener('click', e => {
  const b = e.target.closest('.qopt'); if (!b || !curQ || qCont.style.display !== 'none') return;
  const i = +b.dataset.i, correct = i === curQ.answer;
  [].forEach.call(qOpts.children, x => { x.disabled = true; if (+x.dataset.i === curQ.answer) x.classList.add('right'); });
  if (correct) { tokens++; updateTokens(); morphNote.textContent = 'You have a re-morph! Tap it to reshape the falling piece.'; qResult.innerHTML = '✓ Correct! <b>+1 re-morph.</b> ' + (curQ.teaching || ''); qResult.className = 'qresult ok'; }
  else { b.classList.add('wrong'); penalty *= 0.88; speed(); qResult.innerHTML = 'The answer: <b>' + curQ.options[curQ.answer] + '</b>. ' + (curQ.teaching || '') + ' <em>The pieces fall faster now.</em>'; qResult.className = 'qresult bad'; }
  qCont.style.display = '';
});
qCont.addEventListener('click', () => { qOv.classList.remove('show'); curQ = null; paused = false; });

// ── re-morph ──
TYPES.forEach(t => { const b = document.createElement('button'); b.className = 'shp'; b.dataset.t = t; b.textContent = t; picker.appendChild(b); });
morphBtn.addEventListener('click', () => { if (tokens <= 0 || over || paused) return; picker.classList.toggle('show'); });
picker.addEventListener('click', e => { const b = e.target.closest('.shp'); if (b) doMorph(b.dataset.t); });
function doMorph(t) {
  const s = SHAPES[t], m = s.m.map(r => r.slice());
  for (const dr of [0, -1]) for (const k of [0, -1, 1, -2, 2, -3, 3]) {
    if (!collide(m, cur.r + dr, cur.x + k)) { cur.m = m; cur.c = s.c; cur.type = t; cur.r += dr; cur.x += k; tokens--; updateTokens(); picker.classList.remove('show'); morphNote.textContent = 'Reshaped! Use it well.'; render(); return; }
  }
  morphNote.textContent = "That shape won't fit there — try another.";
}

// ── controls ──
document.querySelector('.ctrls').addEventListener('click', e => { const b = e.target.closest('.cbtn'); if (!b) return; const a = b.dataset.act; if (a === 'left') move(-1); else if (a === 'right') move(1); else if (a === 'rotate') rotateCur(); else if (a === 'soft') softDrop(); else if (a === 'hard') hardDrop(); });
document.addEventListener('keydown', e => { if (qOv.classList.contains('show')) return; if (e.key === 'ArrowLeft') move(-1); else if (e.key === 'ArrowRight') move(1); else if (e.key === 'ArrowUp') rotateCur(); else if (e.key === 'ArrowDown') softDrop(); else if (e.key === ' ') { e.preventDefault(); hardDrop(); } });

function winVerse() { over = true; clearTimeout(loop); save({ score, lines, revealed }, true, 3); setTimeout(() => document.getElementById('winOv').classList.add('show'), 450); }
function gameOver() { over = true; clearTimeout(loop); save({ score, lines, revealed }, revealed >= words.length, revealed > 0 ? 1 : 0); document.getElementById('goMsg').textContent = 'You cleared ' + lines + ' lines and uncovered ' + revealed + ' of ' + words.length + ' words. Come back and finish the verse!'; setTimeout(() => document.getElementById('goOv').classList.add('show'), 300); }

function startGame() { board = newBoard(); score = 0; lines = 0; revealed = 0; penalty = 1; tokens = 0; pieceCount = 0; over = false; paused = false; nextType = rand(TYPES); buildCells(); renderStats(); renderVerse(); updateTokens(); speed(); spawn(); render(); startLoop(); }
gate();
</script>
</body>
</html>
