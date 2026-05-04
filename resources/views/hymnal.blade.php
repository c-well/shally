<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@include('partials.seo-head', [
  'title'       => 'Hymnal — 694 hymns · The Church of Peace',
  'description' => 'The Adventist Hymnal online. 694 hymns — searchable by title, number, or lyric. Copy any hymn as plain text for worship, ProPresenter, or printing.',
  'path'        => '/hymnal',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

<script type="application/ld+json">@verbatim
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Adventist Hymnal — Search & Copy",
  "url": "https://thechurchofpeace.org/hymnal",
  "applicationCategory": "Reference",
  "operatingSystem": "Any",
  "browserRequirements": "Requires JavaScript",
  "offers": { "@type": "Offer", "price": "0", "priceCurrency": "USD" },
  "publisher": {
    "@type": "Church",
    "name": "Shalom Seventh-day Adventist Church",
    "url": "https://thechurchofpeace.org"
  },
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://thechurchofpeace.org/hymnal?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
@endverbatim</script>

<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --brass-dark:#8c6f2e; --line:rgba(26,35,50,0.10); --cream:#f7f1d8; --warm:#faf3e0; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--warm); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--brass); outline-offset: 2px; border-radius: 3px; }

  main { max-width: 760px; margin: 0 auto; padding: clamp(36px, 6vh, 64px) clamp(20px, 5vw, 32px) 80px; }

  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--brass); margin-bottom: 14px; text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px, 6vw, 60px); font-weight: 500; letter-spacing: -0.02em; line-height: 1.05; color: var(--ink); margin-bottom: 16px; text-align: center; }
  h1 em { color: var(--brass); font-style: italic; }
  .subtitle { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.5; color: var(--ink-soft); margin: 0 auto 36px; text-align: center; max-width: 540px; }

  /* Search input */
  .search-wrap {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px;
    background: #fff;
    border: 1px solid var(--line); border-radius: 4px;
    box-shadow: 0 8px 28px -10px rgba(176,141,60,0.18);
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .search-wrap:focus-within { border-color: var(--brass); box-shadow: 0 8px 32px -8px rgba(176,141,60,0.32); }
  .search-wrap svg { color: var(--ink-soft); flex-shrink: 0; }
  .search-input {
    flex: 1; border: 0; background: transparent; outline: none;
    font-family: 'Instrument Sans', sans-serif;
    font-size: clamp(18px, 2.6vw, 22px); font-weight: 500;
    color: var(--ink); letter-spacing: 0;
    padding: 4px 0;
  }
  .search-input::placeholder { color: rgba(26,35,50,0.4); font-family: 'Instrument Sans', sans-serif; font-size: 16px; font-weight: 500; letter-spacing: 0; }
  .search-clear { background: transparent; border: 0; color: var(--ink-soft); cursor: pointer; opacity: 0.6; padding: 4px 8px; display: none; font-family: 'JetBrains Mono', monospace; font-size: 12px; }
  .search-wrap.has-text .search-clear { display: inline-flex; }

  .hint { margin-top: 10px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }

  /* Quick links */
  .section-label { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); margin: 50px 0 16px; }
  .quick-links { display: flex; flex-wrap: wrap; gap: 8px; }
  .quick-link {
    padding: 9px 16px;
    background: var(--cream); color: var(--ink);
    border: 1px solid var(--line); border-radius: 4px;
    font-family: 'Cormorant Garamond', serif; font-size: 16px; font-weight: 500;
    text-decoration: none;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
    cursor: pointer;
  }
  .quick-link:hover, .quick-link:focus-visible { background: var(--brass); color: #fff; border-color: var(--brass); outline: none; }

  /* Results list */
  .results { margin-top: 36px; }
  .result-meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 14px; opacity: 0.7; }
  .result {
    display: flex; align-items: baseline; gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid var(--line);
    cursor: pointer;
    transition: background 0.15s;
  }
  .result:hover { background: rgba(176,141,60,0.06); margin: 0 -10px; padding-left: 10px; padding-right: 10px; }
  .result-num {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px; color: var(--brass); font-weight: 500; letter-spacing: 0.04em;
    min-width: 50px;
  }
  .result-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 20px; font-weight: 500; line-height: 1.3;
    color: var(--ink); flex: 1;
  }
  .result-title mark { background: rgba(176,141,60,0.22); color: var(--ink); padding: 0 2px; border-radius: 2px; }
  .results-empty { padding: 30px 0; text-align: center; font-family: 'Cormorant Garamond', serif; font-style: italic; color: var(--ink-soft); opacity: 0.7; }
  .more-btn { margin: 24px auto 0; display: block; padding: 12px 22px; background: transparent; color: var(--brass); border: 1px solid var(--brass); border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; cursor: pointer; transition: background 0.15s, color 0.15s; }
  .more-btn:hover { background: var(--brass); color: #fff; }

  /* Hymn detail overlay */
  .hymn-overlay {
    position: fixed; inset: 0; z-index: 300;
    background: rgba(26,35,50,0.55);
    display: none; align-items: center; justify-content: center;
    padding: clamp(12px, 4vw, 40px);
    overflow-y: auto;
  }
  .hymn-overlay.open { display: flex; }
  .hymn-card {
    background: var(--parchment);
    border: 1px solid var(--line); border-radius: 6px;
    max-width: 720px; width: 100%; max-height: calc(100dvh - 60px);
    display: flex; flex-direction: column;
    box-shadow: 0 30px 80px -20px rgba(0,0,0,0.45);
  }
  .hymn-card-header {
    padding: 20px 26px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
    border-bottom: 1px solid var(--line);
  }
  .hymn-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(24px, 3vw, 32px); font-weight: 500; line-height: 1.15;
    color: var(--ink);
  }
  .hymn-card-num {
    display: block; margin-bottom: 4px;
    font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--brass);
    letter-spacing: 0.16em; text-transform: uppercase;
  }
  .hymn-actions { display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0; }
  .btn-copy {
    padding: 9px 14px; background: var(--brass); color: #fff; border: 0; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 6px;
  }
  .btn-copy:hover { background: var(--brass-dark); }
  .btn-copy.copied { background: #2d8659; }
  .btn-close {
    width: 36px; height: 36px; background: transparent; border: 0; cursor: pointer;
    color: var(--ink-soft); border-radius: 999px;
    display: inline-flex; align-items: center; justify-content: center;
    font-family: 'JetBrains Mono', monospace; font-size: 18px;
    transition: background 0.15s;
  }
  .btn-close:hover { background: rgba(0,0,0,0.05); }

  .hymn-body {
    padding: 26px 30px;
    overflow-y: auto;
    font-family: 'Cormorant Garamond', serif;
    font-size: 17px; line-height: 1.6;
    color: var(--ink);
    white-space: pre-wrap;
  }
  .hymn-body .verse-num {
    display: block; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--brass);
    letter-spacing: 0.18em; margin-top: 16px; margin-bottom: 4px;
  }
  .hymn-body .verse-num:first-child { margin-top: 0; }

  /* Cross-link footer */
  .cross-links { margin-top: 60px; padding: 28px 24px; background: var(--cream); border-radius: 4px; text-align: center; }
  .cross-links h3 { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; margin-bottom: 14px; }
  .cross-links p { font-size: 14px; line-height: 1.5; color: var(--ink-soft); margin-bottom: 16px; }
  .cross-links a { display: inline-block; margin: 0 12px; color: var(--brass); font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; }
  .cross-links a:hover { color: var(--brass-dark); text-decoration: underline; }
