<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $level->reference }} — Verse Blocks</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;600;700;800;900&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
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
  .rmchip { margin-left: auto; font: inherit; font-size: 12px; font-weight: 600; padding: 9px 16px; border-radius: var(--r-btn); background: var(--teal); color: #fff; border: 0; cursor: pointer; white-space: nowrap; }
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

.ov{visibility:hidden}.ov.show{visibility:visible}.card{max-height:90dvh;overflow:auto}.card p{margin-top:14px;line-height:1.6;color:var(--ink-soft)}.small{font:12px/1.5 'IBM Plex Sans',sans-serif;color:var(--ink-soft)}.small a{color:var(--teal)}.sr-only{position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0)}.cbtn{flex-direction:column;gap:2px}.cbtn small{font:10px 'IBM Plex Sans',sans-serif}.save-row{padding:0 20px;font-size:11px;min-height:17px;color:var(--ink-soft);flex-shrink:0}.save-row button{font:inherit;padding:3px 8px;margin-left:8px}.verse-progress{margin:0 20px;font-weight:600;flex-shrink:0}.infobar{flex-wrap:wrap;gap:6px 12px}.who{white-space:normal;overflow-wrap:anywhere;max-width:100%}.btn:disabled{opacity:.5}.stars-big{font-size:30px}@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}
</style>
@include('partials.theme-vars')
@include('kids.design')
</head>
<body class="kids kids-tetris" data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@php $mission = \App\Services\KidsVerseMission::forReference($level->reference); @endphp
<div class="game">
  <div class="topbar">
    <a class="back" href="{{ route('kids') }}">@include('partials._arl') Games</a>
    <div class="tb-mid"><div class="tb-ref">{{ $level->reference }}</div><div class="tb-title">Verse Blocks</div></div>
    <button class="pausebtn" id="pauseBtn" aria-label="Pause"><svg viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg></button>
  </div>

  <div class="arcade-hud"><span>UP NEXT <span id="nextShape" class="next-shape" aria-label="Next block"></span></span><button id="soundToggle" aria-pressed="false">Sound off</button></div>
  <div class="play-stage">
    <div class="boardarea"><div class="arcade-toast" id="arcadeToast" role="status"></div><div class="board" id="board"></div></div>
    <aside class="verse-panel" aria-label="Read along">
      <div class="read-heading"><span>READ ALONG</span><b>{{ $level->reference }}</b></div>
      <div class="word-builder"><span id="buildLabel">BUILD THE FIRST WORD</span><div id="letterSlots" aria-label="Word being built"></div></div><div class="versestrip" id="verse" aria-label="Your verse">{{ $level->verse_text }}</div>
      <div class="verse-footer"><span id="verseProgress" class="small verse-progress"></span><button type="button" id="readVerse">Pause &amp; read</button></div>
      <div class="mission-note"><b>{{ $mission['title'] }}</b><span id="missionNote">{{ $mission['notes'][0] }}</span></div>
    </aside>
  </div>

  <div class="infobar">
    <span>Lines <b id="st-lines">0</b></span>
    <span>Score <b id="st-score">0</b></span>
    <span class="who" id="who"></span>
    <button class="rmchip" id="morphBtn" disabled>Shape swap · 2 lines</button>
  </div>
  <div class="save-row"><span id="saveStatus" role="status"></span><button id="retrySave" hidden>Retry saving</button></div>

  <div class="ctrls">
    <button class="cbtn" data-act="left" aria-label="Left"><span aria-hidden="true">◀</span><small>Left</small></button>
    <button class="cbtn" data-act="rotate" aria-label="Turn"><span aria-hidden="true">⟳</span><small>Turn</small></button>
    <button class="cbtn" data-act="right" aria-label="Right"><span aria-hidden="true">▶</span><small>Right</small></button>
    <button class="cbtn" data-act="soft" aria-label="Lower"><span aria-hidden="true">▼</span><small>Lower</small></button>
    <button class="cbtn" data-act="hard" aria-label="Drop"><span aria-hidden="true">⤓</span><small>Drop</small></button>
  </div>
</div>

