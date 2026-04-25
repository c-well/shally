<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Sign out — The Church of Peace</title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .stage { min-height: 100dvh; display: grid; grid-template-rows: auto 1fr auto; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; justify-content: space-between; align-items: center; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: clamp(40px, 8vh, 80px) clamp(20px, 5vw, 32px); max-width: 480px; margin: 0 auto; width: 100%; }

  .stamp { font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 500; letter-spacing: 0.32em; text-transform: uppercase; color: var(--brass); margin-bottom: 28px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px, 6vw, 56px); font-weight: 500; font-style: italic; line-height: 1.1; letter-spacing: -0.025em; color: var(--ink); margin-bottom: 22px; }
  .lede { font-size: 16px; line-height: 1.6; color: var(--ink-soft); max-width: 380px; margin-bottom: 48px; }
  .lede strong { color: var(--ink); font-weight: 600; }

  .ctas { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; }
  .btn-primary, .btn-ghost {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 16px 32px; border-radius: 5px;
    font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    text-decoration: none; cursor: pointer;
    transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.1s;
    border: 1px solid var(--line); background: transparent; color: var(--ink-soft);
    font: inherit; font-family: 'Instrument Sans', sans-serif; font-weight: 700;
  }
  .btn-primary { background: var(--teal); color: #fff; border-color: var(--teal); }
  .btn-primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); transform: translateY(-1px); }
  .btn-ghost:hover { color: var(--teal); border-color: var(--teal); }

  form { display: inline; }

  footer { padding: 22px clamp(20px, 5vw, 40px) 32px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.55; }
</style>
</head>
<body>
<div class="stage">
<header class="top">
  <a href="/">← Home</a>
  <span class="meta">sign out</span>
</header>

<main>
  @auth
    <div class="stamp">Signed in as {{ auth()->user()->name }}</div>
    <h1>Sign out?</h1>
    <p class="lede">You'll be returned to the public site. Sign back in any time with your magic link or PIN.</p>

    <div class="ctas">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-primary">Yes, sign me out →</button>
      </form>
      <a href="/" class="btn-ghost">Stay signed in</a>
    </div>
  @else
    <div class="stamp">Already signed out</div>
    <h1>You're not signed in.</h1>
    <p class="lede">No need to do anything — you're already a public visitor.</p>

    <div class="ctas">
      <a href="/" class="btn-primary">Go to the home page →</a>
      <a href="{{ route('login') }}" class="btn-ghost">Sign in instead</a>
    </div>
  @endauth
</main>

<footer>The Church of Peace · Shalom SDA · Bronx, NY</footer>
</div>
</body>
</html>
