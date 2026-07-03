<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $sermon->exists ? 'Edit' : 'Add' }} sermon — Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 18px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 720px; margin: 0 auto; padding: 28px clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px, 6vw, 56px); font-weight: 500; font-style: italic; letter-spacing: -0.025em; line-height: 1.1; margin-bottom: 24px; }

  .flash { margin-bottom: 22px; padding: 14px 18px; background: color-mix(in srgb, var(--teal) 8%, transparent); border-left: 3px solid var(--teal); border-radius: 0 4px 4px 0; font-size: 14px; }
  .flash.error { background: rgba(192,57,43,0.08); border-left-color: #c0392b; }

  form { display: grid; gap: 18px; }
  .field { display: flex; flex-direction: column; gap: 6px; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .field label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); }
  .field input, .field textarea {
    font: inherit; font-size: 14px; padding: 10px 12px;
    border: 1px solid var(--line); border-radius: 4px; background: #fff; color: var(--ink);
  }
  .field input:focus, .field textarea:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }
  .field textarea { min-height: 200px; resize: vertical; font-family: 'JetBrains Mono', monospace; line-height: 1.55; }
  .field .hint { font-size: 12px; color: var(--ink-soft); opacity: 0.7; margin-top: 2px; }

  .audio-current { padding: 14px 18px; background: color-mix(in srgb, var(--teal) 6%, transparent); border: 1px solid color-mix(in srgb, var(--teal) 20%, transparent); border-radius: 6px; }
  .audio-current audio { width: 100%; height: 40px; margin-top: 6px; }
  .audio-current .label { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--teal); }

  .actions { display: flex; gap: 14px; align-items: center; margin-top: 8px; padding-top: 22px; border-top: 1px solid var(--line); }
  .actions button {
    padding: 14px 28px; border-radius: 5px; cursor: pointer;
    background: var(--teal); color: #fff; border: 1px solid var(--teal);
    font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
  }
  .actions button:hover { background: var(--teal-dark); }
  .actions a { color: var(--ink-soft); text-decoration: none; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; }
  .actions a:hover { color: var(--teal); }

  @media (max-width: 600px) { .field-row { grid-template-columns: 1fr; } }

  /* Markdown toolbar — sits inline with the description label */
  .md-toolbar { display: inline-flex; align-items: center; gap: 12px; margin-left: 12px; vertical-align: middle; }
  .md-tool {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px;
    background: transparent; color: var(--ink-soft);
    border: 1px solid var(--line); border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif; font-size: 9px; font-weight: 600;
    letter-spacing: 0.14em; text-transform: uppercase;
    cursor: pointer; transition: color 0.15s, border-color 0.15s, background 0.15s;
  }
  .md-tool:hover { color: var(--teal); border-color: var(--teal); background: color-mix(in srgb, var(--teal) 4%, transparent); }
  .md-status { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--ink-soft); opacity: 0.7; letter-spacing: 0.04em; }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')
<header class="top">
  <a href="{{ route('admin.sermons.index') }}">@include('partials._arl') Sermons</a>
  <span class="meta">{{ $sermon->exists ? 'editing' : 'new sermon' }}</span>
