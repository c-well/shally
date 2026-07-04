<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $sermon->title }} · Peace Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 880px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'Instrument Sans', sans-serif; font-size: clamp(26px, 4vw, 36px); font-weight: 500; line-height: 1.05; margin-bottom: 4px; }
  .sub { font-size: 13px; color: var(--ink-soft); margin-bottom: 32px; }
  .sub a { color: var(--teal); text-decoration: none; }

  .status { padding: 14px 18px; background: rgba(45,134,89,0.10); border-left: 4px solid #2d8659; border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; }

  .card {
    background: #fff; border: 1px solid var(--line);
    border-radius: 6px; padding: 22px 26px; margin-bottom: 18px;
  }
  .card h2 {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--brass); margin-bottom: 16px;
  }

  .field { margin-bottom: 16px; }
  .field label { display: block; font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
  .field input[type=text], .field input[type=number], .field textarea {
    width: 100%; padding: 11px 14px;
    border: 1px solid var(--line); border-radius: 4px;
    background: #fff; color: var(--ink);
    font: inherit; font-size: 15px;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .field textarea { min-height: 100px; resize: vertical; line-height: 1.55; font-family: 'Instrument Sans', sans-serif; font-size: 16px; }
  .field input:focus, .field textarea:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); outline: none; }

  .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .row2 { grid-template-columns: 1fr; } }

  .check-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; cursor: pointer; }
  .check-row input { width: 18px; height: 18px; accent-color: var(--teal); }
  .check-row .lbl-text { font-size: 14px; }
  .check-row small { display: block; font-size: 12px; color: var(--ink-soft); margin-top: 2px; }

  .qa-edit, .scr-edit { padding: 16px 0; border-top: 1px dashed var(--line); }
  .qa-edit:first-of-type, .scr-edit:first-of-type { border-top: 0; padding-top: 0; }

  button.primary, button.danger, button.ghost {
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    padding: 9px 18px; border-radius: 4px; cursor: pointer;
    border: 1px solid var(--teal); background: var(--teal); color: #fff;
    transition: background 0.15s;
  }
  button.primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  button.ghost { background: transparent; color: var(--teal); border-color: var(--teal); }
  button.ghost:hover { background: var(--teal); color: #fff; }
  button.danger { background: transparent; color: var(--warn); border-color: var(--warn); padding: 6px 12px; font-size: 10px; }
  button.danger:hover { background: var(--warn); color: #fff; }

  .ref-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; gap: 12px; }
  .ref-row .ref-label { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--brass); letter-spacing: 0.16em; text-transform: uppercase; min-width: 120px; }
  .ref-row .ref-text { flex: 1; color: var(--ink-soft); font-family: 'Instrument Sans', sans-serif; font-size: 14px; }

  .add-row { display: flex; gap: 8px; padding-top: 14px; border-top: 1px dashed var(--line); margin-top: 14px; }
  .add-row input { flex: 1; }

  .topic-input { display: block; width: 100%; }

  .public-link { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--teal); text-decoration: none; }
  .public-link:hover { text-decoration: underline; }

  /* Bookends readable + tappable on every device (Karlon 2026-07-04) */
  .top { padding: 24px 28px; }
  .top a { font-size: 13.5px !important; padding: 10px 12px; margin: -10px -12px; }
  .top .meta { font-size: 12.5px !important; }
  @media (max-width: 700px) { .top { padding: 16px 16px; } }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.peace.index') }}">@include('partials._arl') Peace</a>
  <span class="meta">edit sermon</span>
</header>

