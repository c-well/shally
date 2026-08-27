{{-- Shared by the edit and add forms so the two can never drift apart.
     $s is a ServiceTime, or null when adding. --}}
@php
  $days = old('days', $s?->days ?? []);
  $days = array_map('intval', is_array($days) ? $days : []);
@endphp

<div class="row2">
  <div class="field">
    <label>Name</label>
    <input type="text" name="name" value="{{ old('name', $s?->name) }}" maxlength="80" required placeholder="Prayer Meeting">
  </div>
  <div class="field">
    <label>Time as it reads <span class="sub">Shown exactly as typed.</span></label>
    <input type="text" name="when_label" value="{{ old('when_label', $s?->when_label) }}" maxlength="60" required placeholder="WED · 7:00 PM">
  </div>
</div>

<div class="row2">
  <div class="field">
    <label>Where</label>
    <input type="text" name="where_label" value="{{ old('where_label', $s?->where_label ?? 'In person') }}" maxlength="40" required>
  </div>
  <div class="field">
    <label>Zoom link <span class="sub">Leave empty for in-person.</span></label>
    <input type="url" name="zoom_url" value="{{ old('zoom_url', $s?->zoom_url) }}" placeholder="https://us02web.zoom.us/j/…">
  </div>
</div>

<div class="field">
  <label>Which days it happens</label>
  <div class="days">
    @foreach (\App\Models\ServiceTime::DAY_NAMES as $n => $label)
      <label><input type="checkbox" name="days[]" value="{{ $n }}" @checked(in_array($n, $days, true))> {{ $label }}</label>
    @endforeach
  </div>
</div>

<div class="row2">
  <div class="field">
    <label>Takes over the section from
      <span class="sub">Start a little early — the card reads “Happening now”.</span>
    </label>
    <input type="time" name="live_from" value="{{ old('live_from', $s?->live_from ? substr($s->live_from, 0, 5) : '') }}">
  </div>
  <div class="field">
    <label>…until
      <span class="sub">Leave both empty and it never takes over.</span>
    </label>
    <input type="time" name="live_until" value="{{ old('live_until', $s?->live_until ? substr($s->live_until, 0, 5) : '') }}">
  </div>
</div>

<div class="field">
  <label class="check" style="text-transform:none;letter-spacing:0;font-weight:500">
    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $s?->is_published ?? true))>
    Show this on the home page
  </label>
</div>
