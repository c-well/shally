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

  main { max-width: 660px; margin: 0 auto; padding: clamp(56px,10vh,100px) clamp(20px,5vw,28px) clamp(90px,12vh,140px); }
  .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.26em; text-transform: uppercase; color: var(--teal); text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(42px,9vw,58px); font-weight: 500; letter-spacing: -0.02em; text-align: center; margin-top: 14px; line-height: 1.03; }
  .when { text-align: center; font-size: 13px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-soft); margin-top: 16px; }

  /* Mission — the week's centerpiece: upright serif, roomy, quietly framed in teal */
  .ann.mission { text-align: center; background: color-mix(in srgb, var(--teal) 5%, #fff); border: 1px solid color-mix(in srgb, var(--teal) 22%, var(--line)); border-radius: 18px; padding: 34px 30px; margin-top: clamp(40px,7vh,60px); }
  .ann.mission h2 { font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); }
  .ann.mission .body { margin-top: 14px; color: var(--ink); font-family: 'Cormorant Garamond', serif; font-style: normal; font-size: 21px; line-height: 1.55; }

  .ann { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 26px 28px; margin-top: 24px; box-shadow: 0 1px 3px rgba(26,35,50,.04); }
  .ann:not(.mission) { position: relative; overflow: hidden; }
  .ann:not(.mission)::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: color-mix(in srgb, var(--teal) 55%, transparent); }
  .ann h2 { font-size: 12px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--teal); }
  .ann .body { margin-top: 12px; font-size: 16px; line-height: 1.7; color: var(--ink); }
  .ann .body p + p { margin-top: 10px; }
  .ann ul { margin: 0; padding: 0; list-style: none; }
  .ann li { font-size: 16px; line-height: 1.65; color: var(--ink); padding-left: 22px; position: relative; margin: 10px 0; }
  .ann li::before { content: ''; position: absolute; left: 4px; top: 0.62em; width: 7px; height: 7px; border-radius: 999px; background: color-mix(in srgb, var(--teal) 45%, #fff); border: 1.5px solid var(--teal); }
  .ann li.bf::before { background: var(--teal); }

  .empty { text-align: center; color: var(--ink-soft); padding: 80px 0; }
  .close { text-align: center; margin-top: clamp(48px,8vh,72px); font-family: 'Cormorant Garamond', serif; font-size: 20px; color: var(--ink-soft); }
  .backrow { text-align: center; margin-top: 30px; }
  .backrow a { font-size: 12px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--teal); text-decoration: none; border: 1px solid var(--line); background: #fff; border-radius: 9px; padding: 13px 22px; display: inline-block; }
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
            <ul>@foreach ($lines as $l)@php $bf = str_starts_with($l, '•'); @endphp<li @if($bf)class="bf"@endif>{{ $bf ? ltrim(substr($l, strlen('•'))) : $l }}</li>@endforeach</ul>
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
