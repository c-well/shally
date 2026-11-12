{{-- Bulletin PDF — BOOKLET imposition (2026-11-12, first used for Pathfinder & Adventurer Day).
     One landscape letter sheet, printed DOUBLE-SIDED (flip on the short edge), folded in half:
       • Outside:  left half = back page (announcements), right half = front cover
       • Inside:   left half = order of service, part one; right half = the rest
     The program splits between two lines, never between a line and the title under it
     (a hymn name or scripture reference belongs with the line above it). --}}
@php
  $date  = \Carbon\Carbon::parse($snapshot['service_date'] ?? now());
  $cover = is_array($cover ?? null) ? $cover : [];
  $lines = array_values($snapshot['lines'] ?? []);

  /* Split nearest the middle, only where the next row is a real line. */
  $n = count($lines); $split = $n;
  if ($n > 1) {
      $best = null;
      for ($i = 1; $i < $n; $i++) {
          if (($lines[$i]['kind'] ?? 'line') === 'section_header') continue;
          if ($best === null || abs($i - $n / 2) < abs($best - $n / 2)) $best = $i;
      }
      $split = $best ?? $n;
  }
  $halves = [array_slice($lines, 0, $split), array_slice($lines, $split)];

  /* Spread the rows to fill the page: about 470pt of height for the program.
     Few lines get generous spacing; a long program packs tighter but still fits. */
  $rowsMax = max(1, count($halves[0]), count($halves[1]));
  /* dompdf collapses the margins between rows, so each gap counts once, not twice. */
  $gap     = (int) max(3, min(24, floor(470 / $rowsMax - 17)));
  $pt      = $rowsMax > 16 ? 10.5 : 12;

  $emblems = [];
  foreach ((array) ($cover['emblems'] ?? []) as $f) {
      $p = public_path('img/emblems/' . basename($f));
      if (is_file($p)) $emblems[] = 'data:' . (mime_content_type($p) ?: 'image/png') . ';base64,' . base64_encode(file_get_contents($p));
  }
  $kicker   = $cover['kicker'] ?? 'Welcome to';
  $title    = $cover['title'] ?? (in_array(strtolower($snapshot['title'] ?? ''), ['', 'sabbath service', 'sabbath worship']) ? 'Sabbath Worship' : $snapshot['title']);
