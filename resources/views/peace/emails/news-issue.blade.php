<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;padding:0;background:#f5f0fb;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<div style="display:none;max-height:0;overflow:hidden;">{{ $sermon->heart_line }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0fb;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;">
  <tr><td style="padding:22px 32px;border-bottom:1px solid #eee;">
    <table role="presentation" width="100%"><tr>
      <td style="font-family:Georgia,serif;font-style:italic;font-size:24px;color:#6b4d8a;">shalom</td>
      <td align="right" style="font-size:13px;color:#5f6876;">Peace Notes News · {{ $issue->send_after->timezone('America/New_York')->format('M j') }}</td>
    </tr></table>
  </td></tr>

  <tr><td align="center" style="padding:34px 32px 6px;">
    <div style="font-family:Georgia,serif;font-size:40px;line-height:1.05;">{{ $sermon->title }}</div>
    @if ($sermon->heart_line)<div style="font-size:18px;line-height:1.45;color:#334455;margin-top:12px;">{{ $sermon->heart_line }}</div>@endif
    <div style="font-size:14px;color:#5f6876;margin-top:12px;">{{ $sermon->speaker }} · Sabbath, {{ \Illuminate\Support\Carbon::parse($sermon->sermon_date)->format('F j') }}</div>
  </td></tr>

  <tr><td style="padding:20px 32px 0;font-size:16px;line-height:1.7;">
    @foreach (array_slice((array) $sermon->summary_paragraphs, 0, 3) as $p)<p style="margin:0 0 14px;">{{ $p }}</p>@endforeach
  </td></tr>

  <tr><td style="padding:10px 32px 0;">
    <div style="background:#f5f0fb;border-radius:12px;padding:22px 22px 8px;">
      <div style="font-size:19px;font-weight:600;margin-bottom:12px;">This week, ask yourself</div>
      @foreach ($issue->questions as $q)<p style="margin:0 0 14px;font-size:16px;line-height:1.55;">{{ $q }}</p>@endforeach
    </div>
  </td></tr>

  <tr><td align="center" style="padding:26px 32px 6px;">
    <a href="{{ $answerUrl }}" style="display:inline-block;background:#6b4d8a;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:14px 26px;border-radius:6px;">Share what spoke to you</a>
    <div style="font-size:14px;margin-top:14px;">
      @if ($issue->youtubeAt($issue->opening_seconds))<a href="{{ $issue->youtubeAt($issue->opening_seconds) }}" style="color:#6b4d8a;">Watch the sermon</a> · @endif
      <a href="{{ $issueUrl }}" style="color:#6b4d8a;">Read it on the site</a>
    </div>
  </td></tr>

  @if ($youSaid->count())
  <tr><td style="padding:26px 32px 0;">
    <div style="font-size:19px;font-weight:600;margin-bottom:12px;">You said, last week</div>
    @foreach ($youSaid as $a)
      <p style="margin:0 0 4px;font-size:16px;line-height:1.6;">“{{ \Illuminate\Support\Str::limit($a->body, 400) }}”</p>
      <p style="margin:0 0 16px;font-size:14px;color:#5f6876;">{{ $a->first_name ?: 'A reader' }}</p>
    @endforeach
  </td></tr>
  @endif

  <tr><td align="center" style="padding:24px 32px 28px;border-top:1px solid #eee;font-size:13px;line-height:1.7;color:#5f6876;">
    Shalom Seventh-day Adventist Church · 3323 White Plains Road, Bronx, NY 10467<br>
    You get this because you signed up on thechurchofpeace.org. <a href="{{ $unsubUrl }}" style="color:#5f6876;">Unsubscribe</a>
  </td></tr>
</table>
</td></tr>
</table>
</body></html>
