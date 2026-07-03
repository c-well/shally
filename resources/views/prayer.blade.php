<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@include('partials.seo-head', [
  'title'       => 'Prayer Request · The Church of Peace',
  'description' => 'Send a prayer request to Shalom SDA Church in the Bronx. Confidential — only seen by the prayer team.',
  'path'        => '/prayer',
])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  main { max-width: 720px; margin: 0 auto; padding: clamp(40px, 8vh, 80px) clamp(20px, 5vw, 32px); }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(40px, 7vw, 60px); line-height: 1.05; font-weight: 500; letter-spacing: -0.015em; margin-bottom: 18px; color: var(--ink); }
  .lede { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.5; color: var(--ink-soft); max-width: 540px; margin-bottom: 18px; }

  .privacy-card {
    margin: 28px 0 36px; padding: 18px 22px;
    background: color-mix(in srgb, var(--teal) 6%, transparent);
    border-left: 3px solid var(--teal);
    border-radius: 0 6px 6px 0;
    font-size: 14px; line-height: 1.55; color: var(--ink-soft);
  }
  .privacy-card strong { color: var(--ink); }

  form.prayer { display: grid; gap: 16px; }
  .field { display: flex; flex-direction: column; gap: 7px; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 540px) { .field-row { grid-template-columns: 1fr; } }
  .field label { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); }
  .field .hint { font-family: 'Cormorant Garamond', serif; font-size: 14px; font-style: italic; color: var(--ink-soft); opacity: 0.7; }
  .field input, .field textarea {
    font: inherit; font-size: 15px; padding: 12px 14px;
    border: 1px solid var(--line); border-radius: 4px; background: #fff; color: var(--ink); width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .field textarea { min-height: 180px; resize: vertical; font-family: 'Poppins', sans-serif; line-height: 1.55; }
  .field input:focus, .field textarea:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); outline: none; }
  .field .err { color: #c0392b; font-size: 12px; margin-top: 2px; }

  .check-row { display: flex; align-items: flex-start; gap: 10px; padding: 6px 0; cursor: pointer; }
  .check-row input { width: 18px; height: 18px; margin-top: 2px; accent-color: var(--teal); }
  .check-row .lbl-text { font-size: 14px; line-height: 1.4; color: var(--ink); }
  .check-row .lbl-text small { display: block; font-size: 12px; color: var(--ink-soft); margin-top: 2px; }

  .honey { position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none; }

  button.submit {
    margin-top: 12px; padding: 16px 28px;
    background: var(--teal); color: #fff; border: 1px solid var(--teal); border-radius: 5px;
    font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s; justify-self: start;
  }
  button.submit:hover { background: var(--teal-dark); }

  .flash {
    margin-bottom: 24px; padding: 22px 26px;
    background: color-mix(in srgb, var(--teal) 8%, transparent); border-left: 4px solid var(--teal);
    border-radius: 0 6px 6px 0;
    font-size: 16px; line-height: 1.55; color: var(--ink);
  }
  .flash strong { color: var(--teal); }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="eyebrow">A church praying for you</div>
  <h1>Prayer Request</h1>
  <p class="lede">Whatever you're walking through — illness, a hard decision, a loss, joy, gratitude — share it with our prayer team. We'll lift it up before God on your behalf.</p>

  @if (session('sent'))
    <div class="flash">
      <strong>Thank you. Your prayer request has been received.</strong><br>
      The prayer team will lift this before God within 24 hours. If you asked for follow-up, someone will reach out by phone or email.
    </div>
  @endif

  <div class="privacy-card">
    <strong>This is private.</strong> Your prayer request is never displayed publicly. By default, only the prayer team sees it. You can leave your name blank to submit anonymously.
  </div>

  <form class="prayer" method="POST" action="{{ route('prayer.send') }}">
    @csrf
    <div class="honey" aria-hidden="true">
      <label for="website">Website</label>
      <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
    </div>
    <input type="hidden" name="rendered_at" value="{{ $renderToken ?? '' }}">

    <div class="field-row">
      <div class="field">
        <label for="name">Your name <span class="hint">— optional</span></label>
        <input type="text" id="name" name="name" maxlength="120" value="{{ old('name') }}" autocomplete="name" placeholder="Leave blank to stay anonymous">
        @error('name')<span class="err">{{ $message }}</span>@enderror
      </div>
      <div class="field">
        <label for="email">Email <span class="hint">— optional</span></label>
        <input type="email" id="email" name="email" maxlength="200" value="{{ old('email') }}" autocomplete="email" placeholder="For follow-up only">
        @error('email')<span class="err">{{ $message }}</span>@enderror
      </div>
    </div>

    <div class="field">
      <label for="phone">Phone <span class="hint">— optional</span></label>
      <input type="tel" id="phone" name="phone" maxlength="40" value="{{ old('phone') }}" autocomplete="tel" placeholder="For follow-up only">
    </div>

    <div class="field">
      <label for="body">What can we pray about?</label>
      <textarea id="body" name="body" required minlength="8" maxlength="5000" placeholder="Share what's on your heart…">{{ old('body') }}</textarea>
      @error('body')<span class="err">{{ $message }}</span>@enderror
    </div>

    <label class="check-row">
      <input type="checkbox" name="want_followup" value="1" {{ old('want_followup') ? 'checked' : '' }}>
      <span class="lbl-text">I'd like a follow-up call from the church<small>Someone will reach out by phone or email if you provided contact info above.</small></span>
    </label>

    <label class="check-row">
      <input type="checkbox" name="keep_private" value="1" {{ old('keep_private', '1') ? 'checked' : '' }}>
      <span class="lbl-text">Keep private — only share with the prayer team<small>Uncheck if you'd like the request shared with the wider congregation during prayer time.</small></span>
    </label>

    <button type="submit" class="submit">Send prayer request @include('partials._ar')</button>
  </form>
</main>

@include('partials.footer')

@include('partials._event-tracker')
</body>
</html>
