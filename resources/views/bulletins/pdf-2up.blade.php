{{-- Bulletin PDF — 2-UP landscape imposition (2026-07-04, Karlon).
     One landscape letter sheet, printed DOUBLE-SIDED:
       • Front side: order-of-service printed TWICE, side by side
       • Back side:  announcements printed TWICE, side by side (aligns behind the front)
     Cut down the middle → TWO identical 5.5x8.5 bulletins, participants front / announcements back.
     Both halves are identical, so the duplex flip direction does not matter.
     Self-contained — the portrait pdf.blade.php is untouched. --}}
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Bulletin (2-up) · {{ \Carbon\Carbon::parse($snapshot['service_date'] ?? now())->format('M j, Y') }}</title>
<style>
  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; background: #fff; color: #111; font-family: Arial, Helvetica, sans-serif; }
  @page { margin: 0; }

  /* Two-up scaffold: a fixed 11in table, two 5.5in columns, dashed cut guide. */
  /* Absolute-positioned columns inside a page-sized relative sheet: out of normal
     flow, so dompdf can't insert phantom blank pages (its usual side-by-side bug). */
  .sheet { position: relative; width: 11in; height: 8.5in; overflow: hidden; }
  .back-sheet { page-break-before: always; }
  .col { position: absolute; top: 0; width: 5.5in; height: 8.5in; }
  .col.leftcol  { left: 0; border-right: 0.5pt dashed #c4c4c4; }   /* cut/fold line */
  .col.rightcol { left: 5.5in; }
  .page { padding: 0.38in 0.42in; }
  .caps { text-transform: uppercase; }

  /* ── FRONT (order of service) — header trimmed to fit the narrower 5.5in column ── */
  .front .welcome-top { text-align: center; line-height: 1.15; margin-bottom: 13pt; }
  .front .welcome-top .small-top   { font-size: 12pt; font-weight: 700; letter-spacing: 0.5pt; }
  .front .welcome-top .church-name { font-size: 16pt; font-weight: 400; letter-spacing: 0.4pt; margin-top: 3pt; }
  .front .welcome-top .date        { font-size: 10pt; margin-top: 3pt; font-weight: 400; }

  .program-row { width: 100%; border-collapse: collapse; font-family: Georgia, "Times New Roman", serif; font-size: 10.5pt; line-height: 1.3; margin: 4.5pt 0; }
  .program-row td { vertical-align: bottom; padding: 0; }
  .program-row td.left  { font-weight: 700; white-space: nowrap; padding-right: 4pt; }
  .program-row td.dots  { width: 100%; border-bottom: 1pt dotted #222; padding: 0 3pt 3pt 3pt; }
  .program-row td.right { font-weight: 400; white-space: nowrap; padding-left: 4pt; text-align: right; }
  .program-row td.right.italic { font-style: italic; }

  .program-centered { text-align: center; font-family: Georgia, "Times New Roman", serif; font-size: 10.5pt; line-height: 1.3; font-weight: 700; margin: 7pt 0 1.5pt; }
  .program-centered.subtitle { font-size: 10pt; font-weight: 400; font-style: italic; margin: 1.5pt 0 5pt; }

  .front-footer { text-align: center; font-size: 9pt; line-height: 1.4; margin-top: 12pt; padding-top: 6pt; }
  .front-footer .email { text-decoration: underline; }

  /* ── BACK (announcements) ── */
  .back .title { text-align: center; font-size: 19pt; font-weight: 800; letter-spacing: 1pt; text-decoration: underline; margin-bottom: 12pt; }
  .section { margin-bottom: 9pt; }
  .section-title { font-size: 11pt; font-weight: 700; margin-bottom: 2pt; }
  .bullet-list { margin: 0; padding-left: 18pt; list-style-type: circle; }
  .bullet-list li { font-size: 10pt; line-height: 1.3; margin: 1pt 0; }
  .offerings { margin: 9pt 0 11pt; }
  .offerings .heading { font-size: 11pt; font-weight: 700; margin-bottom: 2pt; }
  .offerings .line { font-size: 10.5pt; font-weight: 700; line-height: 1.3; }
  .mission { text-align: center; margin-top: 9pt; margin-bottom: 12pt; }
  .mission .heading { font-size: 11pt; font-weight: 700; text-decoration: underline; margin-bottom: 5pt; }
  .mission .text { margin: 0 auto; font-size: 10pt; line-height: 1.3; }
  .qr-row { text-align: center; margin-top: 10pt; }
  .qr-row .qr { width: 52pt; height: 52pt; }
  .qr-cap { font-size: 8pt; line-height: 1.35; color: #333; margin-top: 3pt; }
  .pleasant { text-align: center; margin-top: 12pt; font-size: 10pt; font-style: italic; }

  .watermark-prev { position: fixed; top: 0.25in; right: 0.4in; font-size: 8pt; letter-spacing: 1.5pt; text-transform: uppercase; color: #b08d3c; font-weight: 700; }
</style>
</head>
<body>

@if (!empty($isPrevious))
  <div class="watermark-prev">Previous version · {{ $previousPublishedAt ?? '' }}</div>
@endif

{{-- ═══ FRONT SHEET — order of service, printed twice ═══ --}}
<div class="sheet">
  @for ($c = 0; $c < 2; $c++)
  <div class="col front {{ $c === 0 ? 'leftcol' : 'rightcol' }}">
    <div class="page">
      <header class="welcome-top">
        <div class="small-top caps">Welcome to</div>
        <div class="church-name caps">Shalom SDA Church</div>
        <div class="date">{{ \Carbon\Carbon::parse($snapshot['service_date'] ?? now())->format('F j, Y') }}</div>
        @if (($snapshot['kind'] ?? 'sabbath') === 'event_night' && !empty($snapshot['event_name']))
          <div class="date" style="font-weight: 700; letter-spacing: 2pt; text-transform: uppercase; font-size: 9pt; margin-top: 6pt;">
            {{ $snapshot['event_name'] }} · Night {{ $snapshot['event_night_number'] ?? 1 }}@if (!empty($snapshot['event_total_nights'])) of {{ $snapshot['event_total_nights'] }}@endif
          </div>
        @elseif (!empty($snapshot['title']) && !in_array(strtolower($snapshot['title']), ['sabbath service', 'sabbath worship', '']))
          <div class="date" style="font-style: italic;">{{ $snapshot['title'] }}</div>
        @endif
      </header>

      <main>
        @foreach ($snapshot['lines'] ?? [] as $line)
          @if (($line['kind'] ?? 'line') === 'section_header')
            <div class="program-centered">{{ $line['section'] ?? '' }}</div>
          @else
            @php
              $part   = trim((string)($line['part'] ?? ''));
              $person = trim((string)($line['person'] ?? ''));
              $isSubLine = $part !== '' && (str_starts_with($part, '#') || str_starts_with($part, '"') || str_starts_with($part, '“'));
              $isGroupName = $person !== '' && (stripos($person, 'team') !== false || stripos($person, 'choir') !== false);
            @endphp
            @if ($isSubLine)
              @php $display = (str_starts_with($part, '"') || str_starts_with($part, '“')) ? $part : '"' . $part . '"'; @endphp
              <div class="program-centered subtitle">{{ $display }}</div>
            @elseif ($part !== '')
              <table class="program-row">
                <tr>
                  <td class="left">{{ $part }}</td>
                  <td class="dots">&nbsp;</td>
                  <td class="right{{ $isGroupName ? ' italic' : '' }}">{{ $person }}</td>
                </tr>
              </table>
            @endif
          @endif
        @endforeach
      </main>

      <footer class="front-footer">
        <div>Shalom Seventh-day Adventist Church</div>
        <div>3323 White Plains Rd, Bronx, NY 10467</div>
        <div class="email">contact@thechurchofpeace.org</div>
      </footer>
    </div>
  </div>
  @endfor
</div>

{{-- ═══ BACK SHEET — announcements, printed twice (aligns behind the front) ═══ --}}
<div class="sheet back-sheet">
  @for ($c = 0; $c < 2; $c++)
  <div class="col back {{ $c === 0 ? 'leftcol' : 'rightcol' }}">
    <div class="page">
      <div class="title">Announcements</div>
      @php $foldedAnns ??= \App\Models\Bulletin::foldAnnouncements(array_values(array_filter($snapshot['announcements'] ?? [], fn ($a) => empty($a['is_web_only'])))); @endphp
      @foreach ($foldedAnns as $a)
        @php
          $aTitle  = trim((string)($a['title'] ?? ''));
          $aDetail = trim((string)($a['detail'] ?? ''));
          $tLower  = strtolower($aTitle);
          $isMission  = str_contains($tLower, 'mission');
          $isOffering = str_contains($tLower, 'offering');
          $titleColon = str_ends_with($aTitle, ':') ? $aTitle : $aTitle . ':';
        @endphp
        @if ($aTitle === '')
          @continue
        @elseif ($isMission)
          <section class="mission"><div class="heading">{{ $titleColon }}</div><div class="text">{{ $aDetail }}</div></section>
        @elseif ($isOffering)
          <section class="offerings"><div class="heading">{{ $titleColon }}</div>
            @foreach (preg_split("/\r?\n/", $aDetail) as $line)@if (trim($line) !== '')<div class="line">{{ trim($line) }}</div>@endif @endforeach
          </section>
        @else
          <section class="section"><div class="section-title">{{ $titleColon }}</div>
            @if ($aDetail !== '')
              <ul class="bullet-list">
                @foreach (preg_split("/\r?\n/", $aDetail) as $bullet)@if (trim($bullet) !== '')<li>{{ trim($bullet) }}</li>@endif @endforeach
              </ul>
            @endif
          </section>
        @endif
      @endforeach
      <div class="qr-row">
      <img class="qr" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('qr-announcements.png'))) }}" alt="QR code to all announcements">
      <div class="qr-cap">Scan for <b>all</b> announcements &amp; details<br>thechurchofpeace.org/announcements</div>
    </div>

    <div class="pleasant">Have a pleasant Sabbath :)</div>
    </div>
  </div>
  @endfor
</div>

</body>
</html>