{{-- Pause --}}
<div class="ov" id="pauseOv"><div class="card">
  <h2>Pause &amp; read</h2><div class="ref">{{ $level->reference }}</div><div class="verse-final pause-verse">{{ $level->verse_text }}</div>
  <p>{{ $mission['notes'][0] }}</p><details class="play-help"><summary>How to play</summary><p>← → move · ↑ turn · ↓ lower<br>Space drops · P pauses<br>On touch: swipe to move, tap to turn.</p><p>Every landed piece adds letters to the verse.<br>Clear 2 rows to earn a shape swap.</p></details>
  <div><button class="btn" id="resumeBtn">Resume @include('partials._ar')</button></div>
  <div><a class="btn btn-ghost" href="{{ route('kids') }}">Quit to games</a></div>
</div></div>

{{-- Re-morph picker --}}
<div class="ov" id="morphOv"><div class="card">
  <div class="ref" style="margin-bottom:6px">SHAPE SWAP</div>
  <h2>Pick a new shape</h2>
  <p style="color:var(--ink-soft);margin-top:6px;font-size:14px">It might not fit perfectly — but it can help.</p>
  <div class="shapes" id="shapes"></div>
  <div><button class="btn btn-ghost" id="morphCancel">Never mind</button></div>
</div></div>

{{-- Win --}}
<div class="ov" id="winOv"><div class="card">
  <div class="win-companion">@include('kids.mascot', ['mascotClass'=>'celebration-sheep'])</div><div class="win-art" aria-hidden="true"><span class="win-spark">★</span><span class="win-spark">★</span><span class="win-spark">★</span></div>
  <h2>Verse complete!</h2>
  <p style="color:var(--ink-soft);margin-top:8px">You discovered <b>{{ $level->reference }}</b>.</p>
  <div class="verse-final">“{{ $level->verse_text }}”</div>
  <div class="take-it-with-you"><b>TAKE IT INTO YOUR DAY</b><p>{{ $mission['practice'] }}</p><details><summary>{{ $mission['reflect'] }}</summary><p>{{ $mission['answer'] }}</p></details></div>
  @if($nextLevel)<div><a class="btn" href="{{ route('kids.play',$nextLevel) }}{{ request()->boolean('preview') ? '?preview=1' : '' }}">Next adventure →</a></div>@endif
  <div><a class="btn btn-ghost" href="{{ route('kids') }}">Choose another game</a></div>
  <div><button class="btn btn-ghost" onclick="location.reload()">Play again</button></div>
</div></div>

{{-- Game over --}}
<div class="ov" id="goOv"><div class="card">
  <h2>Your words are safe.</h2>
  <p style="color:var(--ink-soft);margin-top:8px" id="goMsg"></p>
  <div class="verse-final">“{{ $level->verse_text }}”</div>
  <div><button class="btn" id="continueVerse">Keep building →</button></div>
  <div><a class="btn btn-ghost" href="{{ route('kids') }}">Other games</a></div>
</div></div>

@include('kids.player-gate')
<div class="ov" id="introOv" role="dialog" aria-modal="true" aria-labelledby="introTitle"><div class="card">
  <div class="intro-block-art game-preview" aria-hidden="true">@include('kids.game-art', ['kind'=>'verse_tetris'])</div>
  <div class="ref">{{ $level->reference }}</div><h2 id="introTitle">{{ $mission['title'] }}</h2>
  <p>Land letter blocks. Build the verse.</p>
  <button class="btn" id="startButton">Play →</button>
  <details class="play-help"><summary>How to play</summary><p><b>Move:</b> ← → or swipe<br><b>Turn:</b> ↑ or tap the board<br><b>Drop:</b> Space or swipe down</p><p>Clear 2 rows to earn a shape swap.<br>Pause anytime with P or the pause button.</p></details>
</div></div>

@php $levelData = ['id' => $level->id, 'reference' => $level->reference, 'verse' => $level->verse_text]; @endphp
<script>
const LEVEL = @json($levelData);
const MISSION = @json($mission);
let versePhrases = [],letterSteps=[],initialState={};



</script>
@include('kids.session')
@include('kids.audio')
@include('kids.mascot-script')
<script>
// ── Tetris engine ──
document.getElementById('soundToggle').addEventListener('click',e=>{const on=GameAudio.toggle();e.currentTarget.textContent=on?'Sound on':'Sound off';e.currentTarget.setAttribute('aria-pressed',String(on));});
const COLS = 10, ROWS = 18;
const SHAPES = { I:{m:[[1,1,1,1]],c:1}, O:{m:[[1,1],[1,1]],c:2}, T:{m:[[0,1,0],[1,1,1]],c:3}, S:{m:[[0,1,1],[1,1,0]],c:4}, Z:{m:[[1,1,0],[0,1,1]],c:5}, J:{m:[[1,0,0],[1,1,1]],c:6}, L:{m:[[0,0,1],[1,1,1]],c:7} };
const TYPES = Object.keys(SHAPES);
let pieceBag=[];
function takeType(){if(!pieceBag.length){pieceBag=[...TYPES];for(let i=pieceBag.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[pieceBag[i],pieceBag[j]]=[pieceBag[j],pieceBag[i]];}}return pieceBag.pop();}
const words = LEVEL.verse.replace(/\s+/g, ' ').trim().split(' ');

