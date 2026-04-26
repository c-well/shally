{{-- ────────────────────────────────────────────────────────────────────
     Dark mode — global partial.
     Gated to super_admin (Karlon + André) so guests, clerks, and search
     engines never inherit a dark page from a stray localStorage value.

     Usage: @include('partials.dark-mode') inside every public view's <head>.
     The toggle UI itself lives in the welcome.blade.php Admin ▾ menu.

     Color math from #191919:
       LIGHT   →   DARK
       --parchment #faf7f0/#fefcef → #191919
       --cream     #f1ebf9 et al   → #222222
       --ink       #1a2332         → #ededed   (13.7:1 ✓ AAA)
       --ink-soft  #5a6478/#334455 → #9e9e9e   (6.4:1 ✓ AA)
       --teal      #03617a         → #4fb8d4   (7.5:1 ✓ AAA)
       --teal-dark #014a5e         → #6cc8e0
       --line      .12 ink         → rgba(255,255,255,0.10)
       --brass     #b08a3e         → #d4a85a   (7.6:1 ✓ AAA)
     ──────────────────────────────────────────────────────────────────── --}}
@if (auth()->check() && auth()->user()->role === 'super_admin')
{{-- FOUC prevention — runs in <head> before body paints. --}}
<script>
  (function () {
    try {
      if (localStorage.getItem('cop_admin_theme') === 'dark') {
        document.documentElement.classList.add('dark');
      }
    } catch (e) {}
  })();
</script>

