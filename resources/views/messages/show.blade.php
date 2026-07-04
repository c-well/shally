<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $sermon->title }} — The Church of Peace</title>
{{-- Members' listening room. The seeker-facing copy of this message lives on Find
     Peace, which owns the SEO for it — canonical points there so the two never compete. --}}
<link rel="canonical" href="{{ url('/find-peace/' . $sermon->slug) }}">
<meta name="description" content="{{ $sermon->heart_line }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Instrument Sans', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
  main { max-width: 660px; margin: 0 auto; padding: clamp(48px,8vh,84px) clamp(20px,5vw,28px) 110px; }

  .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(34px,7vw,48px); font-weight: 500; letter-spacing: -0.02em; text-align: center; margin-top: 12px; line-height: 1.08; }
  .who { text-align: center; font-size: 13px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-soft); margin-top: 14px; }

  .player-card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px 22px 14px; margin-top: clamp(30px,5vh,44px); box-shadow: 0 1px 3px rgba(26,35,50,.04); }
  .player { display: flex; align-items: center; gap: 14px; }
  .pp { flex-shrink: 0; width: 52px; height: 52px; border-radius: 50%; border: 0; background: var(--teal); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background .15s; }
  .pp:hover { background: var(--teal-dark, var(--teal)); }
  .pp svg { width: 21px; height: 21px; }
  .track { flex: 1; min-width: 0; }
  .bar { height: 6px; background: color-mix(in srgb, var(--ink) 10%, transparent); border-radius: 999px; cursor: pointer; position: relative; overflow: hidden; }
  .prog { position: absolute; inset: 0 100% 0 0; background: var(--teal); border-radius: 999px; }
  .time { margin-top: 7px; font-size: 12px; color: var(--ink-soft); display: flex; justify-content: space-between; font-variant-numeric: tabular-nums; }
  .hint { font-size: 11px; color: var(--ink-faint); margin-top: 10px; text-align: center; }

  .heart { font-family: 'Cormorant Garamond', serif; font-size: 21px; line-height: 1.55; text-align: center; color: var(--ink); margin-top: clamp(30px,5vh,42px); }
  .summary { margin-top: 26px; }
  .summary p { font-size: 16px; line-height: 1.75; color: var(--ink-soft); margin-top: 14px; }

  .refs { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 30px; }
  .ref { font-size: 12px; font-weight: 600; letter-spacing: 0.04em; color: var(--teal); border: 1px solid color-mix(in srgb, var(--teal) 30%, var(--line)); background: color-mix(in srgb, var(--teal) 5%, #fff); border-radius: 7px; padding: 7px 12px; }

  .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 36px; }
  .act { font-size: 12px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--teal); text-decoration: none; border: 1px solid var(--line); background: #fff; border-radius: 8px; padding: 12px 18px; cursor: pointer; font-family: inherit; }
  .act:hover { border-color: var(--teal); }
  .words { margin-top: clamp(44px,7vh,64px); border-top: 1px solid var(--line); padding-top: 34px; }
  .words-h { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 500; text-align: center; margin-bottom: 20px; }
  .words-ok { text-align: center; font-size: 13px; font-weight: 600; color: var(--teal); background: color-mix(in srgb, var(--teal) 7%, #fff); border: 1px solid color-mix(in srgb, var(--teal) 25%, var(--line)); border-radius: 8px; padding: 10px; margin-bottom: 16px; }
  .words-form textarea { width: 100%; font: inherit; font-size: 15px; line-height: 1.6; padding: 13px 15px; border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink); resize: vertical; }
  .words-form textarea:focus { outline: none; border-color: var(--teal); }
  .words-err { font-size: 12px; color: #a33d3d; margin-top: 6px; }
  .words-send { margin-top: 10px; }
  .words-signin { text-align: center; font-size: 14px; color: var(--ink-soft); background: #fff; border: 1px dashed var(--line); border-radius: 10px; padding: 16px; }
  .words-signin a { color: var(--teal); font-weight: 600; }
  .word { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 15px 17px; margin-top: 12px; }
  .word-meta { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-faint); }
  .word-body { font-size: 15px; line-height: 1.65; color: var(--ink); margin-top: 6px; white-space: pre-line; }
  .words-none { text-align: center; font-size: 13px; color: var(--ink-faint); margin-top: 18px; font-style: italic; }
  .back { text-align: center; margin-top: 40px; font-size: 13px; }
  .back a { color: var(--ink-soft); text-decoration: none; }
  .back a:hover { color: var(--teal); }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<main>
  <div class="eyebrow">Message · {{ optional($sermon->sermon_date)->format('F j, Y') }}</div>
  <h1>{{ $sermon->title }}.</h1>
  <div class="who">{{ $sermon->speaker }}</div>

  @if ($sermon->audio_status === 'ready' && $sermon->audio_url)
    @php $durS = $sermon->audio_duration_seconds; $durFmt = $durS ? sprintf('%d:%02d', intdiv($durS, 60), $durS % 60) : ''; @endphp
    <div class="player-card">
      <div class="player" data-audio="{{ $sermon->audio_url }}">
        <button class="pp" aria-label="Play"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></button>
        <div class="track">
          <div class="bar"><div class="prog"></div></div>
          <div class="time"><span class="cur">0:00</span><span class="dur">{{ $durFmt }}</span></div>
        </div>
      </div>
      <div class="hint">The message only — song service and announcements not included.</div>
    </div>
  @endif

  @if ($sermon->heart_line)
    <p class="heart">{{ $sermon->heart_line }}</p>
  @endif

  @if (is_array($sermon->summary_paragraphs) && count($sermon->summary_paragraphs))
    <div class="summary">
      @foreach ($sermon->summary_paragraphs as $para)
        <p>{{ $para }}</p>
      @endforeach
    </div>
  @endif

  @if ($sermon->scriptures->count())
    <div class="refs">
      @foreach ($sermon->scriptures as $ref)
        <span class="ref">{{ $ref->reference_display }}</span>
      @endforeach
    </div>
  @endif

  <div class="actions">
    <button type="button" class="act" id="shareBtn"
            data-title="{{ $sermon->title }}" data-speaker="{{ $sermon->speaker }}">Share this message</button>
    <a class="act" href="{{ route('calendar') }}?v=day&d={{ optional($sermon->sermon_date)->toDateString() }}">That Sabbath on the calendar</a>
  </div>
  <div class="back"><a href="{{ route('messages') }}">← All messages</a></div>

  {{-- ── Leave a word — members only, honeypot-guarded ── --}}
  <section class="words">
    <h2 class="words-h">Words from the family</h2>
    @if (session('commented'))
      <div class="words-ok">Thank you — your word is up.</div>
    @endif
    @auth
      <form class="words-form" method="POST" action="{{ route('messages.comments.store', $sermon->slug) }}">
        @csrf
        <input type="text" name="website" value="" style="position:absolute;left:-9999px;opacity:0" tabindex="-1" autocomplete="off" aria-hidden="true">
        <textarea name="body" rows="3" maxlength="1000" required
                  placeholder="What stayed with you from this message?">{{ old('body') }}</textarea>
        @error('body')<div class="words-err">{{ $message }}</div>@enderror
        <button type="submit" class="act words-send">Leave a word</button>
      </form>
    @else
      <div class="words-signin">
        <a href="{{ route('login') }}">Sign in</a> to leave a word — quick, no password, just your email.
      </div>
    @endauth

    @forelse ($comments as $c)
      <div class="word">
        <div class="word-meta">{{ $c->user->name ?? 'A member' }} · {{ $c->created_at->diffForHumans() }}</div>
        <div class="word-body">{{ $c->body }}</div>
      </div>
    @empty
      <div class="words-none">No words yet — be the first.</div>
    @endforelse
  </section>
</main>

<script>
(function () {
  var PLAY = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
  var PAUSE = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>';
  function fmt(s) { s = Math.floor(s || 0); return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2); }
  var p = document.querySelector('.player'); if (!p) return;
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
    if (audio.paused) { audio.play(); btn.innerHTML = PAUSE; }
    else { audio.pause(); btn.innerHTML = PLAY; }
  });
  bar.addEventListener('click', function (e) {
    ensure(); if (!audio.duration) return;
    var r = bar.getBoundingClientRect(); audio.currentTime = ((e.clientX - r.left) / r.width) * audio.duration;
  });
})();
document.getElementById('shareBtn')?.addEventListener('click', async function () {
  const url = location.href;
  const text = 'Check out this message — “' + this.dataset.title + '” by ' + this.dataset.speaker + '. It spoke to me:';
  if (navigator.share) {
    try { await navigator.share({ title: this.dataset.title, text, url }); return; }
    catch (e) { if (e.name === 'AbortError') return; }
  }
  try { await navigator.clipboard.writeText(text + ' ' + url); } catch (e) {}
  const was = this.textContent; this.textContent = 'Copied — paste anywhere';
  setTimeout(() => { this.textContent = was; }, 2000);
});
</script>
@include('partials._event-tracker')
</body>
</html>
