<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Peace · Polls · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  main { max-width: 980px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(28px, 4vw, 38px); font-weight: 500; line-height: 1.05; margin-bottom: 8px; }
  .lede { font-size: 15px; color: var(--ink-soft); max-width: 600px; margin-bottom: 24px; }
  .actions { margin-bottom: 32px; }
  .btn { display: inline-block; padding: 9px 18px; background: var(--teal); color: #fff; text-decoration: none; border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; border: 0; cursor: pointer; }
  .btn:hover { background: var(--teal-dark); }
  .status { padding: 14px 18px; background: rgba(3,97,122,0.08); border-left: 4px solid var(--teal); border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; }
  .poll-row { display: grid; grid-template-columns: 1fr auto auto auto; gap: 18px; align-items: center; padding: 18px 0; border-bottom: 1px solid var(--line); }
  .poll-q { font-family: 'Cormorant Garamond', serif; font-size: 19px; font-weight: 500; }
  .poll-q a { color: var(--ink); text-decoration: none; }
  .poll-q a:hover { color: var(--teal); }
  .poll-meta { font-size: 12px; color: var(--ink-soft); margin-top: 4px; }
  .badge { display: inline-block; padding: 3px 7px; border-radius: 3px; font-size: 9px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; }
  .badge.live { background: rgba(45,134,89,0.12); color: #2d8659; }
  .badge.off { background: rgba(26,35,50,0.08); color: var(--ink-soft); }
  .counts { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-soft); }
  .edit-btn { padding: 7px 14px; background: var(--teal); color: #fff; text-decoration: none; border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; }
</style>
</head>
<body>
@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.peace.index') }}">← Peace admin</a>
  <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;color:var(--ink-soft);opacity:0.65;">peace polls</span>
</header>

<main>
  <h1>Polls.</h1>
  <p class="lede">Multiple-choice questions that gently relate a reader's feeling to a scripture moment. Topic-bound polls show on matching sermon pages; unbound polls fall back as defaults.</p>

  @if (session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif

  <div class="actions">
    <a href="{{ route('admin.peace.polls.create') }}" class="btn">+ New poll</a>
  </div>

  @forelse($polls as $poll)
    <div class="poll-row">
      <div>
        <div class="poll-q"><a href="{{ route('admin.peace.polls.edit', $poll) }}">{{ $poll->question }}</a></div>
        <div class="poll-meta">
          @if($poll->topic) topic: {{ $poll->topic->name }} · @endif
          @if($poll->sermon) sermon: {{ $poll->sermon->title }} · @endif
          {{ $poll->options->count() }} options
        </div>
      </div>
      <span class="counts">{{ $poll->responses_count }} responses</span>
      @if($poll->is_active)
        <span class="badge live">Live</span>
      @else
        <span class="badge off">Off</span>
      @endif
      <a href="{{ route('admin.peace.polls.edit', $poll) }}" class="edit-btn">Edit</a>
    </div>
  @empty
    <p style="padding:60px 0;text-align:center;color:var(--ink-soft);">No polls yet — click "New poll" to create one.</p>
  @endforelse
</main>
</body>
</html>
