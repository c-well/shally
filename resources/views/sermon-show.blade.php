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
  'title'       => $sermon->title . ' — Peace Notes · Shalom SDA',
  'description' => $sermon->scripture_ref ? "Sermon on {$sermon->scripture_ref} preached at Shalom SDA Church, Bronx." : "Sermon preached at Shalom SDA Church, Bronx.",
  'path'        => "/peace-notes/{$sermon->slug}",
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 760px; margin: 0 auto; padding: clamp(36px, 7vh, 64px) clamp(20px, 5vw, 32px) 80px; }

  /* Header — lesson-page pattern (eyebrow + non-italic Cormorant + brass meta line) */
  .head { text-align: center; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px, 6vw, 56px); font-weight: 500; line-height: 1.05; letter-spacing: -0.025em; color: var(--ink); max-width: 620px; margin: 0 auto; }
  .header-text { display: block; margin-bottom: 14px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.28em; text-transform: uppercase; color: var(--brass); }
  .date-stamp { margin-top: 14px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.28em; text-transform: uppercase; color: var(--brass); }
  .speaker { margin-top: 10px; font-family: 'Cormorant Garamond', serif; font-size: 17px; font-style: italic; color: var(--ink-soft); }

  /* Hero cover — adapts to image's natural aspect ratio (set by JS on load).
     Default 16:9 fallback. Capped at 70vh so very tall portraits don't dominate.
     Ken Burns is a 2.5% breath — gentle enough to never crop a head. */
  .hero-cover {
    margin: 0 calc(-1 * clamp(20px, 5vw, 32px)) clamp(36px, 6vh, 56px);
    aspect-ratio: 16 / 9;
    max-height: 70vh;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(3,97,122,0.10), rgba(176,141,60,0.10));
    display: flex; align-items: center; justify-content: center;
  }
  .hero-cover img {
    width: 100%; height: 100%; object-fit: cover; object-position: center top;
    animation: ken-burns 32s ease-in-out infinite;
    transform-origin: center;
    will-change: transform;
  }
  @keyframes ken-burns {
    0%, 100% { transform: scale(1.0); }
    50%      { transform: scale(1.025); }
  }
  @media (prefers-reduced-motion: reduce) { .hero-cover img { animation: none; } }
  @media (max-width: 600px) {
    .hero-cover { margin-left: -20px; margin-right: -20px; max-height: 60vh; }
  }

  /* ── Premium audio player ─────────────────────────────
     Custom-built replacement for the generic <audio controls>:
     big circular play, ⏪15s / ⏩30s skip, smooth scrub, time + speed pills, download. */
  .player {
    margin: 48px auto 0; max-width: 680px;
    padding: 28px clamp(20px, 4vw, 32px);
    background: #fff; border: 1px solid var(--line); border-radius: 14px;
    box-shadow: 0 18px 44px -22px rgba(3,97,122,0.22);
  }
  .player audio { display: none; }
  .player-controls {
    display: flex; align-items: center; justify-content: center;
    gap: clamp(18px, 4vw, 32px); margin-bottom: 24px;
  }
  .skip-btn {
    background: transparent; border: 0; cursor: pointer;
    padding: 8px;
    color: var(--ink-soft);
    display: inline-flex; flex-direction: column; align-items: center;
    gap: 2px;
    transition: color 0.15s, transform 0.1s;
  }
  .skip-btn:hover { color: var(--teal); }
  .skip-btn:active { transform: scale(0.92); }
  .skip-btn svg { width: 28px; height: 28px; }
  .skip-btn .skip-num { font-family: 'JetBrains Mono', monospace; font-size: 9px; font-weight: 600; letter-spacing: 0.06em; }
  .play-btn {
    width: 72px; height: 72px; border-radius: 999px;
    background: var(--teal); color: #fff; border: 0;
    cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px -8px rgba(3,97,122,0.55);
    transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
  }
  .play-btn:hover { background: var(--teal-dark); box-shadow: 0 10px 28px -8px rgba(3,97,122,0.65); }
  .play-btn:active { transform: scale(0.95); }
  .play-btn svg { width: 28px; height: 28px; }
  .play-btn .icon-pause { display: none; }
  .player.playing .play-btn .icon-play { display: none; }
  .player.playing .play-btn .icon-pause { display: block; }

  /* Progress bar */
  .progress-wrap { padding: 8px 0; cursor: pointer; }
  .progress-bar {
    position: relative;
    height: 5px; border-radius: 999px;
    background: rgba(26,35,50,0.10);
    overflow: visible;
  }
  .progress-buffer { position: absolute; inset: 0; width: 0%; background: rgba(3,97,122,0.18); border-radius: 999px; }
  .progress-fill { position: absolute; inset: 0; width: 0%; background: var(--teal); border-radius: 999px; }
  .progress-handle {
    position: absolute; top: 50%; left: 0;
    width: 14px; height: 14px;
    background: var(--teal); border: 2px solid #fff; border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    box-shadow: 0 2px 6px rgba(3,97,122,0.45);
    transition: transform 0.15s;
  }
  .progress-wrap:hover .progress-handle, .player.playing .progress-handle { transform: translate(-50%, -50%) scale(1); }

  .player-meta {
    margin-top: 14px;
    display: flex; align-items: center; justify-content: space-between;
    font-family: 'JetBrains Mono', monospace; font-size: 12px;
    color: var(--ink-soft); letter-spacing: 0.04em;
    gap: 16px;
  }
  .player-time { font-variant-numeric: tabular-nums; min-width: 45px; }
  .player-time.duration { text-align: right; }

  .speed-pills { display: inline-flex; gap: 4px; flex-wrap: wrap; justify-content: center; }
  .speed-pills button {
    background: transparent; border: 0; cursor: pointer;
    font-family: 'JetBrains Mono', monospace; font-size: 11px;
    color: var(--ink-soft); opacity: 0.55;
    padding: 4px 8px; border-radius: 4px;
    transition: opacity 0.15s, color 0.15s, background 0.15s;
  }
  .speed-pills button:hover { opacity: 1; color: var(--teal); }
  .speed-pills button.active { opacity: 1; color: var(--teal); background: rgba(3,97,122,0.10); font-weight: 600; }

  .player-utils {
    margin-top: 18px; padding-top: 18px;
    border-top: 1px solid rgba(26,35,50,0.06);
    display: flex; align-items: center; justify-content: space-between;
    font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 600;
    letter-spacing: 0.20em; text-transform: uppercase;
    color: var(--ink-soft);
  }
  .player-utils a { display: inline-flex; align-items: center; gap: 6px; color: var(--ink-soft); text-decoration: none; transition: color 0.15s; }
  .player-utils a:hover { color: var(--teal); }
  .player-utils svg { width: 14px; height: 14px; }
  .player-utils .util-size { opacity: 0.6; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.10em; text-transform: none; }

  @media (max-width: 600px) {
    .player { padding: 22px 18px; }
    .play-btn { width: 60px; height: 60px; }
    .play-btn svg { width: 24px; height: 24px; }
    .player-meta { font-size: 11px; }
  }

  /* Description body */
  .prose { margin: 64px auto 0; max-width: 640px; font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.7; color: var(--ink); }
  .prose p { margin-bottom: 1.2em; }
  .prose h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(24px, 3.5vw, 32px); font-weight: 500; letter-spacing: -0.02em; color: var(--ink); margin: 1.6em 0 0.5em; }
  .prose h3 { font-family: 'Cormorant Garamond', serif; font-size: clamp(20px, 2.8vw, 24px); font-weight: 500; color: var(--ink); margin: 1.4em 0 0.4em; }
  .prose blockquote { margin: 1.5em 0; padding: 14px 22px; border-left: 3px solid var(--teal); background: rgba(3,97,122,0.05); font-style: italic; color: var(--ink-soft); }
  .prose ul, .prose ol { margin: 1em 0 1em 1.4em; }
  .prose li { margin-bottom: 0.4em; }
  .prose a { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }

  /* Prev/next nav */
  .prev-next {
    margin-top: 80px; display: grid; grid-template-columns: 1fr 1fr; gap: 18px;
    padding-top: 26px; border-top: 1px solid var(--line);
  }
  .pn-card {
    display: block; padding: 22px;
    background: #fff; border: 1px solid var(--line); border-radius: 8px;
    text-decoration: none; color: var(--ink);
    transition: border-color 0.15s, transform 0.1s;
  }
  .pn-card:hover { border-color: var(--teal); transform: translateY(-2px); }
  .pn-card .lbl { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); margin-bottom: 6px; }
  .pn-card .ttl { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 500; line-height: 1.2; }
  .pn-card.next { text-align: right; }
  .pn-card.empty { opacity: 0.3; pointer-events: none; }

  .back-all { margin-top: 36px; text-align: center; }
  .back-all a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; padding-bottom: 2px; border-bottom: 1px solid transparent; transition: color 0.15s, border-color 0.15s; }
  .back-all a:hover { color: var(--teal); border-bottom-color: var(--teal); }

  footer { padding: 22px clamp(20px, 5vw, 40px) 40px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.28em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.6; }

  @media (max-width: 600px) {
    .prev-next { grid-template-columns: 1fr; }
    .pn-card.next { text-align: left; }
  }