@endphp
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Bulletin booklet · {{ $date->format('M j, Y') }}</title>
<style>
  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; background: #fff; color: {{ $fontColor ?? '#111111' }}; font-family: Helvetica, Arial, sans-serif; }
  @page { margin: 0; }
  .sheet { position: relative; width: 11in; height: 8.5in; overflow: hidden; }
  .inside { page-break-before: always; }
  .col { position: absolute; top: 0; width: 5.5in; height: 8.5in; }
  .col.l { left: 0; }
  .col.r { left: 5.5in; }
  .page { padding: 0.45in 0.48in; }
  .fold { position: absolute; left: 5.5in; top: 0.2in; height: 0.25in; border-left: 0.5pt solid #bdbdbd; }
  .fold.b { top: auto; bottom: 0.2in; }

  /* ── Cover ── */
  .cover { text-align: center; height: 8.5in; }
  .cover .church { font-size: 9pt; letter-spacing: 2.2pt; text-transform: uppercase; color: #444; padding-top: 0.15in; }
  .cover .emblems { margin: 0.42in auto 0; border-collapse: collapse; }
  .cover .emblems td { padding: 0 0.16in; vertical-align: middle; }
  .cover .emblems img { height: 1.35in; width: auto; }
  .cover .kicker { margin-top: 0.42in; font-family: Times, "Times New Roman", serif; font-size: 15pt; letter-spacing: 3pt; text-transform: uppercase; }
  .cover .title { margin-top: 8pt; font-family: Times, "Times New Roman", serif; font-size: 30pt; line-height: 1.08; font-weight: 700; }
  .cover .date { margin-top: 12pt; font-size: 12pt; letter-spacing: 1.2pt; text-transform: uppercase; }
  .cover .rule { width: 0.9in; margin: 0.34in auto 0.3in; border-top: 1.2pt solid #111; }
  .cover .theme { font-family: Times, "Times New Roman", serif; font-style: italic; font-size: 16pt; line-height: 1.32; padding: 0 0.2in; }
  .cover .ref { margin-top: 8pt; font-size: 10pt; letter-spacing: 1.5pt; text-transform: uppercase; color: #444; }
  .cover .addr { position: absolute; left: 0; right: 0; bottom: 0.45in; font-size: 8.5pt; line-height: 1.45; color: #444; }

  /* ── Program ── */
  .ph { text-align: center; font-family: Times, "Times New Roman", serif; font-size: 15pt; letter-spacing: 2.5pt; text-transform: uppercase; margin-bottom: 4pt; }
  .ph-sub { text-align: center; font-size: 9pt; letter-spacing: 1.2pt; text-transform: uppercase; color: #555; margin-bottom: 16pt; }
  .program-row { width: 100%; border-collapse: collapse; font-family: Times, "Times New Roman", serif; font-size: {{ $pt }}pt; line-height: 1.3; margin: {{ $gap }}pt 0; }
  .program-row td { vertical-align: bottom; padding: 0; }
  .program-row td.left  { font-weight: 700; white-space: nowrap; padding-right: 4pt; }
  .program-row td.dots  { width: 100%; border-bottom: 1pt dotted #222; padding: 0 3pt 3pt 3pt; }
  .program-row td.right { font-weight: 400; white-space: nowrap; padding-left: 4pt; text-align: right; }
  .program-row td.right.italic { font-style: italic; }
  .program-centered { text-align: center; font-family: Times, "Times New Roman", serif; font-size: {{ $pt - 0.5 }}pt; font-style: italic; margin: -{{ max(0, $gap - 3) }}pt 0 {{ $gap }}pt; color: #333; }
  .cont { text-align: center; font-size: 8.5pt; color: #777; margin-top: 14pt; letter-spacing: 1pt; text-transform: uppercase; }

  /* ── Back (announcements) — same rules as the 2-up print ── */
  .back .title { text-align: center; font-size: 16pt; font-weight: 800; letter-spacing: 1pt; text-decoration: underline; margin-bottom: 9pt; }
  .section { margin-bottom: 6pt; }
  .section-title { font-size: 10.5pt; font-weight: 700; margin-bottom: 2pt; }
  .bullet-list { margin: 0; padding-left: 18pt; list-style-type: circle; }
  .bullet-list li.bf { list-style-type: disc; }
  .bullet-list li { font-size: 9.5pt; line-height: 1.24; margin: 0.5pt 0; }
  .offerings { margin: 6pt 0 0; }
  .offerings .heading { font-size: 10.5pt; font-weight: 700; margin-bottom: 2pt; }
  .offerings .line { font-size: 10pt; font-weight: 700; line-height: 1.3; }
  .mission { text-align: center; margin-top: 2pt; margin-bottom: 9pt; }
  .mission .heading { font-size: 10.5pt; font-weight: 700; text-decoration: underline; margin-bottom: 4pt; }
  .mission .text { margin: 0 auto; font-size: 9.5pt; line-height: 1.24; }
  .qr-row { margin: 8pt auto 0; border-collapse: collapse; }
  .qr-cell { vertical-align: middle; padding-right: 7pt; }
  .qr-row .qr { width: 40pt; height: 40pt; display: block; }
  .qr-cap { vertical-align: middle; text-align: left; font-size: 8pt; line-height: 1.4; color: #333; }
  .pleasant { text-align: center; margin-top: 7pt; font-size: 9.5pt; font-style: italic; }
  .watermark-prev { position: fixed; top: 0.2in; right: 0.4in; font-size: 8pt; letter-spacing: 1.5pt; text-transform: uppercase; color: #b08d3c; font-weight: 700; }
</style>
</head>
<body>
@if (!empty($isPrevious))<div class="watermark-prev">Previous version · {{ $previousPublishedAt ?? '' }}</div>@endif

{{-- ═══ OUTSIDE: back page (left) + front cover (right) ═══ --}}
<div class="sheet">
  <div class="fold"></div><div class="fold b"></div>

  <div class="col l back"><div class="page">
    <div class="title">Announcements</div>
    @php $foldedAnns = \App\Models\Bulletin::foldAnnouncements(array_values(array_filter($snapshot['announcements'] ?? [], fn ($a) => empty($a['is_web_only'])))); @endphp
    @foreach ($foldedAnns as $a)
      @php
        $aTitle = trim((string) ($a['title'] ?? '')); $aDetail = trim((string) ($a['detail'] ?? ''));
        $tLower = strtolower($aTitle); $titleColon = str_ends_with($aTitle, ':') ? $aTitle : $aTitle . ':';
      @endphp
      @if ($aTitle === '')
        @continue
      @elseif (str_contains($tLower, 'mission'))
        <section class="mission"><div class="heading">{{ $titleColon }}</div><div class="text">{{ $aDetail }}</div></section>
      @elseif (str_contains($tLower, 'offering'))
        <section class="offerings"><div class="heading">{{ $titleColon }}</div>
          @foreach (preg_split("/\r?\n/", $aDetail) as $l)@if (trim($l) !== '')<div class="line">{{ trim($l) }}</div>@endif @endforeach
        </section>
      @else
        <section class="section"><div class="section-title">{{ $titleColon }}</div>
          @if ($aDetail !== '')
            <ul class="bullet-list">
              @foreach (preg_split("/\r?\n/", $aDetail) as $b)@php $b = trim($b); $bf = str_starts_with($b, '•'); @endphp @if ($b !== '')<li @if ($bf) class="bf" @endif>{{ $bf ? ltrim(substr($b, strlen('•'))) : $b }}</li>@endif @endforeach
            </ul>
          @endif
        </section>
      @endif
    @endforeach
    <table class="qr-row"><tr>
      <td class="qr-cell"><img class="qr" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('qr-announcements.png'))) }}" alt=""></td>
      <td class="qr-cap">Scan for <b>more</b><br>thechurchofpeace.org/announcements</td>
    </tr></table>
    <div class="pleasant">Have a pleasant Sabbath :)</div>
  </div></div>

  <div class="col r"><div class="page cover">
    <div class="church">Shalom Seventh-day Adventist Church</div>
    @if ($emblems)
      <table class="emblems"><tr>@foreach ($emblems as $src)<td><img src="{{ $src }}" alt=""></td>@endforeach</tr></table>
    @else
      <div style="height: 0.5in;"></div>
    @endif
    <div class="kicker">{{ $kicker }}</div>
    <div class="title">{{ $title }}</div>
    <div class="date">{{ $date->format('F j, Y') }}</div>
    @if (!empty($cover['theme']))
      <div class="rule"></div>
      <div class="theme">&ldquo;{{ $cover['theme'] }}&rdquo;</div>
      @if (!empty($cover['theme_ref']))<div class="ref">{{ $cover['theme_ref'] }}</div>@endif
    @endif
    <div class="addr">3323 White Plains Rd, Bronx, NY 10467<br>thechurchofpeace.org</div>
  </div></div>
</div>

{{-- ═══ INSIDE: order of service across both pages ═══ --}}
<div class="sheet inside">
  <div class="fold"></div><div class="fold b"></div>
  @foreach ($halves as $h => $rows)
  <div class="col {{ $h === 0 ? 'l' : 'r' }}"><div class="page">
    @if ($h === 0)
      <div class="ph">Order of Service</div>
      <div class="ph-sub">{{ $date->format('l, F j, Y') }}</div>
    @else
      <div class="ph" style="visibility:hidden">.</div><div class="ph-sub" style="visibility:hidden">.</div>
    @endif
    @foreach ($rows as $line)
      @if (($line['kind'] ?? 'line') === 'section_header')
        <div class="program-centered">{{ $line['section'] ?? '' }}</div>
      @else
        @php
          $part = trim((string) ($line['part'] ?? '')); $person = trim((string) ($line['person'] ?? ''));
          $isGroup = $person !== '' && (stripos($person, 'team') !== false || stripos($person, 'choir') !== false || stripos($person, 'pathfinder') !== false);
        @endphp
        @if ($part !== '')
          <table class="program-row"><tr>
            <td class="left">{{ $part }}</td><td class="dots">&nbsp;</td><td class="right{{ $isGroup ? ' italic' : '' }}">{{ $person }}</td>
          </tr></table>
        @endif
      @endif
    @endforeach
  </div></div>
  @endforeach
</div>
</body>
</html>
