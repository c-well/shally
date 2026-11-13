{{-- One page of the web booklet. $page: cover | left | right | back. $L comes from App\Support\BookletLayout.
     Sizes are in --u (one printed point on a 5.5in page), so the screen copy keeps the print proportions. --}}
@if ($page === 'cover')
  <div class="pg-in bk-cover">
    <div class="bk-logo" role="img" aria-label="Shalom Seventh-day Adventist Church">shalom</div>
    @if ($L['emblems'])
      <div class="bk-emblems">
        @foreach ($L['emblems'] as $e)<img src="{{ asset('img/emblems/' . $e) }}" alt="" draggable="false">@endforeach
      </div>
    @else
      <div style="height: calc(var(--u) * 40);"></div>
    @endif
    <div class="bk-kicker">{{ $L['kicker'] }}</div>
    <div class="bk-title">{{ $L['title'] }}</div>
    <div class="bk-date">{{ $L['date']->format('F j, Y') }}</div>
    @if (! empty($L['cover']['theme']))
      <div class="bk-rule"></div>
      <div class="bk-theme">&ldquo;{{ $L['cover']['theme'] }}&rdquo;</div>
      @if (! empty($L['cover']['theme_ref']))<div class="bk-ref">{{ \App\Support\BookletLayout::linkify($L['cover']['theme_ref']) }}</div>@endif
    @endif
    <div class="bk-addr">3323 White Plains Rd, Bronx, NY 10467<br>thechurchofpeace.org</div>
  </div>

@elseif ($page === 'left' || $page === 'right')
  @php $rows = $L['halves'][$page === 'left' ? 0 : 1]; @endphp
  <div class="pg-in bk-program" style="--pt: {{ $L['pt'] }}; --gap: {{ $L['gap'] }};">
    @if ($page === 'left')
      <div class="bk-ph">Order of service</div>
      <div class="bk-ph-sub">{{ $L['date']->format('l, F j, Y') }}</div>
    @else
      <div class="bk-ph" aria-hidden="true">&nbsp;</div><div class="bk-ph-sub" aria-hidden="true">&nbsp;</div>
    @endif
    @foreach ($rows as $line)
      @if (($line['kind'] ?? 'line') === 'section_header')
        <div class="bk-under">{{ \App\Support\BookletLayout::linkify((string) ($line['section'] ?? '')) }}</div>
      @else
        @php $part = trim((string) ($line['part'] ?? '')); $person = trim((string) ($line['person'] ?? '')); @endphp
        @if ($part !== '')
          <div class="bk-row">
            <span class="bk-part">{{ $part }}</span><span class="bk-dots"></span><span class="bk-who{{ \App\Support\BookletLayout::isGroup($person) ? ' grp' : '' }}">{{ \App\Support\BookletLayout::linkify($person) }}</span>
          </div>
        @endif
      @endif
    @endforeach
  </div>

@else
  <div class="pg-in bk-back">
    <div class="bk-back-title">Announcements</div>
    @foreach ($L['announcements'] as $a)
      @php
        $aTitle = trim((string) ($a['title'] ?? '')); $aDetail = trim((string) ($a['detail'] ?? ''));
        $tLower = strtolower($aTitle); $head = rtrim($aTitle, ':');
      @endphp
      @if ($aTitle === '')
        @continue
      @elseif (str_contains($tLower, 'mission'))
        <section class="bk-mission"><div class="bk-sec">{{ $head }}</div><p>{{ $aDetail }}</p></section>
      @elseif (str_contains($tLower, 'offering'))
        <section class="bk-offer"><div class="bk-sec">{{ $head }}</div>
          @foreach (preg_split("/\r?\n/", $aDetail) as $l)@if (trim($l) !== '')<div>{{ trim($l) }}</div>@endif @endforeach
        </section>
      @else
        <section class="bk-ann"><div class="bk-sec">{{ $head }}</div>
          @if ($aDetail !== '')
            <ul>
              @foreach (preg_split("/\r?\n/", $aDetail) as $b)
                @php $b = trim($b); $bf = str_starts_with($b, '•'); @endphp
                @if ($b !== '')<li @if ($bf) class="bf" @endif>{{ $bf ? ltrim(substr($b, strlen('•'))) : $b }}</li>@endif
              @endforeach
            </ul>
          @endif
        </section>
      @endif
    @endforeach
    <div class="bk-pleasant">Have a pleasant Sabbath :)</div>
    <button type="button" class="bk-ask" data-ask>How was your Sabbath? Tell us <svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button>
  </div>
@endif
