<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@include('partials.seo-head', [
  'title'       => 'Welcome — The Church of Peace',
  'description' => 'First time at Shalom? Leave your name so we can say hello properly.',
  'path'        => '/connect',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Instrument Sans', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
  main { max-width: 560px; margin: 0 auto; padding: clamp(48px,8vh,84px) clamp(20px,5vw,28px) 110px; }
  .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); text-align: center; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px,8vw,50px); font-weight: 500; text-align: center; margin-top: 12px; line-height: 1.05; }
  .lede { text-align: center; font-size: 15px; color: var(--ink-soft); margin: 14px auto 0; max-width: 400px; line-height: 1.65; }
  form { margin-top: 36px; }
  label.f { display: block; margin-top: 18px; }
  label.f span { display: block; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 7px; }
  label.f small { font-weight: 500; letter-spacing: 0; text-transform: none; color: var(--ink-faint, #6b7280); }
  input[type=text], input[type=tel], input[type=email], select { width: 100%; font: inherit; font-size: 16px; padding: 14px 15px; border: 1px solid var(--line); border-radius: 10px; background: #fff; color: var(--ink); }
  input:focus, select:focus { outline: none; border-color: var(--teal); }
  .bd { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .opts { margin-top: 22px; display: grid; gap: 12px; }
  .opt { display: flex; align-items: flex-start; gap: 12px; background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 14px 15px; cursor: pointer; }
  .opt input { transform: scale(1.3); accent-color: var(--teal); margin-top: 3px; }
  .opt b { font-size: 14.5px; } .opt small { display: block; color: var(--ink-soft); margin-top: 2px; font-size: 12.5px; line-height: 1.5; }
  .send { margin-top: 28px; width: 100%; font: 700 13px 'Instrument Sans'; letter-spacing: 0.12em; text-transform: uppercase; color: #fff; background: var(--teal); border: 0; border-radius: 10px; padding: 17px; cursor: pointer; }
  .send:hover { filter: brightness(1.08); }
  .err { color: #a33d3d; font-size: 13px; margin-top: 8px; }
  .thanks { text-align: center; background: #fff; border: 1px solid color-mix(in srgb, var(--teal) 25%, var(--line)); border-radius: 14px; padding: 40px 26px; margin-top: 36px; }
  .thanks .big { font-family: 'Cormorant Garamond', serif; font-size: 28px; }
  .thanks p { color: var(--ink-soft); margin-top: 10px; font-size: 14.5px; line-height: 1.65; }
  .honey { position: absolute; left: -9999px; opacity: 0; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<main>
  <div class="eyebrow">First time here?</div>
  <h1>We're glad you came.</h1>

  @if (session('sent'))
    <div class="thanks">
      <div class="big">Thank you 🙏</div>
      <p>It was a joy having you with us. Someone from the Shalom family will say hello soon — no spam, no pressure, just a welcome.</p>
    </div>
  @else
    <p class="lede">Leave your name so we can welcome you properly. Takes twenty seconds.</p>
    <form method="POST" action="{{ route('connect.store') }}">
      @csrf
      <div class="honey" aria-hidden="true">
        <label for="form_meta_field">Leave this empty</label>
        <input type="text" id="form_meta_field" name="form_meta_field" tabindex="-1" autocomplete="one-time-code">
      </div>
      <input type="hidden" name="rendered_at" value="{{ $renderToken }}">

      <label class="f"><span>Your name</span>
        <input type="text" name="name" required maxlength="120" value="{{ old('name') }}" autocomplete="name">
      </label>
      <label class="f"><span>Phone <small>— we'll text a short hello</small></span>
        <input type="tel" name="phone" maxlength="40" value="{{ old('phone') }}" autocomplete="tel" placeholder="(___) ___-____">
      </label>
      <label class="f"><span>Email <small>— if you prefer</small></span>
        <input type="email" name="email" maxlength="200" value="{{ old('email') }}" autocomplete="email">
      </label>
      @error('phone')<div class="err">{{ $message }}</div>@enderror

      <label class="f"><span>Birthday <small>— optional, so we can celebrate you</small></span></label>
      <div class="bd">
        <select name="birthday_month"><option value="">Month</option>
          @foreach (['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $i => $mn)
            <option value="{{ $i+1 }}" @selected(old('birthday_month') == $i+1)>{{ $mn }}</option>
          @endforeach
        </select>
        <select name="birthday_day"><option value="">Day</option>
          @for ($d = 1; $d <= 31; $d++)<option value="{{ $d }}" @selected(old('birthday_day') == $d)>{{ $d }}</option>@endfor
        </select>
      </div>

      <div class="opts">
        <label class="opt"><input type="checkbox" name="wants_updates" value="1" @checked(old('wants_updates'))>
          <span><b>Keep me posted</b><small>Prayer meetings, announcements, what's happening at Shalom.</small></span></label>
        <label class="opt"><input type="checkbox" name="wants_volunteer" value="1" @checked(old('wants_volunteer'))>
          <span><b>I'd like to help</b><small>Outreach and church activities — tell me when hands are needed.</small></span></label>
      </div>

      <button type="submit" class="send">That's me — say hello</button>
    </form>
  @endif
</main>
@include('partials._event-tracker')
</body>
</html>
