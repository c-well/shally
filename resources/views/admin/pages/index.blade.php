<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Site pages — Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 920px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(28px, 4vw, 38px); font-weight: 500; letter-spacing: -0.015em; line-height: 1.05; margin-bottom: 4px; }
  .lede { margin-top: 22px; font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 580px; }
  .row { display: grid; grid-template-columns: 100px 1fr auto auto; gap: 18px; align-items: center; padding: 18px 4px; border-bottom: 1px solid var(--line); text-decoration: none; color: var(--ink); transition: background 0.12s; }
  .row:hover { background: rgba(3,97,122,0.04); }
  .row:last-child { border-bottom: 0; }
  .row .slug { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: var(--teal); }
  .row .title { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; line-height: 1.2; }
  .row .lastedit { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-soft); opacity: 0.7; }
  .row .arrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); }
  .list { margin-top: 48px; background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 0 24px; }
  .empty { padding: 48px; text-align: center; color: var(--ink-soft); }
  @media (max-width: 700px) { .row { grid-template-columns: 1fr auto; } .row .slug, .row .lastedit { display: none; } }
</style>
@include('admin.partials._typography')
</head>
<body>

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">site pages</span>
</header>

<main>
  <h1>Site pages.</h1>
  <p class="lede">Edit any public page below. Changes go live the moment you hit save — no SSH, no code, no waiting on Karlon.</p>

  <div class="list">
    @forelse ($pages as $page)
      <a href="{{ route('admin.pages.edit', $page->slug) }}" class="row">
        <span class="slug">/{{ $page->slug }}</span>
        <div>
          <div class="title">{{ $page->title }}</div>
          @if ($page->eyebrow)
            <div class="lastedit">{{ $page->eyebrow }}</div>
          @endif
        </div>
        <div class="lastedit">
          @if ($page->updated_by_user_id)
            edited {{ $page->updated_at->diffForHumans() }}
          @else
            never edited
          @endif
        </div>
        <span class="arrow">Edit →</span>
      </a>
    @empty
      <div class="empty">No pages yet — run the migration to seed the initial set.</div>
    @endforelse
  </div>
</main>

</body>
</html>
