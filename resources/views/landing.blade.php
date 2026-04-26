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
  'title'       => 'Shalom SDA Church · The Church of Peace · Bronx, NY',
  'description' => 'Shalom Seventh-day Adventist Church in the Bronx — Sabbath worship at 11 AM, weekly bulletin, Sabbath School lesson, sermon archive, and prayer meetings on Zoom.',
  'path'        => '/welcome',
])
@include('partials.church-schema')
<meta name="description" content="Shalom Seventh-day Adventist Church · The Church of Peace · 3323 White Plains Rd, Bronx, NY. Sabbath School, worship service, prayer meetings, podcast, and the weekly bulletin.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  @font-face { font-family: 'Xtreem'; src: url('/fonts/XtreemMedium.ttf') format('truetype'); font-display: swap; }
  :root {
    --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455;
    --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c;
    --line:rgba(26,35,50,0.10);
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  a { color: var(--teal); text-decoration: none; }

  /* Font sizer hooks (Granny accessibility) */
  html.font-large body { font-size: 18px; }
  html.font-large h1 { font-size: clamp(70px, 11vw, 130px) !important; }
  html.font-large h2 { font-size: clamp(34px, 4.4vw, 46px) !important; }
  html.font-large .lede, html.font-large p { font-size: 18px; }
  html.font-xlarge body { font-size: 21px; }
  html.font-xlarge h1 { font-size: clamp(86px, 13vw, 156px) !important; }
  html.font-xlarge h2 { font-size: clamp(40px, 5vw, 54px) !important; }
  html.font-xlarge .lede, html.font-xlarge p { font-size: 20px; }

  /* ── Top nav strip ─────────────────────────────────────── */
  .navbar {
    padding: 22px clamp(20px, 5vw, 48px);
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid var(--line);
    background: color-mix(in srgb, var(--parchment) 96%, transparent);
    backdrop-filter: blur(6px);
    position: sticky; top: 0; z-index: 50;
  }
  /* Brand wordmark — same Xtreem treatment as the bulletin (app.thechurchofpeace.org) */
  .brand {
    display: inline-block;
    font-family: 'Xtreem', 'Cormorant Garamond', serif;
    font-style: normal; font-weight: 500;
    font-size: 88px;
    letter-spacing: -0.02em;
    line-height: 0.95;
    color: var(--teal);
    text-decoration: none;
    text-transform: lowercase;
  }
  .brand .sub {
    display: block; margin-top: 6px;
    font-family: 'Instrument Sans', sans-serif; font-style: normal;
    font-size: 9px; font-weight: 600; letter-spacing: 0.28em;
    text-transform: uppercase; color: var(--ink-soft);
  }
  .nav-links {
    display: flex; gap: 28px; align-items: center;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
  }
  .nav-links a {
    color: var(--ink-soft); padding: 8px 0;
    border-bottom: 2px solid transparent;
    transition: color 0.15s, border-color 0.15s;
  }
  .nav-links a:hover { color: var(--ink); border-bottom-color: var(--teal); }
  .nav-links a:focus-visible {
    color: var(--ink);
    border-bottom-color: var(--teal);
    outline: 2px solid var(--teal);
    outline-offset: 4px;
    border-radius: 2px;
  }
  .nav-links .has-menu:focus-visible {
    outline: 2px solid var(--teal);
    outline-offset: 4px;
    border-radius: 4px;
  }
  /* WCAG 2.2 AA — 2.4.11 Focus Not Obscured: ensure focused elements aren't
     hidden under the sticky navbar when scrolling lands on them. */
  :target, :focus-visible { scroll-margin-top: 100px; }
  .nav-links .has-menu { position: relative; }
  .nav-links .has-menu > a::after { content: ' ▾'; opacity: 0.5; font-size: 9px; }
  .nav-links .submenu {
    position: absolute; top: 100%; right: 0; margin-top: 8px;
    background: #fff; border: 1px solid var(--line); border-radius: 6px;
    padding: 8px; min-width: 220px;
    display: none; flex-direction: column;
    box-shadow: 0 18px 36px -16px rgba(26,35,50,0.25);
  }
  .nav-links .has-menu:hover .submenu,
  .nav-links .has-menu:focus-within .submenu,
  .nav-links .submenu.open { display: flex; }
  .nav-links .submenu a { padding: 10px 14px; border-bottom: 0; border-radius: 4px; }
  .nav-links .submenu a:hover { background: rgba(3,97,122,0.08); color: var(--teal); }

  /* Submenu sections — when About Us has multiple groupings */
  .submenu.submenu-wide { min-width: 240px; padding: 14px 8px; }
  .submenu.submenu-wide a { padding: 9px 14px; font-size: 14px; line-height: 1.3; }
  .submenu-eyebrow {
    display: block;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 9px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--ink-soft);
    opacity: 0.55;
    padding: 12px 14px 4px;
  }
  .submenu-eyebrow:first-child { padding-top: 4px; }

  /* Nav tools — search + text-size icons in the header chrome */
  .nav-tool {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px;
    padding: 0;
    margin-left: 4px;
    background: transparent;
    border: 1px solid var(--line);
    border-radius: 999px;
    color: var(--ink-soft);
    cursor: pointer;
    transition: color 0.15s, border-color 0.15s, background 0.15s;
  }
  .nav-tool:hover { color: var(--teal); border-color: var(--teal); }
  .nav-tool:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .nav-tool-aa {
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px; font-weight: 500;
    letter-spacing: -0.5px; line-height: 1;
  }
  html.font-large .nav-tool-aa { font-size: 16px; }
  html.font-xlarge .nav-tool-aa { font-size: 18px; }
  html.dark .nav-tool { color: #9e9e9e; border-color: rgba(255,255,255,0.18); }
  html.dark .nav-tool:hover { color: #4fb8d4; border-color: #4fb8d4; }

  
  /* Tools cluster — search + text size + hamburger. Always visible. */
  .nav-tools {
    display: flex; align-items: center; gap: 6px;
  }
  /* On desktop the cluster sits inside the navbar's flex row, after .nav-links.
     The .nav-mobile-toggle is hidden (existing rule already does this).
     On mobile the cluster collapses to just the icons + hamburger; the
     hamburger toggles .nav-links visibility independently. */
  @media (max-width: 720px) {
    .nav-tools .nav-tool { width: 36px; height: 36px; }
  }

  
  /* Accordion mobile drawer — convert About Us hover-dropdown into a
     collapsible accordion when nav-links is opened on mobile. */
  @media (max-width: 720px) {
    /* When drawer is open, About Us submenu expands inline (not floating) */
    .nav-links.open .has-menu {
      display: block;
      width: 100%;
    }
    .nav-links.open .has-menu > a {
      display: block;
      padding: 14px 18px;
      font-size: 14px;
      letter-spacing: 0.18em;
    }
    /* Default: submenu collapsed on mobile */
    .nav-links.open .submenu {
      display: none;
      position: static !important;
      box-shadow: none;
      border: 0;
      padding: 0 0 8px 18px;
      background: transparent !important;
    }
    /* When About Us is "active" (we'll toggle via JS), expand */
    .nav-links.open .has-menu.is-expanded .submenu {
      display: block;
    }
    .nav-links.open .has-menu.is-expanded > a::after {
      content: ' ▴';
    }
    .nav-links.open .submenu a {
      padding: 8px 14px !important;
      font-size: 13px !important;
      letter-spacing: 0.10em;
    }
    .nav-links.open .submenu-eyebrow {
      padding: 10px 14px 4px;
      font-size: 9px;
    }
  }

    .nav-mobile-toggle {
    display: none; background: none; border: 0; cursor: pointer;
    padding: 8px; color: var(--ink);
  }
  @media (max-width: 820px) {
    .nav-links { display: none; }
    .nav-mobile-toggle { display: inline-flex; }
    .nav-links.open {
      display: flex; flex-direction: column; align-items: stretch;
      position: absolute; top: 100%; left: 0; right: 0;
      background: #fff; border-bottom: 1px solid var(--line);
      padding: 14px 22px 22px; gap: 14px;
    }
    .nav-links.open .submenu { position: static; box-shadow: none; border: 0; padding-left: 18px; padding-right: 0; display: flex; }
    .nav-links.open .has-menu > a::after { display: none; }
  }

  /* ── HERO ─────────────────────────────────────────────── */
  .hero {
    text-align: center;
    padding: clamp(72px, 14vh, 140px) clamp(20px, 5vw, 32px) clamp(56px, 10vh, 96px);
    max-width: 1100px; margin: 0 auto;
  }
  .hero-eyebrow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: 0.36em;
    text-transform: uppercase; color: var(--teal);
    margin-bottom: 22px;
  }
  .hero h1 {
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: clamp(56px, 9vw, 108px);
    font-weight: 500; line-height: 1.02;
    letter-spacing: -0.035em;
    color: var(--ink);
    max-width: 900px; margin: 0 auto;
  }
  .hero h1 em {
    font-style: normal; color: var(--teal);
    font-weight: 500;
  }
  .hero .lede {
    margin: 32px auto 36px;
    max-width: 580px;
    font-size: clamp(15px, 1.6vw, 17px);
    line-height: 1.65; color: var(--ink-soft);
  }
  .hero-cta {
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--teal); color: #fff;
    border: 1px solid var(--teal);
    padding: 16px 36px; border-radius: 5px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    transition: background 0.15s;
  }
  .hero-cta:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .hero-cta-secondary {
    display: inline-flex; align-items: center; justify-content: center;
    margin-left: 12px;
    color: var(--ink-soft); border: 1px solid var(--line);
    padding: 16px 28px; border-radius: 5px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    transition: color 0.15s, border-color 0.15s;
  }
  .hero-cta-secondary:hover { color: var(--teal); border-color: var(--teal); }

  /* ── Section header ───────────────────────────────────── */
  section.block { padding: clamp(56px, 10vh, 96px) clamp(20px, 5vw, 32px); max-width: 1080px; margin: 0 auto; }
  .section-eyebrow {
    text-align: center;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: 0.32em;
    text-transform: uppercase; color: var(--teal);
    margin-bottom: 16px;
  }
  h2 {
    text-align: center;
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(32px, 4vw, 42px); font-weight: 500;
    letter-spacing: -0.025em; line-height: 1.1;
    color: var(--ink);
    max-width: 720px; margin: 0 auto;
  }
  .section-sub { text-align: center; margin-top: 18px; max-width: 540px; margin-left: auto; margin-right: auto; font-size: 15px; line-height: 1.6; color: var(--ink-soft); }

  /* ── Schedule cards ───────────────────────────────────── */
  .schedule {
    margin-top: 56px;
    display: grid; gap: 14px;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
  .svc-card {
    background: #fff; border: 1px solid var(--line); border-radius: 8px;
    padding: 24px 22px;
    text-align: center;
    transition: border-color 0.15s, transform 0.12s, box-shadow 0.15s;
  }
  .svc-card:hover { border-color: var(--teal); transform: translateY(-2px); box-shadow: 0 12px 28px -16px rgba(3,97,122,0.25); }
  .svc-card .svc-name {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px; font-weight: 500;
    color: var(--ink); margin-bottom: 6px;
    letter-spacing: -0.012em;
  }
  .svc-card .svc-when {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px; letter-spacing: 0.04em;
    color: var(--teal); margin-bottom: 6px;
  }
  .svc-card .svc-where {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--ink-soft); font-weight: 600;
  }
  .svc-card .svc-where.zoom { color: var(--brass); }

  /* ── Cards row (Bulletin / Lesson / Podcast) ──────────── */
  .feature-row {
    margin-top: 56px;
    display: grid; gap: 18px;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }
  .feature-card {
    background: #fff; border: 1px solid var(--line); border-radius: 10px;
    padding: 28px 26px;
    display: flex; flex-direction: column;
    transition: border-color 0.15s, transform 0.12s, box-shadow 0.15s;
  }
  .feature-card:hover { border-color: var(--teal); transform: translateY(-2px); box-shadow: 0 12px 28px -16px rgba(3,97,122,0.25); }
  .feature-card .fc-eyebrow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; font-weight: 700; letter-spacing: 0.28em;
    text-transform: uppercase; color: var(--teal); margin-bottom: 12px;
  }
  .feature-card .fc-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 30px; font-weight: 500; color: var(--ink);
    letter-spacing: -0.02em; line-height: 1.1; margin-bottom: 10px;
  }
  .feature-card .fc-sub {
    color: var(--ink-soft); font-size: 14px; line-height: 1.55;
    margin-bottom: 18px; flex: 1;
  }
  .feature-card .fc-arrow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: 0.22em;
    text-transform: uppercase; color: var(--teal);
  }

  /* ── YouTube section ──────────────────────────────────── */
  .youtube-wrap {
    margin: 40px auto 0;
    max-width: 880px; aspect-ratio: 16 / 9;
    border: 1px solid var(--line); border-radius: 10px; overflow: hidden;
    box-shadow: 0 18px 40px -20px rgba(26,35,50,0.25);
    background: #000;
  }
  .youtube-wrap iframe { width: 100%; height: 100%; border: 0; display: block; }

  /* ── Connect block ────────────────────────────────────── */
  .connect-row {
    margin-top: 40px;
    display: grid; gap: 14px;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    max-width: 740px; margin-left: auto; margin-right: auto;
  }
  .connect-tile {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 22px 18px;
    background: #fff; border: 1px solid var(--line); border-radius: 8px;
    text-align: center;
    transition: border-color 0.15s, transform 0.12s;
    color: var(--ink);
  }
  .connect-tile:hover { border-color: var(--teal); transform: translateY(-2px); }
  .connect-tile svg { width: 28px; height: 28px; color: var(--teal); }
  .connect-tile .ct-name {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: 0.22em;
    text-transform: uppercase;
  }
  .connect-tile .ct-sub {
    font-size: 12px; color: var(--ink-soft);
  }

  /* ── Donate band ──────────────────────────────────────── */
  .donate-band {
    margin: 64px auto 0; padding: 56px 32px;
    max-width: 840px;
    background: var(--teal); color: #fff;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 24px 48px -22px rgba(3,97,122,0.4);
  }
  .donate-band h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(28px, 4vw, 38px); font-weight: 500;
    letter-spacing: -0.02em; line-height: 1.1;
    margin-bottom: 14px;
  }
  .donate-band p { color: rgba(255,255,255,0.85); margin-bottom: 26px; max-width: 480px; margin-left: auto; margin-right: auto; }
  .donate-band a {
    display: inline-block;
    background: #fff; color: var(--teal);
    padding: 16px 36px; border-radius: 5px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 12px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    transition: background 0.15s;
  }
  .donate-band a:hover { background: var(--parchment); }

  /* ── Footer ───────────────────────────────────────────── */
  footer.site {
    margin-top: 64px;
    padding: 48px clamp(20px, 5vw, 40px) 64px;
    text-align: center;
    border-top: 1px solid var(--line);
    color: var(--ink-soft);
  }
  footer.site .footer-brand {
    font-family: 'Xtreem', 'Cormorant Garamond', serif;
    font-size: 72px; font-weight: 500; font-style: normal; text-transform: lowercase; line-height: 0.95;
    color: var(--teal); margin-bottom: 8px; letter-spacing: -0.02em;
  }
  footer.site address {
    font-style: normal;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; letter-spacing: 0.18em;
    text-transform: uppercase; line-height: 1.85;
    color: var(--ink-soft); margin-bottom: 24px;
  }
  footer.site address a { color: var(--ink-soft); border-bottom: 1px solid transparent; transition: color 0.15s, border-color 0.15s; }
  footer.site address a:hover { color: var(--teal); border-bottom-color: var(--teal); }
  footer.site .socials { display: flex; gap: 16px; justify-content: center; margin-bottom: 24px; }
  footer.site .socials a {
    width: 40px; height: 40px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid var(--line); border-radius: 50%;
    color: var(--ink-soft);
    transition: color 0.15s, border-color 0.15s;
  }
  footer.site .socials a:hover { color: var(--teal); border-color: var(--teal); }
  footer.site .socials svg { width: 16px; height: 16px; }
  footer.site .copy {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase;
    color: var(--ink-soft); opacity: 0.7;
  }

  /* ── Font sizer (bottom-right floating) ───────────────── */
  .font-toggle {
    position: fixed; bottom: 22px; right: 22px;
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 14px;
    background: #fff; border: 1px solid var(--line); border-radius: 999px;
    box-shadow: 0 6px 18px -8px rgba(26,35,50,0.25);
    cursor: pointer; user-select: none;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: var(--ink-soft); z-index: 100;
    transition: border-color 0.15s, color 0.15s;
  }
  .font-toggle:hover { border-color: var(--teal); color: var(--teal); }
  .font-toggle .aa { font-family: 'Cormorant Garamond', serif; font-size: 18px; line-height: 1; letter-spacing: -0.5px; }

  @media (max-width: 600px) {
    .hero { padding-top: 56px; padding-bottom: 40px; }
    .hero h1 { font-size: 48px; }
    .hero-cta-secondary { margin-left: 0; margin-top: 12px; }
    .donate-band { padding: 40px 24px; margin-top: 48px; }
  }

  /* ── Hero photo slider ─────────────────────────────────── */
  /* Fixed-dimension slider: the container height is locked so the page layout doesn't
     jump as slides change. Images fit INSIDE that area with `object-fit: contain` — they
     shrink to fit without cropping. The letterbox space is parchment so it reads as a
     framed photograph rather than empty void. Previously used `cover` which guaranteed
     fills but cropped portrait shots (heads cut off). */
  .slider-section { padding: 0; max-width: 100%; margin: 0; background: var(--parchment); }
  /* Editable intro block above the slider — admin-managed at /admin/pages → slider-intro.
     Stays out of the slider's fixed dimensions; sits above on its own row. */
  .slider-intro { max-width: 760px; margin: 0 auto; padding: 56px 24px 32px; text-align: center; }
  .slider-intro-eyebrow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--ink-soft); margin-bottom: 14px;
  }
  .slider-intro-title {
    font-family: 'Cormorant Garamond', serif; font-weight: 500;
    font-size: clamp(28px, 4vw, 42px); color: var(--ink);
    line-height: 1.15; margin-bottom: 14px; letter-spacing: -0.015em;
  }
  .slider-intro-body { color: var(--ink-soft); font-size: 16px; line-height: 1.65; }
  .slider-intro-body p { margin-bottom: 12px; }
  .slider-intro-body p:last-child { margin-bottom: 0; }
  @media (max-width: 600px) {
    .slider-intro { padding: 40px 20px 24px; }
  }
  .slider {
    position: relative; width: 100%; height: clamp(360px, 60vh, 620px);
    overflow: hidden; background: var(--parchment);
  }
  .slider-track {
    display: flex; height: 100%; width: 100%;
    transition: transform 1.1s cubic-bezier(0.7, 0, 0.3, 1);
    /* (When the JS snaps back to a cloned slide for seamless looping, it temporarily
       overrides this with `transition: none` to make the snap invisible.) */
  }
  .slide { position: relative; flex: 0 0 100%; height: 100%; background: var(--parchment); }
  .slide img { width: 100%; height: 100%; object-fit: contain; object-position: center center; display: block; }
  .slide-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0) 30%, rgba(0,0,0,0.65) 100%);
    display: flex; flex-direction: column; justify-content: flex-end;
    padding: clamp(28px, 5vw, 56px);
    color: #fff;
  }
  .slide-caption {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(28px, 4.5vw, 52px); font-weight: 500;
    font-style: italic; letter-spacing: -0.02em;
    line-height: 1.05; max-width: 720px;
  }
  .slide-subcaption {
    margin-top: 12px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.28em; text-transform: uppercase;
    opacity: 0.85;
  }
  .slider-dots {
    position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%);
    display: flex; gap: 8px; z-index: 5;
  }
  .slider-dots button {
    width: 8px; height: 8px; border-radius: 50%;
    background: rgba(255,255,255,0.4); border: 0; padding: 0; cursor: pointer;
    transition: background 0.2s, transform 0.2s;
  }
  .slider-dots button.active { background: #fff; transform: scale(1.3); }
  .slider-arrow {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(0,0,0,0.4); border: 0; cursor: pointer;
    color: #fff; font-size: 22px; line-height: 0;
    display: flex; align-items: center; justify-content: center;
    z-index: 5; transition: background 0.2s;
  }
  .slider-arrow:hover { background: rgba(0,0,0,0.65); }
  .slider-arrow.prev { left: 18px; }
  .slider-arrow.next { right: 18px; }
  @media (max-width: 600px) { .slider-arrow { display: none; } }

</style>
</head>
<body>
@include("partials.admin-badge")

{{-- ── Top nav ── --}}
<header class="navbar">
  <a href="/" class="brand">Shalom</a>
  <nav class="nav-links" id="nav-links">
    {{-- About Us — the umbrella for everything that's not a primary CTA.
         Hover/tap reveals the full submenu of pages + watch options. --}}
    <span class="has-menu" id="about-menu-trigger" role="button"
          tabindex="0"
          aria-haspopup="menu"
          aria-expanded="false"
          aria-controls="about-menu-submenu">
      <a href="{{ route('about') }}" tabindex="-1">About Us ▾</a>
      <span class="submenu submenu-wide" id="about-menu-submenu" role="menu"
            aria-labelledby="about-menu-trigger">
        <span class="submenu-eyebrow" role="presentation">Get to know us</span>
        <a href="{{ route('about') }}" role="menuitem">Our story</a>
        <a href="{{ route('beliefs') }}" role="menuitem">Beliefs</a>
        <a href="{{ route('contact.show') }}" role="menuitem">Contact</a>

        <span class="submenu-eyebrow" role="presentation">Spiritual life</span>
        <a href="{{ route('lesson.show') }}" role="menuitem">Sabbath School</a>
        <a href="{{ route('peace-notes') }}" role="menuitem">Peace Notes</a>

        <span class="submenu-eyebrow" role="presentation">Connect</span>
        <a href="https://us02web.zoom.us/j/83002967327?pwd=dk13eXhDeUU1QjJ0TklqMjVtUWk0UT09" target="_blank" rel="noopener" role="menuitem">Join us on Zoom</a>
        <a href="#watch" role="menuitem">Watch live</a>
        <a href="https://www.facebook.com/thechurchofpeace" target="_blank" rel="noopener" role="menuitem">Facebook</a>
      </span>
    </span>

    {{-- Primary CTAs — the three things visitors actually do --}}
    <a href="{{ route('visit') }}">Visit</a>
    <a href="{{ url('/welcome') }}">Bulletin</a>
    <a href="https://adventistgiving.org/#/org/AN48SH/envelope/start" target="_blank" rel="noopener" style="color: var(--teal); border-color: var(--teal);">Donate</a>

  </nav>

  {{-- Tools cluster — always visible on every viewport. On mobile, lives
       next to the hamburger so search and text-size stay reachable even
       when the nav drawer is closed. --}}
  <div class="nav-tools">
    <button type="button" id="nav-search-btn" class="nav-tool" aria-label="Search Scripture or Hymnal" title="Search">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </button>
    <button type="button" id="nav-font-btn" class="nav-tool" aria-label="Text size" title="Text size">
      <span class="nav-tool-aa">Aa</span>
    </button>
    <button class="nav-mobile-toggle" id="nav-toggle" aria-label="Open navigation menu" aria-expanded="false" aria-controls="nav-links">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
    </button>
  </div>
</header>

{{-- ── Hero ── --}}
<section class="hero">
  <div class="hero-eyebrow">{{ $heroPage?->eyebrow ?? "Shalom Seventh-day Adventist · Bronx, NY" }}</div>
  <h1>Welcome to <em>The Church of Peace</em>.</h1>
  <div class="lede">@if($heroPage && $heroPage->body_html){!! $heroPage->body_html !!}@else<p><em>&quot;Where two or three gather in my name, there am I with them.&quot;</em> (Matt. 18:20) Every Sabbath, we gather at 3323 White Plains Road to do just that.</p>@endif</div>
  <a href="{{ url('/welcome') }}" class="hero-cta">This Sabbath's bulletin →</a>
  <a href="{{ route('lesson.show') }}" class="hero-cta-secondary">Sabbath School lesson</a>
</section>

{{-- ── Schedule ── --}}
<section class="block" id="schedule">
  <div class="section-eyebrow">Each week</div>
  <h2>Our service schedule.</h2>
  <p class="section-sub">In person at 3323 White Plains Rd · all weekday gatherings on Zoom.</p>

  <div class="schedule">
    <div class="svc-card">
      <div class="svc-name">Sabbath School</div>
      <div class="svc-when">SAT · 9:30 AM</div>
      <div class="svc-where">In person</div>
    </div>
    <div class="svc-card">
      <div class="svc-name">Worship Service</div>
      <div class="svc-when">SAT · 11:00 AM</div>
      <div class="svc-where">In person</div>
    </div>
    <a href="https://us02web.zoom.us/j/83002967327?pwd=dk13eXhDeUU1QjJ0TklqMjVtUWk0UT09" target="_blank" rel="noopener" class="svc-card" title="Open in Zoom">
      <div class="svc-name">Hour of Prayer</div>
      <div class="svc-when">MON–FRI · 5:00 AM</div>
      <div class="svc-where zoom">Zoom</div>
    </a>
    <a href="https://us02web.zoom.us/j/83002967327?pwd=dk13eXhDeUU1QjJ0TklqMjVtUWk0UT09" target="_blank" rel="noopener" class="svc-card" title="Open in Zoom">
      <div class="svc-name">Prayer Meeting</div>
      <div class="svc-when">WED · 7:00 PM</div>
      <div class="svc-where zoom">Zoom</div>
    </a>
  </div>
</section>

{{-- ── Hero photo slider (admin-managed via /admin/slides) ── --}}
@if (isset($slides) && count($slides) > 0)
<section class="slider-section">
  @if (!empty($sliderIntro?->title) || !empty($sliderIntro?->body_html))
    <div class="slider-intro">
      @if (!empty($sliderIntro->eyebrow))
        <div class="slider-intro-eyebrow">{{ $sliderIntro->eyebrow }}</div>
      @endif
      @if (!empty($sliderIntro->title))
        <h2 class="slider-intro-title">{{ $sliderIntro->title }}</h2>
      @endif
      @if (!empty($sliderIntro->body_html))
        <div class="slider-intro-body">{!! $sliderIntro->body_html !!}</div>
      @endif
    </div>
  @endif
  <div class="slider" id="hero-slider" data-count="{{ count($slides) }}">
    <div class="slider-track">
      @foreach ($slides as $i => $s)
        <div class="slide">
          @if ($s->link_url)
            <a href="{{ $s->link_url }}" target="_blank" rel="noopener" style="position:absolute;inset:0;z-index:3;"></a>
          @endif
          <img src="{{ $s->imageUrl() }}" alt="{{ $s->caption ?? '' }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
          @if ($s->caption || $s->subcaption)
            <div class="slide-overlay">
              @if ($s->caption)<div class="slide-caption">{{ $s->caption }}</div>@endif
              @if ($s->subcaption)<div class="slide-subcaption">{{ $s->subcaption }}</div>@endif
            </div>
          @endif
        </div>
      @endforeach
    </div>
    @if (count($slides) > 1)
      <button type="button" class="slider-arrow prev" aria-label="Previous slide">‹</button>
      <button type="button" class="slider-arrow next" aria-label="Next slide">›</button>
      <div class="slider-dots">
        @foreach ($slides as $i => $s)
          <button type="button" data-i="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
      </div>
    @endif
  </div>
</section>
@endif

{{-- ── This week (cards) ── --}}
<section class="block">
  <div class="section-eyebrow">This week</div>
  <h2>What's happening at Shalom.</h2>

  <div class="feature-row">
    <a href="{{ url('/welcome') }}" class="feature-card">
      <span class="fc-eyebrow">Sabbath</span>
      <span class="fc-title">This week's bulletin.</span>
      <span class="fc-sub">Order of service, who's leading what, and the announcements — refreshed every Sabbath.</span>
      <span class="fc-arrow">Read →</span>
    </a>
    <a href="{{ route('lesson.show') }}" class="feature-card">
      <span class="fc-eyebrow">Adult Bible Study Guide</span>
      <span class="fc-title">Sabbath School lesson.</span>
      <span class="fc-sub">This quarter's full guide. Download the PDF or follow the daily readings.</span>
      <span class="fc-arrow">Open →</span>
    </a>
    <a href="{{ route('peace-notes') }}" class="feature-card">
      <span class="fc-eyebrow">Podcast</span>
      <span class="fc-title">Peace Notes.</span>
      <span class="fc-sub">Conversations from the pulpit and beyond — listen any time.</span>
      <span class="fc-arrow">Listen →</span>
    </a>
  </div>
</section>

{{-- ── Watch latest service (YouTube embed) ── --}}
<section class="block" id="watch">
  <div class="section-eyebrow">Live & on demand</div>
  <h2>Watch our latest service.</h2>
  <p class="section-sub">Streaming each Sabbath on YouTube. Catch up on a missed week below.</p>

  <div class="youtube-wrap">
    <iframe src="https://www.youtube.com/embed/videoseries?list=UUe0XMb5MrFYVqV4OyUNkBmw"
            title="Shalom SDA latest service"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            loading="lazy"></iframe>
  </div>
</section>

{{-- ── Connect ── --}}
<section class="block" id="connect">
  <div class="section-eyebrow">Stay connected</div>
  <h2>Join us between Sabbaths.</h2>

  <div class="connect-row">
    <a href="https://us02web.zoom.us/j/83002967327?pwd=dk13eXhDeUU1QjJ0TklqMjVtUWk0UT09" target="_blank" rel="noopener" class="connect-tile">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="14" height="12" rx="2"/><path d="m22 8-6 4 6 4z"/></svg>
      <span class="ct-name">Zoom</span>
      <span class="ct-sub">Join a meeting</span>
    </a>
    <a href="https://www.youtube.com/c/ShalomSDAChurchBX/videos" target="_blank" rel="noopener" class="connect-tile">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.7-.5-5.4c-.3-.9-1-1.6-1.9-1.9C18.9 4.2 12 4.2 12 4.2s-6.9 0-8.6.5c-.9.3-1.6 1-1.9 1.9C1 8.3 1 12 1 12s0 3.7.5 5.4c.3.9 1 1.6 1.9 1.9 1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5c.9-.3 1.6-1 1.9-1.9.5-1.7.5-5.4.5-5.4zM9.7 15.4V8.6l5.8 3.4-5.8 3.4z"/></svg>
      <span class="ct-name">YouTube</span>
      <span class="ct-sub">Watch services</span>
    </a>
    <a href="https://www.facebook.com/thechurchofpeace" target="_blank" rel="noopener" class="connect-tile">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.5-4.5-10-10-10S2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.5 2.9h-2.3v7c4.7-.8 8.4-4.9 8.4-9.9z"/></svg>
      <span class="ct-name">Facebook</span>
      <span class="ct-sub">Follow us</span>
    </a>
  </div>
</section>

{{-- ── Donate ── --}}
<section class="block" style="padding-top:0;">
  <div class="donate-band">
    <h3>Support the ministry.</h3>
    <p>Tithes, offerings, and gifts run securely through Adventist Giving.</p>
    <a href="https://adventistgiving.org/#/org/AN48SH/envelope/start" target="_blank" rel="noopener">Give now ↗</a>
  </div>
</section>

{{-- ── Footer ── --}}
@include("partials.footer")

{{-- Font sizer --}}
<button type="button" class="font-toggle" id="font-toggle" aria-label="Change text size">
  <span class="aa" id="font-toggle-icon">A</span>
  <span id="font-toggle-label">Text size</span>
</button>

<script>
(function () {
  // Mobile nav — toggle drawer + sync aria-expanded for screen readers
  const tog = document.getElementById('nav-toggle');
  const nav = document.getElementById('nav-links');
  if (tog && nav) {
    tog.addEventListener('click', () => {
      const isOpen = nav.classList.toggle('open');
      tog.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    // Esc closes the drawer + returns focus to toggle (WCAG 2.2 AA dismissible)
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && nav.classList.contains('open')) {
        nav.classList.remove('open');
        tog.setAttribute('aria-expanded', 'false');
        tog.focus();
      }
    });
  }

  // Font sizer (persists across pages via localStorage)
  const html = document.documentElement;
  const ftBtn = document.getElementById('font-toggle');
  const ftIcon = document.getElementById('font-toggle-icon');
  const ftLabel = document.getElementById('font-toggle-label');
  const states = ['normal', 'large', 'xlarge'];
  const labels = { normal: 'Text size', large: 'Larger', xlarge: 'Largest' };

  function apply(state) {
    html.classList.remove('font-normal', 'font-large', 'font-xlarge');
    html.classList.add('font-' + state);
    ftLabel.textContent = labels[state];
    try { localStorage.setItem('cop_font_size', state); } catch (e) {}
  }
  let cur = 'normal';
  try { cur = localStorage.getItem('cop_font_size') || 'normal'; } catch (e) {}
  apply(cur);
  ftBtn.addEventListener('click', () => {
    cur = states[(states.indexOf(cur) + 1) % states.length];
    apply(cur);
  });
})();
</script>

