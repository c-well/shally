<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Edit /{{ $page->slug }} — Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .top { padding: 18px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  .top .actions { display: flex; gap: 12px; align-items: center; }
  .top .actions a { padding: 6px 14px; border: 1px solid var(--line); border-radius: 4px; }
  .top .actions a:hover { color: var(--teal); border-color: var(--teal); }
  .top .actions .save-btn { background: var(--teal); color: #fff; border-color: var(--teal); padding: 8px 18px; cursor: pointer; font: inherit; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; }
  .top .actions .save-btn:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  .top .actions .save-btn:disabled { opacity: 0.5; cursor: wait; }

  main { max-width: 1280px; margin: 0 auto; padding: 28px clamp(20px, 5vw, 32px) 80px; }

  .flash { margin-bottom: 22px; padding: 14px 18px; background: rgba(3,97,122,0.08); border-left: 3px solid var(--teal); border-radius: 0 4px 4px 0; font-size: 14px; line-height: 1.5; }
  .flash.error { background: rgba(192,57,43,0.08); border-left-color: #c0392b; }

  .meta-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px; }
  .field { display: flex; flex-direction: column; gap: 6px; }
  .field label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); }
  .field input { font: inherit; font-size: 14px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 4px; background: #fff; color: var(--ink); }
  .field input:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(3,97,122,0.12); }
  .field input[readonly] { background: rgba(26,35,50,0.04); color: var(--ink-soft); cursor: default; }

  /* Toolbar */
  .toolbar { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px; background: #fff; border: 1px solid var(--line); border-radius: 6px 6px 0 0; border-bottom: 0; }
  .toolbar button {
    padding: 8px 12px; background: transparent; border: 1px solid transparent;
    border-radius: 3px; cursor: pointer;
    font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 500;
    color: var(--ink-soft); transition: background 0.12s, color 0.12s, border-color 0.12s;
  }
  .toolbar button:hover { background: rgba(3,97,122,0.08); color: var(--teal); border-color: var(--line); }
  .toolbar .sep { width: 1px; background: var(--line); margin: 4px 6px; }
  .toolbar .image-btn { color: var(--brass); font-weight: 600; }

  /* Editor + preview split */
  .split { display: grid; grid-template-columns: 1fr 1fr; gap: 0; min-height: 65vh; border: 1px solid var(--line); border-radius: 0 0 6px 6px; background: #fff; overflow: hidden; }
  .pane-left, .pane-right { padding: 22px 26px; overflow-y: auto; max-height: 75vh; }
  .pane-left { border-right: 1px solid var(--line); background: #fff; }
  .pane-left textarea {
    width: 100%; min-height: 60vh;
    font-family: 'JetBrains Mono', monospace; font-size: 14px;
    line-height: 1.6; color: var(--ink);
    border: 0; outline: 0; resize: vertical; background: transparent;
  }
  .pane-right { background: var(--parchment); position: relative; }
  .pane-right .preview-label {
    position: sticky; top: 0; padding-bottom: 12px; margin-bottom: 12px;
    border-bottom: 1px solid var(--line); background: var(--parchment);
    font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft);
    z-index: 1;
  }

  /* Preview rendering — match the brand prose look */
  .preview-body { font-family: 'Cormorant Garamond', serif; font-size: 17px; line-height: 1.7; color: var(--ink); }
  .preview-body h1 { font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 500; font-style: italic; margin: 1em 0 0.5em; color: var(--ink); }
  .preview-body h2 { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 500; margin: 1.2em 0 0.4em; color: var(--ink); }
  .preview-body h3 { font-family: 'Cormorant Garamond', serif; font-size: 21px; font-weight: 500; margin: 1em 0 0.4em; color: var(--ink); }
  .preview-body p { margin-bottom: 1em; }
  .preview-body blockquote { margin: 1.2em 0; padding: 12px 18px; border-left: 3px solid var(--teal); background: rgba(3,97,122,0.05); font-style: italic; color: var(--ink-soft); }
  .preview-body ul, .preview-body ol { margin: 1em 0 1em 1.4em; }
  .preview-body li { margin-bottom: 0.4em; }
  .preview-body a { color: var(--teal); text-decoration: underline; text-underline-offset: 2px; }
  .preview-body img { max-width: 100%; height: auto; border-radius: 6px; margin: 1em 0; box-shadow: 0 8px 24px -12px rgba(0,0,0,0.2); }
  .preview-body code { font-family: 'JetBrains Mono', monospace; font-size: 0.9em; background: rgba(0,0,0,0.05); padding: 1px 5px; border-radius: 3px; }
  .preview-body pre { background: rgba(0,0,0,0.05); padding: 14px 18px; border-radius: 6px; overflow-x: auto; margin-bottom: 1em; }
  .preview-body pre code { background: none; padding: 0; }
  .preview-body hr { border: 0; border-top: 1px solid var(--line); margin: 2em 0; }
  .preview-body table { width: 100%; border-collapse: collapse; margin: 1em 0; }
  .preview-body th, .preview-body td { padding: 8px 12px; border: 1px solid var(--line); text-align: left; }
  .preview-body th { background: rgba(3,97,122,0.05); font-weight: 600; }

  .footer-info { margin-top: 24px; display: flex; justify-content: space-between; align-items: center; color: var(--ink-soft); font-size: 13px; }
  .footer-info .last-edit { font-family: 'JetBrains Mono', monospace; font-size: 11px; opacity: 0.7; }
  .footer-info .public-link { color: var(--teal); text-decoration: none; border-bottom: 1px solid transparent; transition: border-color 0.15s; }
  .footer-info .public-link:hover { border-bottom-color: var(--teal); }

  .upload-progress { display: none; padding: 8px 14px; margin-left: 8px; background: rgba(176,141,60,0.12); color: var(--brass); border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; }
  .upload-progress.on { display: inline-block; }

  @media (max-width: 900px) {
    .split { grid-template-columns: 1fr; }
    .pane-left { border-right: 0; border-bottom: 1px solid var(--line); }
    .meta-row { grid-template-columns: 1fr; }
  }
</style>
@include('admin.partials._typography')
</head>
<body>

<header class="top">
  <a href="{{ route('admin.pages.index') }}">← Site pages</a>
  <span class="meta">editing /{{ $page->slug }}</span>
  <div class="actions">
    <a href="/{{ $page->slug }}" target="_blank" rel="noopener">View live ↗</a>
    <span class="upload-progress" id="upload-progress">Uploading…</span>
    <button type="submit" form="page-form" class="save-btn" id="save-btn">Save changes →</button>
  </div>
</header>

<main>
  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="flash error">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('admin.pages.update', $page->slug) }}" id="page-form">
    @csrf @method('PATCH')

    <div class="meta-row">
      <div class="field">
        <label for="title">Page title (shows as the &lt;h1&gt;)</label>
        <input id="title" type="text" name="title" required maxlength="200" value="{{ old('title', $page->title) }}">
      </div>
      <div class="field">
        <label for="slug">URL slug (read-only)</label>
        <input id="slug" type="text" value="/{{ $page->slug }}" readonly>
      </div>
    </div>

    <div class="field" style="margin-bottom: 16px;">
      <label for="eyebrow">Eyebrow (small label above the title) — optional</label>
      <input id="eyebrow" type="text" name="eyebrow" maxlength="120" value="{{ old('eyebrow', $page->eyebrow) }}">
    </div>

    {{-- Toolbar --}}
    <div class="toolbar" id="toolbar">
      <button type="button" data-md="h1">H1</button>
      <button type="button" data-md="h2">H2</button>
      <button type="button" data-md="h3">H3</button>
      <span class="sep"></span>
      <button type="button" data-md="bold"><strong>B</strong></button>
      <button type="button" data-md="italic"><em>I</em></button>
      <span class="sep"></span>
      <button type="button" data-md="quote">" Quote</button>
      <button type="button" data-md="link">🔗 Link</button>
      <span class="sep"></span>
      <button type="button" data-md="ul">• List</button>
      <button type="button" data-md="ol">1. List</button>
      <span class="sep"></span>
      <button type="button" class="image-btn" id="image-btn">📷 Insert image</button>
      <input type="file" id="image-input" accept="image/*" style="display:none;">
    </div>

    {{-- Split editor + preview --}}
    <div class="split">
      <div class="pane-left">
        <textarea id="body-md" name="body_md" required>{{ old('body_md', $page->body_md) }}</textarea>
      </div>
      <div class="pane-right">
        <div class="preview-label">Live preview · how visitors will see it</div>
        <div class="preview-body" id="preview"></div>
      </div>
    </div>

    <div class="footer-info">
      <div class="last-edit">
        @if ($page->updated_by_user_id && $page->updatedBy)
          Last saved {{ $page->updated_at->diffForHumans() }} by {{ $page->updatedBy->name }}
        @else
          Never edited yet
        @endif
      </div>
      <a href="/{{ $page->slug }}" target="_blank" rel="noopener" class="public-link">View live page ↗</a>
    </div>
  </form>
