<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.seo-head', [
  'title'       => 'Undercover — Youth Game — The Church of Peace',
  'description' => 'A live youth-night mystery: find out who is who — and who the Crook is.',
  'path'        => '/youth',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  main { max-width: 460px; margin: 0 auto; padding: clamp(48px,10vh,110px) 24px 80px; }
  .eyebrow { font-size: 12px; font-weight: 600; letter-spacing: 0.26em; text-transform: uppercase; color: var(--teal); }
  h1 { font-family: 'IBM Plex Serif', serif; font-size: clamp(44px,9vw,64px); font-weight: 500; line-height: 1; letter-spacing: -0.02em; margin-top: 14px; }
  .lede { margin-top: 18px; font-size: 16px; line-height: 1.6; color: var(--ink-soft); }
  .card { margin-top: 30px; background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 24px; }
  label { display: block; font-size: 10px; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 7px; }
  input { font: inherit; font-size: 26px; font-weight: 600; letter-spacing: 0.3em; text-align: center; text-transform: uppercase; padding: 14px; border: 1px solid var(--line); border-radius: 10px; background: var(--parchment); color: var(--ink); width: 100%; }
  input:focus { border-color: var(--teal); outline: none; box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }
  .btn { margin-top: 14px; font: inherit; font-size: 16px; font-weight: 600; padding: 15px; border: 0; border-radius: 10px; background: var(--teal); color: #fff; cursor: pointer; width: 100%; }
  .btn:hover { background: var(--teal-dark); }
  .err { margin-top: 12px; font-size: 13px; color: var(--red); min-height: 16px; text-align: center; }
  .host { margin-top: 26px; text-align: center; }
  .host a { font-size: 13px; font-weight: 600; color: var(--ink-soft); text-decoration: none; cursor: pointer; border-bottom: 1px solid var(--line); padding-bottom: 2px; }
  .host a:hover { color: var(--teal); border-color: var(--teal); }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<main>
  <div class="eyebrow">Youth night</div>
  <h1>Undercover.</h1>
  <p class="lede">Everyone's hidden behind a codename. Answer the questions, watch the clues, and figure out <b>who is who</b> — and who the Crook is. No one has to lie; the Crook just stays quiet.</p>

  <div class="card">
    <label>Enter the room code</label>
    <input id="code" maxlength="5" autocomplete="off" autocapitalize="characters" placeholder="••••">
    <button class="btn" id="joinBtn">Join the game →</button>
    <div class="err" id="err"></div>
  </div>

  <div class="host"><a id="hostBtn">Leading the night? Host a game →</a></div>
</main>
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const code = document.getElementById('code'), err = document.getElementById('err');
code.addEventListener('input', () => { code.value = code.value.toUpperCase().replace(/[^A-Z]/g, ''); });
document.getElementById('joinBtn').addEventListener('click', go);
code.addEventListener('keydown', e => { if (e.key === 'Enter') go(); });
function go() {
  const c = code.value.trim();
  if (c.length < 4) { err.textContent = 'That code looks too short.'; return; }
  location.href = '/youth/play/' + c;
}
document.getElementById('hostBtn').addEventListener('click', () => {
  fetch('/youth/room', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ rounds: 8 }) })
    .then(r => r.json()).then(d => {
      if (!d.ok) { err.textContent = 'Could not start a game.'; return; }
      localStorage.setItem('uc_host_' + d.code, d.host_token);
      location.href = '/youth/host/' + d.code;
    });
});
</script>
</body>
</html>
