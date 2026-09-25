{{-- Shared admin typography — Instrument Sans / Newsreader / JetBrains Mono.
     ADMIN ONLY — public-facing views keep their own fonts. To update the rule,
     edit this one partial; every admin view @includes it.

     2026-09-25, Karlon picked Option A from _ops-admin-type.html. This is the
     mail room's own type system moved to the rest of the admin, so the back
     end reads as one thing rather than two.

     It replaces Varela Round + Noto Serif, which failed for a measurable
     reason rather than a matter of taste: Varela Round is published in a
     SINGLE weight (400). Every `font-weight: 600` below used to be synthesised
     by the browser smearing the 400, which is what made the whole admin look
     thickened and slightly blurred. Instrument Sans carries 400–700, so
     hierarchy now comes from the font instead of from a guess.

     Three faces, three jobs — do not mix them up:
       Instrument Sans   interface. Headings, names, labels, buttons, meta.
       Newsreader        record titles and anything read as prose. Optical
                         sizing, so it holds at 17px and at 15px alike.
       JetBrains Mono    COLUMNAR data only — a date on a row, a file size, a
                         log line, code. A headline figure is a quantity, not
                         a column: it stays in Instrument Sans with
                         tabular-nums. Monospace forces every digit to the
                         same advance and puts a gap inside "30". --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

{{-- Shared admin shell — the responsive floor under every admin page. Loads
     AFTER each page's own <style> (this partial is included last in every
     admin head), so it can correct a page that never considered a phone.
     Source of truth: resources/admin/admin.css — edit there and copy to
     public_html/css/, because public/ is a symlink outside the repo. --}}
<link rel="stylesheet" href="/css/admin.css?v={{ @filemtime(public_path('css/admin.css')) ?: 1 }}">
<style>
  /* No italic anywhere in admin — Karlons directive. Scoped to NOT include
     .preview-body and the markdown toolbar where italic is meaningful (it shows
     the public rendering / what the I button does). */
  body :not(.preview-body):not(.preview-body *):not([data-md="italic"]):not([data-md="italic"] *) {
    font-style: normal !important;
  }

  /* ── Interface — Instrument Sans ────────────────────────────────────────
     The default for everything. !important because thirty admin blades each
     declare their own inline Poppins/system stack and this partial has to
     outrank all of them without editing any. */
  body, body p, body td, body th, body li, body span, body div, body a,
  body input, body select, body textarea, body button {
    font-family: "Instrument Sans", system-ui, -apple-system, sans-serif !important;
  }

  /* ── Prose — Newsreader ─────────────────────────────────────────────────
     A record title is the thing you are scanning the list for, and a lede or
     a summary is read rather than operated. Both get the serif. Declared
     after the sans so it wins, and kept to a short list so the serif never
     leaks into controls. */
  body .lede, body .prose, body .preview-body, body .preview-body p,
  body .row .title, body .sermon-row .title, body .lesson-row .title,
  body .poll-row .title, body .item-row .title,
  body .rec-title, body .entry-title, body .msg-subject, body .subj,
  body .summary-text, body textarea.prose, body .blurb {
    font-family: "Newsreader", Georgia, "Times New Roman", serif !important;
    font-optical-sizing: auto;
    letter-spacing: -0.004em;
  }
  body .lede, body .prose, body .preview-body { line-height: 1.62; }

  /* ── Columns — JetBrains Mono ───────────────────────────────────────────
     Only where digits or tokens stack in a column and must line up: the date
     on a record row, a file size, a log timestamp, code. NOT for headline
     figures — see .num/.stat-num below. */
  body code, body pre, body .mono, body kbd, body samp,
  body .row .date, body .sermon-row .date, body .lesson-row .date,
  body .stamp, body .ts, body .addr, body .filesize, body .fsize, body .uid {
    font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, monospace !important;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.01em;
  }

  /* ── Figures ────────────────────────────────────────────────────────────
     A count on a tile is read as a quantity. It stays in the interface face
     and gets tabular figures, which is the only thing monospace was buying. */
  body .num, body .stat-num, body .stat-value, body .count, body .figure,
  body .st-n, body .metric {
    font-family: "Instrument Sans", system-ui, sans-serif !important;
    font-variant-numeric: tabular-nums lining-nums;
    font-weight: 600;
    letter-spacing: -0.02em;
  }
  /* Any cell of numbers aligns, whatever face it ended up in. */
  body td, body th, body .n, body .amount, body .qty { font-variant-numeric: tabular-nums; }

  /* ── Headings and labels ────────────────────────────────────────────────
     Instrument Sans has real weights now, so hierarchy is weight + size, and
     display sizes take NEGATIVE tracking — Varela Round's positive 0.02em was
     right for a geometric round and is loose here. Uppercase micro-labels are
     the exception and keep their positive tracking, because that is what
     makes small caps legible. */
  body h1, body h2, body h3, body h4,
  body .admin-title, body .admin-h, body .page-title {
    font-family: "Instrument Sans", system-ui, sans-serif !important;
    letter-spacing: -0.02em;
    font-weight: 600;
    text-wrap: balance;
  }
  body label, body .admin-label, body .field-label,
  body .label, body .eyebrow, body .card-eyebrow, body .meta {
    font-family: "Instrument Sans", system-ui, sans-serif !important;
    font-weight: 600;
  }
  body h1, body .page-title { font-size: 22px; line-height: 1.18; }
  body h2 { font-size: 16px; line-height: 1.25; }
  body h3 { font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-soft, #334455); }
  body label, body .admin-label, body .field-label {
    letter-spacing: 0.06em;
    font-size: 11px;
    text-transform: uppercase;
    color: var(--ink-soft, #334455);
    font-weight: 600;
  }
</style>

@if (auth()->check() && auth()->user()->role === 'super_admin')
{{-- ─────────────────────────────────────────────────────────────────────
     DARK MODE — super-admin only (Karlon + André).
     The toggle UI lives in the welcome page Admin ▾ dropdown — tucked
     next to the user name. This partial just provides the CSS rules and
     a head-script that applies the class before paint to prevent FOUC.
     Both surfaces share localStorage key 'cop_admin_theme'.

     Color math from #191919:
       LIGHT   →   DARK
       --------    --------
       bg     #faf7f0  →  #191919
       surface #fff    →  #222222   (+3.5% L lift)
       elev    shadow  →  #2a2a2a   (+6.7% L lift)
       line   .12 ink  →  rgba(255,255,255,0.10)
       ink    #1a2332  →  #ededed   (13.7:1 ✓ AAA)
       softer #5a6478  →  #9e9e9e   (6.4:1 ✓ AA)
       teal   #03617a  →  #4fb8d4   (7.5:1 ✓ AAA)
       brass  #b08a3e  →  #d4a85a   (7.6:1 ✓ AAA)
       danger #c0392b  →  #ec7e72
       green  #2d8659  →  #5ec790
     ───────────────────────────────────────────────────────────────────── --}}
<style>
  @media (prefers-reduced-motion: no-preference) {
    html.dark, html.dark body, html.dark .card, html.dark .tile, html.dark .top,
    html.dark input, html.dark select, html.dark textarea, html.dark .btn,
    html.dark header, html.dark main, html.dark footer {
      transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
    }
  }

  html.dark { color-scheme: dark; }
  html.dark body { background: #191919 !important; color: #ededed !important; }

  html.dark .card, html.dark .tile, html.dark .panel, html.dark .field-card,
  html.dark .table-wrap, html.dark .stat, html.dark .stat-card, html.dark .row-card,
  html.dark .preview-card, html.dark .editor-card, html.dark .upload-zone,
  html.dark .filter-bar, html.dark .empty, html.dark .flash, html.dark .form-row,
  html.dark fieldset, html.dark .composer {
    background: #222222 !important;
    border-color: rgba(255,255,255,0.10) !important;
    color: #ededed !important;
    box-shadow: none !important;
  }

  html.dark header.top, html.dark .top {
    background: #191919 !important;
    border-bottom-color: rgba(255,255,255,0.10) !important;
    color: #9e9e9e !important;
  }

  html.dark .lede, html.dark .meta, html.dark .sub, html.dark .muted,
  html.dark .hint, html.dark .caption, html.dark small,
  html.dark .card-eyebrow, html.dark .card-sub, html.dark .card-arrow,
  html.dark .field-help, html.dark .empty p, html.dark footer, html.dark th {
    color: #9e9e9e !important;
  }

  html.dark h1, html.dark h2, html.dark h3,
  html.dark .card-title, html.dark .name, html.dark .u-name,
  html.dark .stat-num, html.dark .stat-value, html.dark td {
    color: #ededed !important;
  }
  html.dark h3 { color: #9e9e9e !important; }

  html.dark hr, html.dark .divider, html.dark .table tr, html.dark .table td,
  html.dark .table th, html.dark .form-row + .form-row {
    border-color: rgba(255,255,255,0.10) !important;
  }

  html.dark input[type="text"], html.dark input[type="email"], html.dark input[type="password"],
  html.dark input[type="number"], html.dark input[type="search"], html.dark input[type="url"],
  html.dark input[type="date"], html.dark input[type="time"], html.dark input[type="datetime-local"],
  html.dark select, html.dark textarea {
    background: #2a2a2a !important;
    color: #ededed !important;
    border: 1px solid rgba(255,255,255,0.14) !important;
  }
  html.dark input::placeholder, html.dark textarea::placeholder { color: #6e6e6e !important; }
  html.dark input:focus, html.dark select:focus, html.dark textarea:focus {
    border-color: #4fb8d4 !important;
    outline: 2px solid rgba(79,184,212,0.25) !important;
    outline-offset: 1px;
  }

  html.dark .btn, html.dark button:not(.theme-toggle button), html.dark .copy,
  html.dark .ghost, html.dark .btn-ghost, html.dark .day-foot-nav button {
    background: #2a2a2a !important;
    color: #ededed !important;
    border-color: rgba(255,255,255,0.14) !important;
  }
  html.dark .btn:hover, html.dark .copy:hover, html.dark .ghost:hover,
  html.dark .btn-ghost:hover {
    background: #333333 !important;
    border-color: #4fb8d4 !important;
    color: #4fb8d4 !important;
  }

  html.dark .btn-primary, html.dark button.primary,
  html.dark button[type="submit"]:not(.del):not(.danger) {
    background: #4fb8d4 !important;
    color: #0c1e23 !important;
    border-color: #4fb8d4 !important;
  }
  html.dark .btn-primary:hover { background: #6cc8e0 !important; border-color: #6cc8e0 !important; }

  html.dark .del, html.dark .danger, html.dark button.del {
    background: transparent !important;
    color: #ec7e72 !important;
    border-color: rgba(236,126,114,0.4) !important;
  }
  html.dark .del:hover { background: rgba(236,126,114,0.10) !important; }

  html.dark a:not(.card):not(.btn):not(.btn-primary):not(.btn-ghost):not(.tile):not(.copy) {
    color: #4fb8d4;
  }
  html.dark a:not(.card):not(.btn):hover { color: #6cc8e0; }

  html.dark .brass, html.dark .archive-line, html.dark .badge.brass { color: #d4a85a !important; }

  html.dark .status-ok, html.dark .badge-ok { background: rgba(94,199,144,0.15) !important; color: #5ec790 !important; }
  html.dark .status-warn, html.dark .badge-warn { background: rgba(212,168,90,0.15) !important; color: #d4a85a !important; }
  html.dark .status-err, html.dark .badge-err { background: rgba(236,126,114,0.15) !important; color: #ec7e72 !important; }

  html.dark tbody tr:hover { background: rgba(255,255,255,0.04) !important; }

  html.dark code, html.dark pre, html.dark .mono {
    background: #2a2a2a !important;
    color: #d4a85a !important;
  }

  html.dark .filter-pill, html.dark .pill {
    background: #2a2a2a !important;
    color: #9e9e9e !important;
    border-color: rgba(255,255,255,0.10) !important;
  }
  html.dark .filter-pill.active, html.dark .pill.active {
    background: #4fb8d4 !important;
    color: #0c1e23 !important;
    border-color: #4fb8d4 !important;
  }

  html.dark .trendline path, html.dark svg.trendline path { stroke: #4fb8d4 !important; }
  html.dark .trendline-fill, html.dark svg.trendline .area { fill: rgba(79,184,212,0.10) !important; }
</style>

{{-- FOUC prevention: applied before body paints. The toggle UI itself lives
     in the welcome page Admin ▾ menu (welcome.blade.php). --}}
<script>
  (function () {
    try {
      if (localStorage.getItem('cop_admin_theme') === 'dark') {
        document.documentElement.classList.add('dark');
      }
    } catch (e) {}
  })();
</script>
@endif
