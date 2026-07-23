<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Intercessors — Sign in · The Church of Peace</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: #fefcef; color: #1a2332; font-family: 'Instrument Sans', sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  main { max-width: 480px; margin: 0 auto; padding: clamp(48px,10vh,96px) 24px 60px; }
  .eyebrow { font-family: 'Instrument Sans'; font-size: 11px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; color: #03617A; margin-bottom: 14px; text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 500; letter-spacing: -.02em; margin-bottom: 12px; text-align: center; }
  .subtitle { font-family: 'Cormorant Garamond', serif; font-size: 18px; color: #4a5568; text-align: center; margin-bottom: 40px; line-height: 1.5; }
  .who { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .who button { padding: 18px 12px; background: #fff; border: 1px solid rgba(26,35,50,.12); border-radius: 10px; cursor: pointer; font-family: 'Cormorant Garamond', serif; font-size: 21px; font-weight: 500; color: #1a2332; }
  .who button:hover { background: #03617A; border-color: #03617A; color: #fff; }
  .who button.head::after { content: '★'; font-size: 12px; color: #b08d3c; margin-left: 6px; vertical-align: 3px; }
  .who button.head:hover::after { color: #f5d795; }

  .pin-panel { display: none; margin-top: 26px; }
  .pin-panel.open { display: block; }
  .picked { text-align: center; font-family: 'Cormorant Garamond', serif; font-size: 22px; margin-bottom: 6px; }
  .change { display: block; text-align: center; font-size: 11px; letter-spacing: .16em; text-transform: uppercase; color: #03617A; background: transparent; border: 0; cursor: pointer; margin-bottom: 22px; font-family: 'Instrument Sans'; font-weight: 600; }
  .pin-input { width: 100%; padding: 16px 18px; background: #fff; border: 1px solid rgba(26,35,50,.15); border-radius: 8px; font-family: 'JetBrains Mono', monospace; font-size: 22px; text-align: center; letter-spacing: .3em; outline: none; }
  .pin-input:focus { border-color: #03617A; box-shadow: 0 0 0 3px rgba(3,97,122,.15); }
  .pin-submit { width: 100%; margin-top: 14px; padding: 15px; background: #03617A; color: #fff; border: 0; border-radius: 8px; font-family: 'Instrument Sans'; font-size: 12px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; cursor: pointer; }
  .pin-submit:hover { background: #024f65; }
  .forgot { display: block; margin: 18px auto 0; background: transparent; border: 0; color: #4a5568; font-size: 13px; text-decoration: underline; cursor: pointer; text-align: center; }

  .err { margin-top: 12px; padding: 12px 14px; background: #fef0ed; border: 1px solid #f5c6b8; border-radius: 6px; color: #8a2a10; font-size: 13.5px; }
  .ok  { margin-top: 12px; padding: 12px 14px; background: #ecf7f0; border: 1px solid #b8e0c5; border-radius: 6px; color: #1e5b2f; font-size: 13.5px; }
  /* honeypot */
  .hp { position: absolute; left: -9999px; opacity: 0; pointer-events: none; }
</style>
</head>
<body>
<main>
  <div class="eyebrow">Prayer team</div>
  <h1>Welcome, intercessor.</h1>
  <p class="subtitle">Tap your name to sign in. Cookie stays with you six months — you won't have to do this every time.</p>

  @if (session('pin_texted'))
    <div class="ok">Sent. Check your phone for the new PIN, then tap your name again.</div>
  @endif
  @if ($errors->any())
    <div class="err">{{ $errors->first() }}</div>
  @endif

  <div class="who" id="who">
    @foreach ($intercessors as $i)
      <button type="button" class="{{ $i->role === 'head' ? 'head' : '' }}" data-id="{{ $i->id }}" data-name="{{ e($i->name) }}">{{ $i->name }}</button>
    @endforeach
  </div>

  <form id="pinForm" class="pin-panel" method="POST" action="{{ route('intercessors.attemptSignIn') }}" autocomplete="off">
    @csrf
    <div class="picked" id="pickedName">—</div>
    <button type="button" class="change" id="changeBtn">‹ Not you? Change</button>
    <input type="hidden" name="intercessor_id" id="picked_id">
    <input type="text" name="form_meta_field" class="hp" tabindex="-1" autocomplete="one-time-code">
    <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
    <input type="tel" name="pin" class="pin-input" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="6-digit PIN" autofocus>
    <button type="submit" class="pin-submit">Sign in</button>
  </form>

  <form id="forgotForm" method="POST" action="{{ route('intercessors.forgotPin') }}" style="display:none;">
    @csrf
    <input type="hidden" name="intercessor_id" id="forgot_id">
    <input type="text" name="form_meta_field" class="hp" tabindex="-1" autocomplete="one-time-code">
  </form>
  <button type="button" class="forgot" id="forgotBtn" style="display:none;">Text me my PIN</button>
</main>

<script>
(function () {
  const who = document.getElementById('who');
  const panel = document.getElementById('pinForm');
  const picked = document.getElementById('pickedName');
  const pickedId = document.getElementById('picked_id');
  const forgotId = document.getElementById('forgot_id');
  const forgotBtn = document.getElementById('forgotBtn');
  const forgotForm = document.getElementById('forgotForm');
  const changeBtn = document.getElementById('changeBtn');
  const pinInput = panel.querySelector('.pin-input');

  who.addEventListener('click', (e) => {
    const b = e.target.closest('button');
    if (!b) return;
    picked.textContent = b.dataset.name;
    pickedId.value = b.dataset.id;
    forgotId.value = b.dataset.id;
    who.style.display = 'none';
    panel.classList.add('open');
    forgotBtn.style.display = 'block';
    setTimeout(() => pinInput.focus(), 100);
  });

  changeBtn.addEventListener('click', () => {
    panel.classList.remove('open');
    who.style.display = 'grid';
    forgotBtn.style.display = 'none';
    pinInput.value = '';
  });

  forgotBtn.addEventListener('click', () => forgotForm.submit());
})();
</script>
</body>
</html>
