<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Security · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --ink-faint:rgba(26,35,50,0.45); --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --red:#a82a1f; --green:#2d8659; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  main { max-width: 1080px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(20px, 3vw, 26px); font-weight: 500; letter-spacing: 0.04em; margin-bottom: 8px; text-transform: uppercase; }
  h2 { font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); font-weight: 500; margin: 36px 0 14px; }
  .lede { font-size: 14px; color: var(--ink-soft); max-width: 720px; margin-bottom: 30px; }
  .status { padding: 14px 18px; background: rgba(3,97,122,0.08); border-left: 4px solid var(--teal); border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; }

  .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 36px; }
  .kpi { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 16px 20px; }
  .kpi .lbl { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
  .kpi .num { font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 500; line-height: 1; color: var(--ink); }
  .kpi.warn .num { color: var(--red); }
  .kpi.ok .num { color: var(--green); }
  .kpi .sub { font-size: 11px; color: var(--ink-soft); margin-top: 4px; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.04em; }

  .panel { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 14px 20px; margin-bottom: 24px; }
  .row { display: grid; grid-template-columns: auto 1fr auto auto; gap: 14px; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--line); font-size: 13px; }
  .row:last-child { border-bottom: none; }
  .ip { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink); }
  .reason { color: var(--ink-soft); font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 420px; }
  .when { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-faint); white-space: nowrap; }
  .pill { display:inline-block; padding: 2px 7px; border-radius: 3px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; }
  .pill.blocked { background: rgba(168,42,31,0.10); color: var(--red); }
  .pill.warn { background: rgba(176,141,60,0.15); color: var(--brass); }
  .pill.ok { background: rgba(45,134,89,0.12); color: var(--green); }
  .pill.event { background: rgba(26,35,50,0.06); color: var(--ink-soft); }

  .btn { padding: 5px 11px; background: transparent; color: var(--ink-soft); border: 1px solid var(--line); border-radius: 3px; font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; cursor: pointer; text-decoration: none; }
  .btn:hover { color: var(--teal); border-color: var(--teal); }
  .btn.danger { color: var(--red); border-color: rgba(168,42,31,0.3); }
  .btn.danger:hover { background: rgba(168,42,31,0.08); }

  .csf-list { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-soft); line-height: 1.65; padding: 14px 18px; background: #1a2332; color: #fefcef; border-radius: 6px; overflow-x: auto; }
  .csf-list div { white-space: nowrap; }

  .empty { padding: 20px 0; text-align: center; color: var(--ink-soft); font-style: italic; font-size: 13px; }

  .columns { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
  @media (max-width: 700px) { .columns { grid-template-columns: 1fr; } }

  details.add-block { margin-bottom: 20px; padding: 14px 18px; background: rgba(3,97,122,0.04); border: 1px solid var(--line); border-radius: 6px; }
  details.add-block summary { cursor: pointer; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--teal); list-style: none; }
  details.add-block summary::-webkit-details-marker { display: none; }
  details.add-block form { margin-top: 14px; display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 10px; align-items: end; }
  details.add-block input, details.add-block select { padding: 7px 9px; background: #fff; border: 1px solid var(--line); border-radius: 4px; font-family: inherit; font-size: 13px; }
  details.add-block label { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 4px; display: block; }
</style>
</head>
<body>
@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;color:var(--ink-soft);opacity:0.65;">security</span>
</header>

<main>
  <h1>Security</h1>
  <p class="lede">App-level blocks (auto-populated when an IP crosses 20 failures/24h) and OS-level CSF firewall summary. The app blocks are managed here; CSF blocks are read-only — managed via cPanel or root SSH.</p>

  @if (session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif

  <div class="kpis">
    <div class="kpi {{ count($blocked) > 0 ? 'warn' : 'ok' }}">
      <div class="lbl">App blocks</div>
      <div class="num">{{ count($blocked) }}</div>
      <div class="sub">auto + manual</div>
    </div>
    <div class="kpi">
      <div class="lbl">CSF firewall</div>
      <div class="num">{{ $csf['available'] ? number_format($csf['total_blocked']) : '—' }}</div>
      <div class="sub">{{ $csf['available'] ? 'OS-level deny' : 'snapshot unavailable' }}</div>
    </div>
    <div class="kpi {{ count($topAbusers24h) > 0 ? 'warn' : 'ok' }}">
      <div class="lbl">Active threats 24h</div>
      <div class="num">{{ count($topAbusers24h) }}</div>
      <div class="sub">distinct attacking IPs</div>
    </div>
    <div class="kpi">
      <div class="lbl">Top abusers 30d</div>
      <div class="num">{{ count($topAbusers30d) }}</div>
      <div class="sub">tracked IPs</div>
    </div>
  </div>

  <details class="add-block">
    <summary>+ Manual block</summary>
    <form method="POST" action="{{ route('admin.security.block') }}">
      @csrf
      <div>
        <label>IP address</label>
        <input type="text" name="ip_address" required placeholder="45.148.10.25">
      </div>
      <div>
        <label>Reason</label>
        <input type="text" name="reason" placeholder="why blocking?">
      </div>
      <div>
        <label>Duration</label>
        <select name="duration">
          <option value="24h">24 hours</option>
          <option value="7d">7 days</option>
          <option value="30d">30 days</option>
          <option value="permanent">Permanent</option>
        </select>
      </div>
      <button type="submit" class="btn">Block →</button>
    </form>
  </details>

  <h2>App-level blocks ({{ count($blocked) }})</h2>
  <div class="panel">
    @forelse ($blocked as $b)
      <div class="row">
        <span class="ip">{{ $b->ip_address }}</span>
        <span class="reason" title="{{ $b->reason }}">{{ $b->reason ?? '—' }}</span>
        <span class="when">
          @php $isExpired = $b->expires_at && \Illuminate\Support\Carbon::parse($b->expires_at)->isPast(); @endphp
          @if ($b->expires_at)
            expires {{ \Illuminate\Support\Carbon::parse($b->expires_at)->diffForHumans() }}
          @else
            <span class="pill warn">permanent</span>
          @endif
        </span>
        <form method="POST" action="{{ route('admin.security.unblock', $b->id) }}" onsubmit="return confirm('Unblock {{ $b->ip_address }}?');" style="display:inline;">
          @csrf
          <button type="submit" class="btn danger">Unblock</button>
        </form>
      </div>
    @empty
      <div class="empty">No app-level blocks. Auto-blocker is armed — fires after 20 failures from one IP in 24h.</div>
    @endforelse
  </div>

  <div class="columns">
    <div>
      <h2>Top abusers — last 24h</h2>
      <div class="panel">
        @forelse ($topAbusers24h as $r)
          <div class="row" style="grid-template-columns: 1fr auto auto;">
            <span class="ip">{{ $r->ip_address ?? '(unknown)' }}</span>
            <span class="when">{{ $r->n }} attempts</span>
            @if ($r->is_blocked)
              <span class="pill blocked">blocked</span>
            @else
              <span class="pill warn">active</span>
            @endif
          </div>
        @empty
          <div class="empty">Quiet — no failed-auth bursts in the last 24h.</div>
        @endforelse
      </div>
    </div>
    <div>
      <h2>Top abusers — last 30 days</h2>
      <div class="panel">
        @forelse ($topAbusers30d as $r)
          <div class="row" style="grid-template-columns: 1fr auto auto;">
            <span class="ip">{{ $r->ip_address ?? '(unknown)' }}</span>
            <span class="when">{{ $r->n }} ({{ $r->distinct_events }} kinds)</span>
            @if ($r->is_blocked)
              <span class="pill blocked">blocked</span>
            @else
              <span class="pill ok">low</span>
            @endif
          </div>
        @empty
          <div class="empty">No activity tracked.</div>
        @endforelse
      </div>
    </div>
  </div>

  <h2>Recent failed-auth events (last 50)</h2>
  <div class="panel">
    @forelse ($recent as $log)
      <div class="row" style="grid-template-columns: auto auto 1fr auto;">
        <span class="pill event">{{ $log->event }}</span>
        <span class="ip">{{ $log->ip_address ?? '(?)' }}</span>
        <span class="reason" title="{{ $log->description }}">{{ \Illuminate\Support\Str::limit($log->description, 100) }}</span>
        <span class="when" title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</span>
      </div>
    @empty
      <div class="empty">No recent failed-auth events. Good news.</div>
    @endforelse
  </div>

  <h2>CSF firewall snapshot</h2>
  @if ($csf['available'])
    <p style="font-size: 13px; color: var(--ink-soft); margin-bottom: 14px;">
      <strong>{{ number_format($csf['total_blocked']) }} IPs blocked at firewall level.</strong>
      Sample (10 most-recent additions). Updated {{ \Illuminate\Support\Carbon::parse($csf['updated_at'])->diffForHumans() }}.
      Full management via <code style="background:rgba(26,35,50,0.05);padding:2px 6px;border-radius:3px;">cPanel → Security → ConfigServer Security</code>.
    </p>
    <div class="csf-list">
      @foreach ($csf['recent_top'] as $line)
        <div>{{ $line }}</div>
      @endforeach
    </div>
  @else
    <div class="empty">CSF snapshot unavailable — check that the root cron <code>csf-stats-snapshot.sh</code> is running.</div>
  @endif
</main>
</body>
</html>
