<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
@php $topicName = \Illuminate\Support\Str::title($topic->name); @endphp
@include('partials.seo-head', [
    'title'       => 'Messages on ' . $topicName . ' · Finding Peace · Shalom SDA Church',
    'description' => 'What scripture says about ' . strtolower($topic->name)
        . ' — messages, questions and answers from Shalom Seventh-day Adventist Church in the Bronx.',
    'path'        => '/find-peace/topic/' . $topic->slug,
])
<script type="application/ld+json">{!! \App\Support\Ld::json([
  '@type'       => 'CollectionPage',
  'name'        => 'Messages on ' . $topicName,
  'url'         => url('/find-peace/topic/' . $topic->slug),
  'about'       => ['@type' => 'Thing', 'name' => $topicName],
  'isPartOf'    => ['@type' => 'CollectionPage', 'name' => 'Finding Peace', 'url' => url('/find-peace')],
  'publisher'   => ['@type' => 'Organization', 'name' => 'The Church of Peace', 'url' => url('/')],
]) !!}</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Poppins', -apple-system, sans-serif;
    background: var(--bg); color: var(--ink);
    line-height: 1.7; font-weight: 300;
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    padding: 4rem 1.5rem;
  }
  .container { max-width: 720px; margin: 0 auto; }
  .breadcrumb { font-size: 0.75rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-faint); font-weight: 500; }
  .breadcrumb a { color: var(--brass); text-decoration: none; }
  .breadcrumb a:hover { color: var(--brass-bright); }
  h1 { font-size: clamp(2rem, 5vw, 2.75rem); font-weight: 600; line-height: 1.15; margin: 0.75rem 0 0.5rem; text-transform: capitalize; }
  .lede { color: var(--ink-soft); font-size: 1rem; margin-bottom: 3rem; max-width: 50ch; }
  .sermon-list { list-style: none; }
  .sermon-list li { border-bottom: 1px solid var(--line); padding: 1.5rem 0; }
  .sermon-list li:last-child { border-bottom: none; }
  .sermon-list a { color: var(--ink); text-decoration: none; display: block; transition: padding-left 0.2s; }
  .sermon-list a:hover { padding-left: 0.5rem; }
  .sermon-meta { font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 0.7rem; letter-spacing: 0.14em; color: var(--ink-faint); text-transform: uppercase; margin-bottom: 0.4rem; }
  .sermon-title { font-size: 1.2rem; font-weight: 500; margin-bottom: 0.5rem; }
  .sermon-list a:hover .sermon-title { color: var(--brass-bright); }
  .sermon-heart { color: var(--ink-soft); font-size: 0.95rem; }
  .empty { padding: 4rem 0; text-align: center; color: var(--ink-soft); }
  .footer-link { display: inline-block; margin-top: 3rem; font-size: 0.85rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--brass); text-decoration: none; font-weight: 500; }
  .footer-link:hover { color: var(--brass-bright); }
</style>
</head>
<body>
  <div class="container">
    <div class="breadcrumb"><a href="{{ route('find-peace.index') }}">Finding Peace</a> · Topic</div>
    <h1>{{ $topic->name }}</h1>
    <p class="lede">{{ $sermons->count() }} {{ Str::plural('message', $sermons->count()) }} that touch on {{ $topic->name }}.</p>

    @if($sermons->isNotEmpty())
      <ul class="sermon-list">
        @foreach($sermons as $s)
          <li>
            <a href="{{ route('find-peace.show', $s->slug) }}">
              <div class="sermon-meta">{{ strtoupper($s->sermon_date->format('M j, Y')) }} @if($s->speaker) · {{ $s->speaker }} @endif</div>
              <div class="sermon-title">{{ $s->title }}</div>
              @if($s->heart_line)
                <div class="sermon-heart">{{ \Illuminate\Support\Str::limit($s->heart_line, 180) }}</div>
              @endif
            </a>
          </li>
        @endforeach
      </ul>
    @else
      <div class="empty">No messages on this topic yet.</div>
    @endif

    <a href="{{ route('find-peace.index') }}" class="footer-link">@include('partials._arl') All of Finding Peace</a>
  </div>
</body>
</html>
