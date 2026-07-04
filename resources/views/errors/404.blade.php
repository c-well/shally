<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
    @include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Page not found — The Church of Peace</title>
<meta name="robots" content="noindex,follow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#8a6c26; --line:color-mix(in srgb, var(--ink) 10%, transparent); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .stage { min-height: 100dvh; display: grid; grid-template-rows: auto 1fr auto; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); transition: color 0.15s; }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center;
    padding: clamp(48px, 10vh, 96px) clamp(20px, 5vw, 32px);
    max-width: 560px; margin: 0 auto; width: 100%;
  }

  /* The number — small, monospace, restrained. Not a billboard. */
  .four-oh-four {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; font-weight: 500;
    letter-spacing: 0.32em; color: var(--brass);
    margin-bottom: 32px;
  }

  /* The headline — confident italic serif, sized down so it reads, not screams. */
  h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(40px, 6vw, 56px);
    font-weight: 500; 
    line-height: 1.1; letter-spacing: -0.025em;
    color: var(--ink);
    margin-bottom: 22px;
  }

  .lede {
    font-size: 16px; line-height: 1.6;
    color: var(--ink-soft);
    max-width: 420px;
    margin-bottom: 64px;
  }

  /* Scripture — inline, no box, no border. Just typography doing the work. */
  .verse {
    margin: 0 auto 72px;
    max-width: 460px;
  }
  .verse p {
    font-family: 'Cormorant Garamond', serif;
    font-size: 21px; line-height: 1.55;
     color: var(--ink);
  }
  .verse cite {
    display: block; margin-top: 18px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px; font-weight: 500;
    letter-spacing: 0.28em; text-transform: uppercase;
    color: var(--teal); font-style: normal;
    opacity: 0.85;
  }

  /* One clear action. */
  .home {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 16px 36px; border-radius: 5px;
    background: var(--teal); color: #fff;
    border: 1px solid var(--teal);
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    text-decoration: none;
    transition: background 0.15s, transform 0.1s;
  }
  .home:hover { background: var(--teal-dark); transform: translateY(-1px); }

  /* Quiet secondary nav — single line of underlined links, no chips. */
  .also {
    margin-top: 40px;
    font-size: 13px; color: var(--ink-soft);
  }
  .also a {
    color: var(--ink-soft); text-decoration: none;
    border-bottom: 1px solid var(--line);
    transition: color 0.15s, border-color 0.15s;
    padding-bottom: 1px;
  }
  .also a:hover { color: var(--teal); border-bottom-color: var(--teal); }
  .also .sep { margin: 0 12px; opacity: 0.4; }

  footer {
    padding: 22px clamp(20px, 5vw, 40px) 32px;
    text-align: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase;
    color: var(--ink-soft); opacity: 0.5;
  }

  @media (max-width: 600px) {
    h1 { font-size: 36px; }
    .verse p { font-size: 19px; }
    .also .sep { display: block; margin: 8px 0; opacity: 0; }
  }

  /* ── Mobile viewport lock (Karlon 2026-07-04: page panned & clipped on phones) ──
     The shared footer's letter-spaced caps lines can exceed a phone's width;
     clip the axis, wrap long tokens, and give the content honest gutters. */
  html, body { overflow-x: clip; max-width: 100vw; }
  main { padding-left: clamp(24px, 7vw, 60px); padding-right: clamp(24px, 7vw, 60px); }
  .wordmark, h1, .lede { max-width: min(540px, 100%); overflow-wrap: break-word; }
  footer, footer * { max-width: 100%; overflow-wrap: anywhere; }
  @media (max-width: 480px) {
    footer, footer * { letter-spacing: 0.12em !important; }
  }
</style>
</head>
<body>

<div class="stage">

@include('partials.site-menu')

<main>
  <div class="four-oh-four">404 · Not found</div>

  <h1>This page isn't here.</h1>


  <blockquote class="verse">
    <p>"Ask, and it shall be given you; seek, and ye shall find."</p>
    <cite>Matthew 7:7</cite>
  </blockquote>

  <a href="/" class="home">Take me home @include('partials._ar')</a>

  <div class="also">
    <a href="/">Bulletin</a><span class="sep">·</span>
    <a href="/lesson">Sabbath School</a><span class="sep">·</span>
    <a href="/visit">Visit</a><span class="sep">·</span>
    <a href="/contact">Contact</a>
  </div>
</main>

@include("partials.footer")

</div>

</body>
</html>
