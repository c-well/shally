{{-- Shared admin typography — mono headers per Karlons request (admin = simpler/tighter
     than public). Public-facing views keep Cormorant Garamond. To update the rule,
     edit this one partial — every admin view @includes it. --}}
<style>
  /* No italic anywhere in admin — Karlons directive. Scoped to NOT include
     .preview-body and the markdown toolbar where italic is meaningful (it shows
     the public rendering / what the I button does). */
  body :not(.preview-body):not(.preview-body *):not([data-md="italic"]):not([data-md="italic"] *) {
    font-style: normal !important;
  }

  /* All admin headings use JetBrains Mono — admin reads as "back office" vs polished public. */
  body h1, body h2, body h3,
  body .admin-title, body .admin-h, body .page-title {
    font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Consolas, monospace !important;
    letter-spacing: 0.02em;
    font-weight: 600;
  }
  body h1, body .page-title { font-size: 22px; }
  body h2 { font-size: 16px; }
  body h3 { font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-soft, #334455); }
  /* Tighten label affordance too — admin likes terse mono labels */
  body label, body .admin-label, body .field-label {
    font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    letter-spacing: 0.06em;
    font-size: 11px;
    text-transform: uppercase;
    color: var(--ink-soft, #334455);
    font-weight: 500;
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
