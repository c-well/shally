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
  main { max-width: 680px; margin: 0 auto; padding: clamp(36px, 6vh, 64px) clamp(18px, 5vw, 32px) 80px; }
  .lede { margin-top: 10px; font-size: 15px; color: var(--ink-soft); }

  /* One row per feature. Closed it is a title and one line; open it shows the rest. */
  .list { margin-top: 26px; background: #fff; border: 1px solid var(--line); border-radius: 14px; overflow: hidden; }
  details + details { border-top: 1px solid var(--line); }
  summary { list-style: none; cursor: pointer; display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 14px; align-items: center; padding: 16px 18px; }
  summary::-webkit-details-marker { display: none; }
  summary:hover { background: color-mix(in srgb, var(--parchment) 60%, #fff); }
  summary:focus-visible { outline: 2px solid var(--teal); outline-offset: -2px; }
  .dot { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; }
  .dot svg { width: 17px; height: 17px; }
  .t { font-size: 16px; font-weight: 600; line-height: 1.3; }
  .s { margin-top: 2px; font-size: 14px; color: var(--ink-soft); line-height: 1.4; }
  .chev { width: 18px; height: 18px; color: var(--ink-soft); transition: transform .2s; }
  details[open] .chev { transform: rotate(180deg); }
  @media (prefers-reduced-motion: reduce) { .chev { transition: none; } }

  .more { padding: 0 18px 20px calc(18px + 34px + 14px); }
  .more p { font-size: 15px; line-height: 1.6; }
  .steps { margin-top: 12px; display: grid; gap: 8px; list-style: none; counter-reset: s; }
  .steps li { position: relative; padding-left: 26px; font-size: 14.5px; line-height: 1.5; counter-increment: s; }
  .steps li::before { content: counter(s) "."; position: absolute; left: 0; font-weight: 700; color: var(--ink-soft); }
  .note { margin-top: 10px; font-size: 13.5px; color: var(--ink-soft); }
  .go { margin-top: 14px; display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; border-radius: 8px; font-size: 14px; font-weight: 600; color: #fff; text-decoration: none; }
  @media (max-width: 520px) { .more { padding-left: 18px; } }
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
  <p class="lede">Tap any one for how it works.</p>

  @php
    $icons = [
      'music'    => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
      'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
      'bulletin' => '<path d="M6 3h9l3 3v15H6z"/><path d="M9 11h6M9 15h6"/>',
      'admin'    => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
    ];
  @endphp
  <div class="list">
    @foreach ($items as $it)
      @php [$fg, $bg] = \App\Support\WhatsNew::colours($it['area']); $link = \App\Support\WhatsNew::link($it); @endphp
      <details id="{{ $it['key'] }}">
        <summary>
          <span class="dot" style="color: {{ $fg }}; background: {{ $bg }};"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$it['area']] ?? $icons['admin'] !!}</svg></span>
          <span><span class="t">{{ $it['title'] }}</span><span class="s" style="display:block">{{ $it['short'] }}</span></span>
          <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </summary>
        <div class="more">
          <p>{{ $it['summary'] }}</p>
          @if (! empty($it['steps']))
            <ol class="steps">@foreach ($it['steps'] as $s)<li>{{ $s }}</li>@endforeach</ol>
          @endif
          @if (! empty($it['note']))<p class="note">{{ $it['note'] }}</p>@endif
          @if ($link && ! empty($it['cta']))
            <a class="go" href="{{ $link }}" style="background: {{ $fg }};">{{ $it['cta'] }} &rarr;</a>
          @endif
        </div>
      </details>
    @endforeach
  </div>
</main>
<script>
/* Arriving from the email on #music-day opens that one and brings it into view. */
(function () {
  function openHash() {
    var d = location.hash && document.getElementById(location.hash.slice(1));
    if (d && d.tagName === 'DETAILS') { d.open = true; d.scrollIntoView({ block: 'center' }); }
  }
  openHash(); window.addEventListener('hashchange', openHash);
})();
</script>
</body>
</html>
