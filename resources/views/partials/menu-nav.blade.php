{{-- ENGINE-RENDERED PUBLIC NAV (app/Services/MenuConfig.php · studio at /admin/menu).
     Four styles, one config. Unknown routes skip silently — the menu can never 500. --}}
@php
  $mCfg = \App\Services\MenuConfig::get();
  $mStyle = $mCfg['style'];
  $mGroups = collect($mCfg['groups'])->map(function ($g) {
      $g['items'] = collect($g['items'] ?? [])
          ->filter(fn ($i) => empty($i['hidden']))
          ->map(function ($i) { $i['href'] = \App\Services\MenuConfig::href($i); return $i; })
          ->filter(fn ($i) => $i['href'])
          ->values()->all();
      return $g;
  })->filter(fn ($g) => count($g['items']))->values();
  $mExt = fn ($i) => ! empty($i['external']) ? 'target="_blank" rel="noopener"' : '';
@endphp

@if ($mStyle === 'clean')
  {{-- Apple flyout pattern, incl. drill-in: one screen shows 4 primaries + group names;
       tapping a group slides to its short list with a back chevron. No scroll. --}}
  <nav class="mn-clean" aria-label="Site">
    <div class="mn-clean-root">
      @foreach ($mGroups as $gk => $g)
        @if ($gk === 0)
          @foreach ($g['items'] as $ik => $i)
            @if (!empty($i['children']))
              <button type="button" class="mn-clean-link primary mn-drill" data-panel="mnp-i-{{ $gk }}-{{ $ik }}" aria-expanded="false">{{ $i['label'] }}@if (!empty($i['badge'])) <span class="mn-badge">{{ $i['badge'] }}</span>@endif<svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
            @else
              <a class="mn-clean-link primary" href="{{ $i['href'] }}" {!! $mExt($i) !!}>{{ $i['label'] }}@if (!empty($i['badge'])) <span class="mn-badge">{{ $i['badge'] }}</span>@endif</a>
            @endif
          @endforeach
        @else
          <button type="button" class="mn-clean-link primary mn-drill" data-panel="mnp-{{ $gk }}" aria-expanded="false">{{ $g['label'] ?: 'More' }}<svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
        @endif
      @endforeach
    </div>
    @foreach ($mGroups as $gk => $g)
      @if ($gk === 0)
        @foreach ($g['items'] as $ik => $i)
          @if (!empty($i['children']))
            <div class="mn-panel" id="mnp-i-{{ $gk }}-{{ $ik }}" hidden>
              <button type="button" class="mn-back" aria-label="Back to menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
              <div class="mn-clean-lab">{{ $i['label'] }}</div>
              @if (!empty($i['href']))
                <a class="mn-clean-link primary" href="{{ $i['href'] }}" {!! $mExt($i) !!}>{{ $i['label'] }}</a>
              @endif
              @foreach ($i['children'] as $c)
                @php $c['href'] = \App\Services\MenuConfig::href($c); @endphp
                @if ($c['href'])
                  <a class="mn-clean-link primary" href="{{ $c['href'] }}" {!! $mExt($c) !!}>{{ $c['label'] }}@if (!empty($c['badge'])) <span class="mn-badge">{{ $c['badge'] }}</span>@endif</a>
                @endif
              @endforeach
            </div>
          @endif
        @endforeach
      @else
        <div class="mn-panel" id="mnp-{{ $gk }}" hidden>
          <button type="button" class="mn-back" aria-label="Back to menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
          <div class="mn-clean-lab">{{ $g['label'] }}</div>
          @foreach ($g['items'] as $i)
            <a class="mn-clean-link primary" href="{{ $i['href'] }}" {!! $mExt($i) !!}>{{ $i['label'] }}@if (!empty($i['badge'])) <span class="mn-badge">{{ $i['badge'] }}</span>@endif</a>
          @endforeach
        </div>
      @endif
    @endforeach
  </nav>
  <script>
  (function () {
    const nav = document.currentScript.previousElementSibling;
    const root = nav.querySelector('.mn-clean-root');
    nav.querySelectorAll('.mn-drill').forEach(b => b.addEventListener('click', () => {
      const p = nav.querySelector('#' + b.dataset.panel);
      root.classList.add('away'); p.hidden = false;
      requestAnimationFrame(() => p.classList.add('in'));
      b.setAttribute('aria-expanded', 'true');
    }));
    nav.querySelectorAll('.mn-back').forEach(b => b.addEventListener('click', () => {
      const p = b.closest('.mn-panel');
      p.classList.remove('in'); root.classList.remove('away');
      setTimeout(() => { p.hidden = true; }, 260);
      nav.querySelectorAll('.mn-drill[aria-expanded="true"]').forEach(d => d.setAttribute('aria-expanded', 'false'));
    }));
  })();
  </script>

