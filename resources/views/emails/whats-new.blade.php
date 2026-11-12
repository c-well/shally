<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title>New on the church site</title>
</head>
{{--
  Built like a product page, not a newsletter: one thing per tile, a short
  name, one line, one button, and room around it. Music day leads, larger.
  Tables and inline styles only, because Gmail strips everything else.
--}}
@php
  $sans = "-apple-system,BlinkMacSystemFont,'SF Pro Display','Segoe UI',Helvetica,Arial,sans-serif";
  $hero = $items[0];
  $rest = array_slice($items, 1);
@endphp
<body style="margin:0; padding:0; background:#ffffff; -webkit-text-size-adjust:100%;">
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">{{ $preheader }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;">
  <tr><td align="center" style="padding:0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">

      <tr><td align="center" style="padding:26px 20px 22px; font-family:{!! $sans !!}; font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#6e6e73;">The Church of Peace</td></tr>

      {{-- Hero --}}
      @php [$fg, $bg] = \App\Support\WhatsNew::colours($hero['area']); @endphp
      <tr><td align="center" style="background:{{ $bg }}; padding:56px 28px 54px; font-family:{!! $sans !!};">
        <div style="font-size:13px; font-weight:600; color:{{ $fg }}; letter-spacing:0.3px;">New for you, {{ $first }}</div>
        <div style="margin-top:10px; font-size:44px; line-height:1.05; font-weight:700; letter-spacing:-1px; color:#1d1d1f;">{{ $hero['headline'] }}</div>
        <div style="margin-top:12px; font-size:19px; line-height:1.35; color:#1d1d1f;">{{ $hero['short'] }}</div>
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;"><tr>
          <td style="border-radius:999px; background:{{ $fg }};"><a href="{{ $page }}#{{ $hero['key'] }}" style="display:inline-block; padding:11px 22px; font-family:{!! $sans !!}; font-size:15px; font-weight:500; color:#ffffff; text-decoration:none;">Learn more</a></td>
        </tr></table>
      </td></tr>

      @foreach ($rest as $it)
        @php [$fg, $bg] = \App\Support\WhatsNew::colours($it['area']); @endphp
        <tr><td style="height:10px; line-height:10px; font-size:0;">&nbsp;</td></tr>
        <tr><td align="center" style="background:{{ $bg }}; padding:42px 28px 40px; font-family:{!! $sans !!};">
          <div style="font-size:30px; line-height:1.1; font-weight:700; letter-spacing:-0.6px; color:#1d1d1f;">{{ $it['headline'] }}</div>
          <div style="margin-top:8px; font-size:17px; line-height:1.4; color:#1d1d1f;">{{ $it['short'] }}</div>
          <div style="margin-top:16px;"><a href="{{ $page }}#{{ $it['key'] }}" style="font-family:{!! $sans !!}; font-size:16px; color:{{ $fg }}; text-decoration:none;">Learn more &rsaquo;</a></div>
        </td></tr>
      @endforeach

      <tr><td align="center" style="padding:30px 24px 40px; font-family:{!! $sans !!}; font-size:13px; line-height:1.6; color:#6e6e73;">
        All of these are under Admin &rarr; What's new.<br>{{ $signoff }}
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
