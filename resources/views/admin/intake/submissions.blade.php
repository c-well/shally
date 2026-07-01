<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $form->title }} — submissions · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  a { color: inherit; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a.back { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a.back:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 1180px; margin: 0 auto; padding: clamp(36px, 7vh, 64px) clamp(20px, 5vw, 32px) 90px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(24px, 3.4vw, 32px); font-weight: 500; letter-spacing: 0.02em; text-transform: uppercase; }
  .lede { margin-top: 12px; font-size: 14.5px; line-height: 1.6; color: var(--ink-soft); max-width: 640px; }

  .toolbar { margin-top: 26px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; padding: 16px 18px; background: #fff; border: 1px solid var(--line); border-radius: 8px; }
  .toolbar .linkwrap { flex: 1; min-width: 240px; }
  .toolbar .lbl { font-family: 'Instrument Sans', sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 4px; }
  .toolbar .linkrow { display: flex; gap: 8px; align-items: center; }
  .toolbar .link { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: var(--teal); word-break: break-all; }
  .btn { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; border-radius: 6px; padding: 9px 15px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; border: 1px solid var(--line); background: transparent; color: var(--teal); transition: border-color .15s, background .15s, color .15s; }
  .btn:hover { border-color: var(--teal); }
  .btn-solid { background: var(--teal); color: #fff; border-color: var(--teal); }
  .btn-solid:hover { background: var(--teal-dark); }
  .btn-ghost { color: var(--ink-soft); }

  .count { margin: 30px 0 14px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); }

  .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 22px; }
  .card { background: #fff; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 2px rgba(26,35,50,.03); }
  .card .preview { aspect-ratio: 16/9; background: var(--parchment); display: block; width: 100%; object-fit: cover; border-bottom: 1px solid var(--line); }
  .card .body { padding: 15px 17px 17px; display: flex; flex-direction: column; gap: 3px; }
  .card .nm { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; color: var(--ink); }
  .card .sub { font-size: 12px; color: var(--ink-soft); }
  .card .when { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--ink-soft); opacity: .7; margin-top: 2px; }

  /* Refined card action buttons — clear hierarchy, softer, roomier. */
  .card .actions { margin-top: 14px; display: flex; flex-wrap: wrap; gap: 7px; }
  .card .actions .btn { font-size: 10px; letter-spacing: 0.11em; padding: 9px 13px; border-radius: 8px; background: #fff; color: var(--ink-soft); border-color: var(--line); }
  .card .actions .btn:hover { background: color-mix(in srgb, var(--teal) 6%, #fff); border-color: var(--teal); color: var(--teal-dark); }
  .card .actions .btn-primary { background: var(--teal); color: #fff; border-color: var(--teal); }
  .card .actions .btn-primary:hover { background: var(--teal-dark); color: #fff; border-color: var(--teal-dark); }
  .card .actions .btn-danger:hover { background: color-mix(in srgb, var(--red, #b23b3b) 7%, #fff); border-color: var(--red, #b23b3b); color: var(--red, #b23b3b); }

  .empty { padding: 40px; text-align: center; background: #fff; border: 1px dashed var(--line); border-radius: 10px; color: var(--ink-soft); }
  .empty .big { font-family: 'Cormorant Garamond', serif; font-size: 24px; font-style: italic; color: var(--ink); margin-bottom: 8px; }

  .removed { margin-top: 44px; }
  .removed .grid .card { opacity: .65; }

  /* per-card edit panel */
  .edit-panel { padding: 14px 16px 16px; border-top: 1px solid var(--line); background: color-mix(in srgb, var(--teal) 3%, #fff); display: none; flex-direction: column; gap: 9px; }
  .edit-panel.open { display: flex; }
  .edit-panel label { font-family: 'Instrument Sans', sans-serif; font-size: 9px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); display: flex; flex-direction: column; gap: 4px; }
  .edit-panel input, .edit-panel textarea { font: inherit; font-size: 14px; padding: 8px 10px; border: 1px solid var(--line); border-radius: 5px; background: #fff; color: var(--ink); }
  .edit-panel textarea { min-height: 54px; resize: vertical; }
  .edit-panel .ep-actions { display: flex; gap: 8px; margin-top: 4px; }

  /* image adjust modal */
  .adj-ov { position: fixed; inset: 0; background: rgba(20,25,35,.62); display: none; align-items: center; justify-content: center; z-index: 120; padding: 20px; }
  .adj-ov.open { display: flex; }
  .adj-box { background: #fff; border-radius: 14px; width: 100%; max-width: 760px; max-height: 94vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 30px 80px -30px rgba(0,0,0,.5); }
  .adj-hd { padding: 15px 20px; border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; }
  .adj-hd h3 { font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: .06em; }
  .adj-hd .hint { font-size: 11px; color: var(--ink-soft); }
  .adj-x { border: 0; background: none; font-size: 18px; color: var(--ink-soft); cursor: pointer; line-height: 1; }
  .adj-stage { height: 52vh; background: #efeadd; display: flex; align-items: center; justify-content: center; overflow: hidden; }
  .adj-stage img { display: block; max-width: 100%; }
  .adj-tools { padding: 12px 16px; border-top: 1px solid var(--line); display: flex; flex-wrap: wrap; gap: 7px; align-items: center; }
  .adj-tools .btn { color: var(--ink-soft); }
  .adj-sep { width: 1px; height: 22px; background: var(--line); margin: 0 4px; }
  .adj-ar.on { background: var(--teal); color: #fff; border-color: var(--teal); }
  .adj-foot { padding: 14px 20px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; gap: 10px; }
  .adj-foot .btn-primary { background: var(--teal); color: #fff; border-color: var(--teal); }
  .adj-foot .btn-primary:hover { background: var(--teal-dark); }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a class="back" href="{{ route('admin.intake.index') }}">← Intake forms</a>
  <span class="meta">{{ strtoupper($form->slug) }}</span>
</div>

<main>
  <h1>{{ $form->title }}.</h1>
  <p class="lede">Every submission becomes a 1920×1080 slide, ready for ProPresenter. Adjust a photo (rotate, crop, reshape), download one, download them all, or strip the text to use just the photo.</p>

  <div class="toolbar">
    <div class="linkwrap">
      <div class="lbl">Share this form</div>
      <div class="linkrow">
        <span class="link" id="formLink">{{ url('/intake/' . $form->slug) }}</span>
        <button class="btn btn-ghost" type="button" id="copyBtn">Copy</button>
      </div>
    </div>
    <button class="btn" type="button" id="menuToggle" data-url="{{ route('admin.intake.menu', $form) }}" data-in="{{ $form->inMenu() ? '1' : '0' }}">{{ $form->inMenu() ? '✓ In site menu' : '+ Add to site menu' }}</button>
    @if ($live->whereNotNull('output_path')->isNotEmpty())
      <a class="btn btn-solid" href="{{ route('admin.intake.bulk', $form) }}">⬇ Download all ({{ $live->whereNotNull('output_path')->count() }})</a>
    @endif
    @if ($live->whereNotNull('photo_path')->isNotEmpty())
      <button class="btn btn-ghost" type="button" data-bulktext="remove" data-url="{{ route('admin.intake.bulktext', $form) }}">Photo-only on all</button>
      <button class="btn btn-ghost" type="button" data-bulktext="restore" data-url="{{ route('admin.intake.bulktext', $form) }}">Text back on all</button>
    @endif
  </div>

  <div class="count">{{ $live->count() }} {{ Str::plural('submission', $live->count()) }}</div>

  @if ($live->isEmpty())
    <div class="empty">
      <div class="big">Nothing yet.</div>
      Share the link above and submissions will appear here as slides.
    </div>
  @else
    <div class="grid">
      @foreach ($live as $s)
        <div class="card" data-row data-id="{{ $s->id }}">
          @if ($s->output_path)
            <img class="preview" id="prev_{{ $s->id }}" src="{{ $s->outputUrl() }}" alt="Slide for {{ $s->displayName() }}" loading="lazy">
          @else
            <div class="preview" style="display:flex;align-items:center;justify-content:center;color:var(--ink-soft);font-size:12px;">No slide generated</div>
          @endif
          <div class="body">
            <div class="nm">{{ $s->displayName() }}</div>
            @php $bits = array_filter([$s->value('level'), $s->value('school'), $s->value('degree')]); @endphp
            @if ($bits)<div class="sub">{{ implode(' · ', $bits) }}</div>@endif
            <div class="when">{{ $s->created_at->format('M j, Y · g:ia') }}{{ $s->show_text ? '' : ' · photo only' }}</div>
            <div class="actions">
              @if ($s->output_path)
                <a class="btn btn-primary" href="{{ route('admin.intake.download', $s) }}">⬇ Download</a>
              @endif
              @if ($s->photo_path)
                <button class="btn" type="button" data-adjust="{{ route('admin.intake.adjust', $s) }}" data-original="{{ $s->originalUrl() }}">✂ Adjust image</button>
              @endif
              @if ($s->output_path)
                <button class="btn" type="button" data-edit-toggle>✎ Edit text</button>
                @if ($s->photo_path)
                  <button class="btn" type="button" data-toggle-text="{{ route('admin.intake.toggle', $s) }}">{{ $s->show_text ? 'Remove text' : 'Add text' }}</button>
                @endif
              @endif
              <button class="btn btn-danger" type="button"
                      data-remove="{{ route('admin.intake.remove', $s) }}"
                      data-confirm="Remove {{ $s->displayName() }}'s slide from the gallery? You can put it back from the Removed section.">Remove</button>
            </div>
          </div>
          @if ($s->output_path)
            <div class="edit-panel" data-edit-url="{{ route('admin.intake.edit', $s) }}">
              <label>Name<input data-ef="name" value="{{ $s->value('name') }}"></label>
              <label>Level / class<input data-ef="level" value="{{ $s->value('level') }}"></label>
              <label>School<input data-ef="school" value="{{ $s->value('school') }}"></label>
              <label>Degree / major<input data-ef="major" value="{{ $s->value('major') }}"></label>
              <label>Honors<input data-ef="honors" value="{{ $s->value('honors') }}"></label>
              <label>With thanks<textarea data-ef="thanks">{{ $s->value('thanks') }}</textarea></label>
              <div class="ep-actions">
                <button class="btn btn-solid" type="button" data-edit-save>Save &amp; regenerate</button>
                <button class="btn btn-ghost" type="button" data-edit-cancel>Cancel</button>
              </div>
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @endif

  @if ($removed->isNotEmpty())
    <div class="removed">
      <div class="count">Removed ({{ $removed->count() }})</div>
      <div class="grid">
        @foreach ($removed as $s)
          <div class="card" data-row data-id="{{ $s->id }}">
            @if ($s->output_path)<img class="preview" src="{{ $s->outputUrl() }}" alt="" loading="lazy">@endif
            <div class="body">
              <div class="nm">{{ $s->displayName() }}</div>
              <div class="actions">
                <button class="btn" type="button" data-restore="{{ route('admin.intake.restore', $s) }}">↺ Put back</button>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</main>

{{-- Image adjust modal --}}
<div class="adj-ov" id="adjOv" aria-hidden="true">
  <div class="adj-box">
    <div class="adj-hd"><h3>Adjust image</h3><span class="hint">Rotate a sideways photo · crop · reshape</span><button class="adj-x" id="adjClose" type="button" aria-label="Close">✕</button></div>
    <div class="adj-stage"><img id="adjImg" alt=""></div>
    <div class="adj-tools">
      <button class="btn" id="adjRotL" type="button">⟲ Left</button>
      <button class="btn" id="adjRotR" type="button">⟳ Right</button>
      <span class="adj-sep"></span>
      <button class="btn adj-ar on" data-ar="free" type="button">Free</button>
      <button class="btn adj-ar" data-ar="1" type="button">Square</button>
      <button class="btn adj-ar" data-ar="1.3333" type="button">Landscape</button>
      <button class="btn adj-ar" data-ar="0.75" type="button">Portrait</button>
      <span class="adj-sep"></span>
      <button class="btn btn-ghost" id="adjReset" type="button">Reset</button>
    </div>
    <div class="adj-foot">
      <button class="btn btn-ghost" id="adjCancel" type="button">Cancel</button>
      <button class="btn btn-primary" id="adjSave" type="button">Save &amp; regenerate</button>
    </div>
  </div>
</div>

@include('partials._confirm')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
(function () {
  var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
  function post(url, body) {
    var opts = { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } };
    if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    return fetch(url, opts).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); });
  }

  // ── image adjust modal ──
  var cropper = null, adjUrl = null, adjId = null;
  var adjOv = document.getElementById('adjOv'), adjImg = document.getElementById('adjImg'), adjSave = document.getElementById('adjSave');
  function openAdjust(btn) {
    adjUrl = btn.getAttribute('data-adjust'); adjId = btn.closest('[data-row]').getAttribute('data-id');
    adjImg.src = btn.getAttribute('data-original') + '?v=' + Date.now();
    adjOv.classList.add('open');
    setAr('free');
    adjImg.onload = function () {
      if (cropper) cropper.destroy();
      cropper = new Cropper(adjImg, { viewMode: 1, autoCropArea: 1, background: false, responsive: true });
    };
  }
  function closeAdjust() { if (cropper) { cropper.destroy(); cropper = null; } adjOv.classList.remove('open'); adjImg.removeAttribute('src'); }
  function setAr(v) {
    document.querySelectorAll('.adj-ar').forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-ar') === v); });
    if (cropper) cropper.setAspectRatio(v === 'free' ? NaN : parseFloat(v));
  }
  document.getElementById('adjClose').addEventListener('click', closeAdjust);
  document.getElementById('adjCancel').addEventListener('click', closeAdjust);
  adjOv.addEventListener('click', function (e) { if (e.target === adjOv) closeAdjust(); });
  document.getElementById('adjRotL').addEventListener('click', function () { if (cropper) cropper.rotate(-90); });
  document.getElementById('adjRotR').addEventListener('click', function () { if (cropper) cropper.rotate(90); });
  document.getElementById('adjReset').addEventListener('click', function () { if (cropper) cropper.reset(); setAr('free'); });
  document.querySelectorAll('.adj-ar').forEach(function (b) { b.addEventListener('click', function () { setAr(b.getAttribute('data-ar')); }); });
  adjSave.addEventListener('click', function () {
    if (!cropper) return;
    adjSave.disabled = true;
    var canvas = cropper.getCroppedCanvas({ maxWidth: 2200, maxHeight: 2200, imageSmoothingQuality: 'high' });
    if (!canvas) { adjSave.disabled = false; window.shToast('Nothing to save.'); return; }
    canvas.toBlob(function (blob) {
      var fd = new FormData(); fd.append('image', blob, 'edited.jpg');
      fetch(adjUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: fd })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          adjSave.disabled = false;
          if (d.ok) { var im = document.getElementById('prev_' + adjId); if (im) im.src = d.url; window.shToast(d.message); closeAdjust(); }
          else { window.shToast(d.message || 'Could not update the image.'); }
        }).catch(function () { adjSave.disabled = false; window.shToast('Network error.'); });
    }, 'image/jpeg', 0.92);
  });

  // Add / remove from site menu
  var menuToggle = document.getElementById('menuToggle');
  if (menuToggle) menuToggle.addEventListener('click', function () {
    menuToggle.disabled = true;
    post(menuToggle.getAttribute('data-url')).then(function (res) {
      menuToggle.disabled = false;
      if (res.ok && res.d.ok) {
        menuToggle.setAttribute('data-in', res.d.in_menu ? '1' : '0');
        menuToggle.textContent = res.d.in_menu ? '✓ In site menu' : '+ Add to site menu';
        window.shToast(res.d.message);
      } else { window.shToast('Could not update the menu — try again.'); }
    }).catch(function () { menuToggle.disabled = false; window.shToast('Network error.'); });
  });

  // Copy form link
  var copyBtn = document.getElementById('copyBtn');
  if (copyBtn) copyBtn.addEventListener('click', function () {
    navigator.clipboard.writeText(document.getElementById('formLink').textContent.trim())
      .then(function () { window.shToast('Link copied.'); copyBtn.textContent = 'Copied'; setTimeout(function(){ copyBtn.textContent = 'Copy'; }, 1600); });
  });

  document.addEventListener('click', function (e) {
    // Adjust image
    var aj = e.target.closest('[data-adjust]');
    if (aj) { openAdjust(aj); return; }
    // Edit-text panel: open / cancel / save
    if (e.target.closest('[data-edit-toggle]')) { var c0 = e.target.closest('[data-row]').querySelector('.edit-panel'); if (c0) c0.classList.toggle('open'); return; }
    if (e.target.closest('[data-edit-cancel]')) { e.target.closest('.edit-panel').classList.remove('open'); return; }
    var es = e.target.closest('[data-edit-save]');
    if (es) {
      var panel = es.closest('.edit-panel'), card2 = es.closest('[data-row]'), id2 = card2.getAttribute('data-id'), body = {};
      panel.querySelectorAll('[data-ef]').forEach(function (f) { body[f.getAttribute('data-ef')] = f.value; });
      es.disabled = true;
      post(panel.getAttribute('data-edit-url'), body).then(function (res) {
        es.disabled = false;
        if (res.ok && res.d.ok) {
          var img = document.getElementById('prev_' + id2); if (img) img.src = res.d.url;
          var nm = card2.querySelector('.nm'); if (nm && body.name) nm.textContent = body.name;
          panel.classList.remove('open'); window.shToast(res.d.message);
        } else { window.shToast('Could not update — try again.'); }
      }).catch(function () { es.disabled = false; window.shToast('Network error.'); });
      return;
    }
    // Bulk text on/off
    var bt = e.target.closest('[data-bulktext]');
    if (bt) {
      var mode = bt.getAttribute('data-bulktext');
      var msg = mode === 'remove' ? 'Strip the text off EVERY photo slide (photo only)? You can put it back anytime.' : 'Put the text back on EVERY photo slide?';
      window.shConfirm(msg, { okLabel: mode === 'remove' ? 'Photo-only all' : 'Text back all' }).then(function (ok) {
        if (!ok) return; bt.disabled = true;
        post(bt.getAttribute('data-url'), { mode: mode }).then(function (res) {
          if (res.ok && res.d.ok) { window.shToast(res.d.message); setTimeout(function () { location.reload(); }, 800); }
          else { bt.disabled = false; window.shToast('Could not update.'); }
        });
      });
      return;
    }
    // Toggle text overlay
    var t = e.target.closest('[data-toggle-text]');
    if (t) {
      t.disabled = true;
      post(t.getAttribute('data-toggle-text')).then(function (res) {
        t.disabled = false;
        if (res.ok && res.d.ok) {
          var img = document.getElementById('prev_' + t.closest('[data-row]').getAttribute('data-id'));
          if (img) img.src = res.d.url;
          t.textContent = res.d.show_text ? 'Remove text' : 'Add text';
          window.shToast(res.d.message);
        } else { window.shToast('Could not update — try again.'); }
      }).catch(function(){ t.disabled = false; window.shToast('Network error.'); });
      return;
    }
    // Remove (confirm) — uses shConfirm
    var rm = e.target.closest('[data-remove]');
    if (rm) {
      window.shConfirm(rm.getAttribute('data-confirm'), { okLabel: 'Remove', danger: true }).then(function (ok) {
        if (!ok) return;
        post(rm.getAttribute('data-remove')).then(function (res) {
          if (res.ok && res.d.ok) {
            var card = rm.closest('[data-row]');
            card.style.transition = 'opacity .25s, transform .25s'; card.style.opacity = '0'; card.style.transform = 'translateY(-6px)';
            setTimeout(function(){ card.remove(); }, 260);
            window.shToast(res.d.message);
          } else { window.shToast('Could not remove.'); }
        });
      });
      return;
    }
    // Restore
    var rs = e.target.closest('[data-restore]');
    if (rs) {
      post(rs.getAttribute('data-restore')).then(function (res) {
        if (res.ok && res.d.ok) { window.shToast(res.d.message + ' Reloading…'); setTimeout(function(){ location.reload(); }, 700); }
      });
      return;
    }
  });
})();
</script>
</body>
</html>
