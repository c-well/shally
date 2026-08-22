<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta http-equiv="refresh" content="60">
<meta name="robots" content="noindex">
<title>Be right back · The Church of Peace</title>
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --line:color-mix(in srgb, var(--ink) 10%, transparent); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  /* The wordmark IS the logo — there is no image mark on this site. Declared
     here rather than inherited from partials.site-menu, because this page no
     longer includes the site chrome and a brand that silently falls back to a
     generic serif is worse than no brand at all. font-display:block holds the
     text invisible until Xtreem lands instead of flashing Cormorant first. */
  @font-face {
    font-family: 'Xtreem';
    src: url('/fonts/XtreemMedium.ttf') format('truetype');
    font-weight: 500; font-style: normal; font-display: block;
  }

  /* This page must never scroll. It says one thing; a scrollbar implies there
     is more below and sends people hunting for it. Fixed to the viewport, with
     the content scaled down rather than overflowing on short screens. */
  html, body {
    height: 100%; width: 100%;
    overflow: hidden;
    background: var(--parchment); color: var(--ink);
    font-family: 'Poppins', system-ui, sans-serif;
    -webkit-font-smoothing: antialiased;
  }

  .stage {
    height: 100dvh;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
    padding: clamp(20px, 5vh, 48px) calc(22px + env(safe-area-inset-left)) clamp(20px, 5vh, 48px) calc(22px + env(safe-area-inset-right));
    gap: clamp(14px, 2.4vh, 26px);
  }

  /* THE BRAND MARK. Identical treatment to .site-menu-brand em in
     partials/site-menu — Xtreem, capital S, teal. This is the global header
     mark used anywhere the brand appears as a mark (never inside body copy).
     Do not substitute a plain serif: Xtreem is a script face and Cormorant is
     not, so a silent fallback does not read as the logo at all. That is why
     font-display is block below — hold the text invisible until Xtreem lands
     rather than flashing the wrong mark. */
  .mark {
    font-family: 'Xtreem', 'Cormorant Garamond', serif;
    font-size: clamp(64px, 12vw, 116px);
    font-weight: 500; font-style: normal;
    /* text-transform:lowercase is NOT cosmetic here and must not be dropped.
       Xtreem's capital S is a large swooping display glyph; the mark uses the
       compact lowercase s. The source text stays "Shalom" for screen readers
       and copy-paste while the face renders the real logo. This mirrors
       .site-menu-brand em exactly - if the header ever changes, change both. */
    text-transform: lowercase;
    line-height: .95; letter-spacing: -1px;
    color: var(--teal);
  }
  .mark em { font-style: normal; }

  .who {
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: clamp(10px, 1.3vw, 11.5px);
    font-weight: 600; letter-spacing: .3em; text-transform: uppercase;
    color: var(--ink-soft); opacity: .6;
  }

  h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(24px, 4vw, 36px);
    font-weight: 400; letter-spacing: -.4px; line-height: 1.25;
    color: var(--ink);
    max-width: min(560px, 100%);
    margin-top: clamp(6px, 1.4vh, 14px);
  }

  .lede {
    font-size: clamp(14px, 1.6vw, 16px);
    line-height: 1.6; color: var(--ink-soft);
    max-width: min(500px, 100%);
  }

  .pulse {
    display: inline-flex; align-items: center; gap: 9px;
    font-family: 'JetBrains Mono', ui-monospace, monospace;
    font-size: 10.5px; font-weight: 500; letter-spacing: .2em; text-transform: uppercase;
    color: var(--teal);
    margin-top: clamp(4px, 1.2vh, 12px);
  }
  .pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--teal); animation: pulse 1.6s ease-in-out infinite; }
  @keyframes pulse { 0%, 100% { opacity: .25; transform: scale(.85); } 50% { opacity: 1; transform: scale(1); } }

  .mark, .who, h1, .lede { max-width: min(560px, 100%); overflow-wrap: break-word; }

  /* Landscape phones and short windows — shed the supporting copy before
     anything gets clipped. The brand and the one-line promise are the page. */
  @media (max-height: 460px) {
    .lede { display: none; }
    .mark { font-size: clamp(40px, 9vh, 64px); }
    h1 { font-size: clamp(18px, 4.5vh, 26px); }
  }

  @media (prefers-reduced-motion: reduce) {
    .pulse-dot { animation: none; opacity: .8; }
  }
</style>
</head>
<body>
<div class="stage">
  <div class="mark"><em>Shalom</em></div>
  <p class="who">Shalom Seventh-day Adventist Church</p>

  <h1>{{ $message ?? "We're updating something — back in a few minutes." }}</h1>

  <p class="lede">
    The bulletin and sign-in are paused while Karlon ships an improvement.
    This page will refresh on its own, or you can reload manually.
  </p>

  <div class="pulse">
    <span class="pulse-dot"></span>
    <span>Updating</span>
  </div>
</div>
</body>
</html>
