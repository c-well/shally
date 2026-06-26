<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.seo-head', [
  'title'       => 'Messages — The Church of Peace',
  'description' => 'Listen to sermons from Shalom SDA Church in the Bronx. A growing archive of messages you can play anytime.',
  'path'        => '/messages',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  main { max-width: 760px; margin: 0 auto; padding: clamp(44px, 8vh, 96px) clamp(22px, 5vw, 34px) 120px; }
  .eyebrow { font-size: 12px; font-weight: 600; letter-spacing: 0.26em; text-transform: uppercase; color: var(--teal); margin-bottom: 18px; }
  h1 { font-size: clamp(40px, 6vw, 62px); font-weight: 700; line-height: 1.02; letter-spacing: -0.025em; }
  .lede { margin-top: 22px; font-size: 17px; line-height: 1.6; color: var(--ink-soft); max-width: 520px; }

  .msgs { margin-top: 58px; display: flex; flex-direction: column; gap: 18px; }
  .msg { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 22px 24px; }
  .msg.feat { background: color-mix(in srgb, var(--teal) 4%, #fff); border-color: color-mix(in srgb, var(--teal) 20%, var(--line)); }
  .msg .tag { font-size: 10px; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; color: var(--teal); margin-bottom: 10px; }
  .msg .t { font-family: 'IBM Plex Serif', serif; font-size: clamp(22px, 3.4vw, 28px); font-weight: 500; color: var(--ink); line-height: 1.2; }
  .msg .by { margin-top: 7px; font-size: 13px; color: var(--ink-soft); }
  .msg .heart { margin-top: 12px; font-family: 'IBM Plex Serif', serif; font-style: italic; font-size: 17px; line-height: 1.5; color: var(--ink-soft); }

  /* Audio player */
  .player { margin-top: 18px; display: flex; align-items: center; gap: 14px; }
  .pp { flex-shrink: 0; width: 48px; height: 48px; border-radius: 50%; border: 0; background: var(--teal); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .15s; }
  .pp:hover { background: var(--teal-dark); }
  .pp svg { width: 20px; height: 20px; }
  .track { flex: 1; min-width: 0; }
  .bar { height: 6px; background: color-mix(in srgb, var(--ink) 10%, transparent); border-radius: 999px; cursor: pointer; position: relative; overflow: hidden; }
  .prog { position: absolute; inset: 0 100% 0 0; background: var(--teal); border-radius: 999px; }
  .time { margin-top: 7px; font-family: 'IBM Plex Sans', monospace; font-size: 12px; color: var(--ink-soft); display: flex; justify-content: space-between; }

  .empty { margin-top: 50px; padding: 40px; text-align: center; border: 1px dashed var(--line); border-radius: 12px; color: var(--ink-soft); font-family: 'IBM Plex Serif', serif; font-style: italic; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="eyebrow">The Church of Peace</div>
  <h1>Messages.</h1>
  <p class="lede">Sermons from our pulpit, ready to play anytime. Catch one you missed, or revisit a word that stayed with you.</p>

  @if ($messages->isEmpty())
    <div class="empty">New messages will appear here after each service.</div>
  @else
    <div class="msgs">
      @foreach ($messages as $i => $m)
        @php
          $dur = $m->audio_duration_seconds ? sprintf('%d:%02d', intdiv($m->audio_duration_seconds, 60), $m->audio_duration_seconds % 60) : '';
        @endphp
        <div class="msg {{ $i === 0 ? 'feat' : '' }}">
          @if ($i === 0)<div class="tag">Latest message</div>@endif
          <div class="t">{{ $m->title }}</div>
          <div class="by">{{ $m->speaker ? $m->speaker . ' · ' : '' }}{{ optional($m->sermon_date)->format('F j, Y') }}</div>
          @if ($m->heart_line)<div class="heart">“{{ $m->heart_line }}”</div>@endif
          <div class="player" data-audio="{{ $m->audio_url }}">
            <button class="pp" aria-label="Play"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></button>
            <div class="track">
              <div class="bar"><div class="prog"></div></div>
              <div class="time"><span class="cur">0:00</span><span class="dur">{{ $dur }}</span></div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</main>

@include('partials.footer')
<script>
(function () {
  var current = null; // the currently-playing <audio>
  var PLAY = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
  var PAUSE = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>';
  function fmt(s) { s = Math.floor(s || 0); return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2); }

  document.querySelectorAll('.player').forEach(function (p) {
    var btn = p.querySelector('.pp'), bar = p.querySelector('.bar'), prog = p.querySelector('.prog'),
        cur = p.querySelector('.cur'), dur = p.querySelector('.dur'), audio = null;

    function ensure() {
      if (audio) return audio;
      audio = new Audio(); audio.preload = 'none'; audio.src = p.getAttribute('data-audio');
      audio.addEventListener('timeupdate', function () {
        if (audio.duration) prog.style.right = (100 - (audio.currentTime / audio.duration) * 100) + '%';
        cur.textContent = fmt(audio.currentTime);
      });
      audio.addEventListener('loadedmetadata', function () { if (audio.duration) dur.textContent = fmt(audio.duration); });
      audio.addEventListener('ended', function () { btn.innerHTML = PLAY; prog.style.right = '100%'; });
      return audio;
    }
    btn.addEventListener('click', function () {
      ensure();
      if (audio.paused) {
        if (current && current !== audio) { current.pause(); current._btn.innerHTML = PLAY; }
        audio._btn = btn; current = audio; audio.play(); btn.innerHTML = PAUSE;
      } else { audio.pause(); btn.innerHTML = PLAY; }
    });
    bar.addEventListener('click', function (e) {
      ensure(); if (!audio.duration) return;
      var r = bar.getBoundingClientRect(); audio.currentTime = ((e.clientX - r.left) / r.width) * audio.duration;
    });
  });
})();
</script>
</body>
</html>
