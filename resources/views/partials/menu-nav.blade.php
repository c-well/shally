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

@if ($mStyle === 'tiles')
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
