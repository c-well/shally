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

  .player { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 18px 20px; margin-top: clamp(30px,5vh,44px); box-shadow: 0 1px 3px rgba(26,35,50,.04); }
  .player audio { width: 100%; display: block; }
  .player .hint { font-size: 11px; color: var(--ink-faint); margin-top: 8px; text-align: center; }

  .heart { font-family: 'Cormorant Garamond', serif; font-size: 21px; line-height: 1.55; text-align: center; color: var(--ink); margin-top: clamp(30px,5vh,42px); }
  .summary { margin-top: 26px; }
  .summary p { font-size: 16px; line-height: 1.75; color: var(--ink-soft); margin-top: 14px; }

  .refs { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 30px; }
  .ref { font-size: 12px; font-weight: 600; letter-spacing: 0.04em; color: var(--teal); border: 1px solid color-mix(in srgb, var(--teal) 30%, var(--line)); background: color-mix(in srgb, var(--teal) 5%, #fff); border-radius: 7px; padding: 7px 12px; }

  .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 36px; }
  .act { font-size: 12px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--teal); text-decoration: none; border: 1px solid var(--line); background: #fff; border-radius: 8px; padding: 12px 18px; cursor: pointer; font-family: inherit; }
  .act:hover { border-color: var(--teal); }
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
    <div class="player">
      <audio controls preload="none" src="{{ $sermon->audio_url }}"></audio>
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
  <div class="back"><a href="{{ url('/welcome') }}">← Back to the bulletin</a></div>
</main>

<script>
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
