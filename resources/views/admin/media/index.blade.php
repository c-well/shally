<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Media library — Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: "Poppins", system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 18px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: "Instrument Sans", sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: "JetBrains Mono", monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 1280px; margin: 0 auto; padding: 32px clamp(20px, 5vw, 40px) 80px; }
  h1 { font-size: 24px; font-weight: 600; margin-bottom: 8px; }
  .summary { font-family: "JetBrains Mono", monospace; font-size: 11px; letter-spacing: 0.16em; color: var(--ink-soft); margin-bottom: 22px; }
  .toolbar { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-bottom: 24px; padding-bottom: 18px; border-bottom: 1px solid var(--line); }
  .toolbar input[type="search"] { padding: 9px 12px; border: 1px solid var(--line); border-radius: 4px; font: inherit; font-size: 13px; min-width: 220px; }
  .toolbar input[type="search"]:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(3,97,122,0.12); }
  .filter-pills { display: inline-flex; gap: 6px; }
  .filter-pills a { padding: 7px 14px; border: 1px solid var(--line); border-radius: 999px; font-family: "Instrument Sans", sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; transition: all 0.15s; }
  .filter-pills a:hover { color: var(--teal); border-color: var(--teal); }
  .filter-pills a.active { background: var(--teal); color: #fff; border-color: var(--teal); }
  .upload-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; background: var(--teal); color: #fff; border: 0; border-radius: 4px; cursor: pointer; font-family: "Instrument Sans", sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; }
  .upload-btn:hover { background: var(--teal-dark); }
  .flash { margin-bottom: 22px; padding: 14px 18px; background: rgba(3,97,122,0.08); border-left: 3px solid var(--teal); border-radius: 0 4px 4px 0; font-size: 14px; }
  .grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
  .tile { background: #fff; border: 1px solid var(--line); border-radius: 8px; overflow: hidden; transition: transform 0.1s, border-color 0.15s, box-shadow 0.15s; display: flex; flex-direction: column; }
  .tile:hover { border-color: var(--teal); transform: translateY(-1px); box-shadow: 0 8px 22px -10px rgba(3,97,122,0.22); }
  .tile-thumb { aspect-ratio: 4 / 3; background: rgba(3,97,122,0.06); display: flex; align-items: center; justify-content: center; overflow: hidden; }
  .tile-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .tile-audio { font-family: "JetBrains Mono", monospace; font-size: 12px; color: var(--teal); letter-spacing: 0.14em; text-transform: uppercase; text-align: center; padding: 22px; }
  .tile-audio .ico { font-size: 28px; display: block; margin-bottom: 6px; }
  .tile-meta { padding: 10px 12px; font-size: 11px; line-height: 1.4; }
  .tile-meta .name { font-family: "Instrument Sans", sans-serif; font-weight: 600; color: var(--ink); display: block; word-break: break-word; max-height: 2.6em; overflow: hidden; }
  .tile-meta .sub { font-family: "JetBrains Mono", monospace; font-size: 9px; letter-spacing: 0.14em; color: var(--ink-soft); margin-top: 4px; opacity: 0.7; }
  .tile-actions { padding: 0 12px 12px; display: flex; gap: 6px; }
  .tile-actions button, .tile-actions a { flex: 1; padding: 6px 8px; border: 1px solid var(--line); background: transparent; font: inherit; font-family: "Instrument Sans", sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); border-radius: 3px; cursor: pointer; text-align: center; text-decoration: none; transition: color 0.15s, border-color 0.15s; }
  .tile-actions .copy:hover { color: var(--teal); border-color: var(--teal); }
  .tile-actions .del:hover { color: #c0392b; border-color: rgba(192,57,43,0.4); }
  .empty { padding: 60px; text-align: center; color: var(--ink-soft); border: 1px dashed var(--line); border-radius: 8px; }
</style>
@include('admin.partials._typography')
</head>
<body>
<header class="top">
  <a href="{{ url('/admin') }}">← Admin</a>
  <span class="meta">media library</span>
</header>

<main>
  <h1>Media library.</h1>
  <div class="summary">
    {{ $totals['all'] }} items · {{ $totals['image'] }} images · {{ $totals['audio'] }} audio · {{ round($totals['bytes']/1048576, 1) }} MB total
  </div>

  @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif

  <div class="toolbar">
    <div class="filter-pills">
      <a href="{{ route('admin.media.index') }}" class="@if(!$kind) active @endif">All</a>
      <a href="{{ route('admin.media.index', ['kind' => 'image']) }}" class="@if($kind === 'image') active @endif">Images</a>
      <a href="{{ route('admin.media.index', ['kind' => 'audio']) }}" class="@if($kind === 'audio') active @endif">Audio</a>
    </div>
    <form method="GET" action="{{ route('admin.media.index') }}">
      @if ($kind)<input type="hidden" name="kind" value="{{ $kind }}">@endif
      <input type="search" name="q" placeholder="Search by name…" value="{{ $q }}">
    </form>
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" style="margin-left:auto;">
      @csrf
      <label class="upload-btn">
        + Upload new
        <input type="file" name="file" onchange="this.form.submit()" style="display:none;">
      </label>
    </form>
  </div>

  @if (count($items) > 0)
    <div class="grid">
      @foreach ($items as $m)
        <div class="tile" data-media-id="{{ $m->id }}">
          <div class="tile-thumb">
            @if ($m->kind === 'image')
              <img src="{{ $m->url() }}" alt="{{ $m->original_name }}" loading="lazy">
            @else
              <div class="tile-audio"><span class="ico">♪</span>{{ pathinfo($m->original_name, PATHINFO_EXTENSION) ?: 'audio' }}</div>
            @endif
          </div>
          <div class="tile-meta">
            <span class="name">{{ $m->original_name ?: basename($m->path) }}</span>
            <span class="sub">{{ $m->sizeHuman() }}@if ($m->kind === 'image' && $m->width){{ ' · ' . $m->width . '×' . $m->height }}@endif @if ($m->source_tag){{ ' · ' . $m->source_tag }}@endif</span>
          </div>
          <div class="tile-actions">
            <button type="button" class="copy" data-url="{{ $m->url() }}">Copy URL</button>
            <form method="POST" action="{{ route('admin.media.destroy', $m) }}" onsubmit="return confirm('Delete this media file?');" style="flex:1; display:flex;">
              @csrf @method('DELETE')
              <button type="submit" class="del">Delete</button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="empty">No media yet. Upload images or audio above to start the library.</div>
  @endif
</main>

<script>
document.querySelectorAll('.copy').forEach(btn => {
  btn.addEventListener('click', () => {
    navigator.clipboard.writeText(window.location.origin + btn.dataset.url);
    const orig = btn.textContent;
    btn.textContent = '✓ Copied';
    setTimeout(() => { btn.textContent = orig; }, 1500);
  });
});
</script>
</body>
</html>
