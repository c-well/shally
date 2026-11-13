{{-- Front-page header: APPLE STACK. Title, one line, two buttons, big photo.
     Chosen in Admin > hub > "Front page header". Needs $isLive, $heroZoom,
     $slides, $liveEvent from landing.blade.php. --}}
<section class="apple-stack" aria-labelledby="hero-title">
  @php
    // The big photo is one of the admin's own slides (/admin/slides): the choir
    // shot when it is there, otherwise the first active slide. Remove every
    // slide and the hero simply has no photo.
    $heroPhoto = isset($slides) && count($slides)
        ? (collect($slides)->first(fn ($s) => str_contains((string) $s->image_path, 'tempimagem4iqpt')) ?? collect($slides)->first())
        : null;
  @endphp
  <div class="apple-stack__text">
    <h1 class="lay-title" id="hero-title">Welcome to The Church of Peace.</h1>
    <p class="lay-sub">Every Sabbath at 3323 White Plains Road, Bronx.</p>
    <div class="lay-actions">
    @if ($isLive)
      {{-- LIVE: watching leads, the bulletin stays one tap away --}}
      <a href="#" class="lay-btn" data-live-cta><span class="live-cta-dot" aria-hidden="true"></span>Watch the service live</a>
      <a href="{{ url('/welcome') }}" class="lay-btn lay-btn--ghost">This Sabbath’s bulletin</a>
    @elseif ($heroZoom)
      {{-- Hour of Prayer / Prayer Meeting is on right now. Steve rule: a
           button changes only when reality changes, and says so. --}}
      <a href="https://us02web.zoom.us/j/83002967327?pwd=dk13eXhDeUU1QjJ0TklqMjVtUWk0UT09" target="_blank" rel="noopener" class="lay-btn"><span class="live-cta-dot" aria-hidden="true"></span>{{ $heroZoom }} · Join now</a>
      <a href="{{ url('/welcome') }}" class="lay-btn lay-btn--ghost">This Sabbath’s bulletin</a>
    @else
      <a href="{{ url('/welcome') }}" class="lay-btn">This Sabbath’s bulletin</a>
      <a href="{{ route('lesson.show') }}" class="lay-btn lay-btn--ghost">Sabbath School lesson</a>
    @endif
    </div>
    @if (($liveEvent ?? null) && ! $isLive)
      {{-- An event WE point to (not our stream) is on right now, e.g. the Crusade --}}
      <div class="lay-actions">
        <a href="{{ $liveEvent->stream_url }}" target="_blank" rel="noopener" class="lay-btn"><span class="live-cta-dot" aria-hidden="true"></span>{{ \Illuminate\Support\Str::limit($liveEvent->title, 42) }} is on. Tune in</a>
      </div>
    @endif
  </div>
  @if ($heroPhoto)
    <img class="apple-stack__media" src="{{ $heroPhoto->imageUrl() }}" alt="{{ $heroPhoto->caption ?: 'Shalom church family on Sabbath' }}" width="1040" height="540" fetchpriority="high">
  @endif
</section>