let board, letterBoard, builtSteps=0, serverComplete=false, cur, nextType, score, lines, revealed, dropMs, penalty, tokens, paused, userPaused, over, loop, pieceCount;
const boardEl = document.getElementById('board'), verseEl = document.getElementById('verse');
const morphBtn = document.getElementById('morphBtn');
function frozen() { return over || paused || userPaused; }

function rand(a) { return a[(Math.random() * a.length) | 0]; }
function rotate(m) { return m[0].map((_, i) => m.map(r => r[i]).reverse()); }
function newBoard() { return Array.from({ length: ROWS }, () => Array(COLS).fill(0)); }
function buildCells() { boardEl.innerHTML = ''; for (let i = 0; i < ROWS * COLS; i++) { const d = document.createElement('div'); d.className = 'tc'; boardEl.appendChild(d); } }
function collide(m, r, x) { for (let i = 0; i < m.length; i++) for (let j = 0; j < m[i].length; j++) { if (!m[i][j]) continue; const R = r + i, X = x + j; if (X < 0 || X >= COLS || R >= ROWS) return true; if (R >= 0 && board[R][X]) return true; } return false; }
function paintNext(){const root=document.getElementById('nextShape'),shape=SHAPES[nextType];root.replaceChildren();root.style.gridTemplateColumns=`repeat(${shape.m[0].length},9px)`;root.setAttribute('aria-label',`Next block: ${nextType}`);shape.m.flat().forEach(n=>{const cell=document.createElement('i');cell.className=n?`tc c${shape.c}`:'next-empty';root.append(cell);});}
function pieceMatrix(s){let n=0;return s.map(row=>row.map(v=>v?++n:0));}
function spawn() { const t = nextType; nextType = takeType(); paintNext(); const s = SHAPES[t]; cur = { type: t, m: pieceMatrix(s.m), letters:Array.from(letterSteps[builtSteps]?.letters||'').map(c=>c.toUpperCase()), c: s.c, r: 0, x: Math.floor((COLS - s.m[0].length) / 2) }; if (collide(cur.m, cur.r, cur.x)) gameOver(); }
function speed() { const lv = Math.floor(lines / 8); dropMs = Math.max(130, (720 - lv * 55) * penalty); }
async function lockPiece() {
  paused = true;
  cur.m.forEach((row, i) => row.forEach((v, j) => { if (v) { const R = cur.r + i, X = cur.x + j; if (R >= 0 && R < ROWS) {board[R][X] = cur.c;letterBoard[R][X]=cur.letters?.[v-1]||'';} } }));
  cur = null;
  const full = []; for (let r = 0; r < ROWS; r++) if (board[r].every(c => c)) full.push(r);
  render();
  const result=await GameSession.move({type:'piece',lines:full.length});
  builtSteps=result.state.progress;revealed=result.state.words_built||0;score=result.state.score;serverComplete=result.state.completed;renderVerse();renderStats();
  paused = false;
  if (full.length) animateClear(full); else afterLock();
}
function animateClear(full) {
  paused = true;
  full.forEach(r => { for (let c = 0; c < COLS; c++) boardEl.children[r * COLS + c].classList.add('clearing'); });
  setTimeout(function () {
    board = board.filter((_, r) => full.indexOf(r) === -1);letterBoard=letterBoard.filter((_,r)=>!full.includes(r));
    while (board.length < ROWS){board.unshift(Array(COLS).fill(0));letterBoard.unshift(Array(COLS).fill(''));}
    const n = full.length; const before=Math.floor(lines/2); lines += n; tokens+=Math.floor(lines/2)-before;updateTokens();GameAudio.play(n>1?'win':'match');const toast=document.getElementById('arcadeToast');toast.textContent=['','Nice clear!','Double clear!','Triple clear!','Four-line clear!'][n];toast.classList.add('show');setTimeout(()=>toast.classList.remove('show'),1000);
    renderStats(); renderVerse(); speed(); paused = false; render();
    afterLock();
  }, 250);
}
function afterLock() { GameAudio.play('tap'); pieceCount++;if(serverComplete){winVerse();render();return;}if(!over)spawn();render(); }
function render() {
  const disp = board.map(r => r.slice()),letters=letterBoard.map(r=>r.slice());
  if (cur && !over) {
    let gr = cur.r; while (!collide(cur.m, gr + 1, cur.x)) gr++;
    cur.m.forEach((row, i) => row.forEach((v, j) => { if (v) { const R = gr + i, X = cur.x + j; if (R >= 0 && R < ROWS && X >= 0 && X < COLS && !disp[R][X]) disp[R][X] = -cur.c; } }));
    cur.m.forEach((row, i) => row.forEach((v, j) => { if (v) { const R = cur.r + i, X = cur.x + j; if (R >= 0 && R < ROWS && X >= 0 && X < COLS) {disp[R][X] = cur.c;letters[R][X]=cur.letters?.[v-1]||'';} } }));
  }
  const cells = boardEl.children;
  for (let i = 0; i < ROWS * COLS; i++) { const v = disp[(i / COLS) | 0][i % COLS]; cells[i].className = v > 0 ? 'tc c' + v : (v < 0 ? 'tc ghost c' + (-v) : 'tc');cells[i].textContent=v>0?letters[(i/COLS)|0][i%COLS]:''; }
}
function renderStats() { document.getElementById('st-lines').textContent = lines; document.getElementById('st-score').textContent = score; }
function renderVerse() {
  verseEl.replaceChildren();
  const current=letterSteps[builtSteps]?.word_index??words.length;
  words.forEach((word,i)=>{const span=document.createElement('span');span.className='vw'+(i<current?' got':i===current?' current':'');span.textContent=word+' ';verseEl.append(span);});
  const slots=document.getElementById('letterSlots');slots.replaceChildren();
  if(current<words.length){
    const spelling=Array.from(words[current].replace(/[^\p{L}\p{N}]/gu,'').toUpperCase());
    const placed=letterSteps.slice(0,builtSteps).filter(step=>step.word_index===current).reduce((n,step)=>n+Array.from(step.letters).length,0);
    spelling.forEach((letter,i)=>{const tile=document.createElement('span');tile.textContent=letter;tile.className=i<placed?'letter built':'letter';slots.append(tile);});
    document.getElementById('buildLabel').textContent='BUILD THIS WORD';
  }else document.getElementById('buildLabel').textContent='VERSE BUILT!';
  document.getElementById('verseProgress').textContent=`${revealed} of ${words.length} words built`;
  document.getElementById('missionNote').textContent=MISSION.notes[Math.min(MISSION.notes.length-1,Math.floor(revealed/Math.max(1,words.length)*MISSION.notes.length))];
  boardEl.setAttribute('aria-label',current<words.length?`Letter blocks for ${words[current]}`:'Verse complete');
}

