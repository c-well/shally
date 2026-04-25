<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    @include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.seo-head', [
  'title'       => 'Contact us — The Church of Peace',
  'description' => 'Get in touch with Shalom SDA Church in the Bronx. Andre and the elders read every message — questions, prayer requests, or to plan your visit.',
  'path'        => '/contact',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  @font-face { font-family: 'Xtreem'; src: url('/fonts/XtreemMedium.ttf') format('truetype'); font-display: swap; }
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 720px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px, 6vw, 56px); font-weight: 500; font-style: italic; line-height: 1.1; letter-spacing: -0.025em; }
  .lede { margin-top: 22px; font-size: 16px; line-height: 1.6; color: var(--ink-soft); max-width: 540px; }

  .grid {
    margin-top: 56px;
    display: grid; gap: 36px;
    grid-template-columns: 1fr 1.4fr;
  }
  @media (max-width: 760px) { .grid { grid-template-columns: 1fr; gap: 28px; } }

  .info { padding: 28px; background: #fff; border: 1px solid var(--line); border-radius: 8px; }
  .info h2 { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 500; margin-bottom: 18px; color: var(--ink); }
  .info-row { margin-bottom: 18px; }
  .info-row:last-child { margin-bottom: 0; }
  .info-row .lbl { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 5px; }
  .info-row .val { font-size: 15px; line-height: 1.5; color: var(--ink); }
  .info-row a { color: var(--teal); text-decoration: none; }
  .info-row a:hover { text-decoration: underline; text-underline-offset: 2px; }

  /* Editable lede body */
  .prose { font-family: 'Cormorant Garamond', serif; font-size: 18px; line-height: 1.65; color: var(--ink-soft); max-width: 540px; margin-top: 22px; }
  .prose p { margin-bottom: 0.8em; }
  .prose p:last-child { margin-bottom: 0; }
  .prose a { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }

  form.contact { display: grid; gap: 16px; }
  .field { display: flex; flex-direction: column; gap: 7px; }
  .field label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); }
  .field input, .field textarea {
    font: inherit; font-size: 15px; padding: 12px 14px;
    border: 1px solid var(--line); border-radius: 4px; background: #fff; color: var(--ink); width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .field textarea { min-height: 160px; resize: vertical; font-family: 'Poppins', sans-serif; line-height: 1.55; }
  .field input:focus, .field textarea:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(3,97,122,0.12); outline: none; }
  .field .err { color: #c0392b; font-size: 12px; margin-top: 2px; }

  .honey { position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none; }

  button.submit {
    margin-top: 8px; padding: 16px 28px;
    background: var(--teal); color: #fff; border: 1px solid var(--teal); border-radius: 5px;
    font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s; justify-self: start;
  }
  button.submit:hover { background: var(--teal-dark); }

  .flash {
    margin-bottom: 24px; padding: 18px 22px;
    background: rgba(3,97,122,0.08); border-left: 4px solid var(--teal);
    border-radius: 0 6px 6px 0;
    font-size: 15px; line-height: 1.55; color: var(--ink);
  }
  .flash strong { color: var(--teal); }

  footer { padding: 22px clamp(20px, 5vw, 40px) 40px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }
</style>
</head>
<body>
@include("partials.admin-badge")

<header class="top">
  <a href="/">← Home</a>
  <span class="meta">contact</span>
</header>

<main>
  @if ($page?->eyebrow)
    <div class="eyebrow">{{ $page->eyebrow }}</div>
  @endif
  <h1>{{ $page?->title ?? 'Get in touch.' }}</h1>
  @if ($page?->body_html)
    <div class="prose">{!! $page->body_html !!}</div>
  @endif

  @if (session('sent'))
    <div class="flash" style="margin-top:36px;">
      <strong>Thank you.</strong> Your message is on its way to the church inbox — we'll get back to you within a couple of days.
    </div>
  @endif

  <div class="grid">
    {{-- Left: church info --}}
    <aside class="info">
      <h2>Shalom SDA</h2>

      <div class="info-row">
        <div class="lbl">Address</div>
        <div class="val">3323 White Plains Road<br>Bronx, NY 10467</div>
      </div>

      <div class="info-row">
        <div class="lbl">Email</div>
        <div class="val"><a href="mailto:contact@thechurchofpeace.org">contact@thechurchofpeace.org</a></div>
      </div>

      <div class="info-row">
        <div class="lbl">Sabbath worship</div>
        <div class="val">Saturdays · 11:00 AM<br>In person · all welcome</div>
      </div>

      <div class="info-row">
        <div class="lbl">Pastor</div>
        <div class="val">All inquiries via the form or email above.</div>
      </div>
    </aside>

    {{-- Right: contact form --}}
    <form class="contact" method="POST" action="{{ route('contact.send') }}" novalidate>
      @csrf

      {{-- Honeypot — bots fill this in, humans never see it. --}}
      <div class="honey" aria-hidden="true">
        <label for="website">Website (leave blank)</label>
        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
      </div>

      <div class="field">
        <label for="name">Your name</label>
        <input type="text" id="name" name="name" required maxlength="120" value="{{ old('name') }}" autocomplete="name">
        @error('name')<span class="err">{{ $message }}</span>@enderror
      </div>

      <div class="field">
        <label for="email">Your email</label>
        <input type="email" id="email" name="email" required maxlength="200" value="{{ old('email') }}" autocomplete="email">
        @error('email')<span class="err">{{ $message }}</span>@enderror
      </div>

      <div class="field">
        <label for="message">Message</label>
        <textarea id="message" name="message" required maxlength="5000" placeholder="Hi — I had a question about…">{{ old('message') }}</textarea>
        @error('message')<span class="err">{{ $message }}</span>@enderror
      </div>

      <button type="submit" class="submit">Send →</button>
    </form>
  </div>
</main>

@include("partials.footer")

@include('partials.search-float')
</body>
</html>
