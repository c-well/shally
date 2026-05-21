<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@include('partials.seo-head', [
  'title'       => 'Search Shalom · The Church of Peace',
  'description' => 'Find anything on the Shalom SDA Church website — pages, peace notes, bulletins, Sabbath School lessons, and events.',
  'path'        => '/search',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
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
    border: 1px solid var(--line); border-radius: 4px;
    box-shadow: 0 8px 28px -10px rgba(26,35,50,0.12);
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .search-wrap:focus-within { border-color: var(--teal); box-shadow: 0 8px 32px -8px rgba(3,97,122,0.22); }
  .search-wrap svg { color: var(--ink-soft); flex-shrink: 0; }
  .search-input {
    flex: 1; border: 0; background: transparent; outline: none;
    font-family: 'Instrument Sans', sans-serif;
    font-size: clamp(18px, 2.6vw, 22px); font-weight: 500;
    color: var(--ink); letter-spacing: 0;
    padding: 4px 0;
  }
  .search-input::placeholder { color: rgba(26,35,50,0.4); font-weight: 500; }
  .search-clear { background: transparent; border: 0; color: var(--ink-soft); cursor: pointer; opacity: 0.6; padding: 4px 8px; display: none; font-family: 'JetBrains Mono', monospace; font-size: 12px; }
  .search-wrap.has-text .search-clear { display: inline-flex; }

  .hint { margin-top: 10px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }

  /* Filter pills */
  .filter-pills { margin-top: 18px; display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
  .filter-pill {
    padding: 6px 14px; background: transparent; color: var(--ink-soft);
    border: 1px solid var(--line); border-radius: 4px;
    font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 500;
    letter-spacing: 0.14em; text-transform: uppercase;
    cursor: pointer; transition: all 0.15s;
  }
  .filter-pill:hover { color: var(--ink); border-color: var(--ink-soft); }
  .filter-pill.active { background: var(--ink); color: #fff; border-color: var(--ink); }
  .filter-pill .count { margin-left: 6px; opacity: 0.6; }

  /* Results */
  .results { margin-top: 36px; }
  .result-meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 14px; opacity: 0.7; }

  .result {
    display: block;
    padding: 18px 0;
    border-bottom: 1px solid var(--line);
    text-decoration: none;
    color: inherit;
    transition: background 0.15s;
  }
  .result:hover { background: rgba(3,97,122,0.04); margin: 0 -10px; padding-left: 10px; padding-right: 10px; }
  .result-kind {
    display: inline-block; padding: 2px 8px;
    background: var(--cream); color: var(--ink-soft);
    border-radius: 3px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 9px; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase;
    margin-bottom: 8px;
  }
  .result-kind.page       { background: rgba(3,97,122,0.10);  color: var(--teal-dark); }
  .result-kind.peace-note { background: rgba(176,141,60,0.10); color: var(--brass); }
  .result-kind.bulletin   { background: rgba(45,134,89,0.10);  color: #2d8659; }
  .result-kind.event      { background: rgba(168,42,31,0.08);  color: #a82a1f; }
  .result-kind.lesson     { background: rgba(107,77,138,0.10); color: #6b4d8a; }
  .result-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px; font-weight: 500; line-height: 1.3;
    color: var(--ink); margin-bottom: 4px;
  }
  .result-title mark { background: rgba(176,141,60,0.22); color: var(--ink); padding: 0 2px; border-radius: 2px; }
  .result-desc {
    font-family: 'Cormorant Garamond', serif;
    font-size: 16px; line-height: 1.5; color: var(--ink-soft);
  }
  .result-desc mark { background: rgba(176,141,60,0.18); color: var(--ink); padding: 0 2px; border-radius: 2px; }
  .results-empty { padding: 30px 0; text-align: center; font-family: 'Cormorant Garamond', serif; font-style: italic; color: var(--ink-soft); opacity: 0.7; }

  /* Cross-link footer */
  .cross-links { margin-top: 60px; padding: 28px 24px; background: var(--cream); border-radius: 4px; text-align: center; }
  .cross-links h3 { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; margin-bottom: 14px; }
  .cross-links p { font-size: 14px; line-height: 1.5; color: var(--ink-soft); margin-bottom: 16px; }
  .cross-links a { display: inline-block; margin: 0 12px; color: var(--teal); font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; }
  .cross-links a:hover { color: var(--teal-dark); text-decoration: underline; }
</style>
</head>
<body>

@include('partials.site-menu')

<main>
  <div class="eyebrow">Search Shalom</div>
  <h1>Find anything.</h1>
  <p class="subtitle">Search pages, sermons (Peace Notes), past bulletins, lessons, and events. Looking for Bible verses or hymns? Those have their own pages.</p>

  <div class="search-wrap" id="search-wrap">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="search" id="search-input" class="search-input" placeholder="Try: visit, prayer, communion, baptism…" autocomplete="off" autocapitalize="off" spellcheck="false">
    <button type="button" class="search-clear" id="search-clear" aria-label="Clear search">×</button>
  </div>

  <div class="filter-pills" id="filter-pills">
    <button class="filter-pill active" data-kind="all" type="button">All <span class="count" id="count-all"></span></button>
    <button class="filter-pill" data-kind="page" type="button">Pages <span class="count" id="count-page"></span></button>
    <button class="filter-pill" data-kind="peace-note" type="button">Peace Notes <span class="count" id="count-peace-note"></span></button>
    <button class="filter-pill" data-kind="bulletin" type="button">Bulletins <span class="count" id="count-bulletin"></span></button>
    <button class="filter-pill" data-kind="lesson" type="button">Lessons <span class="count" id="count-lesson"></span></button>
    <button class="filter-pill" data-kind="event" type="button">Events <span class="count" id="count-event"></span></button>
  </div>

  <div class="hint" id="search-hint">Type any keyword to search the site</div>

  <div class="results" id="search-results"></div>

  <div class="cross-links">
    <h3>Looking for scripture or hymns?</h3>
    <p>Those have their own dedicated search pages so they don't crowd these results.</p>
    <a href="{{ route('bible') }}">Search the Bible →</a>
    <a href="{{ route('hymnal') }}">Search the Hymnal →</a>
  </div>
</main>

@include('partials.footer')

<script src="https://cdn.jsdelivr.net/npm/minisearch@7.1.1/dist/umd/index.min.js"></script>
<script id="site-corpus" type="application/json">{!! json_encode($corpus, JSON_UNESCAPED_SLASHES) !!}</script>
<script>
(function () {
  const corpus = JSON.parse(document.getElementById('site-corpus').textContent || '[]');
  const input    = document.getElementById('search-input');
  const clearBtn = document.getElementById('search-clear');
  const wrap     = document.getElementById('search-wrap');
  const hint     = document.getElementById('search-hint');
  const results  = document.getElementById('search-results');
  const filters  = document.querySelectorAll('.filter-pill');
  let activeKind = 'all';
  let lastQ = '';

  // Build the index once at startup
  const index = new MiniSearch({
    fields: ['title', 'desc'],
    storeFields: ['kind', 'title', 'desc', 'url'],
    searchOptions: { boost: { title: 4 }, prefix: true, fuzzy: 0.15 },
  });
  index.addAll(corpus);

  // Show counts per kind on initial load
  const kinds = ['page','peace-note','bulletin','lesson','event'];
  document.getElementById('count-all').textContent = corpus.length;
  kinds.forEach(k => {
    const c = corpus.filter(x => x.kind === k).length;
    const el = document.getElementById('count-' + k);
    if (el) el.textContent = c;
  });

  function highlight(text, terms) {
    if (!terms || !terms.length || !text) return text;
    const esc = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    const re = new RegExp('\\b(' + esc.join('|') + ')', 'gi');
    return text.replace(re, '<mark>$1</mark>');
  }

  function render(hits, terms) {
    if (!hits.length) {
      results.innerHTML = '<div class="results-empty">No matches in this scope. Try simpler words or All.</div>';
      return;
    }
    results.innerHTML =
      '<div class=\"result-meta\">' + hits.length + ' result' + (hits.length === 1 ? '' : 's') + ' in ' + (activeKind === 'all' ? 'site' : activeKind) + '</div>' +
      hits.map(h => {
        return '<a class=\"result\" href=\"' + h.url + '\">' +
          '<span class=\"result-kind ' + h.kind + '\">' + h.kind.replace('-', ' ') + '</span>' +
          '<div class=\"result-title\">' + highlight(h.title, terms) + '</div>' +
          '<div class=\"result-desc\">' + highlight(h.desc, terms) + '</div>' +
        '</a>';
      }).join('');
  }

  function doSearch(q) {
    lastQ = q;
    if (!q.trim()) { results.innerHTML = ''; hint.textContent = 'Type any keyword to search the site'; return; }
    let hits = index.search(q.trim(), { combineWith: 'AND' });
    if (activeKind !== 'all') hits = hits.filter(h => h.kind === activeKind);
    const terms = q.trim().split(/\s+/).filter(t => t.length >= 2);
    hint.textContent = hits.length ? '' : '';
    render(hits, terms);
  }

  let debounce;
  input.addEventListener('input', () => {
    wrap.classList.toggle('has-text', !!input.value);
    clearTimeout(debounce);
    debounce = setTimeout(() => doSearch(input.value), 140);
  });
  clearBtn.addEventListener('click', () => { input.value = ''; wrap.classList.remove('has-text'); results.innerHTML = ''; input.focus(); });

  filters.forEach(b => {
    b.addEventListener('click', () => {
      filters.forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      activeKind = b.dataset.kind;
      if (lastQ) doSearch(lastQ);
    });
  });

  // ?q= URL param support (deep linking from header search icon, schema.org SearchAction)
  const urlQ = new URLSearchParams(window.location.search).get('q');
  if (urlQ) { input.value = urlQ; wrap.classList.add('has-text'); doSearch(urlQ); }
  else { input.focus(); }
})();
</script>
</body>
</html>
