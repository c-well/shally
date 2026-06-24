<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Sermons — Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 920px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(48px, 7vw, 72px); font-weight: 500; letter-spacing: -0.035em; line-height: 1; }
  .lede { margin-top: 22px; font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 580px; }
  .add-btn {
    display: inline-block; margin-top: 26px; padding: 12px 22px;
    background: var(--teal); color: #fff; border-radius: 5px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px;
    font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    text-decoration: none; transition: background 0.15s;
  }
  .add-btn:hover { background: var(--teal-dark); }

  .flash { margin-top: 22px; padding: 14px 18px; background: color-mix(in srgb, var(--teal) 8%, transparent); border-left: 3px solid var(--teal); border-radius: 0 4px 4px 0; font-size: 14px; }

  .list { margin-top: 50px; background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 0 24px; }
  .row { display: grid; grid-template-columns: 90px 1fr auto auto; gap: 18px; align-items: center; padding: 18px 4px; border-bottom: 1px solid var(--line); }
  .row:last-child { border-bottom: 0; }
  .row .date { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--teal); }
  .row .title { font-family: 'Cormorant Garamond', serif; font-size: 21px; font-weight: 500; line-height: 1.2; color: var(--ink); }
  .row .meta { font-size: 12px; color: var(--ink-soft); margin-top: 2px; }
  .row .edit-btn, .row .del-btn {
    padding: 7px 14px; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif; font-size: 10px;
    font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase;
    text-decoration: none; cursor: pointer; transition: all 0.15s;
    background: transparent; border: 1px solid var(--line);
  }
  .row .edit-btn { color: var(--teal); border-color: color-mix(in srgb, var(--teal) 30%, transparent); }
  .row .edit-btn:hover { background: var(--teal); color: #fff; border-color: var(--teal); }
  .row .del-btn { color: var(--ink-soft); border-color: rgba(192,57,43,0.3); }
  .row .del-btn:hover { background: #c0392b; color: #fff; border-color: #c0392b; }
  .row form { display: inline; margin: 0; padding: 0; }

  .empty { padding: 48px; text-align: center; color: var(--ink-soft); font-style: italic; }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')
<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">sermons</span>
</header>
<main>
  <h1>Sermons.</h1>
  <p class="lede">Add, edit, or remove sermons in the Peace Notes archive. Each sermon gets its own page with an audio player and an optional summary.</p>
  <a href="{{ route('admin.sermons.create') }}" class="add-btn">+ Add a sermon</a>

  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif

  <div class="list">
    @forelse ($sermons as $s)
      <div class="row">
        <div class="date">{{ $s->preached_on->format('Y-m-d') }}</div>
        <div>
          <div class="title">{{ $s->title }}</div>
          <div class="meta">
            @if ($s->scripture_ref) {{ $s->scripture_ref }} ·@endif
            @if ($s->speaker) {{ $s->speaker }} ·@endif
            {{ $s->audioSizeHuman() }}
          </div>
        </div>
        <a href="{{ route('admin.sermons.edit', $s) }}" class="edit-btn">Edit</a>
        <form method="POST" action="{{ route('admin.sermons.destroy', $s) }}"
              data-confirm="Delete '{{ $s->title }}'? Audio file will also be removed.">
          @csrf @method('DELETE')
          <button type="submit" class="del-btn">Delete</button>
        </form>
      </div>
    @empty
      <div class="empty">No sermons yet. Click "Add a sermon" to upload the first.</div>
    @endforelse
  </div>
</main>
@include('partials._confirm')
</body>
</html>
