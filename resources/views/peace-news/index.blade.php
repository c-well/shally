<!doctype html>
<html lang="en">
<head>
@include('peace-news._head')
@include('partials.seo-head', [
  'title'       => 'Peace Notes News · The Church of Peace',
  'description' => 'Every Sunday morning: last Sabbath’s message in two minutes, and a question to carry with you.',
  'path'        => '/peace-notes/news',
])
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')
<main>
  <section style="padding: clamp(56px, 8vw, 104px) 0 24px; text-align: center;">
    <div class="pn-col">
      <h1 class="pn-display">Peace Notes News</h1>
      <p class="pn-sub">Every Sunday morning: last Sabbath’s message in two minutes, and a question to carry with you.</p>
    </div>
  </section>

  <section style="padding: 24px 0 clamp(40px, 6vw, 72px);">
    <div class="pn-col" style="display: flex; flex-direction: column; gap: 14px;">
      @forelse ($issues as $i)
        <a href="{{ route('peace.news.show', $i->slug) }}" style="display: block; background: #fff; border-radius: 14px; padding: 22px 24px; text-decoration: none; color: var(--ink);">
          <span style="display: block; font-size: 13px; font-weight: 500; color: var(--teal);">{{ $i->send_after->timezone('America/New_York')->format('F j, Y') }} · {{ $i->sermon->speaker }}</span>
          <span style="display: block; margin-top: 6px; font-size: 22px; font-weight: 600; letter-spacing: -0.02em;">{{ $i->sermon->title }}</span>
          @if (! empty($i->questions[0]))<span style="display: block; margin-top: 8px; font-weight: 300; font-size: 16px; line-height: 1.5; color: #4a5568;">{{ $i->questions[0] }}</span>@endif
        </a>
      @empty
        <p style="text-align: center; font-weight: 300; font-size: 18px; color: #4a5568;">The first issue goes out this Sunday morning.</p>
      @endforelse
      {{ $issues->links() }}
    </div>
  </section>

  @include('peace.partials.news-signup')
</main>
@include('partials.footer')
@include('partials._event-tracker')
</body>
</html>
