Hi {{ $first }}, here's what's new

{{ $intro }}

@foreach ($items as $it)
{{ strtoupper($it['tag']) }}: {{ $it['title'] }}
{{ $it['summary'] }}
@foreach ($it['steps'] ?? [] as $i => $s)
  {{ $i + 1 }}. {{ $s }}
@endforeach
@if (! empty($it['note'])){{ $it['note'] }}
@endif
@if (\App\Support\WhatsNew::link($it) && ! empty($it['cta'])){{ $it['cta'] }}: {{ \App\Support\WhatsNew::link($it) }}
@endif

@endforeach
All of this is in one place on the site: {{ $page }}

{{ $signoff }}
