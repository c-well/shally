<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.seo-head', [
  'title'       => 'Calendar — The Church of Peace',
  'description' => "What's happening at Shalom — services, sermons, and events, all in one place.",
  'path'        => '/calendar',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Cormorant+Garamond:ital@0;1&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Instrument Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 4px; }
  a { color: inherit; text-decoration: none; }

  main { max-width: 1220px; margin: 0 auto; padding: clamp(24px,4vh,40px) clamp(16px,4vw,40px) 80px; }

  .cal-bar { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
  .cal-nav { display: flex; align-items: center; gap: 8px; }
  .rnd { width: 38px; height: 38px; border-radius: 9px; border: 1px solid var(--line); background: #fff; color: var(--ink-soft); font-size: 17px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
  .rnd:hover { border-color: var(--teal); color: var(--teal); }
  .today-btn { font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; border: 1px solid var(--line); background: #fff; color: var(--teal); border-radius: 9px; padding: 9px 15px; }
  .today-btn:hover { border-color: var(--teal); }
  .cal-title { font-size: clamp(26px,4vw,34px); font-weight: 600; letter-spacing: -0.02em; }
  .cal-title .yr { color: var(--ink-faint); }
  .seg { display: inline-flex; background: #fff; border: 1px solid var(--line); border-radius: 9px; padding: 3px; gap: 2px; }
  .seg a, .seg span { font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 15px; color: var(--ink-soft); }
  .seg .on { background: var(--teal); color: #fff; }
  .seg .soon { color: var(--ink-faint); cursor: default; }

  .cal-sub { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 16px; flex-wrap: wrap; }
  .legend { display: flex; gap: 8px; flex-wrap: wrap; }
  .lg { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-soft); border: 1px solid var(--line); background: #fff; border-radius: 8px; padding: 8px 13px; display: inline-flex; align-items: center; gap: 7px; }
  .lg .dot { width: 8px; height: 8px; border-radius: 999px; }
  .dot.service { background: var(--teal); } .dot.sermon { background: var(--brass); } .dot.event { background: var(--green, #2d8659); }

  .grid { background: #fff; border: 1px solid var(--line); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(26,35,50,.04); }
  .dow { display: grid; grid-template-columns: repeat(7,1fr); background: color-mix(in srgb, var(--cream) 55%, #fff); border-bottom: 1px solid var(--line); }
  .dow div { padding: 12px 0; text-align: center; font-size: 11px; font-weight: 700; letter-spacing: 0.16em; color: var(--ink-soft); }
  .weeks { display: grid; grid-auto-rows: minmax(112px, 1fr); }
  .wk { display: grid; grid-template-columns: repeat(7,1fr); border-bottom: 1px solid var(--line); }
  .wk:last-child { border-bottom: 0; }
  .cell { border-right: 1px solid var(--line); padding: 8px 8px 7px; min-width: 0; }
  .cell:last-child { border-right: 0; }
  .cell.out { background: color-mix(in srgb, var(--cream) 20%, #fff); }
  .dn { font-size: 14px; font-weight: 600; width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; }
  .cell.out .dn { color: var(--ink-faint); font-weight: 500; }
  .dn.today { background: var(--teal); color: #fff; }
  .ev { display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 500; line-height: 1.25; padding: 3px 7px; border-radius: 6px; margin-top: 5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .ev .dot { width: 6px; height: 6px; border-radius: 999px; flex-shrink: 0; }
  .ev.service { background: var(--teal-light, #e6f0f3); color: var(--teal-dark); }
  .ev.sermon  { background: #f5ecd6; color: #7a5f22; }
  .ev.event   { background: #e3f0e8; color: #1f6843; }

  .empty-note { text-align: center; color: var(--ink-soft); font-size: 13px; margin-top: 20px; }

  @media (max-width: 720px) {
    .dow div { font-size: 9px; letter-spacing: 0.06em; }
    .weeks { grid-auto-rows: minmax(84px, 1fr); }
    .cell { padding: 5px 4px; }
    .dn { font-size: 12px; width: 22px; height: 22px; }
    .ev { font-size: 9.5px; padding: 2px 5px; gap: 4px; }
    .ev .dot { display: none; }
    .cal-title { order: -1; width: 100%; text-align: center; }
  }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<main>
  <div class="cal-bar">
    <div class="cal-nav">
      <a class="rnd" href="?ym={{ $prevYm }}" aria-label="Previous month">‹</a>
      <a class="rnd" href="?ym={{ $nextYm }}" aria-label="Next month">›</a>
      <a class="today-btn" href="?ym={{ $todayYm }}">Today</a>
    </div>
    <div class="cal-title">{{ $month->format('F') }} <span class="yr">{{ $month->format('Y') }}</span></div>
    <div class="seg">
      <span class="soon" title="Coming soon">Day</span>
      <span class="soon" title="Coming soon">Week</span>
      <span class="on">Month</span>
      <span class="soon" title="Coming soon">Year</span>
    </div>
  </div>

  <div class="cal-sub">
    <div class="legend">
      <span class="lg"><span class="dot service"></span> Services</span>
      <span class="lg"><span class="dot sermon"></span> Sermons</span>
      <span class="lg"><span class="dot event"></span> Events</span>
    </div>
  </div>

  <div class="grid">
    <div class="dow"><div>SUN</div><div>MON</div><div>TUE</div><div>WED</div><div>THU</div><div>FRI</div><div>SAT</div></div>
    <div class="weeks">
      @foreach (array_chunk($days, 7) as $week)
        <div class="wk">
          @foreach ($week as $day)
            <div class="cell {{ $day['inMonth'] ? '' : 'out' }}">
              <span class="dn {{ $day['isToday'] ? 'today' : '' }}">{{ $day['date']->format('j') }}</span>
              @foreach ($day['entries'] as $e)
                <div class="ev {{ $e['type'] }}" title="{{ $e['title'] }}">
                  <span class="dot {{ $e['type'] }}"></span>{{ $e['title'] }}
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>
</main>
</body>
</html>
