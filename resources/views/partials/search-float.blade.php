{{-- Persistent floating Search button.
     On any public page, taps open /welcome with ?open-search=1, which auto-opens
     the search overlay there. /welcome already has the full search infrastructure
     (MiniSearch indexes for KJV/ESV/Hymnal, lightbox, share-verse, etc) — this
     partial just gives every page a one-tap path into it.

     Visible to everyone (guests + admins). Suppressed only on /welcome itself
     (where the same Search button already lives in the header). --}}
@unless (request()->is('welcome') || request()->is('admin') || request()->is('admin/*'))
  <a href="/welcome?open-search=1" class="search-float" id="search-float-link" aria-label="Search Scripture or Hymnal" title="Search Scripture or Hymnal">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <span>Search</span>
  </a>

  <style>
    /* Floating search button — bottom-left of viewport. Mirror of lesson page's
       font-toggle (which lives bottom-right) so the two don't collide. */
    .search-float {
      position: fixed;
      bottom: 22px;
      left: 22px;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 10px 16px 10px 14px;
      background: var(--ink, #1a2332);
      color: #fff;
      font-family: 'Instrument Sans', sans-serif;
      font-size: 11px; font-weight: 700;
      letter-spacing: 0.18em; text-transform: uppercase;
      border-radius: 999px;
      text-decoration: none;
      box-shadow: 0 6px 18px -8px color-mix(in srgb, var(--ink) 30%, transparent);
      transition: background 0.15s, opacity 0.25s ease, transform 0.25s ease;
      z-index: 240;
    }
    .search-float:hover { background: var(--teal, #03617A); }
    .search-float:active { transform: scale(0.97); }
    .search-float:focus-visible { outline: 2px solid var(--teal, #03617A); outline-offset: 2px; }

    html.dark .search-float { background: #2a2a2a; }
    html.dark .search-float:hover { background: #4fb8d4; color: #0c1e23; }

        .search-float.is-fading { opacity: 0.18; transform: translateY(2px); pointer-events: auto; }
    .search-float.is-fading:hover { opacity: 1; transform: translateY(0); }
    .search-float { transition: background 0.15s, opacity 0.25s ease, transform 0.25s ease; }

    @media (max-width: 600px) {
      .search-float { bottom: 14px; left: 14px; padding: 9px 14px 9px 12px; font-size: 10px; }
    }
    @media print { .search-float { display: none !important; } }
  </style>
  <script>
    // Item 4 — sessionStorage fallback so the welcome page can auto-open even
    // if the URL param gets stripped (SW caching, browser quirks).
    document.getElementById('search-float-link')?.addEventListener('click', function () {
      try { sessionStorage.setItem('cop_open_search', '1'); } catch (e) {}
    });

    // Item 5 — auto-hide on scroll-down, re-show on scroll-up. Keeps the
    // button reachable but out of the way during reading. Reduces visual
    // weight to ~0 when the user is actively scrolling through content.
    (function () {
      var btn = document.querySelector('.search-float');
      if (!btn) return;
      var lastY = window.scrollY;
      var ticking = false;
      function onScroll() {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
          var y = window.scrollY;
          var delta = y - lastY;
          // Only react to meaningful movement (15+ px)
          if (Math.abs(delta) > 15) {
            if (delta > 0 && y > 120) {
              // Scrolling down past the top — fade out
              btn.classList.add('is-fading');
            } else {
              // Scrolling up OR near the top — fade back in
              btn.classList.remove('is-fading');
            }
            lastY = y;
          }
          ticking = false;
        });
      }
      window.addEventListener('scroll', onScroll, { passive: true });
    })();
  </script>
@endunless
