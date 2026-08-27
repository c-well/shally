<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Service schedule — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment, #fefcef); color: var(--ink, #1a2332); font-family: 'Instrument Sans', system-ui, sans-serif; width: 100%; max-width: 100%; overflow-x: clip; }

  .top { padding: 24px 28px; display: flex; align-items: center; justify-content: space-between; gap: 14px; border-bottom: 1px solid var(--line, rgba(26,35,50,.12)); }
  .top a { font-size: 13.5px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft, #4a5568); padding: 10px 12px; margin: -10px -12px; }
  .top a:hover { color: var(--teal, #03617A); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink-soft); opacity: .65; text-align: right; }

  main { max-width: 820px; margin: 0 auto; padding: 34px calc(20px + env(safe-area-inset-left)) 120px calc(20px + env(safe-area-inset-right)); }
  h1 { font-size: 28px; font-weight: 600; letter-spacing: -.01em; }
  .lede { color: var(--ink-soft, #4a5568); font-size: 15px; margin-top: 10px; line-height: 1.65; max-width: 62ch; }
  .sec-h { margin: 40px 0 14px; font-size: 11px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; color: var(--ink-faint, rgba(26,35,50,.45)); }

  /* --r-btn (8px) for anything clickable; the pill is for non-interactive badges. */
  .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 600; border-radius: var(--r-btn, 8px); padding: 12px 20px; border: 1px solid transparent; cursor: pointer; text-decoration: none; transition: background .2s, border-color .2s, color .2s; }
  .btn-primary { background: var(--teal, #03617A); color: #fff; }
  .btn-primary:hover { background: var(--teal-dark, #024357); }
  .btn-ghost { background: #fff; color: var(--ink-soft); border-color: var(--line, rgba(26,35,50,.16)); }
  .btn-ghost:hover { border-color: var(--teal, #03617A); color: var(--teal, #03617A); }
  .mini { font-size: 12px; font-weight: 600; padding: 8px 13px; border-radius: var(--r-btn, 8px); border: 1px solid var(--line); background: #fff; color: var(--ink-soft); cursor: pointer; text-decoration: none; }
  .mini:hover { border-color: var(--teal); color: var(--teal); }
  .mini.kill { color: #a33d3d; border-color: rgba(163,61,61,.28); }
  .mini.kill:hover { background: #a33d3d; color: #fff; }

  .card { background: #fff; border: 1px solid var(--line, rgba(26,35,50,.12)); border-radius: var(--r-card, 10px); padding: 20px 22px; margin-top: 14px; }
  .card.off { opacity: .55; }
  .card-h { display: flex; flex-wrap: wrap; align-items: baseline; gap: 10px; }
  .card-h b { font-size: 17px; font-weight: 600; }
  .tag { font-size: 10.5px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; padding: 4px 10px; border-radius: var(--r-badge, 999px); background: var(--teal-light, #e6f0f3); color: var(--teal-dark, #024357); }
  .tag.live { background: rgba(45,134,89,.14); color: var(--green, #2d8659); }
  .tag.off { background: rgba(26,35,50,.08); color: var(--ink-faint); }
  .stats { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink-soft); margin-top: 9px; line-height: 1.7; overflow-wrap: anywhere; }

  .field { margin-top: 16px; }
  .field label { display: block; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 7px; }
  .field .sub { display: block; text-transform: none; letter-spacing: 0; font-weight: 400; font-size: 12.5px; color: var(--ink-faint, rgba(26,35,50,.45)); margin-top: 4px; }
  /* 16px floor — smaller makes iOS Safari zoom the page on focus. */
  .field input[type=text], .field input[type=url], .field input[type=time], .field textarea, .field select {
    width: 100%; max-width: 100%; min-width: 0; font-size: 16px; font-family: inherit; color: var(--ink);
    background: var(--parchment, #fefcef); border: 1px solid var(--line, rgba(26,35,50,.16)); border-radius: var(--r-field, 8px); padding: 13px 14px;
  }
  .field textarea { min-height: 84px; line-height: 1.6; resize: vertical; }
  .field input:focus, .field textarea:focus, .field select:focus { outline: none; border-color: var(--teal, #03617A); background: #fff; }
  .row2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap: 14px; }

  .days { display: flex; flex-wrap: wrap; gap: 8px; }
  .days label { display: inline-flex; align-items: center; gap: 7px; font-size: 14px; font-weight: 500; text-transform: none; letter-spacing: 0; color: var(--ink); background: var(--parchment, #fefcef); border: 1px solid var(--line); border-radius: var(--r-btn, 8px); padding: 10px 13px; cursor: pointer; margin: 0; }
  .days label:has(input:checked) { border-color: var(--teal, #03617A); background: var(--teal-light, #e6f0f3); color: var(--teal-dark, #024357); }
  .days input { width: auto; }

  .check { display: inline-flex; align-items: center; gap: 9px; font-size: 14.5px; color: var(--ink); }
  .check input { width: auto; }

  details.edit { margin-top: 14px; border-top: 1px solid var(--line); padding-top: 4px; }
  details.edit > summary { list-style: none; cursor: pointer; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-soft); padding: 12px 0 4px; }
  details.edit > summary::-webkit-details-marker { display: none; }
  details.edit > summary:hover { color: var(--teal); }

  .foot { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
  .flash { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%); background: var(--teal, #03617A); color: #fff; font-size: 13.5px; font-weight: 600; padding: 12px 20px; border-radius: var(--r-btn, 8px); box-shadow: 0 12px 30px -14px rgba(26,35,50,.6); z-index: 40; max-width: calc(100% - 40px); }
  .err { margin-top: 18px; background: rgba(163,61,61,.07); border: 1px solid rgba(163,61,61,.3); border-radius: var(--r-card, 10px); padding: 14px 16px; font-size: 13.5px; color: #a33d3d; }
  .err li { margin-left: 18px; }
  .note { margin-top: 14px; font-size: 13.5px; color: var(--ink-soft); line-height: 1.6; background: #fff; border: 1px dashed var(--line); border-radius: var(--r-card, 10px); padding: 15px 17px; }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">service schedule · {{ $services->where('is_published', true)->count() }} showing</span>
</header>

<main>
  <h1>Service schedule.</h1>
  <p class="lede">
    The cards on the home page under “Each week”. Change a time, swap a Zoom link, add or retire a gathering —
    it goes live immediately. Every edit is kept in Edit history, so anything here can be undone.
  </p>

  @if ($errors->any())
    <div class="err"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  {{-- The section's own words. Stored as a Page row (slug schedule-intro),
       the same way slider-intro works — but edited here, beside the cards it
       belongs to, instead of in a separate room. --}}
  <h2 class="sec-h">The heading</h2>
  <form method="POST" action="{{ route('admin.services.intro') }}" class="card">
    @csrf @method('PATCH')
    <div class="row2">
      <div class="field">
        <label>Small line above <span class="sub">Currently “Each week”.</span></label>
        <input type="text" name="eyebrow" value="{{ old('eyebrow', $intro->eyebrow) }}" maxlength="80">
      </div>
      <div class="field">
        <label>Heading</label>
        <input type="text" name="title" value="{{ old('title', $intro->title) }}" maxlength="120" required>
      </div>
    </div>
    <div class="field">
      <label>The line underneath</label>
      <textarea name="body_md" maxlength="400">{{ old('body_md', $intro->body_md) }}</textarea>
    </div>
    <div class="foot"><button class="btn btn-primary" type="submit">Save heading</button></div>
  </form>

  <h2 class="sec-h">The gatherings</h2>

  @if (! $living)
    <p class="note">
      <strong>Heads up:</strong> the living schedule is currently switched off in the admin hub, so no service will
      take over the section while it is happening. The cards below still show; the “Happening now” takeover does not.
    </p>
  @endif

  @foreach ($services as $s)
    <article class="card {{ $s->is_published ? '' : 'off' }}">
      <div class="card-h">
        <b>{{ $s->name }}</b>
        @if ($s->isLiveNow())<span class="tag live">Happening now</span>@endif
        @unless ($s->is_published)<span class="tag off">Hidden</span>@endunless
        <span class="tag">{{ $s->where_label }}</span>
      </div>
      <p class="stats">
        {{ $s->when_label }} · takes over: {{ $s->windowLabel() }}
        @if ($s->zoom_url) <br>zoom: {{ \Illuminate\Support\Str::limit($s->zoom_url, 62) }} @endif
      </p>

      <details class="edit">
        <summary>Edit</summary>
        <form method="POST" action="{{ route('admin.services.update', $s) }}">
          @csrf @method('PATCH')
          @include('admin.partials._service-fields', ['s' => $s])
          <div class="foot">
            <button class="btn btn-primary" type="submit">Save</button>
          </div>
        </form>
        <form method="POST" action="{{ route('admin.services.destroy', $s) }}"
              onsubmit="return confirm('Remove “{{ $s->name }}” from the home page?')" style="margin-top:10px">
          @csrf @method('DELETE')
          <button class="mini kill" type="submit">Remove from the page</button>
        </form>
      </details>
    </article>
  @endforeach

  <h2 class="sec-h">Add a gathering</h2>
  <form method="POST" action="{{ route('admin.services.store') }}" class="card">
    @csrf
    @include('admin.partials._service-fields', ['s' => null])
    <div class="foot"><button class="btn btn-primary" type="submit">Add to the home page</button></div>
  </form>
</main>

@if (session('status'))
  <div class="flash" id="flash">{{ session('status') }}</div>
@endif

<script>
  const f = document.getElementById('flash');
  if (f) setTimeout(() => f.remove(), 4000);
</script>
</body>
</html>
