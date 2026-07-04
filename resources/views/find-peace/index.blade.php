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
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org',
  '@type'    => 'WebSite',
  'name'        => 'Finding Peace',
  'url'         => url('/find-peace'),
  'description' => 'A quiet place for anyone seeking God — messages of peace, one question at a time.',
  'publisher'   => ['@type' => 'Organization', 'name' => 'The Church of Peace', 'url' => url('/')],
], JSON_UNESCAPED_SLASHES) !!}</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }
  body {
    font-family: 'Poppins', -apple-system, sans-serif;
    background: var(--bg); color: var(--ink);
    line-height: 1.7; font-weight: 300;
    -webkit-font-smoothing: antialiased;
  }

  @font-face {
    font-family: 'Xtreem';
    src: url('/fonts/XtreemMedium.ttf') format('truetype');
    font-weight: 500; font-style: normal; font-display: swap;
  }
  /* Quiet top nav — Find Peace's own menu, deliberately not the church header. */
  .fp-nav {
    display: flex; align-items: center; justify-content: space-between;
    max-width: 860px; margin: 0 auto;
    padding: 1.6rem clamp(1.2rem, 4vw, 2rem) 0.5rem;
  }
  .fp-nav .mark {
    font-family: 'Xtreem', 'Poppins', sans-serif;
    font-style: normal; font-weight: 500;
    color: var(--ink); text-transform: lowercase;
    font-size: clamp(38px, 6vw, 52px);
    letter-spacing: -0.02em; line-height: 0.8;
    text-decoration: none;
  }
  .fp-nav-links { display: flex; gap: clamp(0.9rem, 3vw, 1.6rem); }
  .fp-nav-links a {
    font-size: 0.72rem; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--ink-soft); text-decoration: none; font-weight: 400;
    padding: 0.4rem 0; transition: color 0.2s;
  }
  .fp-nav-links a:hover { color: var(--brass-bright); }

  .hero {
    display: flex; flex-direction: column;
    align-items: center; justify-content: flex-start;
    padding: 2rem 2rem 3.5rem; position: relative; text-align: center;
  }
  .hero-content { max-width: 640px; width: 100%; }
  .hero h1 {
    font-size: clamp(2rem, 5vw, 2.75rem);
    font-weight: 200; letter-spacing: -0.01em;
    color: var(--ink); margin-top: clamp(20px, 4vw, 44px); margin-bottom: 0.75rem; line-height: 1.2;
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
  .search-input:focus { border-color: var(--brass); box-shadow: 0 0 0 4px color-mix(in srgb, var(--brass) 12%, transparent); }
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
  .qa-card:hover { border-color: var(--brass); box-shadow: 0 4px 16px -8px color-mix(in srgb, var(--brass) 25%, transparent); }
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
    display: inline-flex; flex-direction: column; align-items: center; gap: 0.5rem;
    margin-top: 2.2rem;
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
  /* Message cards: summary in the heart-line's voice, the scriptures preached
     from, and a share affordance — the archive honors each message (2026-07-04). */
  .msg-item { padding: 1.6rem 0.25rem 1.5rem; }
  .msg-title { display: flex; justify-content: space-between; align-items: center; gap: 1rem; color: var(--ink); text-decoration: none; font-size: 1.1rem; font-weight: 500; transition: color 0.2s; }
  .msg-title:hover { color: var(--brass-bright); }
  .msg-title .arrow { color: var(--brass); flex-shrink: 0; }
  .msg-meta { font-size: 0.72rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-faint); margin-top: 0.4rem; }
  .msg-heart { font-size: 0.95rem; font-weight: 300; line-height: 1.75; color: var(--ink-soft); margin-top: 0.65rem; max-width: 62ch; }
  .msg-foot { display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem; margin-top: 0.85rem; }
  .msg-ref { font-size: 0.72rem; font-weight: 400; letter-spacing: 0.04em; color: var(--ink-soft); border: 1px solid var(--line); border-radius: 6px; padding: 0.32rem 0.6rem; }
  .msg-share { margin-left: auto; font-size: 0.72rem; font-weight: 500; letter-spacing: 0.1em; text-transform: uppercase; color: var(--brass); background: none; border: 1px solid var(--line); border-radius: 7px; padding: 0.45rem 0.85rem; cursor: pointer; transition: border-color .2s, color .2s; }
  .msg-share:hover { border-color: var(--brass); color: var(--brass-bright); }
  .mh-label { font-size: 0.65rem; letter-spacing: 0.28em; text-transform: uppercase; color: var(--brass); margin: 1.6rem 0 0.7rem; text-align: center; }
  .msg-hit { display: block; text-decoration: none; border: 1px solid var(--line); border-radius: 10px; padding: 0.9rem 1.1rem; margin-bottom: 0.55rem; transition: border-color .2s; }
  .msg-hit:hover { border-color: var(--brass); }
  .msg-hit .t { color: var(--ink); font-weight: 500; font-size: 0.98rem; }
  .msg-hit .m { font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-faint); margin-top: 0.25rem; }
  .msg-hit .sn { font-size: 0.85rem; font-weight: 300; font-style: italic; color: var(--ink-soft); margin-top: 0.45rem; line-height: 1.6; }
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

  /* Topics — the words people are actually carrying. Every chip is a real page. */
  .topics-band { padding: 4.5rem 2rem; border-top: 1px solid var(--line); }
  .topics-cloud { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.55rem; max-width: 760px; margin: 0 auto; }
  .topic-pill {
    display: inline-block; padding: 0.5rem 1rem;
    font-size: 0.85rem; font-weight: 300; line-height: 1;
    color: var(--ink-soft); text-decoration: none;
    border: 1px solid var(--line); border-radius: 999px;
    transition: color 0.2s, border-color 0.2s, background 0.2s;
  }
  .topic-pill:hover { color: var(--brass-bright); border-color: var(--brass); background: color-mix(in srgb, var(--brass) 6%, transparent); }

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
    .fp-nav { padding-top: 1.1rem; }
    .fp-nav-links a { font-size: 0.64rem; letter-spacing: 0.16em; }
    .hero { min-height: auto; padding: 1.2rem 1rem 3rem; }
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
@include('partials.find-peace-vars')
</head>
<body>

