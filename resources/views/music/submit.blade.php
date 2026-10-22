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

      <button type="submit" class="send" id="sendBtn">Send my song</button>
    </form>
  @endif
</main>

<script>
(function () {
  var form = document.getElementById('songForm'); if (!form) return;
  var radios = form.querySelectorAll('input[name=method]');
  function sync() {
    var v = (form.querySelector('input[name=method]:checked') || {}).value;
    form.querySelectorAll('.reveal').forEach(function (r) { r.classList.toggle('on', r.getAttribute('data-for') === v); });
  }
  radios.forEach(function (r) { r.addEventListener('change', sync); });
  sync();

  var fi = form.querySelector('input[name=track]'), box = document.getElementById('fileBox'), nm = document.getElementById('fileName');
  fi.addEventListener('change', function () {
    var f = fi.files && fi.files[0];
    box.classList.toggle('has', !!f);
    nm.textContent = f ? f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB)' : 'Choose the track…';
    if (f && f.size > 50 * 1048576) nm.textContent = f.name + ' is over 50MB. Send an MP3 instead, or bring it on a USB.';
  });

  form.addEventListener('submit', function () {
    var b = document.getElementById('sendBtn'); b.disabled = true;
    b.textContent = (form.querySelector('input[name=method]:checked') || {}).value === 'upload' ? 'Uploading…' : 'Sending…';
  });
})();
</script>
</body>
</html>
