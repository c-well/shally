<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Peace · Subscribers · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --ink-faint:rgba(26,35,50,0.45); --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  main { max-width: 980px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(20px, 3vw, 26px); font-weight: 500; letter-spacing: 0.04em; margin-bottom: 8px; text-transform: uppercase; }
  .lede { font-size: 14px; color: var(--ink-soft); margin-bottom: 24px; max-width: 660px; }
  .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 30px; }
  .kpi { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 16px 20px; }
  .kpi .lbl { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
  .kpi .num { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 500; color: var(--ink); line-height: 1; }
  .row { display: grid; grid-template-columns: 1fr auto auto auto auto; gap: 14px; align-items: center; padding: 12px 0; border-bottom: 1px dashed var(--line); font-size: 13px; }
  .row:last-child { border-bottom: none; }
  .email { font-family: 'JetBrains Mono', monospace; font-size: 12.5px; color: var(--ink); }
  .source { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; padding: 2px 7px; border-radius: 3px; background: rgba(176,141,60,0.10); color: var(--brass); }
  .verified { padding: 2px 7px; border-radius: 3px; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; }
  .verified.yes { background: rgba(45,134,89,0.12); color: #2d8659; }
  .verified.no  { background: rgba(168,42,31,0.10); color: #a82a1f; }
  .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-soft); white-space: nowrap; }
  .panel { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 14px 20px; }
  .empty { padding: 60px 0; text-align: center; color: var(--ink-soft); font-style: italic; }
</style>
</head>
<body>
@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.peace.index') }}">← Peace admin</a>
  <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;color:var(--ink-soft);opacity:0.65;">seekers</span>
</header>

<main>
  <h1>Subscribers</h1>
  <p class="lede">Seekers who signed up via magic-link on /find-peace. Email is the account — no passwords. Sources show which interaction triggered signup. Click-through verification + 60-day cookie session.</p>

  <div class="kpis">
    <div class="kpi"><div class="lbl">Total subscribers</div><div class="num">{{ number_format($subs->count()) }}</div></div>
    <div class="kpi"><div class="lbl">Verified</div><div class="num">{{ number_format($subs->whereNotNull('email_verified_at')->count()) }}</div></div>
    <div class="kpi"><div class="lbl">Newsletter opt-ins</div><div class="num">{{ number_format($subs->where('subscribed_newsletter', true)->count()) }}</div></div>
    <div class="kpi"><div class="lbl">Active last 7d</div><div class="num">{{ number_format($subs->filter(fn($s) => $s->last_seen_at && $s->last_seen_at->gte(now()->subDays(7)))->count()) }}</div></div>
  </div>

  <div class="panel">
    @forelse ($subs as $s)
      <div class="row">
        <span class="email">{{ $s->email }}</span>
        <span class="source">{{ $s->source ?? 'direct' }}</span>
        <span class="verified {{ $s->email_verified_at ? 'yes' : 'no' }}">{{ $s->email_verified_at ? 'verified' : 'pending' }}</span>
        <span class="meta">{{ $s->saved_qas_count }} saved · {{ $s->magic_links_count }} links</span>
        <span class="meta">joined {{ $s->created_at->diffForHumans() }}@if($s->last_seen_at) · seen {{ $s->last_seen_at->diffForHumans() }}@endif</span>
      </div>
    @empty
      <div class="empty">No subscribers yet. First signup will appear here.</div>
    @endforelse
  </div>
</main>
</body>
</html>
