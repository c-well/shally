<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Hero slides — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 920px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(48px, 7vw, 72px); font-weight: 500; letter-spacing: -0.035em; line-height: 1; }
  .lede { margin-top: 22px; font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 640px; }
  .flash { margin-top: 22px; padding: 14px 18px; background: rgba(3,97,122,0.08); border-left: 3px solid var(--teal); border-radius: 0 4px 4px 0; font-size: 14px; }

  .add-form { margin-top: 36px; padding: 24px; background: #fff; border: 1px solid var(--line); border-radius: 8px; display: grid; gap: 14px; }
  .add-form .row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .add-form .field { display: flex; flex-direction: column; gap: 6px; }
  .add-form .field.full { grid-column: 1/-1; }
  .add-form label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); }
  .add-form input[type=text], .add-form input[type=url], .add-form input[type=file] {
    font: inherit; font-size: 14px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 4px; background: #fff;
  }
  .add-form button {
    margin-top: 4px; padding: 12px 22px; background: var(--teal); color: #fff; border: 1px solid var(--teal); border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer; justify-self: start;
  }
  .add-form button:hover { background: var(--teal-dark); }
  @media (max-width: 600px) { .add-form .row { grid-template-columns: 1fr; } }

  .slides { margin-top: 50px; display: grid; gap: 18px; }
  .slide-card { display: grid; grid-template-columns: 200px 1fr auto; gap: 22px; padding: 18px; background: #fff; border: 1px solid var(--line); border-radius: 8px; align-items: center; }
  .slide-card img { width: 200px; height: 130px; object-fit: cover; border-radius: 4px; background: rgba(0,0,0,0.05); }
  .slide-card .info form { display: grid; gap: 8px; }
  /* Text-style inputs go full width. Scoped by [type] so checkbox/number don't stretch
     and break the meta-row layout (the bug: is_active checkbox was 100%-width, pushing
     "Order:" label to overlap "Active"). */
  .slide-card .info input[type="text"],
  .slide-card .info input[type="url"] { width: 100%; padding: 8px 10px; border: 1px solid var(--line); border-radius: 4px; font: inherit; font-size: 13px; }
  .slide-card .info input[type="checkbox"] { width: auto; flex: 0 0 auto; margin: 0; }
  .slide-card .info input[type="number"] { width: 70px; padding: 4px 6px; font-family: inherit; font-size: 11px; flex: 0 0 auto; }
  .slide-card .info .meta-row { display: flex; gap: 18px; font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--ink-soft); align-items: center; flex-wrap: wrap; }
  .slide-card .info .meta-row label { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; white-space: nowrap; }
  .slide-card .save-row { display: flex; gap: 8px; }
  .slide-card .save-row button { padding: 6px 14px; border-radius: 3px; font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; cursor: pointer; border: 1px solid; }
  .slide-card .save-row .save { background: var(--teal); color: #fff; border-color: var(--teal); }
  .slide-card .save-row .save:hover { background: var(--teal-dark); }
  .slide-card .actions { display: flex; flex-direction: column; gap: 8px; align-items: stretch; }
  .slide-card .actions button { padding: 6px 14px; border-radius: 3px; font: inherit; font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; cursor: pointer; background: transparent; color: var(--ink-soft); border: 1px solid rgba(192,57,43,0.3); }
  .slide-card .actions button:hover { background: #c0392b; color: #fff; border-color: #c0392b; }
  .slide-card .actions form { margin: 0; }

  .empty { padding: 48px; text-align: center; color: var(--ink-soft); font-style: italic; background: #fff; border: 1px dashed var(--line); border-radius: 8px; }

  @media (max-width: 700px) {
    .slide-card { grid-template-columns: 1fr; }
    .slide-card img { width: 100%; }
  }
</style>
@include('admin.partials._typography')
</head>
<body>

@include('partials.site-menu')
<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">hero slides</span>
</header>
<main>
  <h1>Hero slides.</h1>
  <p class="lede">Photos that rotate in the slider on the home page (between the schedule and "what's happening"). Aim for 3–6 wide landscape shots — building, congregation, services. Wide aspect (16:9) looks best.</p>

  @if (session('status'))<div class="flash">{{ session('status') }}</div>@endif
  @if ($errors->any())<div class="flash" style="background:rgba(192,57,43,0.08); border-left-color:#c0392b;">{{ $errors->first() }}</div>@endif

  <form class="add-form" method="POST" action="{{ route('admin.slides.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="field full">
      <label for="image">+ Add a slide · upload a photo</label>
      <input id="image" type="file" name="image" required accept="image/*,.heic,.heif">
    </div>
    <div class="row">
      <div class="field">
        <label for="caption">Caption (optional, big text)</label>
        <input id="caption" type="text" name="caption" maxlength="200" placeholder='e.g. "Welcome home."'>
      </div>
      <div class="field">
        <label for="subcaption">Sub-caption (optional, small)</label>
        <input id="subcaption" type="text" name="subcaption" maxlength="200" placeholder="e.g. Sabbath worship at 11 AM">
      </div>
    </div>
    <div class="field full">
      <label for="link_url">Click destination (optional)</label>
      <input id="link_url" type="url" name="link_url" maxlength="500" placeholder="https://...">
    </div>
    <button type="submit">Upload slide →</button>
  </form>

  <div class="slides">
    @forelse ($slides as $slide)
      <div class="slide-card">
        <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->caption ?? 'slide' }}">
        <div class="info">
          <form method="POST" action="{{ route('admin.slides.update', $slide) }}">
            @csrf @method('PATCH')
            <input type="text" name="caption" placeholder="Caption" value="{{ $slide->caption }}" maxlength="200">
            <input type="text" name="subcaption" placeholder="Sub-caption" value="{{ $slide->subcaption }}" maxlength="200">
            <input type="url"  name="link_url" placeholder="Link URL (optional)" value="{{ $slide->link_url }}" maxlength="500">
            <div class="meta-row">
              <label><input type="checkbox" name="is_active" value="1" {{ $slide->is_active ? 'checked' : '' }}> Active</label>
              <label>Order: <input type="number" name="sort_order" value="{{ $slide->sort_order }}" min="0" max="9999" style="width:70px; padding: 4px 6px; font-family: inherit; font-size: 11px;"></label>
            </div>
            <div class="save-row"><button type="submit" class="save">Save</button></div>
          </form>
        </div>
        <div class="actions">
          <form method="POST" action="{{ route('admin.slides.destroy', $slide) }}"
                onsubmit="return confirm('Delete this slide?');">
            @csrf @method('DELETE')
            <button type="submit">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="empty">No slides yet — add one above. The slider on the home page only shows when at least one slide is active.</div>
    @endforelse
  </div>
</main>

<!-- HEIC → JPEG client-side conversion (iPhone photos). Lazy-loads heic2any only when an HEIC file is selected. -->
<script>
(function () {
  const fileInput = document.getElementById("image");
  if (!fileInput) return;
  const label = document.querySelector("label[for=\"image\"]");
  const origLabel = label ? label.textContent : "";

  fileInput.addEventListener("change", async (e) => {
    const f = e.target.files[0];
    if (!f) return;
    const isHeic = /\.(heic|heif)$/i.test(f.name) || ["image/heic", "image/heif"].includes(f.type);
    if (!isHeic) return;

    if (label) label.textContent = "⏳ Converting HEIC… (a few seconds)";

    if (!window.heic2any) {
      try {
        await new Promise((resolve, reject) => {
          const s = document.createElement("script");
          s.src = "https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js";
          s.onload = resolve;
          s.onerror = reject;
          document.head.appendChild(s);
        });
      } catch (err) {
        if (label) label.textContent = "⚠ HEIC support didn’t load. Save the photo as JPEG and try again.";
        return;
      }
    }

    try {
      const blob = await window.heic2any({ blob: f, toType: "image/jpeg", quality: 0.9 });
      const newName = f.name.replace(/\.(heic|heif)$/i, ".jpg");
      const jpgFile = new File([blob], newName, { type: "image/jpeg" });
      const dt = new DataTransfer();
      dt.items.add(jpgFile);
      fileInput.files = dt.files;
      if (label) label.textContent = "✓ HEIC converted — " + (jpgFile.size / 1048576).toFixed(1) + "MB ready to upload";
    } catch (err) {
      console.error("HEIC conversion failed:", err);
      if (label) label.textContent = "⚠ Could not convert this HEIC. Save as JPEG and try again.";
    }
  });
})();
</script>

</body>
</html>