<script>
/* ───────────────────────────────────────────────────────────────────────────
   Hero slider — seamless infinite loop with auto-advance.

   The trick: clone the first slide and append it at the end, clone the last
   slide and prepend it at the start. The track now has count+2 children.
   When we slide PAST the real last slide we land on the cloned-first (which
   looks identical to slide 0). After that transition completes, we silently
   snap (transition: none) back to the real slide 0. Same logic in reverse.

   Result: clicking next on the last slide brings the first slide in from the
   right — never a long rewind back to position 0.

   Drop-in: requires `.slider`, `.slider-track`, `.slide`, `.slider-dots button`,
   `.slider-arrow.prev`, `.slider-arrow.next`, and `data-count` on the wrapper.
   ─────────────────────────────────────────────────────────────────────────── */
(function () {
  var slider = document.getElementById("hero-slider");
  if (!slider) return;
  var track = slider.querySelector(".slider-track");
  var dots  = slider.querySelectorAll(".slider-dots button");
  var prev  = slider.querySelector(".slider-arrow.prev");
  var next  = slider.querySelector(".slider-arrow.next");
  var count = parseInt(slider.dataset.count, 10);
  if (!count || count < 2) return; // single slide → no loop logic needed

  // Clone first → append at end. Clone last → prepend at start.
  var slides = track.children;
  var firstClone = slides[0].cloneNode(true);
  var lastClone  = slides[count - 1].cloneNode(true);
  firstClone.setAttribute("aria-hidden", "true");
  lastClone.setAttribute("aria-hidden", "true");
  track.appendChild(firstClone);
  track.insertBefore(lastClone, slides[0]);
  // Track structure now: [lastClone, slide0, slide1, ..., slide(N-1), firstClone]
  // Real slide 0 is at track index 1; real slide N-1 is at track index N.

  var i = 1;            // start showing real slide 0 (track index 1)
  var animating = false;

  function moveTo(idx, animate) {
    track.style.transition = animate
      ? "transform 1.1s cubic-bezier(0.7, 0, 0.3, 1)"
      : "none";
    track.style.transform = "translateX(-" + (idx * 100) + "%)";
  }
  function syncDots() {
    // Real slide index from track position (handles both clones gracefully)
    var real = ((i - 1) % count + count) % count;
    dots.forEach(function (d, idx) { d.classList.toggle("active", idx === real); });
  }

  // Initial: snap to real slide 0 without animation
  moveTo(i, false);
  syncDots();

  function step(direction) {
    if (animating) return;
    animating = true;
    i += direction;
    moveTo(i, true);
    syncDots();
  }
  function jumpTo(realIdx) {
    if (animating) return;
    animating = true;
    i = realIdx + 1; // map real index → track index (offset by lastClone)
    moveTo(i, true);
    syncDots();
  }

  // After each transition, if we're sitting on a clone, snap silently to its real twin.
  track.addEventListener("transitionend", function () {
    animating = false;
    if (i === count + 1) { i = 1;       moveTo(i, false); }   // came off firstClone → snap to real 0
    if (i === 0)         { i = count;   moveTo(i, false); }   // came off lastClone  → snap to real N-1
  });

  // Wire controls
  dots.forEach(function (d) { d.addEventListener("click", function () { jumpTo(parseInt(d.dataset.i, 10)); resetAuto(); }); });
  if (prev) prev.addEventListener("click", function () { step(-1); resetAuto(); });
  if (next) next.addEventListener("click", function () { step( 1); resetAuto(); });

  // Auto-advance — pauses when tab is hidden so we don't burn cycles in background
  var timer;
  function resetAuto() {
    clearInterval(timer);
    timer = setInterval(function () { if (!document.hidden) step(1); }, 6500);
  }
  resetAuto();

  // Keyboard support — left/right arrows when slider is focused
  slider.tabIndex = 0;
  slider.addEventListener("keydown", function (e) {
    if (e.key === "ArrowLeft")  { step(-1); resetAuto(); e.preventDefault(); }
    if (e.key === "ArrowRight") { step( 1); resetAuto(); e.preventDefault(); }
  });

  // Respect prefers-reduced-motion — hold first slide, no auto-advance
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    clearInterval(timer);
  }
})();
</script>

