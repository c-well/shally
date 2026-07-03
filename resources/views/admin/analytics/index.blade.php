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
    padding: 7px 14px; border: 1px solid var(--line); border-radius: var(--r-btn, 8px);
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
  .trend .grid-line { stroke: color-mix(in srgb, var(--ink) 8%, transparent); stroke-width: 1; }
  .trend .area { fill: color-mix(in srgb, var(--teal) 10%, transparent); }
  .trend .line { fill: none; stroke: var(--teal); stroke-width: 2; }
  .trend .uniq-line { fill: none; stroke: var(--brass); stroke-width: 2; stroke-dasharray: 4,3; }
  .trend-legend { display: flex; gap: 18px; margin-top: 10px; font-family: "JetBrains Mono", monospace; font-size: 10px; letter-spacing: 0.12em; color: var(--ink-soft); text-transform: uppercase; }
  .trend-legend .dot { display: inline-block; width: 10px; height: 2px; background: var(--teal); vertical-align: middle; margin-right: 6px; }
  .trend-legend .dot.uniq { background: var(--brass); }

  .row-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  .row-table th { text-align: left; padding: 10px 14px; font-family: "Instrument Sans", sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); border-bottom: 1px solid var(--line); }
  .row-table td { padding: 10px 14px; border-bottom: 1px solid color-mix(in srgb, var(--ink) 4%, transparent); }
  .row-table td.path { font-family: "JetBrains Mono", monospace; font-size: 12px; color: var(--ink); word-break: break-all; }
  .row-table td.num { text-align: right; font-family: "JetBrains Mono", monospace; font-variant-numeric: tabular-nums; color: var(--teal); }
  .row-table tr:last-child td { border-bottom: 0; }

  .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  @media (max-width: 760px) { .two-col { grid-template-columns: 1fr; } }

  .empty-mini { padding: 24px; text-align: center; color: var(--ink-soft); opacity: 0.6; font-style: italic; font-size: 13px; }

  .pill-row { display: flex; flex-wrap: wrap; gap: 8px; }
  .pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; background: color-mix(in srgb, var(--teal) 6%, transparent);
    border-radius: 999px; font-family: "JetBrains Mono", monospace; font-size: 11px;
    color: var(--ink); letter-spacing: 0.04em;
  }
  .pill .n { color: var(--teal); font-weight: 500; }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

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

  {{-- ═══════════════════════════════════════════════════════════════════
       INTERACTION ANALYTICS — clicks, heatmap, scroll, named events, sessions
       Added 2026-05-30. Data from interaction_events table.
       ═══════════════════════════════════════════════════════════════════ --}}
  <h2 style="font-size:18px; font-weight:600; margin:36px 0 16px; padding-top:24px; border-top:2px solid var(--line);">Interaction analytics</h2>

  <div class="totals">
    <div class="stat">
      <div class="label">Total events</div>
      <div class="value">{{ number_format($interactionTotals->total_events ?? 0) }}</div>
      <div class="sub">{{ number_format($interactionTotals->sessions ?? 0) }} sessions</div>
    </div>
    <div class="stat">
      <div class="label">Clicks</div>
      <div class="value">{{ number_format($interactionTotals->clicks ?? 0) }}</div>
    </div>
    <div class="stat">
      <div class="label">Hovers (sampled)</div>
      <div class="value">{{ number_format($interactionTotals->hovers ?? 0) }}</div>
    </div>
    <div class="stat">
      <div class="label">Named events</div>
      <div class="value">{{ number_format($interactionTotals->named ?? 0) }}</div>
      <div class="sub">qa_open · audio · etc.</div>
    </div>
  </div>

  {{-- HEATMAP VIEWER --}}
  <section class="panel">
    <h2>Click hotspots</h2>
    <p style="font-size:12px; color:var(--ink-soft); margin:-8px 0 12px;">Each dot is a click. Brighter = more clicks. Hovers shown lighter.</p>
    @if ($heatmapPages->isEmpty())
      <p style="color:var(--ink-soft); font-size:13px;">No click data yet — once visitors interact, heatmaps populate here.</p>
    @else
      <form method="GET" action="{{ route('admin.analytics') }}" style="margin-bottom:14px;">
        <input type="hidden" name="days" value="{{ $days }}">
        <label style="font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:var(--ink-soft); margin-right:8px;">Page:</label>
        <select name="heatmap_path" onchange="this.form.submit()" style="font-family:'JetBrains Mono',monospace; font-size:11px; padding:5px 8px; border:1px solid var(--line); border-radius:4px;">
          @foreach ($heatmapPages as $hp)
            <option value="{{ $hp->path }}" @if($hp->path === $heatmapPath) selected @endif>{{ $hp->path }} ({{ $hp->clicks }})</option>
          @endforeach
        </select>
      </form>
      <div style="position:relative; background:#faf7f0; border:1px solid var(--line); border-radius:6px; overflow:hidden;">
        <div style="padding:16px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:var(--ink-soft); border-bottom:1px solid var(--line);">
          {{ $heatmapPath }} · {{ count($heatmapPoints) }} interaction points
        </div>
        <canvas id="heatmapCanvas" width="900" height="600" style="width:100%; max-width:900px; height:auto; display:block; background:#fff;"></canvas>
      </div>
      <p style="font-size:11px; color:var(--ink-soft); margin-top:10px; font-family:'JetBrains Mono',monospace; letter-spacing:0.06em;">
        Coordinates are visitor-viewport normalized. Aim for clusters in expected hotspots (CTAs, links). Empty regions = invisible content.
      </p>
    @endif
  </section>

  {{-- SCROLL DEPTH --}}
  <section class="panel">
    <h2>Scroll depth — how far visitors actually get</h2>
    @if ($scrollDepth->isEmpty())
      <p style="color:var(--ink-soft); font-size:13px;">No scroll data yet — captured on page unload.</p>
    @else
      <table style="width:100%; border-collapse:collapse; font-size:12px;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft);">Page</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">Sessions</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">10%</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">25%</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">50%</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">75%</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">100%</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($scrollDepth as $row)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:9px 6px; font-family:'JetBrains Mono',monospace; font-size:11px;">{{ $row->path }}</td>
              <td style="padding:9px 6px; text-align:right; font-family:'JetBrains Mono',monospace;">{{ $row->total }}</td>
              @foreach (['r10','r25','r50','r75','r100'] as $bucket)
                @php $pct = $row->total > 0 ? round(($row->$bucket / $row->total) * 100) : 0; @endphp
                <td style="padding:9px 6px; text-align:right; font-family:'JetBrains Mono',monospace; color:{{ $pct >= 75 ? '#2d8659' : ($pct >= 50 ? '#5b6478' : ($pct >= 25 ? '#b08a3e' : '#a82a1f')) }};">{{ $pct }}%</td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </section>

  {{-- NAMED EVENTS --}}
  <section class="panel">
    <h2>Named events</h2>
    @if ($namedEvents->isEmpty())
      <p style="color:var(--ink-soft); font-size:13px;">No named events yet. As visitors open Q&As, play sermons, download PDFs, etc., they appear here.</p>
    @else
      <table style="width:100%; border-collapse:collapse; font-size:12px;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft);">Event</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">Occurrences</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">Unique sessions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($namedEvents as $ev)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:9px 6px; font-family:'JetBrains Mono',monospace; font-size:11px;"><strong>{{ $ev->event_type }}</strong></td>
              <td style="padding:9px 6px; text-align:right; font-family:'JetBrains Mono',monospace;">{{ number_format($ev->occurrences) }}</td>
              <td style="padding:9px 6px; text-align:right; font-family:'JetBrains Mono',monospace; color:var(--ink-soft);">{{ number_format($ev->uniques) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </section>

  {{-- CLICK LEADERBOARD --}}
  <section class="panel">
    <h2>Top clicked elements</h2>
    @if ($topClicks->isEmpty())
      <p style="color:var(--ink-soft); font-size:13px;">No click data yet.</p>
    @else
      <table style="width:100%; border-collapse:collapse; font-size:12px;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft);">Element</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft);">Page</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">Clicks</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($topClicks as $c)
            <tr style="border-bottom:1px solid var(--line);">
              <td style="padding:9px 6px; font-size:11px; max-width:340px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                @if($c->element_text)<strong>{{ Str::limit($c->element_text, 50) }}</strong>@endif
                <span style="font-family:'JetBrains Mono',monospace; font-size:10px; color:var(--ink-soft); display:block;">{{ Str::limit($c->element_selector, 80) }}</span>
              </td>
              <td style="padding:9px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; color:var(--ink-soft);">{{ $c->path }}</td>
              <td style="padding:9px 6px; text-align:right; font-family:'JetBrains Mono',monospace;">{{ $c->clicks }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </section>

  {{-- SESSION TRAIL --}}
  <section class="panel">
    <h2>Recent visitor sessions</h2>
    <p style="font-size:12px; color:var(--ink-soft); margin:-8px 0 12px;">Click a session to see the event trail.</p>
    @if ($recentSessions->isEmpty())
      <p style="color:var(--ink-soft); font-size:13px;">No qualifying sessions yet (need ≥3 events to surface).</p>
    @else
      <table style="width:100%; border-collapse:collapse; font-size:12px;">
        <thead>
          <tr style="text-align:left; border-bottom:1px solid var(--line);">
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft);">Started</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft);">Device</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">Events</th>
            <th style="padding:8px 6px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.12em; color:var(--ink-soft); text-align:right;">Pages</th>
            <th style="padding:8px 6px;"></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($recentSessions as $s)
            <tr style="border-bottom:1px solid var(--line); @if($selectedSession === $s->session_id) background:rgba(176,138,62,0.08); @endif">
              <td style="padding:9px 6px; font-family:'JetBrains Mono',monospace; font-size:11px;">{{ \Carbon\Carbon::parse($s->started)->diffForHumans() }}</td>
              <td style="padding:9px 6px; font-family:'JetBrains Mono',monospace; font-size:11px;">{{ $s->device ?? '—' }} {{ $s->country ? '· '.$s->country : '' }}</td>
              <td style="padding:9px 6px; text-align:right; font-family:'JetBrains Mono',monospace;">{{ $s->events }}</td>
              <td style="padding:9px 6px; text-align:right; font-family:'JetBrains Mono',monospace;">{{ $s->pages }}</td>
              <td style="padding:9px 6px; text-align:right;">
                <a href="{{ route('admin.analytics') }}?days={{ $days }}&session={{ $s->session_id }}" style="font-family:'Instrument Sans',sans-serif; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:var(--teal); text-decoration:none;">View trail →</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      @if ($selectedSession && $sessionTrail)
        <div style="margin-top:22px; padding:14px 16px; background:#faf7f0; border:1px solid var(--line); border-radius:6px;">
          <div style="font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:var(--ink-soft); margin-bottom:12px;">
            Trail · session {{ substr($selectedSession,0,8) }}… · {{ count($sessionTrail) }} events
          </div>
          <ol style="list-style:none; padding:0; max-height:380px; overflow-y:auto;">
            @foreach ($sessionTrail as $ev)
              <li style="padding:5px 0; font-family:'JetBrains Mono',monospace; font-size:11px; border-bottom:1px dashed rgba(0,0,0,0.06);">
                <span style="color:var(--ink-soft); font-size:9px;">{{ \Carbon\Carbon::parse($ev->created_at)->format('H:i:s') }}</span>
                <strong style="color:var(--teal); margin-left:8px;">{{ $ev->event_type }}</strong>
                <span style="color:var(--ink-soft); margin-left:8px;">{{ $ev->path }}</span>
                @if($ev->element_text)<span style="margin-left:8px;">"{{ Str::limit($ev->element_text, 50) }}"</span>@endif
              </li>
            @endforeach
          </ol>
        </div>
      @endif
    @endif
  </section>

  <p style="margin-top:30px; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:0.18em; color:var(--ink-soft); opacity:0.55; text-transform:uppercase;">
    First-party telemetry · No external trackers · IPs hashed · 90-day retention<br>
    Interaction events 30-day raw retention + rolled summaries
  </p>

  {{-- HEATMAP CANVAS RENDERING --}}
  @if(!$heatmapPages->isEmpty())
  <script>
  (function () {
    var canvas = document.getElementById('heatmapCanvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var points = @json($heatmapPoints);

    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Build density grid scaled to canvas viewport
    var W = canvas.width, H = canvas.height;
    points.forEach(function (p) {
      var srcW = p.viewport_w || 1280;
      var srcH = p.viewport_h || 800;
      var nx = (p.x / srcW) * W;
      var ny = (p.y / srcH) * H;
      var isClick = p.event_type === 'click';
      var radius = isClick ? 14 : 8;
      var color = isClick ? 'rgba(176,138,62,0.42)' : 'rgba(3,97,122,0.18)';
      var grad = ctx.createRadialGradient(nx, ny, 0, nx, ny, radius);
      grad.addColorStop(0, color);
      grad.addColorStop(1, 'rgba(0,0,0,0)');
      ctx.fillStyle = grad;
      ctx.beginPath();
      ctx.arc(nx, ny, radius, 0, Math.PI * 2);
      ctx.fill();
    });

    // Footer label
    ctx.fillStyle = 'rgba(0,0,0,0.45)';
    ctx.font = '11px monospace';
    ctx.fillText('Heatmap of clicks + hovers, normalized to 900×600 viewport.', 12, H - 12);
  })();
  </script>
  @endif
</main>
</body>
</html>
