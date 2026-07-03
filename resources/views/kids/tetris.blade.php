<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $level->reference }} — Verse Tetris</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; -webkit-font-smoothing: antialiased; -webkit-tap-highlight-color: transparent; overflow: hidden; overscroll-behavior: none; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }

  .game { display: flex; flex-direction: column; height: 100dvh; width: 100%; max-width: 560px; margin: 0 auto; overflow: hidden; }
  .topbar { flex-shrink: 0; display: flex; align-items: center; gap: 10px; padding: 10px clamp(12px,4vw,18px); border-bottom: 1px solid var(--line); }
  .topbar a.back { font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; white-space: nowrap; }
  .tb-mid { flex: 1; text-align: center; min-width: 0; }
  .tb-ref { font-size: 10px; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; color: var(--teal); }
  .tb-title { font-family: 'IBM Plex Serif', serif; font-size: 16px; font-weight: 500; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .who { font-size: 12px; color: var(--ink-soft); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; } .who b { color: var(--teal); }
  .pausebtn { background: none; border: 0; cursor: pointer; color: var(--ink-soft); width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 9px; }
  .pausebtn:hover { background: var(--cream); color: var(--teal); }
  .pausebtn svg { width: 20px; height: 20px; }

  .boardarea { flex: 1; min-height: 0; display: flex; align-items: center; justify-content: center; padding: 8px; }
  .board { height: 100%; max-height: 100%; max-width: 100%; aspect-ratio: 10/18; display: grid; grid-template-columns: repeat(10, 1fr); gap: 3px; background: linear-gradient(162deg, #fbf6ea, #f2ebd8); padding: 7px; border-radius: 16px; border: 1px solid var(--line); box-shadow: inset 0 2px 12px rgba(26,35,50,.07), 0 14px 36px -20px rgba(26,35,50,.32); touch-action: none; }
  .tc { background: rgba(26,35,50,.04); border-radius: 4px; }
  .tc.c1, .tc.c2, .tc.c3, .tc.c4, .tc.c5, .tc.c6, .tc.c7 {
    background: linear-gradient(150deg, color-mix(in srgb, var(--bc) 80%, #fff), var(--bc) 56%, color-mix(in srgb, var(--bc) 82%, #000));
    box-shadow: inset 0 1.5px 0 rgba(255,255,255,.5), inset 0 -3px 5px rgba(0,0,0,.17), 0 1px 2px rgba(0,0,0,.14); border-radius: 5px;
  }
  .tc.c1 { --bc: #2e7e8c; } .tc.c2 { --bc: #c0923f; } .tc.c3 { --bc: #7d6796; } .tc.c4 { --bc: #5f9469; } .tc.c5 { --bc: #bd7a5a; } .tc.c6 { --bc: #5e7aa0; } .tc.c7 { --bc: #a8883d; }
  .tc.ghost { background: transparent !important; box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--bc) 36%, transparent) !important; }
  .tc.clearing { animation: clearFlash .24s ease forwards; }
  @keyframes clearFlash { 0% { background: #fff; box-shadow: 0 0 16px rgba(255,255,255,.95); transform: scale(1); } 100% { background: #fff; opacity: .12; transform: scale(.82); } }

  .infobar { flex-shrink: 0; display: flex; align-items: center; gap: 16px; padding: 6px clamp(14px,4vw,20px); font-size: 13px; color: var(--ink-soft); }
  .infobar b { color: var(--ink); font-weight: 600; }
  .rmchip { margin-left: auto; font: inherit; font-size: 12px; font-weight: 600; padding: 9px 16px; border-radius: 999px; background: var(--teal); color: #fff; border: 0; cursor: pointer; white-space: nowrap; }
  .rmchip:disabled { background: color-mix(in srgb, var(--ink) 11%, transparent); color: var(--ink-soft); }
  .versestrip { flex-shrink: 0; margin: 0 clamp(14px,4vw,20px); padding: 8px 0; max-height: 3.6em; overflow-y: auto; font-family: 'IBM Plex Serif', serif; font-size: 14px; line-height: 1.65; color: var(--ink-faint); border-top: 1px solid var(--line); }
  .versestrip .vw.got { color: var(--ink); font-weight: 500; }

  .ctrls { flex-shrink: 0; display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; padding: 8px clamp(12px,4vw,18px) calc(10px + env(safe-area-inset-bottom)); }
  .cbtn { height: 52px; font-size: 22px; border: 1px solid var(--line); border-radius: 13px; background: #fff; color: var(--ink); cursor: pointer; display: flex; align-items: center; justify-content: center; user-select: none; }
  .cbtn:active { background: var(--cream); transform: scale(.95); }

  .ov { position: fixed; inset: 0; background: color-mix(in srgb, var(--ink) 58%, transparent); display: flex; align-items: center; justify-content: center; padding: 22px; z-index: 60; opacity: 0; pointer-events: none; transition: opacity .2s; }
  .ov.show { opacity: 1; pointer-events: auto; }
  .card { background: var(--parchment); border-radius: 18px; padding: 28px 26px; max-width: 440px; width: 100%; text-align: center; box-shadow: 0 30px 70px -30px rgba(0,0,0,.5); }
  .card h2 { font-family: 'IBM Plex Serif', serif; font-size: 26px; font-weight: 500; }
  .ref { font-size: 11px; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; color: var(--teal); }
  .qq { font-size: 19px; font-weight: 600; margin-bottom: 16px; line-height: 1.4; }
  .qopts { display: grid; gap: 9px; }
  .qopt { font: inherit; font-size: 15px; padding: 14px 15px; border: 1px solid var(--line); border-radius: 11px; background: #fff; color: var(--ink); cursor: pointer; text-align: left; }
  .qopt.right { background: color-mix(in srgb, var(--green) 16%, #fff); border-color: var(--green); color: var(--green); }
  .qopt.wrong { background: color-mix(in srgb, var(--red) 12%, #fff); border-color: var(--red); }
  .qresult { margin-top: 14px; font-size: 14px; line-height: 1.5; min-height: 20px; }
  .qresult.ok { color: var(--green); } .qresult.bad { color: var(--ink-soft); }
  .btn { margin-top: 16px; font: inherit; font-size: 15px; font-weight: 600; padding: 14px 30px; border: 0; border-radius: 11px; background: var(--teal); color: #fff; cursor: pointer; text-decoration: none; display: inline-block; }
  .btn-ghost { background: transparent; color: var(--teal); }
  .verse-final { margin-top: 14px; font-family: 'IBM Plex Serif', serif; font-style: italic; font-size: 19px; line-height: 1.5; }
  .card input { margin-top: 16px; width: 100%; font: inherit; font-size: 17px; padding: 14px 16px; border: 1px solid var(--line); border-radius: 11px; text-align: center; }
  .stars-big { font-size: 38px; letter-spacing: 6px; }
  .shapes { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 6px; }
  .shp { aspect-ratio: 1; font: inherit; font-weight: 700; font-size: 20px; border: 1px solid var(--line); border-radius: 12px; background: #fff; color: var(--teal); cursor: pointer; }
  .shp:active { background: var(--cream); }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<div class="game">
  <div class="topbar">
    <a class="back" href="{{ route('kids') }}">@include('partials._arl') Games</a>
    <div class="tb-mid"><div class="tb-ref">{{ $level->reference }}</div><div class="tb-title">{{ $level->title ?: $level->book }}</div></div>
    <button class="pausebtn" id="pauseBtn" aria-label="Pause"><svg viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg></button>
  </div>

  <div class="boardarea"><div class="board" id="board"></div></div>

  <div class="infobar">
    <span>Lines <b id="st-lines">0</b></span>
    <span>Score <b id="st-score">0</b></span>
    <span class="who" id="who"></span>
    <button class="rmchip" id="morphBtn" disabled>Re-morph ×0</button>
  </div>
  <div class="versestrip" id="verse"></div>

  <div class="ctrls">
    <button class="cbtn" data-act="left">◀</button>
    <button class="cbtn" data-act="rotate">⟳</button>
    <button class="cbtn" data-act="right">▶</button>
    <button class="cbtn" data-act="soft">▼</button>
    <button class="cbtn" data-act="hard">⤓</button>
  </div>
</div>

{{-- Pause --}}
<div class="ov" id="pauseOv"><div class="card">
  <h2>Paused</h2>
  <p style="color:var(--ink-soft);margin-top:8px">Take a breath.</p>
  <div><button class="btn" id="resumeBtn">Resume @include('partials._ar')</button></div>
  <div><a class="btn btn-ghost" href="{{ route('kids') }}">Quit to games</a></div>
</div></div>

{{-- Re-morph picker --}}
<div class="ov" id="morphOv"><div class="card">
  <div class="ref" style="margin-bottom:6px">Re-morph</div>
  <h2>Pick a new shape</h2>
  <p style="color:var(--ink-soft);margin-top:6px;font-size:14px">It might not fit perfectly — but it can help.</p>
  <div class="shapes" id="shapes"></div>
  <div><button class="btn btn-ghost" id="morphCancel">Never mind</button></div>
</div></div>

{{-- Question --}}
<div class="ov" id="qOv"><div class="card">
  <div class="ref" style="margin-bottom:10px">A question about Jesus</div>
  <div class="qq" id="qText"></div>
  <div class="qopts" id="qOpts"></div>
  <div class="qresult" id="qResult"></div>
  <button class="btn" id="qCont" style="display:none">Keep playing @include('partials._ar')</button>
</div></div>

{{-- Win --}}
<div class="ov" id="winOv"><div class="card">
  <div class="stars-big">⭐️⭐️⭐️</div>
  <h2>Verse mastered!</h2>
  <p style="color:var(--ink-soft);margin-top:8px">You built <b>{{ $level->reference }}</b> word by word.</p>
  <div class="verse-final">“{{ $level->verse_text }}”</div>
  <div><a class="btn" href="{{ route('kids') }}">More games @include('partials._ar')</a></div>
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
  <div><button class="btn" id="nameGo">Let's go @include('partials._ar')</button></div>
</div></div>

@php $levelData = ['id' => $level->id, 'reference' => $level->reference, 'verse' => $level->verse_text]; @endphp
<script>
const LEVEL = @json($levelData);
const QUESTIONS = @json($questions);
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// ── player identity + save ──
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
let board, cur, nextType, score, lines, revealed, dropMs, penalty, tokens, paused, userPaused, over, loop, pieceCount, curQ;
const boardEl = document.getElementById('board'), verseEl = document.getElementById('verse');
const qOv = document.getElementById('qOv'), qText = document.getElementById('qText'), qOpts = document.getElementById('qOpts'), qResult = document.getElementById('qResult'), qCont = document.getElementById('qCont');
const morphBtn = document.getElementById('morphBtn');
function frozen() { return over || paused || userPaused; }

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
  const full = []; for (let r = 0; r < ROWS; r++) if (board[r].every(c => c)) full.push(r);
  render();
  if (full.length) animateClear(full); else afterLock();
}
function animateClear(full) {
  paused = true;
  full.forEach(r => { for (let c = 0; c < COLS; c++) boardEl.children[r * COLS + c].classList.add('clearing'); });
  setTimeout(function () {
    board = board.filter((_, r) => full.indexOf(r) === -1);
    while (board.length < ROWS) board.unshift(Array(COLS).fill(0));
    const n = full.length; lines += n; score += [0, 40, 100, 300, 1200][n]; revealed = Math.min(words.length, revealed + n);
    renderStats(); renderVerse(); speed(); paused = false; render();
    if (revealed >= words.length) { winVerse(); return; }
    afterLock();
  }, 250);
}
function afterLock() { pieceCount++; if (!over) { spawn(); if (pieceCount % 5 === 0 && QUESTIONS.length) askQuestion(); } render(); }
function render() {
  const disp = board.map(r => r.slice());
  if (cur && !over) {
    let gr = cur.r; while (!collide(cur.m, gr + 1, cur.x)) gr++;
    cur.m.forEach((row, i) => row.forEach((v, j) => { if (v) { const R = gr + i, X = cur.x + j; if (R >= 0 && R < ROWS && X >= 0 && X < COLS && !disp[R][X]) disp[R][X] = -cur.c; } }));
    cur.m.forEach((row, i) => row.forEach((v, j) => { if (v) { const R = cur.r + i, X = cur.x + j; if (R >= 0 && R < ROWS && X >= 0 && X < COLS) disp[R][X] = cur.c; } }));
  }
  const cells = boardEl.children;
  for (let i = 0; i < ROWS * COLS; i++) { const v = disp[(i / COLS) | 0][i % COLS]; cells[i].className = v > 0 ? 'tc c' + v : (v < 0 ? 'tc ghost c' + (-v) : 'tc'); }
}
function renderStats() { document.getElementById('st-lines').textContent = lines; document.getElementById('st-score').textContent = score; }
function renderVerse() { verseEl.innerHTML = words.map((w, i) => i < revealed ? '<span class="vw got">' + w + '</span>' : '<span class="vw">' + w.replace(/[A-Za-z0-9]/g, '_') + '</span>').join(' '); verseEl.scrollTop = verseEl.scrollHeight; }
function updateTokens() { morphBtn.textContent = 'Re-morph ×' + tokens; morphBtn.disabled = tokens <= 0; }

function move(dx) { if (frozen() || !cur) return; if (!collide(cur.m, cur.r, cur.x + dx)) { cur.x += dx; render(); } }
function rotateCur() { if (frozen() || !cur) return; const rm = rotate(cur.m); for (const k of [0, -1, 1, -2, 2]) { if (!collide(rm, cur.r, cur.x + k)) { cur.m = rm; cur.x += k; render(); return; } } }
function softDrop() { if (frozen() || !cur) return; if (!collide(cur.m, cur.r + 1, cur.x)) { cur.r++; render(); } else lockPiece(); }
function hardDrop() { if (frozen() || !cur) return; while (!collide(cur.m, cur.r + 1, cur.x)) cur.r++; lockPiece(); }
function step() { if (frozen() || !cur) return; if (collide(cur.m, cur.r + 1, cur.x)) lockPiece(); else { cur.r++; render(); } }
function startLoop() { clearTimeout(loop); loop = setTimeout(() => { step(); startLoop(); }, dropMs); }

// ── pause ──
const pauseOv = document.getElementById('pauseOv');
document.getElementById('pauseBtn').addEventListener('click', () => setPause(true));
document.getElementById('resumeBtn').addEventListener('click', () => setPause(false));
function setPause(on) { if (over) return; userPaused = on; pauseOv.classList.toggle('show', on); }

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
  if (correct) { tokens++; updateTokens(); qResult.innerHTML = '✓ Correct! <b>+1 re-morph.</b> ' + (curQ.teaching || ''); qResult.className = 'qresult ok'; }
  else { b.classList.add('wrong'); penalty *= 0.88; speed(); qResult.innerHTML = 'The answer: <b>' + curQ.options[curQ.answer] + '</b>. ' + (curQ.teaching || '') + ' <em>The pieces fall faster now.</em>'; qResult.className = 'qresult bad'; }
  qCont.style.display = '';
});
qCont.addEventListener('click', () => { qOv.classList.remove('show'); curQ = null; paused = false; });

// ── re-morph (overlay picker) ──
const morphOv = document.getElementById('morphOv'), shapesEl = document.getElementById('shapes');
TYPES.forEach(t => { const b = document.createElement('button'); b.className = 'shp'; b.dataset.t = t; b.textContent = t; shapesEl.appendChild(b); });
morphBtn.addEventListener('click', () => { if (tokens <= 0 || over) return; userPaused = true; morphOv.classList.add('show'); });
document.getElementById('morphCancel').addEventListener('click', closeMorph);
function closeMorph() { morphOv.classList.remove('show'); userPaused = false; }
shapesEl.addEventListener('click', e => { const b = e.target.closest('.shp'); if (b) doMorph(b.dataset.t); });
function doMorph(t) {
  const s = SHAPES[t], m = s.m.map(r => r.slice());
  for (const dr of [0, -1]) for (const k of [0, -1, 1, -2, 2, -3, 3]) {
    if (cur && !collide(m, cur.r + dr, cur.x + k)) { cur.m = m; cur.c = s.c; cur.type = t; cur.r += dr; cur.x += k; tokens--; updateTokens(); closeMorph(); render(); return; }
  }
  closeMorph();
}

// ── controls: buttons + gestures ──
document.querySelector('.ctrls').addEventListener('click', e => { const b = e.target.closest('.cbtn'); if (!b) return; const a = b.dataset.act; if (a === 'left') move(-1); else if (a === 'right') move(1); else if (a === 'rotate') rotateCur(); else if (a === 'soft') softDrop(); else if (a === 'hard') hardDrop(); });
document.addEventListener('keydown', e => { if (qOv.classList.contains('show') || pauseOv.classList.contains('show')) return; if (e.key === 'ArrowLeft') move(-1); else if (e.key === 'ArrowRight') move(1); else if (e.key === 'ArrowUp') rotateCur(); else if (e.key === 'ArrowDown') softDrop(); else if (e.key === ' ') { e.preventDefault(); hardDrop(); } else if (e.key.toLowerCase() === 'p') setPause(!userPaused); });

// swipe/tap on the board
let ts = null;
boardEl.addEventListener('touchstart', e => { if (frozen()) return; const t = e.touches[0]; ts = { x: t.clientX, y: t.clientY, t: Date.now(), steps: 0 }; }, { passive: true });
boardEl.addEventListener('touchmove', e => { if (!ts || frozen()) return; const t = e.touches[0]; const cell = boardEl.clientWidth / COLS; const want = Math.round((t.clientX - ts.x) / cell); if (want !== ts.steps) { move(want - ts.steps); ts.steps = want; } }, { passive: true });
boardEl.addEventListener('touchend', e => {
  if (!ts) return; const t = e.changedTouches[0]; const dx = t.clientX - ts.x, dy = t.clientY - ts.y, dt = Date.now() - ts.t;
  if (ts.steps === 0 && Math.abs(dy) < 18 && Math.abs(dx) < 18 && dt < 300) rotateCur();
  else if (dy > 36 && dy > Math.abs(dx)) { (dy > 110 || dy / dt > 0.7) ? hardDrop() : softDrop(); }
  ts = null;
}, { passive: true });

function winVerse() { over = true; clearTimeout(loop); save({ score, lines, revealed }, true, 3); setTimeout(() => document.getElementById('winOv').classList.add('show'), 450); }
function gameOver() { over = true; clearTimeout(loop); save({ score, lines, revealed }, revealed >= words.length, revealed > 0 ? 1 : 0); document.getElementById('goMsg').textContent = 'You cleared ' + lines + ' lines and uncovered ' + revealed + ' of ' + words.length + ' words. Come back and finish the verse!'; setTimeout(() => document.getElementById('goOv').classList.add('show'), 300); }

function startGame() { board = newBoard(); score = 0; lines = 0; revealed = 0; penalty = 1; tokens = 0; pieceCount = 0; over = false; paused = false; userPaused = false; nextType = rand(TYPES); buildCells(); renderStats(); renderVerse(); updateTokens(); speed(); spawn(); render(); startLoop(); }
gate();
</script>
</body>
</html>
