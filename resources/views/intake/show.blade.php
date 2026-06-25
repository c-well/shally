<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.seo-head', [
  'title'       => $form->title . ' — The Church of Peace',
  'description' => \Illuminate\Support\Str::limit(strip_tags($form->intro ?? $form->title), 150),
  'path'        => '/intake/' . $form->slug,
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  main { max-width: 720px; margin: 0 auto; padding: clamp(40px, 8vh, 80px) clamp(20px, 5vw, 32px) 90px; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px, 6vw, 56px); font-weight: 500; font-style: italic; line-height: 1.08; letter-spacing: -0.025em; }
  .lede { margin-top: 22px; font-size: 16px; line-height: 1.6; color: var(--ink-soft); max-width: 560px; }

  form.intake { margin-top: 48px; display: grid; gap: 22px; }
  .field { display: flex; flex-direction: column; gap: 8px; }
  .field > label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); }
  .field .help { font-size: 12.5px; line-height: 1.5; color: var(--ink-soft); opacity: 0.85; margin-top: -2px; }
  .field input, .field textarea, .field select {
    font: inherit; font-size: 15px; padding: 13px 15px;
    border: 1px solid var(--line); border-radius: 6px; background: #fff; color: var(--ink); width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .field textarea { min-height: 130px; resize: vertical; font-family: 'Poppins', sans-serif; line-height: 1.55; }
  .field select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%235b6472' d='M1 1l5 5 5-5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px; }
  .field input:focus, .field textarea:focus, .field select:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); outline: none; }
  .field .err { color: #c0392b; font-size: 12px; margin-top: 2px; }

  /* Photo dropzone */
  .photo-zone { border: 1.5px dashed var(--line); border-radius: 8px; padding: 22px; text-align: center; cursor: pointer; transition: border-color 0.15s, background 0.15s; background: #fff; }
  .photo-zone:hover { border-color: var(--teal); background: color-mix(in srgb, var(--teal) 3%, #fff); }
  .photo-zone input { display: none; }
  .photo-zone .pz-icon { font-size: 26px; opacity: 0.5; }
  .photo-zone .pz-text { font-size: 14px; color: var(--ink-soft); margin-top: 6px; }
  .photo-zone .pz-text b { color: var(--teal); font-weight: 600; }
  .photo-zone.has img { max-height: 180px; border-radius: 6px; margin: 0 auto 10px; display: block; box-shadow: 0 8px 22px -12px rgba(0,0,0,0.25); }

  /* Conditional reveal */
  .cond { display: none; }
  .cond.show { display: flex; animation: revealIn 0.32s ease both; }
  @keyframes revealIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

  button.submit {
    margin-top: 10px; padding: 17px 30px;
    background: var(--teal); color: #fff; border: 1px solid var(--teal); border-radius: 6px;
    font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s; justify-self: start;
  }
  button.submit:hover { background: var(--teal-dark); }

  .gate-hint { font-size: 13.5px; color: var(--ink-soft); font-style: italic; opacity: 0.8; padding: 4px 0; }

  .flash-done {
    margin-top: 40px; padding: 28px 30px;
    background: color-mix(in srgb, var(--teal) 8%, transparent); border-left: 4px solid var(--teal);
    border-radius: 0 8px 8px 0; font-size: 17px; line-height: 1.6; color: var(--ink);
    font-family: 'Cormorant Garamond', serif;
  }
  .flash-done .ok { display: block; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); margin-bottom: 10px; }
  .again { display: inline-block; margin-top: 18px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--teal); text-decoration: none; }
  .honey { position: absolute; left: -9999px; opacity: 0; pointer-events: none; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="eyebrow">The Church of Peace</div>
  <h1>{{ $form->title }}</h1>
  @if ($form->intro)
    <p class="lede">{{ $form->intro }}</p>
  @endif

  @if (session('intake_done'))
    <div class="flash-done">
      <span class="ok">Received</span>
      {{ session('intake_done') }}
      <a href="{{ route('intake.show', $form) }}" class="again">Submit another →</a>
    </div>
  @else
    <form class="intake" method="POST" action="{{ route('intake.submit', $form) }}" enctype="multipart/form-data" novalidate>
      @csrf
      <div class="honey" aria-hidden="true">
        <label for="website">Website (leave blank)</label>
        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
      </div>
      <input type="hidden" name="rendered_at" value="{{ $renderToken }}">

      @foreach ($form->fields() as $f)
        @php
          $key = $f['key'];
          $type = $f['type'] ?? 'text';
          $cond = $f['show_if'] ?? null;
        @endphp
        <div class="field {{ $cond ? 'cond' : '' }}" @if($cond) data-cond='@json($cond)' @endif data-field-wrap="{{ $key }}">
          <label for="f_{{ $key }}">{{ $f['label'] }}{!! ($f['required'] ?? false) ? '' : ' <span style=\'font-weight:500;opacity:.6\'>(optional)</span>' !!}</label>
          @if (!empty($f['help']))
            <span class="help">{{ $f['help'] }}</span>
          @endif

          @if ($type === 'textarea')
            <textarea id="f_{{ $key }}" name="{{ $key }}" maxlength="5000" placeholder="{{ $f['placeholder'] ?? '' }}">{{ old($key) }}</textarea>
          @elseif ($type === 'select')
            <select id="f_{{ $key }}" name="{{ $key }}">
              <option value="">Choose one…</option>
              @foreach (($f['options'] ?? []) as $opt)
                <option value="{{ $opt }}" @selected(old($key) === $opt)>{{ $opt }}</option>
              @endforeach
            </select>
          @elseif ($type === 'photo')
            <label class="photo-zone" id="pz_{{ $key }}" for="f_{{ $key }}">
              <div class="pz-icon">🖼️</div>
              <div class="pz-text"><b>Tap to add a photo</b><br>JPG, PNG or HEIC · from your phone or computer</div>
              <input type="file" id="f_{{ $key }}" name="{{ $key }}" accept="image/*">
            </label>
          @elseif ($type === 'email')
            <input type="email" id="f_{{ $key }}" name="{{ $key }}" maxlength="200" value="{{ old($key) }}" placeholder="{{ $f['placeholder'] ?? '' }}" autocomplete="email">
          @else
            <input type="text" id="f_{{ $key }}" name="{{ $key }}" maxlength="500" value="{{ old($key) }}" placeholder="{{ $f['placeholder'] ?? '' }}">
          @endif

          @error($key)<span class="err">{{ $message }}</span>@enderror
        </div>
      @endforeach

      <p class="gate-hint" id="gateHint">Choose an option above and the rest of the form will appear.</p>

      <button type="submit" class="submit">{{ $form->setting('submit_label', 'Submit →') }}</button>
    </form>
  @endif
</main>

@include('partials.footer')

<script>
(function () {
  var form = document.querySelector('form.intake');
  if (!form) return;

  // ── Conditional reveal ──────────────────────────────────────────────
  var conds = Array.prototype.slice.call(form.querySelectorAll('.cond'));
  var gateHint = document.getElementById('gateHint');

  function valueOf(key) {
    var el = form.querySelector('[name="' + key + '"]');
    return el ? (el.value || '').trim() : '';
  }
  function matches(cond) {
    var v = valueOf(cond.field);
    if (cond.not_empty) return v !== '';
    if (cond.in)        return cond.in.indexOf(v) !== -1;
    if (cond.equals !== undefined) return v === cond.equals;
    return true;
  }
  function refresh() {
    var anyShown = false;
    conds.forEach(function (wrap) {
      var cond = JSON.parse(wrap.getAttribute('data-cond'));
      var show = matches(cond);
      wrap.classList.toggle('show', show);
      // Disable hidden inputs so they neither submit nor block validation.
      wrap.querySelectorAll('input, select, textarea').forEach(function (i) { i.disabled = !show; });
      if (show) anyShown = true;
    });
    if (gateHint) gateHint.style.display = anyShown ? 'none' : '';
  }
  form.addEventListener('input', refresh);
  form.addEventListener('change', refresh);
  refresh();

  // ── Photo dropzone preview ──────────────────────────────────────────
  form.querySelectorAll('.photo-zone').forEach(function (zone) {
    var input = zone.querySelector('input[type=file]');
    input.addEventListener('change', function () {
      var f = input.files && input.files[0];
      if (!f) return;
      var url = URL.createObjectURL(f);
      zone.classList.add('has');
      zone.innerHTML = '<img src="' + url + '" alt="preview">' +
        '<div class="pz-text"><b>' + f.name + '</b><br>Tap to choose a different photo</div>';
      zone.appendChild(input);
    });
  });
})();
</script>
</body>
</html>