</header>
<main>
  <h1>{{ $sermon->exists ? 'Edit sermon.' : 'Add a sermon.' }}</h1>

  @if (session('status'))     <div class="flash">{{ session('status') }}</div>            @endif
  @if ($errors->any())        <div class="flash error">{{ $errors->first() }}</div>      @endif

  <form method="POST"
        action="{{ $sermon->exists ? route('admin.sermons.update', $sermon) : route('admin.sermons.store') }}"
        enctype="multipart/form-data">
    @csrf
    @if ($sermon->exists) @method('PATCH') @endif

    <div class="field">
      <label for="header_text">Header (optional · small label above title)</label>
      <input id="header_text" type="text" name="header_text" maxlength="200"
             value="{{ old('header_text', $sermon->header_text) }}"
             placeholder='e.g. "Sabbath Worship · April 27" or "Revival Night 3"'>
      <span class="hint">Shows as a small uppercase eyebrow above the sermon title.</span>
    </div>

    <div class="field">
      <label for="title">Title</label>
      <input id="title" type="text" name="title" required maxlength="200"
             value="{{ old('title', $sermon->title) }}"
             placeholder="e.g. He Knows My Name">
    </div>

    <div class="field-row">
      <div class="field">
        <label for="preached_on">Preached on</label>
        <input id="preached_on" type="date" name="preached_on" required
               value="{{ old('preached_on', $sermon->preached_on?->format('Y-m-d')) }}">
      </div>
      <div class="field">
        <label for="speaker">Speaker (optional)</label>
        <input id="speaker" type="text" name="speaker" maxlength="120"
               value="{{ old('speaker', $sermon->speaker) }}"
               placeholder="e.g. Pastor John Doe">
      </div>
    </div>

    <div class="field">
      <label for="scripture_ref">Scripture reference (optional)</label>
      <input id="scripture_ref" type="text" name="scripture_ref" maxlength="200"
             value="{{ old('scripture_ref', $sermon->scripture_ref) }}"
             placeholder="e.g. Isaiah 43:1-7">
    </div>

    @if ($sermon->exists && $sermon->audioUrl())
      <div class="audio-current">
        <div class="label">Current audio · {{ $sermon->audioSizeHuman() }}</div>
        <audio controls preload="metadata" src="{{ $sermon->audioUrl() }}"></audio>
      </div>
    @endif

    <div class="field">
      <label for="audio">{{ $sermon->exists ? 'Replace audio (optional)' : 'Audio file' }}</label>
      <input id="audio" type="file" name="audio" accept="audio/mpeg,audio/mp4,audio/wav,audio/aac,audio/ogg,.mp3,.m4a,.wav,.aac,.ogg"
             {{ $sermon->exists ? '' : 'required' }}>
      <span class="hint">MP3, M4A, WAV, OGG, or AAC · up to 50 MB</span>
    </div>

    @if ($sermon->exists && $sermon->coverUrl())
      <div class="audio-current" style="background:color-mix(in srgb, var(--brass) 6%, transparent); border-color:color-mix(in srgb, var(--brass) 20%, transparent);">
        <div class="label" style="color:var(--brass);">Current cover</div>
        <img src="{{ $sermon->coverUrl() }}" alt="cover" style="margin-top:8px; max-width:240px; height:auto; border-radius:4px;">
      </div>
    @endif

    <div class="field">
      <label for="cover">
        {{ $sermon->exists ? 'Replace cover image (optional)' : 'Cover image (optional)' }}
        <button type="button" id="cover-pick-btn" class="md-tool" style="margin-left:12px;">📚 Pick from library</button>
      </label>
      <input id="cover" type="file" name="cover" accept="image/*,.heic,.heif">
      <input type="hidden" id="cover_media_id" name="cover_media_id" value="">
      <div id="cover-pick-preview" style="display:none; margin-top:8px;">
        <img id="cover-pick-img" alt="picked cover" style="max-width:240px; height:auto; border-radius:4px; border:1px solid var(--line);">
        <button type="button" id="cover-pick-clear" style="margin-left:10px; padding:4px 10px; font-size:9px; background:transparent; border:1px solid var(--line); border-radius:3px; cursor:pointer; color:var(--ink-soft);">Clear</button>
      </div>
      <span class="hint" id="cover-status">JPG, PNG, WebP, GIF, or iPhone HEIC · up to 10 MB · OR pick from the library above</span>
    </div>

    <div class="field">
      <label for="video_url">Video link (optional)</label>
      <input id="video_url" type="url" name="video_url" maxlength="500"
             value="{{ old('video_url', $sermon->video_url) }}"
             placeholder="https://www.youtube.com/watch?v=...">
      <span class="hint">YouTube, Vimeo, or Facebook video URL · shows as a "Watch on YouTube @include('partials._arup')" link in the sermon player.</span>
    </div>

    <div class="field">
      <label for="description_md">
        Description (optional · markdown supported)
        <span class="md-toolbar">
          <button type="button" id="insert-image-btn" class="md-tool" title="Upload + insert image">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg>
            Insert image
          </button>
          <input type="file" id="md-image-input" accept="image/*,.heic,.heif" style="display:none">
          <span id="md-image-status" class="md-status"></span>
        </span>
      </label>
      <textarea id="description_md" name="description_md" maxlength="50000"
                placeholder="A sentence or two on the sermon. Use **bold**, *italic*, [links](https://...), ## headings, and ![](image-url) for inline images.">{{ old('description_md', $sermon->description_md) }}</textarea>
    </div>

    <div class="actions">
      <button type="submit">{{ $sermon->exists ? 'Save changes @include('partials._ar')' : 'Add sermon @include('partials._ar')' }}</button>
      <a href="{{ route('admin.sermons.index') }}">Cancel</a>
    </div>
  </form>
</main>

