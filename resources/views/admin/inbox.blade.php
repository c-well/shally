<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
    <link rel="shortcut icon" href="/favicon.ico?v=2">
    @include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Inbox — admin · Shalom</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Cormorant Garamond', serif; min-height: 100dvh; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 980px; margin: 0 auto; padding: clamp(36px, 7vh, 64px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: 22px; font-weight: 600; letter-spacing: 0.02em; margin-bottom: 6px; }
  .lede { font-size: 17px; color: var(--ink-soft); margin-bottom: 28px; }

  .filter-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
  .filter-pill {
    padding: 7px 16px;
    background: transparent;
    border: 1px solid var(--line);
    border-radius: var(--r-btn, 8px);
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 600;
    letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--ink-soft);
    text-decoration: none;
    transition: color 0.15s, border-color 0.15s, background 0.15s;
  }
  .filter-pill:hover { color: var(--teal); border-color: var(--teal); }
  .filter-pill.active { background: var(--ink); color: #fff; border-color: var(--ink); }
  .filter-pill .count { opacity: 0.6; margin-left: 4px; font-weight: 500; }

  .flash {
    margin-bottom: 22px; padding: 12px 16px;
    background: rgba(94,199,144,0.12); border: 1px solid rgba(94,199,144,0.30);
    border-radius: 8px;
    font-family: 'Instrument Sans', sans-serif; font-size: 13px;
    color: #1f6843;
  }

  .ticket {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 22px 24px;
    margin-bottom: 14px;
  }
  .ticket-head {
    display: flex; justify-content: space-between; align-items: baseline;
    gap: 12px; flex-wrap: wrap; margin-bottom: 10px;
  }
  .ticket-meta {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; letter-spacing: 0.08em; color: var(--ink-soft);
  }
  .ticket-meta .id { color: var(--brass); font-weight: 600; }
  .ticket-status {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 999px;
  }
  .ticket-status.pending { background: rgba(212,168,90,0.15); color: var(--brass); }
  .ticket-status.closed  { background: rgba(94,199,144,0.15); color: #1f6843; }

  .ticket-body {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px; line-height: 1.55;
    color: var(--ink);
    margin-bottom: 14px;
    white-space: pre-wrap;
    word-wrap: break-word;
  }
  .ticket-attachments {
    display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;
  }
  .ticket-attachments img {
    max-width: 120px; max-height: 120px;
    border-radius: 6px; border: 1px solid var(--line);
    cursor: pointer;
  }
  .ticket-actions {
    display: flex; gap: 8px; flex-wrap: wrap; padding-top: 12px;
    border-top: 1px solid var(--line);
  }
  .btn {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid var(--line);
    border-radius: 6px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 600;
    letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--ink-soft);
    cursor: pointer;
    text-decoration: none;
    transition: color 0.15s, border-color 0.15s;
  }
  .btn:hover { color: var(--teal); border-color: var(--teal); }
  .btn-primary { background: var(--ink); color: #fff; border-color: var(--ink); }
  .btn-primary:hover { background: var(--teal); border-color: var(--teal); color: #fff; }
  .ticket-closed-note {
    margin-top: 8px;
    padding: 10px 12px;
    background: rgba(94,199,144,0.06);
    border-left: 3px solid rgba(94,199,144,0.40);
    font-size: 14px; color: var(--ink-soft);
    font-style: normal;
  }

  .empty { text-align: center; padding: 60px 20px; color: var(--ink-soft); font-size: 18px; }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">inbox</span>
</header>

<main>
  <h1>Inbox.</h1>
  <p class="lede">Bug reports and feedback submitted by users. Read, then close.</p>

  @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif

  <div class="filter-bar">
    <a href="{{ route('admin.inbox', ['filter' => 'open']) }}"
       class="filter-pill @if($filter === 'open') active @endif">Open<span class="count">({{ $counts['open'] }})</span></a>
    <a href="{{ route('admin.inbox', ['filter' => 'closed']) }}"
       class="filter-pill @if($filter === 'closed') active @endif">Closed<span class="count">({{ $counts['closed'] }})</span></a>
    <a href="{{ route('admin.inbox', ['filter' => 'all']) }}"
       class="filter-pill @if($filter === 'all') active @endif">All<span class="count">({{ $counts['all'] }})</span></a>
  </div>

  @forelse ($tickets as $ticket)
    <article class="ticket">
      <div class="ticket-head">
        <div>
          <span class="ticket-meta">
            <span class="id">#{{ $ticket->id }}</span>
            · {{ $ticket->created_at->format('M j, g:i A') }}
            · {{ $ticket->user?->name ?? 'guest' }}
            @if ($ticket->tag)· tag: {{ $ticket->tag }}@endif
          </span>
        </div>
        <span class="ticket-status {{ $ticket->status }}">{{ $ticket->status }}</span>
      </div>
      <div class="ticket-body">{{ $ticket->message }}</div>

      @if (!empty($ticket->attachments))
        @php $atts = is_array($ticket->attachments) ? $ticket->attachments : json_decode($ticket->attachments, true) ?? []; @endphp
        @if (count($atts) > 0)
          <div class="ticket-attachments">
            @foreach ($atts as $att)
              @php $url = is_array($att) ? ($att['url'] ?? null) : (str_starts_with((string)$att, '/') ? $att : null); @endphp
              @if ($url)<a href="{{ $url }}" target="_blank" rel="noopener"><img src="{{ $url }}" alt="attachment"></a>@endif
            @endforeach
          </div>
        @endif
      @endif

      @if ($ticket->status === 'closed' && $ticket->closed_note)
        <div class="ticket-closed-note">
          <strong>Closed note:</strong> {{ $ticket->closed_note }}
          @if ($ticket->closed_at)· {{ $ticket->closed_at->format('M j, g:i A') }}@endif
        </div>
      @endif

      @if ($ticket->status !== 'closed')
        <div class="ticket-actions">
          <form method="POST" action="{{ route('admin.inbox.close', $ticket) }}" style="display:flex; gap:8px; flex-wrap:wrap; flex:1;"
                data-confirm="Close ticket #{{ $ticket->id }}?">
            @csrf
            @method('PATCH')
            <input name="note" type="text" placeholder="Optional close note…"
                   style="flex:1; min-width:200px; padding:8px 12px; border:1px solid var(--line); border-radius:6px; font:inherit; font-size:14px; color:var(--ink); background:#fff;">
            <button type="submit" class="btn btn-primary">Close ticket</button>
          </form>
        </div>
      @endif
    </article>
  @empty
    <div class="empty">No tickets in this view. 🙌</div>
  @endforelse
</main>
@include('partials._confirm')

</body>
</html>
