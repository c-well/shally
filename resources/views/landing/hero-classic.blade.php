{{-- Front-page header: CLASSIC WELCOME, exactly as it was at commit 735b934
     (church label, italic headline, the admin's welcome paragraphs, John 3:16
     swapping in after 25 s). Kept so the church can switch back on its own in
     Admin > hub > "Front page header". --}}
<section class="hero">
  <div class="hero-eyebrow">{{ $heroPage?->eyebrow ?? "Shalom Seventh-day Adventist · Bronx, NY" }}</div>
  <h1>Welcome to <em>The Church of Peace</em>.</h1>
  <div class="lede-stage" id="lede-stage">
    <div class="lede lede-welcome">@if($heroPage && $heroPage->body_html){!! \Str::arrowize($heroPage->body_html) !!}@else<p><em>&quot;Where two or three gather in my name, there am I with them.&quot;</em> (Matt. 18:20) Every Sabbath, we gather at 3323 White Plains Road to do just that.</p>@endif</div>
    <div class="lede lede-verse" aria-hidden="true">
      <p class="verse-text">"For God so loved the world, that he gave his only begotten Son, that whosoever believeth in him should not perish, but have everlasting life."</p>
      <p class="verse-ref">— John 3:16 · this week's Sabbath School memory verse</p>
    </div>
  </div>
  @if ($isLive)
    {{-- LIVE — single primary CTA, bulletin + lesson demote to text links --}}
    <a href="#" class="hero-cta live-cta" data-live-cta>
      <span class="live-cta-dot" aria-hidden="true"></span>
      Watch the service · Live now
    </a>
    <div class="hero-live-links">
      <a href="{{ url('/welcome') }}">This Sabbath's bulletin</a>
      <span aria-hidden="true">·</span>
      <a href="{{ route('lesson.show') }}">Sabbath School lesson</a>
    </div>
  @elseif ($heroZoom)
    {{-- Hour of Prayer is LIVE right now (weekday dawn) — the moment leads.
         Steve rule: a button changes only when reality changes, and says so. --}}
    <a href="https://us02web.zoom.us/j/83002967327?pwd=dk13eXhDeUU1QjJ0TklqMjVtUWk0UT09" target="_blank" rel="noopener" class="hero-cta live-cta">
      <span class="live-cta-dot" aria-hidden="true"></span>
      {{ $heroZoom }} · happening now — join
    </a>
    <div class="hero-live-links">
      <a href="{{ url('/welcome') }}">This Sabbath's bulletin</a>
      <span aria-hidden="true">·</span>
      <a href="{{ route('lesson.show') }}">Sabbath School lesson</a>
    </div>
  @else
    {{-- DORMANT — normal two-button hero (unchanged) --}}
    <a href="{{ url('/welcome') }}" class="hero-cta">This Sabbath's bulletin @include('partials._ar')</a>
    <a href="{{ route('lesson.show') }}" class="hero-cta-secondary">Sabbath School lesson</a>
  @endif

  @if (($liveEvent ?? null) && ! $isLive)
    {{-- An event WE point to (not our stream) is on right now — e.g. the Crusade --}}
    <a href="{{ $liveEvent->stream_url }}" target="_blank" rel="noopener" class="hero-cta live-cta" style="margin-top: 12px;">
      <span class="live-cta-dot" aria-hidden="true"></span>
      {{ \Illuminate\Support\Str::limit($liveEvent->title, 42) }} · Happening now — tune in
    </a>
  @endif
  <div class="verse-toggle-wrap"><button class="verse-toggle hidden" id="verse-toggle" type="button"><span class="verse-toggle-dot"></span><span id="verse-toggle-text">Show verse of the day</span></button></div>
</section>

<script>
  (function() {
    const stage  = document.getElementById('lede-stage');
    const toggle = document.getElementById('verse-toggle');
    const txt    = document.getElementById('verse-toggle-text');
    if (!stage || !toggle) return;
    const SWAP_AFTER_MS = 25 * 1000;
    setTimeout(() => {
      stage.classList.add('swapped');
      toggle.classList.remove('hidden');
      txt.textContent = 'Show welcome message';
    }, SWAP_AFTER_MS);
    toggle.addEventListener('click', () => {
      const isVerse = stage.classList.toggle('swapped');
      txt.textContent = isVerse ? 'Show welcome message' : 'Show verse of the day';
    });
  })();
</script>
