<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Sign in with PIN — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --line:color-mix(in srgb, var(--ink) 10%, transparent); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  input:focus-visible { outline: 0; }

  .stage { min-height: 100dvh; display: grid; grid-template-rows: auto 1fr auto; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }

  .center { display: flex; align-items: center; justify-content: center; padding: clamp(24px, 6vh, 60px) 22px; }
  .panel { width: 100%; max-width: 440px; }

  .wordmark {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(40px, 6.2vw, 56px); font-weight: 500;
    line-height: 1.05; letter-spacing: -1.4px;
    color: var(--teal); text-align: center; margin-bottom: 36px;
  }

  .lede { color: var(--ink-soft); font-size: 15px; line-height: 1.55; margin-bottom: 28px; text-align: center; }

  .err { color: var(--ink); background: rgba(192, 57, 43, 0.08); border-left: 3px solid #c0392b; padding: 8px 14px; font-size: 13px; line-height: 1.4; border-radius: 0 4px 4px 0; margin-bottom: 18px; }

  form { display: flex; flex-direction: column; gap: 18px; }
  .row { display: flex; flex-direction: column; gap: 7px; }
  .lbl { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--ink-soft); }
  input[type="text"], input[type="tel"], input[type="password"] {
    font: inherit; font-size: 17px; padding: 13px 16px;
    border: 1px solid var(--line); border-radius: 5px;
    background: #fff; color: var(--ink); width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  input[type="tel"] {
    font-family: 'JetBrains Mono', monospace;
    font-size: 22px; letter-spacing: 8px; text-align: center;
    padding: 16px;
  }
  input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }

  .actions { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 8px; }
  .link { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; }
  .link:hover { color: var(--teal); }
  .btn-primary {
    background: var(--teal); color: #fff; border: 1px solid var(--teal);
    padding: 13px 28px; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s;
  }
  .btn-primary:hover { background: var(--teal-dark); }

  footer { padding: 22px clamp(20px, 5vw, 40px) 30px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); }
</style>
</head>
<body>
<div class="stage">

  <header class="top">
    <a href="/">← Back to bulletin</a>
    <span style="font-family:'JetBrains Mono',monospace;font-size:10px;color:var(--ink-soft);opacity:0.5;">v1.0</span>
  </header>

  <main class="center">
    <div class="panel">

      <div class="wordmark">The Church of Peace</div>
      <p class="lede">Type your first name and 4-digit PIN.</p>

      @if ($errors->any())
        <div class="err">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('pin-login.attempt') }}">
        @csrf
        <div class="row">
          <label for="name" class="lbl">First name</label>
          <input id="name" type="text" name="name" required autofocus
                 autocomplete="given-name" autocapitalize="words"
                 maxlength="80" value="{{ old('name') }}" placeholder="Glenda">
        </div>

        <div class="row">
          <label for="pin" class="lbl">PIN</label>
          <input id="pin" type="tel" name="pin" required
                 inputmode="numeric" pattern="\d{4,8}"
                 minlength="4" maxlength="8" autocomplete="one-time-code"
                 placeholder="• • • •">
        </div>

        <div class="actions">
          <a href="{{ route('login') }}" class="link">← Use email</a>
          <button type="submit" class="btn-primary">Sign in →</button>
        </div>
      </form>
    </div>
  </main>

  <footer>The Church of Peace · 3323 White Plains Rd · Bronx NY</footer>
</div>
</body>
</html>
