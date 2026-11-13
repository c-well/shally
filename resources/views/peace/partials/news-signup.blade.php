{{-- Peace Notes News sign-up: sits in the page, never pops up.
     Uses the Finding Peace email sign-in (no password); the link in the email
     confirms the address and turns the newsletter on. --}}
@php $nsId = 'ns' . substr(md5(uniqid('', true)), 0, 6); @endphp
<section class="news-signup" aria-labelledby="{{ $nsId }}-t" style="max-width: 720px; margin: 0 auto; padding: clamp(48px, 6vw, 72px) clamp(20px, 5vw, 32px); font-family: 'Poppins', system-ui, sans-serif; color: var(--ink, #1a2332);">
  <h2 id="{{ $nsId }}-t" style="margin: 0; text-align: left; font-family: 'Poppins', system-ui, sans-serif; font-weight: 600; font-size: 24px; letter-spacing: -0.02em; line-height: 1.25;">Get Peace Notes News every Sunday morning.</h2>
  <p style="margin: 8px 0 20px; font-weight: 300; font-size: 17px; line-height: 1.5; color: #4a5568;">The week’s message in two minutes, and a question to carry with you. One email a week. Leave any time.</p>
  <form id="{{ $nsId }}" method="post" action="{{ route('peace.auth.request') }}" style="display: flex; flex-wrap: wrap; gap: 12px;">
    @csrf
    <input type="hidden" name="intent[type]" value="news_signup">
    <input type="hidden" name="source_url" value="{{ url()->current() }}">
    <input type="hidden" name="form_opened_at" value="0" data-opened>
    <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position: absolute; left: -9999px; width: 1px; height: 1px;">
    <label for="{{ $nsId }}-e" style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0);">Email</label>
    <input id="{{ $nsId }}-e" type="email" name="email" required autocomplete="email" placeholder="Your email" style="flex: 1 1 240px; min-width: 0; font: 400 16px 'Poppins', system-ui, sans-serif; color: #1a2332; background: #fff; border: 1px solid rgba(26,35,50,.18); border-radius: 8px; padding: 12px 14px;">
    <button type="submit" style="min-height: 48px; padding: 0 24px; border: 0; border-radius: 6px; background: var(--teal, #6b4d8a); color: #fff; font: 500 15px 'Poppins', system-ui, sans-serif; cursor: pointer;">Sign me up</button>
  </form>
  <p data-msg role="status" aria-live="polite" style="margin: 12px 0 0; font-size: 15px; color: var(--teal, #6b4d8a); min-height: 1em;">{{ session('peace_flash') }}</p>
</section>
<script>
(function () {
  var f = document.getElementById(@json($nsId)); if (!f) return;
  f.querySelector('[data-opened]').value = Date.now();
  var msg = f.parentNode.querySelector('[data-msg]');
  f.addEventListener('submit', function (e) {
    e.preventDefault();
    var b = f.querySelector('button'); b.disabled = true;
    fetch(f.action, { method: 'POST', headers: { 'Accept': 'application/json' }, body: new FormData(f), credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) { msg.textContent = (j && j.message) ? 'Check your inbox: tap the link to start getting it.' : 'Something went wrong. Please try again.'; if (j && j.ok) f.reset(); })
      .catch(function () { msg.textContent = 'Something went wrong. Please try again.'; })
      .finally(function () { b.disabled = false; f.querySelector('[data-opened]').value = Date.now(); });
  });
})();
</script>
