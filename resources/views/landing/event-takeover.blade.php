{{-- EVENT TAKEOVER (Layouts shelf: cwell-bible/layouts/event-takeover).
     The dark block under the header in an event week. Everything comes from
     the event in the calendar (title, date, time, flyer), so nobody edits this
     page, and it disappears on its own the day after the event.
     Off switch: Admin hub > "Event block" (setting event_takeover). --}}
@php
  $etOn = \App\Models\AppSetting::get('event_takeover', '1') === '1';
  $etNy = now('America/New_York');
  $et = $etOn ? \App\Models\Event::where('is_public', true)
      ->whereNotNull('start_at')
      ->whereBetween('start_at', [$etNy->copy()->startOfDay(), $etNy->copy()->addDays(7)])
      ->orderBy('start_at')
      ->first() : null;
  if ($et) {
      $etAt    = $et->start_at->copy()->timezone('America/New_York');
      $etDay   = $etAt->isToday() ? 'Today' : ($etAt->isTomorrow() ? 'Tomorrow' : 'This ' . $etAt->format('l'));
      $etTime  = $etAt->minute === 0 ? $etAt->format('g A') : $etAt->format('g:i A');
      // Display only: tidy the spacing around a slash and let it break there.
      $etTitle = str_replace('/', '/<wbr>', e(preg_replace('/\s*\/\s*/', '/', trim($et->title))));
  }
@endphp
@if ($et)
<style>
  .event-takeover {
    background: #2a1f3a; color: #fff;
    display: grid; grid-template-columns: minmax(0, 560px) minmax(0, 400px);
    justify-content: center; align-items: center; gap: clamp(48px, 7vw, 96px);
    padding: clamp(72px, 9vw, 112px) clamp(20px, 6vw, 96px);
    text-align: center; overflow: hidden;
  }
  .event-takeover.no-flyer { grid-template-columns: minmax(0, 720px); }
  .event-takeover .lay-title { color: #fff; }
  .event-takeover .lay-sub { color: rgba(255,255,255,.78); }
  .event-takeover .lay-btn { background: #fff; color: var(--ink); border-color: #fff; }
  .event-takeover .lay-btn:hover { background: #efe8f7; border-color: #efe8f7; color: var(--ink); }
  .event-takeover .lay-btn--ghost { background: transparent; color: #fff; border-color: rgba(255,255,255,.6); }
  .event-takeover .lay-btn--ghost:hover { background: rgba(255,255,255,.08); color: #fff; border-color: #fff; }
  .event-takeover .lay-btn:focus-visible { outline-color: #fff; }
  .event-takeover__flyer { display: block; width: 100%; height: auto; border-radius: 8px; box-shadow: 0 30px 60px -20px rgba(0,0,0,.6); }
  @media (max-width: 760px) {
    .event-takeover { grid-template-columns: minmax(0, 1fr); padding-bottom: 0; }
    .event-takeover__flyer { max-width: 300px; margin: 0 auto -48px; }
    .event-takeover.no-flyer { padding-bottom: 72px; }
  }
  @supports (animation-timeline: view()) {
    @media (prefers-reduced-motion: no-preference) {
      .event-takeover > * { animation: etRise linear both; animation-timeline: view(); animation-range: entry 0% entry 45%; }
    }
  }
  @keyframes etRise { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: none; } }
</style>
<section class="event-takeover {{ $et->flyer_path ? '' : 'no-flyer' }}" aria-labelledby="et-title">
  <div>
    <h2 class="lay-title" id="et-title">{!! $etTitle !!}</h2>
    <p class="lay-sub">{{ $etDay }}, {{ $etAt->format('M j') }} at {{ $etTime }}.<br>Everyone is welcome.</p>
    <div class="lay-actions">
      <a href="{{ route('calendar') }}" class="lay-btn">See the calendar</a>
      <a href="{{ url('/visit') }}" class="lay-btn lay-btn--ghost">Plan your visit</a>
    </div>
  </div>
  @if ($et->flyer_path)
    <img class="event-takeover__flyer" src="/{{ $et->flyer_path }}" alt="Flyer for {{ $et->title }}" width="400" height="500" loading="lazy">
  @endif
</section>
@endif