<style>
  /* ── Soft transition when toggling — but respect motion preference ── */
  @media (prefers-reduced-motion: no-preference) {
    html.dark, html.dark body, html.dark .card, html.dark .tile, html.dark .top,
    html.dark input, html.dark select, html.dark textarea, html.dark .btn,
    html.dark header, html.dark main, html.dark footer, html.dark .panel,
    html.dark .admin-panel, html.dark .verse-modal-card, html.dark .player,
    html.dark .day-panel, html.dark .day-cell, html.dark .sermon-card {
      transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
    }
  }

  html.dark { color-scheme: dark; }

  /* ── Re-map CSS variables — base dark palette for default theme ── */
  html.dark, html.dark body,
  html.dark body[data-theme],
  html.dark body[data-theme="default"] {
    --parchment:   #191919;
    --cream:       #222222;
    --ink:         #ededed;
    --ink-soft:    #9e9e9e;
    --teal:        #4fb8d4;             /* cyan — default accent */
    --teal-dark:   #6cc8e0;
    --teal-light:  rgba(79,184,212,0.14);
    --line:        rgba(255,255,255,0.10);
    --brass:       #d4a85a;
    --bg:          #191919;
    --surface:     #222222;
    --surface-elev:#2a2a2a;
  }

  /* ── Per-theme dark accents (Item 4)
        Each season keeps a recognizable identity in dark mode. All accents
        calibrated for ≥ 4.5:1 contrast on #191919 (text) and ≥ 3:1 (large/UI).
        ── */

  /* Communion → desaturated lavender (light mode: #6b4d8a → dark: #b08bd1, 7.4:1) */
  html.dark body[data-theme="communion"] {
    --parchment:   #191919;
    --cream:       #221d2a;
    --ink:         #ededed;
    --ink-soft:    #b0a8c0;
    --teal:        #b08bd1;
    --teal-dark:   #c2a3dc;
    --teal-light:  rgba(176,139,209,0.14);
    --line:        rgba(176,139,209,0.16);
    --brass:       #d4a85a;
  }

  /* Easter → soft mint (light: #3a8e63 → dark: #6dd49d, 8.1:1) */
  html.dark body[data-theme="easter"] {
    --parchment:   #191919;
    --cream:       #1d2620;
    --ink:         #ededed;
    --ink-soft:    #a3c0b0;
    --teal:        #6dd49d;
    --teal-dark:   #88dcaf;
    --teal-light:  rgba(109,212,157,0.14);
    --line:        rgba(109,212,157,0.16);
    --brass:       #d4c25a;
  }

  /* Christmas → warm rose (light: #8b3a4b → dark: #e88b9c, 7.0:1) */
  html.dark body[data-theme="christmas"] {
    --parchment:   #191919;
    --cream:       #261d1f;
    --ink:         #ededed;
    --ink-soft:    #c0a8ad;
    --teal:        #e88b9c;
    --teal-dark:   #f0a3b1;
    --teal-light:  rgba(232,139,156,0.14);
    --line:        rgba(232,139,156,0.16);
    --brass:       #d4a85a;
  }

  /* Mother's Day → dusty pink (light: #b1657a → dark: #efa3b8, 8.5:1) */
  html.dark body[data-theme="mothers"] {
    --parchment:   #191919;
    --cream:       #261e21;
    --ink:         #ededed;
    --ink-soft:    #c8b0b8;
    --teal:        #efa3b8;
    --teal-dark:   #f5b8c8;
    --teal-light:  rgba(239,163,184,0.14);
    --line:        rgba(239,163,184,0.16);
    --brass:       #d4a85a;
  }

  /* Thanksgiving → autumn amber (light: #8a5a2c → dark: #d4a368, 7.7:1) */
  html.dark body[data-theme="thanksgiving"] {
    --parchment:   #191919;
    --cream:       #26201a;
    --ink:         #ededed;
    --ink-soft:    #c0b0a0;
    --teal:        #d4a368;
    --teal-dark:   #e0b585;
    --teal-light:  rgba(212,163,104,0.14);
    --line:        rgba(212,163,104,0.16);
    --brass:       #d4a85a;
  }

  /* ── Body + chrome ── */
  html.dark body { background: #191919 !important; color: #ededed !important; }
  html.dark html, html.dark { background: #191919 !important; }

  /* Headers + nav strips (most pages call this .top or use header tag) */
  html.dark header,
  html.dark header.top,
  html.dark .top,
  html.dark nav.site-nav,
  html.dark .lesson-header,
  html.dark .peace-notes-header,
  html.dark .sermon-header,
  html.dark .page-header {
    background: #191919 !important;
    border-bottom-color: rgba(255,255,255,0.10) !important;
    color: #9e9e9e !important;
  }

  /* Footer */
  html.dark footer,
  html.dark .site-footer,
  html.dark .footer-brand {
    background: #191919 !important;
    color: #9e9e9e !important;
    border-top-color: rgba(255,255,255,0.10) !important;
  }

  /* ── Container surfaces ── */
  html.dark .card, html.dark .tile, html.dark .panel,
  html.dark .field-card, html.dark .table-wrap,
  html.dark .stat, html.dark .stat-card, html.dark .row-card,
  html.dark .preview-card, html.dark .editor-card, html.dark .upload-zone,
  html.dark .filter-bar, html.dark .empty, html.dark .flash,
  html.dark .form-row, html.dark fieldset, html.dark .composer,
  html.dark .verse-modal-card,
  html.dark .day-panel, html.dark .day-tab-strip,
  html.dark .sermon-card, html.dark .peace-notes-card,
  html.dark .player, html.dark .audio-player,
  html.dark .archive-card, html.dark .info-card,
  html.dark .admin-panel {
    background: #222222 !important;
    border-color: rgba(255,255,255,0.10) !important;
    color: #ededed !important;
    box-shadow: none !important;
  }
  /* Elevated panels (modals, dropdowns) get a subtle lift */
  html.dark .admin-panel,
  html.dark .verse-modal-card {
    background: #222222 !important;
    box-shadow: 0 18px 56px -10px rgba(0,0,0,0.65) !important;
  }
  /* The verse modal backdrop — keep dim but readable */
  html.dark .verse-modal { background: rgba(0,0,0,0.70) !important; }

  /* ── Typography ── */
  html.dark h1, html.dark h2, html.dark h3, html.dark h4,
  html.dark .card-title, html.dark .name, html.dark .u-name,
  html.dark .stat-num, html.dark .stat-value, html.dark td,
  html.dark .week-title, html.dark .day-title, html.dark .lesson-title,
  html.dark .sermon-title, html.dark .peace-notes-title, html.dark .theme {
    color: #ededed !important;
  }
  html.dark h3 { color: #9e9e9e !important; }

  html.dark p, html.dark .prose, html.dark .prose p, html.dark .prose li,
  html.dark .body-copy {
    color: #ededed !important;
  }

  html.dark .lede, html.dark .meta, html.dark .sub, html.dark .muted,
  html.dark .hint, html.dark .caption, html.dark small,
  html.dark .card-eyebrow, html.dark .card-sub, html.dark .card-arrow,
  html.dark .field-help, html.dark .empty p, html.dark th,
  html.dark .eyebrow, html.dark .day-cell .dow, html.dark .day-cell .dnum,
  html.dark .scripture-meta, html.dark .speaker-meta, html.dark .archive-line,
  html.dark .copyright, html.dark .player-meta, html.dark .player-utils {
    color: #9e9e9e !important;
  }

  /* Brass / amber accent text */
  html.dark .brass, html.dark .badge.brass, html.dark .pill-active,
  html.dark .verse-modal-body sup, html.dark .day-cell.active .dow,
  html.dark .preached-on {
    color: #d4a85a !important;
  }

  /* ── Borders, dividers, hairlines ── */
  html.dark hr, html.dark .divider,
  html.dark .table tr, html.dark .table td, html.dark .table th,
  html.dark .form-row + .form-row,
  html.dark .day-strip, html.dark .day-cell,
  html.dark blockquote, html.dark .archive-line {
    border-color: rgba(255,255,255,0.10) !important;
  }

  /* ── Form controls ── */
  html.dark input[type="text"], html.dark input[type="email"],
  html.dark input[type="password"], html.dark input[type="number"],
  html.dark input[type="search"], html.dark input[type="url"],
  html.dark input[type="date"], html.dark input[type="time"],
  html.dark input[type="datetime-local"],
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

  /* ── Buttons ── */
  html.dark .btn, html.dark .btn-ghost, html.dark .copy, html.dark .ghost,
  html.dark .day-foot-nav button {
    background: #2a2a2a !important;
    color: #ededed !important;
    border-color: rgba(255,255,255,0.14) !important;
  }
  html.dark .btn:hover, html.dark .btn-ghost:hover, html.dark .copy:hover,
  html.dark .ghost:hover, html.dark .day-foot-nav button:hover {
    background: #333333 !important;
    border-color: #4fb8d4 !important;
    color: #4fb8d4 !important;
  }

  /* Primary CTA */
  html.dark .btn-primary, html.dark .btn.primary,
  html.dark button.primary, html.dark a.btn-primary {
    background: #4fb8d4 !important;
    color: #0c1e23 !important;
    border-color: #4fb8d4 !important;
  }
  html.dark .btn-primary:hover, html.dark .btn.primary:hover {
    background: #6cc8e0 !important;
    border-color: #6cc8e0 !important;
  }

  /* Danger */
  html.dark .del, html.dark .danger, html.dark button.del,
  html.dark .btn-danger {
    background: transparent !important;
    color: #ec7e72 !important;
    border-color: rgba(236,126,114,0.4) !important;
  }
  html.dark .del:hover, html.dark .btn-danger:hover {
    background: rgba(236,126,114,0.10) !important;
  }

  /* ── Links ── */
  html.dark a:not(.card):not(.btn):not(.btn-primary):not(.btn-ghost):not(.tile):not(.copy):not(.day-cell):not(.brand):not(.admin-trigger):not(.admin-panel-item):not(.theme-toggle button) {
    color: #4fb8d4;
  }
  html.dark a:not(.card):not(.btn):not(.tile):hover { color: #6cc8e0; }

  /* ── Lesson page specifics ── */
  html.dark .prose blockquote {
    background: rgba(255,255,255,0.03) !important;
    border-left-color: #4fb8d4 !important;
    color: #ededed !important;
  }
  html.dark .prose blockquote.memory-verse {
    background: rgba(79,184,212,0.10) !important;
    border-left-color: #4fb8d4 !important;
  }
  html.dark .prose a.verse {
    background: rgba(79,184,212,0.14) !important;
    color: #4fb8d4 !important;
  }
  html.dark .prose a.verse:hover {
    background: rgba(79,184,212,0.26) !important;
  }
  html.dark .prose a:not(.verse) {
    color: #4fb8d4 !important;
  }
  html.dark .day-cell {
    background: #222222 !important;
    color: #9e9e9e !important;
    border-color: rgba(255,255,255,0.10) !important;
  }
  html.dark .day-cell:hover {
    border-color: #4fb8d4 !important;
    color: #ededed !important;
  }
  html.dark .day-cell.active {
    background: #4fb8d4 !important;
    color: #0c1e23 !important;
    border-color: #4fb8d4 !important;
  }
  html.dark .day-cell.active .dow,
  html.dark .day-cell.active .dnum {
    color: #0c1e23 !important;
  }
  html.dark .verse-modal-body { color: #ededed !important; }
  html.dark .verse-modal-body h2 { color: #4fb8d4 !important; }
  html.dark .verse-modal-body sup { color: #d4a85a !important; }
  html.dark .verse-modal-head .label { color: #4fb8d4 !important; }
  html.dark .verse-modal-head { border-bottom-color: rgba(255,255,255,0.10) !important; }

  /* Font sizer fixed button */
  html.dark .font-toggle {
    background: #222222 !important;
    border-color: rgba(255,255,255,0.14) !important;
    color: #9e9e9e !important;
  }
  html.dark .font-toggle:hover {
    border-color: #4fb8d4 !important;
    color: #4fb8d4 !important;
  }

  /* ── Peace notes archive ── */
  html.dark .sermon-card, html.dark .peace-notes-card {
    background: #222222 !important;
    border-color: rgba(255,255,255,0.10) !important;
  }
  html.dark .sermon-card:hover {
    border-color: #4fb8d4 !important;
  }
  html.dark .sermon-card .title,
  html.dark .sermon-card h3 {
    color: #ededed !important;
  }
  html.dark .date-overlay,
  html.dark .sermon-card .date {
    background: rgba(0,0,0,0.6) !important;
    color: #ededed !important;
  }

  /* ── Sermon-show audio player ── */
  html.dark .player {
    background: #222222 !important;
    border: 1px solid rgba(255,255,255,0.10) !important;
  }
  html.dark .play-btn {
    background: #4fb8d4 !important;
    color: #0c1e23 !important;
  }
  html.dark .play-btn:hover { background: #6cc8e0 !important; }
  html.dark .skip-btn {
    background: #2a2a2a !important;
    color: #ededed !important;
    border-color: rgba(255,255,255,0.14) !important;
  }
  html.dark .skip-btn:hover {
    background: #333333 !important;
    color: #4fb8d4 !important;
    border-color: #4fb8d4 !important;
  }
  html.dark .progress-bar {
    background: rgba(255,255,255,0.10) !important;
  }
  html.dark .progress-buffer {
    background: rgba(255,255,255,0.18) !important;
  }
  html.dark .progress-fill {
    background: #4fb8d4 !important;
  }
  html.dark .progress-handle {
    background: #ededed !important;
    border-color: #4fb8d4 !important;
  }
  html.dark .speed-pill {
    background: #2a2a2a !important;
    color: #9e9e9e !important;
    border-color: rgba(255,255,255,0.14) !important;
  }
  html.dark .speed-pill.active {
    background: #4fb8d4 !important;
    color: #0c1e23 !important;
    border-color: #4fb8d4 !important;
  }
  html.dark .player-time, html.dark .time-current, html.dark .time-total {
    color: #9e9e9e !important;
  }

  /* ── Hero slider (landing page) ── */
  /* Slides themselves are photographs — leave them alone. Chrome only. */
  html.dark .slider-dots .dot {
    background: rgba(255,255,255,0.30) !important;
  }
  html.dark .slider-dots .dot.active {
    background: #ededed !important;
  }
  html.dark .slider-arrow {
    background: rgba(0,0,0,0.6) !important;
    color: #ededed !important;
  }
  html.dark .slider-arrow:hover {
    background: rgba(0,0,0,0.85) !important;
  }
  /* Landing page brand "Shalom" wordmark stays its assigned color */

  /* ── Status pills / badges ── */
  html.dark .status-ok, html.dark .badge-ok {
    background: rgba(94,199,144,0.15) !important;
    color: #5ec790 !important;
  }
  html.dark .status-warn, html.dark .badge-warn {
    background: rgba(212,168,90,0.15) !important;
    color: #d4a85a !important;
  }
  html.dark .status-err, html.dark .badge-err {
    background: rgba(236,126,114,0.15) !important;
    color: #ec7e72 !important;
  }

  /* ── Tables ── */
  html.dark tbody tr:hover { background: rgba(255,255,255,0.04) !important; }

  /* ── Code / mono ── */
  html.dark code, html.dark pre, html.dark .mono {
    background: #2a2a2a !important;
    color: #d4a85a !important;
  }

  /* ── Filter pills (analytics, media, peace-notes) ── */
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

  /* ── SVG charts ── */
  html.dark .trendline path, html.dark svg.trendline path {
    stroke: #4fb8d4 !important;
  }
  html.dark .trendline-fill, html.dark svg.trendline .area {
    fill: rgba(79,184,212,0.10) !important;
  }

  /* ── Error pages (500/502/503/504/404) ── */
  html.dark .error-shell {
    background: #191919 !important;
    color: #ededed !important;
  }
  html.dark .error-shell h1,
  html.dark .error-shell .number {
    color: #ededed !important;
  }
  html.dark .error-shell p {
    color: #9e9e9e !important;
  }
  html.dark .error-shell a {
    color: #4fb8d4 !important;
  }
  html.dark .error-shell .accent,
  html.dark .reconnecting-dot {
    background: #4fb8d4 !important;
    color: #4fb8d4 !important;
  }

  /* ── Image rendering hint — keep photos warm-looking on dark via a hair of brightness loss ── */
  html.dark .photo-warm img,
  html.dark .sermon-card img {
    filter: brightness(0.92) saturate(1.05);
  }

  /* ── Selection highlight ── */
  html.dark ::selection {
    background: rgba(79,184,212,0.35);
    color: #ededed;
  }

  /* ── Critical button overrides — force visible labels in dark mode ── */
  html.dark .admin-badge { background: rgba(40,40,40,0.95) !important; color: #ededed !important; }
  html.dark .admin-badge:hover { background: #4fb8d4 !important; color: #0c1e23 !important; }
  html.dark .admin-badge svg { color: #ededed !important; opacity: 0.85; }
  html.dark .admin-badge:hover svg { color: #0c1e23 !important; opacity: 1; }

  /* Force button labels visible — text-color !important across ALL button shapes.
     Catches custom buttons that hardcoded white text against navy backgrounds and
     are now showing white-on-teal in dark mode. */
  html.dark button, html.dark .btn, html.dark a.btn,
  html.dark .admin-trigger, html.dark .admin-panel-item,
  html.dark .play-btn, html.dark .skip-btn, html.dark .copy,
  html.dark .speed-pill, html.dark .filter-pill, html.dark .pill {
    color: #ededed !important;
  }
  html.dark .btn-primary, html.dark .btn.primary,
  html.dark button[type="submit"]:not(.del):not(.danger):not(.admin-panel-item),
  html.dark .admin-trigger-go-live,
  html.dark .play-btn,
  html.dark .day-cell.active,
  html.dark .filter-pill.active, html.dark .pill.active,
  html.dark .speed-pill.active {
    color: #0c1e23 !important;
  }
  /* Spans / labels nested inside buttons — inherit, not white */
  html.dark button *, html.dark .btn *, html.dark .admin-trigger * {
    color: inherit !important;
  }

  /* ── Admin ▾ dropdown panel — guarantee text is readable in dark mode.
     High-specificity override beats any conflicting button[type=submit] / .btn rules. --- */
  html.dark body .admin-panel .admin-panel-item,
  html.dark body .admin-panel .admin-panel-item span,
  html.dark body .admin-panel .admin-panel-item:not(:hover) {
    color: #ededed !important;
  }
  html.dark body .admin-panel .admin-panel-item:hover,
  html.dark body .admin-panel .admin-panel-item:hover span {
    color: #4fb8d4 !important;
  }
  /* Go Live items keep their green identity even in dark mode */
  html.dark body .admin-panel .admin-panel-item-go-live,
  html.dark body .admin-panel .admin-panel-item-go-live span {
    color: #5ec790 !important;
  }
  html.dark body .admin-panel .admin-panel-item-go-live:hover,
  html.dark body .admin-panel .admin-panel-item-go-live:hover span {
    color: #7ed4a4 !important;
  }
  /* Danger items (e.g. Delete bulletin) — keep coral */
  html.dark body .admin-panel .admin-panel-item-danger,
  html.dark body .admin-panel .admin-panel-item-danger span {
    color: #ec7e72 !important;
  }
  /* Section labels stay muted */
  html.dark body .admin-panel .admin-panel-label { color: #9e9e9e !important; }
  /* Sign-out form button — explicitly part of admin-panel-item; reset its bg too
     since it inherited the wider dark[type=submit] teal bg before the :not exclusion. */
  html.dark body .admin-panel form button.admin-panel-item {
    background: transparent !important;
    border: 0 !important;
  }

  /* ── Department filter "All" pill — its inline --dept-color is var(--ink),
     which flips to near-white in dark mode and made the white "All" label vanish
     against a white-on-white background. Force a cyan accent + dark ink in dark. */
  html.dark .dept-pill {
    color: #ededed !important;
    border-color: rgba(255,255,255,0.18) !important;
  }
  html.dark .dept-pill:hover {
    background: rgba(255,255,255,0.06) !important;
    color: #ededed !important;
  }
  html.dark .dept-pill[data-filter="all"] {
    --dept-color: #4fb8d4 !important;
  }
  html.dark .dept-pill.active {
    background: var(--dept-color, #4fb8d4) !important;
    color: #0c1e23 !important;
    border-color: var(--dept-color, #4fb8d4) !important;
  }

  /* ── Catch-all sweep — surfaces using hardcoded #fff or #1a2332 in their
     individual blade files. Listed exhaustively so we cover every page. ── */
  /* Surfaces that should be lifted to #222 in dark */
  html.dark .info,
  html.dark .address-info,
  html.dark .icon-btn,
  html.dark .audio-player,
  html.dark .player-card,
  html.dark .schedule-card,
  html.dark .feedback-form,
  html.dark .lightbox,
  html.dark .lightbox-card,
  html.dark .image-lightbox,
  html.dark .img-lightbox,
  html.dark .verse-modal-card,
  html.dark .feature-card,
  html.dark .event-card,
  html.dark .flyer-card,
  html.dark .card-info,
  html.dark .composer,
  html.dark .editor,
  html.dark .editor-wrap,
  html.dark .preview,
  html.dark .preview-wrap,
  html.dark .reader,
  html.dark .lightbox-body-wrap,
  html.dark .search-overlay .inner,
  html.dark .search-result,
  html.dark .search-input,
  html.dark .icon-btn,
  html.dark .nav-mobile-toggle,
  html.dark .has-menu .submenu {
    background: #222222 !important;
    border-color: rgba(255,255,255,0.10) !important;
    color: #ededed !important;
  }

  /* Search overlay specifics — its own scrim + inner panel */
  html.dark .search-overlay { background: rgba(0,0,0,0.78) !important; }
  html.dark .search-tab { color: #9e9e9e !important; border-color: rgba(255,255,255,0.10) !important; }
  html.dark .search-tab.active { color: #ededed !important; border-color: #4fb8d4 !important; }
  html.dark .search-result { background: #2a2a2a !important; border-color: rgba(255,255,255,0.10) !important; color: #ededed !important; }
  html.dark .search-result:hover { background: #333 !important; border-color: #4fb8d4 !important; }
  html.dark .search-result .verse-num,
  html.dark .search-result .ref-tag,
  html.dark .search-result .scope-tag { color: #d4a85a !important; }
  html.dark .search-hint, html.dark .search-loading { color: #9e9e9e !important; }
  html.dark .lightbox-translation-toggle button { color: #9e9e9e !important; border-color: rgba(255,255,255,0.14) !important; }
  html.dark .lightbox-translation-toggle button.active { background: #4fb8d4 !important; color: #0c1e23 !important; border-color: #4fb8d4 !important; }
  html.dark .lightbox-share { background: #2a2a2a !important; color: #ededed !important; border-color: rgba(255,255,255,0.14) !important; }
  html.dark .lightbox-share:hover { background: #333 !important; color: #4fb8d4 !important; border-color: #4fb8d4 !important; }
  html.dark .verse-row { color: #ededed !important; }
  html.dark .verse-row.selected { background: rgba(79,184,212,0.14) !important; }
  html.dark .verse-row .verse-num { color: #d4a85a !important; }
  html.dark .hymn-verse-num { color: #d4a85a !important; }
  html.dark .share-toast { background: #ededed !important; color: #191919 !important; }

  /* Bulletin editor surfaces (welcome.blade.php) — lots of inline #fff backgrounds */
  html.dark .undo-toast { background: rgba(212,168,90,0.95) !important; color: #0c1e23 !important; }
  html.dark .undo-toast button { background: #d4a85a !important; color: #0c1e23 !important; }
  html.dark .undo-toast button:hover { background: #e0b86a !important; }
  html.dark .icon-btn { background: #2a2a2a !important; border-color: rgba(255,255,255,0.14) !important; color: #ededed !important; }
  html.dark .icon-btn:hover { background: #333 !important; border-color: #4fb8d4 !important; color: #4fb8d4 !important; }
  html.dark .preview-banner { background: rgba(212,168,90,0.16) !important; color: #d4a85a !important; border-color: rgba(212,168,90,0.30) !important; }
  html.dark .preview-banner a, html.dark .preview-exit { color: #d4a85a !important; }

  /* Department schedule (schedule.blade.php) */
  html.dark .dept-row, html.dark .dept-card { background: #222222 !important; border-color: rgba(255,255,255,0.10) !important; }
  html.dark .role-cell { color: #ededed !important; }
  html.dark .person-cell { color: #9e9e9e !important; }
  html.dark .empty-cell { background: rgba(255,255,255,0.04) !important; }

  /* Feedback form (feedback.blade.php) */
  html.dark .feedback-form, html.dark .feedback-card { background: #222222 !important; border-color: rgba(255,255,255,0.10) !important; }
  html.dark .feedback-form input, html.dark .feedback-form textarea { background: #2a2a2a !important; color: #ededed !important; border-color: rgba(255,255,255,0.14) !important; }
  html.dark .feedback-form button[type="submit"] { background: #4fb8d4 !important; color: #0c1e23 !important; }

  /* Landing page slider arrows + dots */
  html.dark .slider .slide-content { color: #ededed; }
  html.dark .slider-arrow { background: rgba(0,0,0,0.6) !important; color: #ededed !important; }
  html.dark .slider-arrow:hover { background: rgba(0,0,0,0.85) !important; }
  html.dark .slider-dots button { background: rgba(255,255,255,0.30); }
  html.dark .slider-dots button.active { background: #ededed; }

  /* Landing nav-mobile toggle button */
  html.dark .nav-mobile-toggle { background: #2a2a2a !important; color: #ededed !important; border-color: rgba(255,255,255,0.14) !important; }

  /* Landing nav submenu (.has-menu) */
  html.dark .has-menu .submenu { background: #222222 !important; border-color: rgba(255,255,255,0.10) !important; box-shadow: 0 18px 56px -10px rgba(0,0,0,0.55) !important; }
  html.dark .has-menu .submenu a { color: #ededed !important; }
  html.dark .has-menu .submenu a:hover { color: #4fb8d4 !important; background: rgba(255,255,255,0.04) !important; }

  /* Anything that was using #1a2332 hardcoded — convert via specific overrides */
  html.dark .ink-bg, html.dark .ink-fill { background: #ededed !important; color: #191919 !important; }

  /* Email magic-link — those have their own dark variant template, ignore here */

  /* Image lightbox (flyer enlarge) */
  html.dark #img-lightbox, html.dark .img-lightbox { background: rgba(0,0,0,0.85) !important; }

  /* Schedule-platform mockup-leak (any old .platform colors) */
  html.dark .platform { color: #ededed !important; }

  /* Tinted text states */
  html.dark .err, html.dark .error-text { color: #ec7e72 !important; }
  html.dark .success, html.dark .success-text { color: #5ec790 !important; }
  html.dark .warn, html.dark .warning-text { color: #d4a85a !important; }
</style>
@endif
