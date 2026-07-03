<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    @include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@include('partials.seo-head', [
  'title'       => 'What we believe — Shalom SDA Church',
  'description' => 'Shalom holds to the 28 Fundamental Beliefs of the Seventh-day Adventist Church — Scripture, the Sabbath, and Christ\'s soon return at the center.',
  'path'        => '/beliefs',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 820px; margin: 0 auto; padding: clamp(48px, 9vh, 96px) clamp(20px, 5vw, 32px) 80px; }
  .head { text-align: center; margin-bottom: 64px; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(56px, 9vw, 96px); font-weight: 500; letter-spacing: -0.04em; line-height: 1; }
  .lede { margin-top: 24px; font-size: 17px; line-height: 1.65; color: var(--ink-soft); max-width: 580px; margin-left: auto; margin-right: auto; }
  .lede a { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }

  /* Markdown body — what the editor produces */
  .prose { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.7; color: var(--ink); }
  .prose p { margin-bottom: 1.2em; }
  .prose h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(28px, 4vw, 38px); font-weight: 500; letter-spacing: -0.02em; color: var(--ink); margin: 2em 0 0.5em; padding-top: 0.4em; border-top: 1px solid var(--line); }
  .prose h2:first-child { border-top: 0; padding-top: 0; margin-top: 1em; }
  .prose h3 { font-family: 'Cormorant Garamond', serif; font-size: clamp(22px, 3vw, 28px); font-weight: 500; color: var(--ink); margin: 1.6em 0 0.5em; }
  .prose blockquote { margin: 1.8em 0; padding: 18px 26px; background: color-mix(in srgb, var(--teal) 6%, transparent); border-left: 4px solid var(--teal); border-radius: 0 8px 8px 0; font-style: italic; color: var(--ink-soft); }
  .prose ul, .prose ol { margin: 1em 0 1em 1.4em; }
  .prose li { margin-bottom: 0.4em; }
  .prose a { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }
  .prose img { max-width: 100%; height: auto; border-radius: 8px; margin: 2em 0; box-shadow: 0 18px 44px -22px rgba(0,0,0,0.3); display: block; }

  .ctas { margin-top: 64px; display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
  .btn-primary, .btn-ghost { display: inline-flex; align-items: center; justify-content: center; padding: 15px 32px; border-radius: 5px; font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; text-decoration: none; cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s; }
  .btn-primary { background: var(--teal); color: #fff; border: 1px solid var(--teal); }
  .btn-primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .btn-ghost { background: transparent; color: var(--ink-soft); border: 1px solid var(--line); }
  .btn-ghost:hover { color: var(--teal); border-color: var(--teal); }

  footer { padding: 22px clamp(20px, 5vw, 40px) 40px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="head">
    @if ($page?->eyebrow)
      <div class="eyebrow">{{ $page->eyebrow }}</div>
    @endif
    <h1>{{ $page?->title ?? 'What we believe.' }}</h1>
  </div>

  <article class="prose">
    @if ($page?->body_html)
      {!! \Str::arrowize($page->body_html) !!}
    @else
      <p><em>This page hasn't been set up yet. <a href="{{ route('admin.pages.index') }}">Add it in admin.</a></em></p>
    @endif
  </article>

  <div class="ctas">
    <a href="https://www.adventist.org/beliefs/" target="_blank" rel="noopener" class="btn-primary">Read all 28 @include('partials._arup')</a>
    <a href="{{ route('visit') }}" class="btn-ghost">Plan your visit</a>
  </div>
</main>

@include("partials.footer")

@include('partials.search-float')
</body>
</html>
