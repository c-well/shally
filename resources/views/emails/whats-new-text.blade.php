Hi {{ $first }}, a few new things

{{ $intro }}

@foreach ($items as $it)
- {{ $it['title'] }}: {{ $it['short'] }}
@endforeach

How each one works: {{ $page }}

{{ $signoff }}