<nav class="fp-nav" aria-label="Finding Peace">
  <a class="mark" href="{{ route('find-peace.index') }}">shalom</a>
  <div class="fp-nav-links">
    <a href="#recent">Messages</a>
    <a href="#topics">Topics</a>
    <a href="{{ route('find-peace.saved') }}">Yours</a>
  </div>
</nav>

<section class="hero">
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
                <span aria-hidden="true">@include('partials._ar')</span>
              </a>
            </div>
          </details>
        </div>
      @endforeach
    </div>
    <div id="msg-results" data-url="{{ route('find-peace.search-messages') }}"></div>
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
        <li class="msg-item">
          <a class="msg-title" href="{{ route('find-peace.show', $sermon->slug) }}">
            <span>{{ $sermon->title }}@if($i === 0) <span class="new-badge">· NEW</span>@endif</span>
            <span class="arrow">@include('partials._ar')</span>
          </a>
          <div class="msg-meta">{{ $sermon->speaker }}@if($sermon->sermon_date) · {{ $sermon->sermon_date->format('F j, Y') }}@endif</div>
          @php
            $sp = $sermon->summary_paragraphs;
            $firstPara = is_array($sp) ? trim((string) ($sp[0] ?? '')) : \Illuminate\Support\Str::of((string) $sp)->before("\n")->trim();
            $heartText = trim((string) $sermon->heart_line) ?: $firstPara;
          @endphp
          @if((string) $heartText !== '')<p class="msg-heart">{{ $heartText }}</p>@endif
          <div class="msg-foot">
            @foreach($sermon->scriptures as $ref)
              <span class="msg-ref">{{ $ref->reference_display }}</span>
            @endforeach
            <button type="button" class="msg-share"
                    data-slug="{{ $sermon->slug }}"
                    data-title="{{ $sermon->title }}"
                    data-speaker="{{ $sermon->speaker }}">Share this message</button>
          </div>
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