@elseif ($mStyle === 'tiles')
  @php $tileItems = $mGroups->flatMap(fn ($g) => $g['items'])->take(4); $restGroups = $mGroups; @endphp
  <div class="mn-tiles">
    @foreach ($tileItems as $k => $i)
      <a class="mn-tile {{ $k === 0 ? 'hero' : '' }}" href="{{ $i['href'] }}" {!! $mExt($i) !!}>
        <span class="t">{{ $i['label'] }}</span>
        @if (!empty($i['badge']))<span class="mn-badge">{{ $i['badge'] }}</span>@endif
        <span class="arr">@include('partials._ar')</span>
      </a>
    @endforeach
  </div>
  @foreach ($restGroups as $gk => $g)
    @php $items = collect($g['items'])->when($gk === 0, fn ($c) => $c->slice(4))->values(); @endphp
    @if ($items->count())
      @if ($g['label'])<div class="mn-grouplab">{{ $g['label'] }}</div>@endif
      @foreach ($items as $i)
        <a class="site-menu-sub-link mn-row" href="{{ $i['href'] }}" {!! $mExt($i) !!}>{{ $i['label'] }}@if (!empty($i['badge'])) <span class="mn-badge">{{ $i['badge'] }}</span>@endif</a>
      @endforeach
    @endif
  @endforeach

@elseif ($mStyle === 'grouped' || $mStyle === 'today')
  @if ($mStyle === 'today')
    @php
      $mToday = \Illuminate\Support\Facades\Cache::remember('menu_today_card', 300, function () {
          $b = \App\Models\Bulletin::activeForNow();
          $live = \App\Models\Event::happeningNow();
          return [
              'date' => now('America/New_York')->format('l · F j'),
              'line' => $b ? (($b->kind === 'event_night' && $b->event_name) ? $b->event_name : 'Sabbath Worship') . ($b->service_time ? ' · ' . ucfirst($b->service_time) : '') : null,
              'live' => $live?->title,
              'liveUrl' => $live?->stream_url,
          ];
      });
    @endphp
    <div class="mn-today">
      <div class="lab">{{ $mToday['date'] }}</div>
      @if ($mToday['line'])<div class="big">{{ $mToday['line'] }}</div>@endif
      @if ($mToday['live'])<a class="mn-live" href="{{ $mToday['liveUrl'] }}" target="_blank" rel="noopener">● {{ \Illuminate\Support\Str::limit($mToday['live'], 34) }} — live</a>@endif
    </div>
  @endif
  @foreach ($mGroups as $g)
    @if ($g['label'])<div class="mn-grouplab">{{ $g['label'] }}</div>@endif
    @foreach ($g['items'] as $i)
      <a class="site-menu-link mn-butter" href="{{ $i['href'] }}" {!! $mExt($i) !!}>{{ $i['label'] }}
        @if (!empty($i['badge']))<span class="mn-badge">{{ $i['badge'] }}</span>@else<span class="arrow">@include('partials._ar')</span>@endif
      </a>
    @endforeach
  @endforeach

@else {{-- classic: collapsible sections, the original anatomy --}}
  @foreach ($mGroups as $g)
    @if (!empty($g['collapsible']) && $g['label'])
      <div class="site-menu-section">
        <button class="site-menu-section-toggle" type="button" aria-expanded="false">
          {{ $g['label'] }}
          <svg class="chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="site-menu-section-body">
          <div class="site-menu-sub-list">
            @foreach ($g['items'] as $i)
              <a class="site-menu-sub-link" href="{{ $i['href'] }}" {!! $mExt($i) !!}>{{ $i['label'] }}@if (!empty($i['badge'])) <span class="mn-badge">{{ $i['badge'] }}</span>@endif</a>
            @endforeach
          </div>
        </div>
      </div>
    @else
      @foreach ($g['items'] as $i)
        <a class="site-menu-link" href="{{ $i['href'] }}" {!! $mExt($i) !!}>{{ $i['label'] }}
          @if (!empty($i['badge']))<span class="mn-badge">{{ $i['badge'] }}</span>@else<span class="arrow">@include('partials._ar')</span>@endif
        </a>
      @endforeach
    @endif
  @endforeach
@endif
