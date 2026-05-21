<!doctype html>
<html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#fefcef;font-family:-apple-system,Segoe UI,Roboto,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fefcef;padding:32px 16px;">
  <tr><td align="center">
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border:1px solid rgba(26,35,50,0.1);border-radius:8px;overflow:hidden;">
      <tr><td style="padding:28px 28px 6px;">
        <div style="font-size:11px;letter-spacing:0.2em;text-transform:uppercase;color:#a82a1f;font-weight:600;">Finding Peace · Confirm permanent removal</div>
        <h1 style="font-family:Georgia,serif;font-size:26px;font-weight:500;margin:8px 0 4px;line-height:1.2;">{{ $sermon->title }}</h1>
        <div style="font-size:13px;color:#334455;">{{ $sermon->speaker }} · {{ $sermon->sermon_date?->format('F j, Y') }}</div>
      </td></tr>

      <tr><td style="padding:14px 28px 0;">
        <p style="font-size:15px;line-height:1.55;color:#334455;margin:14px 0;">
          You marked this sermon for discard 48 hours ago. The page has been offline since then. This is your last call before we mark it permanently removed.
        </p>
        <p style="font-size:15px;line-height:1.55;color:#334455;margin:14px 0;">
          If you ignore this email, the sermon stays in soft-deleted limbo — DB record kept, page never served. You can come back and restore it any time by clicking Restore below.
        </p>
      </td></tr>

      <tr><td align="center" style="padding:18px 28px 28px;">
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
          <tr>
            <td style="padding:0 6px;"><a href="{{ $confirmDeleteUrl }}" style="display:inline-block;background:#a82a1f;color:#fff;text-decoration:none;padding:12px 22px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;">Confirm permanent removal</a></td>
            <td style="padding:0 6px;"><a href="{{ $restoreUrl }}" style="display:inline-block;background:#03617A;color:#fff;text-decoration:none;padding:12px 22px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;">Restore →</a></td>
          </tr>
        </table>
        <p style="font-size:12px;color:#334455;margin:18px 0 0;">
          No action = stays soft-deleted (recoverable from /admin/peace).
        </p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>
