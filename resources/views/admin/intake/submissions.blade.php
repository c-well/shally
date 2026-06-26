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
  .btn { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; border-radius: 6px; padding: 9px 15px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; border: 1px solid var(--line); background: transparent; color: var(--teal); transition: border-color .15s, background .15s; }
  .btn:hover { border-color: var(--teal); }
  .btn-solid { background: var(--teal); color: #fff; border-color: var(--teal); }
  .btn-solid:hover { background: var(--teal-dark); }
  .btn-ghost { color: var(--ink-soft); }

  .count { margin: 30px 0 14px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); }

  .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 22px; }
  .card { background: #fff; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; }
  .card .preview { aspect-ratio: 16/9; background: var(--parchment); display: block; width: 100%; object-fit: cover; border-bottom: 1px solid var(--line); }
  .card .body { padding: 14px 16px; display: flex; flex-direction: column; gap: 3px; }
  .card .nm { font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 500; color: var(--ink); }
  .card .sub { font-size: 12px; color: var(--ink-soft); }
  .card .when { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--ink-soft); opacity: .7; margin-top: 2px; }
  .card .actions { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; }

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
  <p class="lede">Every submission becomes a 1920×1080 slide, ready for ProPresenter. Download one, download them all, or strip the text to use just the photo.</p>

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
                <a class="btn" href="{{ route('admin.intake.download', $s) }}">⬇ Download</a>
                <button class="btn" type="button" data-edit-toggle>Edit text</button>
                @if ($s->photo_path)
                  <button class="btn" type="button" data-toggle-text="{{ route('admin.intake.toggle', $s) }}">{{ $s->show_text ? 'Remove text' : 'Add text' }}</button>
                @endif
              @endif
              <button class="btn btn-ghost" type="button"
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

@include('partials._confirm')
<script>
(function () {
  var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
  function post(url, body) {
    var opts = { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } };
    if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    return fetch(url, opts).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); });
  }

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
