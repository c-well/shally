<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;padding:0;background:#eceaf0;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eceaf0;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;">
  <tr><td style="background:#2a1f3a;color:#ffffff;padding:22px 28px;">
    <div style="font-size:13px;opacity:.8;">For Andre and KC · first approval sends</div>
    <div style="font-size:21px;font-weight:600;margin-top:6px;">Sunday’s Peace Notes News is ready</div>
    <div style="font-size:15px;opacity:.85;margin-top:4px;">Goes out {{ $issue->send_after->isPast() ? 'as soon as one of you approves' : $issue->send_after->timezone('America/New_York')->format('l g:i A') . ' if one of you approves' }}. Two minutes to read.</div>
  </td></tr>

  <tr><td style="padding:22px 28px 6px;">
    <a href="{{ $reviewUrl }}" style="display:block;text-align:center;background:#1f6b45;color:#ffffff;text-decoration:none;font-size:16px;font-weight:600;padding:14px 0;border-radius:6px;">Review and approve</a>
    <div style="text-align:center;font-size:14px;margin-top:10px;"><a href="{{ $editUrl }}" style="color:#6b4d8a;">Edit it first</a></div>
  </td></tr>

  <tr><td style="padding:18px 28px 0;">
    <div style="font-size:13px;color:#6b4d8a;font-weight:600;">{{ $sermon->speaker }} · Sabbath, {{ \Illuminate\Support\Carbon::parse($sermon->sermon_date)->format('M j') }}</div>
    <div style="font-family:Georgia,serif;font-size:32px;line-height:1.1;margin-top:6px;">{{ $sermon->title }}</div>
    @if ($sermon->heart_line)<div style="font-size:16px;line-height:1.5;margin-top:8px;color:#334455;">{{ $sermon->heart_line }}</div>@endif
  </td></tr>

  @foreach ([['How it opens', $issue->opening_summary, $issue->opening_quote, $issue->opening_seconds, 'Listen to the opening'], ['How it closes', $issue->closing_summary, $issue->closing_quote, $issue->closing_seconds, 'Listen to the close']] as [$label, $sum, $quote, $sec, $cta])
  <tr><td style="padding:14px 28px 0;">
    <div style="background:#f5f0fb;border-radius:10px;padding:14px 16px;">
      <div style="font-size:13px;font-weight:600;color:#6b4d8a;">{{ $label }}@if ($sec !== null) · {{ \App\Models\PeaceNewsIssue::clock($sec) }}@endif</div>
      @if ($sum)<div style="font-size:15px;line-height:1.55;margin-top:6px;">{{ $sum }}</div>@endif
      @if ($quote)<div style="font-size:15px;line-height:1.55;margin-top:8px;font-style:italic;color:#334455;">“{{ $quote }}”</div>@endif
      @if ($issue->youtubeAt($sec))<div style="margin-top:8px;"><a href="{{ $issue->youtubeAt($sec) }}" style="color:#6b4d8a;font-size:14px;font-weight:600;">{{ $cta }} ›</a></div>@endif
    </div>
  </td></tr>
  @endforeach

  <tr><td style="padding:18px 28px 0;">
    <div style="font-size:15px;font-weight:600;margin-bottom:6px;">The summary readers get</div>
    @foreach ((array) $sermon->summary_paragraphs as $p)<div style="font-size:14px;line-height:1.6;color:#334455;margin-bottom:8px;">{{ $p }}</div>@endforeach
  </td></tr>

  <tr><td style="padding:10px 28px 0;">
    <div style="font-size:15px;font-weight:600;margin-bottom:6px;">The questions that go out</div>
    @foreach ($issue->questions as $n => $q)<div style="font-size:15px;line-height:1.5;margin-bottom:6px;">{{ $n + 1 }}. {{ $q }}</div>@endforeach
  </td></tr>

  @if (! empty($issue->checks))
  <tr><td style="padding:16px 28px 0;">
    <div style="background:#fff6e6;border:1px solid #f0d9a8;border-radius:10px;padding:14px 16px;">
      <div style="font-size:15px;font-weight:600;color:#7a4b00;margin-bottom:6px;">Check before sending</div>
      @foreach ($issue->checks as $c)<div style="font-size:14px;line-height:1.5;color:#4a3a1a;margin-bottom:4px;">{{ $c }}</div>@endforeach
    </div>
  </td></tr>
  @endif

  @if ($pending > 0)
  <tr><td style="padding:16px 28px 0;font-size:15px;">
    <strong>{{ $pending }} reader {{ \Illuminate\Support\Str::plural('answer', $pending) }} waiting.</strong> <a href="{{ $answersUrl }}" style="color:#6b4d8a;">Read and approve ›</a>
  </td></tr>
  @endif

  <tr><td style="padding:22px 28px;font-size:13px;color:#5f6876;border-top:1px solid #eee;margin-top:16px;">
    If neither of you approves, nothing sends. The sermon stays on Find Peace as usual.
  </td></tr>
</table>
</td></tr>
</table>
</body></html>
