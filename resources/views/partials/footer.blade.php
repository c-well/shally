{{--
  Shared site footer — Xtreem brand wordmark matches the header treatment.
  Self-contained: brings its own @font-face + CSS so it renders correctly
  on any page (public, error, etc.) regardless of the page's own <head> CSS.
  Do NOT include on: bulletins/*, lesson/*, auth/*, bulletins/pdf (those have
  context-specific footers — see project_church_app memory for rationale).
--}}
<style>
  @font-face { font-family: 'Xtreem'; src: url('/fonts/XtreemMedium.ttf') format('truetype'); font-display: swap; }
  footer.site {
    margin-top: 64px;
    padding: 48px clamp(20px, 5vw, 40px) 64px;
    text-align: center;
    border-top: 1px solid rgba(26,35,50,0.10);
    color: #334455;
  }
  footer.site .footer-brand {
    font-family: 'Xtreem', 'Cormorant Garamond', serif;
    font-size: 72px; font-weight: 500; font-style: normal; text-transform: lowercase;
    line-height: 0.95; color: #03617A; margin-bottom: 8px; letter-spacing: -0.02em;
  }
  footer.site address {
    font-style: normal;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase;
    line-height: 1.85; color: #334455; margin-bottom: 24px;
  }
  footer.site address a { color: #334455; border-bottom: 1px solid transparent; transition: color 0.15s, border-color 0.15s; }
  footer.site address a:hover { color: #03617A; border-bottom-color: #03617A; }
  footer.site .socials { display: flex; gap: 16px; justify-content: center; margin-bottom: 24px; }
  footer.site .socials a {
    width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid rgba(26,35,50,0.10); border-radius: 50%; color: #334455;
    transition: color 0.15s, border-color 0.15s;
  }
  footer.site .socials a:hover { color: #03617A; border-color: #03617A; }
  footer.site .socials svg { width: 16px; height: 16px; }
  footer.site .copy {
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px; color: #334455; opacity: 0.7; letter-spacing: 0.08em;
  }
</style>
<footer class="site">
  <div class="footer-brand">Shalom</div>
  <address>
    Shalom Seventh-day Adventist Church<br>
    3323 White Plains Rd &middot; Bronx, NY 10467<br>
    <a href="mailto:contact@thechurchofpeace.org">contact@thechurchofpeace.org</a>
  </address>
  <div class="socials">
    <a href="https://www.facebook.com/thechurchofpeace" target="_blank" rel="noopener" aria-label="Facebook">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12c0-5.5-4.5-10-10-10S2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.5 2.9h-2.3v7c4.7-.8 8.4-4.9 8.4-9.9z"/></svg>
    </a>
    <a href="https://www.youtube.com/c/ShalomSDAChurchBX/videos" target="_blank" rel="noopener" aria-label="YouTube">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.7-.5-5.4c-.3-.9-1-1.6-1.9-1.9C18.9 4.2 12 4.2 12 4.2s-6.9 0-8.6.5c-.9.3-1.6 1-1.9 1.9C1 8.3 1 12 1 12s0 3.7.5 5.4c.3.9 1 1.6 1.9 1.9 1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5c.9-.3 1.6-1 1.9-1.9.5-1.7.5-5.4.5-5.4zM9.7 15.4V8.6l5.8 3.4-5.8 3.4z"/></svg>
    </a>
  </div>
  <div class="copy">&copy; {{ date("Y") }} Shalom Seventh-day Adventist Church &middot; The Church of Peace</div>
</footer>
