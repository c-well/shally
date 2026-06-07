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
  'title'       => 'Plan your visit — Shalom SDA Church · Bronx, NY',
  'description' => 'First-time visitor guide to Shalom SDA Church at 3323 White Plains Road, Bronx. Service times, directions, parking, what to wear, and what to expect.',
  'path'        => '/visit',
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

  main { max-width: 880px; margin: 0 auto; padding: clamp(48px, 9vh, 96px) clamp(20px, 5vw, 32px) 80px; }
  .head { text-align: center; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px, 6vw, 56px); font-weight: 500; font-style: italic; line-height: 1.1; letter-spacing: -0.025em; }
  .lede { margin-top: 24px; font-size: 17px; line-height: 1.65; color: var(--ink-soft); max-width: 580px; margin-left: auto; margin-right: auto; }

  /* Address card with embedded map */
  .address-card {
    margin-top: 56px;
    display: grid; grid-template-columns: 1fr 1.2fr; gap: 0;
    background: #fff; border: 1px solid var(--line); border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 18px 44px -22px rgba(0,0,0,0.18);
  }
  .address-info { padding: 32px; }
  .address-info h2 { font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 500; letter-spacing: -0.02em; line-height: 1.1; color: var(--ink); margin-bottom: 18px; }
  .address-info .addr { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.55; color: var(--ink); margin-bottom: 22px; }
  .address-info .row { margin-bottom: 14px; font-size: 14px; line-height: 1.55; color: var(--ink-soft); }
  .address-info .row strong { color: var(--ink); font-weight: 600; }
  .address-info .open-maps { display: inline-block; margin-top: 8px; padding: 11px 22px; background: var(--teal); color: #fff; border-radius: 4px; text-decoration: none; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; transition: background 0.15s; }
  .address-info .open-maps:hover { background: var(--teal-dark); }
  .address-map { min-height: 320px; background: color-mix(in srgb, var(--teal) 4%, transparent); }
  .address-map iframe { width: 100%; height: 100%; border: 0; display: block; }
  @media (max-width: 700px) {
    .address-card { grid-template-columns: 1fr; }
    .address-map { order: -1; min-height: 240px; }
  }

  /* Editable prose body */
  .prose { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.7; color: var(--ink); margin: 64px auto 0; max-width: 720px; }
  .prose p { margin-bottom: 1.2em; }
  .prose h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(28px, 4vw, 38px); font-weight: 500; letter-spacing: -0.02em; color: var(--ink); margin: 1.8em 0 0.5em; text-align: center; }
  .prose h3 { font-family: 'Cormorant Garamond', serif; font-size: clamp(22px, 3vw, 28px); font-weight: 500; color: var(--ink); margin: 1.4em 0 0.4em; }
  .prose blockquote { margin: 1.8em 0; padding: 18px 26px; background: color-mix(in srgb, var(--teal) 6%, transparent); border-left: 4px solid var(--teal); border-radius: 0 8px 8px 0; font-style: italic; color: var(--ink-soft); }
  .prose ul, .prose ol { margin: 1em 0 1em 1.4em; }
  .prose li { margin-bottom: 0.4em; }
  .prose a { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }
  .prose img { max-width: 100%; height: auto; border-radius: 8px; margin: 2em 0; box-shadow: 0 18px 44px -22px rgba(0,0,0,0.3); display: block; }

  /* FAQ */
  .faq { margin-top: 72px; max-width: 720px; margin-left: auto; margin-right: auto; }
  .faq h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(32px, 5vw, 44px); font-weight: 500; letter-spacing: -0.02em; color: var(--ink); margin-bottom: 28px; text-align: center; }
  .q { padding: 22px 0; border-bottom: 1px solid var(--line); }
  .q:last-child { border-bottom: 0; }
  .q summary { cursor: pointer; font-family: 'Cormorant Garamond', serif; font-size: 21px; font-weight: 500; color: var(--ink); list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 14px; }
  .q summary::-webkit-details-marker { display: none; }
  .q summary::after { content: '+'; font-family: 'JetBrains Mono', monospace; font-size: 22px; color: var(--teal); transition: transform 0.2s; }
  .q[open] summary::after { content: '−'; }
  .q p { margin-top: 14px; font-family: 'Cormorant Garamond', serif; font-size: 17px; line-height: 1.65; color: var(--ink-soft); }

  .ctas { margin-top: 72px; display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
  .btn-primary, .btn-ghost { display: inline-flex; align-items: center; justify-content: center; padding: 15px 32px; border-radius: 5px; font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; text-decoration: none; cursor: pointer; transition: background 0.15s, color 0.15s, border-color 0.15s; }
  .btn-primary { background: var(--teal); color: #fff; border: 1px solid var(--teal); }
  .btn-primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .btn-ghost { background: transparent; color: var(--ink-soft); border: 1px solid var(--line); }
  .btn-ghost:hover { color: var(--teal); border-color: var(--teal); }

  footer { padding: 22px clamp(20px, 5vw, 40px) 40px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="head">
    @if ($page?->eyebrow)
      <div class="eyebrow">{{ $page->eyebrow }}</div>
    @endif
    <h1>{{ $page?->title ?? 'Come visit.' }}</h1>
  </div>

  <div class="address-card">
    <div class="address-info">
      <h2>Shalom SDA Church</h2>
      <div class="addr">
        3323 White Plains Road<br>
        Bronx, NY 10467
      </div>
      <div class="row"><strong>Sabbath worship:</strong> Saturdays · 11:00 AM</div>
      <div class="row"><strong>Sabbath School:</strong> Saturdays · 9:30 AM</div>
      <div class="row"><strong>Subway:</strong> 2 / 5 to Burke Av · walk toward Gunhill Rd · 4 min</div>
      <div class="row"><strong>Bus:</strong> Bx39 / Bx41 stops nearby</div>
      <div class="row"><strong>Parking:</strong> street on White Plains & adjacent side streets</div>
      <a href="https://maps.google.com/?q=3323+White+Plains+Road+Bronx+NY+10467" target="_blank" rel="noopener" class="open-maps">Open in Maps ↗</a>
    </div>
    <div class="address-map">
      <iframe
        src="https://www.google.com/maps?q=3323+White+Plains+Road+Bronx+NY+10467&output=embed"
        title="Map to Shalom SDA Church"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>

  @if ($page?->body_html)
    <article class="prose">
      {!! $page->body_html !!}
    </article>
  @endif

  <section class="faq">
    <h2>Common questions.</h2>

    <details class="q">
      <summary>I've never been to an Adventist service. Will I be lost?</summary>
      <p>Not at all. The order of worship is in our weekly bulletin (linked below) — hymn, prayer, scripture, sermon, closing hymn. You can follow along even on your first visit.</p>
    </details>

    <details class="q">
      <summary>Do I have to be a member to attend?</summary>
      <p>No. Every service, every potluck, every Bible class is open to anyone. We'd rather have you visit a hundred times before you ever consider membership.</p>
    </details>

    <details class="q">
      <summary>What if I can't make it Saturday morning?</summary>
      <p>The service streams live each Sabbath on YouTube — and we have weekday Zoom gatherings (Hour of Prayer 5 AM weekdays, Bible Class 4 PM Sabbath, Prayer Meeting 7 PM Wednesday). Find them on the home page under <a href="/#connect" style="color:var(--teal);text-decoration:underline;text-underline-offset:2px;">Connect</a>.</p>
    </details>

    <details class="q">
      <summary>Where do I park?</summary>
      <p>Street parking on White Plains Road and side streets. Sabbath morning is usually quiet enough to find a spot within a block.</p>
    </details>

    <details class="q">
      <summary>How do I get involved?</summary>
      <p>Start by visiting. After a few Sabbaths, talk to Andre or one of the elders — they'll plug you in based on what you're drawn to (music, teaching, hospitality, prayer ministry).</p>
    </details>
  </section>

  <div class="ctas">
    <a href="{{ url('/') }}" class="btn-primary">See this Sabbath's bulletin →</a>
    <a href="{{ route('contact.show') }}" class="btn-ghost">Ask a question</a>
  </div>
</main>

@include("partials.footer")

@include('partials.search-float')
@include('partials._event-tracker')
</body>
</html>