{{-- Media library picker modal — opens when "Pick from library" is clicked --}}
<div id="media-picker" style="display:none; position:fixed; inset:0; background:color-mix(in srgb, var(--ink) 55%, transparent); z-index:9000; align-items:center; justify-content:center;">
  <div style="background:#fff; max-width:840px; width:calc(100% - 32px); max-height:80vh; border-radius:14px; padding:24px; display:flex; flex-direction:column; box-shadow:0 20px 50px -12px color-mix(in srgb, var(--ink) 40%, transparent);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
      <h3 style="font-family:'JetBrains Mono',monospace; font-size:14px; letter-spacing:0.06em; font-weight:600;">Pick a cover image</h3>
      <button type="button" id="media-picker-close" style="background:transparent; border:0; cursor:pointer; font-size:22px; color:var(--ink-soft);" aria-label="Close">×</button>
    </div>
    <input type="search" id="media-picker-search" placeholder="Search…" style="padding:9px 12px; border:1px solid var(--line); border-radius:4px; font:inherit; font-size:13px; margin-bottom:14px;">
    <div id="media-picker-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(120px, 1fr)); gap:10px; overflow-y:auto; flex:1;"></div>
    <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--line); display:flex; justify-content:space-between; align-items:center;">
      <a href="{{ route('admin.media.index') }}" target="_blank" rel="noopener" style="font-family:'Instrument Sans',sans-serif; font-size:10px; letter-spacing:0.18em; text-transform:uppercase; color:var(--ink-soft); text-decoration:none;">Manage library @include('partials._arup')</a>
      <button type="button" id="media-picker-cancel" style="padding:9px 18px; background:transparent; border:1px solid var(--line); border-radius:4px; cursor:pointer; font-family:'Instrument Sans',sans-serif; font-size:10px; letter-spacing:0.18em; text-transform:uppercase; color:var(--ink-soft);">Cancel</button>
    </div>
  </div>
</div>

<!-- HEIC @include('partials._ar') JPEG client-side conversion for the cover input (iPhone photos). -->
<script>
(function () {
  const fi = document.getElementById('cover');
  const status = document.getElementById('cover-status');
  if (!fi) return;
  fi.addEventListener('change', async (e) => {
    const f = e.target.files[0];
    if (!f) return;
    const isHeic = /\.(heic|heif)$/i.test(f.name) || ['image/heic','image/heif'].includes(f.type);
    if (!isHeic) { if (status) status.textContent = '✓ ' + f.name; return; }
    if (status) status.textContent = '⏳ Converting HEIC… (a few seconds)';
    if (!window.heic2any) {
      try {
        await new Promise((resolve, reject) => {
          const s = document.createElement('script');
          s.src = 'https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js';
          s.onload = resolve; s.onerror = reject;
          document.head.appendChild(s);
        });
      } catch { if (status) status.textContent = '⚠ HEIC support did not load. Save as JPEG and try again.'; return; }
    }
    try {
      const blob = await window.heic2any({ blob: f, toType: 'image/jpeg', quality: 0.9 });
      const newName = f.name.replace(/\.(heic|heif)$/i, '.jpg');
      const jpg = new File([blob], newName, { type: 'image/jpeg' });
      const dt = new DataTransfer(); dt.items.add(jpg); fi.files = dt.files;
      if (status) status.textContent = '✓ HEIC converted — ready to upload';
    } catch {
      if (status) status.textContent = '⚠ Could not convert this HEIC. Save as JPEG and try again.';
    }
  });
})();
</script>

