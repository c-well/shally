<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Undercover — Host — {{ $room->code }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .stage { max-width: 1000px; margin: 0 auto; padding: clamp(20px,4vh,44px) clamp(20px,4vw,40px) 120px; }
  .bar { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; flex-wrap: wrap; border-bottom: 1px solid var(--line); padding-bottom: 16px; }
  .brand { font-family: 'IBM Plex Serif', serif; font-size: 26px; font-weight: 500; }
  .round { font-size: 13px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-soft); }
  .codewrap { text-align: center; margin: 26px 0 6px; }
  .codewrap .lbl { font-size: 12px; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-soft); }
  .code { font-family: 'IBM Plex Serif', serif; font-size: clamp(56px,12vw,104px); font-weight: 500; letter-spacing: 0.12em; color: var(--teal); line-height: 1; }
  .join-url { font-size: 15px; color: var(--ink-soft); margin-top: 6px; }
  .join-url b { color: var(--ink); }
  h2.q { font-family: 'IBM Plex Serif', serif; font-size: clamp(26px,4vw,38px); font-weight: 500; line-height: 1.25; margin: 8px 0; text-align: center; }
  .sub { text-align: center; color: var(--ink-soft); font-size: 15px; }
  .roster { margin-top: 20px; display: flex; flex-wrap: wrap; gap: 9px; justify-content: center; }
  .pill { font-size: 15px; font-weight: 500; padding: 8px 15px; border-radius: 999px; background: #fff; border: 1px solid var(--line); }
  .board { margin-top: 26px; display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 12px; }
  .pcard { background: #fff; border: 1px solid var(--line); border-radius: 13px; padding: 14px 15px; }
  .pcard .cn { font-family: 'IBM Plex Serif', serif; font-size: 19px; font-weight: 500; color: var(--teal); }
  .pcard.quiet .cn { color: var(--ink-soft); }
  .pcard ul { list-style: none; margin: 9px 0 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
  .pcard li { font-size: 14px; color: var(--ink); }
  .pcard li .silent { color: var(--ink-soft); font-style: italic; }
  .pcard .who { margin-top: 8px; font-size: 13px; color: var(--ink-soft); }
  .pcard.crook { border-color: var(--red); } .pcard.cop { border-color: var(--teal); }
  .role-tag { display: inline-block; margin-top: 6px; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; padding: 2px 7px; border-radius: 4px; }
  .role-tag.crook { background: color-mix(in srgb,var(--red) 14%,#fff); color: var(--red); }
  .role-tag.cop { background: color-mix(in srgb,var(--teal) 14%,#fff); color: var(--teal-dark); }
  .role-tag.citizen { background: color-mix(in srgb,var(--ink) 8%,#fff); color: var(--ink-soft); }
  .controls { position: fixed; left: 0; right: 0; bottom: 0; background: color-mix(in srgb,var(--parchment) 92%,transparent); backdrop-filter: blur(6px); border-top: 1px solid var(--line); padding: 14px clamp(20px,4vw,40px) calc(14px + env(safe-area-inset-bottom)); display: flex; align-items: center; justify-content: space-between; gap: 14px; }
  .ctxt { font-size: 14px; color: var(--ink-soft); }
  .btn { font: inherit; font-size: 16px; font-weight: 600; padding: 13px 30px; border: 0; border-radius: 10px; background: var(--teal); color: #fff; cursor: pointer; }
  .btn:hover { background: var(--teal-dark); } .btn:disabled { opacity: .4; cursor: not-allowed; }
  .winban { text-align: center; margin: 14px 0 4px; font-family: 'IBM Plex Serif', serif; font-size: clamp(28px,5vw,44px); font-weight: 500; }
  .winban.caught { color: var(--green); } .winban.escaped { color: var(--red); }
  .nohost { max-width: 460px; margin: 16vh auto; text-align: center; padding: 0 24px; color: var(--ink-soft); }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<div id="root"></div>
<script>
const CODE = @json($room->code), CSRF = document.querySelector('meta[name=csrf-token]').content;
const _hp = new URLSearchParams(location.search).get('h');
if (_hp) localStorage.setItem('uc_host_' + CODE, _hp);
const HOST = _hp || localStorage.getItem('uc_host_' + CODE);
const root = document.getElementById('root');
const esc = s => (s == null ? '' : String(s).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])));

if (!HOST) {
  root.innerHTML = '<div class="nohost"><h2 style="font-family:IBM Plex Serif;font-size:28px;color:var(--ink)">Host controls live on the device that started this game.</h2><p style="margin-top:14px">Open this on the phone or laptop you created the room on, or <a href="/youth" style="color:var(--teal)">start a new game</a>.</p></div>';
} else {
  poll(); setInterval(poll, 2000);
}

function api(action, body) {
  return fetch('/youth/room/' + CODE + '/' + action, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(Object.assign({ host_token: HOST }, body || {})) }).then(r => r.json());
}
function poll() {
  fetch('/youth/room/' + CODE + '/state?host=' + encodeURIComponent(HOST)).then(r => r.json()).then(render).catch(() => {});
}

function render(s) {
  let h = '<div class="stage">';
  h += '<div class="bar"><span class="brand">Undercover</span><span class="round">' + (s.status === 'lobby' ? 'Lobby' : (s.status === 'accusation' ? 'The accusation' : (s.status === 'revealed' || s.status === 'ended' ? 'Revealed' : 'Round ' + s.round_no + ' / ' + s.rounds_total))) + '</span></div>';

  if (s.status === 'lobby') {
    h += '<div class="codewrap"><div class="lbl">Room code</div><div class="code">' + esc(s.code || CODE) + '</div><div class="join-url">Join at <b>thechurchofpeace.org/youth</b></div></div>';
    h += '<p class="sub" style="margin-top:18px">' + s.player_count + ' here' + (s.player_count < 3 ? ' · need at least 3' : '') + '</p>';
    h += '<div class="roster">' + (s.roster || []).map(p => '<span class="pill">' + esc(p.name) + '</span>').join('') + '</div>';
    h += controls('Start the game →', 'start', s.player_count < 3, 'When everyone\'s in, deal the codenames.');
  } else if (s.status === 'round_question') {
    h += '<h2 class="q">' + esc(s.question ? s.question.prompt : '…') + '</h2>';
    h += '<p class="sub">Players answer on their phones — or stay quiet. <b>' + (s.answered || 0) + ' / ' + s.player_count + '</b> answered.</p>';
    h += boardHtml(s);
    h += controls('Reveal the clues →', 'advance', false, 'Read the question aloud. Advance when most have answered.');
  } else if (s.status === 'round_clues') {
    h += '<p class="sub" style="margin-top:8px">The clues so far — who is who? Talk it out.</p>';
    h += boardHtml(s);
    h += controls(s.round_no < s.rounds_total ? 'Next round →' : 'Time to accuse →', 'advance', false, 'Let the room discuss and lock guesses.');
  } else if (s.status === 'accusation') {
    h += '<h2 class="q">Who is the Crook?</h2><p class="sub">Everyone votes on their phone. <b>' + (s.accuse_count || 0) + ' / ' + s.player_count + '</b> have accused.</p>';
    h += boardHtml(s);
    h += controls('Unmask everyone →', 'advance', false, 'When the votes are in, reveal.');
  } else { // revealed / ended
    const r = s.reveal || {};
    h += '<div class="winban ' + (r.caught ? 'caught' : 'escaped') + '">' + (r.caught ? 'The Crook was caught! 🎉' : 'The Crook got away. 🎭') + '</div>';
    h += '<p class="sub">' + (r.accused ? 'The room accused <b>' + esc(r.accused) + '</b>.' : 'No clear accusation.') + '</p>';
    h += boardHtml(s);
    h += '<div class="controls"><span class="ctxt">Good game.</span><a class="btn" href="/youth">New game</a></div>';
  }
  h += '</div>';
  root.innerHTML = h;
  const b = document.getElementById('actBtn');
  if (b) b.addEventListener('click', () => { b.disabled = true; api(b.dataset.act).then(poll); });
}

function controls(label, act, disabled, ctxt) {
  return '<div class="controls"><span class="ctxt">' + esc(ctxt || '') + '</span><button class="btn" id="actBtn" data-act="' + act + '"' + (disabled ? ' disabled' : '') + '>' + esc(label) + '</button></div>';
}

function boardHtml(s) {
  if (!s.profiles || !s.profiles.length) return '';
  const revealed = s.status === 'revealed' || s.status === 'ended';
  return '<div class="board">' + s.profiles.map(p => {
    const ans = (p.answers || []);
    const quiet = ans.filter(a => a.answer).length === 0;
    let c = 'pcard' + (quiet && !revealed ? ' quiet' : '');
    if (revealed && p.role === 'crook') c += ' crook'; if (revealed && p.role === 'cop') c += ' cop';
    let inner = '<div class="cn">' + esc(p.codename) + '</div>';
    if (revealed) inner += '<div class="who">' + esc(p.name) + ' · <span class="role-tag ' + p.role + '">' + p.role + '</span></div>';
    inner += '<ul>' + ans.map(a => '<li>' + (a.answer ? esc(a.answer) : '<span class="silent">— stayed quiet —</span>') + '</li>').join('') + '</ul>';
    return '<div class="' + c + '">' + inner + '</div>';
  }).join('') + '</div>';
}
</script>
</body>
</html>
