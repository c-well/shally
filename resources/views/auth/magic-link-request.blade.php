<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Email me a sign-in link — Shalom SDA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root {
    --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455;
    --teal:#03617A; --teal-dark:#024357; --line:color-mix(in srgb, var(--ink) 12%, transparent);
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  @font-face { font-family: 'Xtreem'; src: url('/fonts/XtreemMedium.ttf') format('truetype'); font-weight: 500; font-display: swap; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  input:focus-visible { outline: 0; }

  .stage { min-height: 100dvh; display: grid; grid-template-rows: auto 1fr auto; }
  .top-brand { padding: 26px clamp(20px, 5vw, 40px); }
  .top-brand a {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 14px; font-weight: 600; letter-spacing: 0.08em;
    text-transform: uppercase; text-decoration: none; color: var(--ink);
    display: inline-flex; align-items: center; gap: 6px;
  }
  .top-brand em {
    font-family: 'Xtreem', 'Cormorant Garamond', serif;
    font-style: normal; color: var(--teal);
    text-transform: lowercase; font-size: 50px; line-height: 0.8;
    padding-right: 6px; transform: translateY(2px);
  }

  .center { display: flex; align-items: center; justify-content: center; padding: clamp(24px, 6vh, 60px) 22px; }
  .panel { width: 100%; max-width: 440px; }

  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px, 5.5vw, 50px); font-weight: 500; line-height: 1.05; letter-spacing: -0.03em; margin-bottom: 14px; color: var(--ink); }
  .lede { color: var(--ink-soft); font-size: 16px; line-height: 1.55; margin-bottom: 32px; max-width: 380px; }

  .flash {
    background: color-mix(in srgb, var(--teal) 8%, transparent);
    border-left: 3px solid var(--teal);
    color: var(--teal-dark);
    padding: 12px 16px; border-radius: 0 4px 4px 0;
    margin-bottom: 22px; font-size: 14px; line-height: 1.5;
  }

  form { display: flex; flex-direction: column; gap: 18px; }
  .row { display: flex; flex-direction: column; gap: 7px; }
  .lbl { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--ink-soft); }
  input[type="email"] { font: inherit; font-size: 16px; padding: 12px 14px; border: 1px solid var(--line); border-radius: 5px; background: #fff; color: var(--ink); width: 100%; transition: border-color 0.15s, box-shadow 0.15s; }
  input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }
  .err { color: var(--ink); background: rgba(192, 57, 43, 0.08); border-left: 3px solid #c0392b; padding: 6px 12px; font-size: 13px; line-height: 1.4; border-radius: 0 4px 4px 0; margin-top: 6px; }

  .actions { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 12px; }
  .link { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; }
  .link:hover { color: var(--teal); }

  .btn-primary {
    background: var(--teal); color: #fff; border: 1px solid var(--teal);
    padding: 12px 26px; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s, border-color 0.15s, transform 0.08s;
  }
  .btn-primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .btn-primary:active { transform: translateY(1px); }

  footer { padding: 22px clamp(20px, 5vw, 40px) 30px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); }
</style>
</head>
<body>
<div class="stage">

  <header class="top-brand">
    <a href="/"><em>Shalom</em> SDA · Bronx, NY</a>
  </header>

  <main class="center">
    <div class="panel">

      <div class="eyebrow">Magic link sign-in</div>
      <h1>One tap. No&nbsp;password.</h1>
      <p class="lede">
        Type the email tied to your account. We'll send you a sign-in link
        that's good for 30 minutes and can only be used once.
      </p>

      @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('magic-link.send') }}">
        @csrf
        <div style="position:absolute;left:-9999px;opacity:0;pointer-events:none" aria-hidden="true"><label for="website">Website (leave blank)</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="row">
          <label for="email" class="lbl">Email</label>
          <input id="email" type="email" name="email" required autofocus
                 autocomplete="email" value="{{ old('email') }}">
          @error('email')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="actions">
          <a href="{{ route('login') }}" class="link">← Use password</a>
          <button type="submit" class="btn-primary">Send link →</button>
        </div>
      </form>
    </div>
  </main>

  <footer>
    Shalom SDA · 3323 White Plains Rd, Bronx NY · contact@thechurchofpeace.org
  </footer>
</div>
</body>
</html>
