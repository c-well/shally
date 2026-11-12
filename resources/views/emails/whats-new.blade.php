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
@php $sans = "-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif"; @endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4efe3;">
  <tr><td align="center" style="padding:28px 12px 36px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;">
      <tr><td style="padding:0 4px 14px; font-family:Georgia,'Times New Roman',serif; font-size:13px; letter-spacing:2px; text-transform:uppercase; color:#03617a;">The Church of Peace</td></tr>
      <tr><td style="background:#ffffff; border:1px solid #e6dfcf; border-radius:14px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr><td style="padding:24px 22px 16px;">
            <div style="font-family:Georgia,'Times New Roman',serif; font-size:24px; line-height:1.25; color:#1a2332;">Hi {{ $first }}, a few new things</div>
            <div style="margin-top:6px; font-family:{!! $sans !!}; font-size:15px; line-height:1.5; color:#5a6676;">{{ $intro }}</div>
          </td></tr>
          @foreach ($items as $it)
            @php [$fg, $bg] = \App\Support\WhatsNew::colours($it['area']); $href = $page . '#' . $it['key']; @endphp
            <tr><td style="border-top:1px solid #efe9dc;">
              <a href="{{ $href }}" style="display:block; text-decoration:none; padding:14px 22px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                  <td width="12" valign="top" style="padding-top:7px;"><div style="width:9px; height:9px; border-radius:9px; background:{{ $fg }};"></div></td>
                  <td style="padding-left:10px; font-family:{!! $sans !!};">
                    <div style="font-size:16px; font-weight:600; line-height:1.3; color:#1a2332;">{{ $it['title'] }}</div>
                    <div style="margin-top:2px; font-size:14px; line-height:1.4; color:#5a6676;">{{ $it['short'] }}</div>
                  </td>
                  <td width="20" align="right" valign="middle" style="font-family:{!! $sans !!}; font-size:18px; color:#9aa3ad;">&rsaquo;</td>
                </tr></table>
              </a>
            </td></tr>
          @endforeach
          <tr><td style="border-top:1px solid #efe9dc; padding:18px 22px 22px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
              <td style="border-radius:8px; background:#03617a;"><a href="{{ $page }}" style="display:inline-block; padding:11px 18px; font-family:{!! $sans !!}; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none;">See how each one works &rarr;</a></td>
            </tr></table>
          </td></tr>
        </table>
      </td></tr>
      <tr><td style="padding:18px 4px 0; font-family:{!! $sans !!}; font-size:14px; color:#5a6676;">{{ $signoff }}</td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
