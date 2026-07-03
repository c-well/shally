<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Sabbath School lessons — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 920px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(48px, 7vw, 72px); font-weight: 500; letter-spacing: -0.035em; line-height: 1; }
  .lede { margin-top: 22px; font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 540px; }

  .flash { margin-top: 26px; background: color-mix(in srgb, var(--brass) 10%, transparent); border-left: 3px solid var(--brass); padding: 14px 18px; border-radius: 0 4px 4px 0; font-size: 14px; line-height: 1.5; }

  .form {
    margin-top: 36px;
    padding: 24px;
    background: #fff;
    border: 1px solid var(--line); border-radius: 8px;
    display: grid; gap: 14px;
    grid-template-columns: 1fr 1fr;
  }
  .form .row { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
  .form .row.full { grid-column: 1 / -1; }
  .form .lbl { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); }
  .form input, .form textarea, .form select {
    font: inherit; font-size: 14px; padding: 10px 12px;
    border: 1px solid var(--line); border-radius: 4px; background: #fff; color: var(--ink); width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .form input:focus, .form textarea:focus, .form select:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }
  .form button {
    background: var(--teal); color: #fff; border: 1px solid var(--teal);
    padding: 12px 22px; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px;
    font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s;
  }
  .form button:hover { background: var(--teal-dark); }
  .form .actions { grid-column: 1 / -1; display: flex; justify-content: flex-end; }

  .lessons-list { margin-top: 50px; }
  .section-label { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); margin-bottom: 16px; }
  .lesson-row {
    display: grid; grid-template-columns: 70px 1fr auto auto auto auto; gap: 14px;
    align-items: center; padding: 16px 4px; border-bottom: 1px solid var(--line);
  }
  .lesson-row:last-child { border-bottom: 0; }
  .l-quarter { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: var(--ink-soft); }
  .l-theme { font-family: 'Cormorant Garamond', serif; font-size: 19px; font-weight: 500; color: var(--ink); }
  .l-dates { font-size: 12px; color: var(--ink-soft); margin-top: 2px; }
  .l-pdf {
    font-family: 'Instrument Sans', sans-serif; font-size: 10px;
    letter-spacing: 0.18em; text-transform: uppercase; font-weight: 600;
  }
  .l-pdf.has { color: var(--teal); }
  .l-pdf.no  { color: var(--ink-soft); opacity: 0.6; }

  /* Cache pill — at-a-glance "is the lesson text on disk yet?" */
  .l-cache {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: 999px;
    font-family: 'JetBrains Mono', monospace; font-size: 10px;
    letter-spacing: 0.06em; font-weight: 500;
    white-space: nowrap;
  }
  .l-cache.full   { background: color-mix(in srgb, var(--teal) 10%, transparent); color: var(--teal); }
  .l-cache.partial{ background: color-mix(in srgb, var(--brass) 12%, transparent); color: var(--brass); }
  .l-cache.empty  { background: color-mix(in srgb, var(--ink) 6%, transparent); color: var(--ink-soft); opacity: 0.7; }
  .l-cache .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

  .l-actions form, .l-sync form { display: inline; }
  .l-sync button, .l-actions button {
    background: transparent;
    border-radius: 4px; padding: 6px 12px; cursor: pointer;
    font-family: 'Instrument Sans', sans-serif; font-size: 10px;
    font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase;
    transition: all 0.15s;
  }
  .l-sync button { color: var(--teal); border: 1px solid color-mix(in srgb, var(--teal) 30%, transparent); }
  .l-sync button:hover { background: var(--teal); color: #fff; border-color: var(--teal); }
  .l-sync button:disabled { opacity: 0.5; cursor: wait; }
  .l-actions button { color: var(--ink-soft); border: 1px solid rgba(192,57,43,0.3); }
  .l-actions button:hover { background: #c0392b; color: #fff; border-color: #c0392b; }

  @media (max-width: 720px) {
    .form { grid-template-columns: 1fr; }
    .lesson-row { grid-template-columns: 1fr; gap: 10px; padding: 18px 4px; }
    .lesson-row > div:nth-child(1) { font-size: 11px; }
    .lesson-row > .l-cache, .lesson-row > .l-pdf-cell, .lesson-row > .l-sync, .lesson-row > .l-actions {
      justify-self: start;
    }
    .lesson-row .row-meta { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
  }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">sabbath school</span>
</header>

<main>
  <h1>Lessons.</h1>
  <p class="lede">The current quarter's Adult Bible Study Guide. Members see a card on the home page linking to the PDF. Update once per quarter (4× a year).</p>

  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="flash" style="background:rgba(192,57,43,0.08); border-left-color:#c0392b;">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('admin.lessons.store') }}" enctype="multipart/form-data" class="form">
    @csrf
    <div class="row">
      <label class="lbl" for="year">Year</label>
      <input id="year" type="number" name="year" required min="2020" max="2099" value="{{ old('year', date('Y')) }}">
    </div>
    <div class="row">
      <label class="lbl" for="quarter">Quarter</label>
      <select id="quarter" name="quarter" required>
        @foreach ([1,2,3,4] as $q)
          <option value="{{ $q }}" {{ old('quarter', (int)ceil(date('n') / 3)) == $q ? 'selected' : '' }}>Q{{ $q }}</option>
        @endforeach
      </select>
    </div>
    <div class="row full">
      <label class="lbl" for="theme">Theme</label>
      <input id="theme" type="text" name="theme" required maxlength="180" placeholder="e.g., Growing in a Relationship With God" value="{{ old('theme') }}">
    </div>
    <div class="row">
      <label class="lbl" for="starts_on">Starts on</label>
      <input id="starts_on" type="date" name="starts_on" required value="{{ old('starts_on') }}">
    </div>
    <div class="row">
      <label class="lbl" for="ends_on">Ends on</label>
      <input id="ends_on" type="date" name="ends_on" required value="{{ old('ends_on') }}">
    </div>
    <div class="row full">
      <label class="lbl" for="pdf_url">External PDF URL (optional)</label>
      <input id="pdf_url" type="url" name="pdf_url" maxlength="500" placeholder="https://www.fustero.es/en_2026t2.pdf" value="{{ old('pdf_url') }}">
    </div>
    <div class="row full">
      <label class="lbl" for="pdf">Upload PDF (optional — overrides external URL)</label>
      <input id="pdf" type="file" name="pdf" accept="application/pdf">
    </div>
    <div class="actions">
      <button type="submit">Save quarter @include('partials._ar')</button>
    </div>
  </form>

  <section class="lessons-list">
    <div class="section-label">{{ $lessons->count() }} quarter{{ $lessons->count() === 1 ? '' : 's' }}</div>
    @foreach ($lessons as $lesson)
      @php
        $s = $stats[$lesson->id] ?? ['days' => 0, 'total_days' => 91, 'lessons' => 0];
        $cls = $s['days'] >= $s['total_days'] ? 'full' : ($s['days'] > 0 ? 'partial' : 'empty');
      @endphp
      <div class="lesson-row">
        <div class="l-quarter">{{ $lesson->quarterLabel() }}</div>
        <div>
          <div class="l-theme">{{ $lesson->theme }}</div>
          <div class="l-dates">{{ $lesson->starts_on->format('M j') }} – {{ $lesson->ends_on->format('M j, Y') }}</div>
        </div>
        <div class="l-cache {{ $cls }}" title="Lesson text cached on the server. Tap Sync to refresh from Adventech.">
          <span class="dot"></span> {{ $s['days'] }}/{{ $s['total_days'] }} days
        </div>
        <div class="l-pdf-cell">
          @if ($lesson->downloadUrl())
            <a class="l-pdf has" href="{{ $lesson->downloadUrl() }}" target="_blank" rel="noopener">PDF ↓</a>
          @else
            <span class="l-pdf no">no PDF</span>
          @endif
        </div>
        <div class="l-sync">
          <form method="POST" action="{{ route('admin.lessons.sync', $lesson) }}"
                onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Syncing…';">
            @csrf
            <button type="submit" title="Pull latest readings from Adventech">Sync ↻</button>
          </form>
        </div>
        <div class="l-actions">
          <a class="l-pdf" href="{{ route('admin.changes', ['type' => get_class($lesson), 'id' => $lesson->id]) }}">History</a>
          <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}"
                data-confirm="Delete {{ $lesson->quarterLabel() }}?">
            @csrf @method('DELETE')
            <button type="submit">Delete</button>
          </form>
        </div>
      </div>
    @endforeach
  </section>
</main>
@include('partials._confirm')

</body>
</html>
