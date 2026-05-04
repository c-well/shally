<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@include('partials.seo-head', [
  'title'       => 'Bible — KJV & ESV Search · The Church of Peace',
  'description' => 'Search the Bible online — King James Version and English Standard Version. Free, fast, no app required. Hosted by Shalom Seventh-day Adventist Church in the Bronx, NY.',
  'path'        => '/bible',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

{{-- Schema.org: WebApplication with SearchAction so Google understands this is a search tool --}}
<script type="application/ld+json">@verbatim
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Bible Search (KJV & ESV)",
  "url": "https://thechurchofpeace.org/bible",
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
    "target": "https://thechurchofpeace.org/bible?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
@endverbatim</script>

<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); --cream:#f7f1d8; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  main { max-width: 760px; margin: 0 auto; padding: clamp(36px, 6vh, 64px) clamp(20px, 5vw, 32px) 80px; }

  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px, 6vw, 60px); font-weight: 500; letter-spacing: -0.02em; line-height: 1.05; color: var(--ink); margin-bottom: 16px; text-align: center; }
  .subtitle { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.5; color: var(--ink-soft); margin: 0 auto 36px; text-align: center; max-width: 540px; }

  /* Search input */
  .search-wrap {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px;
    background: #fff;
    border: 1px solid var(--line); border-radius: 8px;
    box-shadow: 0 8px 28px -10px rgba(26,35,50,0.12);
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .search-wrap:focus-within { border-color: var(--teal); box-shadow: 0 8px 32px -8px rgba(3,97,122,0.22); }
  .search-wrap svg { color: var(--ink-soft); flex-shrink: 0; }
  .search-input {
    flex: 1; border: 0; background: transparent; outline: none;
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(20px, 3vw, 28px); font-weight: 500;
    color: var(--ink); letter-spacing: -0.01em;
    padding: 4px 0;
  }
  .search-input::placeholder { color: rgba(26,35,50,0.4); font-family: 'Instrument Sans', sans-serif; font-size: 16px; font-weight: 500; letter-spacing: 0; }
  .search-clear { background: transparent; border: 0; color: var(--ink-soft); cursor: pointer; opacity: 0.6; padding: 4px 8px; display: none; font-family: 'JetBrains Mono', monospace; font-size: 12px; }
  .search-clear:hover { opacity: 1; color: var(--ink); }
  .search-wrap.has-text .search-clear { display: inline-flex; }

  /* Translation tabs (KJV / ESV) */
  .translation-tabs { margin-top: 14px; display: flex; gap: 4px; justify-content: center; }
  .translation-tab {
    padding: 7px 16px; background: transparent;
    color: var(--ink-soft); border: 1px solid transparent; border-radius: 999px;
    font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 500;
    letter-spacing: 0.14em; text-transform: uppercase;
    cursor: pointer; transition: color 0.15s, background 0.15s, border-color 0.15s;
  }
  .translation-tab:hover { color: var(--ink); }
  .translation-tab.active { background: var(--ink); color: #fff; border-color: var(--ink); }

  .hint { margin-top: 10px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }

  /* Quick links */
  .section-label { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); margin: 50px 0 16px; }
  .quick-links { display: flex; flex-wrap: wrap; gap: 8px; }
  .quick-link {
    padding: 9px 16px;
    background: var(--cream); color: var(--ink);
    border: 1px solid var(--line); border-radius: 999px;
    font-family: 'Cormorant Garamond', serif; font-size: 16px; font-weight: 500;
    text-decoration: none;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
    cursor: pointer;
  }
  .quick-link:hover, .quick-link:focus-visible { background: var(--teal); color: #fff; border-color: var(--teal); outline: none; }

  /* Results */
  .results { margin-top: 36px; }
  .result-meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 14px; opacity: 0.7; }
  .result {
    padding: 18px 0;
    border-bottom: 1px solid var(--line);
  }
  .result-ref {
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px; font-weight: 600;
    color: var(--teal); letter-spacing: 0.04em;
    margin-bottom: 6px;
  }
  .result-text {
    font-family: 'Cormorant Garamond', serif;
    font-size: 19px; line-height: 1.5;
    color: var(--ink);
  }
  .result-text mark {
    background: rgba(176,141,60,0.18); color: var(--ink); padding: 0 2px; border-radius: 2px;
  }
  .results-empty {
    padding: 30px 0; text-align: center;
    font-family: 'Cormorant Garamond', serif;
    font-style: italic; color: var(--ink-soft); opacity: 0.7;
  }
  .loading {
    padding: 30px 0; text-align: center;
    font-family: 'JetBrains Mono', monospace; font-size: 11px;
    letter-spacing: 0.16em; text-transform: uppercase;
    color: var(--ink-soft); opacity: 0.6;
  }
  .more-btn {
    margin: 24px auto 0; display: block;
    padding: 12px 22px; background: transparent;
    color: var(--teal); border: 1px solid var(--teal); border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700;
    letter-spacing: 0.2em; text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
  }
  .more-btn:hover { background: var(--teal); color: #fff; }

  /* Cross-link footer */
  .cross-links {
    margin-top: 60px; padding: 28px 24px;
    background: var(--cream); border-radius: 8px;
    text-align: center;
  }
  .cross-links h3 { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; margin-bottom: 14px; }
  .cross-links p { font-size: 14px; line-height: 1.5; color: var(--ink-soft); margin-bottom: 16px; }
  .cross-links a {
    display: inline-block; margin: 0 12px;
    color: var(--teal); font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    text-decoration: none;
  }
  .cross-links a:hover { color: var(--teal-dark); text-decoration: underline; }
</style>
</head>
<body>

@include('partials.site-menu')

<main>
  <div class="eyebrow">King James Version · free · no app required</div>
  <h1>Read the Bible.</h1>
  <p class="subtitle">Search any verse, phrase, or reference. Browse popular passages. All hosted right here on Shalom — no need to leave the site.</p>

  <div class="search-wrap" id="search-wrap">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="search" id="bible-input" class="search-input" placeholder="Try: John 3:16 · faith · the Lord is my shepherd" autocomplete="off" autocapitalize="off" spellcheck="false">
    <button type="button" class="search-clear" id="bible-clear" aria-label="Clear search">×</button>
  </div>
  <div class="translation-tabs" role="tablist">
    <button class="translation-tab active" data-trans="kjv" type="button">KJV</button>
    <button class="translation-tab" data-trans="esv" type="button">ESV</button>
  </div>
  <div class="hint" id="bible-hint">Type a verse reference or any words to search</div>

  <div class="section-label">Popular passages</div>
  <div class="quick-links">
    <a class="quick-link" data-q="John 3:16">John 3:16</a>
    <a class="quick-link" data-q="Psalm 23">Psalm 23</a>
    <a class="quick-link" data-q="1 Corinthians 13">1 Corinthians 13</a>
    <a class="quick-link" data-q="Romans 8">Romans 8</a>
    <a class="quick-link" data-q="Genesis 1">Genesis 1</a>
    <a class="quick-link" data-q="Matthew 5">Matthew 5 (Beatitudes)</a>
    <a class="quick-link" data-q="Philippians 4:13">Philippians 4:13</a>
    <a class="quick-link" data-q="Proverbs 3:5">Proverbs 3:5</a>
    <a class="quick-link" data-q="Isaiah 40:31">Isaiah 40:31</a>
  </div>

  <div class="results" id="bible-results"></div>

  <div class="cross-links">
    <h3>Going deeper this week</h3>
    <p>The Sabbath School lesson is studying a memory verse all members are working through. Peace Notes is the pastor's weekly reflection.</p>
    <a href="{{ route('lesson.show') }}">This week's lesson →</a>
    <a href="{{ route('peace-notes') }}">Peace Notes →</a>
  </div>
</main>

@include('partials.footer')

<script src="https://cdn.jsdelivr.net/npm/minisearch@7.1.1/dist/umd/index.min.js"></script>
<script>
(function () {
  const input    = document.getElementById('bible-input');
  const clearBtn = document.getElementById('bible-clear');
  const wrap     = document.getElementById('search-wrap');
  const hint     = document.getElementById('bible-hint');
  const results  = document.getElementById('bible-results');

  const indexes = {};   // { kjv: MiniSearch, esv: MiniSearch }
  const loadingFlags = {};
  let trans = 'kjv';
  let limit = 30;
  let lastQ = '';

  function loadCorpus(name) {
    if (loadingFlags[name] || indexes[name]) return Promise.resolve(indexes[name]);
    loadingFlags[name] = true;
    hint.textContent = 'Loading ' + name.toUpperCase() + '…';
    return fetch('/search/' + name + '.json')
      .then(r => r.json())
      .then(docs => {
        const mini = new MiniSearch({
          fields: ['ref', 'text'],
          storeFields: ['ref', 'text'],
          searchOptions: { boost: { ref: 5 }, prefix: true, fuzzy: 0.15 },
        });
        mini.addAll(docs);
        indexes[name] = mini;
        loadingFlags[name] = false;
        if (trans === name) hint.textContent = 'Type a verse reference or any words to search';
        if (lastQ && trans === name) doSearch(lastQ);
        return mini;
      })
      .catch(() => { loadingFlags[name] = false; hint.textContent = 'Could not load ' + name.toUpperCase() + '. Refresh to retry.'; });
  }

  // Highlight match terms in result text
  function highlight(text, terms) {
    if (!terms || !terms.length) return text;
    const esc = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    const re = new RegExp('\\b(' + esc.join('|') + ')', 'gi');
    return text.replace(re, '<mark>$1</mark>');
  }

  function doSearch(q) {
    lastQ = q;
    if (!q.trim()) { results.innerHTML = ''; return; }
    const idx = indexes[trans];
    if (!idx) { loadCorpus(trans); return; }
    const hits = idx.search(q.trim(), { combineWith: 'AND' });
    if (!hits.length) {
      results.innerHTML = '<div class="results-empty">No verses match — try simpler words.</div>';
      return;
    }
    const totalCount = hits.length;
    const shown = hits.slice(0, limit);
    const terms = q.trim().split(/\s+/).filter(t => t.length >= 2);
    let html = '<div class="result-meta">' + totalCount + ' verse' + (totalCount === 1 ? '' : 's') + ' found' + (totalCount > limit ? ' · showing first ' + limit : '') + '</div>';
    html += shown.map(h => {
      return '<div class="result"><div class="result-ref">' + h.ref + '</div><div class="result-text">' + highlight(h.text, terms) + '</div></div>';
    }).join('');
    if (totalCount > limit) {
      html += '<button type="button" class="more-btn" id="more-btn">Show more</button>';
    }
    results.innerHTML = html;
    const more = document.getElementById('more-btn');
    if (more) more.addEventListener('click', () => { limit += 30; doSearch(lastQ); });
  }

  let debounce;
  input.addEventListener('input', () => {
    wrap.classList.toggle('has-text', !!input.value);
    clearTimeout(debounce);
    debounce = setTimeout(() => { limit = 30; doSearch(input.value); }, 180);
  });
  clearBtn.addEventListener('click', () => { input.value = ''; wrap.classList.remove('has-text'); results.innerHTML = ''; input.focus(); });
  document.querySelectorAll('.quick-link').forEach(a => {
    a.addEventListener('click', (e) => { e.preventDefault(); input.value = a.dataset.q; wrap.classList.add('has-text'); limit = 30; doSearch(a.dataset.q); window.scrollTo({ top: results.offsetTop - 80, behavior: 'smooth' }); });
  });

  // Pre-fill from ?q= URL param (so SearchAction in schema.org works)
  const urlQ = new URLSearchParams(window.location.search).get('q');
  if (urlQ) { input.value = urlQ; wrap.classList.add('has-text'); }

  // Translation tab switching
  document.querySelectorAll('.translation-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.translation-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      trans = tab.dataset.trans;
      loadCorpus(trans).then(() => { if (lastQ) { limit = 30; doSearch(lastQ); } });
    });
  });

  // Eagerly start loading KJV in the background after a short idle so the
  // first search is instant. ESV loads on demand when the tab is clicked.
  setTimeout(() => loadCorpus('kjv'), 300);
})();
</script>
</body>
</html>
