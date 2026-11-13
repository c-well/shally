{{-- The booklet you can flip through on the site (2026-11-13). Same pages as the folded
     print (bulletins.pdf-booklet), both built by App\Support\BookletLayout.
     Two leaves turn on a centre spine:
       leaf 1: front = cover,          back = order of service (left page)
       leaf 2: front = order, cont'd,  back = announcements (back page)
     Computer: three spreads (closed cover, open inside, closed back).
     Phone: one page at a time; the book slides to the page and turns between spreads. --}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
<link rel="shortcut icon" href="/favicon.ico?v=2">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $L['title'] }} · {{ $L['date']->format('M j') }} — The Church of Peace</title>
<meta name="description" content="The program for {{ $L['title'] }}, {{ $L['date']->format('l, F j, Y') }}, at Shalom Seventh-day Adventist Church. Flip through it or print it.">
<meta property="og:title" content="{{ $L['title'] }} · {{ $L['date']->format('F j') }}">
<meta property="og:description" content="Flip through the program, or print it and fold it in half.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; -webkit-font-smoothing: antialiased; overflow-x: clip; overscroll-behavior-x: none; }

  .bk-top { max-width: 1100px; margin: 0 auto; padding: 14px calc(18px + env(safe-area-inset-right)) 0 calc(18px + env(safe-area-inset-left)); }
  .bk-top a { display: inline-flex; align-items: center; gap: 6px; color: var(--ink-soft); text-decoration: none; font-size: 15px; }
  .bk-top a:hover { color: var(--teal); }

  /* ── Sizing: one page is 5.5 × 8.5 in. --pw is a page's width on screen. ── */
  .bk-stage {
    --pw: min(calc(100vw - 36px), calc((100dvh - 210px) * 5.5 / 8.5), 460px);
    position: relative; width: 100%; height: calc(var(--pw) * 8.5 / 5.5);
    margin: 22px 0 0; overflow: clip;
    touch-action: pan-y; user-select: none; -webkit-user-select: none; cursor: pointer;
  }
  @media (min-width: 900px) {
    .bk-stage { --pw: min(calc((100vw - 120px) / 2), calc((100dvh - 230px) * 5.5 / 8.5), 520px); margin-top: 30px; overflow: visible; }
  }
  /* Perspective only while a page is turning: flat pages at rest stay crisp. */
  .bk-stage.moving { perspective: 2600px; }
  .bk-book {
    position: absolute; top: 0; left: 50%; width: calc(var(--pw) * 2); height: 100%;
    margin-left: calc(var(--pw) * -1);
    transform-style: preserve-3d;
    transform: translateX(-25%);
    transition: transform 0.85s cubic-bezier(0.32, 0.08, 0.2, 1);
  }
  .bk-leaf {
    position: absolute; top: 0; left: 50%; width: 50%; height: 100%;
    transform-style: preserve-3d; transform-origin: left center;
    transition: transform 0.85s cubic-bezier(0.32, 0.08, 0.2, 1);
  }
  .bk-leaf.turned { transform: rotateY(-180deg); }
  .bk-face {
    position: absolute; inset: 0; overflow: hidden;
    backface-visibility: hidden; -webkit-backface-visibility: hidden;
    background: #fff; color: #1a2332;
    box-shadow: 0 24px 50px -28px rgba(26, 35, 50, 0.45), 0 2px 6px rgba(26, 35, 50, 0.06);
    container-type: inline-size;
  }
  .bk-face.back { transform: rotateY(180deg); }
  /* The fold: a soft shadow along the spine edge of each page. */
  .bk-face::after { content: ''; position: absolute; inset: 0; pointer-events: none; }
  .bk-face.front::after { background: linear-gradient(to right, rgba(26,35,50,0.10), rgba(26,35,50,0) 7%); }
  .bk-face.back::after  { background: linear-gradient(to left,  rgba(26,35,50,0.10), rgba(26,35,50,0) 7%); }
  .bk-face.front { border-radius: 0 4px 4px 0; }
  .bk-face.back  { border-radius: 4px 0 0 4px; }

  /* ── Page content. --u is one printed point, so 12pt type is calc(var(--u) * 12). ── */
  .pg-in { --u: calc(100cqw / 396); position: relative; height: 100%; padding: calc(var(--u) * 32) calc(var(--u) * 34); }

  .bk-cover { text-align: center; }
  .bk-church { font-size: calc(var(--u) * 10); color: #5f6876; padding-top: calc(var(--u) * 10); }
  .bk-emblems { display: flex; justify-content: center; align-items: center; gap: calc(var(--u) * 22); margin-top: calc(var(--u) * 30); }
  .bk-emblems img { height: calc(var(--u) * 97); width: auto; pointer-events: none; }
  .bk-kicker { margin-top: calc(var(--u) * 30); font-family: 'Cormorant Garamond', serif; font-style: italic; font-weight: 500; font-size: calc(var(--u) * 19); color: var(--teal); }
  .bk-title { margin-top: calc(var(--u) * 6); font-family: 'Cormorant Garamond', serif; font-weight: 600; font-size: calc(var(--u) * 31); line-height: 1.06; letter-spacing: -0.01em; text-wrap: balance; }
  .bk-date { margin-top: calc(var(--u) * 12); font-size: calc(var(--u) * 11.5); color: #334455; }
  .bk-rule { width: calc(var(--u) * 54); margin: calc(var(--u) * 24) auto calc(var(--u) * 22); border-top: calc(var(--u) * 1.2) solid var(--teal); }
  .bk-theme { font-family: 'Cormorant Garamond', serif; font-style: italic; font-weight: 500; font-size: calc(var(--u) * 17); line-height: 1.3; padding: 0 calc(var(--u) * 30); text-wrap: balance; }
  .bk-ref { margin-top: calc(var(--u) * 8); font-size: calc(var(--u) * 10); color: #5f6876; }
  .bk-addr { position: absolute; left: 0; right: 0; bottom: calc(var(--u) * 30); font-size: calc(var(--u) * 8.5); line-height: 1.5; color: #5f6876; }

  .bk-program { font-family: 'Cormorant Garamond', serif; font-size: calc(var(--u) * var(--pt)); }
  .bk-ph { text-align: center; font-weight: 600; font-size: calc(var(--u) * 17); line-height: 1.2; }
  .bk-ph-sub { text-align: center; font-family: 'Poppins', sans-serif; font-size: calc(var(--u) * 9); color: #5f6876; margin: calc(var(--u) * 4) 0 calc(var(--u) * 16); }
  .bk-row { display: flex; align-items: baseline; gap: calc(var(--u) * 4); margin: calc(var(--u) * var(--gap)) 0; line-height: 1.3; }
  .bk-part { font-weight: 700; white-space: nowrap; }
  .bk-dots { flex: 1 1 auto; min-width: calc(var(--u) * 10); border-bottom: calc(var(--u) * 1) dotted #8a93a0; transform: translateY(calc(var(--u) * -3)); }
  .bk-who { text-align: right; min-width: 0; }
  .bk-who.grp { font-style: italic; }
  .bk-under { text-align: center; font-style: italic; color: #334455; margin: calc(var(--u) * (3 - var(--gap))) 0 calc(var(--u) * var(--gap)); }

  .bk-back { font-size: calc(var(--u) * 9.5); line-height: 1.3; }
  .bk-back-title { text-align: center; font-family: 'Cormorant Garamond', serif; font-weight: 600; font-size: calc(var(--u) * 19); margin-bottom: calc(var(--u) * 10); }
  .bk-sec { font-weight: 600; font-size: calc(var(--u) * 10.5); margin-bottom: calc(var(--u) * 2); }
  .bk-ann { margin-bottom: calc(var(--u) * 7); }
  .bk-ann ul { margin: 0; padding-left: calc(var(--u) * 16); list-style: circle; }
  .bk-ann li { margin: calc(var(--u) * 0.6) 0; }
  .bk-ann li.bf { list-style: disc; }
  .bk-mission { text-align: center; margin-bottom: calc(var(--u) * 10); }
  .bk-mission p { margin: 0; color: #334455; }
  .bk-offer { margin: calc(var(--u) * 6) 0; font-weight: 500; }
  .bk-pleasant { text-align: center; margin-top: calc(var(--u) * 10); font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: calc(var(--u) * 12); }

  /* ── Controls ── */
  .bk-nav { display: flex; align-items: center; justify-content: center; gap: 18px; margin: 26px auto 0; padding: 0 18px; }
  .bk-arrow { width: 44px; height: 44px; display: inline-grid; place-items: center; border-radius: 999px; border: 1px solid var(--line); background: color-mix(in srgb, #fff 70%, var(--parchment)); color: var(--ink-soft); cursor: pointer; transition: color 0.15s, border-color 0.15s, opacity 0.15s, transform 0.1s; }
  .bk-arrow:hover:not(:disabled) { color: var(--teal); border-color: var(--teal); }
  .bk-arrow:active:not(:disabled) { transform: scale(0.94); }
  .bk-arrow:disabled { opacity: 0.35; cursor: default; }
  .bk-arrow:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .bk-where { min-width: 170px; text-align: center; font-size: 15px; color: var(--ink-soft); }
  .bk-foot { text-align: center; margin: 18px 0 calc(40px + env(safe-area-inset-bottom)); padding: 0 18px; }
  .bk-foot a { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 16px; border-radius: 6px; border: 1px solid var(--line); color: var(--ink); text-decoration: none; font-size: 14px; }
  .bk-foot a:hover { border-color: var(--teal); color: var(--teal); }
  .bk-foot p { margin: 10px 0 0; font-size: 13px; color: var(--ink-soft); }

  @media (prefers-reduced-motion: reduce) {
    .bk-book, .bk-leaf { transition: none; }
  }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="bk-top"><a href="{{ route('welcome') }}">@include('partials._arl') This week’s bulletin</a></div>

<main>
  <h1 style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">{{ $L['title'] }}, {{ $L['date']->format('F j, Y') }}</h1>

  <div class="bk-stage" id="bk-stage" aria-roledescription="booklet">
    <div class="bk-book" id="bk-book">
      <div class="bk-leaf" data-leaf="0" style="z-index: 2;">
        <div class="bk-face front">@include('bulletins._booklet-page', ['page' => 'cover'])</div>
        <div class="bk-face back">@include('bulletins._booklet-page', ['page' => 'left'])</div>
      </div>
      <div class="bk-leaf" data-leaf="1" style="z-index: 1;">
        <div class="bk-face front">@include('bulletins._booklet-page', ['page' => 'right'])</div>
        <div class="bk-face back">@include('bulletins._booklet-page', ['page' => 'back'])</div>
      </div>
    </div>
  </div>

  <nav class="bk-nav" aria-label="Turn pages">
    <button type="button" class="bk-arrow" id="bk-prev" aria-label="Previous page">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <span class="bk-where" id="bk-where" aria-live="polite">Cover</span>
    <button type="button" class="bk-arrow" id="bk-next" aria-label="Next page">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
    </button>
  </nav>

  <div class="bk-foot">
    <a href="{{ route('bulletins.pdf', $bulletin) }}?layout=booklet">Print it</a>
    <p>Print both sides, flip on the short edge, then fold in half.</p>
  </div>
</main>

<script>
(() => {
  const stage = document.getElementById('bk-stage');
  const book  = document.getElementById('bk-book');
  const leaves = [...book.querySelectorAll('.bk-leaf')];
  const where = document.getElementById('bk-where');
  const prev  = document.getElementById('bk-prev');
  const next  = document.getElementById('bk-next');
  const wide  = matchMedia('(min-width: 900px)');

  /* A view = which spread is open (0 closed cover, 1 inside, 2 closed back) and where the book sits. */
  const VIEWS = {
    wide:  [ { s: 0, x: -25, name: 'Cover' }, { s: 1, x: 0, name: 'Order of service' }, { s: 2, x: 25, name: 'Announcements' } ],
    phone: [ { s: 0, x: -25, name: 'Cover' }, { s: 1, x: 25, name: 'Order of service' }, { s: 1, x: -25, name: 'Order of service, continued' }, { s: 2, x: 25, name: 'Announcements' } ],
  };
  const list = () => VIEWS[wide.matches ? 'wide' : 'phone'];
  let i = 0, spread = 0, rest = 0;

  /* Unturned leaves stack first-on-top; turned leaves stack last-on-top. A leaf in motion rides above both. */
  const restZ = (n, turned) => turned ? n + 1 : leaves.length - n;

  function show(to) {
    const views = list();
    i = Math.max(0, Math.min(views.length - 1, to));
    const v = views[i];
    leaves.forEach((leaf, n) => {
      const turned = v.s > n;
      if (leaf.classList.contains('turned') !== turned) {
        stage.classList.add('moving'); clearTimeout(rest); rest = setTimeout(() => stage.classList.remove('moving'), 950);
        leaf.style.zIndex = leaves.length + 1;
        leaf.classList.toggle('turned', turned);
        const settle = (e) => { if (e.target !== leaf) return; leaf.style.zIndex = restZ(n, leaf.classList.contains('turned')); leaf.removeEventListener('transitionend', settle); };
        leaf.addEventListener('transitionend', settle);
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) leaf.style.zIndex = restZ(n, turned);
      }
    });
    spread = v.s;
    book.style.transform = `translateX(${v.x}%)`;
    where.textContent = v.name;
    prev.disabled = i === 0;
    next.disabled = i === views.length - 1;
  }

  prev.addEventListener('click', () => show(i - 1));
  next.addEventListener('click', () => show(i + 1));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') show(i + 1);
    if (e.key === 'ArrowLeft')  show(i - 1);
  });

  /* Tap the right side to go on, the left side to go back. Swipe works too. */
  let x0 = null, swiped = false;
  stage.addEventListener('pointerdown', (e) => { x0 = e.clientX; swiped = false; });
  stage.addEventListener('pointerup', (e) => {
    if (x0 === null) return;
    const dx = e.clientX - x0; x0 = null;
    if (Math.abs(dx) > 40) { swiped = true; show(i + (dx < 0 ? 1 : -1)); }
  });
  stage.addEventListener('click', (e) => {
    if (swiped) { swiped = false; return; }
    const r = stage.getBoundingClientRect();
    show(i + (e.clientX > r.left + r.width / 2 ? 1 : -1));
  });

  /* Crossing the phone/computer width keeps you on the same spread. */
  wide.addEventListener('change', () => show(list().findIndex(v => v.s === spread)));
  show(0);
})();
</script>

@include('partials._event-tracker')
</body>
</html>