</style>
</head>
<body>

@include('partials.site-menu')

<main>
  <div class="eyebrow">Adventist Hymnal · 694 hymns</div>
  <h1>The <em>Hymnal</em>.</h1>
  <p class="subtitle">Search by title, number, or any line. Tap a hymn to read it, or copy the full plain text — ready for ProPresenter, printing, or sharing.</p>

  <div class="search-wrap" id="search-wrap">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="search" id="hymn-input" class="search-input" placeholder="Try: Amazing Grace · 50 · holy holy" autocomplete="off" autocapitalize="off" spellcheck="false">
    <button type="button" class="search-clear" id="hymn-clear" aria-label="Clear search">×</button>
  </div>
  <div class="hint" id="hymn-hint">Type a title, number, or any words from the lyrics</div>

  <div class="section-label">Popular hymns</div>
  <div class="quick-links">
    <a class="quick-link" data-q="Amazing Grace">Amazing Grace</a>
    <a class="quick-link" data-q="How Great Thou Art">How Great Thou Art</a>
    <a class="quick-link" data-q="Holy Holy Holy">Holy, Holy, Holy</a>
    <a class="quick-link" data-q="It is Well">It is Well With My Soul</a>
    <a class="quick-link" data-q="Blessed Assurance">Blessed Assurance</a>
    <a class="quick-link" data-q="Great Is Thy Faithfulness">Great Is Thy Faithfulness</a>
    <a class="quick-link" data-q="When We All Get to Heaven">When We All Get to Heaven</a>
  </div>

  <div class="results" id="hymn-results"></div>

  <div class="cross-links">
    <h3>Going deeper</h3>
    <p>Looking for scripture instead? The Bible page has KJV and ESV with the same search experience.</p>
    <a href="{{ route('bible') }}">Bible (KJV & ESV) →</a>
    <a href="{{ route('lesson.show') }}">Sabbath School lesson →</a>
  </div>
