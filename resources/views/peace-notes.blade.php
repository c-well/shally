<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    @include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@include('partials.seo-head', [
  'title'       => 'Peace Notes — Sermon archive · Shalom SDA Church',
  'description' => 'Sermons preached at Shalom SDA Church, Bronx — listen to the audio archive any time, free.',
  'path'        => '/peace-notes',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 760px; margin: 0 auto; padding: clamp(36px, 7vh, 64px) clamp(20px, 5vw, 32px) 80px; }
  .head { text-align: center; margin-bottom: 8px; }
  .head h1 { font-size: clamp(28px, 4vw, 38px); line-height: 1.1; margin: 4px 0 0; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px, 6vw, 56px); font-weight: 500; line-height: 1.05; letter-spacing: -0.025em; color: var(--ink); max-width: 620px; margin: 0 auto; }
  .archive-line { margin-top: 14px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.28em; text-transform: uppercase; color: var(--brass); }

  /* Editable intro markdown body (still managed via /admin/pages) */
  .prose { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.7; color: var(--ink); margin: 56px auto 0; max-width: 720px; }
  .prose p { margin-bottom: 1.2em; }
  .prose h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(28px, 4vw, 38px); font-weight: 500; letter-spacing: -0.02em; color: var(--ink); margin: 1.8em 0 0.5em; text-align: center; }
  .prose a { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }

  /* Sermon list */
  .sermons { margin: 4px auto 0; max-width: 760px; }
  /* Keep the lede prose tight against the sermon list so messages appear right after */
  main > article.prose { margin-bottom: 8px; }
  main > article.prose p:last-child { margin-bottom: 0; }
  .sermons-eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.28em; text-transform: uppercase; color: var(--brass); margin-bottom: 22px; text-align: center; }
  .sermon-card {
    display: grid; grid-template-columns: 120px 1fr auto; gap: 22px;
    align-items: center; padding: 18px 0;
    border-bottom: 1px solid var(--line);
    text-decoration: none; color: inherit;
    transition: background 0.12s;
  }
  .sermon-card:hover { background: color-mix(in srgb, var(--teal) 4%, transparent); }
  .sermon-card:last-child { border-bottom: 0; }

  /* Cover thumbnail with subtle Ken Burns animation. Each card uses an offset so they
     don't all sync. The animation runs slowly (24s) and only plays when the card is
     in the viewport (we use animation-play-state via :hover or simpler: always run, low cost). */
  .sermon-cover {
    position: relative;
    width: 120px; height: 120px;
    overflow: hidden; border-radius: 8px;
    background: linear-gradient(135deg, color-mix(in srgb, var(--teal) 8%, transparent), color-mix(in srgb, var(--brass) 10%, transparent));
    flex-shrink: 0;
  }
  .sermon-cover img {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: cover;
    animation: ken-burns 26s ease-in-out infinite;
    transform-origin: center;
  }
  /* Stagger by giving each Nth card a different animation-delay */
  .sermon-card:nth-child(2n) .sermon-cover img   { animation-delay: -6s; }
  .sermon-card:nth-child(3n) .sermon-cover img   { animation-delay: -12s; transform-origin: top right; }
  .sermon-card:nth-child(4n) .sermon-cover img   { animation-delay: -18s; transform-origin: bottom left; }
  .sermon-cover.empty {
    display: flex; align-items: center; justify-content: center;
    color: var(--ink-soft); opacity: 0.4;
    font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
  }
  /* Date badge layered on top of the cover */
  .sermon-cover .date-overlay {
    position: absolute; left: 8px; bottom: 8px;
    background: rgba(254,252,239,0.92); backdrop-filter: blur(2px);
    border-radius: 4px; padding: 4px 8px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px; letter-spacing: 0.06em;
    color: var(--ink); line-height: 1.2;
    text-align: center;
  }
  .sermon-cover .date-overlay .month { text-transform: uppercase; color: var(--teal); }
  .sermon-cover .date-overlay .day { display: block; font-size: 20px; font-family: 'Cormorant Garamond', serif; }

  .sermon-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(22px, 3vw, 28px); font-weight: 500; letter-spacing: -0.015em; line-height: 1.2; color: var(--ink); }
  .sermon-header-text { display: block; margin-bottom: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); }
  .sermon-meta { margin-top: 6px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); }
  .sermon-meta .scripture { color: var(--teal); margin-right: 12px; }
  .sermon-arrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); white-space: nowrap; }

  @keyframes ken-burns {
    0%   { transform: scale(1.0)  translate(0%, 0%); }
    25%  { transform: scale(1.06) translate(-1.5%, -1%); }
    50%  { transform: scale(1.08) translate(0.5%, -1.5%); }
    75%  { transform: scale(1.05) translate(1.5%, 0.5%); }
    100% { transform: scale(1.0)  translate(0%, 0%); }
  }
  @media (prefers-reduced-motion: reduce) {
    .sermon-cover img { animation: none; }
  }

  .empty {
    margin-top: 56px; padding: 48px;
    text-align: center; color: var(--ink-soft);
    font-family: 'Cormorant Garamond', serif; font-style: italic;
    font-size: 19px; background: color-mix(in srgb, var(--teal) 4%, transparent); border-radius: 8px;
  }

  .ctas { margin-top: 64px; display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
  .btn-primary, .btn-ghost { display: inline-flex; align-items: center; justify-content: center; padding: 15px 30px; border-radius: 5px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; text-decoration: none; cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s; }
  .btn-primary { background: var(--teal); color: #fff; border: 1px solid var(--teal); }
  .btn-primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .btn-ghost { background: transparent; color: var(--ink-soft); border: 1px solid var(--line); }
  .btn-ghost:hover { color: var(--teal); border-color: var(--teal); }

  footer { padding: 22px clamp(20px, 5vw, 40px) 40px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }

  @media (max-width: 600px) {
    .sermon-card { grid-template-columns: 70px 1fr; gap: 14px; }
    .sermon-arrow { display: none; }
    .sermon-date .day { font-size: 22px; }
  }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="head">
    @if ($page?->eyebrow)
      <div class="eyebrow">{{ $page->eyebrow }}</div>
    @else
      <div class="eyebrow">Peace Notes</div>
    @endif
    <h1>{{ $page?->title ?? 'Sermon archive.' }}</h1>
    <div class="archive-line">{{ count($sermons) }} {{ count($sermons) === 1 ? 'sermon' : 'sermons' }} · listen anytime</div>
  </div>

  @php
    // Split the admin-edited body so the sermon list injects right after the
    // first paragraph (the lede). Anything after the first <p>...</p> shows
    // BELOW the sermons. Keeps the lede flush against the cards as Karlon asked.
    $bodyHtml = $page?->body_html ?? '';
    $bodyTop = '';
    $bodyRest = '';
    if ($bodyHtml) {
      // Find the end of the FIRST </p> tag (case-insensitive) and split there
      $endP = stripos($bodyHtml, '</p>');
      if ($endP !== false) {
        $cut = $endP + 4; // length of "</p>"
        $bodyTop = substr($bodyHtml, 0, $cut);
        $bodyRest = trim(substr($bodyHtml, $cut));
      } else {
        $bodyTop = $bodyHtml;
      }
    }
  @endphp

  @if ($bodyTop)
    <article class="prose prose-lede">{!! $bodyTop !!}</article>
  @endif

  <section class="sermons">
    @forelse ($sermons as $s)
      <a href="{{ route('peace-notes.show', $s->slug) }}" class="sermon-card">
        <div class="sermon-cover @if(!$s->coverUrl()) empty @endif">
          @if ($s->coverUrl())
            <img src="{{ $s->coverUrl() }}" alt="{{ $s->title }}" loading="lazy">
          @else
            <span>{{ $s->preached_on->format('M Y') }}</span>
          @endif
          <div class="date-overlay">
            <span class="month">{{ $s->preached_on->format('M') }}</span>
            <span class="day">{{ $s->preached_on->format('j') }}</span>
          </div>
        </div>
        <div>
          @if ($s->header_text)
            <span class="sermon-header-text">{{ $s->header_text }}</span>
          @endif
          <div class="sermon-title">{{ $s->title }}</div>
          @if ($s->scripture_ref || $s->speaker)
            <div class="sermon-meta">
              @if ($s->scripture_ref)
                <span class="scripture">{{ $s->scripture_ref }}</span>
              @endif
              @if ($s->speaker)
                <span>{{ $s->speaker }}</span>
              @endif
            </div>
          @endif
        </div>
        <div class="sermon-arrow">▶ Listen</div>
      </a>
    @empty
      <div class="empty">No sermons posted yet — Andre will add them as we go.</div>
    @endforelse
  </section>

  @if (!empty($bodyRest))
    <article class="prose prose-rest">{!! $bodyRest !!}</article>
  @endif

  <div class="ctas">
    <a href="https://www.youtube.com/c/ShalomSDAChurchBX/videos" target="_blank" rel="noopener" class="btn-ghost">Watch live on YouTube ↗</a>
  </div>
</main>

@include("partials.footer")

@include('partials.search-float')
</body>
</html>