@include('partials.search-float')

<script>
// Nav tools — search + text size in the landing header chrome.
// Replaces the floating search-float and per-page font-toggle for the
// landing page. The text size cycles normal/large/xlarge and persists
// across pages via localStorage (key 'cop_font_size'), so reading pages
// honor the choice.
(function () {
  // Search — opens /welcome?open-search=1 just like the floating button did
  var searchBtn = document.getElementById('nav-search-btn');
  if (searchBtn) {
    searchBtn.addEventListener('click', function () {
      try { sessionStorage.setItem('cop_open_search', '1'); } catch (e) {}
      window.location.href = '/welcome?open-search=1';
    });
  }

  // Text size — same cycling pattern as the lesson page font-toggle
  var fontBtn = document.getElementById('nav-font-btn');
  if (fontBtn) {
    var html = document.documentElement;
    var states = ['normal', 'large', 'xlarge'];
    function applyFont(state) {
      html.classList.remove('font-normal', 'font-large', 'font-xlarge');
      html.classList.add('font-' + state);
      try { localStorage.setItem('cop_font_size', state); } catch (e) {}
    }
    var current = 'normal';
    try { current = localStorage.getItem('cop_font_size') || 'normal'; } catch (e) {}
    applyFont(current);
    fontBtn.addEventListener('click', function () {
      var idx = states.indexOf(current);
      current = states[(idx + 1) % states.length];
      applyFont(current);
    });
  }
})();
</script>