</main>

{{-- Hymn detail modal --}}
<div class="hymn-overlay" id="hymn-overlay" role="dialog" aria-modal="true" aria-labelledby="hymn-modal-title">
  <div class="hymn-card">
    <div class="hymn-card-header">
      <div>
        <span class="hymn-card-num" id="hymn-modal-num">Hymn #50</span>
        <h2 class="hymn-card-title" id="hymn-modal-title">Title</h2>
      </div>
      <div class="hymn-actions">
        <button class="btn-copy" id="hymn-copy" type="button" title="Copy plain text">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
          <span id="hymn-copy-label">Copy</span>
        </button>
        <button class="btn-close" id="hymn-close" type="button" aria-label="Close">×</button>
      </div>
    </div>
    <div class="hymn-body" id="hymn-modal-body"></div>
  </div>
</div>

@include('partials.footer')

<script src="https://cdn.jsdelivr.net/npm/minisearch@7.1.1/dist/umd/index.min.js"></script>
<script>
(function () {
  const input    = document.getElementById('hymn-input');
  const clearBtn = document.getElementById('hymn-clear');
  const wrap     = document.getElementById('search-wrap');
  const hint     = document.getElementById('hymn-hint');
  const results  = document.getElementById('hymn-results');
  const overlay  = document.getElementById('hymn-overlay');
  const modalNum = document.getElementById('hymn-modal-num');
  const modalTit = document.getElementById('hymn-modal-title');
  const modalBody= document.getElementById('hymn-modal-body');
  const copyBtn  = document.getElementById('hymn-copy');
  const copyLbl  = document.getElementById('hymn-copy-label');
  const closeBtn = document.getElementById('hymn-close');

  let docs = null, index = null, loading = false, byId = {};
  let limit = 30;
  let lastQ = '';

  function loadCorpus() {
    if (loading || index) return Promise.resolve();
    loading = true;
    hint.textContent = 'Loading hymnal…';
    return fetch('/search/hymnal.json')
      .then(r => r.json())
      .then(data => {
        docs = data;
        data.forEach(d => byId[d.id] = d);
        index = new MiniSearch({
          fields: ['number', 'title', 'body'],
          storeFields: ['id', 'number', 'title'],
          searchOptions: { boost: { title: 4, number: 5 }, prefix: true, fuzzy: 0.15 },
        });
        index.addAll(data);
        loading = false;
        hint.textContent = 'Type a title, number, or any words from the lyrics';
        if (lastQ) doSearch(lastQ);
      })
      .catch(() => { loading = false; hint.textContent = 'Could not load hymnal. Refresh to retry.'; });
  }

  function highlight(text, terms) {
    if (!terms || !terms.length) return text;
    const esc = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    const re = new RegExp('\\b(' + esc.join('|') + ')', 'gi');
    return text.replace(re, '<mark>$1</mark>');
  }

  function doSearch(q) {
    lastQ = q;
    if (!q.trim()) { results.innerHTML = ''; return; }
    if (!index) { loadCorpus(); return; }
    const hits = index.search(q.trim(), { combineWith: 'AND' });
    if (!hits.length) {
      results.innerHTML = '<div class="results-empty">No hymns match — try simpler words or a hymn number.</div>';
      return;
    }
    const total = hits.length;
    const shown = hits.slice(0, limit);
    const terms = q.trim().split(/\s+/).filter(t => t.length >= 2);
    let html = '<div class="result-meta">' + total + ' hymn' + (total === 1 ? '' : 's') + ' found' + (total > limit ? ' · showing first ' + limit : '') + '</div>';
    html += shown.map(h => {
      return '<div class="result" data-id="' + h.id + '">' +
        '<div class="result-num">#' + h.number + '</div>' +
        '<div class="result-title">' + highlight(h.title, terms) + '</div>' +
      '</div>';
    }).join('');
    if (total > limit) html += '<button type="button" class="more-btn" id="more-btn">Show more</button>';
    results.innerHTML = html;
    results.querySelectorAll('.result').forEach(el => {
      el.addEventListener('click', () => openHymn(el.dataset.id));
    });
    const more = document.getElementById('more-btn');
    if (more) more.addEventListener('click', () => { limit += 30; doSearch(lastQ); });
  }

  function openHymn(id) {
    const d = byId[id];
    if (!d) return;
    modalNum.textContent = 'Hymn #' + d.number;
    modalTit.textContent = d.title;
    // Format body: turn standalone "1", "2", "Refrain" lines into styled labels
    const formatted = d.body.replace(/^(\d+|Refrain|Chorus)\s*$/gmi, '<span class="verse-num">$1</span>');
    modalBody.innerHTML = formatted;
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    copyBtn.dataset.id = id;
    copyBtn.classList.remove('copied');
    copyLbl.textContent = 'Copy';
  }
  function closeHymn() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  copyBtn.addEventListener('click', async () => {
    const d = byId[copyBtn.dataset.id];
    if (!d) return;
    const plain = '#' + d.number + ' · ' + d.title + '\n\n' + d.body;
    try {
      await navigator.clipboard.writeText(plain);
      copyBtn.classList.add('copied');
      copyLbl.textContent = 'Copied!';
      setTimeout(() => { copyBtn.classList.remove('copied'); copyLbl.textContent = 'Copy'; }, 1800);
    } catch (e) {
      // Fallback: select + execCommand
      const ta = document.createElement('textarea');
      ta.value = plain; document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy'); copyBtn.classList.add('copied'); copyLbl.textContent = 'Copied!'; } catch (_) {}
      document.body.removeChild(ta);
      setTimeout(() => { copyBtn.classList.remove('copied'); copyLbl.textContent = 'Copy'; }, 1800);
    }
  });
  closeBtn.addEventListener('click', closeHymn);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closeHymn(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && overlay.classList.contains('open')) closeHymn(); });

  let debounce;
  input.addEventListener('input', () => {
    wrap.classList.toggle('has-text', !!input.value);
    clearTimeout(debounce);
    debounce = setTimeout(() => { limit = 30; doSearch(input.value); }, 180);
  });
  clearBtn.addEventListener('click', () => { input.value = ''; wrap.classList.remove('has-text'); results.innerHTML = ''; input.focus(); });
  document.querySelectorAll('.quick-link').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      input.value = a.dataset.q; wrap.classList.add('has-text');
      limit = 30; doSearch(a.dataset.q);
      window.scrollTo({ top: results.offsetTop - 80, behavior: 'smooth' });
    });
  });

  // Pre-fill from ?q= and ?n= URL params
  const params = new URLSearchParams(window.location.search);
  const urlQ = params.get('q');
  const urlN = params.get('n');
  if (urlQ) { input.value = urlQ; wrap.classList.add('has-text'); }
  // Eagerly load corpus
  setTimeout(() => loadCorpus().then(() => {
    if (urlN && byId['hymn-' + urlN]) openHymn('hymn-' + urlN);
    else if (urlQ) doSearch(urlQ);
  }), 200);
})();
</script>
</body>
</html>
