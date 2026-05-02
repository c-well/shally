<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Analytics — Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: "Poppins", system-ui, sans-serif; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 18px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: "Instrument Sans", sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: "JetBrains Mono", monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 1280px; margin: 0 auto; padding: 32px clamp(20px, 5vw, 40px) 80px; }
  h1 { font-size: 24px; font-weight: 600; margin-bottom: 8px; }

  .range-pills { display: inline-flex; gap: 6px; margin: 4px 0 22px; }
  .range-pills a {
    padding: 7px 14px; border: 1px solid var(--line); border-radius: 999px;
    font-family: "Instrument Sans", sans-serif; font-size: 10px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--ink-soft); text-decoration: none; transition: all 0.15s;
  }
  .range-pills a:hover { color: var(--teal); border-color: var(--teal); }
  .range-pills a.active { background: var(--teal); color: #fff; border-color: var(--teal); }

  .totals { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 26px; }
  .totals .stat { padding: 18px 20px; background: #fff; border: 1px solid var(--line); border-radius: 8px; }
  .totals .stat .label { font-family: "JetBrains Mono", monospace; font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
  .totals .stat .value { font-family: "Cormorant Garamond", serif; font-size: 36px; font-weight: 500; color: var(--ink); line-height: 1; }
  .totals .stat .sub { margin-top: 4px; font-size: 11px; color: var(--ink-soft); opacity: 0.7; font-family: "JetBrains Mono", monospace; letter-spacing: 0.06em; }

  .panel { background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 22px; margin-bottom: 22px; }
  .panel h2 { font-size: 14px; font-weight: 600; margin-bottom: 14px; letter-spacing: 0.06em; }

  /* Trendline (SVG-based, no external lib) */
  .trend { width: 100%; height: 180px; }
  .trend .grid-line { stroke: rgba(26,35,50,0.08); stroke-width: 1; }
  .trend .area { fill: rgba(3,97,122,0.10); }
  .trend .line { fill: none; stroke: var(--teal); stroke-width: 2; }
  .trend .uniq-line { fill: none; stroke: var(--brass); stroke-width: 2; stroke-dasharray: 4,3; }
  .trend-legend { display: flex; gap: 18px; margin-top: 10px; font-family: "JetBrains Mono", monospace; font-size: 10px; letter-spacing: 0.12em; color: var(--ink-soft); text-transform: uppercase; }
  .trend-legend .dot { display: inline-block; width: 10px; height: 2px; background: var(--teal); vertical-align: middle; margin-right: 6px; }
  .trend-legend .dot.uniq { background: var(--brass); }

  .row-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  .row-table th { text-align: left; padding: 10px 14px; font-family: "Instrument Sans", sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); border-bottom: 1px solid var(--line); }
  .row-table td { padding: 10px 14px; border-bottom: 1px solid rgba(26,35,50,0.04); }
  .row-table td.path { font-family: "JetBrains Mono", monospace; font-size: 12px; color: var(--ink); word-break: break-all; }
  .row-table td.num { text-align: right; font-family: "JetBrains Mono", monospace; font-variant-numeric: tabular-nums; color: var(--teal); }
  .row-table tr:last-child td { border-bottom: 0; }

  .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  @media (max-width: 760px) { .two-col { grid-template-columns: 1fr; } }

  .empty-mini { padding: 24px; text-align: center; color: var(--ink-soft); opacity: 0.6; font-style: italic; font-size: 13px; }

  .pill-row { display: flex; flex-wrap: wrap; gap: 8px; }
  .pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; background: rgba(3,97,122,0.06);
    border-radius: 999px; font-family: "JetBrains Mono", monospace; font-size: 11px;
    color: var(--ink); letter-spacing: 0.04em;
  }
  .pill .n { color: var(--teal); font-weight: 500; }
</style>
@include('admin.partials._typography')
</head>
<body>

@include('partials.site-menu')
<header class="top">
  <a href="{{ url('/admin') }}">← Admin</a>
  <span class="meta">analytics</span>
</header>

<main>
  <h1>Analytics.</h1>
  <div class="range-pills">
    @foreach ([7, 14, 30, 90] as $d)
      <a href="?days={{ $d }}" class="@if($days === $d) active @endif">Last {{ $d }} days</a>
    @endforeach
  </div>

  <div class="totals">
    <div class="stat">
      <div class="label">Page views</div>
      <div class="value">{{ number_format($totals->views ?? 0) }}</div>
      <div class="sub">in last {{ $days }} days</div>
    </div>
    <div class="stat">
      <div class="label">Unique visitors</div>
      <div class="value">{{ number_format($totals->uniques ?? 0) }}</div>
      <div class="sub">distinct sessions</div>
    </div>
    <div class="stat">
      <div class="label">Pages seen</div>
      <div class="value">{{ number_format($totals->pages ?? 0) }}</div>
      <div class="sub">distinct URLs</div>
    </div>
    <div class="stat">
      <div class="label">Avg pages/visitor</div>
      <div class="value">{{ ($totals->uniques ?? 0) > 0 ? number_format(($totals->views ?? 0) / $totals->uniques, 1) : '—' }}</div>
      <div class="sub">depth of session</div>
    </div>
  </div>

  {{-- Daily trend SVG --}}
  <div class="panel">
    <h2>Daily traffic</h2>
    @php
      $maxVal = max(1, collect($filled)->max('views') ?: 1);
      $w = 1280; $h = 180; $pad = 16;
      $n = max(1, count($filled));
      $stepX = $n > 1 ? ($w - 2*$pad) / ($n - 1) : 0;
      $points = [];
      $uniqPoints = [];
      foreach ($filled as $i => $row) {
        $x = $pad + $i * $stepX;
        $y = $h - $pad - (($row->views   / $maxVal) * ($h - 2*$pad));
        $u = $h - $pad - (($row->uniques / $maxVal) * ($h - 2*$pad));
        $points[] = round($x,1) . ',' . round($y,1);
        $uniqPoints[] = round($x,1) . ',' . round($u,1);
      }
      $area = 'M ' . $pad . ',' . ($h-$pad) . ' L ' . implode(' L ', $points) . ' L ' . ($pad + ($n-1)*$stepX) . ',' . ($h-$pad) . ' Z';
      $line = 'M ' . implode(' L ', $points);
      $uniqLine = 'M ' . implode(' L ', $uniqPoints);
    @endphp
    <svg class="trend" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" aria-label="Daily views and uniques">
      @for ($i = 1; $i <= 3; $i++)
        <line class="grid-line" x1="{{ $pad }}" y1="{{ $pad + $i * (($h - 2*$pad)/4) }}" x2="{{ $w - $pad }}" y2="{{ $pad + $i * (($h - 2*$pad)/4) }}"/>
      @endfor
      <path class="area" d="{{ $area }}"/>
      <path class="line" d="{{ $line }}"/>
      <path class="uniq-line" d="{{ $uniqLine }}"/>
    </svg>
    <div class="trend-legend">
      <span><span class="dot"></span> Views</span>
      <span><span class="dot uniq"></span> Unique visitors</span>
      <span style="margin-left:auto; opacity:0.6;">{{ $filled[0]->d ?? '' }} → {{ end($filled)->d ?? '' }}</span>
    </div>
  </div>

  {{-- Top pages --}}
  <div class="panel">
    <h2>Top pages</h2>
    @if (count($topPaths) > 0)
      <table class="row-table">
        <thead><tr><th>Path</th><th style="text-align:right;">Views</th><th style="text-align:right;">Uniques</th></tr></thead>
        <tbody>
          @foreach ($topPaths as $p)
            <tr>
              <td class="path">{{ $p->path }}</td>
              <td class="num">{{ number_format($p->views) }}</td>
              <td class="num">{{ number_format($p->uniques) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="empty-mini">No traffic yet — start sharing the site.</div>
    @endif
  </div>

  <div class="two-col">
    <div class="panel">
      <h2>Top referrers</h2>
      @if (count($topReferrers) > 0)
        <table class="row-table">
          <thead><tr><th>Source</th><th style="text-align:right;">Views</th></tr></thead>
          <tbody>
            @foreach ($topReferrers as $r)
              <tr><td class="path">{{ \Illuminate\Support\Str::limit($r->referrer, 60) }}</td><td class="num">{{ number_format($r->views) }}</td></tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="empty-mini">No external referrers yet.</div>
      @endif
    </div>

    <div class="panel">
      <h2>Devices</h2>
      @if (count($devices) > 0)
        <div class="pill-row">
          @foreach ($devices as $d)
            <span class="pill">{{ ucfirst($d->device) }} <span class="n">{{ number_format($d->views) }}</span></span>
          @endforeach
        </div>
        <h2 style="margin:18px 0 10px; font-size:11px; color:var(--ink-soft);">Browsers</h2>
        <div class="pill-row">
          @foreach ($browsers as $b)
            <span class="pill">{{ ucfirst($b->browser) }} <span class="n">{{ number_format($b->views) }}</span></span>
          @endforeach
        </div>
      @else
        <div class="empty-mini">No traffic yet.</div>
      @endif
    </div>
  </div>

  @if (count($countries) > 0)
    <div class="panel">
      <h2>Countries</h2>
      <div class="pill-row">
        @foreach ($countries as $c)
          <span class="pill">{{ $c->country }} <span class="n">{{ number_format($c->views) }}</span></span>
        @endforeach
      </div>
      <p style="margin-top:14px; font-size:11px; color:var(--ink-soft); opacity:0.65; font-family:'JetBrains Mono',monospace; letter-spacing:0.04em;">
        Country detection uses the CF-IPCountry header (only set if traffic flows through Cloudflare). Empty if not on CF.
      </p>
    </div>
  @endif

  <p style="margin-top:30px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.18em; color:var(--ink-soft); opacity:0.55; text-transform:uppercase;">
    First-party telemetry · No external trackers · IPs hashed · 90-day retention
  </p>
</main>
</body>
</html>
