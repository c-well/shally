{{-- ============================================================
     Site header + full-screen ☰ menu — single source of truth.
     @include('partials.site-menu') on every public/admin page.

     Renders:
       - Sticky brand + ☰ trigger
       - Full-screen overlay menu with accordion sections
       - Public nav for everyone
       - Admin section conditional on auth + role

     CSS + JS are inline so this partial is fully self-contained.
     ============================================================ --}}
<style>
  /* ─── Header ─── */
  .site-menu-header {
    position: sticky; top: 0; z-index: 50;
    background: var(--parchment, #fefcef);
    border-bottom: 1px solid rgba(3,97,122,0.12);
  }
  .site-menu-header-inner {
    max-width: 1100px; margin: 0 auto;
    padding: 18px 22px 14px;
    display: flex; align-items: center; justify-content: space-between;
    min-height: 60px;
  }
  /* Site brand — "shalom" in Xtreem teal, lowercase, matches original */
  @font-face {
    font-family: 'Xtreem';
    src: url('/fonts/XtreemMedium.ttf') format('truetype');
    font-weight: 500; font-style: normal; font-display: swap;
  }
  .site-menu-brand {
    text-decoration: none; line-height: 1;
    display: inline-flex; align-items: center;
  }
  .site-menu-brand em {
    font-family: 'Xtreem', 'Cormorant Garamond', serif;
    font-style: normal; font-weight: 500;
    color: var(--teal, #03617A);
    text-transform: lowercase;
    font-size: 56px; letter-spacing: -0.02em; line-height: 0.8;
    display: inline-block;
  }
  @media (max-width: 600px) {
    .site-menu-brand em { font-size: 42px; }
  }
  .site-menu-trigger {
    display: inline-flex; align-items: center; gap: 10px;
    height: 44px; padding: 0 16px;
    background: var(--ink, #1a2332); color: #fff;
    border: 0; border-radius: 10px; cursor: pointer;
    transition: background 0.15s;
  }
  .site-menu-trigger:hover { background: #2a3340; }
  .site-menu-trigger:focus-visible { outline: 2px solid var(--teal, #03617A); outline-offset: 2px; }
  .site-menu-trigger-label {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px; font-weight: 600; line-height: 1;
    letter-spacing: 0.18em; text-transform: uppercase;
  }
  .site-menu-trigger svg { display: block; }

  /* ─── Full-screen menu ─── */
  .site-menu-overlay {
    position: fixed; inset: 0; z-index: 200;
    background: var(--parchment, #fefcef);
    display: none; overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }
  .site-menu-overlay.open { display: block; animation: site-menu-fade 0.2s ease-out; }
  @keyframes site-menu-fade { from { opacity: 0; } to { opacity: 1; } }

  .site-menu-overlay-header {
    position: sticky; top: 0;
    padding: 14px 22px;
    display: flex; align-items: center; justify-content: space-between;
    background: var(--parchment, #fefcef);
  }
  .site-menu-overlay-header .site-menu-brand em { font-size: 38px; }
  @media (max-width: 600px) {
    .site-menu-overlay-header .site-menu-brand em { font-size: 32px; }
  }
  .site-menu-close {
    width: 44px; height: 44px;
    background: transparent; border: 0;
    color: var(--ink, #1a2332); cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 999px; transition: background 0.15s;
  }
  .site-menu-close:hover { background: rgba(0,0,0,0.06); }
  .site-menu-close svg { width: 20px; height: 20px; }

  .site-menu-nav { max-width: 560px; margin: 0 auto; padding: 36px 28px 80px; }

  /* Top-level rows (direct links + accordion toggles share the same shell) */
  .site-menu-link, .site-menu-section-toggle {
    display: flex; align-items: center; justify-content: space-between;
    width: 100%;
    padding: 20px 0;
    font-family: 'Cormorant Garamond', serif;
    font-size: 32px; font-weight: 600; line-height: 1;
    letter-spacing: -0.005em;
    color: var(--ink, #1a2332);
    text-decoration: none;
    background: transparent; border: 0;
    border-bottom: 1px solid rgba(26,35,50,0.12);
    text-align: left; cursor: pointer;
    transition: color 0.15s;
  }
  .site-menu-link:hover, .site-menu-link:focus-visible,
  .site-menu-section-toggle:hover, .site-menu-section-toggle:focus-visible {
    color: var(--teal, #03617A);
  }
  .site-menu-link .arrow {
    opacity: 0.4; font-size: 18px; font-weight: 400;
    transition: opacity 0.15s, transform 0.15s;
  }
  .site-menu-link:hover .arrow { opacity: 0.85; transform: translateX(2px); }
  .site-menu-link-admin { color: var(--teal, #03617A); }
  .site-menu-link-signout { color: #a82a1f; }
  .site-menu-link-signout:hover { color: #c0392b; }

  .site-menu-section { border-bottom: 1px solid rgba(26,35,50,0.12); }
  .site-menu-section-toggle { border-bottom: 0; }
  .site-menu-section-toggle .chev {
    width: 18px; height: 18px; opacity: 0.4;
    transition: transform 0.25s ease, opacity 0.15s;
  }
  .site-menu-section-toggle:hover .chev { opacity: 0.85; }
  .site-menu-section.open .site-menu-section-toggle .chev {
    transform: rotate(180deg); opacity: 0.85;
  }
  .site-menu-section-body {
    max-height: 0; overflow: hidden;
    transition: max-height 0.3s ease;
  }
  .site-menu-section.open .site-menu-section-body { max-height: 800px; }
  .site-menu-sub-list {
    padding: 4px 0 22px 4px;
    display: flex; flex-direction: column; gap: 2px;
  }
  .site-menu-sub-link {
    display: block;
    padding: 12px 0;
    font-family: 'Poppins', sans-serif;
    font-size: 17px; font-weight: 500; line-height: 1.2;
    color: var(--ink, #1a2332);
    text-decoration: none;
    transition: color 0.15s, transform 0.15s;
  }
  .site-menu-sub-link:hover { color: var(--teal, #03617A); transform: translateX(2px); }
  .site-menu-sub-link-form { all: unset; cursor: pointer; display: block; }
</style>

<header class="site-menu-header">
  <div class="site-menu-header-inner">
    <a href="{{ url('/') }}" class="site-menu-brand"><em>Shalom</em></a>
    <button class="site-menu-trigger" id="site-menu-btn" type="button" aria-label="Open menu" aria-expanded="false">
      <span class="site-menu-trigger-label">Menu</span>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>
  </div>
</header>

<div class="site-menu-overlay" id="site-menu-overlay" role="dialog" aria-modal="true" aria-label="Site menu">
  <div class="site-menu-overlay-header">
    <a href="{{ url('/') }}" class="site-menu-brand"><em>Shalom</em></a>
    <button class="site-menu-close" id="site-menu-close" type="button" aria-label="Close menu">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
        <line x1="5" y1="5" x2="19" y2="19"/>
        <line x1="19" y1="5" x2="5" y2="19"/>
      </svg>
    </button>
  </div>

  <nav class="site-menu-nav">
    <a class="site-menu-link" href="{{ url('/') }}">Home <span class="arrow">→</span></a>

    <div class="site-menu-section">
      <button class="site-menu-section-toggle" type="button" aria-expanded="false">
        About Us
        <svg class="chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="site-menu-section-body">
        <div class="site-menu-sub-list">
          <a class="site-menu-sub-link" href="{{ route('about') }}">Our story</a>
          <a class="site-menu-sub-link" href="{{ route('beliefs') }}">Beliefs</a>
          <a class="site-menu-sub-link" href="{{ route('contact.show') }}">Contact</a>
        </div>
      </div>
    </div>

    <a class="site-menu-link" href="{{ route('visit') }}">Visit Us <span class="arrow">→</span></a>

    <div class="site-menu-section">
      <button class="site-menu-section-toggle" type="button" aria-expanded="false">
        Spiritual Life
        <svg class="chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="site-menu-section-body">
        <div class="site-menu-sub-list">
          <a class="site-menu-sub-link" href="{{ route('peace-notes') }}">Peace Notes</a>
          <a class="site-menu-sub-link" href="{{ route('lesson.show') }}">Sabbath School Lesson</a>
        </div>
      </div>
    </div>

    <a class="site-menu-link" href="{{ url('/welcome') }}">Bulletin <span class="arrow">→</span></a>

    <a class="site-menu-link" href="https://adventistgiving.org/#/org/AN48SH/envelope/start" target="_blank" rel="noopener">Donate <span class="arrow">→</span></a>

    @auth
      @if ((auth()->user()->role === 'super_admin' || auth()->user()->role === 'clerk') && ($bulletin ?? null))
        {{-- Bulletin tools — only on the bulletin page when admin is editing --}}
        <div class="site-menu-section">
          <button class="site-menu-section-toggle" type="button" aria-expanded="false">
            Bulletin tools
            <svg class="chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="site-menu-section-body">
            <div class="site-menu-sub-list">
              <button class="site-menu-sub-link site-menu-sub-link-form" data-standard-url="{{ route('bulletins.load-standard', $bulletin) }}" type="button">{{ $bulletin->lines->isEmpty() ? 'Load standard order' : 'Reset to default' }}</button>
              <button class="site-menu-sub-link site-menu-sub-link-form" data-next-week-url="{{ route('bulletins.next-week') }}" type="button">Next Sabbath</button>
              <button class="site-menu-sub-link site-menu-sub-link-form" id="new-event-series-btn" type="button">Event series</button>
              <a class="site-menu-sub-link" href="{{ route('bulletins.pdf', $bulletin) }}" target="_blank" rel="noopener">Download PDF</a>
              @if ($bulletin->hasAvailablePreviousVersion())
                <a class="site-menu-sub-link" href="{{ route('bulletins.pdf', $bulletin) }}?version=previous" target="_blank" rel="noopener">Previous PDF</a>
              @endif
              <a class="site-menu-sub-link" href="/?preview=1">Preview as public</a>
              <button class="site-menu-sub-link site-menu-sub-link-form" id="theme-pick-btn-menu" type="button">Special-day theme</button>
              @if (auth()->user()->role === 'super_admin')
                <label class="site-menu-sub-link" style="display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer;">
                  <span>Show all week (override)</span>
                  <input type="checkbox" id="always-show-toggle" data-update-url="{{ route('bulletins.update', $bulletin) }}" @if(($bulletin->always_show_during_week ?? false)) checked @endif style="transform:scale(1.2); accent-color:var(--teal);">
                </label>
                <button class="site-menu-sub-link site-menu-sub-link-form" id="bulletin-delete-btn" type="button"
                  style="color:#a82a1f;"
                  data-delete-url="{{ route('bulletins.destroy', $bulletin) }}"
                  data-bulletin-title="{{ $bulletin->title ?? optional($bulletin->service_date)->format('M j, Y') }}"
                  data-service-date="{{ $bulletin->service_date?->toDateString() }}"
                  data-service-date-formatted="{{ $bulletin->service_date?->format('l, F j') }}">Delete this bulletin</button>
              @endif
            </div>
          </div>
        </div>
      @endif

      @if (auth()->user()->role === 'super_admin' || auth()->user()->role === 'clerk')
        {{-- Admin tools — available on every page when signed in as admin --}}
        <div class="site-menu-section">
          <button class="site-menu-section-toggle" type="button" aria-expanded="false">
            Admin tools
            <svg class="chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="site-menu-section-body">
            <div class="site-menu-sub-list">
              <button class="site-menu-sub-link site-menu-sub-link-form" id="search-btn-mobile" type="button" data-action="search">Search</button>
              <a class="site-menu-sub-link" href="{{ route('schedule.index') }}">Department schedule</a>
              <button class="site-menu-sub-link site-menu-sub-link-form" id="manage-names-btn" type="button">Manage names</button>
              <a class="site-menu-sub-link" href="{{ route('admin.hub') }}">Admin hub</a>
            </div>
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="site-menu-link site-menu-link-signout site-menu-sub-link-form">Sign out <span class="arrow">→</span></button>
      </form>
    @else
      <a class="site-menu-link" href="{{ route('login') }}">Sign in <span class="arrow">→</span></a>
    @endauth
  </nav>
</div>

<script>
  (function() {
    const btn = document.getElementById('site-menu-btn');
    const overlay = document.getElementById('site-menu-overlay');
    const closeBtn = document.getElementById('site-menu-close');
    if (!btn || !overlay) return;

    const open = () => {
      overlay.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    };
    const close = () => {
      overlay.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
      overlay.querySelectorAll('.site-menu-section.open').forEach(s => s.classList.remove('open'));
    };

    btn.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

    // Accordion: only one section open at a time
    overlay.querySelectorAll('.site-menu-section-toggle').forEach(t => {
      t.addEventListener('click', () => {
        const section = t.closest('.site-menu-section');
        const wasOpen = section.classList.contains('open');
        overlay.querySelectorAll('.site-menu-section.open').forEach(s => s.classList.remove('open'));
        if (!wasOpen) {
          section.classList.add('open');
          t.setAttribute('aria-expanded', 'true');
        } else {
          t.setAttribute('aria-expanded', 'false');
        }
      });
    });
  })();
</script>
