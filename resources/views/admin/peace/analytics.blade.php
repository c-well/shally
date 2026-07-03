<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Peace · Analytics · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  main { max-width: 1080px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(20px, 3vw, 26px); font-weight: 500; letter-spacing: 0.04em; margin-bottom: 8px; text-transform: uppercase; }
  h2 { font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); font-weight: 500; margin: 36px 0 14px; }
  .lede { font-size: 14px; color: var(--ink-soft); max-width: 680px; margin-bottom: 30px; }
  .kpi-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 36px; }
  .kpi { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 18px 22px; }
  .kpi .label { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
  .kpi .num { font-family: 'Cormorant Garamond', serif; font-size: 38px; font-weight: 500; line-height: 1; color: var(--ink); }
  .kpi.accent .num { color: var(--teal); }
  .kpi .sub { font-size: 11px; color: var(--ink-soft); margin-top: 6px; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em; }

  .columns { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
  @media (max-width: 700px) { .columns { grid-template-columns: 1fr; } }

  .panel { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 14px 20px; }
  .panel .row { display: flex; justify-content: space-between; align-items: baseline; padding: 10px 0; border-bottom: 1px dashed var(--line); font-size: 13px; }
  .panel .row:last-child { border-bottom: none; }
  .panel .row a { color: var(--ink); text-decoration: none; }
  .panel .row a:hover { color: var(--teal); }
  .panel .row .n { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink-soft); white-space: nowrap; margin-left: 14px; }
  .panel .row .label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

  .chart { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 18px 22px; margin-bottom: 24px; }
  .chart-bars { display: flex; gap: 4px; align-items: flex-end; height: 140px; margin-top: 14px; }
  .chart-bar { flex: 1; background: var(--teal); opacity: 0.5; border-radius: 2px 2px 0 0; min-height: 2px; position: relative; transition: opacity 0.15s; }
  .chart-bar:hover { opacity: 1; }
  .chart-bar .tooltip { display: none; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: var(--ink); color: var(--parchment); font-family: 'JetBrains Mono', monospace; font-size: 10px; padding: 4px 7px; border-radius: 3px; white-space: nowrap; margin-bottom: 4px; }
  .chart-bar:hover .tooltip { display: block; }
  .chart-axis { display: flex; justify-content: space-between; margin-top: 6px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--ink-soft); }

  .empty { padding: 20px 0; text-align: center; color: var(--ink-soft); font-size: 13px; font-style: italic; }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.peace.index') }}">@include('partials._arl') Peace admin</a>
  <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;color:var(--ink-soft);opacity:0.65;">analytics</span>
</header>

<main>
  <h1>Analytics</h1>
  <p class="lede">Traffic to /find-peace and its sermon pages over the last 30 days. Page views are 90-day rolling — older data is pruned by the daily cron.</p>

  <div class="kpi-row">
    <div class="kpi accent"><div class="label">24 hours</div><div class="num">{{ number_format($totals['24h']) }}</div><div class="sub">page views</div></div>
    <div class="kpi accent"><div class="label">7 days</div><div class="num">{{ number_format($totals['7d']) }}</div><div class="sub">page views · {{ number_format($totals['uniq7d']) }} unique</div></div>
    <div class="kpi"><div class="label">30 days</div><div class="num">{{ number_format($totals['30d']) }}</div><div class="sub">page views</div></div>
    <div class="kpi"><div class="label">All time</div><div class="num">{{ number_format($totals['all']) }}</div><div class="sub">page views</div></div>
  </div>

  <h2>Daily views — last 30 days</h2>
  <div class="chart">
    @php $max = max(array_values($chart) ?: [1]); @endphp
    <div class="chart-bars">
      @foreach ($chart as $date => $count)
        <div class="chart-bar" style="height: {{ $max > 0 ? max(2, round(($count / $max) * 140)) : 2 }}px;">
          <span class="tooltip">{{ $date }}: {{ $count }}</span>
        </div>
      @endforeach
    </div>
    <div class="chart-axis">
      <span>{{ \Illuminate\Support\Carbon::parse(array_key_first($chart))->format('M j') }}</span>
      <span>{{ \Illuminate\Support\Carbon::parse(array_key_last($chart))->format('M j') }}</span>
    </div>
  </div>

  <div class="columns">
    <div>
      <h2>Top sermons (30d)</h2>
      <div class="panel">
        @forelse ($topSermons as $s)
          <div class="row">
            <span class="label"><a href="{{ url('/find-peace/' . $s->slug) }}" target="_blank">{{ $s->title }}</a></span>
            <span class="n">{{ number_format($s->n) }} · {{ number_format($s->uniq) }} uniq</span>
          </div>
        @empty
          <div class="empty">No traffic yet — first sermon went live recently.</div>
        @endforelse
      </div>
    </div>
    <div>
      <h2>Top topics (30d)</h2>
      <div class="panel">
        @forelse ($topTopics as $t)
          <div class="row">
            <span class="label"><a href="{{ url('/find-peace/topic/' . $t->slug) }}" target="_blank">{{ $t->slug }}</a></span>
            <span class="n">{{ number_format($t->n) }}</span>
          </div>
        @empty
          <div class="empty">No topic page traffic yet.</div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="columns" style="margin-top:28px;">
    <div>
      <h2>Referrers (30d)</h2>
      <div class="panel">
        @forelse ($referrers as $r)
          <div class="row">
            <span class="label">{{ \Illuminate\Support\Str::limit($r->referrer, 50) }}</span>
            <span class="n">{{ number_format($r->n) }}</span>
          </div>
        @empty
          <div class="empty">No external referrers yet — traffic is direct or from search.</div>
        @endforelse
      </div>
    </div>
    <div>
      <h2>Countries (30d)</h2>
      <div class="panel">
        @forelse ($countries as $c)
          <div class="row">
            <span class="label">{{ $c->country }}</span>
            <span class="n">{{ number_format($c->n) }}</span>
          </div>
        @empty
          <div class="empty">No country data yet.</div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="columns" style="margin-top:28px;">
    <div>
      <h2>Devices (30d)</h2>
      <div class="panel">
        @forelse ($devices as $d)
          <div class="row">
            <span class="label">{{ $d->device }}</span>
            <span class="n">{{ number_format($d->n) }}</span>
          </div>
        @empty
          <div class="empty">No device data yet.</div>
        @endforelse
      </div>
    </div>
    <div>
      <h2>Polls</h2>
      <div class="panel">
        <div class="row"><span class="label">Active polls</span><span class="n">{{ number_format($pollStats['polls_active']) }}</span></div>
        <div class="row"><span class="label">Responses (30d)</span><span class="n">{{ number_format($pollStats['responses_30d']) }}</span></div>
      </div>
    </div>
  </div>
</main>
</body>
</html>
