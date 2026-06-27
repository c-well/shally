<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
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
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,500;1,400&display=swap" rel="stylesheet">
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
  .chip { font-size: 13px; font-weight: 600; padding: 9px 16px; border-radius: 999px; border: 1px solid var(--line); background: #fff; color: var(--ink-soft); cursor: pointer; }
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
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<main>
  <div class="eyebrow">For our children</div>
  <h1>Scripture Games.</h1>
  <p class="lede">Not just play — these bring the <b>Word of God</b> before our kids. Every game teaches a real verse, one piece at a time. Pick a verse, learn it, and earn a star.</p>
  <div class="who" id="who"></div>

  <div class="filters" id="filters">
    <button class="chip on" data-band="all">All ages</button>
    <button class="chip" data-band="little">Little (4–6)</button>
    <button class="chip" data-band="older">Kids (7–9)</button>
    <button class="chip" data-band="teens">Teens</button>
  </div>

  @php
    $byType = $levels->groupBy('game_type');
    $meta = [
      'verse_tetris' => ['Verse Tetris', '🧱', 'Real Tetris — clear lines to build the verse; questions about Jesus power you up. (Teens)'],
      'word_search'  => ['Word Search', '🔎', 'Find the hidden words, then read the whole verse.'],
      'memory_match' => ['Memory Match', '🃏', 'Flip and match the pairs to reveal the verse.'],
      'hidden_words' => ['Hidden Words', '✨', 'Tap to uncover each word of the verse.'],
    ];
  @endphp

  @foreach (['verse_tetris','word_search','memory_match','hidden_words'] as $gt)
    @if (($byType[$gt] ?? collect())->isNotEmpty())
      <div class="sec" data-sec>
        <h2>{{ $meta[$gt][1] }} {{ $meta[$gt][0] }}</h2>
        <div class="sub">{{ $meta[$gt][2] }}</div>
        <div class="grid">
          @foreach ($byType[$gt] as $lvl)
            <a class="lvl" href="{{ route('kids.play', $lvl) }}" data-band="{{ $lvl->age_band }}">
              <div class="ref">{{ $lvl->reference }}</div>
              <div class="t">{{ $lvl->title ?: $lvl->book }}</div>
              <div class="bk">{{ $lvl->book }} · {{ ['little'=>'Ages 4–6','older'=>'Ages 7–9','teens'=>'Teens'][$lvl->age_band] ?? $lvl->age_band }}</div>
              <div class="go">Play →</div>
            </a>
          @endforeach
        </div>
      </div>
    @endif
  @endforeach

  <div class="board">
    <h2>⭐️ Top learners</h2>
    <p>We're not competing — we're cheering each other on to know the Word better.</p>
    @if ($leaders->isNotEmpty())
      <ol>
        @foreach ($leaders as $p)
          <li><span class="nm">{{ $p->name }}</span> <span class="st">★ {{ $p->total_stars }}</span></li>
        @endforeach
      </ol>
    @else
      <div class="empty">Be the first — play a game and your name appears here.</div>
    @endif
  </div>
</main>

@include('partials.footer')
<script>
  // player greeting
  try { var pl = JSON.parse(localStorage.getItem('cop_kid') || 'null'); if (pl) document.getElementById('who').innerHTML = 'Welcome back, <b>' + pl.name + '</b> · ★ ' + (pl.total_stars || 0); } catch (e) {}
  // age filter
  var chips = document.querySelectorAll('.chip');
  chips.forEach(function (c) { c.addEventListener('click', function () {
    chips.forEach(x => x.classList.remove('on')); c.classList.add('on');
    var band = c.getAttribute('data-band');
    document.querySelectorAll('.lvl').forEach(function (l) { l.style.display = (band === 'all' || l.getAttribute('data-band') === band) ? '' : 'none'; });
    document.querySelectorAll('[data-sec]').forEach(function (s) { var any = [].slice.call(s.querySelectorAll('.lvl')).some(l => l.style.display !== 'none'); s.style.display = any ? '' : 'none'; });
  }); });
</script>
</body>
</html>
