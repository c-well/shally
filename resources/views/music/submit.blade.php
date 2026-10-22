<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>{{ $day->title }} — send your song · The Church of Peace</title>
<meta name="description" content="{{ $day->service_date->format('l, F j') }} · The Church of Peace. Upload your track, paste a YouTube link, or tell us you are bringing it.">
<meta property="og:type" content="website">
<meta property="og:site_name" content="The Church of Peace">
<meta property="og:url" content="{{ $day->link() }}">
<meta property="og:title" content="{{ trim(mb_strtolower($day->title)) === 'music day' ? 'Music Day' : 'Music Day — ' . $day->title }}">
<meta property="og:description" content="{{ $day->service_date->format('l, F j') }} · The Church of Peace. Upload your track, paste a YouTube link, or tell us you are bringing it.">
@if ($og)
<meta property="og:image" content="{{ $og }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Music Day, {{ $day->title }}, {{ $day->service_date->format('l, F j') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ $og }}">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Instrument Sans', system-ui, sans-serif; -webkit-font-smoothing: antialiased; overflow-x: clip; }
  main { width: 100%; max-width: 560px; margin: 0 auto;
         padding: clamp(44px,8vh,80px) calc(20px + env(safe-area-inset-right)) 110px calc(20px + env(safe-area-inset-left)); }
  .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px,8vw,50px); font-weight: 500; text-align: center; margin-top: 12px; line-height: 1.05; text-wrap: balance; }
  .when { text-align: center; margin-top: 10px; font-size: 14px; color: var(--ink-soft); }
  .lede { text-align: center; font-size: 15px; color: var(--ink-soft); margin: 16px auto 0; max-width: 420px; line-height: 1.65; }
  form { margin-top: 34px; }
  label.f { display: block; margin-top: 20px; }
  label.f > span, .legend { display: block; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 8px; }
  label.f small, .legend small { font-weight: 500; letter-spacing: 0; text-transform: none; color: var(--ink-faint, #6b7280); }
  input[type=text], input[type=url] { width: 100%; font: inherit; font-size: 16px; padding: 14px 15px; border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink); }
  input:focus { outline: none; border-color: var(--teal); }

  .how { margin-top: 26px; }
  .opts { display: grid; gap: 10px; }
  .opt { position: relative; display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 15px 16px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
  .opt input { position: absolute; opacity: 0; pointer-events: none; }
  .opt .ic { width: 38px; height: 38px; flex-shrink: 0; display: grid; place-items: center; border-radius: 10px; background: color-mix(in srgb, var(--teal) 9%, transparent); color: var(--teal); }
  .opt b { display: block; font-size: 15px; font-weight: 600; }
  .opt small { display: block; color: var(--ink-soft); margin-top: 2px; font-size: 12.5px; line-height: 1.45; }
  .opt:has(input:checked) { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 14%, transparent); }
  .opt:has(input:focus-visible) { outline: 2px solid var(--teal); outline-offset: 2px; }

  .reveal { display: none; margin-top: 14px; }
  .reveal.on { display: block; }
  .file { display: flex; align-items: center; gap: 12px; padding: 14px 15px; border: 1.5px dashed var(--line); border-radius: 10px; background: #fff; cursor: pointer; }
  .file input { position: absolute; opacity: 0; width: 1px; height: 1px; }
  .file span { font-size: 14.5px; color: var(--ink-soft); min-width: 0; overflow-wrap: anywhere; }
  .file.has span { color: var(--ink); font-weight: 600; }
  .seg { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .seg label { display: block; text-align: center; padding: 13px; border: 1px solid var(--line); border-radius: 10px; background: #fff; font-weight: 600; font-size: 15px; cursor: pointer; }
  .seg input { position: absolute; opacity: 0; }
  .seg label:has(input:checked) { border-color: var(--teal); color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 14%, transparent); }
  .hint { font-size: 12.5px; color: var(--ink-soft); margin-top: 8px; line-height: 1.5; }

  .send { margin-top: 30px; width: 100%; font: 700 13px 'Instrument Sans'; letter-spacing: 0.12em; text-transform: uppercase; color: #fff; background: var(--teal); border: 0; border-radius: 10px; padding: 17px; cursor: pointer; }
  .send:disabled { opacity: .6; cursor: wait; }
  .err { color: #a33d3d; font-size: 13px; margin-top: 8px; }
  .errs { background: #fff; border: 1px solid #e3b7b2; border-radius: 10px; padding: 12px 14px; margin-top: 24px; color: #a33d3d; font-size: 14px; line-height: 1.5; }
  .thanks, .closed { text-align: center; background: #fff; border: 1px solid color-mix(in srgb, var(--teal) 25%, var(--line)); border-radius: 14px; padding: 38px 24px; margin-top: 34px; }
  .thanks .big, .closed .big { font-family: 'Cormorant Garamond', serif; font-size: 30px; }
  .thanks p, .closed p { color: var(--ink-soft); margin-top: 10px; font-size: 14.5px; line-height: 1.65; }
  .again { display: inline-block; margin-top: 18px; color: var(--teal); font-weight: 600; font-size: 14px; text-decoration: none; }
  /*
   * Preview player — the same player André uses, so a singer hears exactly
   * what the music team will get before sending it. Purple, lilac inside.
   */
  .mp {
    --mp-accent: #5b2a86; --mp-accent-bright: #48206c; --mp-bg: #f3edf8;
    --mp-border: color-mix(in srgb, #5b2a86 16%, transparent);
    --mp-track: color-mix(in srgb, #5b2a86 16%, transparent);
    --mp-buffer: color-mix(in srgb, #5b2a86 18%, transparent);
    --mp-glow: color-mix(in srgb, #5b2a86 45%, transparent);
    margin-top: 12px; display: flex; align-items: center; gap: 14px;
    padding: 12px 16px 10px 12px; background: var(--mp-bg);
    border: 1px solid var(--mp-border); border-radius: 12px;
    box-shadow: 0 12px 30px -20px var(--mp-glow);
  }
  .mp[hidden] { display: none; }
  .mp audio { display: none; }
  .mp-play { width: 46px; height: 46px; flex-shrink: 0; border-radius: 999px; background: var(--mp-accent); color: #fff; border: 0; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px -8px var(--mp-glow); transition: background .15s, transform .1s; }
  .mp-play:hover { background: var(--mp-accent-bright); }
  .mp-play:active { transform: scale(0.95); }
  .mp-play svg { width: 18px; height: 18px; }
  .mp-play .i-play { margin-left: 2px; }
  .mp-play .i-pause { display: none; }
  .mp.playing .mp-play .i-play { display: none; }
  .mp.playing .mp-play .i-pause { display: block; }
  .mp-body { flex: 1; min-width: 0; }
  .mp-wrap { padding: 10px 0; cursor: pointer; position: relative; touch-action: none; }
  .mp-bar { position: relative; height: 5px; border-radius: 999px; background: var(--mp-track); }
  .mp-fill { position: absolute; inset: 0; width: 0%; background: var(--mp-accent); border-radius: 999px; }
  .mp-handle { position: absolute; top: 50%; left: 0; width: 14px; height: 14px; background: var(--mp-accent); border: 2px solid #fff; border-radius: 50%; transform: translate(-50%, -50%) scale(0); box-shadow: 0 2px 6px var(--mp-glow); transition: transform .15s; }
  .mp.playing .mp-handle, .mp.started .mp-handle { transform: translate(-50%, -50%) scale(1); }
  .mp-meta { display: flex; justify-content: space-between; gap: 12px; font-size: 12px; color: var(--ink-soft); letter-spacing: .04em; font-variant-numeric: tabular-nums; }
  .mp-hint { margin-top: 8px; font-size: 12.5px; color: var(--ink-soft); }

  /* Upload progress — a real bar, so a slow phone never looks frozen. */
  .up { margin-top: 26px; }
  .up[hidden] { display: none; }
  .up-bar { height: 8px; border-radius: 999px; background: color-mix(in srgb, var(--teal) 14%, transparent); overflow: hidden; }
  .up-fill { height: 100%; width: 0%; background: var(--teal); border-radius: 999px; transition: width .2s; }
  .up-txt { margin-top: 10px; display: flex; justify-content: space-between; gap: 12px; font-size: 13.5px; color: var(--ink-soft); font-variant-numeric: tabular-nums; }
  .up-txt b { color: var(--ink); font-weight: 600; }
  .up.done .up-fill { animation: up-pulse 1.2s ease-in-out infinite; }
  @keyframes up-pulse { 50% { opacity: .55; } }
  @media (prefers-reduced-motion: reduce) { .up.done .up-fill { animation: none; } .up-fill { transition: none; } }
  .honey { position: absolute; left: -9999px; opacity: 0; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<main>
  <div class="eyebrow">{{ $day->title }}</div>
  <h1>Send your song.</h1>
  <p class="when">{{ $day->service_date->format('l, F j') }}</p>

  @if (session('sent'))
    <div class="thanks">
      <div class="big">Got it, thank you.</div>
      @if (session('sent_method') === 'youtube')
        <p>We'll pull the track from that YouTube link ourselves. Nothing else you need to do.</p>
      @elseif (session('sent_method') === 'bringing')
        <p>We've noted that you're bringing it. Please hand it to the music team before the service.</p>
      @else
        <p>Your track is in. See you on {{ $day->service_date->format('l') }}.</p>
      @endif
      <a class="again" href="{{ route('music.show', $day->token) }}">Send another song</a>
    </div>
  @elseif (! $day->is_open)
    <div class="closed">
      <div class="big">Songs are closed.</div>
      <p>We're no longer taking songs for this day. If yours still needs to get to us, speak to André.</p>
    </div>
  @else
    <p class="lede">Tell us what you're singing and get us the track, so the music team has everything ready on the day.</p>

    @if ($errors->any())
      <div class="errs">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif

    <form method="POST" action="{{ route('music.store', $day->token) }}" enctype="multipart/form-data" id="songForm">
      @csrf
      <div class="honey" aria-hidden="true">
        <label for="form_meta_field">Leave this empty</label>
        <input type="text" id="form_meta_field" name="form_meta_field" tabindex="-1" autocomplete="one-time-code">
      </div>
      <input type="hidden" name="rendered_at" value="{{ $renderToken }}">

      <label class="f"><span>Your name</span>
        <input type="text" name="name" required maxlength="120" value="{{ old('name') }}" autocomplete="name">
      </label>
      <label class="f"><span>Song <small>— the title</small></span>
        <input type="text" name="song_title" maxlength="200" value="{{ old('song_title') }}" autocomplete="off">
      </label>

      <div class="how">
        <div class="legend">How is the track getting to us?</div>
        <div class="opts">
          <label class="opt">
            <input type="radio" name="method" value="upload" @checked(old('method', 'upload') === 'upload')>
            <span class="ic"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V3"/><path d="m7 8 5-5 5 5"/><path d="M20 15v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-4"/></svg></span>
            <span><b>Upload the track</b><small>The best option. MP3, M4A or WAV, up to 50MB.</small></span>
          </label>
          <label class="opt">
            <input type="radio" name="method" value="youtube" @checked(old('method') === 'youtube')>
            <span class="ic"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></span>
            <span><b>Paste a YouTube link</b><small>We'll take the audio from it for you.</small></span>
          </label>
          <label class="opt">
            <input type="radio" name="method" value="bringing" @checked(old('method') === 'bringing')>
            <span class="ic"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="2.5"/></svg></span>
            <span><b>I'll bring it</b><small>On a CD or a USB stick, on the day.</small></span>
          </label>
        </div>

        <div class="reveal" data-for="upload">
          <label class="file" id="fileBox">
            <input type="file" name="track" accept="audio/*,.mp3,.m4a,.wav,.aac,.flac,.aif,.aiff,.ogg">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--teal);flex-shrink:0"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
            <span id="fileName">Choose the track…</span>
          </label>
          <div class="mp" id="preview" hidden>
            <audio preload="metadata"></audio>
            <button class="mp-play" type="button" aria-label="Play your track">
              <svg class="i-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="6,4 20,12 6,20"/></svg>
              <svg class="i-pause" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
            </button>
            <div class="mp-body">
              <div class="mp-wrap" role="slider" tabindex="0" aria-label="Position in your track" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div class="mp-bar"><div class="mp-fill"></div><div class="mp-handle"></div></div>
              </div>
              <div class="mp-meta"><span class="mp-cur">0:00</span><span class="mp-dur">--:--</span></div>
            </div>
          </div>
          <p class="mp-hint" id="previewHint" hidden>Have a listen to make sure it's the right one. Tap the box above to pick a different file.</p>
        </div>
        <div class="reveal" data-for="youtube">
          <input type="url" name="source_url" inputmode="url" autocapitalize="off" autocorrect="off" spellcheck="false"
                 placeholder="https://youtu.be/…" value="{{ old('source_url') }}">
          <p class="hint">On the video, tap <b>Share</b>, then <b>Copy link</b>, and paste it here. Pick an instrumental or accompaniment version if you can find one.</p>
        </div>
        <div class="reveal" data-for="bringing">
          <div class="seg">
            <label><input type="radio" name="medium" value="cd" @checked(old('medium') === 'cd')>CD</label>
            <label><input type="radio" name="medium" value="usb" @checked(old('medium') === 'usb')>USB stick</label>
          </div>
        </div>
      </div>

      <div class="up" id="up" hidden aria-live="polite">
        <div class="up-bar"><div class="up-fill" id="upFill"></div></div>
        <div class="up-txt"><span id="upWhat"><b>Uploading</b></span><span id="upPct">0%</span></div>
      </div>

      <button type="submit" class="send" id="sendBtn">Send my song</button>
    </form>
  @endif
</main>

<script>
(function () {
  var form = document.getElementById('songForm'); if (!form) return;
  var radios = form.querySelectorAll('input[name=method]');
  function method() { return (form.querySelector('input[name=method]:checked') || {}).value; }
  function sync() {
    var v = method();
    form.querySelectorAll('.reveal').forEach(function (r) { r.classList.toggle('on', r.getAttribute('data-for') === v); });
    if (v !== 'upload' && audio) audio.pause();
  }
  radios.forEach(function (r) { r.addEventListener('change', sync); });

  /* ── Preview: hear the file before sending it ── */
  var fi = form.querySelector('input[name=track]'), box = document.getElementById('fileBox'), nm = document.getElementById('fileName');
  var pv = document.getElementById('preview'), hint = document.getElementById('previewHint');
  var audio = pv.querySelector('audio'), playBtn = pv.querySelector('.mp-play'), wrap = pv.querySelector('.mp-wrap');
  var fill = pv.querySelector('.mp-fill'), handle = pv.querySelector('.mp-handle'), cur = pv.querySelector('.mp-cur'), dur = pv.querySelector('.mp-dur');
  var url = null, MAX = 50 * 1048576;
  function fmt(t) { if (!isFinite(t) || t < 0) return '0:00'; return Math.floor(t / 60) + ':' + String(Math.floor(t % 60)).padStart(2, '0'); }
  function mb(b) { return (b / 1048576).toFixed(1) + ' MB'; }

  fi.addEventListener('change', function () {
    var f = fi.files && fi.files[0];
    audio.pause(); pv.classList.remove('playing', 'started');
    if (url) { URL.revokeObjectURL(url); url = null; }
    box.classList.toggle('has', !!f);
    if (!f) { nm.textContent = 'Choose the track…'; pv.hidden = true; hint.hidden = true; return; }
    if (!/\.(mp3|m4a|aac|wav|flac|ogg|aif|aiff)$/i.test(f.name) && !/^audio\//.test(f.type)) {
      nm.textContent = f.name + " isn't an audio file. Choose an MP3, M4A or WAV.";
      pv.hidden = true; hint.hidden = true; return;
    }
    if (f.size > MAX) {
      nm.textContent = f.name + ' is ' + mb(f.size) + '. The limit is 50 MB. Send an MP3 instead, or bring it on a USB.';
      pv.hidden = true; hint.hidden = true; return;
    }
    nm.textContent = f.name + ' (' + mb(f.size) + ')';
    url = URL.createObjectURL(f);
    audio.src = url; cur.textContent = '0:00'; dur.textContent = '--:--';
    fill.style.width = '0%'; handle.style.left = '0%';
    pv.hidden = false; hint.hidden = false;
  });
  audio.addEventListener('error', function () {
    if (!audio.getAttribute('src')) return;
    pv.hidden = true;
    hint.hidden = false;
    hint.textContent = "This phone can't play that file, but you can still send it. The music team will check it.";
  });
  playBtn.addEventListener('click', function () { audio.paused ? audio.play() : audio.pause(); });
  audio.addEventListener('play', function () { pv.classList.add('playing', 'started'); playBtn.setAttribute('aria-label', 'Pause'); });
  audio.addEventListener('pause', function () { pv.classList.remove('playing'); playBtn.setAttribute('aria-label', 'Play your track'); });
  audio.addEventListener('loadedmetadata', function () { dur.textContent = fmt(audio.duration); });
  audio.addEventListener('timeupdate', function () {
    var pc = (audio.currentTime / audio.duration) * 100 || 0;
    fill.style.width = pc + '%'; handle.style.left = pc + '%'; cur.textContent = fmt(audio.currentTime);
    wrap.setAttribute('aria-valuenow', Math.round(pc));
  });
  var scrub = false;
  function seek(x) { if (!audio.duration) return; var r = wrap.getBoundingClientRect(); var k = Math.max(0, Math.min(1, (x - r.left) / r.width)); audio.currentTime = k * audio.duration; pv.classList.add('started'); }
  wrap.addEventListener('pointerdown', function (e) { scrub = true; seek(e.clientX); if (wrap.setPointerCapture) wrap.setPointerCapture(e.pointerId); });
  wrap.addEventListener('pointermove', function (e) { if (scrub) seek(e.clientX); });
  wrap.addEventListener('pointerup', function () { scrub = false; });
  wrap.addEventListener('keydown', function (e) {
    var d = e.key === 'ArrowRight' ? 5 : e.key === 'ArrowLeft' ? -5 : 0; if (!d) return;
    e.preventDefault(); audio.currentTime = Math.max(0, Math.min(audio.duration || 0, audio.currentTime + d));
  });

  /* ── Upload with a real progress bar ── */
  var btn = document.getElementById('sendBtn'), up = document.getElementById('up'), upFill = document.getElementById('upFill');
  var upWhat = document.getElementById('upWhat'), upPct = document.getElementById('upPct');
  var busy = false;
  function guard(e) { if (busy) { e.preventDefault(); e.returnValue = ''; } }
  window.addEventListener('beforeunload', guard);

  function showErrors(list) {
    var box = form.parentNode.querySelector('.errs');
    if (!box) { box = document.createElement('div'); box.className = 'errs'; form.parentNode.insertBefore(box, form); }
    box.innerHTML = '';
    list.forEach(function (t) { var d = document.createElement('div'); d.textContent = t; box.appendChild(d); });
    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
  function reset(msg) {
    busy = false; btn.disabled = false; btn.textContent = 'Send my song';
    up.hidden = true; up.classList.remove('done');
    if (msg) showErrors([msg]);
  }

  form.addEventListener('submit', function (e) {
    if (method() !== 'upload' || !fi.files || !fi.files[0] || !window.FormData || !window.XMLHttpRequest) {
      /* A file picked earlier must not ride along when the singer chose another way. */
      if (method() !== 'upload') fi.disabled = true;
      btn.disabled = true; btn.textContent = 'Sending…';
      return;
    }
    e.preventDefault();
    if (busy) return;
    var f = fi.files[0];
    if (f.size > MAX) { showErrors(['That file is over 50 MB. Send an MP3 instead, or bring it on a USB.']); return; }

    audio.pause();
    busy = true; btn.disabled = true; btn.textContent = 'Uploading…';
    up.hidden = false; upFill.style.width = '0%'; upPct.textContent = '0%';
    upWhat.innerHTML = '<b>Uploading</b> 0 of ' + mb(f.size);

    var x = new XMLHttpRequest();
    x.open('POST', form.action);
    x.setRequestHeader('Accept', 'application/json');
    x.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    x.upload.onprogress = function (ev) {
      if (!ev.lengthComputable) return;
      var pc = Math.min(100, Math.round(ev.loaded / ev.total * 100));
      upFill.style.width = pc + '%'; upPct.textContent = pc + '%';
      upWhat.innerHTML = '<b>Uploading</b> ' + mb(Math.min(ev.loaded, f.size)) + ' of ' + mb(f.size);
      if (ev.loaded >= ev.total) { up.classList.add('done'); upWhat.innerHTML = '<b>Almost done</b> saving your track'; upPct.textContent = ''; }
    };
    x.onload = function () {
      var d = null; try { d = JSON.parse(x.responseText); } catch (err) {}
      if (x.status >= 200 && x.status < 300 && d && d.ok) { busy = false; location.assign(d.redirect); return; }
      if (x.status === 422 && d && d.errors) {
        var list = []; Object.keys(d.errors).forEach(function (k) { list = list.concat(d.errors[k]); });
        reset(); showErrors(list); return;
      }
      if (x.status === 419) { reset('This page was open a long time. Refresh it and send your song again.'); return; }
      if (x.status === 413) { reset('That file is too big to send. Send an MP3 instead, or bring it on a USB.'); return; }
      if (x.status === 429) { reset('Too many songs from this phone in the last hour. Try again a bit later.'); return; }
      reset('Something went wrong sending your song. Your file is still chosen, so tap Send to try again.');
    };
    x.onerror = function () { reset('The connection dropped while sending. Your file is still chosen, so tap Send to try again.'); };
    x.send(new FormData(form));
  });

  sync();
})();
</script>
</body>
</html>
