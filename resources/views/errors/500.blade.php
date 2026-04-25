<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
    @include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta http-equiv="refresh" content="20">
<title>Be right back · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }

  .stage { min-height: 100dvh; display: grid; grid-template-rows: auto 1fr auto; }
  .top { padding: 22px clamp(20px, 5vw, 40px); text-align: right; }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: clamp(24px, 6vh, 60px) 22px; }

  .wordmark { font-family: 'Cormorant Garamond', serif; font-size: clamp(48px, 8vw, 88px); font-weight: 500; line-height: 1.05; letter-spacing: -2px; color: var(--teal); margin-bottom: 36px; }

  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(28px, 4vw, 36px); font-weight: 400;  letter-spacing: -0.4px; line-height: 1.3; color: var(--ink); max-width: 540px; margin-bottom: 22px; }

  .lede { font-size: clamp(15px, 1.6vw, 16px); line-height: 1.6; color: var(--ink-soft); max-width: 480px; }

  .pulse { display: inline-flex; align-items: center; gap: 8px; margin-top: 36px; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; color: var(--teal); }
  .pulse-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--teal); animation: pulse 1.6s ease-in-out infinite; }
  @keyframes pulse {
    0%, 100% { opacity: 0.3; transform: scale(0.85); }
    50%      { opacity: 1;   transform: scale(1); }
  }

  footer { padding: 22px clamp(20px, 5vw, 40px) 28px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.55; }
</style>
</head>
<body>
<div class="stage">
  <header class="top"><span class="meta">be right back</span></header>
  <main>
    <div class="wordmark"><em>The Church of Peace</em></div>
    <h1>We&rsquo;ll be right back &mdash; just a moment.</h1>
    <p class="lede">The page is refreshing itself every few seconds. If it&rsquo;s still not loading after a minute, try again later.</p>
    <div class="pulse"><span class="pulse-dot"></span> reconnecting</div>
  </main>
  @include("partials.footer")
</div>
</body>
</html>
