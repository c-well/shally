<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Intake forms — The Church of Peace</title>
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
  main { max-width: 880px; margin: 0 auto; padding: clamp(40px, 8vh, 72px) clamp(20px, 5vw, 32px) 90px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(26px, 3.6vw, 34px); font-weight: 500; letter-spacing: 0.02em; text-transform: uppercase; }
  .lede { margin-top: 14px; font-size: 15px; line-height: 1.6; color: var(--ink-soft); max-width: 600px; }
  .forms { margin-top: 38px; display: flex; flex-direction: column; gap: 14px; }
  .form-card { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 20px 22px; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .form-card .info { min-width: 0; }
  .form-card .name { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 500; color: var(--ink); }
  .form-card .slug { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--teal); margin-top: 3px; }
  .form-card .n { font-size: 12.5px; color: var(--ink-soft); margin-top: 5px; }
  .form-card .go { display: flex; gap: 8px; flex-shrink: 0; }
  .btn { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; border-radius: 6px; padding: 10px 16px; text-decoration: none; border: 1px solid var(--line); color: var(--teal); white-space: nowrap; }
  .btn:hover { border-color: var(--teal); }
  .btn-solid { background: var(--teal); color: #fff; border-color: var(--teal); }
  .soon { margin-top: 36px; padding: 18px 22px; background: color-mix(in srgb, var(--brass, #b08d57) 8%, transparent); border-left: 3px solid var(--brass, #b08d57); border-radius: 0 8px 8px 0; font-size: 13.5px; line-height: 1.6; color: var(--ink-soft); }
  .soon b { color: var(--ink); }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<div class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">INTAKE</span>
</div>
<main>
  <h1>Intake forms.</h1>
  <p class="lede">Forms people fill out — graduations, events, and more. Each one has a memorable link you can share, and every submission lands in its gallery.</p>

  <div class="forms">
    @foreach ($forms as $f)
      <div class="form-card">
        <div class="info">
          <div class="name">{{ $f->title }}</div>
          <div class="slug">thechurchofpeace.org/intake/{{ $f->slug }}</div>
          <div class="n">{{ $f->submissions_count }} {{ Str::plural('submission', $f->submissions_count) }} · {{ $f->is_active ? 'live' : 'paused' }}</div>
        </div>
        <div class="go">
          <a class="btn" href="{{ route('intake.show', $f) }}" target="_blank" rel="noopener">Open form ↗</a>
          <a class="btn btn-solid" href="{{ route('admin.intake.submissions', $f) }}">Gallery →</a>
        </div>
      </div>
    @endforeach
  </div>

  <div class="soon">
    <b>Build-your-own forms</b> (baby blessings, sign-ups, anything) is the next piece — a phone-friendly builder on top of this same engine, so a new form is just a new memorable link. Coming next.
  </div>
</main>
</body>
</html>