function updateTokens() { morphBtn.textContent = tokens?'Shape swap ×' + tokens:'Shape swap · 2 lines'; morphBtn.disabled = tokens <= 0; }

function move(dx) { if (frozen() || !cur) return; if (!collide(cur.m, cur.r, cur.x + dx)) { cur.x += dx; render(); } }
function rotateCur() { if (frozen() || !cur) return; const rm = rotate(cur.m); for (const k of [0, -1, 1, -2, 2]) { if (!collide(rm, cur.r, cur.x + k)) { cur.m = rm; cur.x += k; render(); return; } } }
function softDrop() { if (frozen() || !cur) return; if (!collide(cur.m, cur.r + 1, cur.x)) { cur.r++; render(); } else lockPiece(); }
function hardDrop() { if (frozen() || !cur) return; while (!collide(cur.m, cur.r + 1, cur.x)) cur.r++; lockPiece(); }
function step() { if (frozen() || !cur) return; if (collide(cur.m, cur.r + 1, cur.x)) lockPiece(); else { cur.r++; render(); } }
function startLoop() { clearTimeout(loop); loop = setTimeout(() => { step(); startLoop(); }, dropMs); }

// ── pause ──
const pauseOv = document.getElementById('pauseOv');
document.getElementById('pauseBtn').addEventListener('click', () => setPause(true));
document.getElementById('readVerse').addEventListener('click',()=>setPause(true));
document.getElementById('resumeBtn').addEventListener('click', () => setPause(false));
function setPause(on) { if (over) return; userPaused=on; pauseOv.classList.toggle('show',on); }