@if(($topics ?? collect())->isNotEmpty())
<section class="topics-band" id="topics">
  <div class="section-label">What are you carrying?</div>
  <div class="topics-cloud">
    @foreach($topics as $t)
      <a class="topic-pill" href="{{ route('find-peace.topic', $t->slug) }}">{{ strtolower($t->name) }}</a>
    @endforeach
  </div>
</section>
@endif

<div style="text-align: center; padding: 1rem 0 0; font-size: 0.85rem;"><a href="{{ route('peace.share.show') }}" style="color: var(--brass); text-decoration: none;">Got your own question or experience? Add yours @include('partials._ar')</a></div>
<footer class="footer">
  <div class="footer-mark">shalom</div>
  <p class="footer-text">Peace, wellness, and freedom — for every member of our community.</p>
</footer>

{{-- Q&A corpus baked into the page for MiniSearch --}}
<script id="qa-corpus" type="application/json">{!! json_encode($qaCorpus, JSON_UNESCAPED_SLASHES) !!}</script>

<script src="https://cdn.jsdelivr.net/npm/minisearch@7.1.1/dist/umd/index.min.js"></script>
<script>
// ── Share a message straight from the archive (native sheet, clipboard fallback) ──
(function () {
  document.querySelectorAll('.msg-share').forEach(btn => {
    btn.addEventListener('click', async () => {
      const url = location.origin + '/find-peace/' + btn.dataset.slug;
      const text = 'Check out this message \u2014 \u201C' + btn.dataset.title + '\u201D'
                 + (btn.dataset.speaker ? ' by ' + btn.dataset.speaker : '') + '. It spoke to me:';
      if (navigator.share) {
        try { await navigator.share({ title: btn.dataset.title, text, url }); return; }
        catch (e) { if (e.name === 'AbortError') return; }
      }
      try { await navigator.clipboard.writeText(text + ' ' + url); } catch (e) {}
      const was = btn.textContent;
      btn.textContent = 'Copied \u2014 paste anywhere';
      setTimeout(() => { btn.textContent = was; }, 2000);
    });
  });
})();

// ── Deep message search: title, speaker, summary, and what was actually SAID
//    (server searches the transcript; the transcript itself is never shown) ──
(function () {
  const input = document.getElementById('qa-search');
  const mount = document.getElementById('msg-results');
  if (!input || !mount) return;
  const URL_ = mount.dataset.url;
  let t = null, seq = 0;
  const esc = s => String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  input.addEventListener('input', () => {
    clearTimeout(t);
    const q = input.value.trim();
    if (q.length < 3) { mount.innerHTML = ''; return; }
    t = setTimeout(async () => {
      const my = ++seq;
      try {
        const r = await fetch(URL_ + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
        const d = await r.json();
        if (my !== seq) return;   // stale response
        if (!d.results || !d.results.length) { mount.innerHTML = ''; return; }
        mount.innerHTML = '<div class="mh-label">Messages</div>' + d.results.map(h =>
          '<a class="msg-hit" href="/find-peace/' + esc(h.slug) + '">'
          + '<div class="t">' + esc(h.title) + '</div>'
          + '<div class="m">' + esc(h.speaker || '') + (h.when ? ' \u00B7 ' + esc(h.when) : '') + '</div>'
          + (h.snippet ? '<div class="sn">\u201C' + esc(h.snippet) + '\u201D</div>'
                       : (h.heart ? '<div class="sn">' + esc(h.heart) + '</div>' : ''))
          + '</a>').join('');
      } catch (e) { /* quiet — Q&A search still works */ }
    }, 300);
  });
})();

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

@include('partials._event-tracker')
</body>
</html>
