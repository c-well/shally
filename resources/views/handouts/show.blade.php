<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

{{-- Three layers keep a handout out of search: this tag, the X-Robots-Tag
     header set in HandoutController, and robots.txt disallowing /h/. A gift
     registry is a family's private business — belt, braces, and a third belt. --}}
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
<meta name="referrer" content="no-referrer">

<title>{{ $h->title }}</title>

{{-- DOCTRINE: the preview looks like the page. These links get pasted into
     family group chats, and a generic card — favicon on a coloured square —
     tells the recipient nothing and reads as spam. The image is a real render
     of this card: same parchment, same theme colour, same mark and title. See
     App\Services\HandoutOgImage.

     The description stays deliberately short. The picture carries the identity;
     the body copy is the family's own words and does not need repeating into
     every chat preview that touches the link. --}}
<meta property="og:title" content="{{ $h->title }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="The Church of Peace">
<meta property="og:url" content="{{ $h->url() }}">
<meta property="og:image" content="{{ route('handout.og', $h->token) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@if ($h->eyebrow)
  <meta property="og:description" content="{{ $h->eyebrow }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $h->title }}">
<meta name="twitter:image" content="{{ route('handout.og', $h->token) }}">

<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600&family=instrument-sans:400,500,600,700&family=jetbrains-mono:400,500&display=swap" rel="stylesheet">
@include('partials.theme-vars')

<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  html, body {
    background: var(--parchment);
    color: var(--ink);
    font-family: 'Cormorant Garamond', Georgia, serif;
    min-height: 100dvh;
    width: 100%;
    max-width: 100%;
    -webkit-font-smoothing: antialiased;
    overflow-x: clip;
    overscroll-behavior-x: none;
  }

  /* The page is one card on a table. Everything is centred and given room —
     a handout that feels cramped reads as a form, and this is a gift. */
  .table {
    min-height: 100dvh;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding-block: clamp(32px, 8vh, 80px);
    padding-inline: calc(20px + env(safe-area-inset-left)) calc(20px + env(safe-area-inset-right));
  }

  /* A hand-torn warmth behind the card: two very soft washes of the theme
     colour, so the background is never a flat slab of cream. */
  .table::before {
    content: ''; position: fixed; inset: 0; z-index: 0; pointer-events: none;
    background:
      radial-gradient(58vw 44vw at 12% -8%,  color-mix(in srgb, var(--teal) 11%, transparent), transparent 70%),
      radial-gradient(52vw 40vw at 92% 108%, color-mix(in srgb, var(--brass) 13%, transparent), transparent 70%);
  }

  .card {
    position: relative; z-index: 1;
    width: 100%; max-width: 560px; min-width: 0;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 18px;
    box-shadow: 0 1px 2px rgba(26,35,50,.04), 0 18px 50px -22px rgba(26,35,50,.28);
    overflow: hidden;
    animation: rise .62s cubic-bezier(.2,.75,.25,1) both;
  }
  @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

  /* A ribbon along the top edge — the seal on the envelope. */
  .ribbon { height: 5px; background: linear-gradient(90deg, var(--teal), var(--brass)); }

  .photo { display: block; width: 100%; height: auto; max-height: 340px; object-fit: cover; }

  .inner { padding: clamp(30px, 6vw, 46px); text-align: center; }

  .eyebrow {
    font-family: 'Instrument Sans', system-ui, sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: .3em; text-transform: uppercase;
    color: var(--brass);
    margin-bottom: 16px;
  }

  h1 {
    font-size: clamp(32px, 7vw, 46px);
    font-weight: 500; line-height: 1.08; letter-spacing: -.02em;
    color: var(--ink);
    text-wrap: balance;
  }

  /* A short brass rule under the title instead of a border — quieter, and it
     reads as printed rather than boxed. */
  .rule { width: 46px; height: 2px; background: var(--brass); opacity: .5; margin: 22px auto; border-radius: 2px; }

  .prose { font-size: 18.5px; line-height: 1.72; color: var(--ink-soft); text-align: left; }
  .prose p { margin-bottom: 1.05em; }
  .prose p:last-child { margin-bottom: 0; }
  .prose strong { color: var(--ink); font-weight: 600; }
  .prose em { font-style: italic; }
  .prose a { color: var(--teal); text-decoration: underline; text-underline-offset: 3px; }
  .prose ul, .prose ol { margin: 0 0 1.05em 1.15em; }
  .prose li { margin-bottom: .35em; }
  .prose h2, .prose h3 { font-size: 22px; font-weight: 600; color: var(--ink); margin: 1.3em 0 .4em; }

  /* When + where. Only the event and guest shapes fill these in. */
  .facts { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 26px; }
  .fact {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: 'Instrument Sans', system-ui, sans-serif;
    font-size: 13.5px; font-weight: 500; color: var(--ink-soft);
    background: var(--teal-light);
    border-radius: 999px; padding: 9px 15px;
    max-width: 100%; min-width: 0;
  }
  .fact svg { flex: none; color: var(--teal); }
  .fact span { min-width: 0; overflow-wrap: anywhere; }

  .cta { margin-top: 32px; }
  .cta a {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    font-family: 'Instrument Sans', system-ui, sans-serif;
    font-size: 15px; font-weight: 600; letter-spacing: .01em;
    color: #fff; background: var(--teal);
    text-decoration: none;
    padding: 16px 30px; border-radius: 999px;
    max-width: 100%;
    transition: transform .22s cubic-bezier(.2,.75,.25,1), background .22s ease;
  }
  .cta a:hover { background: var(--teal-dark); transform: translateY(-2px); }
  .cta a:active { transform: translateY(0); }
  .cta a svg { flex: none; }

  .signoff {
    margin-top: 26px; text-align: center; position: relative; z-index: 1;
    font-family: 'Instrument Sans', system-ui, sans-serif;
    font-size: 11px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase;
  }
  .signoff a { color: var(--ink-faint); text-decoration: none; }
  .signoff a:hover { color: var(--teal); }

  @media (prefers-reduced-motion: reduce) {
    .card { animation: none; }
    .cta a { transition: none; }
    .cta a:hover { transform: none; }
  }
</style>
</head>

<body data-theme="{{ $h->theme }}">
<div class="table">
  <article class="card">
    <div class="ribbon"></div>

    @if ($h->image_path)
      <img class="photo" src="{{ \Illuminate\Support\Facades\Storage::url($h->image_path) }}" alt="">
    @endif

    <div class="inner">
      @if ($h->eyebrow)
        <p class="eyebrow">{{ $h->eyebrow }}</p>
      @endif

      <h1>{{ $h->title }}</h1>

      @if ($body || $h->happens_at || $h->location)
        <div class="rule"></div>
      @endif

      @if ($body)
        <div class="prose">{!! $body !!}</div>
      @endif

      @if ($h->happens_at || $h->location)
        <div class="facts">
          @if ($h->happens_at)
            <span class="fact">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/>
              </svg>
              <span>{{ $h->happens_at->timezone(\App\Models\Handout::TZ)->format('l, F j · g:i a') }}</span>
            </span>
          @endif
          @if ($h->location)
            <span class="fact">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
              </svg>
              <span>{{ $h->location }}</span>
            </span>
          @endif
        </div>
      @endif

      @if ($h->link_url)
        <p class="cta">
          {{-- noopener/noreferrer both matter: the destination is usually an
               outside shop or form, and it has no business knowing where the
               visitor came from. --}}
          <a href="{{ $h->link_url }}" target="_blank" rel="noopener noreferrer nofollow">
            {{ $h->link_label ?: 'Open' }}
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h13M13 6l6 6-6 6"/>
            </svg>
          </a>
        </p>
      @endif

      {{-- No expiry notice on the card, deliberately. "This page comes down
           December 20" reads as a cold administrative stamp on what is a
           family's announcement, and the visitor gains nothing from it — they
           either use the link now or they do not. The lifespan is still shown
           plainly to the clerk in /admin/handouts, which is where it matters. --}}
    </div>
  </article>

  <p class="signoff"><a href="/">The Church of Peace</a></p>
</div>
</body>
</html>
