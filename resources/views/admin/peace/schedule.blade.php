<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Peace · Schedule · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  main { max-width: 1000px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(20px, 3vw, 26px); font-weight: 500; letter-spacing: 0.04em; margin-bottom: 8px; text-transform: uppercase; }
  h2 { font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); font-weight: 500; margin: 36px 0 14px; }
  .lede { font-size: 14px; color: var(--ink-soft); max-width: 680px; margin-bottom: 30px; }
  .status { padding: 14px 18px; background: rgba(3,97,122,0.08); border-left: 4px solid var(--teal); border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; }

  .panel { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 18px 22px; margin-bottom: 16px; }
  .row { display: grid; grid-template-columns: auto 1fr auto; gap: 14px; align-items: baseline; padding: 12px 0; border-bottom: 1px dashed var(--line); }
  .row:last-child { border-bottom: none; }
  .kind { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; padding: 3px 8px; border-radius: 3px; }
  .kind.recurring { background: rgba(3,97,122,0.10); color: var(--teal); }
  .kind.one-off { background: rgba(176,141,60,0.15); color: var(--brass); }
  .cmd { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink); }
  .when { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-soft); white-space: nowrap; text-align: right; }
  .when .et { color: var(--teal); font-weight: 500; }
  .expr { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--ink-soft); opacity: 0.7; margin-top: 4px; }

  .trigger-form { margin: 16px 0 0; display: inline-flex; gap: 10px; align-items: center; }
  .btn { padding: 9px 18px; background: var(--teal); color: #fff; border: 0; text-decoration: none; border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; cursor: pointer; }
  .btn:hover { background: var(--teal-dark); }
  .btn.ghost { background: transparent; color: var(--ink-soft); border: 1px solid var(--line); }
  .btn.ghost:hover { color: var(--teal); border-color: var(--teal); }

  .sermon-row { display: grid; grid-template-columns: 100px 1fr auto; gap: 14px; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--line); }
  .sermon-row:last-child { border-bottom: none; }
  .sermon-row .date { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.08em; color: var(--ink-soft); text-transform: uppercase; }
  .sermon-row .title { font-family: 'Cormorant Garamond', serif; font-size: 16px; color: var(--ink); }
  .sermon-row .title a { color: var(--ink); text-decoration: none; }
  .sermon-row .title a:hover { color: var(--teal); }
  .sermon-row .stat { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; padding: 3px 8px; border-radius: 3px; }
  .stat.published { background: rgba(45,134,89,0.12); color: #2d8659; }
  .stat.pending_review { background: rgba(176,141,60,0.15); color: var(--brass); }
  .stat.draft { background: rgba(168,42,31,0.10); color: #a82a1f; }
  .stat.soft_discarded, .stat.trashed { background: rgba(26,35,50,0.08); color: var(--ink-soft); }
  .speaker { font-size: 12px; color: var(--ink-soft); margin-top: 2px; }
  .deadline { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--ink-soft); margin-top: 2px; opacity: 0.7; }

  .log-tail { background: #1a2332; color: #fefcef; padding: 14px 18px; border-radius: 6px; font-family: 'JetBrains Mono', monospace; font-size: 11px; line-height: 1.55; overflow-x: auto; }
  .log-tail .line { white-space: pre; }
  .log-tail.empty { background: rgba(26,35,50,0.04); color: var(--ink-soft); font-style: italic; }

  .empty-msg { padding: 30px 0; text-align: center; color: var(--ink-soft); font-size: 13px; }
</style>
</head>
<body>
@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.peace.index') }}">← Peace admin</a>
  <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;color:var(--ink-soft);opacity:0.65;">automation</span>
</header>

<main>
  <h1>Schedule</h1>
  <p class="lede">Saturday 3 PM ET the cron scans @gotoshalom for the latest Sabbath stream, runs the Peace pipeline, and emails you for approval. Hourly the state-machine tick handles deadlines. Cookies-authenticated yt-dlp is wired via <code style="background:rgba(26,35,50,0.05);padding:1px 5px;border-radius:3px;font-family:'JetBrains Mono',monospace;font-size:12px;">~/bin/ytdlp-auth</code>.</p>

  @if (session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif

  <h2>Upcoming runs</h2>
  <div class="panel">
    @forelse ($scheduled as $row)
      <div class="row">
        <span class="kind {{ $row['kind'] }}">{{ $row['kind'] }}</span>
        <div>
          <div class="cmd">{{ $row['command'] }}</div>
          <div class="expr">{{ $row['expression'] }}</div>
        </div>
        <div class="when">
          @if ($row['next_run_et'])
            <span class="et">{{ $row['next_run_et']->format('D M j · g:i A') }}</span><br>
            <span style="opacity:0.7;">{{ $row['next_run_et']->diffForHumans() }} (ET)</span>
          @else
            <span style="opacity:0.6;">never</span>
          @endif
        </div>
      </div>
    @empty
      <div class="empty-msg">No peace tasks scheduled.</div>
    @endforelse
  </div>

  <form action="{{ route('admin.peace.schedule.trigger') }}" method="POST" class="trigger-form"
        onsubmit="return confirm('Fire peace:scan-channel right now? This costs ~$0.13 in API and triggers an email.');">
    @csrf
    <input type="hidden" name="confirm" value="yes">
    <button type="submit" class="btn">Fire now →</button>
    <span style="font-size:12px;color:var(--ink-soft);">Useful if you missed Saturday or want a manual test run.</span>
  </form>

  <h2>Recent sermons (top of queue)</h2>
  <div class="panel">
    @forelse ($recentSermons as $s)
      <div class="sermon-row">
        <span class="date">{{ optional($s->sermon_date)->format('M j Y') }}</span>
        <div>
          <div class="title"><a href="{{ route('admin.peace.edit', $s->slug) }}">{{ $s->title }}</a></div>
          <div class="speaker">{{ $s->speaker ?? '—' }}</div>
          @if ($s->processing_status === 'pending_review' && $s->review_deadline)
            <div class="deadline">expires {{ $s->review_deadline->setTimezone('America/New_York')->format('D M j g:i A') }} ET ({{ $s->review_deadline->diffForHumans() }})</div>
          @endif
        </div>
        <span class="stat {{ $s->processing_status }}">{{ str_replace('_', ' ', $s->processing_status) }}</span>
      </div>
    @empty
      <div class="empty-msg">No sermons yet.</div>
    @endforelse
  </div>

  <h2>Pipeline log tail</h2>
  @if (empty($logTail))
    <div class="log-tail empty">No log entries yet — first fire will populate this.</div>
  @else
    @foreach ($logTail as $name => $lines)
      <div style="margin-bottom:8px;font-family:'JetBrains Mono',monospace;font-size:10px;letter-spacing:0.14em;color:var(--ink-soft);text-transform:uppercase;">{{ $name }}</div>
      <div class="log-tail">@foreach ($lines as $l)<div class="line">{{ $l }}</div>@endforeach</div>
    @endforeach
  @endif
</main>
</body>
</html>