</style>
</head>
<body>
@include("partials.admin-badge")

<header class="top">
  <a href="{{ route('peace-notes') }}">← Peace Notes</a>
  <span class="meta">sermon</span>
</header>

<main>
  @if ($sermon->coverUrl())
    <div class="hero-cover" aria-hidden="true">
      <img src="{{ $sermon->coverUrl() }}" alt="">
    </div>
  @endif

  <div class="head">
    @if ($sermon->header_text)
      <div class="header-text">{{ $sermon->header_text }}</div>
    @endif
    <div class="eyebrow">@if ($sermon->scripture_ref){{ $sermon->scripture_ref }}@else Sermon @endif</div>
    <h1>{{ $sermon->title }}</h1>
    <div class="date-stamp">{{ $sermon->preached_on->format('l · F j, Y') }}</div>
    @if ($sermon->speaker)
      <div class="speaker">— {{ $sermon->speaker }}</div>
    @endif
  </div>

  @if ($sermon->audioUrl())
    <div class="player" id="sermon-player">
      <audio id="sermon-audio" preload="metadata" src="{{ $sermon->audioUrl() }}"></audio>

      <div class="player-controls">
        <button class="skip-btn" data-seek="-15" type="button" aria-label="Back 15 seconds">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 4v4h4"/>
          </svg>
          <span class="skip-num">15s</span>
        </button>

        <button class="play-btn" id="play-btn" type="button" aria-label="Play">
          <svg class="icon-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="6,4 20,12 6,20"/></svg>
          <svg class="icon-pause" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        </button>

        <button class="skip-btn" data-seek="30" type="button" aria-label="Forward 30 seconds">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 4v4h-4"/>
          </svg>
          <span class="skip-num">30s</span>
        </button>
      </div>

      <div class="progress-wrap" id="progress-wrap" role="slider" aria-label="Audio progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" tabindex="0">
        <div class="progress-bar">
          <div class="progress-buffer" id="progress-buffer"></div>
          <div class="progress-fill" id="progress-fill"></div>
          <div class="progress-handle" id="progress-handle"></div>
        </div>
      </div>

      <div class="player-meta">
        <span class="player-time current" id="time-current">0:00</span>
        <div class="speed-pills" id="speed-pills" role="group" aria-label="Playback speed">
          <button data-speed="0.75" type="button">0.75×</button>
          <button data-speed="1" type="button" class="active">1×</button>
          <button data-speed="1.25" type="button">1.25×</button>
          <button data-speed="1.5" type="button">1.5×</button>
          <button data-speed="2" type="button">2×</button>
        </div>
        <span class="player-time duration" id="time-duration">--:--</span>
      </div>

      <div class="player-utils">
        <a href="{{ $sermon->audioUrl() }}" download>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Download
        </a>
        @if ($sermon->video_url)
          <a href="{{ $sermon->video_url }}" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polygon points="5 3 19 12 5 21 5 3"/>
            </svg>
            Watch video ↗
          </a>
        @endif
        <span class="util-size">{{ $sermon->audioSizeHuman() }}</span>
      </div>
    </div>

    <script>
    (function () {
      const audio = document.getElementById('sermon-audio');
      const player = document.getElementById('sermon-player');
      if (!audio || !player) return;

      const playBtn = document.getElementById('play-btn');
      const fill = document.getElementById('progress-fill');
      const buffer = document.getElementById('progress-buffer');
      const handle = document.getElementById('progress-handle');
      const wrap = document.getElementById('progress-wrap');
      const tCur = document.getElementById('time-current');
      const tDur = document.getElementById('time-duration');

      const fmt = (s) => {
        if (!isFinite(s)) return '--:--';
        s = Math.max(0, Math.floor(s));
        const m = Math.floor(s / 60);
        const sec = String(s % 60).padStart(2, '0');
        return m + ':' + sec;
      };

      const togglePlay = () => audio.paused ? audio.play() : audio.pause();
      playBtn.addEventListener('click', togglePlay);

      audio.addEventListener('play', () => {
        player.classList.add('playing');
        playBtn.setAttribute('aria-label', 'Pause');
      });
      audio.addEventListener('pause', () => {
        player.classList.remove('playing');
        playBtn.setAttribute('aria-label', 'Play');
      });

      audio.addEventListener('loadedmetadata', () => { tDur.textContent = fmt(audio.duration); });
      audio.addEventListener('timeupdate', () => {
        if (!audio.duration) return;
        const pct = (audio.currentTime / audio.duration) * 100;
        fill.style.width = pct + '%';
        handle.style.left = pct + '%';
        tCur.textContent = fmt(audio.currentTime);
        wrap.setAttribute('aria-valuenow', Math.round(pct));
      });
      audio.addEventListener('progress', () => {
        if (!audio.duration || !audio.buffered.length) return;
        const end = audio.buffered.end(audio.buffered.length - 1);
        buffer.style.width = (end / audio.duration) * 100 + '%';
      });

      // Skip buttons
      document.querySelectorAll('.skip-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const delta = parseFloat(btn.dataset.seek) || 0;
          audio.currentTime = Math.max(0, Math.min((audio.duration || 0), audio.currentTime + delta));
        });
      });

      // Progress scrub — click and drag
      const seekFromEvent = (e) => {
        const rect = wrap.getBoundingClientRect();
        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        const pct = Math.max(0, Math.min(1, x / rect.width));
        if (audio.duration) audio.currentTime = pct * audio.duration;
      };
      let dragging = false;
      wrap.addEventListener('pointerdown', (e) => { dragging = true; wrap.setPointerCapture(e.pointerId); seekFromEvent(e); });
      wrap.addEventListener('pointermove', (e) => { if (dragging) seekFromEvent(e); });
      wrap.addEventListener('pointerup', (e) => { dragging = false; });
      // Keyboard accessibility on the slider
      wrap.addEventListener('keydown', (e) => {
        if (!audio.duration) return;
        if (e.key === 'ArrowLeft')  { audio.currentTime = Math.max(0, audio.currentTime - 5); e.preventDefault(); }
        if (e.key === 'ArrowRight') { audio.currentTime = Math.min(audio.duration, audio.currentTime + 5); e.preventDefault(); }
        if (e.key === ' ' || e.key === 'Enter') { togglePlay(); e.preventDefault(); }
      });

      // Speed pills
      document.querySelectorAll('#speed-pills button').forEach(btn => {
        btn.addEventListener('click', () => {
          const rate = parseFloat(btn.dataset.speed);
          audio.playbackRate = rate;
          document.querySelectorAll('#speed-pills button').forEach(b => b.classList.toggle('active', b === btn));
        });
      });

      // Spacebar global toggle when not in an input
      document.addEventListener('keydown', (e) => {
        if (e.key === ' ' && !['INPUT','TEXTAREA','BUTTON'].includes(document.activeElement?.tagName)) {
          togglePlay();
          e.preventDefault();
        }
      });
    })();
    </script>
  @endif

  @if ($sermon->description_html)
    <article class="prose">
      {!! $sermon->description_html !!}
    </article>
  @endif

  <nav class="prev-next" aria-label="Sermon navigation">
    @if ($prev)
      <a href="{{ route('peace-notes.show', $prev->slug) }}" class="pn-card">
        <div class="lbl">← Previous</div>
        <div class="ttl">{{ $prev->title }}</div>
      </a>
    @else
      <span class="pn-card empty"><div class="lbl">— end —</div></span>
    @endif

    @if ($next)
      <a href="{{ route('peace-notes.show', $next->slug) }}" class="pn-card next">
        <div class="lbl">Next →</div>
        <div class="ttl">{{ $next->title }}</div>
      </a>
    @else
      <span class="pn-card empty next"><div class="lbl">— most recent —</div></span>
    @endif
  </nav>

  <div class="back-all">
    <a href="{{ route('peace-notes') }}">All sermons</a>
  </div>
</main>

@include("partials.footer")


<script>
// Sermon cover — snap container to image's natural aspect ratio so portraits
// don't crop heads and landscapes don't waste space.
(function () {
  document.querySelectorAll('.hero-cover img').forEach(function (img) {
    function fit() {
      if (!img.naturalWidth || !img.naturalHeight) return;
      var ar = img.naturalWidth / img.naturalHeight;
      ar = Math.max(0.6, Math.min(2.4, ar));
      img.parentElement.style.aspectRatio = ar.toFixed(3);
    }
    if (img.complete) fit();
    else img.addEventListener('load', fit);
  });
})();
</script>
@include('partials.search-float')
</body>
</html>
