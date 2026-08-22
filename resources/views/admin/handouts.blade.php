<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Handouts — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment, #fefcef); color: var(--ink, #1a2332); font-family: 'Instrument Sans', system-ui, sans-serif; width: 100%; max-width: 100%; overflow-x: clip; }

  .top { padding: 24px 28px; display: flex; align-items: center; justify-content: space-between; gap: 14px; border-bottom: 1px solid var(--line, rgba(26,35,50,.12)); }
  .top a { font-size: 13.5px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft, #4a5568); padding: 10px 12px; margin: -10px -12px; }
  .top a:hover { color: var(--teal, #03617A); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink-soft); opacity: .65; text-align: right; }

  main { max-width: 780px; margin: 0 auto; padding: 34px calc(20px + env(safe-area-inset-left)) 120px calc(20px + env(safe-area-inset-right)); }
  h1 { font-size: 28px; font-weight: 600; letter-spacing: -.01em; }
  .lede { color: var(--ink-soft, #4a5568); font-size: 15px; margin-top: 10px; line-height: 1.65; max-width: 60ch; }

  .sec-h { margin: 40px 0 14px; font-size: 11px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase; color: var(--ink-faint, rgba(26,35,50,.45)); }

  /* ── Buttons ── */
  /* --r-btn (8px) governs anything clickable; the 999px pill is reserved for
     non-interactive badges. See the token block in public/css/shalom.css. */
  .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-weight: 600; border-radius: var(--r-btn, 8px); padding: 12px 20px; border: 1px solid transparent; cursor: pointer; text-decoration: none; transition: background .2s, border-color .2s, color .2s; }
  .btn-primary { background: var(--teal, #03617A); color: #fff; }
  .btn-primary:hover { background: var(--teal-dark, #024357); }
  .btn-ghost { background: #fff; color: var(--ink-soft); border-color: var(--line, rgba(26,35,50,.16)); }
  .btn-ghost:hover { border-color: var(--teal, #03617A); color: var(--teal, #03617A); }
  .btn-danger { background: #fff; color: #a33d3d; border-color: rgba(163,61,61,.3); }
  .btn-danger:hover { background: #a33d3d; color: #fff; }
  .btn-lg { padding: 14px 24px; font-size: 15px; }

  /* ── The nudge banner: the whole reason this feature stays honest ── */
  .nudge { margin-top: 26px; background: #fff; border: 1px solid rgba(176,141,60,.4); border-left: 4px solid var(--brass, #b08d3c); border-radius: 12px; padding: 20px 22px; }
  .nudge h2 { font-size: 16px; font-weight: 600; }
  .nudge p { font-size: 14px; color: var(--ink-soft); margin-top: 6px; line-height: 1.6; }
  .nudge ul { list-style: none; margin-top: 14px; }
  .nudge li { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 11px 0; border-top: 1px solid var(--line, rgba(26,35,50,.1)); font-size: 14px; }
  .nudge li strong { font-weight: 600; min-width: 0; overflow-wrap: anywhere; }
  .nudge li .spacer { flex: 1 1 auto; min-width: 0; }
  .nudge form { display: inline; }
  .mini { font-size: 12px; font-weight: 600; padding: 8px 13px; border-radius: var(--r-btn, 8px); border: 1px solid var(--line); background: #fff; color: var(--ink-soft); cursor: pointer; text-decoration: none; }
  .mini:hover { border-color: var(--teal); color: var(--teal); }
  .mini.kill { color: #a33d3d; border-color: rgba(163,61,61,.28); }
  .acts .mini { line-height: 1.2; }
  .mini.kill:hover { background: #a33d3d; color: #fff; }

  /* ── Wizard ── */
  .starter { margin-top: 24px; }
  #wiz { display: none; margin-top: 20px; background: #fff; border: 1px solid var(--line, rgba(26,35,50,.14)); border-radius: 16px; padding: clamp(20px, 4vw, 30px); }
  #wiz.on { display: block; }
  .steps { display: flex; gap: 8px; margin-bottom: 22px; }
  .pip2 { flex: 1; height: 3px; border-radius: 3px; background: var(--line, rgba(26,35,50,.14)); }
  .pip2.on { background: var(--teal, #03617A); }
  .step { display: none; }
  .step.on { display: block; }
  .step h2 { font-size: 20px; font-weight: 600; }
  .step .hint { font-size: 14px; color: var(--ink-soft); margin-top: 7px; line-height: 1.6; }

  .tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 210px), 1fr)); gap: 12px; margin-top: 20px; }
  .tile { text-align: left; background: var(--parchment, #fefcef); border: 1px solid var(--line, rgba(26,35,50,.14)); border-radius: 12px; padding: 18px; cursor: pointer; transition: border-color .2s, transform .2s; min-width: 0; }
  .tile:hover { border-color: var(--teal, #03617A); transform: translateY(-2px); }
  .tile b { display: block; font-size: 16px; font-weight: 600; }
  .tile span { display: block; font-size: 13px; color: var(--ink-soft); margin-top: 6px; line-height: 1.55; }

  .field { margin-top: 18px; }
  .field label { display: block; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 7px; }
  .field .sub { display: block; text-transform: none; letter-spacing: 0; font-weight: 400; font-size: 12.5px; color: var(--ink-faint, rgba(26,35,50,.45)); margin-top: 4px; }
  /* 16px floor — anything smaller makes iOS Safari zoom the whole page on focus. */
  .field input, .field textarea, .field select { width: 100%; max-width: 100%; min-width: 0; font-size: 16px; font-family: inherit; color: var(--ink); background: var(--parchment, #fefcef); border: 1px solid var(--line, rgba(26,35,50,.16)); border-radius: 9px; padding: 13px 14px; }
  .field textarea { min-height: 120px; line-height: 1.65; resize: vertical; }
  .field input:focus, .field textarea:focus, .field select:focus { outline: none; border-color: var(--teal, #03617A); background: #fff; }
  .row2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 190px), 1fr)); gap: 14px; }

  .lifes { display: grid; gap: 12px; margin-top: 20px; }
  .life { display: block; background: var(--parchment, #fefcef); border: 1px solid var(--line, rgba(26,35,50,.14)); border-radius: 12px; padding: 18px; cursor: pointer; }
  .life:has(input:checked) { border-color: var(--teal, #03617A); background: var(--teal-light, #e6f0f3); }
  .life input { margin-right: 10px; }
  .life b { font-size: 15.5px; font-weight: 600; }
  .life span { display: block; font-size: 13px; color: var(--ink-soft); margin-top: 6px; line-height: 1.55; padding-left: 24px; }
  .life .tail { margin-top: 12px; padding-left: 24px; display: none; }
  .life:has(input:checked) .tail { display: block; }
  .life .tail input, .life .tail select { max-width: 260px; }

  .wizfoot { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 26px; }

  /* ── Minted result ── */
  .minted { margin-top: 24px; background: #fff; border: 1px solid var(--teal, #03617A); border-radius: 16px; padding: clamp(20px, 4vw, 30px); text-align: center; }
  .minted h2 { font-size: 20px; font-weight: 600; }
  .minted p { font-size: 14px; color: var(--ink-soft); margin-top: 8px; line-height: 1.6; }
  .linkbox { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: center; margin-top: 18px; }
  .linkbox code { font-family: 'JetBrains Mono', monospace; font-size: 14px; background: var(--parchment, #fefcef); border: 1px solid var(--line); border-radius: 9px; padding: 12px 15px; overflow-wrap: anywhere; min-width: 0; }
  .qrwrap { margin-top: 20px; }
  .qrwrap img { width: 200px; height: 200px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }

  /* ── List ── */
  .card { background: #fff; border: 1px solid var(--line, rgba(26,35,50,.12)); border-radius: 13px; padding: 18px 20px; margin-top: 12px; }
  .card.dead { opacity: .55; }
  .card-h { display: flex; flex-wrap: wrap; align-items: baseline; gap: 10px; }
  .card-h b { font-size: 16.5px; font-weight: 600; min-width: 0; overflow-wrap: anywhere; }
  .tag { font-size: 10.5px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; background: var(--teal-light, #e6f0f3); color: var(--teal-dark, #024357); }
  .tag.warn { background: rgba(176,141,60,.16); color: var(--brass-dark, #8c6f2e); }
  .tag.dead { background: rgba(26,35,50,.08); color: var(--ink-faint); }
  .stats { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--ink-soft); margin-top: 9px; line-height: 1.7; overflow-wrap: anywhere; }
  .acts { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
  .acts form { display: inline; }

  details.edit { margin-top: 14px; border-top: 1px solid var(--line); padding-top: 4px; }
  details.edit > summary { list-style: none; cursor: pointer; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-soft); padding: 12px 0 4px; }
  details.edit > summary::-webkit-details-marker { display: none; }
  details.edit > summary:hover { color: var(--teal); }

  .empty { margin-top: 16px; background: #fff; border: 1px dashed var(--line); border-radius: 12px; padding: 34px 24px; text-align: center; color: var(--ink-soft); font-size: 14px; line-height: 1.65; }
  .flash { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%); background: var(--teal, #03617A); color: #fff; font-size: 13.5px; font-weight: 600; padding: 12px 20px; border-radius: 10px; box-shadow: 0 12px 30px -14px rgba(26,35,50,.6); z-index: 40; max-width: calc(100% - 40px); }
  .err { margin-top: 18px; background: rgba(163,61,61,.07); border: 1px solid rgba(163,61,61,.3); border-radius: 10px; padding: 14px 16px; font-size: 13.5px; color: #a33d3d; }
  .err li { margin-left: 18px; }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">handouts · {{ $live->count() }} live</span>
</header>

<main>
  <h1>Handouts.</h1>
  <p class="lede">
    A page you hand someone — a registry, a welcome card, a one-off event — that lives at its own private link
    and then goes away. Nothing here is on the menu and nothing here is in Google. Pick a shape, answer a few
    questions, print the QR.
  </p>

  @if ($errors->any())
    <div class="err"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  {{-- The nudge, at the top where it cannot be scrolled past. This is the
       feature's conscience: it is what stops "temporary" turning permanent. --}}
  @if ($dueNudge->isNotEmpty())
    <section class="nudge">
      <h2>Still needed?</h2>
      <p>These have no end date, so we check in. Keep one and we will ask again later — or destroy it and the link dies tonight.</p>
      <ul>
        @foreach ($dueNudge as $h)
          <li>
            <strong>{{ $h->title }}</strong>
            <span class="spacer"></span>
            <span class="stats">{{ $h->ageInDays() }}d · {{ $h->uniques }} opened</span>
            <form method="POST" action="{{ route('admin.handouts.keep', $h) }}">@csrf
              <button class="mini" type="submit">Keep {{ $h->nudge_every_days }}d</button>
            </form>
            <form method="POST" action="{{ route('admin.handouts.destroy', $h) }}" onsubmit="return confirm('Destroy “{{ $h->title }}”? The link stops working straight away.')">@csrf @method('DELETE')
              <button class="mini kill" type="submit">Destroy</button>
            </form>
          </li>
        @endforeach
      </ul>
    </section>
  @endif

  {{-- The link that was just minted — shown once, with its QR, right where the
       clerk is looking after pressing Create. --}}
  @if (session('minted'))
    @php $new = \App\Models\Handout::where('token', session('minted'))->first(); @endphp
    @if ($new)
      <section class="minted">
        <h2>Ready to hand out.</h2>
        <p>{{ $new->lifespanLabel() }}. Copy the link, or print the code — both point at the same page.</p>
        <div class="linkbox">
          <code id="mintedlink">{{ $new->url() }}</code>
          <button class="btn btn-ghost" type="button" onclick="copyLink(this, '{{ $new->url() }}')">Copy link</button>
        </div>
        <div class="qrwrap">
          <img src="{{ route('admin.handouts.qr', $new) }}" alt="QR code for {{ $new->title }}">
          <p><a class="btn btn-ghost" href="{{ route('admin.handouts.qr', $new) }}" style="margin-top:14px">Download the code</a></p>
        </div>
      </section>
    @endif
  @endif

  {{-- ── The wizard ── --}}
  <div class="starter">
    <button class="btn btn-primary btn-lg" type="button" id="startbtn">+ New handout</button>
  </div>

  <form id="wiz" method="POST" action="{{ route('admin.handouts.store') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="template" id="f-template" value="notice">

    <div class="steps"><i class="pip2 on" data-pip="1"></i><i class="pip2" data-pip="2"></i><i class="pip2" data-pip="3"></i></div>

    <section class="step on" data-step="1">
      <h2>What are you handing out?</h2>
      <p class="hint">This only sets the starting point — you can change any of it on the next screen.</p>
      <div class="tiles">
        @foreach (\App\Models\Handout::TEMPLATES as $key => $t)
          <button type="button" class="tile" data-pick="{{ $key }}"
                  data-eyebrow="{{ $t['eyebrow'] }}" data-label="{{ $t['label'] }}"
                  data-theme="{{ $t['theme'] }}" data-days="{{ $t['days'] }}"
                  data-asks="{{ implode(',', $t['asks']) }}">
            <b>{{ $t['name'] }}</b>
            <span>{{ $t['blurb'] }}</span>
          </button>
        @endforeach
      </div>
    </section>

    <section class="step" data-step="2">
      <h2>The words.</h2>
      <p class="hint">Write it the way you would say it out loud. Only the title is required.</p>

      <div class="field">
        <label for="f-title">Title</label>
        <input id="f-title" name="title" maxlength="160" required placeholder="Baby Taliya’s Registry">
      </div>

      <div class="field">
        <label for="f-eyebrow">Small line above it <span class="sub">A greeting, not a heading.</span></label>
        <input id="f-eyebrow" name="eyebrow" maxlength="80">
      </div>

      <div class="field">
        <label for="f-body">The message</label>
        <textarea id="f-body" name="body" maxlength="4000" placeholder="Hi everyone! We are getting closer to meeting our sweet…"></textarea>
      </div>

      <div class="row2" data-ask="when">
        <div class="field">
          <label for="f-when">When</label>
          <input id="f-when" name="happens_at" type="datetime-local">
        </div>
      </div>

      <div class="field" data-ask="location">
        <label for="f-loc">Where</label>
        <input id="f-loc" name="location" maxlength="160" placeholder="Shalom SDA Church, Bronx">
      </div>

      <div class="row2" data-ask="link">
        <div class="field">
          <label for="f-link">The link it points to</label>
          <input id="f-link" name="link_url" type="url" inputmode="url" placeholder="https://…">
        </div>
        <div class="field">
          <label for="f-linklabel">Button says</label>
          <input id="f-linklabel" name="link_label" maxlength="60">
        </div>
      </div>

      <div class="row2">
        <div class="field">
          <label for="f-theme">Colour</label>
          <select id="f-theme" name="theme">
            @foreach (\App\Models\Handout::THEMES as $k => $label)
              <option value="{{ $k }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label for="f-image">Photo <span class="sub">Optional. Sits above the title.</span></label>
          <input id="f-image" name="image" type="file" accept="image/*">
        </div>
      </div>

      <div class="wizfoot">
        <button class="btn btn-ghost" type="button" data-goto="1">Back</button>
        <button class="btn btn-primary" type="button" data-goto="3">Next</button>
      </div>
    </section>

    <section class="step" data-step="3">
      <h2>How long does it live?</h2>
      <p class="hint">Every handout has an ending. That is the point of them — the site does not silt up with pages nobody remembers making.</p>

      <div class="lifes">
        <label class="life">
          <input type="radio" name="mode" value="expires" checked>
          <b>It expires</b>
          <span>The link dies on this date on its own. Nobody has to remember to clean up.</span>
          <div class="tail">
            <input name="expires_at" id="f-expires" type="date" required>
          </div>
        </label>

        <label class="life">
          <input type="radio" name="mode" value="open">
          <b>Keep it open — and keep asking me</b>
          <span>No end date, but we will send you a reminder on a schedule: still needed, or destroy it? There is no “forever” option, on purpose.</span>
          <div class="tail">
            <select name="nudge_every_days">
              <option value="14">Ask me every 2 weeks</option>
              <option value="30" selected>Ask me every month</option>
              <option value="60">Ask me every 2 months</option>
              <option value="90">Ask me every 3 months</option>
            </select>
          </div>
        </label>
      </div>

      <div class="wizfoot">
        <button class="btn btn-ghost" type="button" data-goto="2">Back</button>
        <button class="btn btn-primary" type="submit">Create the link</button>
      </div>
    </section>
  </form>

  {{-- ── What is out there ── --}}
  <h2 class="sec-h">Out there</h2>

  @forelse ($handouts as $h)
    <article class="card {{ $h->isLive() ? '' : 'dead' }}">
      <div class="card-h">
        <b>{{ $h->title }}</b>
        @if ($h->trashed())
          <span class="tag dead">Destroyed</span>
        @elseif ($h->isExpired())
          <span class="tag dead">Expired</span>
        @elseif ($h->mode === 'open')
          <span class="tag warn">Open</span>
        @else
          <span class="tag">{{ $h->lifespanLabel() }}</span>
        @endif
        <span class="tag dead">{{ $h->templateMeta()['name'] }}</span>
      </div>

      <p class="stats">
        {{ $h->uniques }} {{ \Illuminate\Support\Str::plural('person', $h->uniques) }} ·
        {{ $h->views }} {{ \Illuminate\Support\Str::plural('open', $h->views) }}
        @if ($h->last_seen_at) · last {{ $h->last_seen_at->timezone(\App\Models\Handout::TZ)->diffForHumans() }} @endif
        <br>/h/{{ $h->token }} · {{ $h->lifespanLabel() }} · by {{ $h->creator->name ?? 'someone' }}
      </p>

      <div class="acts">
        @if ($h->isLive())
          <button class="mini" type="button" onclick="copyLink(this, '{{ $h->url() }}')">Copy link</button>
          <a class="mini" href="{{ $h->url() }}" target="_blank" rel="noopener">Open</a>
          <a class="mini" href="{{ route('admin.handouts.qr', $h) }}">QR</a>
        @endif
        @if ($h->trashed())
          <form method="POST" action="{{ route('admin.handouts.restore', $h->id) }}">@csrf
            <button class="mini" type="submit">Bring it back</button>
          </form>
        @else
          @if ($h->mode === 'open' || $h->isExpired())
            <form method="POST" action="{{ route('admin.handouts.keep', $h) }}">@csrf
              <input type="hidden" name="days" value="{{ $h->nudge_every_days }}">
              <button class="mini" type="submit">Keep {{ $h->nudge_every_days }}d</button>
            </form>
          @endif
          <form method="POST" action="{{ route('admin.handouts.destroy', $h) }}" onsubmit="return confirm('Destroy “{{ $h->title }}”? The link stops working straight away.')">@csrf @method('DELETE')
            <button class="mini kill" type="submit">Destroy</button>
          </form>
        @endif
      </div>

      @unless ($h->trashed())
        <details class="edit">
          <summary>Edit</summary>
          <form method="POST" action="{{ route('admin.handouts.update', $h) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <input type="hidden" name="template" value="{{ $h->template }}">

            <div class="field"><label>Title</label><input name="title" value="{{ $h->title }}" maxlength="160" required></div>
            <div class="field"><label>Small line above it</label><input name="eyebrow" value="{{ $h->eyebrow }}" maxlength="80"></div>
            <div class="field"><label>The message</label><textarea name="body" maxlength="4000">{{ $h->body }}</textarea></div>

            <div class="row2">
              <div class="field"><label>When</label><input name="happens_at" type="datetime-local" value="{{ $h->happens_at?->timezone(\App\Models\Handout::TZ)->format('Y-m-d\TH:i') }}"></div>
              <div class="field"><label>Where</label><input name="location" value="{{ $h->location }}" maxlength="160"></div>
            </div>

            <div class="row2">
              <div class="field"><label>The link it points to</label><input name="link_url" type="url" value="{{ $h->link_url }}"></div>
              <div class="field"><label>Button says</label><input name="link_label" value="{{ $h->link_label }}" maxlength="60"></div>
            </div>

            <div class="row2">
              <div class="field"><label>Colour</label>
                <select name="theme">
                  @foreach (\App\Models\Handout::THEMES as $k => $label)
                    <option value="{{ $k }}" @selected($h->theme === $k)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="field"><label>Replace photo</label><input name="image" type="file" accept="image/*"></div>
            </div>

            <div class="row2">
              <div class="field"><label>Lifespan</label>
                <select name="mode">
                  <option value="expires" @selected($h->mode === 'expires')>Expires on a date</option>
                  <option value="open" @selected($h->mode === 'open')>Open, with reminders</option>
                </select>
              </div>
              <div class="field"><label>Ends / asks again</label>
                <input name="expires_at" type="date" value="{{ $h->expires_at?->timezone(\App\Models\Handout::TZ)->format('Y-m-d') }}">
                <select name="nudge_every_days" style="margin-top:10px">
                  @foreach ([14 => 'every 2 weeks', 30 => 'every month', 60 => 'every 2 months', 90 => 'every 3 months'] as $d => $l)
                    <option value="{{ $d }}" @selected($h->nudge_every_days === $d)>Ask {{ $l }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="wizfoot"><button class="btn btn-primary" type="submit">Save</button></div>
          </form>
        </details>
      @endunless
    </article>
  @empty
    <p class="empty">Nothing out there yet. The first one takes about a minute.</p>
  @endforelse
</main>

@if (session('status'))
  <div class="flash" id="flash">{{ session('status') }}</div>
@endif

<script>
(function () {
  const wiz = document.getElementById('wiz');
  const startBtn = document.getElementById('startbtn');

  function showStep(n) {
    wiz.querySelectorAll('.step').forEach(s => s.classList.toggle('on', s.dataset.step === String(n)));
    wiz.querySelectorAll('.pip2').forEach(p => p.classList.toggle('on', Number(p.dataset.pip) <= n));
    wiz.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  startBtn.addEventListener('click', () => {
    wiz.classList.add('on');
    startBtn.style.display = 'none';
    showStep(1);
  });

  // Step 1 — a template both seeds the copy and decides which questions
  // step 2 bothers to ask. A registry never gets asked for a location.
  wiz.querySelectorAll('.tile').forEach(tile => {
    tile.addEventListener('click', () => {
      const d = tile.dataset;
      document.getElementById('f-template').value = d.pick;
      document.getElementById('f-eyebrow').value = d.eyebrow;
      document.getElementById('f-linklabel').value = d.label;
      document.getElementById('f-theme').value = d.theme;

      const asks = d.asks.split(',');
      wiz.querySelectorAll('[data-ask]').forEach(el => {
        el.style.display = asks.includes(el.dataset.ask) ? '' : 'none';
      });

      // Pre-fill the end date from the template's natural life, so the common
      // case is one tap. A registry outlives a Sabbath concert.
      const by = new Date();
      by.setDate(by.getDate() + Number(d.days));
      document.getElementById('f-expires').value = by.toISOString().slice(0, 10);

      showStep(2);
      setTimeout(() => document.getElementById('f-title').focus(), 260);
    });
  });

  wiz.querySelectorAll('[data-goto]').forEach(b => {
    b.addEventListener('click', () => {
      const to = Number(b.dataset.goto);
      // Do not let someone walk past the one required field and hit a
      // validation wall two screens later.
      if (to === 3 && !document.getElementById('f-title').value.trim()) {
        document.getElementById('f-title').focus();
        return;
      }
      showStep(to);
    });
  });

  // The date input is only required while the expiring mode is actually
  // selected — otherwise a hidden required field silently blocks submit.
  const dateEl = document.getElementById('f-expires');
  wiz.querySelectorAll('input[name="mode"]').forEach(r => {
    r.addEventListener('change', () => { dateEl.required = (r.value === 'expires' && r.checked); });
  });

  const flash = document.getElementById('flash');
  if (flash) setTimeout(() => flash.remove(), 4000);
})();

function copyLink(btn, url) {
  const done = () => { const t = btn.textContent; btn.textContent = 'Copied'; setTimeout(() => btn.textContent = t, 1600); };
  if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(done).catch(() => prompt('Copy this link:', url));
  } else {
    prompt('Copy this link:', url);
  }
}
</script>
</body>
</html>
