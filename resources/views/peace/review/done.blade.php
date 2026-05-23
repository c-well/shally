<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $title }} · Finding Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
<style>  :root { --teal-bright:#048aaa; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  .wrap { max-width: 560px; margin: 0 auto; padding: 80px 32px; text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(34px, 5vw, 44px); font-weight: 500; line-height: 1.05; margin-bottom: 16px; }
  .meta { font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 32px; }
  p { font-size: 16px; line-height: 1.6; color: var(--ink-soft); margin-bottom: 28px; max-width: 440px; margin-left: auto; margin-right: auto; }
  .cta { display: inline-block; padding: 12px 28px; background: var(--teal); color: #fff; text-decoration: none; border-radius: 6px; font-size: 12px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; transition: background 0.15s; }
  .cta:hover { background: var(--teal-bright); }
  .sermon-card { background: color-mix(in srgb, var(--teal) 4%, transparent); border-left: 3px solid var(--teal); padding: 16px 20px; text-align: left; margin: 0 auto 28px; max-width: 440px; border-radius: 0 6px 6px 0; }
  .sermon-card .label { font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
  .sermon-card .title { font-family: 'Cormorant Garamond', serif; font-size: 20px; color: var(--ink); font-weight: 500; }
  .sermon-card .speaker { font-size: 13px; color: var(--ink-soft); margin-top: 4px; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
  <div class="wrap">
    <div class="meta">Finding Peace · Review</div>
    <h1>{{ $title }}</h1>

    <div class="sermon-card">
      <div class="label">Sermon</div>
      <div class="title">{{ $sermon->title }}</div>
      @if ($sermon->speaker)
        <div class="speaker">{{ $sermon->speaker }} · {{ $sermon->sermon_date?->format('F j, Y') }}</div>
      @endif
    </div>

    <p>{{ $msg }}</p>

    @if (!empty($cta))
      <a href="{{ $cta['url'] }}" class="cta">{{ $cta['label'] }}</a>
    @endif
  </div>
</body>
</html>
