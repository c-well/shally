<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title>New on the church site</title>
</head>
<body style="margin:0; padding:0; background:#f4efe3; -webkit-text-size-adjust:100%;">
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">{{ $preheader }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4efe3;">
  <tr><td align="center" style="padding:32px 14px 40px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">

      <tr><td style="padding:0 6px 22px; font-family:Georgia,'Times New Roman',serif; font-size:15px; letter-spacing:2px; text-transform:uppercase; color:#03617a;">The Church of Peace</td></tr>

      <tr><td style="background:#ffffff; border-radius:14px; padding:34px 30px 30px; border:1px solid #e6dfcf;">
        <h1 style="margin:0; font-family:Georgia,'Times New Roman',serif; font-size:30px; line-height:1.2; font-weight:normal; color:#1a2332;">Hi {{ $first }}, here's what's new</h1>
        <p style="margin:14px 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:16px; line-height:1.6; color:#334455;">{{ $intro }}</p>
      </td></tr>

      @foreach ($items as $it)
        @php [$fg, $bg] = \App\Support\WhatsNew::colours($it['area']); $link = \App\Support\WhatsNew::link($it); @endphp
        <tr><td style="height:14px; line-height:14px; font-size:0;">&nbsp;</td></tr>
        <tr><td style="background:#ffffff; border-radius:14px; padding:26px 30px 26px; border:1px solid #e6dfcf;">
          <span style="display:inline-block; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:{{ $fg }}; background:{{ $bg }}; padding:4px 10px; border-radius:999px;">{{ $it['tag'] }}</span>
          <h2 style="margin:14px 0 0; font-family:Georgia,'Times New Roman',serif; font-size:22px; line-height:1.3; font-weight:normal; color:#1a2332;">{{ $it['title'] }}</h2>
          <p style="margin:10px 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:15px; line-height:1.65; color:#334455;">{{ $it['summary'] }}</p>
          @if (! empty($it['steps']))
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:16px; background:#faf6ec; border-radius:10px;">
              <tr><td style="padding:14px 16px 6px;">
                @foreach ($it['steps'] as $i => $s)
                  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:10px;"><tr>
                    <td valign="top" width="30" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:13px; font-weight:700; color:{{ $fg }};">{{ $i + 1 }}.</td>
                    <td style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:14.5px; line-height:1.55; color:#1a2332;">{{ $s }}</td>
                  </tr></table>
                @endforeach
              </td></tr>
            </table>
          @endif
          @if (! empty($it['note']))
            <p style="margin:12px 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:13.5px; line-height:1.55; color:#5a6676;">{{ $it['note'] }}</p>
          @endif
          @if ($link && ! empty($it['cta']))
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:18px;"><tr>
              <td style="border-radius:8px; background:{{ $fg }};"><a href="{{ $link }}" style="display:inline-block; padding:12px 20px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">{{ $it['cta'] }} &rarr;</a></td>
            </tr></table>
          @endif
        </td></tr>
      @endforeach

      <tr><td style="height:14px; line-height:14px; font-size:0;">&nbsp;</td></tr>
      <tr><td style="background:#ffffff; border-radius:14px; padding:24px 30px; border:1px solid #e6dfcf; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:15px; line-height:1.6; color:#334455;">
        All of this is in one place on the site, so you can come back to it any time:
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:14px;"><tr>
          <td style="border-radius:8px; border:1px solid #03617a;"><a href="{{ $page }}" style="display:inline-block; padding:11px 18px; font-size:14px; font-weight:600; color:#03617a; text-decoration:none;">See what's new &rarr;</a></td>
        </tr></table>
      </td></tr>

      <tr><td style="padding:22px 6px 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif; font-size:14px; line-height:1.6; color:#5a6676;">{{ $signoff }}</td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
