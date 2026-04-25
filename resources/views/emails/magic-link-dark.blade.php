<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title>Sign in</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    /* No custom @font-face — text header renders identically in every client. */
    body { margin: 0; padding: 0; background: #0d1421; }
    a { color: #03617A; }
    @media (max-width: 600px) {
      .pad   { padding-left: 24px !important; padding-right: 24px !important; }
      .mark  { font-size: 36px !important; line-height: 1.1 !important; letter-spacing: -1px !important; white-space: normal !important; }
      .lede  { font-size: 22px !important; }
      .btn   { padding: 18px 36px !important; font-size: 13px !important; width: 100% !important; box-sizing: border-box; }
    }
  </style>
</head>
<body style="margin:0;padding:0;background:#0d1421;font-family:'Poppins',-apple-system,BlinkMacSystemFont,'Helvetica Neue',Arial,sans-serif;color:#f5f0e0;-webkit-font-smoothing:antialiased;">

<!-- Preheader (hidden, shows in inbox preview line) -->
<div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:#0d1421;opacity:0;">
  Tap to sign in to Shalom — link expires in 30 minutes.
</div>

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background:#0d1421;">
  <tr>
    <td align="center" style="padding:60px 16px 48px;">

      <!-- Header — just the wordmark, no chrome below -->
      <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="540" style="max-width:540px;width:100%;">
        <tr>
          <td align="center" class="pad" style="padding:0 24px 56px;">
            <div class="mark" style="font-family:'Cormorant Garamond','Palatino Linotype',Georgia,serif;font-weight:500;font-size:56px;line-height:1.05;letter-spacing:-1.4px;color:#5cb8d4;white-space:nowrap;">
              The Church of Peace
            </div>
          </td>
        </tr>

        <!-- Single-sentence purpose -->
        <tr>
          <td align="center" class="pad" style="padding:0 24px 32px;">
            <p class="lede" style="margin:0;font-family:'Cormorant Garamond','Palatino Linotype',Georgia,serif;font-size:22px;font-weight:400;line-height:1.4;letter-spacing:-0.2px;color:#f5f0e0;">
              Sign in to the bulletin.
            </p>
          </td>
        </tr>

        <!-- The button -->
        <tr>
          <td align="center" class="pad" style="padding:0 24px 24px;">
            <table role="presentation" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td align="center" bgcolor="#03617A" style="border-radius:6px;">
                  <a href="{{ $url }}" class="btn"
                     style="display:inline-block;padding:18px 56px;font-family:'Poppins',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:12px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#0d1421;text-decoration:none;border-radius:6px;mso-padding-alt:18px 56px;">
                    Sign in
                  </a>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Caption: expiry + safety, single line -->
        <tr>
          <td align="center" class="pad" style="padding:8px 24px 56px;">
            <p style="margin:0;font-family:'Poppins',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:13px;line-height:1.6;color:#a8b3c2;">
              Expires in 30 minutes &middot; One-time use<br>
              <span style="color:#a8b3c2;opacity:0.75;">Didn't request this? Safely ignore.</span>
            </p>
          </td>
        </tr>

        <!-- (URL fallback removed — only the Sign in button is interactive.) -->

        <!-- Address — ultra-tight, ultra-small -->
        <tr>
          <td align="center" class="pad" style="padding:32px 24px 0;border-top:1px solid rgba(26,35,50,0.10);">
            <p style="margin:0;font-family:'Poppins',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:11px;font-weight:500;letter-spacing:2px;text-transform:uppercase;color:#a8b3c2;">
              Shalom Seventh-day Adventist Church
            </p>
            <p style="margin:6px 0 0;font-family:'Poppins',-apple-system,'Helvetica Neue',Arial,sans-serif;font-size:11px;line-height:1.6;color:#a8b3c2;">
              3323 White Plains Rd &middot; Bronx, NY<br>
              <a href="mailto:contact@thechurchofpeace.org" style="color:#a8b3c2;text-decoration:underline;">contact@thechurchofpeace.org</a>
            </p>
          </td>
        </tr>
      </table>

    </td>
  </tr>
</table>

</body>
</html>
