<div class="ev {{ $e->is_public ? '' : 'hidden-ev' }}" data-ev-id="{{ $e->id }}">
  <div class="ev-tile">
    <div class="m">{{ strtoupper($e->start_at->format('M')) }}</div>
    <div class="d">{{ $e->start_at->format('j') }}</div>
    <div class="w">{{ strtoupper($e->start_at->format('D')) }}</div>
  </div>
  <div class="ev-main">
    <div class="ev-title">{{ $e->title }}</div>
    @php
      $meta = $e->start_at->format('l, M j, Y');
      if ($e->start_at->format('H:i') !== '00:00') $meta .= ' · ' . $e->start_at->format('g:i A');
      if ($e->location) $meta .= ' · ' . $e->location;
    @endphp
    <div class="ev-meta">{{ $meta }}</div>
    <div class="ev-actions">
      <button class="mini {{ $e->is_public ? 'on' : '' }}" type="button" data-toggle-live="{{ $e->is_public ? '0' : '1' }}">{{ $e->is_public ? 'On the website' : 'Hidden — tap to show' }}</button>
      @if($e->flyer_path)<button class="mini" type="button" data-remove-flyer>Remove flyer</button>@endif
      <button class="mini danger" type="button" data-delete data-title="{{ $e->title }}">Delete</button>
    </div>
  </div>
  @if($e->flyer_path)<div class="ev-thumb" style="background-image:url('/{{ $e->flyer_path }}')"></div>@endif
</div>