</main>

{{-- Markdown parser for live preview (the same library Laravel uses on the server, but client-side: marked.js) --}}
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
(function () {
  const ta      = document.getElementById('body-md');
  const preview = document.getElementById('preview');
  const csrfTok = document.querySelector('meta[name="csrf-token"]').content;
  const upload  = document.getElementById('upload-progress');
  const fileInp = document.getElementById('image-input');
  const imgBtn  = document.getElementById('image-btn');
  const saveBtn = document.getElementById('save-btn');

  // Live preview — rebuild HTML on every keystroke (debounced)
  let renderTimer;
  function render() {
    clearTimeout(renderTimer);
    renderTimer = setTimeout(() => {
      preview.innerHTML = marked.parse(ta.value || '');
    }, 60);
  }
  ta.addEventListener('input', render);
  render(); // initial paint

  // Toolbar buttons — wrap or insert markdown around the current selection
  const wrap = (before, after = before) => {
    const s = ta.selectionStart, e = ta.selectionEnd;
    const sel = ta.value.slice(s, e);
    const text = ta.value.slice(0, s) + before + sel + after + ta.value.slice(e);
    ta.value = text;
    ta.focus();
    ta.selectionStart = s + before.length;
    ta.selectionEnd   = s + before.length + sel.length;
    render();
  };
  const linePrefix = (prefix) => {
    const s = ta.selectionStart;
    // Find start of current line
    const lineStart = ta.value.lastIndexOf('\n', s - 1) + 1;
    ta.value = ta.value.slice(0, lineStart) + prefix + ta.value.slice(lineStart);
    ta.focus();
    ta.selectionStart = ta.selectionEnd = s + prefix.length;
    render();
  };

  document.querySelectorAll('.toolbar button[data-md]').forEach(b => {
    b.addEventListener('click', () => {
      switch (b.dataset.md) {
        case 'h1':     return linePrefix('# ');
        case 'h2':     return linePrefix('## ');
        case 'h3':     return linePrefix('### ');
        case 'bold':   return wrap('**');
        case 'italic': return wrap('*');
        case 'quote':  return linePrefix('> ');
        case 'link':   {
          const url = prompt('Link URL:', 'https://');
          if (url) wrap('[', '](' + url + ')');
          return;
        }
        case 'ul':     return linePrefix('- ');
        case 'ol':     return linePrefix('1. ');
      }
    });
  });

  // Image upload — click button OR drag image onto the textarea
  imgBtn.addEventListener('click', () => fileInp.click());
  fileInp.addEventListener('change', () => {
    if (fileInp.files[0]) uploadFile(fileInp.files[0]);
    fileInp.value = ''; // reset so picking same file twice still fires
  });

  // Drag-and-drop straight into the editor
  ta.addEventListener('dragover', e => { e.preventDefault(); ta.style.background = 'rgba(3,97,122,0.05)'; });
  ta.addEventListener('dragleave', () => ta.style.background = '');
  ta.addEventListener('drop', e => {
    e.preventDefault();
    ta.style.background = '';
    const f = e.dataTransfer.files[0];
    if (f && f.type.startsWith('image/')) uploadFile(f);
  });

  function uploadFile(file) {
    upload.classList.add('on');
    saveBtn.disabled = true;
    const form = new FormData();
    form.append('image', file);
    fetch('{{ route("admin.pages.upload-image") }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfTok, 'Accept': 'application/json' },
      body: form,
    })
      .then(r => r.json())
      .then(data => {
        if (data.error) { alert('Upload failed: ' + data.error); return; }
        // Insert markdown image at cursor
        const md = '![' + (data.alt || 'image') + '](' + data.url + ')\n';
        const s = ta.selectionStart;
        ta.value = ta.value.slice(0, s) + md + ta.value.slice(s);
        ta.selectionStart = ta.selectionEnd = s + md.length;
        ta.focus();
        render();
      })
      .catch(err => alert('Upload failed: ' + err.message))
      .finally(() => {
        upload.classList.remove('on');
        saveBtn.disabled = false;
      });
  }

  // Cmd/Ctrl+S → save (no surprise lost work)
  document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 's') {
      e.preventDefault();
      document.getElementById('page-form').submit();
    }
  });
})();
</script>

</body>
</html>
