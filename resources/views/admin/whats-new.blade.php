<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>What's new — Admin · The Church of Peace</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top .meta { font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 720px; margin: 0 auto; padding: clamp(40px, 7vh, 72px) clamp(20px, 5vw, 32px) 96px; }
  .lede { margin-top: 14px; font-size: 16px; line-height: 1.65; color: var(--ink-soft); max-width: 540px; }
  .list { margin-top: 36px; display: grid; gap: 18px; }
  .item { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 24px 24px 22px; }
  .head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .tag { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; }
  .when { font-size: 12.5px; color: var(--ink-soft); }
  .item h2 { margin-top: 14px; font-size: 22px !important; line-height: 1.25; letter-spacing: -0.01em; }
  .sum { margin-top: 10px; font-size: 15.5px; line-height: 1.65; color: var(--ink); }
  .steps { margin-top: 16px; padding: 16px 18px; background: var(--parchment); border-radius: 10px; display: grid; gap: 10px; counter-reset: s; list-style: none; }
  .steps li { position: relative; padding-left: 32px; font-size: 14.5px; line-height: 1.55; counter-increment: s; }
  .steps li::before { content: counter(s); position: absolute; left: 0; top: 0; width: 22px; height: 22px; border-radius: 999px; background: #fff; border: 1px solid var(--line); display: grid; place-items: center; font-size: 12px; font-weight: 700; color: var(--ink-soft); }
  .note { margin-top: 14px; font-size: 13.5px; color: var(--ink-soft); line-height: 1.55; }
  .go { margin-top: 18px; display: inline-flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; color: #fff; text-decoration: none; }
  .go svg { width: 15px; height: 15px; }
  .foot { margin-top: 34px; font-size: 14px; color: var(--ink-soft); line-height: 1.6; }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')
<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">what's new</span>
</header>
<main>
  <h1>What's new.</h1>
  <p class="lede">What has been added to the church site lately, what each one does, and where to find it.</p>

  <div class="list">
    @foreach ($items as $it)
      @php [$fg, $bg] = \App\Support\WhatsNew::colours($it['area']); $link = \App\Support\WhatsNew::link($it); @endphp
      <article class="item" id="{{ $it['key'] }}">
        <div class="head">
          <span class="tag" style="color: {{ $fg }}; background: {{ $bg }};">{{ $it['tag'] }}</span>
          <span class="when">{{ \Carbon\Carbon::parse($it['date'])->format('F j') }}</span>
        </div>
        <h2>{{ $it['title'] }}</h2>
        <p class="sum">{{ $it['summary'] }}</p>
        @if (! empty($it['steps']))
          <ol class="steps">
            @foreach ($it['steps'] as $s)<li>{{ $s }}</li>@endforeach
          </ol>
        @endif
        @if (! empty($it['note']))<p class="note">{{ $it['note'] }}</p>@endif
        @if ($link && ! empty($it['cta']))
          <a class="go" href="{{ $link }}" style="background: {{ $fg }};">{{ $it['cta'] }}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></a>
        @endif
      </article>
    @endforeach
  </div>

  <p class="foot">This page is always under Admin, then What's new.</p>
</main>
</body>
</html>
