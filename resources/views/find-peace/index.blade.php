<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Finding Peace · Shalom</title>
<meta name="description" content="A quiet place. Tell us where you are — a feeling, a question, a verse, anything.">
<link rel="canonical" href="{{ url('/find-peace') }}">
<meta name="robots" content="index, follow">
<meta property="og:title" content="Finding Peace · Shalom">
<meta property="og:description" content="A quiet place. Tell us where you are.">
<meta property="og:url" content="{{ url('/find-peace') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #fefcef; --bg-elevated: #f7f1d8;
    --ink: #1a2332; --ink-soft: #334455; --ink-faint: rgba(26,35,50,0.45);
    /* Light mode: brass tokens map to teal so the accent matches the main site. */
    --brass: #03617A; --brass-bright: #048aaa;
    --line: rgba(26,35,50,0.10); --line-strong: rgba(26,35,50,0.18);
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --bg: #0a1014; --bg-elevated: #111820;
      --ink: #fefcef; --ink-soft: rgba(254,252,239,0.65); --ink-faint: rgba(254,252,239,0.35);
      /* Dark mode: warm brass reads better on deep bg. */
      --brass: #b08d3c; --brass-bright: #d4a94a;
      --line: rgba(254,252,239,0.08); --line-strong: rgba(254,252,239,0.15);
    }
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }
  body {
    font-family: 'Poppins', -apple-system, sans-serif;
    background: var(--bg); color: var(--ink);
    line-height: 1.7; font-weight: 300;
    -webkit-font-smoothing: antialiased;
  }

  .hero {
    min-height: 100vh;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 2rem; position: relative; text-align: center;
  }
  @font-face {
    font-family: 'Xtreem';
    src: url('/fonts/XtreemMedium.ttf') format('truetype');
    font-weight: 500; font-style: normal; font-display: swap;
  }
  .mark {
    position: absolute; top: 2rem; left: 50%; transform: translateX(-50%);
    font-family: 'Xtreem', 'Poppins', sans-serif;
    font-style: normal; font-weight: 500;
    color: var(--ink);
    text-transform: lowercase;
    font-size: clamp(56px, 11vw, 88px);
    letter-spacing: -0.02em; line-height: 0.8;
  }
  .hero-content { max-width: 640px; width: 100%; }
  .hero h1 {
    font-size: clamp(2rem, 5vw, 2.75rem);
    font-weight: 200; letter-spacing: -0.01em;
    color: var(--ink); margin-top: clamp(80px, 14vw, 120px); margin-bottom: 0.75rem; line-height: 1.2;
  }
  .hero .sub { font-size: 1.05rem; color: var(--ink-soft); font-weight: 300; margin-bottom: 2.5rem; }
  .search-wrap { position: relative; margin-bottom: 1.5rem; }
  .search-input {
    width: 100%; padding: 1.15rem 1.5rem;
    font-size: 1rem; font-family: inherit; font-weight: 300;
    background: var(--bg-elevated);
    border: 1px solid var(--line); border-radius: 8px;
    color: var(--ink); outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
  }
  .search-input:focus { border-color: var(--brass); box-shadow: 0 0 0 4px rgba(176,141,60,0.12); }
  .search-input::placeholder { color: var(--ink-faint); font-weight: 300; }
  .whisper { font-size: 0.85rem; color: var(--ink-faint); font-weight: 300; margin-bottom: 2.5rem; }

  /* Q&A cards directly below search */
  .qa-cards {
    display: grid; gap: 0.75rem;
    text-align: left;
    margin-bottom: 5rem;
  }
  .qa-card {
    background: var(--bg-elevated);
    border: 1px solid var(--line);
    border-radius: 6px;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .qa-card:hover { border-color: var(--brass); box-shadow: 0 4px 16px -8px rgba(176,141,60,0.25); }
  .qa-card details summary {
    cursor: pointer; list-style: none;
    padding: 1.1rem 1.4rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    font-size: 0.95rem; color: var(--ink); font-weight: 400;
    transition: color 0.2s;
    user-select: none;
  }
  .qa-card details summary::-webkit-details-marker { display: none; }
  .qa-card details summary:hover { color: var(--brass-bright); }
  .qa-card-chev {
    width: 9px; height: 9px;
    border-right: 1.5px solid var(--brass);
    border-bottom: 1.5px solid var(--brass);
    transform: rotate(-45deg);
    transition: transform 0.3s;
    flex-shrink: 0;
  }
  .qa-card details[open] .qa-card-chev { transform: rotate(45deg); margin-bottom: -3px; }
  .qa-card-body {
    padding: 0 1.4rem 1.4rem;
    border-top: 1px solid var(--line);
    padding-top: 1.1rem;
    margin-top: -1px;
  }
  .qa-card-answer {
    font-size: 0.92rem; color: var(--ink-soft); font-weight: 300;
    line-height: 1.65; margin-bottom: 1rem;
  }
  .qa-card-readmore {
    display: inline-flex; align-items: center; gap: 0.4rem;
    font-size: 0.7rem; letter-spacing: 0.2em;
    color: var(--brass); text-transform: uppercase;
    text-decoration: none; font-weight: 500;
    transition: color 0.2s, gap 0.2s;
  }
  .qa-card-readmore:hover { color: var(--brass-bright); gap: 0.6rem; }
  .qa-card-meta {
    display: block; font-size: 0.65rem; letter-spacing: 0.18em;
    color: var(--ink-faint); text-transform: uppercase;
    margin-bottom: 0.4rem; font-weight: 500;
  }

  .search-status {
    text-align: center; font-size: 0.75rem; letter-spacing: 0.18em;
    color: var(--ink-faint); text-transform: uppercase;
    margin-bottom: 1rem; font-weight: 400;
  }
  .search-status.hidden { display: none; }

  .scroll-cue {
    position: absolute; bottom: 1.4rem; left: 50%;
    transform: translateX(-50%);
    display: inline-flex; flex-direction: column; align-items: center; gap: 0.5rem;
    font-size: 0.92rem; color: var(--ink-soft);
    letter-spacing: 0.18em; text-transform: uppercase;
    animation: cue-pulse 3s ease-in-out infinite;
    font-weight: 500;
    text-decoration: none;
  }
  .scroll-cue .arrow { font-size: 1.6rem; line-height: 1; color: var(--brass); }
  .scroll-cue:hover { color: var(--ink); }
  .scroll-cue:hover .arrow { color: var(--brass-bright); }
  @keyframes cue-pulse {
    0%, 100% { opacity: 0.4; }
    50% { opacity: 1; }
  }

  .recent-band { padding: 5rem 2rem; border-top: 1px solid var(--line); }
  .container { max-width: 720px; margin: 0 auto; }
  .section-label {
    font-size: 0.7rem; letter-spacing: 0.3em;
    color: var(--brass); text-transform: uppercase;
    text-align: center; margin-bottom: 2.5rem; font-weight: 400;
  }
  .topic-list { list-style: none; }
  .topic-list li { border-bottom: 1px solid var(--line); }
  .topic-list a {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.5rem 0.25rem;
    color: var(--ink); text-decoration: none;
    font-size: 1.1rem; font-weight: 500;
    transition: color 0.2s, padding-left 0.3s;
  }
  .topic-list a:hover { color: var(--brass-bright); padding-left: 0.75rem; }
  .topic-list .new-badge { font-size: 0.65rem; letter-spacing: 0.2em; color: var(--brass); text-transform: uppercase; font-weight: 500; }
  .topic-list .arrow { color: var(--brass); transition: transform 0.3s, color 0.2s; }
  .topic-list a:hover .arrow { transform: translateX(4px); color: var(--brass-bright); }

  .footer { text-align: center; padding: 4rem 2rem 3rem; border-top: 1px solid var(--line); }
  .footer-mark {
    font-family: 'Xtreem', 'Poppins', sans-serif;
    font-style: normal; font-weight: 500;
    color: var(--ink);
    text-transform: lowercase;
    font-size: 36px; letter-spacing: -0.02em; line-height: 0.8;
    margin-bottom: 0.75rem;
  }
  .footer-text { font-size: 0.8rem; color: var(--ink-faint); font-weight: 300; }

  .empty-state { padding: 4rem 0; text-align: center; color: var(--ink-soft); font-weight: 300; }

  /* ── Mobile fit (≤620px): make hero + cards + cue all fit without overflow ─── */
  @media (max-width: 620px) {
    .hero { min-height: auto; padding: 1.5rem 1rem 4rem; }
    .hero h1 { font-size: clamp(1.6rem, 8vw, 2rem); margin-bottom: 0.6rem; }
    .hero .sub { font-size: 0.9rem; margin-bottom: 1.4rem; }
    .search-input { font-size: 0.95rem; padding: 0.85rem 1rem; }
    .whisper { font-size: 0.78rem; margin-bottom: 1.5rem; }
    .qa-cards { gap: 0.55rem; margin-bottom: 1.4rem; }
    .qa-card details summary {
      padding: 0.75rem 1rem;
      font-size: 0.85rem; gap: 0.6rem;
    }
    .qa-card-chev { width: 7px; height: 7px; }
    .qa-card-body { padding: 0 1rem 1rem; font-size: 0.85rem; }
    .qa-card-meta { font-size: 0.65rem; }
    .scroll-cue {
      position: static; transform: none;
      margin-top: 0.8rem; gap: 0.3rem;
      font-size: 0.78rem; letter-spacing: 0.16em;
    }
    .scroll-cue .arrow { font-size: 1.2rem; }
  }
</style>
</head>
<body>

<section class="hero">
  <div class="mark">shalom</div>
  <div class="hero-content">
    <h1>Peace finds you here.</h1>
    <p class="sub">Tell us where you are.</p>

    <div class="search-wrap">
      <input type="search" id="qa-search" class="search-input" placeholder="What are you walking through today?" autocomplete="off">
    </div>
    <p class="whisper">A feeling, a question, a verse — anything.</p>

    <p class="search-status hidden" id="search-status"></p>

    <div class="qa-cards" id="qa-cards">
      @foreach($featuredQAs as $qa)
        <div class="qa-card" data-qa-id="{{ $qa->id }}">
          <details>
            <summary>
              <span>{{ $qa->question }}</span>
              <span class="qa-card-chev"></span>
            </summary>
            <div class="qa-card-body">
              <span class="qa-card-meta">From: {{ $qa->sermon->title }}</span>
              <p class="qa-card-answer">{{ $qa->answer }}</p>
              <a href="{{ route('find-peace.show', $qa->sermon->slug) }}?qa={{ $qa->id }}" class="qa-card-readmore">
                Read full message
                <span aria-hidden="true">→</span>
              </a>
            </div>
          </details>
        </div>
      @endforeach
    </div>
  </div>

  @if($recent->isNotEmpty())
    <a href="#recent" class="scroll-cue"><span>recent messages</span><span class="arrow">↓</span></a>
  @endif
</section>

@if($recent->isNotEmpty())
<section class="recent-band" id="recent">
  <div class="container">
    <div class="section-label">Recent Messages</div>
    <ul class="topic-list">
      @foreach($recent as $i => $sermon)
        <li>
          <a href="{{ route('find-peace.show', $sermon->slug) }}">
            <span>{{ $sermon->title }}@if($i === 0) <span class="new-badge">· NEW</span>@endif</span>
            <span class="arrow">→</span>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</section>
@else
<section class="recent-band">
  <div class="container empty-state">
    <p>The first message is being prepared.</p>
  </div>
</section>
@endif

<div style="text-align: center; padding: 1rem 0 0; font-size: 0.85rem;"><a href="{{ route('peace.share.show') }}" style="color: var(--brass); text-decoration: none;">Got your own question or experience? Add yours →</a></div>
<footer class="footer">
  <div class="footer-mark">shalom</div>
  <p class="footer-text">Peace, wellness, and freedom — for every member of our community.</p>
</footer>

{{-- Q&A corpus baked into the page for MiniSearch --}}
<script id="qa-corpus" type="application/json">{!! json_encode($qaCorpus, JSON_UNESCAPED_SLASHES) !!}</script>

<script src="https://cdn.jsdelivr.net/npm/minisearch@7.1.1/dist/umd/index.min.js"></script>
<script>
(function () {
  const corpus = JSON.parse(document.getElementById('qa-corpus').textContent || '[]');
  const input = document.getElementById('qa-search');
  const cards = document.getElementById('qa-cards');
  const status = document.getElementById('search-status');
  if (!input || !cards) return;

  // Save the default cards markup so we can restore when search is cleared
  const defaultCardsHTML = cards.innerHTML;

  // Build search index over questions + answers
  const index = new MiniSearch({
    fields: ['question', 'answer'],
    storeFields: ['id', 'question', 'answer', 'sermon_title', 'sermon_slug'],
    searchOptions: { boost: { question: 4 }, prefix: true, fuzzy: 0.2 },
  });
  index.addAll(corpus);

  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function renderResults(hits) {
    if (hits.length === 0) {
      cards.innerHTML = '<div class="empty-state" style="padding:2rem 0;">No matches yet — try simpler words.</div>';
      return;
    }
    cards.innerHTML = hits.slice(0, 12).map(h => `
      <div class="qa-card" data-qa-id="${h.id}">
        <details>
          <summary>
            <span>${escapeHtml(h.question)}</span>
            <span class="qa-card-chev"></span>
          </summary>
          <div class="qa-card-body">
            <span class="qa-card-meta">From: ${escapeHtml(h.sermon_title)}</span>
            <p class="qa-card-answer">${escapeHtml(h.answer)}</p>
            <a href="/find-peace/${encodeURIComponent(h.sermon_slug)}?qa=${h.id}" class="qa-card-readmore">
              Read full message <span aria-hidden="true">→</span>
            </a>
          </div>
        </details>
      </div>
    `).join('');
  }

  let debounce;
  input.addEventListener('input', () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      const q = input.value.trim();
      if (q === '') {
        cards.innerHTML = defaultCardsHTML;
        status.classList.add('hidden');
        return;
      }
      const hits = index.search(q, { combineWith: 'AND' });
      const total = hits.length;
      status.textContent = total === 0
        ? 'No matches'
        : (total === 1 ? '1 match' : total + ' matches');
      status.classList.remove('hidden');
      renderResults(hits);
    }, 140);
  });
})();
</script>

</body>
</html>
