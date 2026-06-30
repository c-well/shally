<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Undercover — {{ $room->code }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; -webkit-tap-highlight-color: transparent; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  main { max-width: 540px; margin: 0 auto; padding: 16px clamp(14px,4vw,20px) 60px; }
  .rolecard { border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; border: 1px solid var(--line); background: #fff; }
  .rolecard.crook { border-color: var(--red); background: color-mix(in srgb,var(--red) 6%,#fff); }
  .rolecard.cop { border-color: var(--teal); background: color-mix(in srgb,var(--teal) 6%,#fff); }
  .rolecard .cn { font-family: 'IBM Plex Serif', serif; font-size: 22px; font-weight: 500; }
  .rolecard .cn b { color: var(--teal); }
  .rolecard .desc { font-size: 13.5px; color: var(--ink-soft); margin-top: 3px; line-height: 1.45; }
  .rolecard.crook .desc { color: var(--red); } .rolecard .desc b { color: inherit; font-weight: 600; }
  h2 { font-family: 'IBM Plex Serif', serif; font-size: 24px; font-weight: 500; line-height: 1.25; }
  .muted { color: var(--ink-soft); font-size: 14px; line-height: 1.5; }
  .q { font-family: 'IBM Plex Serif', serif; font-size: 22px; font-weight: 500; line-height: 1.3; margin: 4px 0 14px; }
  .opts { display: grid; gap: 9px; }
  .opt { font: inherit; font-size: 16px; padding: 15px; border: 1px solid var(--line); border-radius: 11px; background: #fff; color: var(--ink); cursor: pointer; text-align: left; }
  .opt.sel { border-color: var(--teal); background: color-mix(in srgb,var(--teal) 10%,#fff); font-weight: 600; }
  .silent { margin-top: 9px; font: inherit; font-size: 14px; padding: 12px; width: 100%; border: 1px dashed var(--line); border-radius: 11px; background: transparent; color: var(--ink-soft); cursor: pointer; }
  .silent.sel { border-style: solid; border-color: var(--ink-soft); color: var(--ink); font-weight: 600; }
  .done { margin-top: 12px; padding: 13px; border-radius: 11px; background: color-mix(in srgb,var(--green) 12%,#fff); color: var(--green); font-size: 14px; font-weight: 600; text-align: center; }
  .sec-lbl { margin: 22px 0 10px; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); }
  .board { display: flex; flex-direction: column; gap: 10px; }
  .pcard { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 13px 14px; }
  .pcard.me { border-color: var(--teal); }
  .pcard .cn { font-family: 'IBM Plex Serif', serif; font-size: 18px; font-weight: 500; color: var(--teal); display: flex; justify-content: space-between; align-items: baseline; }
  .pcard .cn .metag { font-family: 'IBM Plex Sans'; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-soft); }
  .pcard ul { list-style: none; margin: 8px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 5px; }
  .pcard li { font-size: 13px; background: var(--parchment); border: 1px solid var(--line); border-radius: 6px; padding: 3px 8px; }
  .pcard li.s { font-style: italic; color: var(--ink-soft); }
  .pcard select { margin-top: 10px; font: inherit; font-size: 14px; padding: 9px 10px; border: 1px solid var(--line); border-radius: 8px; background: #fff; width: 100%; }
  .pcard select.set { border-color: var(--teal); color: var(--teal-dark); font-weight: 600; }
  .role-tag { font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; padding: 2px 7px; border-radius: 4px; }
  .role-tag.crook { background: color-mix(in srgb,var(--red) 14%,#fff); color: var(--red); }
  .role-tag.cop { background: color-mix(in srgb,var(--teal) 14%,#fff); color: var(--teal-dark); }
  .role-tag.citizen { background: color-mix(in srgb,var(--ink) 8%,#fff); color: var(--ink-soft); }
  .cop-panel { margin-top: 18px; border: 1px solid var(--teal); border-radius: 12px; padding: 14px; background: color-mix(in srgb,var(--teal) 5%,#fff); }
  .cop-panel h3 { font-size: 14px; font-weight: 600; } .cop-row { display: flex; gap: 8px; margin-top: 10px; }
  .cop-row select { flex: 1; font: inherit; padding: 10px; border: 1px solid var(--line); border-radius: 8px; }
  .cop-row button { font: inherit; font-weight: 600; padding: 10px 16px; border: 0; border-radius: 8px; background: var(--teal); color: #fff; cursor: pointer; }
  .cop-row button:disabled { opacity: .4; }
  .inv { font-size: 13px; margin-top: 8px; } .inv .yes { color: var(--red); font-weight: 600; } .inv .no { color: var(--ink-soft); }
  .accuse { display: grid; grid-template-columns: repeat(auto-fill,minmax(120px,1fr)); gap: 9px; margin-top: 14px; }
  .acc { font: inherit; font-size: 16px; font-weight: 600; padding: 14px 10px; border: 1px solid var(--line); border-radius: 11px; background: #fff; color: var(--teal); cursor: pointer; }
  .acc.sel { background: var(--teal); color: #fff; border-color: var(--teal); }
  .result { text-align: center; padding: 22px 0; }
  .result .big { font-family: 'IBM Plex Serif', serif; font-size: 30px; font-weight: 500; }
  .result .big.win { color: var(--green); } .result .big.lose { color: var(--ink-soft); }
  .score { font-size: 15px; color: var(--ink-soft); margin-top: 6px; }
  /* join */
  .join { max-width: 420px; margin: 12vh auto; padding: 0 20px; }
  .join h2 { font-size: 28px; } .join label { display:block; font-size:10px; font-weight:600; letter-spacing:.14em; text-transform:uppercase; color:var(--ink-soft); margin:18px 0 7px; }
  .join input { font: inherit; font-size: 18px; padding: 14px; border: 1px solid var(--line); border-radius: 10px; width: 100%; }
  .join .btn, .lead { font: inherit; font-size: 16px; font-weight: 600; padding: 15px; border: 0; border-radius: 10px; background: var(--teal); color: #fff; cursor: pointer; width: 100%; margin-top: 14px; }
  .err { color: var(--red); font-size: 13px; margin-top: 10px; min-height: 15px; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<main id="root"></main>
<script>
const CODE = @json($room->code), CSRF = document.querySelector('meta[name=csrf-token]').content;
const root = document.getElementById('root');
const esc = s => (s == null ? '' : String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])));
const _tp = new URLSearchParams(location.search).get('t');
if (_tp) localStorage.setItem('uc_token_' + CODE, _tp);
let TOKEN = _tp || localStorage.getItem('uc_token_' + CODE);
let LAST = null;

function kidName() { try { return (JSON.parse(localStorage.getItem('cop_kid') || 'null') || {}).name || ''; } catch (e) { return ''; } }
function api(action, body) {
  return fetch('/youth/room/' + CODE + '/' + action, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(Object.assign({ token: TOKEN }, body || {})) }).then(r => r.json());
}

if (!TOKEN) showJoin(); else { poll(); setInterval(poll, 2500); }

function showJoin(errMsg) {
  root.innerHTML = '<div class="join"><h2>Join the game</h2><p class="muted" style="margin-top:8px">Room <b style="color:var(--teal)">' + esc(CODE) + '</b>. What should we call you?</p><label>Your name</label><input id="nm" maxlength="60" autocomplete="off" value="' + esc(kidName()) + '"><button class="btn" id="jb">I\'m in →</button><div class="err">' + esc(errMsg || '') + '</div></div>';
  document.getElementById('nm').focus();
  document.getElementById('jb').addEventListener('click', doJoin);
  document.getElementById('nm').addEventListener('keydown', e => { if (e.key === 'Enter') doJoin(); });
}
function doJoin() {
  const name = document.getElementById('nm').value.trim();
  if (!name) return;
  fetch('/youth/room/' + CODE + '/join', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ name }) })
    .then(r => r.json()).then(d => {
      if (!d.ok) { showJoin(d.error === 'This game has already started.' ? d.error : 'Could not join.'); return; }
      TOKEN = d.token; localStorage.setItem('uc_token_' + CODE, TOKEN);
      try { const k = JSON.parse(localStorage.getItem('cop_kid') || 'null') || {}; k.name = d.name; localStorage.setItem('cop_kid', JSON.stringify(k)); } catch (e) {}
      poll(); setInterval(poll, 2500);
    });
}
function poll() {
  fetch('/youth/room/' + CODE + '/state?token=' + encodeURIComponent(TOKEN || '')).then(r => r.json()).then(s => { LAST = s; render(s); }).catch(() => {});
}

function render(s) {
  // don't yank a dropdown the user is actively using
  if (document.activeElement && document.activeElement.tagName === 'SELECT') return;
  const you = s.you;
  if (!you) { showJoin(); return; }
  let h = '';

  if (s.status !== 'lobby' && you.codename) h += roleCard(you);

  if (s.status === 'lobby') {
    h += '<h2>You\'re in, ' + esc(you.name) + '.</h2><p class="muted" style="margin-top:10px">Waiting for the leader to start. <b>' + s.player_count + '</b> here so far.</p>';
  } else if (s.status === 'round_question') {
    h += questionHtml(s, you);
    h += boardSection(s, you);
  } else if (s.status === 'round_clues') {
    h += '<p class="muted">Talk it out. Match each codename to a person — set your guesses below.</p>';
    h += boardSection(s, you);
    if (you.role === 'cop') h += copPanel(s, you);
  } else if (s.status === 'accusation') {
    h += '<h2>Who is the Crook?</h2><p class="muted" style="margin-top:8px">Lock your accusation. ' + (you.crook_vote ? 'You accused <b>' + esc(you.crook_vote) + '</b>.' : '') + '</p>';
    h += '<div class="accuse">' + s.codenames.filter(c => c !== you.codename).map(c => '<button class="acc' + (you.crook_vote === c ? ' sel' : '') + '" data-acc="' + esc(c) + '">' + esc(c) + '</button>').join('') + '</div>';
  } else { // revealed
    h += resultHtml(s, you);
  }
  root.innerHTML = h;
  wire(s, you);
}

function roleCard(you) {
  let cls = 'rolecard ' + you.role, desc;
  if (you.role === 'crook') desc = '🎭 You\'re the <b>Crook</b>. Stay hidden — you can <b>stay quiet</b> on any question. Don\'t get found.';
  else if (you.role === 'cop') desc = '🕵 You\'re the <b>Cop</b>. Find the Crook. Investigations left: <b>' + (you.investigations_left ?? 0) + '</b>.';
  else desc = 'Help the room work out who is who — and catch the Crook.';
  return '<div class="' + cls + '"><div class="cn">You\'re <b>' + esc(you.codename) + '</b></div><div class="desc">' + desc + '</div></div>';
}
function questionHtml(s, you) {
  if (!s.question) return '<p class="muted">Get ready…</p>';
  const a = you.answered;
  let h = '<div class="q">' + esc(s.question.prompt) + '</div>';
  const opts = s.question.options || [];
  if (opts.length) {
    h += '<div class="opts">' + opts.map(o => '<button class="opt' + (a === o ? ' sel' : '') + '" data-ans="' + esc(o) + '">' + esc(o) + '</button>').join('') + '</div>';
  } else {
    h += '<input id="freeans" class="opt" style="width:100%" placeholder="Type your answer…" value="' + (a && a !== '__silent__' ? esc(a) : '') + '"><button class="opt" data-freesend="1" style="margin-top:8px;text-align:center;font-weight:600">Send</button>';
  }
  h += '<button class="silent' + (a === '__silent__' ? ' sel' : '') + '" data-silent="1">🤫 Stay quiet this round</button>';
  if (a) h += '<div class="done">' + (a === '__silent__' ? 'You stayed quiet.' : 'Answered: ' + esc(a)) + ' — you can change it until the leader moves on.</div>';
  return h;
}
function boardSection(s, you) {
  if (!s.profiles || !s.profiles.length) return '';
  return '<div class="sec-lbl">The clues · who is who?</div>' + boardHtml(s, you);
}
function boardHtml(s, you) {
  const roster = s.roster || [];
  return '<div class="board">' + s.profiles.map(p => {
    const mine = p.codename === you.codename;
    const ans = (p.answers || []);
    let inner = '<div class="cn">' + esc(p.codename) + (mine ? '<span class="metag">you</span>' : '') + '</div>';
    inner += '<ul>' + (ans.length ? ans.map(a => a.answer ? '<li>' + esc(a.answer) + '</li>' : '<li class="s">quiet</li>').join('') : '<li class="s">nothing yet</li>') + '</ul>';
    if (!mine && s.status !== 'revealed') {
      const cur = (you.guesses || {})[p.codename] || '';
      inner += '<select data-guess="' + esc(p.codename) + '" class="' + (cur ? 'set' : '') + '"><option value="">— guess who this is —</option>' +
        roster.map(r => '<option value="' + r.id + '"' + (String(cur) === String(r.id) ? ' selected' : '') + '>' + esc(r.name) + '</option>').join('') + '</select>';
    }
    return '<div class="pcard' + (mine ? ' me' : '') + '">' + inner + '</div>';
  }).join('') + '</div>';
}
function copPanel(s, you) {
  const left = you.investigations_left ?? 0;
  const done = (you.investigations || []);
  let h = '<div class="cop-panel"><h3>🕵 Investigate privately (' + left + ' left)</h3><p class="muted" style="font-size:13px;margin-top:4px">Ask the app whether a codename is the Crook. No one else sees this.</p>';
  h += '<div class="cop-row"><select id="invsel">' + s.codenames.filter(c => c !== you.codename).map(c => '<option value="' + esc(c) + '">' + esc(c) + '</option>').join('') + '</select><button id="invbtn"' + (left <= 0 ? ' disabled' : '') + '>Check</button></div>';
  if (done.length) h += '<div class="inv">' + done.map(i => esc(i.codename) + ': ' + (i.result ? '<span class="yes">the Crook!</span>' : '<span class="no">not the Crook</span>')).join('<br>') + '</div>';
  return h + '</div>';
}
function resultHtml(s, you) {
  const r = s.reveal || {}, win = you.role === 'crook' ? !r.caught : r.caught;
  let h = '<div class="result"><div class="big ' + (win ? 'win' : 'lose') + '">' + (win ? 'Your side won! 🎉' : 'Your side lost this one.') + '</div>';
  h += '<div class="score">' + (r.caught ? 'The Crook (<b>' + esc(crookName(r)) + '</b>) was caught.' : 'The Crook got away.') + ' You scored <b>' + you.score + '</b>.</div></div>';
  h += '<div class="sec-lbl">Everyone unmasked</div><div class="board">' + (r.people || []).map(p =>
    '<div class="pcard"><div class="cn">' + esc(p.codename) + ' <span class="metag" style="text-transform:none;font-size:13px;color:var(--ink)">' + esc(p.name) + '</span></div><div style="margin-top:6px"><span class="role-tag ' + p.role + '">' + p.role + '</span> · ' + p.score + ' pts</div></div>'
  ).join('') + '</div>';
  return h;
}
function crookName(r) { const c = (r.people || []).find(p => p.role === 'crook'); return c ? c.name : '?'; }

function wire(s, you) {
  root.querySelectorAll('[data-ans]').forEach(b => b.addEventListener('click', () => api('answer', { answer: b.dataset.ans }).then(poll)));
  const fs = root.querySelector('[data-freesend]'); if (fs) fs.addEventListener('click', () => { const v = document.getElementById('freeans').value.trim(); if (v) api('answer', { answer: v }).then(poll); });
  const sb = root.querySelector('[data-silent]'); if (sb) sb.addEventListener('click', () => api('answer', { silent: true }).then(poll));
  root.querySelectorAll('[data-guess]').forEach(sel => sel.addEventListener('change', () => api('guess', { codename: sel.dataset.guess, guessed_player_id: sel.value || null }).then(poll)));
  root.querySelectorAll('[data-acc]').forEach(b => b.addEventListener('click', () => api('accuse', { codename: b.dataset.acc }).then(poll)));
  const ib = document.getElementById('invbtn'); if (ib) ib.addEventListener('click', () => { ib.disabled = true; api('investigate', { codename: document.getElementById('invsel').value }).then(poll); });
}
</script>
</body>
</html>