// ── re-morph (overlay picker) ──
const morphOv = document.getElementById('morphOv'), shapesEl = document.getElementById('shapes');
TYPES.forEach(t => { const b = document.createElement('button'); b.className = 'shp'; b.dataset.t = t; b.textContent = t; shapesEl.appendChild(b); });
morphBtn.addEventListener('click', () => { if (tokens <= 0 || frozen() || !cur) return; userPaused = true; morphOv.classList.add('show'); });
document.getElementById('morphCancel').addEventListener('click', closeMorph);
function closeMorph() { morphOv.classList.remove('show'); userPaused = false; }
shapesEl.addEventListener('click', e => { const b = e.target.closest('.shp'); if (b) doMorph(b.dataset.t); });
function doMorph(t) {
  const s = SHAPES[t], m = pieceMatrix(s.m);
  for (const dr of [0, -1]) for (const k of [0, -1, 1, -2, 2, -3, 3]) {
    if (cur && !collide(m, cur.r + dr, cur.x + k)) { cur.m = m; cur.c = s.c; cur.type = t; cur.r += dr; cur.x += k; tokens--; updateTokens(); closeMorph(); render(); return; }
  }
  closeMorph();
}

// ── controls: buttons + gestures ──
document.querySelector('.ctrls').addEventListener('click', e => { const b = e.target.closest('.cbtn'); if (!b) return; const a = b.dataset.act; if (a === 'left') move(-1); else if (a === 'right') move(1); else if (a === 'rotate') rotateCur(); else if (a === 'soft') softDrop(); else if (a === 'hard') hardDrop(); });
document.addEventListener('keydown', e => {
  if(e.target.matches('input,button,a,select,textarea') && (e.key===' '||e.key==='Enter'))return;
  if(document.getElementById('introOv').classList.contains('show') || document.getElementById('nameGate').classList.contains('show') || morphOv.classList.contains('show'))return;
  if(e.key.toLowerCase()==='p'){e.preventDefault();setPause(!userPaused);return;}
  if(pauseOv.classList.contains('show'))return;
  if(['ArrowLeft','ArrowRight','ArrowUp','ArrowDown',' '].includes(e.key))e.preventDefault();
  if(e.key==='ArrowLeft')move(-1);else if(e.key==='ArrowRight')move(1);else if(e.key==='ArrowUp')rotateCur();else if(e.key==='ArrowDown')softDrop();else if(e.key===' ')hardDrop();
});


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

function winVerse() { GameAudio.play('win'); over = true; clearTimeout(loop); setTimeout(() => {document.getElementById('winOv').classList.add('show');KidsMascot.cheer();}, 450); }
function gameOver() { over = true; clearTimeout(loop); document.getElementById('goMsg').textContent = `You've built ${revealed} of ${words.length} words. Make room on a fresh board and keep going.`; setTimeout(() => document.getElementById('goOv').classList.add('show'), 300); }

function resetBoard(){board=newBoard();letterBoard=Array.from({length:ROWS},()=>Array(COLS).fill(''));pieceBag=[];over=false;paused=false;userPaused=false;nextType=takeType();buildCells();renderStats();renderVerse();updateTokens();speed();spawn();render();startLoop();}
function startGame(){score=initialState.score||0;lines=initialState.lines||0;builtSteps=initialState.progress||0;revealed=initialState.words_built||0;pieceCount=initialState.pieces||0;serverComplete=false;penalty=1;tokens=0;resetBoard();}
document.getElementById('continueVerse').addEventListener('click',()=>{document.getElementById('goOv').classList.remove('show');resetBoard();});
GameSession.ready().then(data=>{versePhrases=data.challenge.phrases||words;letterSteps=data.challenge.letter_steps||[];initialState=data.state;if(initialState.progress)document.getElementById('startButton').textContent='Keep building →';document.getElementById('introOv').classList.add('show');document.getElementById('startButton').focus();});
document.getElementById('startButton').addEventListener('click',()=>{document.getElementById('introOv').classList.remove('show');startGame();document.getElementById('pauseBtn').focus();});
document.addEventListener('visibilitychange',()=>{if(document.hidden && board && !over)setPause(true);});
</script>
</body>
</html>
