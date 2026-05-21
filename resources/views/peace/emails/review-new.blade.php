<!doctype html>
<html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#fefcef;font-family:-apple-system,Segoe UI,Roboto,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fefcef;padding:32px 16px;">
  <tr><td align="center">
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border:1px solid rgba(26,35,50,0.1);border-radius:8px;overflow:hidden;">
      <tr><td style="padding:28px 28px 6px;">
        <div style="font-size:11px;letter-spacing:0.2em;text-transform:uppercase;color:#03617A;font-weight:600;">Finding Peace · New sermon</div>
        <h1 style="font-family:Georgia,serif;font-size:26px;font-weight:500;margin:8px 0 4px;line-height:1.2;">{{ $sermon->title }}</h1>
        <div style="font-size:13px;color:#334455;">{{ $sermon->speaker }} · {{ $sermon->sermon_date?->format('F j, Y') }}</div>
      </td></tr>

      <tr><td style="padding:14px 28px 0;">
        <p style="font-size:15px;line-height:1.55;color:#334455;margin:14px 0;">
          The cron just published this sermon at <strong>{{ now()->format('g:i A T') }}</strong>. It's live on <a href="{{ $publicUrl }}" style="color:#03617A;">/find-peace</a> right now with 5 generated Q&amp;As.
        </p>
        <p style="font-size:15px;line-height:1.55;color:#334455;margin:14px 0;">
          <strong>You have 72 hours.</strong> If you don't approve or discard, the page automatically goes offline and you'll get one reminder.
        </p>
      </td></tr>

      <tr><td style="padding:6px 28px 18px;">
        <div style="background:rgba(3,97,122,0.06);border-left:3px solid #03617A;padding:14px 16px;border-radius:0 4px 4px 0;">
          <div style="font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#03617A;font-weight:600;margin-bottom:6px;">Heart line</div>
          <div style="font-family:Georgia,serif;font-size:16px;line-height:1.5;color:#1a2332;">{{ $sermon->heart_line }}</div>
        </div>
      </td></tr>

      <tr><td style="padding:0 28px 22px;">
        @foreach ($sermon->qaPairs->take(5) as $i => $qa)
          <div style="padding:12px 0;border-bottom:1px solid rgba(26,35,50,0.08);">
            <div style="font-size:14px;font-weight:600;color:#1a2332;line-height:1.4;">Q{{ $i + 1 }}: {{ $qa->question }}</div>
            <div style="font-size:13px;color:#334455;line-height:1.55;margin-top:4px;">{{ \Illuminate\Support\Str::limit($qa->answer, 220) }}</div>
          </div>
        @endforeach
      </td></tr>

      <tr><td align="center" style="padding:8px 28px 28px;">
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
          <tr>
            <td style="padding:0 6px;"><a href="{{ $approveUrl }}" style="display:inline-block;background:#03617A;color:#fff;text-decoration:none;padding:12px 22px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;">Approve →</a></td>
            <td style="padding:0 6px;"><a href="{{ $editUrl }}" style="display:inline-block;background:#fff;color:#03617A;text-decoration:none;padding:11px 21px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;border:1px solid #03617A;">Edit Q&amp;As</a></td>
            <td style="padding:0 6px;"><a href="{{ $discardUrl }}" style="display:inline-block;background:#fff;color:#a82a1f;text-decoration:none;padding:11px 21px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;border:1px solid rgba(168,42,31,0.3);">Discard</a></td>
          </tr>
        </table>
        <p style="font-size:12px;color:#334455;margin:18px 0 0;">
          Or <a href="{{ $publicUrl }}" style="color:#03617A;">view the live page</a> first.
        </p>
      </td></tr>

      <tr><td style="padding:14px 28px 22px;background:rgba(26,35,50,0.03);font-size:11px;color:#334455;line-height:1.5;">
        These links are signed and only valid for this sermon. They don't require a login. If you didn't expect this email, just click <a href="{{ $discardUrl }}" style="color:#03617A;">Discard</a> and we'll pull it down.
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
