<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
<link rel="shortcut icon" href="/favicon.ico?v=2">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;1,500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; -webkit-font-smoothing: antialiased; overflow-x: clip; }
  a { color: var(--teal); }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .pn-col { width: 100%; max-width: 720px; margin: 0 auto; padding: 0 clamp(20px, 5vw, 32px); }
  .pn-display { margin: 0; font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 500; font-size: clamp(46px, 7vw, 84px); line-height: 1.02; letter-spacing: -0.012em; text-wrap: balance; }
  .pn-sec { margin: 0; font-weight: 600; font-size: clamp(28px, 3.2vw, 40px); letter-spacing: -0.025em; line-height: 1.1; text-wrap: balance; }
  .pn-sub { margin: 12px 0 0; font-weight: 300; font-size: clamp(18px, 1.9vw, 21px); line-height: 1.45; color: #4a5568; text-wrap: balance; }
  .pn-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 22px; border-radius: 6px; background: var(--teal); color: #fff; text-decoration: none; font: 500 15px 'Poppins', system-ui, sans-serif; border: 1px solid var(--teal); cursor: pointer; }
  .pn-btn--ghost { background: transparent; color: var(--teal); border-color: color-mix(in srgb, var(--teal) 55%, transparent); }
  .pn-field { width: 100%; font: 400 16px 'Poppins', system-ui, sans-serif; color: #1a2332; background: #fff; border: 1px solid rgba(26,35,50,.18); border-radius: 8px; padding: 12px 14px; }
  @media (max-width: 600px) { .pn-btns { flex-direction: column; align-items: stretch; max-width: 340px; margin-left: auto; margin-right: auto; } }
</style>
@include('partials.theme-vars')
