<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Saved · Finding Peace</title>
<meta name="robots" content="noindex, nofollow">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Poppins', system-ui, sans-serif; background: var(--bg); color: var(--ink); line-height: 1.65; min-height: 100dvh; }
  main { max-width: 720px; margin: 0 auto; padding: 4rem 1.5rem 8rem; }
  .breadcrumb { font-family: 'Instrument Sans', sans-serif; font-size: 0.7rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-faint); font-weight: 600; margin-bottom: 0.5rem; }
  .breadcrumb a { color: var(--brass); text-decoration: none; }
  .breadcrumb a:hover { color: var(--brass-bright); }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 5vw, 2.6rem); font-weight: 500; line-height: 1.1; margin-bottom: 0.4rem; }
  .lede { color: var(--ink-soft); font-size: 1rem; margin-bottom: 2.5rem; }

  .item { padding: 1.5rem 0; border-bottom: 1px solid var(--line); }
  .item:last-child { border-bottom: none; }
  .item-meta { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; letter-spacing: 0.1em; color: var(--ink-faint); text-transform: uppercase; margin-bottom: 0.5rem; }
  .item-meta a { color: var(--ink-soft); text-decoration: none; }
  .item-meta a:hover { color: var(--brass); }
  .question { font-family: 'Cormorant Garamond', serif; font-size: 1.25rem; font-weight: 500; line-height: 1.35; color: var(--ink); margin-bottom: 0.5rem; }
  .answer-snip { font-size: 0.96rem; color: var(--ink-soft); margin-bottom: 0.6rem; }
  .actions { display: flex; gap: 1rem; align-items: center; font-size: 0.82rem; }
  .actions a { color: var(--brass); text-decoration: none; font-weight: 500; }
  .actions a:hover { color: var(--brass-bright); }
  .remove-btn { background: transparent; border: 0; color: var(--ink-faint); font-family: inherit; font-size: 0.82rem; cursor: pointer; padding: 0; }
  .remove-btn:hover { color: #a82a1f; }

  .empty { padding: 4rem 0; text-align: center; }
  .empty h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 500; margin-bottom: 0.6rem; }
  .empty p { color: var(--ink-soft); font-size: 0.96rem; }
  .empty a { color: var(--brass); }

  .footer-bar { margin-top: 4rem; display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--line); font-size: 0.82rem; color: var(--ink-soft); }
  .footer-bar a { color: var(--brass); text-decoration: none; }
  .footer-bar a:hover { color: var(--brass-bright); }
  .logout {
    background: transparent; border: 0;
    color: var(--ink-faint); font-family: inherit; font-size: 0.82rem;
    cursor: pointer; padding: 0;
  }
  .logout:hover { color: var(--ink-soft); }
</style>
@include('partials.find-peace-vars')
</head>
<body>
<main>
  <div class="breadcrumb"><a href="{{ route('find-peace.index') }}">Finding Peace</a> · Saved</div>
  <h1>Yours.</h1>
  <p class="lede">{{ $items->count() }} {{ \Illuminate\Support\Str::plural('Q&A', $items->count()) }} you've kept. Signed in as <strong>{{ $seeker->email }}</strong>.</p>

  @if ($items->isNotEmpty())
    @foreach ($items as $qa)
      <div class="item" data-qa-id="{{ $qa->id }}">
        <div class="item-meta">
          @if($qa->sermon)
            from <a href="{{ route('find-peace.show', $qa->sermon->slug) }}">{{ $qa->sermon->title }}</a> · {{ optional($qa->sermon->sermon_date)->format('M j Y') }}
          @endif
        </div>
        <p class="question">{{ $qa->question }}</p>
        <p class="answer-snip">{{ \Illuminate\Support\Str::limit($qa->answer, 200) }}</p>
        <div class="actions">
          @if($qa->sermon)
            <a href="{{ route('find-peace.show', $qa->sermon->slug) }}?qa={{ $qa->id }}">Read the full message @include('partials._ar')</a>
          @endif
          <button type="button" class="remove-btn" data-remove-id="{{ $qa->id }}">Remove from saved</button>
        </div>
      </div>
    @endforeach
  @else
    <div class="empty">
      <h2>Nothing saved yet.</h2>
      <p>When something speaks to you on Finding Peace, tap the bookmark on it. It'll show up here.</p>
      <p style="margin-top:1.2rem;"><a href="{{ route('find-peace.index') }}">@include('partials._arl') Back to Finding Peace</a></p>
    </div>
  @endif

  <div class="footer-bar">
    <span>{{ $seeker->email }}</span>
    <span style="flex: 1; text-align: center;">
      <a href="{{ route('peace.share.show') }}" style="color: var(--brass); text-decoration: none; font-weight: 500;">Add your own question or experience @include('partials._ar')</a>
    </span>
    <form method="POST" action="{{ route('peace.auth.logout') }}" style="display:inline;">
      @csrf
      <button type="submit" class="logout">Sign out</button>
    </form>
  </div>
</main>

<script>
document.querySelectorAll('.remove-btn').forEach(btn => {
  btn.addEventListener('click', async (e) => {
    const qaId = btn.dataset.removeId;
    if (!await window.shConfirm('Remove from your saved list?')) return;
    try {
      const resp = await fetch('/peace/saved/' + qaId, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
          'Accept': 'application/json',
        },
      });
      const data = await resp.json();
      if (data.ok && !data.saved) {
        const row = btn.closest('.item');
        row.style.transition = 'opacity 0.2s';
        row.style.opacity = '0';
        setTimeout(() => row.remove(), 200);
      }
    } catch (err) { console.error(err); }
  });
});
</script>
@include('partials._confirm')
</body>
</html>
