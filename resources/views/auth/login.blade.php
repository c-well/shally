<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
{{-- Branded link preview: admin links shared in iMessage bounce scrapers here,
     so this page carries the same OG card as the public site. --}}
<meta property="og:title" content="The Church of Peace · Shalom SDA Church">
<meta property="og:description" content="Shalom Seventh-day Adventist Church in the Bronx.">
<meta property="og:image" content="https://thechurchofpeace.org/og-default.png?v=3">
<meta property="og:url" content="https://thechurchofpeace.org/">
<meta name="theme-color" content="#03617A">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Sign in — Shalom SDA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root {
    --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455;
    --teal:#03617A; --teal-dark:#024357; --brass:#8a6c26;
    --line:color-mix(in srgb, var(--ink) 12%, transparent);
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  @font-face { font-family: 'Xtreem'; src: url('/fonts/XtreemMedium.ttf') format('truetype'); font-weight: 500; font-display: swap; }

  html, body {
    background: var(--parchment); color: var(--ink);
    font-family: 'Poppins', system-ui, sans-serif;
    -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;
    min-height: 100dvh;
  }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  input:focus-visible, textarea:focus-visible { outline: 0; }

  .stage {
    min-height: 100dvh;
    display: grid;
    grid-template-rows: auto 1fr auto;
  }

  /* Top-brand strip kept tiny — the wordmark IS the page hero (matches the email's lead).  */
  .top-brand {
    padding: 22px clamp(20px, 5vw, 40px);
    display: flex; align-items: center; justify-content: space-between;
  }
  .top-brand a {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 600; letter-spacing: 0.18em;
    text-transform: uppercase; text-decoration: none; color: var(--ink-soft);
  }
  .top-brand a:hover { color: var(--teal); }
  .version-badge {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.14em;
    color: var(--ink-soft);
    opacity: 0.45;
    user-select: none;
  }

  .center {
    display: flex; align-items: center; justify-content: center;
    padding: clamp(24px, 6vh, 60px) 22px;
  }

  .panel {
    width: 100%; max-width: 440px;
  }

  /* Wordmark — same Cormorant serif blue as the email header */
  .wordmark {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(40px, 6.2vw, 56px);
    font-weight: 500; line-height: 1.05;
    letter-spacing: -1.4px;
    color: var(--teal);
    margin-bottom: 36px;
    text-align: center;
  }

  .flash {
    background: color-mix(in srgb, var(--teal) 8%, transparent);
    border-left: 3px solid var(--teal);
    color: var(--teal-dark);
    padding: 12px 16px; border-radius: 0 4px 4px 0;
    margin-bottom: 22px;
    font-size: 14px; line-height: 1.5;
  }

  form { display: flex; flex-direction: column; gap: 18px; }
  .row { display: flex; flex-direction: column; gap: 7px; }
  .lbl {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.24em; text-transform: uppercase;
    color: var(--ink-soft);
  }
  input[type="email"], input[type="password"], input[type="text"] {
    font: inherit; font-size: 16px;
    padding: 12px 14px;
    border: 1px solid var(--line); border-radius: 5px;
    background: #fff; color: var(--ink); width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  input:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent);
  }

  .err {
    color: var(--ink); background: rgba(192, 57, 43, 0.08);
    border-left: 3px solid #c0392b; padding: 6px 12px;
    font-size: 13px; line-height: 1.4; border-radius: 0 4px 4px 0; margin-top: 6px;
  }

  .remember {
    display: inline-flex; align-items: center; gap: 8px;
    cursor: pointer; user-select: none;
    font-size: 14px; color: var(--ink-soft);
    margin-top: 4px;
  }
  .remember input[type="checkbox"] {
    width: 16px; height: 16px; accent-color: var(--teal);
  }

  .actions {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; margin-top: 12px;
  }
  .link {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 600;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--ink-soft); text-decoration: none;
  }
  .link:hover { color: var(--teal); }

  .btn-primary {
    background: var(--teal); color: #fff;
    border: 1px solid var(--teal);
    padding: 12px 26px; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s, border-color 0.15s, transform 0.08s;
  }
  .btn-primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .btn-primary:active { transform: translateY(1px); }

  /* Inline divider with "or" — no card around the magic-link option */
  .divider {
    display: flex; align-items: center; gap: 14px;
    margin: 28px 0 18px;
    color: var(--ink-soft);
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; letter-spacing: 0.32em; text-transform: uppercase;
    font-weight: 600;
  }
  .divider::before, .divider::after {
    content: ''; flex: 1; height: 1px; background: var(--line);
  }

  .magic-link-btn {
    display: block; width: 100%; text-align: center;
    background: transparent; color: var(--teal);
    border: 1px solid var(--teal);
    padding: 12px 18px; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
  }
  .magic-link-btn:hover { background: var(--teal); color: #fff; }

  footer {
    padding: 22px clamp(20px, 5vw, 40px) 30px;
    text-align: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase;
    color: var(--ink-soft);
  }
  footer .verse {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic; font-size: 15px;
    letter-spacing: -0.005em; text-transform: none;
    color: var(--ink-soft);
    margin-bottom: 10px; max-width: 520px;
    margin-left: auto; margin-right: auto;
  }

  @media (max-width: 480px) {
    h1 { font-size: 38px; }
    .lede { font-size: 15px; margin-bottom: 28px; }
    .center { padding: 16px 18px 36px; }
  }
</style>
</head>
<body>
<div class="stage">

  <header class="top-brand">
    <a href="/">@include('partials._arl') Back to bulletin</a>
    <span class="version-badge" title="The Church of Peace · Build 1.0">v1.0</span>
  </header>

  <main class="center">
    <div class="panel">

      <div class="wordmark">The Church of Peace</div>

      @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="row">
          <label for="email" class="lbl">Email</label>
          <input id="email" type="email" name="email" required autofocus
                 autocomplete="username" value="{{ old('email') }}">
          @error('email')<div class="err">{{ $message }}</div>@enderror
        </div>

        <div class="row">
          <label for="password" class="lbl">Password</label>
          <input id="password" type="password" name="password" required
                 autocomplete="current-password">
          @error('password')<div class="err">{{ $message }}</div>@enderror
        </div>

        <label class="remember">
          <input type="checkbox" name="remember">
          <span>Keep me signed in</span>
        </label>

        <div class="actions">
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="link">Forgot password?</a>
          @else
            <span></span>
          @endif
          <button type="submit" class="btn-primary">Sign in @include('partials._ar')</button>
        </div>
      </form>

      <div class="divider">Or</div>

      <a href="{{ route('magic-link.request') }}" class="magic-link-btn">
        Email me a sign-in link @include('partials._ar')
      </a>

      <a href="{{ route('pin-login') }}" class="magic-link-btn" style="margin-top: 10px;">
        Sign in with PIN @include('partials._ar')
      </a>

    </div>
  </main>

  <footer>
    <div class="verse">&ldquo;Peace I leave with you; my peace I give to you.&rdquo;</div>
    Shalom SDA · 3323 White Plains Rd, Bronx NY · contact@thechurchofpeace.org
  </footer>
</div>
</body>
</html>
