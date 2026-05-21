<!doctype html>
<html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#fefcef;font-family:-apple-system,Segoe UI,Roboto,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fefcef;padding:36px 16px;">
  <tr><td align="center">
    <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border:1px solid rgba(26,35,50,0.10);border-radius:8px;overflow:hidden;">
      <tr><td style="padding:36px 32px 8px;">
        <div style="font-size:11px;letter-spacing:0.22em;text-transform:uppercase;color:#03617A;font-weight:600;">Finding Peace</div>
        <h1 style="font-family:Georgia,serif;font-size:30px;font-weight:500;margin:14px 0 6px;line-height:1.15;">{{ $heading ?? 'Tap to come back.' }}</h1>
        @if(!empty($subhead))
          <p style="font-size:14px;color:#334455;line-height:1.55;margin:6px 0 0;">{{ $subhead }}</p>
        @endif
      </td></tr>

      <tr><td style="padding:16px 32px 0;">
        <p style="font-size:15px;line-height:1.6;color:#334455;margin:14px 0;">
          {{ $body ?? "Tap below to come back to Finding Peace. The link expires in 30 minutes and only works once." }}
        </p>
      </td></tr>

      <tr><td align="center" style="padding:14px 32px 28px;">
        <a href="{{ $url }}" style="display:inline-block;background:#03617A;color:#fff;text-decoration:none;padding:14px 28px;border-radius:6px;font-size:13px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;">
          {{ $cta ?? 'Take me back →' }}
        </a>
        <p style="font-size:11px;color:#334455;margin:18px 0 0;line-height:1.5;">
          Or paste this link into your browser:<br>
          <span style="font-family:'JetBrains Mono',ui-monospace,monospace;color:#03617A;word-break:break-all;">{{ $url }}</span>
        </p>
      </td></tr>

      <tr><td style="padding:18px 32px 28px;background:rgba(26,35,50,0.03);font-size:12px;color:#334455;line-height:1.55;border-top:1px solid rgba(26,35,50,0.06);">
        You're getting this because someone (hopefully you) entered <strong>{{ $email }}</strong> at <a href="{{ url('/find-peace') }}" style="color:#03617A;">thechurchofpeace.org/find-peace</a>. If it wasn't you, ignore this email — nothing happens unless the link is clicked.
        <br><br>
        No church invites. No spam. — Finding Peace at Shalom
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
