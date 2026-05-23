<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Changelog — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 900px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(28px, 4vw, 36px); font-weight: 500; letter-spacing: 0.02em; color: var(--ink); margin-bottom: 0.5rem; text-transform: uppercase; }
  .lede { font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 600px; margin-bottom: 2.5rem; }

  .doc { line-height: 1.65; color: var(--ink); }
  .doc h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 500; margin: 2.5rem 0 0.6rem; padding-top: 1.5rem; border-top: 1px solid var(--line); color: var(--ink); }
  .doc > h2:first-of-type { border-top: 0; padding-top: 0; margin-top: 0; }
  .doc h3 { font-family: 'Instrument Sans', sans-serif; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); font-weight: 600; margin: 1.2rem 0 0.4rem; }
  .doc p { margin: 0 0 0.8rem; font-size: 0.93rem; }
  .doc strong { color: var(--ink); font-weight: 600; }
  .doc ul, .doc ol { margin: 0 0 1rem 1.4rem; }
  .doc li { margin-bottom: 0.4rem; font-size: 0.93rem; }
  .doc code { background: color-mix(in srgb, var(--teal) 6%, transparent); color: var(--teal-dark); font-family: 'JetBrains Mono', monospace; font-size: 0.82em; padding: 1px 5px; border-radius: 3px; }
  .doc pre { background: #fff; border: 1px solid var(--line); border-radius: 4px; padding: 12px 14px; overflow-x: auto; margin: 0.8rem 0 1.2rem; }
  .doc pre code { background: none; padding: 0; color: var(--ink); font-size: 0.85em; }
  .doc hr { border: 0; height: 1px; background: var(--line); margin: 2rem 0; }
  .doc blockquote { border-left: 3px solid var(--teal); padding: 8px 18px; margin: 0 0 1.2rem; color: var(--ink-soft); background: color-mix(in srgb, var(--teal) 4%, transparent); border-radius: 0 4px 4px 0; }
  .doc blockquote p { margin-bottom: 0; }
  .doc table { width: 100%; border-collapse: collapse; margin: 0.8rem 0 1.4rem; font-size: 0.92em; }
  .doc th, .doc td { border: 1px solid var(--line); padding: 8px 12px; text-align: left; }
  .doc th { background: color-mix(in srgb, var(--teal) 4%, transparent); font-weight: 600; }

  .edit-hint { margin-top: 3rem; padding: 14px 18px; background: color-mix(in srgb, var(--brass) 7%, transparent); border-left: 3px solid var(--brass); border-radius: 0 4px 4px 0; font-size: 13px; color: var(--ink-soft); line-height: 1.55; }
  .edit-hint strong { color: var(--ink); }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">CHANGELOG</span>
</div>

<main>
  <h1>Changelog.</h1>
  <p class="lede">Every notable change to the site, in plain English. Newest at the top. When something feels off, scroll back through this to see what shifted recently.</p>

  <div class="doc">
    {!! $html !!}
  </div>

  <div class="edit-hint">
    <strong>To add an entry:</strong>
    edit <code>/home/shalom/laravel/docs/CHANGELOG.md</code> on the server.
    Format: <code>## YYYY-MM-DD · Short headline</code> followed by what was wrong / what changed / fix. Newest at the top.
    This page reads the file every load — your edit shows up immediately.
  </div>
</main>

</body>
</html>
