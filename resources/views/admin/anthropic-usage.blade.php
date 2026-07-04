<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>API spend — Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 1080px; margin: 0 auto; padding: clamp(40px, 7vh, 70px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(28px, 4vw, 36px); font-weight: 500; letter-spacing: 0.02em; margin-bottom: 8px; }
  .lede { color: var(--ink-soft); font-size: 14px; max-width: 640px; line-height: 1.55; margin-bottom: 2.5rem; }
  .lede strong { color: var(--ink); font-weight: 600; }

  .totals { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 2.5rem; }
  .total-card { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 18px 20px; }
  .total-card .label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 8px; font-weight: 600; }
  .total-card .value { font-family: 'Instrument Sans', sans-serif; font-size: 2.2rem; font-weight: 500; color: var(--ink); line-height: 1; }
  .total-card .value.small { font-size: 1.6rem; }
  .total-card .sub { font-size: 12px; color: var(--ink-soft); margin-top: 6px; }

  h2 { font-family: 'Instrument Sans', sans-serif; font-size: 1.6rem; font-weight: 500; margin: 2.5rem 0 1rem; color: var(--ink); }

  table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid var(--line); border-radius: 6px; overflow: hidden; }
  table th { font-family: 'Instrument Sans', sans-serif; font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); padding: 12px 14px; text-align: left; background: color-mix(in srgb, var(--ink) 3%, transparent); font-weight: 600; }
  table td { padding: 12px 14px; border-top: 1px solid var(--line); font-size: 14px; color: var(--ink); }
  table td.num { font-family: 'JetBrains Mono', monospace; text-align: right; font-size: 13px; }
  table td.src { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink-soft); }

  .recent table td { font-size: 13px; }
  .recent .ok { color: var(--green, #2d8659); }
  .recent .err { color: var(--red, #a82a1f); }

  .pricing-note { margin-top: 3rem; padding: 14px 18px; background: color-mix(in srgb, var(--brass) 7%, transparent); border-left: 3px solid var(--brass); border-radius: 0 4px 4px 0; font-size: 13px; color: var(--ink-soft); line-height: 1.6; }
  .pricing-note strong { color: var(--ink); }
  .pricing-note code { font-family: 'JetBrains Mono', monospace; font-size: 11px; background: color-mix(in srgb, var(--teal) 6%, transparent); padding: 1px 5px; border-radius: 3px; }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">ANTHROPIC API SPEND</span>
</div>

<main>
  <h1>API spend.</h1>
  <p class="lede">Live tally of every Anthropic call the Shalom app makes. <strong>This shows ONLY calls from the Shalom server</strong> — calls from elsewhere (Claude Code, skills, etc.) appear in the Anthropic Console but not here. Pricing snapshot from <code>config/anthropic_pricing.php</code> ({{ config('anthropic_pricing.as_of') }}).</p>

  <div class="totals">
    <div class="total-card">
      <div class="label">Today</div>
      <div class="value">${{ number_format($totalToday, 2) }}</div>
      <div class="sub">{{ $callsToday }} call{{ $callsToday === 1 ? '' : 's' }}</div>
    </div>
    <div class="total-card">
      <div class="label">Last 7 days</div>
      <div class="value">${{ number_format($total7d, 2) }}</div>
      <div class="sub">{{ $calls7d }} call{{ $calls7d === 1 ? '' : 's' }}</div>
    </div>
    <div class="total-card">
      <div class="label">This month</div>
      <div class="value">${{ number_format($totalMonth, 2) }}</div>
      <div class="sub">{{ $callsMonth }} call{{ $callsMonth === 1 ? '' : 's' }}</div>
    </div>
    <div class="total-card">
      <div class="label">All time</div>
      <div class="value small">${{ number_format($totalAll, 2) }}</div>
      <div class="sub">{{ $callsAll }} call{{ $callsAll === 1 ? '' : 's' }}</div>
    </div>
  </div>

  <h2>By source — last 30 days</h2>
  @if (count($bySource) === 0)
    <p style="color:var(--ink-soft);font-style:italic;">No calls yet. Once the Sabbath scan fires or someone uses the feedback bot, this fills in.</p>
  @else
    <table>
      <thead><tr><th>Source</th><th style="text-align:right;">Calls</th><th style="text-align:right;">Input tokens</th><th style="text-align:right;">Output tokens</th><th style="text-align:right;">Spend</th></tr></thead>
      <tbody>
        @foreach ($bySource as $row)
          <tr>
            <td class="src">{{ $row['source'] }}</td>
            <td class="num">{{ number_format($row['calls']) }}</td>
            <td class="num">{{ number_format($row['input']) }}</td>
            <td class="num">{{ number_format($row['output']) }}</td>
            <td class="num"><strong>${{ number_format($row['total'], 4) }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <h2>By model — last 30 days</h2>
  @if (count($byModel) === 0)
    <p style="color:var(--ink-soft);font-style:italic;">No calls yet.</p>
  @else
    <table>
      <thead><tr><th>Model</th><th style="text-align:right;">Calls</th><th style="text-align:right;">Spend</th></tr></thead>
      <tbody>
        @foreach ($byModel as $row)
          <tr>
            <td class="src">{{ $row['model'] }}</td>
            <td class="num">{{ number_format($row['calls']) }}</td>
            <td class="num"><strong>${{ number_format($row['total'], 4) }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <h2>Recent calls — last 50</h2>
  <div class="recent">
    @if ($recent->count() === 0)
      <p style="color:var(--ink-soft);font-style:italic;">Nothing logged yet.</p>
    @else
      <table>
        <thead><tr><th>When</th><th>Source</th><th>Model</th><th style="text-align:right;">In</th><th style="text-align:right;">Out</th><th style="text-align:right;">Cost</th></tr></thead>
        <tbody>
          @foreach ($recent as $r)
            <tr>
              <td>{{ $r->created_at->diffForHumans() }}</td>
              <td class="src {{ $r->outcome === 'ok' ? 'ok' : 'err' }}">{{ $r->source }}</td>
              <td class="src">{{ $r->model }}</td>
              <td class="num">{{ number_format($r->input_tokens) }}</td>
              <td class="num">{{ number_format($r->output_tokens) }}</td>
              <td class="num">${{ number_format($r->cost_usd, 4) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <div class="pricing-note">
    <strong>Pricing snapshot:</strong> rates are read from <code>config/anthropic_pricing.php</code> at log-time. When Anthropic changes prices, edit that file and the new rates apply to future calls. Existing log rows preserve their original cost calculation.<br>
    <strong>Note:</strong> this dashboard ONLY shows calls Shalom makes. Any other tool using your API key (Claude Code, Skills, etc.) is invisible here — for the complete picture across all tools, check the Anthropic Console.
  </div>
</main>

</body>
</html>
