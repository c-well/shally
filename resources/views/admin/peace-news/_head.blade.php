<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; overflow-x: clip; }
  main { max-width: 920px; margin: 0 auto; padding: 32px clamp(18px, 4vw, 32px) 80px; }
  .top { padding: 18px clamp(18px, 4vw, 32px); border-bottom: 1px solid var(--line); }
  .top a { color: var(--ink-soft); text-decoration: none; font-size: 14px; }
  h1 { font-size: clamp(26px, 3vw, 34px); font-weight: 600; letter-spacing: -0.02em; margin: 0 0 6px; }
  h2 { font-size: 20px; font-weight: 600; letter-spacing: -0.01em; margin: 36px 0 12px; }
  .lede { margin: 0 0 24px; color: var(--ink-soft); font-size: 15px; line-height: 1.6; }
  .card { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 18px 20px; margin-bottom: 12px; }
  .row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; }
  .pill { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
  .s-draft { background: #efe8f7; color: #6b4d8a; } .s-approved { background: #e6f4ec; color: #1f6b45; }
  .s-held { background: #fff6e6; color: #7a4b00; } .s-sent { background: #eceef2; color: #334455; }
  .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 16px; border-radius: 6px; border: 1px solid var(--teal); background: var(--teal); color: #fff; font: 500 14px 'Poppins', system-ui, sans-serif; text-decoration: none; cursor: pointer; }
  .btn.ghost { background: transparent; color: var(--teal); }
  .btn.go { background: #1f6b45; border-color: #1f6b45; }
  .field { width: 100%; font: 400 16px 'Poppins', system-ui, sans-serif; color: #1a2332; background: #fff; border: 1px solid rgba(26,35,50,.18); border-radius: 8px; padding: 10px 12px; }
  label { display: block; font-size: 14px; font-weight: 500; margin: 14px 0 6px; }
  .muted { color: var(--ink-soft); font-size: 14px; }
  .ok { background: #e6f4ec; color: #1f6b45; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; }
</style>
@include('partials.theme-vars')
