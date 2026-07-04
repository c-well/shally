<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@include('partials.seo-head', [
  'title'       => 'Announcements — The Church of Peace',
  'description' => "This week's announcements at Shalom SDA Church — everything on the printed bulletin, and more.",
  'path'        => '/announcements',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Instrument Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 4px; }

  main { max-width: 640px; margin: 0 auto; padding: clamp(30px,6vh,56px) clamp(18px,5vw,28px) 90px; }
  .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(38px,8vw,52px); font-weight: 500; letter-spacing: -0.02em; text-align: center; margin-top: 10px; line-height: 1.05; }
  .when { text-align: center; font-size: 14px; color: var(--ink-soft); margin-top: 10px; }

  .ann { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px 22px; margin-top: 18px; }
  .ann:first-of-type { margin-top: 34px; }
  .ann h2 { font-size: 16px; font-weight: 700; letter-spacing: 0.01em; color: var(--ink); }
  .ann .body { margin-top: 8px; font-size: 15px; line-height: 1.65; color: var(--ink-soft); }
  .ann .body p + p { margin-top: 8px; }
  .ann ul { margin: 8px 0 0; padding-left: 20px; }
  .ann li { font-size: 15px; line-height: 1.6; color: var(--ink-soft); margin: 5px 0; }
  .ann.mission { text-align: center; background: color-mix(in srgb, var(--teal) 5%, #fff); border-color: color-mix(in srgb, var(--teal) 25%, var(--line)); }
  .ann.mission .body { color: var(--ink); font-family: 'Cormorant Garamond', serif; font-size: 18px; font-style: italic; }

  .empty { text-align: center; color: var(--ink-soft); padding: 60px 0; }
  .close { text-align: center; margin-top: 40px; font-size: 15px; color: var(--ink-soft); font-style: italic; }
  .backrow { text-align: center; margin-top: 26px; }
  .backrow a { font-size: 12px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--teal); text-decoration: none; border: 1px solid var(--line); background: #fff; border-radius: 9px; padding: 11px 18px; display: inline-block; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<main>
  <div class="eyebrow">This week at Shalom</div>
  <h1>Announcements.</h1>
  @if ($serviceDate)
    <div class="when">Sabbath · {{ \Carbon\Carbon::parse($serviceDate)->format('F j, Y') }}</div>
  @endif

  @forelse ($announcements as $a)
    @php
      $title  = trim((string) ($a['title'] ?? ''));
      $detail = trim((string) ($a['detail'] ?? ''));
      $isMission = str_contains(strtolower($title), 'mission');
      $lines = array_values(array_filter(array_map('trim', preg_split("/\r?\n/", $detail)), fn ($l) => $l !== ''));
    @endphp
    @if ($title !== '')
      <section class="ann {{ $isMission ? 'mission' : '' }}">
        <h2>{{ rtrim($title, ':') }}</h2>
        <div class="body">
          @if ($isMission || count($lines) <= 1)
            @foreach ($lines as $l)<p>{{ $l }}</p>@endforeach
          @else
            <ul>@foreach ($lines as $l)<li>{{ $l }}</li>@endforeach</ul>
          @endif
        </div>
      </section>
    @endif
  @empty
    <div class="empty">Nothing posted yet — check back soon.</div>
  @endforelse

  <div class="close">Have a pleasant Sabbath :)</div>
  <div class="backrow"><a href="{{ route('welcome') }}">View the full bulletin @include('partials._ar')</a></div>
</main>
</body>
</html>
