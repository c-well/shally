<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.seo-head', [
  'title'       => 'Scripture Games for Kids — The Church of Peace',
  'description' => 'Fun, gentle games that bring the Word before our children — learn a Bible verse one game at a time.',
  'path'        => '/kids',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;600;700;800;900&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  main { max-width: 880px; margin: 0 auto; padding: clamp(40px,7vh,84px) clamp(20px,5vw,34px) 110px; }
  .eyebrow { font-size: 12px; font-weight: 600; letter-spacing: 0.26em; text-transform: uppercase; color: var(--teal); margin-bottom: 16px; }
  h1 { font-size: clamp(40px,6vw,60px); font-weight: 700; line-height: 1.02; letter-spacing: -0.025em; }
  .lede { margin-top: 20px; font-size: 17px; line-height: 1.6; color: var(--ink-soft); max-width: 560px; }
  .lede b { color: var(--ink); font-weight: 600; }
  .who { margin-top: 22px; font-size: 14px; color: var(--ink-soft); }
  .who b { color: var(--teal); }

  .filters { margin-top: 40px; display: flex; gap: 10px; flex-wrap: wrap; }
  .chip { font-size: 13px; font-weight: 600; padding: 9px 16px; border-radius: var(--r-btn); border: 1px solid var(--line); background: #fff; color: var(--ink-soft); cursor: pointer; }
  .chip.on { background: var(--teal); color: #fff; border-color: var(--teal); }

  .sec { margin-top: 40px; }
  .sec h2 { font-family: 'IBM Plex Serif', serif; font-size: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
  .sec .sub { font-size: 13px; color: var(--ink-soft); margin-top: 4px; }
  .grid { margin-top: 18px; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
  .lvl { display: block; background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px; text-decoration: none; color: inherit; transition: transform .12s, border-color .12s, box-shadow .12s; }
  .lvl:hover { transform: translateY(-2px); border-color: var(--teal); box-shadow: 0 14px 30px -18px rgba(0,0,0,.25); }
  .lvl .ref { font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--teal); }
  .lvl .t { font-family: 'IBM Plex Serif', serif; font-size: 21px; font-weight: 500; margin-top: 7px; }
  .lvl .bk { font-size: 12.5px; color: var(--ink-soft); margin-top: 6px; }
  .lvl .go { font-size: 12px; font-weight: 600; color: var(--teal); margin-top: 12px; }

  .board { margin-top: 56px; background: color-mix(in srgb, var(--brass) 8%, #fff); border: 1px solid color-mix(in srgb, var(--brass) 22%, var(--line)); border-radius: 16px; padding: 24px 26px; }
  .board h2 { font-family: 'IBM Plex Serif', serif; font-size: 22px; font-weight: 500; }
  .board p { font-size: 13px; color: var(--ink-soft); margin-top: 4px; }
  .board ol { margin: 16px 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 8px; }
  .board li { display: flex; align-items: center; justify-content: space-between; font-size: 15px; }
  .board li .nm { font-weight: 500; }
  .board li .st { color: var(--brass-dark, #8c6f2e); font-weight: 600; }
  .empty { font-size: 13px; color: var(--ink-soft); font-style: italic; margin-top: 12px; }
.family-note{margin-top:36px;font-size:13px;line-height:1.6;color:var(--ink-soft)}.family-note a{color:var(--teal)}.lvl .go{min-height:18px}@media(prefers-reduced-motion:reduce){*{transition:none!important}}
</style>
@include('partials.theme-vars')
@include('kids.design')
</head>
<body class="kids kids-index" data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<main>
  <section class="adventure-hero">
    <div class="hero-copy"><div class="eyebrow">THE CHURCH OF PEACE · KIDS</div>
    <h1>Little games.<br><span>Big discoveries.</span></h1>
    <p class="lede">Match, explore, find, and build. Your next Bible adventure starts here.</p>
    <a class="btn" href="#choose-game">Let's explore <span aria-hidden="true">↗</span></a>
    <div class="who" id="who"></div></div>
    <div class="hero-world"><img class="hero-art" src="/kids-assets/adventure-path-v1.jpg" width="1200" height="800" alt="Floating islands with a starry path to an open book" fetchpriority="high">@include('kids.mascot', ['mascotClass'=>'hero-sheep'])<button class="mascot-motion" data-mascot-motion type="button" aria-pressed="true">Pause mascot motion</button></div>
  </section>
  <div class="catalog-heading" id="choose-game"><div><div class="eyebrow">PICK YOUR ADVENTURE</div><h2>What will you discover?</h2></div><p>Choose your kind of adventure. Discover the Word as you play.</p></div>
  <div class="filters" id="filters">
    <button class="chip on" aria-pressed="true" data-band="all">All ages</button>
    <button class="chip" aria-pressed="false" data-band="little">Little (4–6)</button>
    <button class="chip" aria-pressed="false" data-band="older">Kids (7–9)</button>
    <button class="chip" aria-pressed="false" data-band="teens">Teens</button>
  </div>

  @php
    $byType = $levels->groupBy('game_type');
    $meta = [
      'escape_room' => ['Bible Escape Rooms', 'EXPLORE + SOLVE', 'Search the scene. Follow Scripture clues. Unlock the next chapter.'],
      'verse_tetris' => ['Verse Blocks', 'SPELL + BUILD', 'Land letter blocks and build your verse, one word at a time.'],
      'word_search'  => ['Word Quest', 'SEEK + FIND', 'Spot the hidden words. Connect the letters. Complete your quest.'],
      'memory_match' => ['Memory Match', 'FLIP + MATCH', 'A little flip. A perfect pair. How many can you remember?'],
      'hidden_words' => ['Treasure Trail', 'EXPLORE + COLLECT', 'Follow the path and collect the words to uncover your verse.'],
    ];
  @endphp

  @foreach (['escape_room','memory_match','hidden_words','word_search','verse_tetris'] as $gt)
    @if (($byType[$gt] ?? collect())->isNotEmpty())
      <section class="sec genre-{{ $gt }}" data-sec>
        <div class="genre-head"><div class="game-preview" aria-hidden="true">@include('kids.game-art', ['kind'=>$gt])</div><div><div class="genre-tag">{{ $meta[$gt][1] }}</div><h2>{{ $meta[$gt][0] }}</h2><div class="sub">{{ $meta[$gt][2] }}</div></div></div>
        <div class="grid">
          @foreach ($byType[$gt] as $lvl)
            <a class="lvl" href="{{ route('kids.play', $lvl) }}" data-band="{{ $lvl->age_band }}" data-level="{{ $lvl->id }}">
              <div class="ref">{{ $lvl->reference }}</div>
              <div class="t">{{ $lvl->title ?: $lvl->book }}</div>
              <div class="bk">{{ $lvl->book }} · {{ ['little'=>'Ages 4–6','older'=>'Ages 7–9','teens'=>'Teens'][$lvl->age_band] ?? $lvl->age_band }}</div>
              <div class="go">Let’s play @include('partials._ar')</div>
            </a>
          @endforeach
        </div>
      </section>
    @endif
  @endforeach

  <p class="family-note">For families: choose a nickname instead of a full name. Games save progress on this browser. <a href="{{ route('privacy') }}">How we handle game information →</a></p>
  <div class="board">
    <h2>Our discovery crew</h2>
    <p>Celebrating completed puzzles and verse progress. Nicknames appear here with stars earned through our updated games.</p>
    @if ($leaders->isNotEmpty())
      <ol>
        @foreach ($leaders as $p)
          <li><span class="nm">{{ $p->name }}</span> <span class="st">★ {{ $p->checked_stars }}</span></li>
        @endforeach
      </ol>
    @else
      <div class="empty">A new chapter starts here. Finish a game to add your nickname and stars.</div>
    @endif
  </div>
</main>

@include('partials.footer')
@include('kids.mascot-script')
<script>
  // Fetch current checked totals; never render a nickname as HTML.
  try {
    const pl=JSON.parse(localStorage.getItem('cop_kid')||'null');
    if(pl?.token)fetch('{{ route('kids.profile') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({token:pl.token})}).then(r=>r.ok?r.json():null).then(d=>{
      if(!d)return;if(d.preview){localStorage.removeItem('cop_kid');document.getElementById('who').textContent='Choose a game to start your adventure.';return;}document.getElementById('who').textContent=`Welcome back, ${d.name} · ★ ${d.total_stars}`;
      pl.total_stars=d.total_stars;localStorage.setItem('cop_kid',JSON.stringify(pl));
      document.querySelectorAll('[data-level]').forEach(card=>{const stars=d.levels[card.dataset.level]||0;if(stars){const go=card.querySelector('.go');go.textContent=stars===3?'★★★ Completed · Play again →':'★ In progress · Play →';}});
    }).catch(()=>{});
  } catch(_){}
  // age filter
  var chips = document.querySelectorAll('.chip');
  chips.forEach(function (c) { c.addEventListener('click', function () {
    chips.forEach(x => x.classList.remove('on')); c.classList.add('on'); chips.forEach(x=>x.setAttribute('aria-pressed',String(x===c)));
    var band = c.getAttribute('data-band');
    document.querySelectorAll('.lvl').forEach(function (l) { l.style.display = (band === 'all' || l.getAttribute('data-band') === band) ? '' : 'none'; });
    document.querySelectorAll('[data-sec]').forEach(function (s) { var any = [].slice.call(s.querySelectorAll('.lvl')).some(l => l.style.display !== 'none'); s.style.display = any ? '' : 'none'; });
  }); });
</script>
</body>
</html>