<script>
// WCAG 2.2 AA — About Us submenu accessibility wiring.
// Manages aria-expanded as the menu opens/closes (hover, focus, click, keyboard).
// Esc dismisses and returns focus to the trigger (1.4.13 Dismissible).
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var trigger = document.getElementById('about-menu-trigger');
    var submenu = document.getElementById('about-menu-submenu');
    if (!trigger || !submenu) return;

    function setExpanded(state) {
      trigger.setAttribute('aria-expanded', state ? 'true' : 'false');
    }

    function open()  { submenu.classList.add('open');    setExpanded(true); }
    function close() { submenu.classList.remove('open'); setExpanded(false); }

    // Hover/focus already drive visibility via CSS. Hook same lifecycle to aria.
    trigger.addEventListener('mouseenter', function () { setExpanded(true); });
    trigger.addEventListener('mouseleave', function () {
      // Don't close immediately; if focus is inside submenu, keep it open
      setTimeout(function () {
        if (!submenu.contains(document.activeElement) && !trigger.matches(':hover')) {
          setExpanded(false);
        }
      }, 100);
    });
    trigger.addEventListener('focusin', function () { setExpanded(true); });
    submenu.addEventListener('focusout', function (e) {
      // Only close if focus is leaving the menu region entirely
      setTimeout(function () {
        if (!submenu.contains(document.activeElement) && document.activeElement !== trigger) {
          setExpanded(false);
        }
      }, 0);
    });

    // Click on the trigger SPAN (not the inner <a>) toggles the menu.
    // Click on the inner <a href=/about> still navigates to /about as expected.
    trigger.addEventListener('click', function (e) {
      // Don't intercept clicks on the inner link OR on submenu items
      if (e.target.tagName === 'A') return;
      e.preventDefault();
      var isOpen = trigger.getAttribute('aria-expanded') === 'true';
      setExpanded(!isOpen);
    });

    // Esc to dismiss + return focus to trigger (1.4.13 Dismissible)
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && trigger.getAttribute('aria-expanded') === 'true') {
        setExpanded(false);
        trigger.focus();
      }
    });

    // Keyboard: Enter/Space on the trigger SPAN opens submenu
    trigger.addEventListener('keydown', function (e) {
      if ((e.key === 'Enter' || e.key === ' ') && e.target === trigger) {
        e.preventDefault();
        var isOpen = trigger.getAttribute('aria-expanded') === 'true';
        setExpanded(!isOpen);
        if (!isOpen) {
          // Move focus to first menu item
          var first = submenu.querySelector('[role="menuitem"]');
          if (first) setTimeout(function () { first.focus(); }, 50);
        }
      }
    });
  });
})();
</script>

<script>
// Mobile accordion — tap About Us inside open drawer to expand its submenu
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var navLinks = document.getElementById('nav-links');
    var trigger  = document.getElementById('about-menu-trigger');
    if (!navLinks || !trigger) return;
    var triggerLink = trigger.querySelector('a');
    if (!triggerLink) return;
    triggerLink.addEventListener('click', function (e) {
      // Only intercept when drawer is open AND we are on mobile width
      if (!navLinks.classList.contains('open')) return;
      if (window.innerWidth > 720) return;
      e.preventDefault();
      trigger.classList.toggle('is-expanded');
    });
  });
})();
</script>
</body>
</html>
