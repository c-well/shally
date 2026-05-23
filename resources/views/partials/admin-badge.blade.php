{{-- Admin trigger — clerk/super_admin only.
     Floats fixed-position on the right side of the viewport, just below the
     header line and aligned vertically with the Donate column. Same Anthropic-
     style "Admin ▾" pill that opens a dropdown panel.

     Visible on: landing + all public content pages
     Suppressed on:
       - /admin and /admin/*  (admin hub + sub-pages — already have admin chrome)
       - /welcome (bulletin editor — has its own Admin ▾ trigger in header) --}}
@auth
  @if (
    in_array(auth()->user()->role ?? null, ["clerk", "super_admin"], true)
    && ! request()->is('admin')
    && ! request()->is('admin/*')
    && ! request()->is('welcome')
  )
    <div class="admin-float-host" id="admin-float-host">
      <button type="button"
              id="admin-float-trigger"
              class="admin-float-trigger"
              aria-haspopup="true"
              aria-expanded="false"
              aria-controls="admin-float-panel"
              aria-label="Admin menu">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h0a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h0a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <span class="admin-float-label">Admin</span>
        <svg class="chev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
      </button>

      <div id="admin-float-panel" class="admin-float-panel" role="menu" aria-hidden="true">
        <div class="admin-float-section">
          <div class="admin-float-eyebrow">Quick links</div>
          <a href="{{ url('/welcome') }}" class="admin-float-item" role="menuitem"><span>Bulletin</span></a>
          <a href="{{ route('schedule.index') }}" class="admin-float-item" role="menuitem"><span>Department schedule</span></a>
          @if (auth()->user()->role === 'super_admin')
            <a href="{{ route('admin.hub') }}" class="admin-float-item" role="menuitem"><span>Admin hub</span></a>
          @endif
        </div>

        <div class="admin-float-section">
          <div class="admin-float-user">{{ auth()->user()->name }}</div>
          <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
            @csrf
            <button type="submit" class="admin-float-item" role="menuitem"><span>Sign out</span></button>
          </form>
        </div>
      </div>
    </div>

    <style>
      /* Float-positioned admin trigger — top-right of viewport, just below the
         header line, horizontally aligned with the Donate column (clamp 20-48px
         from the right edge — matches the nav's outer padding rule). */
      .admin-float-host {
        position: fixed;
        top: 100px;                             /* fallback — JS measures header bottom + sets dynamically */
        right: clamp(20px, 5vw, 48px);
        z-index: 250;
        transition: top 0.18s ease;
      }

      .admin-float-trigger {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 7px 12px 7px 11px;
        background: #1a2332;
        color: #fff;
        border: 1px solid #1a2332;
        border-radius: 999px;
        font-family: 'Instrument Sans', sans-serif;
        font-size: 10px; font-weight: 700;
        letter-spacing: 0.18em; text-transform: uppercase;
        cursor: pointer;
        line-height: 1;
        box-shadow: 0 4px 14px -4px color-mix(in srgb, var(--ink) 30%, transparent);
        transition: background 0.15s, border-color 0.15s, transform 0.1s;
        text-decoration: none;
      }
      .admin-float-trigger:hover { background: #2a3340; border-color: #2a3340; }
      .admin-float-trigger:active { transform: scale(0.97); }
      .admin-float-trigger:focus-visible { outline: 2px solid var(--teal, #03617A); outline-offset: 2px; }
      .admin-float-trigger[aria-expanded="true"] { background: #2a3340; border-color: #2a3340; }
      .admin-float-trigger .chev { transition: transform 0.2s; opacity: 0.85; }
      .admin-float-trigger[aria-expanded="true"] .chev { transform: rotate(180deg); }
      .admin-float-trigger svg { display: inline-block; vertical-align: middle; }
      .admin-float-label { line-height: 1; }

      /* Dropdown — anchored to right of the trigger so it doesn't run off screen */
      .admin-float-panel {
        display: none;
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        min-width: 280px;
        max-width: 340px;
        background: var(--parchment, #fefcef);
        border: 1px solid var(--line, color-mix(in srgb, var(--ink) 12%, transparent));
        border-radius: 16px;
        box-shadow: 0 18px 56px -10px color-mix(in srgb, var(--ink) 28%, transparent);
        padding: 22px 16px;
        z-index: 250;
      }
      .admin-float-panel.open { display: block; }

      .admin-float-section + .admin-float-section {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid var(--line, color-mix(in srgb, var(--ink) 12%, transparent));
      }
      .admin-float-eyebrow {
        font-family: 'Instrument Sans', sans-serif;
        font-size: 10px; font-weight: 700;
        letter-spacing: 0.22em; text-transform: uppercase;
        color: var(--ink-soft, #5a6478);
        opacity: 0.65;
        margin: 0 4px 8px;
      }
      .admin-float-item {
        display: flex; align-items: center; justify-content: space-between;
        gap: 10px;
        width: 100%;
        padding: 10px 8px;
        background: transparent;
        border: 0;
        border-radius: 6px;
        font-family: 'Cormorant Garamond', 'Palatino Linotype', Georgia, serif;
        font-size: 19px; font-weight: 500;
        letter-spacing: -0.005em;
        line-height: 1.25;
        color: var(--ink, #1a2332);
        text-align: left; text-decoration: none;
        cursor: pointer;
        transition: color 0.12s, background 0.12s;
        min-height: 44px;
      }
      .admin-float-item:hover { color: var(--teal, #03617A); background: color-mix(in srgb, var(--teal) 6%, transparent); }
      .admin-float-item:focus-visible { outline: 2px solid var(--teal, #03617A); outline-offset: 2px; }
      .admin-float-user {
        padding: 4px 8px 12px;
        font-family: 'Instrument Sans', sans-serif;
        font-size: 12px;
        color: var(--ink-soft, #5a6478);
        opacity: 0.75;
      }

      /* Dark mode */
      html.dark .admin-float-trigger { background: #2a2a2a; color: #ededed; border-color: #2a2a2a; box-shadow: 0 4px 14px -4px rgba(0,0,0,0.55); }
      html.dark .admin-float-trigger:hover, html.dark .admin-float-trigger[aria-expanded="true"] { background: #333333; border-color: #333333; }
      html.dark .admin-float-panel {
        background: #222222 !important;
        border-color: rgba(255,255,255,0.10) !important;
        box-shadow: 0 18px 56px -10px rgba(0,0,0,0.65) !important;
      }
      html.dark .admin-float-eyebrow { color: #9e9e9e; }
      html.dark .admin-float-item { color: #ededed !important; }
      html.dark .admin-float-item:hover { color: #4fb8d4 !important; background: rgba(255,255,255,0.04) !important; }
      html.dark .admin-float-user { color: #9e9e9e; }
      html.dark .admin-float-section + .admin-float-section { border-top-color: rgba(255,255,255,0.10); }

      @media (max-width: 600px) {
        .admin-float-host { top: 92px; right: 16px; }
        .admin-float-panel { min-width: calc(100vw - 32px); max-width: calc(100vw - 32px); right: 0; }
      }
      @media print { .admin-float-host { display: none !important; } }
    </style>

    <script>
      (function () {
        function init() {
          var btn  = document.getElementById('admin-float-trigger');
          var panel = document.getElementById('admin-float-panel');
          if (!btn || !panel) return;
          var host = document.getElementById('admin-float-host');

          // Measure the page header and place the float 18px below its bottom edge.
          // Adapts per page (different headers have different heights), and re-runs
          // on resize / scroll so the float is always tucked right below the line.
          function positionBelowHeader() {
            if (!host) return;
            var hdr = document.querySelector('header.navbar, header.top, header');
            if (!hdr) { host.style.top = '100px'; return; }
            var b = hdr.getBoundingClientRect().bottom;
            host.style.top = (b > 0 ? b + 18 : 16) + 'px';
          }
          positionBelowHeader();
          window.addEventListener('resize', positionBelowHeader);
          window.addEventListener('scroll', positionBelowHeader, { passive: true });
          if (document.fonts && document.fonts.ready) document.fonts.ready.then(positionBelowHeader);

          function open()  { panel.classList.add('open');    btn.setAttribute('aria-expanded', 'true');  panel.setAttribute('aria-hidden', 'false'); }
          function close() { panel.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); panel.setAttribute('aria-hidden', 'true'); }
          function toggle() { panel.classList.contains('open') ? close() : open(); }

          btn.addEventListener('click', function (e) { e.stopPropagation(); toggle(); });
          document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && !btn.contains(e.target)) close();
          });
          document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panel.classList.contains('open')) { close(); btn.focus(); }
          });
        }
        if (document.readyState !== 'loading') init();
        else document.addEventListener('DOMContentLoaded', init);
      })();
    </script>
  @endif
@endauth
