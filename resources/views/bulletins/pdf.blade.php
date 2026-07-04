{{-- Bulletin PDF — v7 structure (.sheet wrappers + page-break-after, which renders 2 pages cleanly)
     with two v8 tweaks folded in:
       • Names on the right are no longer bold (regular weight)
       • Slightly more breathing space between rows (margin 7pt, up from 4pt)
     $snapshot = Bulletin::snapshotCurrentState() (or 'previous' variant). --}}
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Bulletin · {{ \Carbon\Carbon::parse($snapshot['service_date'] ?? now())->format('M j, Y') }}</title>
<style>
  * { box-sizing: border-box; }
  html, body {
    margin: 0; padding: 0; background: #fff; color: #111;
    font-family: Arial, Helvetica, sans-serif;
  }
  @page { size: letter; margin: 0; }

  /* Single document with explicit page-break-before on the back section.
     No .sheet min-heights or page-break-after — those create dompdf phantom pages. */
  .page { padding: 0.55in 0.6in; }
  .sheet.back { page-break-before: always; }
  .caps { text-transform: uppercase; }

  /* ── FRONT PAGE ── */
  .front .welcome-top {
    text-align: center;
    line-height: 1.15;
    margin-bottom: 26pt;
  }
  .front .welcome-top .small-top { font-size: 17pt; font-weight: 700; letter-spacing: 0.5pt; }
  .front .welcome-top .church-name { font-size: 23pt; font-weight: 400; letter-spacing: 0.6pt; margin-top: 4pt; }
  .front .welcome-top .date { font-size: 12pt; margin-top: 4pt; font-weight: 400; }

  /* Program rows — 3-cell table for reliable dot leaders */
  .program-row {
    width: 100%;
    border-collapse: collapse;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 12pt;
    line-height: 1.4;
    margin: 7pt 0;
  }
  .program-row td { vertical-align: bottom; padding: 0; }
  .program-row td.left  { font-weight: 700; white-space: nowrap; padding-right: 4pt; }
  .program-row td.dots  { width: 100%; border-bottom: 1pt dotted #222; padding: 0 3pt 3pt 3pt; }
  .program-row td.right { font-weight: 400; white-space: nowrap; padding-left: 4pt; text-align: right; }
  .program-row td.right.italic { font-style: italic; }

  /* (The .long class is no longer used — scripture refs get split onto a centered sub-line instead.) */

  /* Centered section break (e.g., "Pastoral Greetings/Baby Dedication") */
  .program-centered {
    text-align: center;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 12pt;
    line-height: 1.3;
    font-weight: 700;
    margin: 10pt 0 2pt;
  }
  .program-centered.subtitle {
    font-size: 11.5pt;
    font-weight: 400;
    font-style: italic;
    margin: 2pt 0 8pt;
  }

  .front-footer {
    text-align: center;
    font-size: 10.5pt;
    line-height: 1.45;
    margin-top: 22pt;
    padding-top: 10pt;
  }
  .front-footer .email { text-decoration: underline; }

  /* ── BACK PAGE — tightened to fit everything on ONE announcements page ── */
  .back .title {
    text-align: center;
    font-size: 22pt;
    font-weight: 800;
    letter-spacing: 1pt;
    text-decoration: underline;
    margin-bottom: 14pt;
  }
  .section { margin-bottom: 10pt; }
  .section-title { font-size: 12pt; font-weight: 700; margin-bottom: 2pt; }
  .bullet-list { margin: 0; padding-left: 20pt; list-style-type: circle; }
  .bullet-list li { font-size: 10.5pt; line-height: 1.35; margin: 1pt 0; }
  .bullet-list.tight li { margin: 0; }

  .offerings { margin: 10pt 0 12pt; }
  .offerings .heading { font-size: 12pt; font-weight: 700; margin-bottom: 2pt; }
  .offerings .line    { font-size: 11pt; font-weight: 700; line-height: 1.35; }

  .mission { text-align: center; margin-top: 10pt; margin-bottom: 14pt; }  /* breathing room before the next announcement (Karlon) */
  .mission .heading { font-size: 12pt; font-weight: 700; text-decoration: underline; margin-bottom: 5pt; }
  .mission .text    { max-width: 6in; margin: 0 auto; font-size: 10.5pt; line-height: 1.35; }

  .qr-row { margin: 9pt auto 0; border-collapse: collapse; }
  .qr-cell { vertical-align: middle; padding-right: 8pt; }
  .qr-row .qr { width: 48pt; height: 48pt; display: block; }
  .qr-cap { vertical-align: middle; text-align: left; font-size: 8.5pt; line-height: 1.4; color: #333; }
  .pleasant { text-align: center; margin-top: 14pt; font-size: 10.5pt; font-style: italic; }

  .watermark-prev {
    position: fixed; top: 0.3in; right: 0.5in;
    font-size: 8pt; letter-spacing: 1.5pt;
    text-transform: uppercase; color: #b08d3c; font-weight: 700;
  }
</style>
</head>
<body>

@if (!empty($isPrevious))
  <div class="watermark-prev">Previous version · {{ $previousPublishedAt ?? '' }}</div>
@endif

{{-- ═══ FRONT PAGE ═══ --}}
<section class="sheet front">
  <div class="page">

    <header class="welcome-top">
      <div class="small-top caps">Welcome to</div>
      <div class="church-name caps">Shalom SDA Church</div>
      <div class="date">{{ \Carbon\Carbon::parse($snapshot['service_date'] ?? now())->format('F j, Y') }}</div>
      @if (($snapshot['kind'] ?? 'sabbath') === 'event_night' && !empty($snapshot['event_name']))
        <div class="date" style="font-weight: 700; letter-spacing: 2pt; text-transform: uppercase; font-size: 10pt; margin-top: 6pt;">
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
            $isSubLine = $part !== '' && (
              str_starts_with($part, '#')
              || str_starts_with($part, '"')
              || str_starts_with($part, '“')
            );
            $isGroupName = $person !== '' && (
              stripos($person, 'team') !== false
              || stripos($person, 'choir') !== false
            );
            $isLong = mb_strlen($part) > 28;
          @endphp
          @if ($isSubLine)
            @php
              $display = (str_starts_with($part, '"') || str_starts_with($part, '“')) ? $part : '"' . $part . '"';
            @endphp
            <div class="program-centered subtitle">{{ $display }}</div>
          @elseif ($part !== '')
            <table class="program-row{{ $isLong ? ' long' : '' }}">
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
</section>

{{-- ═══ BACK PAGE ═══ --}}
@php $foldedAnns = \App\Models\Bulletin::foldAnnouncements(array_values(array_filter($snapshot['announcements'] ?? [], fn ($a) => empty($a['is_web_only'])))); @endphp
@if (!empty($foldedAnns))
<section class="sheet back">
  <div class="page">

    <div class="title">Announcements</div>

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
        <section class="mission">
          <div class="heading">{{ $titleColon }}</div>
          <div class="text">{{ $aDetail }}</div>
        </section>
      @elseif ($isOffering)
        <section class="offerings">
          <div class="heading">{{ $titleColon }}</div>
          @foreach (preg_split("/\r?\n/", $aDetail) as $line)
            @if (trim($line) !== '')
              <div class="line">{{ trim($line) }}</div>
            @endif
          @endforeach
        </section>
      @else
        <section class="section">
          <div class="section-title">{{ $titleColon }}</div>
          @if ($aDetail !== '')
            <ul class="bullet-list">
              @foreach (preg_split("/\r?\n/", $aDetail) as $bullet)
                @if (trim($bullet) !== '')
                  <li>{{ trim($bullet) }}</li>
                @endif
              @endforeach
            </ul>
          @endif
        </section>
      @endif
    @endforeach

    <table class="qr-row"><tr>
      <td class="qr-cell"><img class="qr" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('qr-announcements.png'))) }}" alt="QR code to all announcements"></td>
      <td class="qr-cap">Scan for <b>more</b><br>thechurchofpeace.org/announcements</td>
    </tr></table>

    <div class="pleasant">Have a pleasant Sabbath :)</div>

  </div>
</section>
@endif

</body>
</html>
