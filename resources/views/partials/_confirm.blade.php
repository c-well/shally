{{--
    BRANDED_CONFIRM — site-wide replacement for native confirm()/alert().
    Self-contained: include once per page, before </body>:
        @include('partials._confirm')

    Three ways to use it:

    1. Form that should confirm then submit normally (POST/redirect):
         <form ... data-confirm="Delete this?"> … </form>

    2. Link that should confirm then navigate:
         <a href="…" data-confirm="Leave this page?">…</a>

    3. Form that should confirm then AJAX-delete + fade a row in place
       (no reload — for inline list rows):
         <form ... data-confirm-ajax="Move to trash?"> … </form>
       The fading target is the nearest ancestor with [data-row].

    4. From your own JS (replaces `if (confirm(x))` and `alert(x)`):
         shConfirm('Are you sure?').then(ok => { if (ok) … });
         shAlert('Saved.');

    Honors a custom OK label via data-confirm-ok="Delete".
--}}
<style>
  .bc-backdrop { position: fixed; inset: 0; background: rgba(26,35,50,0.45); backdrop-filter: blur(3px); display: flex; align-items: center; justify-content: center; z-index: 2147483000; opacity: 0; transition: opacity 0.16s ease; padding: 20px; }
  .bc-backdrop.show { opacity: 1; }
  .bc-box { background: var(--parchment, #fefcef); border: 1px solid var(--line, #e3ddc9); border-radius: 10px; max-width: 420px; width: 100%; padding: 26px 28px 20px; box-shadow: 0 24px 64px rgba(0,0,0,0.22); transform: translateY(6px); transition: transform 0.16s ease; }
  .bc-backdrop.show .bc-box { transform: translateY(0); }
  .bc-text { font-family: 'Cormorant Garamond', Georgia, serif; font-size: 19px; line-height: 1.45; color: var(--ink, #1a2332); margin-bottom: 22px; }
  .bc-actions { display: flex; justify-content: flex-end; gap: 10px; }
  .bc-btn { font-family: 'Instrument Sans', system-ui, sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; padding: 10px 18px; border-radius: 5px; cursor: pointer; border: 1px solid transparent; transition: all 0.15s; }
  .bc-cancel { background: transparent; color: var(--ink-soft, #5b6675); border-color: var(--line, #e3ddc9); }
  .bc-cancel:hover { color: var(--ink, #1a2332); border-color: var(--ink-soft, #5b6675); }
  .bc-ok { background: var(--teal, #03617a); color: #fff; }
  .bc-ok.danger { background: var(--warn, #a82a1f); }
  .bc-ok:hover { filter: brightness(1.08); }
  .bc-ok:disabled { opacity: 0.6; cursor: wait; }
  .bc-cancel.hidden { display: none; }  /* alert() mode = OK only */
  .bc-toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(8px); background: var(--ink, #1a2332); color: #fff; font-family: 'Instrument Sans', system-ui, sans-serif; font-size: 13px; font-weight: 500; padding: 12px 20px; border-radius: 8px; box-shadow: 0 12px 32px rgba(0,0,0,0.25); z-index: 2147483001; opacity: 0; transition: opacity 0.22s, transform 0.22s; max-width: 90vw; }
  .bc-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>

<div class="bc-backdrop" id="bcBackdrop" role="dialog" aria-modal="true" aria-labelledby="bcText" hidden>
  <div class="bc-box">
    <p class="bc-text" id="bcText"></p>
    <div class="bc-actions">
      <button type="button" class="bc-btn bc-cancel" id="bcCancel">Cancel</button>
      <button type="button" class="bc-btn bc-ok" id="bcOk">OK</button>
    </div>
  </div>
</div>
<div class="bc-toast" id="bcToast" hidden></div>

<script>
(function () {
  if (window.shConfirm) return;  // include-once guard
  var backdrop = document.getElementById('bcBackdrop');
  var textEl   = document.getElementById('bcText');
  var okBtn    = document.getElementById('bcOk');
  var cancelBtn= document.getElementById('bcCancel');
  var toast    = document.getElementById('bcToast');
  var token    = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var resolver = null;

  function open(opts) {
    textEl.textContent = opts.message || 'Are you sure?';
    okBtn.textContent = opts.okLabel || (opts.alert ? 'OK' : 'OK');
    okBtn.classList.toggle('danger', !!opts.danger);
    cancelBtn.classList.toggle('hidden', !!opts.alert);
    backdrop.hidden = false;
    requestAnimationFrame(function () { backdrop.classList.add('show'); });
    okBtn.disabled = false;
    okBtn.focus();
    return new Promise(function (res) { resolver = res; });
  }
  function close(val) {
    backdrop.classList.remove('show');
    setTimeout(function () { backdrop.hidden = true; }, 160);
    if (resolver) { resolver(val); resolver = null; }
  }
  window.shConfirm = function (message, opts) { return open(Object.assign({ message: message }, opts || {})); };
  window.shAlert   = function (message, opts) { return open(Object.assign({ message: message, alert: true }, opts || {})); };
  window.shToast   = function (message) {
    toast.textContent = message; toast.hidden = false;
    requestAnimationFrame(function () { toast.classList.add('show'); });
    setTimeout(function () { toast.classList.remove('show'); setTimeout(function(){ toast.hidden = true; }, 250); }, 3200);
  };

  okBtn.addEventListener('click', function () { close(true); });
  cancelBtn.addEventListener('click', function () { close(false); });
  backdrop.addEventListener('click', function (e) { if (e.target === backdrop) close(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !backdrop.hidden) close(false); });

  // ── Auto-intercept declarative forms/links ──────────────────────────────
  // Capture phase so we beat any other submit/click handlers.
  document.addEventListener('submit', function (e) {
    var form = e.target.closest('form[data-confirm], form[data-confirm-ajax]');
    if (!form || form.dataset.bcCleared) return;
    e.preventDefault();
    e.stopPropagation();
    var ajax = form.hasAttribute('data-confirm-ajax');
    var msg  = form.getAttribute('data-confirm') || form.getAttribute('data-confirm-ajax');
    shConfirm(msg, { danger: true, okLabel: form.dataset.confirmOk || 'OK' }).then(function (ok) {
      if (!ok) return;
      if (!ajax) {
        form.dataset.bcCleared = '1';  // let the real submit through next time
        if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
        return;
      }
      // AJAX delete: POST, fade nearest [data-row], toast.
      var row = form.closest('[data-row], [data-msg-row]');
      okBtn.disabled = true;
      fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
          close(false);
          if (res.ok && res.d && res.d.ok) {
            if (row) { row.style.transition = 'opacity .25s, transform .25s'; row.style.opacity = '0'; row.style.transform = 'translateX(-8px)'; setTimeout(function(){ row.remove(); }, 260); }
            shToast((res.d && res.d.message) || 'Done.');
          } else { shToast('Could not complete — try again.'); }
        })
        .catch(function () { close(false); shToast('Network error — try again.'); });
    });
  }, true);

  document.addEventListener('click', function (e) {
    var link = e.target.closest('a[data-confirm]');
    if (!link || link.dataset.bcCleared) return;
    e.preventDefault();
    var href = link.href;
    shConfirm(link.getAttribute('data-confirm'), { danger: link.hasAttribute('data-danger') }).then(function (ok) {
      if (ok) window.location.href = href;
    });
  }, true);
})();
</script>
