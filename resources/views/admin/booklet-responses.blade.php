<!doctype html>
<html lang="en">
<head>
@include('admin.peace-news._head')
<title>Sabbath feedback · Admin</title>
<style>
  .tally { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: 12px; }
  .bar { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 4px 10px; align-items: center; font-size: 14px; margin: 8px 0 0; }
  .bar i { grid-column: 1 / -1; display: block; height: 6px; border-radius: 99px; background: color-mix(in srgb, var(--teal) 14%, transparent); overflow: hidden; }
  .bar i b { display: block; height: 100%; background: var(--teal); border-radius: 99px; }
  .chip { display: inline-block; padding: 2px 9px; margin: 0 6px 6px 0; border-radius: 6px; background: #f3efe6; font-size: 13px; }
</style>
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')
<div class="top"><a href="{{ route('admin.hub') }}">‹ Admin</a></div>
<main>
  <h1>Sabbath feedback</h1>
  <p class="lede">Answers to “How was your Sabbath?” from under the booklet on the site. Only church admins see this page.</p>

  @forelse ($groups as $g)
    @php $b = $g['bulletin']; $n = $g['rows']->count(); @endphp
    <h2>{{ $b->cover['title'] ?? $b->title }} · {{ $b->service_date->format('M j') }} <span class="muted" style="font-weight: 400;">({{ $n }} {{ \Illuminate\Support\Str::plural('person', $n) }})</span></h2>

    <div class="tally">
      @foreach (['spoke', 'joined'] as $k)
        <div class="card" style="margin: 0;">
          <strong>{{ $g['q'][$k]['q'] }}</strong>
          @forelse ($g['tally'][$k] as $opt => $c)
            <div class="bar"><span>{{ $opt }}</span><span class="muted">{{ $c }}</span><i><b style="width: {{ round($c / max(1, $n) * 100) }}%"></b></i></div>
          @empty
            <p class="muted" style="margin: 8px 0 0;">No ticks yet.</p>
          @endforelse
        </div>
      @endforeach
    </div>

    @foreach ($g['rows'] as $r)
      @if ($r->story || $r->first_name)
        <div class="card" style="margin-top: 12px;">
          @if ($r->story)<p style="margin: 0 0 10px; font-size: 16px; line-height: 1.6; white-space: pre-line;">{{ $r->story }}</p>@endif
          <div>@foreach (array_merge((array) ($r->picks['spoke'] ?? []), (array) ($r->picks['joined'] ?? [])) as $p)<span class="chip">{{ $p }}</span>@endforeach</div>
          <span class="muted">{{ $r->first_name ?: 'No name' }} · {{ $r->created_at->timezone('America/New_York')->format('D M j, g:i A') }}</span>
        </div>
      @endif
    @endforeach
  @empty
    <p class="muted">Nothing yet. Answers show up here once people use the booklet on the site.</p>
  @endforelse
</main>
</body>
</html>
