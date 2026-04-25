<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>What happened — Shalom SDA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root {
    --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455;
    --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c;
    --line:rgba(26,35,50,0.10);
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  /* Top strip — minimal */
  .top {
    padding: 22px clamp(20px, 5vw, 40px);
    display: flex; align-items: center; justify-content: space-between;
  }
  .top a {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 600; letter-spacing: 0.18em;
    text-transform: uppercase; text-decoration: none; color: var(--ink-soft);
  }
  .top a:hover { color: var(--teal); }
  .top .meta {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px; letter-spacing: 0.14em;
    color: var(--ink-soft); opacity: 0.5;
    cursor: default; user-select: none;
    transition: opacity 0.18s, color 0.18s;
  }
  .top .meta.toggle { cursor: pointer; opacity: 0.7; }
  .top .meta.toggle:hover { opacity: 1; color: var(--teal); }
  .top .meta .meta-fade {
    display: inline-block;
    transition: opacity 0.4s ease;
  }

  main {
    max-width: 1080px; margin: 0 auto;
    padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px;
  }

  /* Hero */
  h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(48px, 7vw, 72px); font-weight: 500;
    letter-spacing: -0.035em; line-height: 1;
    color: var(--ink);
  }

  .summary {
    margin-top: 22px;
    font-family: 'Poppins', sans-serif;
    font-size: 15px; line-height: 1.6;
    color: var(--ink-soft);
  }
  .summary strong {
    color: var(--ink); font-weight: 600;
  }
  .summary .sep { opacity: 0.35; padding: 0 8px; }
  .summary .warn strong { color: var(--brass); }
  .summary .err strong  { color: #c0392b; }

  /* Filter — tab-style with teal underline on active */
  .filters {
    display: flex; gap: 28px; flex-wrap: wrap;
    margin: 56px 0 0;
    padding-bottom: 1px;
    border-bottom: 1px solid var(--line);
  }
  .filters a {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--ink-soft); text-decoration: none;
    padding: 10px 0;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color 0.15s, border-color 0.15s;
  }
  .filters a:hover { color: var(--ink); }
  .filters a.active { color: var(--ink); border-bottom-color: var(--teal); }

  /* Event list — single column rows, no heavy table chrome */
  .events {
    margin-top: 18px;
  }
  .event-row {
    display: grid;
    grid-template-columns: 130px 14px 1fr 160px;
    gap: 16px;
    align-items: baseline;
    padding: 18px 4px;
    border-bottom: 1px solid var(--line);
  }
  .event-row:last-child { border-bottom: 0; }
  .event-row .ts {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.04em;
    color: var(--ink-soft);
    white-space: nowrap;
    line-height: 1.4;
  }
  .event-row .ts .ago {
    display: block;
    font-size: 10px;
    opacity: 0.7;
    margin-top: 2px;
  }
  .event-row .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--ink-soft);
    align-self: center; justify-self: center;
    transform: translateY(1px);
  }
  .event-row .dot.login_success      { background: var(--teal); }
  .event-row .dot.logout             { background: var(--ink-soft); opacity: 0.6; }
  .event-row .dot.login_failed       { background: #c0392b; }
  .event-row .dot.magic_link_request { background: var(--brass); }
  .event-row .dot.magic_link_consume { background: var(--teal); }
  .event-row .dot[class*="error_"]   { background: #c0392b; }

  .event-row .body {
    font-size: 14px; line-height: 1.5; color: var(--ink);
  }
  .event-row .body .who {
    font-weight: 600; color: var(--ink);
  }
  .event-row .body .verb {
    color: var(--ink-soft);
  }
  .event-row .ip {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; color: var(--ink-soft);
    text-align: right; opacity: 0.7;
  }

  .empty {
    margin-top: 60px;
    text-align: center; padding: 60px 0;
    color: var(--ink-soft); font-style: italic;
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
  }

  /* Collapsed run — single row hiding N entries, click to expand */
  details.event-run { padding: 0; border-bottom: 1px solid var(--line); }
  details.event-run > summary {
    list-style: none; cursor: pointer;
    display: grid;
    grid-template-columns: 130px 14px 1fr 160px;
    gap: 16px;
    align-items: baseline;
    padding: 18px 4px;
    transition: background 0.12s;
  }
  details.event-run > summary::-webkit-details-marker { display: none; }
  details.event-run > summary:hover { background: rgba(3,97,122,0.04); }
  details.event-run[open] > summary {
    background: rgba(3,97,122,0.05);
    border-bottom: 1px dashed var(--line);
  }
  details.event-run > summary .body {
    font-size: 14px; color: var(--ink); line-height: 1.5;
  }
  details.event-run > summary .body .who { font-weight: 600; }
  details.event-run > summary .body .verb { color: var(--ink-soft); }
  details.event-run > summary .body .count {
    display: inline-block;
    margin-left: 8px;
    padding: 2px 9px;
    background: var(--teal); color: #fff;
    border-radius: 10px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.12em;
  }
  details.event-run > summary .body .chevron {
    color: var(--ink-soft); margin-left: 6px;
    font-size: 12px;
    transition: transform 0.18s;
    display: inline-block;
  }
  details.event-run[open] > summary .body .chevron { transform: rotate(90deg); }
  details.event-run .nested {
    background: rgba(26,35,50,0.02);
    padding: 4px 0 6px;
  }
  details.event-run .nested .event-row {
    padding: 10px 4px 10px 36px;  /* indented under the parent */
    border-bottom: 1px dotted var(--line);
  }
  details.event-run .nested .event-row:last-child { border-bottom: 0; }

  /* Pagination — Steve-cut: Prev · Page X of Y · Next */
  .pager {
    display: flex; align-items: center; justify-content: center;
    gap: 28px; margin: 40px 0 0;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
  }
  .pager a, .pager span.muted {
    color: var(--ink-soft); text-decoration: none;
    padding: 8px 0;
    transition: color 0.15s;
  }
  .pager a:hover { color: var(--teal); }
  .pager span.muted { opacity: 0.4; cursor: default; }
  .pager .pos {
    color: var(--ink); letter-spacing: 0.16em;
    font-family: 'JetBrains Mono', monospace;
    font-weight: 500; font-size: 12px;
  }

  @media (max-width: 720px) {
    .event-row {
      grid-template-columns: 14px 1fr;
      grid-template-areas: "dot body" ".  meta";
      gap: 12px;
    }
    .event-row .ts { grid-area: meta; padding-top: 4px; }
    .event-row .dot { grid-area: dot; }
    .event-row .body { grid-area: body; }
    .event-row .ip { grid-area: meta; text-align: left; }
    .event-row .ts::after { content: "  ·  "; opacity: 0.4; }
  }
</style>
@include('admin.partials._typography')
</head>
<body>

<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  @if (! empty($isKarlon))
    <span id="retention-badge" class="meta toggle" title="Tap to toggle"
          data-default="40-day retention"
          data-countdown="{{ $daysUntilNextPurge }}d until next purge">
      <span class="meta-fade" id="retention-text">40-day retention</span>
    </span>
  @else
    <span class="meta">40-day retention</span>
  @endif
</header>

<main>
  <h1>What happened.</h1>

  <p class="summary">
    Last 7 days &mdash;
    <strong>{{ $counts['total'] }}</strong> {{ $counts['total'] === 1 ? 'event' : 'events' }}
    <span class="sep">·</span>
    <strong>{{ $counts['logins'] }}</strong> {{ $counts['logins'] === 1 ? 'sign-in' : 'sign-ins' }}
    <span class="sep">·</span>
    <span class="warn"><strong>{{ $counts['failed'] }}</strong> failed</span>
    <span class="sep">·</span>
    <strong>{{ $counts['magic'] }}</strong> magic
    <span class="sep">·</span>
    <span class="err"><strong>{{ $counts['errors'] }}</strong> errors</span>
  </p>

  <nav class="filters">
    <a href="{{ url('/admin/logs') }}" class="{{ !$event ? 'active' : '' }}">All</a>
    <a href="{{ url('/admin/logs?event=login_success') }}" class="{{ $event === 'login_success' ? 'active' : '' }}">Logins</a>
    <a href="{{ url('/admin/logs?event=login_failed') }}" class="{{ $event === 'login_failed' ? 'active' : '' }}">Failed</a>
    <a href="{{ url('/admin/logs?event=magic_link_request') }}" class="{{ $event === 'magic_link_request' ? 'active' : '' }}">Magic requests</a>
    <a href="{{ url('/admin/logs?event=magic_link_consume') }}" class="{{ $event === 'magic_link_consume' ? 'active' : '' }}">Magic used</a>
    <a href="{{ url('/admin/logs?event=error_500') }}" class="{{ $event === 'error_500' ? 'active' : '' }}">Errors</a>
  </nav>

  @if (! empty($isKarlon))
    <script>
      (function () {
        const badge = document.getElementById('retention-badge');
        const text  = document.getElementById('retention-text');
        if (!badge || !text) return;
        const def = badge.dataset.default;
        const cd  = badge.dataset.countdown;
        let mode = 'default'; // 'default' or 'countdown'
        let timer = null;

        function setText(t) {
          text.style.opacity = '0';
          setTimeout(() => { text.textContent = t; text.style.opacity = '1'; }, 350);
        }
        function scheduleFlip() {
          clearTimeout(timer);
          timer = setTimeout(() => {
            mode = (mode === 'default') ? 'countdown' : 'default';
            setText(mode === 'default' ? def : cd);
            scheduleFlip();
          }, 30000); // 30s
        }
        scheduleFlip();

        badge.addEventListener('click', () => {
          // Manual press: flip immediately + reset the 30s timer
          mode = (mode === 'default') ? 'countdown' : 'default';
          setText(mode === 'default' ? def : cd);
          scheduleFlip();
        });
      })();
    </script>
  @endif

  @if ($logs->isEmpty())
    <div class="empty">Nothing to see here.</div>
  @else
    <div class="events">
      @foreach ($groups as $group)
        @php $runCount = count($group['logs']); @endphp

        @if ($runCount <= 5)
          {{-- Small run — render each entry individually --}}
          @foreach ($group['logs'] as $log)
            @php $nycTime = $log->created_at->copy()->setTimezone('America/New_York'); @endphp
            <div class="event-row">
              <div class="ts" title="{{ $nycTime->format('Y-m-d H:i:s T') }}">
                {{ $nycTime->format('M j · g:i:sa') }} <span style="opacity:0.6;">{{ $nycTime->format('T') }}</span>
                <span class="ago">{{ $log->created_at->diffForHumans() }}</span>
              </div>
              <div class="dot {{ $log->event }}"></div>
              <div class="body">
                @if ($log->user)<span class="who">{{ $log->user->name }}</span>@endif
                <span class="verb">{{ $log->description }}</span>
              </div>
              <div class="ip">{{ $log->ip_address ?? '' }}</div>
            </div>
          @endforeach
        @else
          {{-- Big run — collapse into one summary row, expand to show all --}}
          @php
            $first = $group['logs'][0];               // most recent (logs are desc)
            $last  = $group['logs'][$runCount - 1];   // oldest in this run
            $mostRecentNyc = $first->created_at->copy()->setTimezone('America/New_York');
            $whoName = $first->user?->name ?? '—';
            $verbLabel = match($first->event) {
              'login_success'      => 'signed in',
              'login_failed'       => 'failed to sign in',
              'logout'             => 'signed out',
              'magic_link_request' => 'requested magic links',
              'magic_link_consume' => 'used magic links',
              default              => str_replace('_', ' ', $first->event),
            };
          @endphp
          <details class="event-run">
            <summary>
              <div class="ts" title="Most recent: {{ $mostRecentNyc->format('Y-m-d H:i:s T') }}">
                {{ $mostRecentNyc->format('M j · g:i:sa') }} <span style="opacity:0.6;">{{ $mostRecentNyc->format('T') }}</span>
                <span class="ago">most recent · {{ $first->created_at->diffForHumans() }}</span>
              </div>
              <div class="dot {{ $first->event }}"></div>
              <div class="body">
                <span class="who">{{ $whoName }}</span>
                <span class="verb">{{ $verbLabel }}</span>
                <span class="count">× {{ $runCount }}</span>
                <span class="chevron">▸</span>
              </div>
              <div class="ip">{{ $first->ip_address }}</div>
            </summary>
            <div class="nested">
              @foreach ($group['logs'] as $log)
                @php $nyc = $log->created_at->copy()->setTimezone('America/New_York'); @endphp
                <div class="event-row">
                  <div class="ts" title="{{ $nyc->format('Y-m-d H:i:s T') }}">
                    {{ $nyc->format('M j · g:i:sa') }} <span style="opacity:0.6;">{{ $nyc->format('T') }}</span>
                    <span class="ago">{{ $log->created_at->diffForHumans() }}</span>
                  </div>
                  <div class="dot {{ $log->event }}"></div>
                  <div class="body">
                    <span class="verb">{{ $log->description }}</span>
                  </div>
                  <div class="ip">{{ $log->ip_address ?? '—' }}</div>
                </div>
              @endforeach
            </div>
          </details>
        @endif
      @endforeach
    </div>

    @if ($logs->hasPages())
      <nav class="pager" role="navigation" aria-label="Audit log pagination">
        @if ($logs->onFirstPage())
          <span class="muted">← Previous</span>
        @else
          <a href="{{ $logs->previousPageUrl() }}">← Previous</a>
        @endif

        <span class="pos">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>

        @if ($logs->hasMorePages())
          <a href="{{ $logs->nextPageUrl() }}">Next →</a>
        @else
          <span class="muted">Next →</span>
        @endif
      </nav>
    @endif
  @endif
</main>

</body>
</html>
