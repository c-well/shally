<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
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
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
  /* ── Considered: robust type, hard restraint, space as the hero ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'IBM Plex Sans', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 2px; }

  main { max-width: 660px; margin: 0 auto; padding: clamp(48px, 9vh, 104px) clamp(22px, 5vw, 34px) 120px; }
  .eyebrow { font-size: 12px; font-weight: 600; letter-spacing: 0.26em; text-transform: uppercase; color: var(--teal); margin-bottom: 22px; }
  h1 { font-size: clamp(38px, 6vw, 60px); font-weight: 700; line-height: 1.02; letter-spacing: -0.025em; color: var(--ink); }
  .lede { margin-top: 24px; font-size: 17px; line-height: 1.62; color: var(--ink-soft); max-width: 520px; }

  form.intake { margin-top: 64px; display: flex; flex-direction: column; gap: 30px; }
  .field { display: flex; flex-direction: column; gap: 9px; }
  .field > label, .grp-label { font-size: 11px; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); }
  .field .opt { font-weight: 400; letter-spacing: 0.04em; text-transform: none; opacity: 0.55; }
  .field .help { font-size: 13.5px; line-height: 1.5; color: var(--ink-soft); opacity: 0.82; }
  .field input[type=text], .field input[type=email], .field input[type=tel], .field input[type=date], .field textarea, .field select {
    font: inherit; font-size: 16px; padding: 14px 16px; border: 1px solid var(--line); border-radius: 6px;
    background: #fff; color: var(--ink); width: 100%; transition: border-color .15s, box-shadow .15s;
  }
  .field textarea { min-height: 120px; resize: vertical; line-height: 1.55; }
  .field select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%236c727e' d='M1 1l5 5 5-5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 42px; }
  .field input:focus, .field textarea:focus, .field select:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); outline: none; }
  .field .err { color: #b23b2e; font-size: 13px; }

  /* photo */
  .photo-zone { border: 1px dashed var(--line); border-radius: 8px; padding: 26px; text-align: center; cursor: pointer; transition: border-color .15s, background .15s; background: #fff; }
  .photo-zone:hover { border-color: var(--teal); }
  .photo-zone input { display: none; }
  .photo-zone .pz-text { font-size: 14.5px; color: var(--ink-soft); }
  .photo-zone .pz-text b { color: var(--teal); font-weight: 600; }
  .photo-zone .pz-sub { font-size: 12.5px; color: var(--ink-soft); opacity: .7; margin-top: 3px; }
  .photo-zone.has img { max-height: 190px; border-radius: 4px; margin: 0 auto 12px; display: block; }

  /* checkbox group */
  .chk-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  @media (max-width: 520px) { .chk-grid { grid-template-columns: 1fr; } }
  .chk { display: flex; align-items: center; gap: 11px; padding: 13px 15px; border: 1px solid var(--line); border-radius: 6px; background: #fff; cursor: pointer; font-size: 15px; color: var(--ink); transition: border-color .15s; }
  .chk:hover { border-color: var(--teal); }
  .chk input { width: 18px; height: 18px; accent-color: var(--teal); flex-shrink: 0; }
  .chk.checked { border-color: var(--teal); background: color-mix(in srgb, var(--teal) 5%, #fff); }

  /* single consent */
  .consent { display: flex; align-items: flex-start; gap: 13px; padding: 18px 20px; border: 1px solid var(--line); border-radius: 8px; background: #fff; cursor: pointer; }
  .consent input { width: 20px; height: 20px; accent-color: var(--teal); flex-shrink: 0; margin-top: 1px; }
  .consent span { font-size: 14.5px; line-height: 1.55; color: var(--ink); }

  .cond { display: none; }
  .cond.show { display: flex; }
  .gate-hint { font-size: 14px; color: var(--ink-soft); font-style: italic; font-family: 'IBM Plex Serif', serif; opacity: .85; }

  button.submit {
    align-self: flex-start; margin-top: 8px; padding: 17px 34px;
    background: var(--teal); color: #fff; border: none; border-radius: 6px;
    font-family: 'IBM Plex Sans', sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.04em; cursor: pointer; transition: background .15s;
  }
  button.submit:hover { background: var(--teal-dark); }

  .flash-done { margin-top: 48px; padding: 0; }
  .flash-done .ok { display: block; font-size: 12px; font-weight: 600; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); margin-bottom: 16px; }
  .flash-done .msg { font-family: 'IBM Plex Serif', serif; font-size: 24px; line-height: 1.45; color: var(--ink); max-width: 540px; }
  .again { display: inline-block; margin-top: 26px; font-size: 12px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--teal); text-decoration: none; }
  .honey { position: absolute; left: -9999px; opacity: 0; pointer-events: none; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="eyebrow">The Church of Peace</div>
  <h1>{{ $form->title }}</h1>
  @if ($form->intro)<p class="lede">{{ $form->intro }}</p>@endif

  @if (session('intake_done'))
    <div class="flash-done">
      <span class="ok">Received</span>
      <div class="msg">{{ session('intake_done') }}</div>
      <a href="{{ route('intake.show', $form) }}" class="again">Submit another @include('partials._ar')</a>
    </div>
  @else
    <form class="intake" method="POST" action="{{ route('intake.submit', $form) }}" enctype="multipart/form-data" novalidate>
      @csrf
      <div class="honey" aria-hidden="true"><label for="website">Website</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off"></div>
      <input type="hidden" name="rendered_at" value="{{ $renderToken }}">

      @foreach ($form->fields() as $f)
        @php $key = $f['key']; $type = $f['type'] ?? 'text'; $cond = $f['show_if'] ?? null; $optional = !($f['required'] ?? false); @endphp
        <div class="field {{ $cond ? 'cond' : '' }}" @if($cond) data-cond='@json($cond)' @endif>
          @if ($type !== 'checkbox')
            <label for="f_{{ $key }}">{{ $f['label'] }}@if($optional && !in_array($type,['checkboxes'])) <span class="opt">(optional)</span>@endif</label>
          @endif
          @if (!empty($f['help']))<span class="help">{{ $f['help'] }}</span>@endif

          @if ($type === 'textarea')
            <textarea id="f_{{ $key }}" name="{{ $key }}" maxlength="5000" placeholder="{{ $f['placeholder'] ?? '' }}">{{ old($key) }}</textarea>
          @elseif ($type === 'select')
            <select id="f_{{ $key }}" name="{{ $key }}">
              <option value="">Choose one…</option>
              @foreach (($f['options'] ?? []) as $opt)<option value="{{ $opt }}" @selected(old($key)===$opt)>{{ $opt }}</option>@endforeach
            </select>
          @elseif ($type === 'checkboxes')
            <div class="chk-grid">
              @foreach (($f['options'] ?? []) as $opt)
                <label class="chk"><input type="checkbox" name="{{ $key }}[]" value="{{ $opt }}"> {{ $opt }}</label>
              @endforeach
            </div>
          @elseif ($type === 'checkbox')
            <label class="consent"><input type="checkbox" id="f_{{ $key }}" name="{{ $key }}" value="1"> <span>{{ $f['label'] }}</span></label>
          @elseif ($type === 'photo')
            <label class="photo-zone" for="f_{{ $key }}">
              <div class="pz-text"><b>Tap to add a photo</b></div>
              <div class="pz-sub">JPG, PNG, or HEIC — from your phone or computer</div>
              <input type="file" id="f_{{ $key }}" name="{{ $key }}" accept="image/*">
            </label>
          @elseif ($type === 'email')
            <input type="email" id="f_{{ $key }}" name="{{ $key }}" maxlength="200" value="{{ old($key) }}" placeholder="{{ $f['placeholder'] ?? '' }}" autocomplete="email">
          @elseif ($type === 'tel')
            <input type="tel" id="f_{{ $key }}" name="{{ $key }}" maxlength="40" value="{{ old($key) }}" placeholder="{{ $f['placeholder'] ?? '' }}" autocomplete="tel">
          @elseif ($type === 'date')
            <input type="date" id="f_{{ $key }}" name="{{ $key }}" value="{{ old($key) }}">
          @else
            <input type="text" id="f_{{ $key }}" name="{{ $key }}" maxlength="500" value="{{ old($key) }}" placeholder="{{ $f['placeholder'] ?? '' }}">
          @endif

          @error($key)<span class="err">{{ $message }}</span>@enderror
        </div>
      @endforeach

      <p class="gate-hint" id="gateHint">Pick a level above and the rest of the form appears.</p>
      <button type="submit" class="submit">{{ $form->setting('submit_label', 'Submit') }}</button>
    </form>
  @endif
</main>

@include('partials.footer')
<script>
(function () {
  var form = document.querySelector('form.intake');
  if (!form) return;
  var conds = [].slice.call(form.querySelectorAll('.cond'));
  var gateHint = document.getElementById('gateHint');

  function valueOf(key) { var el = form.querySelector('[name="' + key + '"]'); return el ? (el.value || '').trim() : ''; }
  function matches(c) { var v = valueOf(c.field); if (c.not_empty) return v !== ''; if (c.in) return c.in.indexOf(v) !== -1; if (c.equals !== undefined) return v === c.equals; return true; }
  function refresh() {
    var anyShown = false;
    conds.forEach(function (w) {
      var c = JSON.parse(w.getAttribute('data-cond')); var show = matches(c);
      w.classList.toggle('show', show);
      w.querySelectorAll('input, select, textarea').forEach(function (i) { i.disabled = !show; });
      if (show) anyShown = true;
    });
    if (gateHint) gateHint.style.display = anyShown ? 'none' : '';
  }
  form.addEventListener('input', refresh);
  form.addEventListener('change', refresh);

  // checkbox visual state
  form.addEventListener('change', function (e) {
    var chk = e.target.closest('.chk'); if (e.target.matches('.chk input')) e.target.closest('.chk').classList.toggle('checked', e.target.checked);
  });

  // photo previews
  form.querySelectorAll('.photo-zone').forEach(function (zone) {
    var input = zone.querySelector('input[type=file]');
    input.addEventListener('change', function () {
      var f = input.files && input.files[0]; if (!f) return;
      var url = URL.createObjectURL(f); zone.classList.add('has');
      zone.innerHTML = '<img src="' + url + '" alt="preview"><div class="pz-text"><b>' + f.name + '</b></div><div class="pz-sub">Tap to choose a different photo</div>';
      zone.appendChild(input);
    });
  });
  refresh();
})();
</script>
</body>
</html>