<!-- Cover image picker — opens the media library, lets you pick an existing image -->
<script>
(function () {
  const pickBtn  = document.getElementById('cover-pick-btn');
  const modal    = document.getElementById('media-picker');
  const grid     = document.getElementById('media-picker-grid');
  const search   = document.getElementById('media-picker-search');
  const closeBtn = document.getElementById('media-picker-close');
  const cancelBtn= document.getElementById('media-picker-cancel');
  const hiddenId = document.getElementById('cover_media_id');
  const fileInp  = document.getElementById('cover');
  const preview  = document.getElementById('cover-pick-preview');
  const previewImg = document.getElementById('cover-pick-img');
  const clearBtn = document.getElementById('cover-pick-clear');
  const status   = document.getElementById('cover-status');
  if (!pickBtn || !modal || !grid) return;

  let cache = null;
  async function loadItems() {
    if (cache) return cache;
    const res = await fetch('{{ route('admin.media.index') }}?json=1&kind=image', {
      credentials: 'same-origin', headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();
    cache = data.items || [];
    return cache;
  }
  function renderGrid(items, q) {
    const filt = q ? items.filter(i => (i.original_name || '').toLowerCase().includes(q.toLowerCase())) : items;
    if (filt.length === 0) {
      grid.innerHTML = '<div style="padding:40px; text-align:center; color:var(--ink-soft); font-style:italic; grid-column:1/-1;">No images found.</div>';
      return;
    }
    grid.innerHTML = filt.map(i =>
      '<button type="button" class="media-tile" data-id="' + i.id + '" data-url="' + i.url + '" data-name="' + (i.original_name||'').replace(/"/g, '&quot;') + '" '
      + 'style="aspect-ratio:1; background:color-mix(in srgb, var(--teal) 6%, transparent); border:2px solid transparent; border-radius:6px; padding:0; overflow:hidden; cursor:pointer; transition:border-color 0.12s;">'
      + '<img src="' + i.url + '" alt="" style="width:100%; height:100%; object-fit:cover; display:block;" loading="lazy">'
      + '</button>'
    ).join('');
    grid.querySelectorAll('.media-tile').forEach(t => {
      t.addEventListener('mouseenter', () => t.style.borderColor = 'var(--teal)');
      t.addEventListener('mouseleave', () => t.style.borderColor = 'transparent');
      t.addEventListener('click', () => pick(t.dataset.id, t.dataset.url, t.dataset.name));
    });
  }
  function open() {
    modal.style.display = 'flex';
    loadItems().then(items => renderGrid(items, ''));
    setTimeout(() => search.focus(), 50);
  }
  function close() { modal.style.display = 'none'; }
  function pick(id, url, name) {
    hiddenId.value = id;
    fileInp.value = ''; // clear any pending file upload — picker takes precedence
    previewImg.src = url;
    preview.style.display = 'block';
    if (status) status.textContent = '✓ Picked from library: ' + name;
    close();
  }

  pickBtn.addEventListener('click', open);
  closeBtn.addEventListener('click', close);
  cancelBtn.addEventListener('click', close);
  modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
  search.addEventListener('input', (e) => { if (cache) renderGrid(cache, e.target.value); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && modal.style.display === 'flex') close(); });

  if (clearBtn) clearBtn.addEventListener('click', () => {
    hiddenId.value = '';
    preview.style.display = 'none';
    if (status) status.textContent = 'JPG, PNG, WebP, GIF, or iPhone HEIC · up to 10 MB · OR pick from the library above';
  });

  // If user picks a file, clear any prior media-pick (file input takes precedence)
  if (fileInp) fileInp.addEventListener('change', () => {
    if (fileInp.files.length > 0) {
      hiddenId.value = '';
      preview.style.display = 'none';
    }
  });
})();
</script>

<!-- Insert image into description: upload, get URL, splice markdown at cursor -->
<script>
(function () {
  const btn = document.getElementById('insert-image-btn');
  const fi = document.getElementById('md-image-input');
  const ta = document.getElementById('description_md');
  const status = document.getElementById('md-image-status');
  if (!btn || !fi || !ta) return;

  btn.addEventListener('click', () => fi.click());

  fi.addEventListener('change', async (e) => {
    let file = e.target.files[0];
    if (!file) return;
    status.textContent = '⏳ Uploading…';

    // HEIC client-side conversion (iPhone photos)
    const isHeic = /\.(heic|heif)$/i.test(file.name) || ['image/heic','image/heif'].includes(file.type);
    if (isHeic) {
      status.textContent = '⏳ Converting HEIC…';
      if (!window.heic2any) {
        try {
          await new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js';
            s.onload = resolve; s.onerror = reject;
            document.head.appendChild(s);
          });
        } catch { status.textContent = '⚠ HEIC support failed'; return; }
      }
      try {
        const blob = await window.heic2any({ blob: file, toType: 'image/jpeg', quality: 0.9 });
        file = new File([blob], file.name.replace(/\.(heic|heif)$/i, '.jpg'), { type: 'image/jpeg' });
      } catch { status.textContent = '⚠ HEIC convert failed'; return; }
    }

    const fd = new FormData();
    fd.append('image', file);
    try {
      const res = await fetch('{{ route('admin.sermons.upload-image') }}', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json' },
      });
      if (!res.ok) throw new Error('upload failed: ' + res.status);
      const data = await res.json();
      // Splice the markdown at the textarea's cursor position
      const start = ta.selectionStart || 0;
      const end = ta.selectionEnd || 0;
      const md = '\n\n' + data.markdown + '\n\n';
      ta.value = ta.value.slice(0, start) + md + ta.value.slice(end);
      ta.focus();
      ta.selectionStart = ta.selectionEnd = start + md.length;
      status.textContent = '✓ Inserted';
      setTimeout(() => { status.textContent = ''; }, 2400);
    } catch (err) {
      status.textContent = '⚠ Upload failed';
      console.error(err);
    } finally {
      fi.value = ''; // reset so the same file can be picked again
    }
  });
})();
</script>
</body>
</html>