<main>
  <h1>{{ $sermon->title }}</h1>
  <p class="sub">
    {{ strtoupper($sermon->sermon_date->format('M j Y')) }}
    @if($sermon->speaker) · {{ $sermon->speaker }} @endif
    @if($sermon->processing_status === 'published')
      · <a href="{{ route('find-peace.show', $sermon->slug) }}" target="_blank" class="public-link">View public page @include('partials._arup')</a>
    @endif
  </p>

  @if (session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif

  {{-- ── BOUNDARY EDITOR (2026-05-30) — works against the FULL video, not the
       sliced audio. Use when the auto-detected boundaries grabbed the wrong
       span (e.g. picked the children's story instead of the sermon, or cut
       a long sermon short like Stewart's 26:30 of an actual 52:22). ──────── --}}
  @if($sermon->youtube_video_id)
  <details class="boundary-panel" style="margin-bottom:28px;padding:18px 22px;background:color-mix(in srgb, var(--brass) 5%, transparent);border:1px solid var(--line);border-radius:6px;">
    <summary style="cursor:pointer;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:var(--brass);">
      Boundary editor · current span {{ gmdate('H:i:s', $sermon->sermon_start_seconds) }} @include('partials._ar') {{ gmdate('H:i:s', $sermon->sermon_end_seconds) }} ({{ gmdate('i:s', $sermon->sermon_end_seconds - $sermon->sermon_start_seconds) }}) ▾
    </summary>

    <p style="margin:14px 0 16px;font-size:13px;color:var(--ink-soft);">
      Use this when the auto-detected span is wrong — the player below plays the <strong>full</strong> YouTube video. Scrub to find the real start/end, then click "Set IN" or "Set OUT" at the player's current time. <strong>Save + Re-slice</strong> downloads the full audio, cuts to the new span, and optionally regenerates Q&As (~$0.07 in Claude).
    </p>

    <div style="background:#000;border-radius:4px;overflow:hidden;aspect-ratio:16/9;max-width:600px;margin-bottom:18px;">
      <iframe id="boundaryYTPlayer"
        width="100%" height="100%"
        src="https://www.youtube.com/embed/{{ $sermon->youtube_video_id }}?start={{ $sermon->sermon_start_seconds }}&enablejsapi=1&modestbranding=1&rel=0"
        title="Sermon video"
        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen
        frameborder="0"></iframe>
    </div>

    <form method="POST" action="{{ route('admin.peace.boundaries', $sermon->slug) }}" id="boundaryForm">
      @csrf

      {{-- IN POINT --}}
      <div style="display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;margin-bottom:14px;">
        <label style="font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);min-width:60px;">IN</label>
        <input type="text" name="start_human" id="startHuman" value="{{ gmdate('H:i:s', $sermon->sermon_start_seconds) }}"
               pattern="\d+:\d{2}:\d{2}|\d+:\d{2}"
               style="padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:4px;font-family:'JetBrains Mono',monospace;font-size:14px;width:120px;"
               required>
        <input type="hidden" name="start" id="startSec" value="{{ $sermon->sermon_start_seconds }}">
        <div class="jog-buttons" style="display:flex;gap:4px;">
          <button type="button" class="jog" data-target="start" data-delta="-30">−30s</button>
          <button type="button" class="jog" data-target="start" data-delta="-5">−5s</button>
          <button type="button" class="jog set-current" data-target="start">↑ set</button>
          <button type="button" class="jog" data-target="start" data-delta="5">+5s</button>
          <button type="button" class="jog" data-target="start" data-delta="30">+30s</button>
          <button type="button" class="jog jump" data-target="start" title="Jump player to IN">▶</button>
        </div>
      </div>

      {{-- OUT POINT --}}
      <div style="display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;margin-bottom:14px;">
        <label style="font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);min-width:60px;">OUT</label>
        <input type="text" name="end_human" id="endHuman" value="{{ gmdate('H:i:s', $sermon->sermon_end_seconds) }}"
               pattern="\d+:\d{2}:\d{2}|\d+:\d{2}"
               style="padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:4px;font-family:'JetBrains Mono',monospace;font-size:14px;width:120px;"
               required>
        <input type="hidden" name="end" id="endSec" value="{{ $sermon->sermon_end_seconds }}">
        <div class="jog-buttons" style="display:flex;gap:4px;">
          <button type="button" class="jog" data-target="end" data-delta="-30">−30s</button>
          <button type="button" class="jog" data-target="end" data-delta="-5">−5s</button>
          <button type="button" class="jog set-current" data-target="end">↑ set</button>
          <button type="button" class="jog" data-target="end" data-delta="5">+5s</button>
          <button type="button" class="jog" data-target="end" data-delta="30">+30s</button>
          <button type="button" class="jog jump" data-target="end" title="Jump player to OUT">▶</button>
        </div>
      </div>

      {{-- LIVE CAPTION (synced to player position) --}}
      <div id="liveCaption" style="margin:18px 0;padding:10px 14px;background:rgba(0,0,0,0.04);border-radius:4px;min-height:38px;font-family:'Instrument Sans', sans-serif;font-style:italic;color:var(--ink-soft);font-size:14px;">
        <span style="font-family:'JetBrains Mono',monospace;font-style:normal;font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--brass);margin-right:8px;">caption @ <span id="capTime">--:--:--</span></span>
        <span id="capText">click play above to see live captions</span>
      </div>

      {{-- ACTIONS --}}
      <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap;">
        <button type="button" id="playRangeBtn" style="padding:9px 16px;background:#fff;color:var(--brass);border:1px solid var(--brass);border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:600;letter-spacing:0.16em;text-transform:uppercase;cursor:pointer;">
          ▸ Play IN@include('partials._ar')OUT
        </button>
        <button type="submit" name="action" value="save_only"
                onclick="event.preventDefault();var b=this;shConfirm('Save boundaries only? Audio + content stay as-is (you can re-slice later).',{danger:true}).then(function(o){if(o)b.form.requestSubmit(b);});"
                style="padding:9px 16px;background:#fff;color:var(--ink);border:1px solid var(--line);border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:600;letter-spacing:0.16em;text-transform:uppercase;cursor:pointer;">
          Save boundaries
        </button>
        <button type="submit" name="action" value="save_and_reslice"
                onclick="event.preventDefault();var b=this;shConfirm('Save + re-slice audio from full YouTube video? Downloads ~100MB and takes ~2 minutes. Audio file will be replaced.',{danger:true}).then(function(o){if(o)b.form.requestSubmit(b);});"
                style="padding:9px 16px;background:var(--teal);color:#fff;border:0;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;cursor:pointer;">
          Save + Re-slice audio
        </button>
        <button type="submit" name="action" value="save_reslice_regen"
                onclick="event.preventDefault();var b=this;shConfirm('Save + re-slice + regenerate Q&As/heart-line? Costs ~$0.07 in Claude API. Old Q&As will be deleted. Takes ~3 minutes total.',{danger:true}).then(function(o){if(o)b.form.requestSubmit(b);});"
                style="padding:9px 16px;background:var(--brass);color:#fff;border:0;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;cursor:pointer;">
          Save + Re-slice + Regenerate ($0.07)
        </button>
      </div>
    </form>
  </details>

  <style>
    .jog-buttons .jog {
      padding: 6px 10px;
      background: #fff; border: 1px solid var(--line);
      border-radius: 3px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      cursor: pointer;
      transition: all 0.15s;
    }
    .jog-buttons .jog:hover { background: var(--brass); color: #fff; border-color: var(--brass); }
    .jog-buttons .jog.set-current { background: rgba(176,138,62,0.15); color: var(--brass); border-color: var(--brass); font-weight: 700; }
    .jog-buttons .jog.jump { background: var(--teal); color: #fff; border-color: var(--teal); }
  </style>

  <script src="https://www.youtube.com/iframe_api"></script>
  <script>
  (function () {
    var captions = @json($captionEvents ?? []);
    var player;
    var captionCheckTimer;

    window.onYouTubeIframeAPIReady = function () {
      player = new YT.Player('boundaryYTPlayer', {
        events: {
          'onReady': function () { startCaptionTimer(); }
        }
      });
    };

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function fmt(sec) {
      sec = Math.max(0, Math.floor(sec));
      var h = Math.floor(sec / 3600);
      var m = Math.floor((sec % 3600) / 60);
      var s = sec % 60;
      return pad(h) + ':' + pad(m) + ':' + pad(s);
    }
    function parse(str) {
      var parts = str.split(':').map(function(x){ return parseInt(x, 10) || 0; });
      if (parts.length === 3) return parts[0] * 3600 + parts[1] * 60 + parts[2];
      if (parts.length === 2) return parts[0] * 60 + parts[1];
      return parts[0] || 0;
    }

    function getCurrent() {
      if (!player || !player.getCurrentTime) return null;
      return Math.floor(player.getCurrentTime());
    }
    function setValue(target, sec) {
      sec = Math.max(0, Math.floor(sec));
      document.getElementById(target + 'Sec').value = sec;
      document.getElementById(target + 'Human').value = fmt(sec);
    }
    function getValue(target) {
      return parseInt(document.getElementById(target + 'Sec').value, 10) || 0;
    }

    // Jog buttons
    document.querySelectorAll('.jog').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.dataset.target;
        if (btn.classList.contains('set-current')) {
          var c = getCurrent();
          if (c !== null) setValue(target, c);
        } else if (btn.classList.contains('jump')) {
          if (player && player.seekTo) {
            player.seekTo(getValue(target), true);
            player.playVideo();
          }
        } else {
          var delta = parseInt(btn.dataset.delta, 10);
          setValue(target, getValue(target) + delta);
        }
      });
    });

    // Text input syncing
    ['start', 'end'].forEach(function (target) {
      document.getElementById(target + 'Human').addEventListener('change', function () {
        setValue(target, parse(this.value));
      });
    });

    // Play IN→OUT
    document.getElementById('playRangeBtn').addEventListener('click', function () {
      if (!player) return;
      var start = getValue('start');
      var end = getValue('end');
      player.seekTo(start, true);
      player.playVideo();
      var checkEnd = setInterval(function () {
        if (!player || !player.getCurrentTime) { clearInterval(checkEnd); return; }
        if (player.getCurrentTime() >= end) {
          player.pauseVideo();
          clearInterval(checkEnd);
        }
      }, 500);
    });

    // Caption sync — find caption text at current player time
    function startCaptionTimer() {
      captionCheckTimer = setInterval(function () {
        if (!player || !player.getCurrentTime) return;
        var t = player.getCurrentTime();
        document.getElementById('capTime').textContent = fmt(t);
        if (!captions.length) return;
        // Linear scan — fine for ~2000 caption events
        var match = null;
        for (var i = 0; i < captions.length; i++) {
          if (captions[i].t <= t && (i === captions.length - 1 || captions[i+1].t > t)) {
            match = captions[i];
            break;
          }
        }
        if (match) document.getElementById('capText').textContent = match.text || '…';
      }, 500);
    }

    // Form validation
    document.getElementById('boundaryForm').addEventListener('submit', function (e) {
      var s = getValue('start');
      var ed = getValue('end');
      if (ed <= s) {
        e.preventDefault();
        shToast('OUT must be after IN.');
      }
    });
  })();
  </script>
  @endif

  {{-- ── Audio trim — re-cut against the existing trimmed file ─────────── --}}
  @if($sermon->audio_url)
  @php
    $dh = !empty($audioDur) ? floor($audioDur/60).':'.str_pad($audioDur%60,2,'0',STR_PAD_LEFT) : '--';
  @endphp
  <details class="trim-panel" style="margin-bottom:28px;padding:18px 22px;background:color-mix(in srgb, var(--teal) 4%, transparent);border:1px solid var(--line);border-radius:6px;">
    <summary style="cursor:pointer;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:var(--teal);">
      Audio · current length {{ $dh }} — tighten boundaries ▾
    </summary>
    <p style="margin:14px 0 16px;font-size:13px;color:var(--ink-soft);">
      Offsets are relative to the <em>current</em> audio file (the one already trimmed by the pipeline). Use <strong>MM:SS</strong> format. End must be after start. A 3-second fade-out is always applied. To extend <em>past</em> the current end, leave it for Claude — that needs the full source.
    </p>
    {{-- Audition deck: HEAR the message, mark start/end from the playhead (Karlon 2026-07-04:
         "how am I supposed to know where to come in and end — I can't hear the audio") --}}
    <div class="audition">
      <button type="button" class="aud-pp" id="audPP" aria-label="Play"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></button>
      <div class="aud-track">
        <div class="aud-bar" id="audBar"><div class="aud-prog" id="audProg"></div></div>
        <div class="aud-time"><span id="audCur">0:00</span><span>{{ $dh }}</span></div>
      </div>
      <div class="aud-marks">
        <button type="button" class="aud-mark" id="markStart">Playhead → Start</button>
        <button type="button" class="aud-mark" id="markEnd">Playhead → End</button>
      </div>
      <div class="aud-skips">
        <button type="button" class="aud-skip" data-d="-30">-30s</button>
        <button type="button" class="aud-skip" data-d="-5">-5s</button>
        <button type="button" class="aud-skip" data-d="5">+5s</button>
        <button type="button" class="aud-skip" data-d="30">+30s</button>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.peace.trim', $sermon->slug) }}" data-confirm="Re-trim this audio? The original boundaries will be lost (backup is kept server-side).">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:14px;align-items:end;">
        <label style="display:block;">
          <span style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);font-weight:600;">Start (MM:SS)</span>
          <input type="text" name="start" pattern="[0-9]+:[0-5][0-9]" placeholder="0:00" required
                 style="width:100%;padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:4px;font-family:'JetBrains Mono',monospace;font-size:14px;">
        </label>
        <label style="display:block;">
          <span style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);font-weight:600;">End (MM:SS)</span>
          <input type="text" name="end" pattern="[0-9]+:[0-5][0-9]" placeholder="{{ $dh }}" required
                 style="width:100%;padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:4px;font-family:'JetBrains Mono',monospace;font-size:14px;">
        </label>
        <button type="submit" style="padding:9px 18px;background:var(--teal);color:#fff;border:0;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;cursor:pointer;">Re-trim @include('partials._ar')</button>
      </div>
      <div style="margin-top:14px;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--ink);flex-wrap:wrap;">
        <span style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);font-weight:600;">Compressor</span>
        <select name="compressor" style="padding:6px 10px;background:#fff;border:1px solid var(--line);border-radius:4px;font-family:inherit;font-size:13px;">
          <option value="none">None — preserve dynamics</option>
          <option value="simple">Simple — gentle level lift</option>
          <option value="fairchild" selected>Fairchild Vari-Mu — warmth, program-dependent</option>
          <option value="ssl">SSL 4000 G Bus — subtle glue (~2-4 dB GR)</option>
        </select>
        <button type="submit"
                formaction="{{ route('admin.peace.recompress', $sermon->slug) }}"
                formnovalidate
                onclick="event.preventDefault();var b=this;shConfirm('Re-encode the current audio with this compressor? Boundaries stay the same. Old file is replaced.',{danger:true}).then(function(o){if(o)b.form.requestSubmit(b);});"
                style="margin-left:8px;padding:7px 14px;background:#fff;color:var(--teal);border:1px solid var(--teal);border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:10px;font-weight:600;letter-spacing:0.16em;text-transform:uppercase;cursor:pointer;">
          Apply compressor only @include('partials._ar')
        </button>
      </div>
      <p style="margin:10px 0 0;font-size:11px;color:var(--ink-soft);opacity:0.8;">Example: <code style="font-family:'JetBrains Mono',monospace;">5:15</code> @include('partials._ar') <code style="font-family:'JetBrains Mono',monospace;">35:50</code>. Result will be ~30:35 with a clean fade-out tail.</p>
    </form>
  </details>
  @endif

  {{-- ── Core fields ──────────────────────────────────────────── --}}
  <form method="POST" action="{{ route('admin.peace.update', $sermon->slug) }}">
    @csrf
    <div class="card">
      <h2>Sermon details</h2>

      <div class="field">
        <label>Title (public — seeker-friendly)</label>
        <input type="text" name="title" value="{{ old('title', $sermon->title) }}" required maxlength="255">
      </div>

      <div class="row2">
        <div class="field">
          <label>Speaker</label>
          <input type="text" name="speaker" value="{{ old('speaker', $sermon->speaker) }}" maxlength="255">
        </div>
        <div class="field">
          <label>Topics (comma-separated)</label>
          <input type="text" name="topics" class="topic-input" value="{{ old('topics', $sermon->topics->pluck('name')->implode(', ')) }}" placeholder="discouragement, prayer, doubt">
        </div>
      </div>

      <div class="field">
        <label>Heart line (1 sentence, the soul of the page)</label>
        <textarea name="heart_line" maxlength="1000" style="min-height: 70px;">{{ old('heart_line', $sermon->heart_line) }}</textarea>
      </div>

      <h2 style="margin-top: 24px;">Summary paragraphs</h2>
      @php $paras = old('summary_paragraphs', $sermon->summary_paragraphs ?? []); @endphp
      @for($i = 0; $i < max(3, count($paras)); $i++)
        <div class="field">
          <label>Paragraph {{ $i + 1 }}</label>
          <textarea name="summary_paragraphs[]" maxlength="5000">{{ $paras[$i] ?? '' }}</textarea>
        </div>
      @endfor

      <h2 style="margin-top: 24px;">Visibility flags</h2>
      <label class="check-row">
        <input type="hidden" name="is_offsite" value="0">
        <input type="checkbox" name="is_offsite" value="1" @checked($sermon->is_offsite ?? false)>
        <span class="lbl-text">Offsite stream<small>Camp meeting, conference, visiting venue — won't show on /find-peace</small></span>
      </label>
      <label class="check-row">
        <input type="hidden" name="is_no_sermon" value="0">
        <input type="checkbox" name="is_no_sermon" value="1" @checked($sermon->is_no_sermon ?? false)>
        <span class="lbl-text">No sermon<small>Prayer service / special program with no teaching segment — kept in DB but never published</small></span>
      </label>

      <div style="margin-top: 18px; text-align: right;">
        <button type="submit" class="primary">Save sermon</button>
      </div>
    </div>
  </form>

  {{-- ── Q&As — edit / delete each, add new at end ─────────────── --}}
  <div class="card">
    <h2>Q&A pairs ({{ $sermon->qaPairs->count() }})</h2>

    @foreach($sermon->qaPairs as $qa)
      <form method="POST" action="{{ route('admin.peace.qa.update', [$sermon->slug, $qa->id]) }}" class="qa-edit">
        @csrf @method('PATCH')
        <div class="field">
          <label>Question (#{{ $qa->id }} · order {{ $qa->display_order }})</label>
          <input type="text" name="question" value="{{ $qa->question }}" required maxlength="1000">
        </div>
        <div class="field">
          <label>Answer</label>
          <textarea name="answer" required maxlength="5000">{{ $qa->answer }}</textarea>
        </div>
        <div class="row2">
          <div class="field">
            <label>Display order</label>
            <input type="number" name="display_order" value="{{ $qa->display_order }}" min="0">
          </div>
          <div style="display:flex; align-items:flex-end; gap: 10px; padding-bottom: 4px;">
            <button type="submit" class="ghost">Save Q&A</button>
          </div>
        </div>
      </form>
      <form method="POST" action="{{ route('admin.peace.qa.destroy', [$sermon->slug, $qa->id]) }}" style="text-align:right; margin-top:-10px;" data-confirm="Remove this Q&A?">
        @csrf @method('DELETE')
        <button type="submit" class="danger">Delete Q&A #{{ $qa->id }}</button>
      </form>
    @endforeach

    <form method="POST" action="{{ route('admin.peace.qa.add', $sermon->slug) }}" style="border-top: 2px solid var(--line); padding-top: 18px; margin-top: 24px;">
      @csrf
      <h2>Add new Q&A</h2>
      <div class="field">
        <label>Question</label>
        <input type="text" name="question" required maxlength="1000" placeholder="What if my past trauma keeps pulling me back into old patterns?">
      </div>
      <div class="field">
        <label>Answer</label>
        <textarea name="answer" required maxlength="5000" placeholder="Pastoral, 2-4 sentences. Name the feeling, anchor in scripture, end with hope."></textarea>
      </div>
      <div style="text-align: right;">
        <button type="submit" class="primary">Add Q&A</button>
      </div>
    </form>
  </div>

  {{-- ── Scriptures — list, delete, add new (validated against bible-api) ─ --}}
  <div class="card">
    <h2>Scriptures ({{ $sermon->scriptures->count() }})</h2>

    @foreach($sermon->scriptures as $scr)
      <div class="ref-row">
        <span class="ref-label">{{ $scr->reference_display }} · {{ $scr->translation }}</span>
        <span class="ref-text">{{ \Illuminate\Support\Str::limit($scr->verse_text, 100) }}</span>
        <form method="POST" action="{{ route('admin.peace.scripture.destroy', [$sermon->slug, $scr->id]) }}" data-confirm="Remove this scripture?">
          @csrf @method('DELETE')
          <button type="submit" class="danger">Remove</button>
        </form>
      </div>
    @endforeach

    <form method="POST" action="{{ route('admin.peace.scripture.add', $sermon->slug) }}" class="add-row">
      @csrf
      <input type="text" name="reference_display" required maxlength="100" placeholder='e.g., "1 Samuel 30:6" or "Acts 2"'>
      <button type="submit" class="primary">Add &amp; validate</button>
    </form>
    <p style="font-size: 12px; color: var(--ink-soft); margin-top: 10px;">Reference is checked against bible-api.com — invalid refs are rejected.</p>
  </div>

</main>
@include('partials._confirm')

<style>
  .audition { display: grid; grid-template-columns: auto 1fr; gap: 12px 14px; align-items: center; background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 14px 16px; margin: 14px 0 18px; }
  .aud-pp { width: 46px; height: 46px; border-radius: 50%; border: 0; background: var(--teal); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
  .aud-pp svg { width: 18px; height: 18px; }
  .aud-bar { height: 8px; background: color-mix(in srgb, var(--ink) 10%, transparent); border-radius: 999px; cursor: pointer; position: relative; overflow: hidden; }
  .aud-prog { position: absolute; inset: 0 100% 0 0; background: var(--teal); }
  .aud-time { display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink-soft); margin-top: 6px; }
  .aud-marks, .aud-skips { grid-column: 1 / -1; display: flex; gap: 8px; flex-wrap: wrap; }
  .aud-mark { flex: 1; min-width: 140px; padding: 11px 10px; border: 1px solid var(--teal); background: color-mix(in srgb, var(--teal) 7%, #fff); color: var(--teal); border-radius: 7px; font: 700 11px 'Instrument Sans', sans-serif; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer; }
  .aud-mark:hover { background: var(--teal); color: #fff; }
  .aud-skip { padding: 9px 14px; border: 1px solid var(--line); background: #fff; color: var(--ink-soft); border-radius: 7px; font: 600 12px 'JetBrains Mono', monospace; cursor: pointer; }
  .aud-skip:hover { border-color: var(--teal); color: var(--teal); }
</style>
<script>
(function () {
  var wrap = document.querySelector('.audition'); if (!wrap) return;
  var audio = new Audio(); audio.preload = 'none'; audio.src = @json($sermon->audio_url);
  var pp = document.getElementById('audPP'), bar = document.getElementById('audBar'),
      prog = document.getElementById('audProg'), cur = document.getElementById('audCur');
  var PLAY = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
  var PAUSE = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>';
  function fmt(t) { t = Math.floor(t || 0); return Math.floor(t / 60) + ':' + ('0' + (t % 60)).slice(-2); }
  audio.addEventListener('timeupdate', function () {
    if (audio.duration) prog.style.right = (100 - audio.currentTime / audio.duration * 100) + '%';
    cur.textContent = fmt(audio.currentTime);
  });
  audio.addEventListener('ended', function () { pp.innerHTML = PLAY; });
  pp.addEventListener('click', function () {
    if (audio.paused) { audio.play(); pp.innerHTML = PAUSE; } else { audio.pause(); pp.innerHTML = PLAY; }
  });
  bar.addEventListener('click', function (e) {
    if (!audio.duration) return;
    var r = bar.getBoundingClientRect(); audio.currentTime = (e.clientX - r.left) / r.width * audio.duration;
  });
  document.querySelectorAll('.aud-skip').forEach(function (b) {
    b.addEventListener('click', function () { audio.currentTime = Math.max(0, audio.currentTime + parseInt(b.dataset.d, 10)); });
  });
  var trimForm = document.querySelector('form[action*="/trim"]');
  document.getElementById('markStart').addEventListener('click', function () {
    trimForm.querySelector('input[name="start"]').value = fmt(audio.currentTime); this.textContent = 'Start = ' + fmt(audio.currentTime);
  });
  document.getElementById('markEnd').addEventListener('click', function () {
    trimForm.querySelector('input[name="end"]').value = fmt(audio.currentTime); this.textContent = 'End = ' + fmt(audio.currentTime);
  });
})();
</script>
</body>
</html>
