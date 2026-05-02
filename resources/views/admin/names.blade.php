<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Bulletin names — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 800px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(48px, 7vw, 72px); font-weight: 500; letter-spacing: -0.035em; line-height: 1; color: var(--ink); }
  .lede { margin-top: 22px; font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 540px; }

  /* + Add new name button */
  .add-name-bar {
    margin-top: 36px;
    display: flex; gap: 10px; align-items: center;
    padding: 14px 16px;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 8px;
  }
  .add-name-bar input {
    font: inherit; font-size: 15px;
    flex: 1; min-width: 0;
    padding: 10px 14px;
    border: 1px solid var(--line); border-radius: 4px;
    background: #fff; color: var(--ink);
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .add-name-bar input:focus {
    outline: 0; border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(3,97,122,0.12);
  }
  .add-name-bar button {
    background: var(--teal); color: #fff; border: 1px solid var(--teal);
    padding: 10px 18px; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    cursor: pointer; white-space: nowrap;
    transition: background 0.15s;
  }
  .add-name-bar button:hover { background: var(--teal-dark); }

  .name-list { margin-top: 24px; }
  .name-row {
    display: grid; grid-template-columns: 1fr auto;
    align-items: center; gap: 16px;
    padding: 14px 4px; border-bottom: 1px solid var(--line);
  }
  .name-row:last-child { border-bottom: 0; }
  .name-row.is-hidden .name-text { color: var(--ink-soft); text-decoration: line-through; }
  .name-text {
    font-size: 15px; color: var(--ink);
    cursor: text;
    padding: 4px 8px; margin: -4px -8px;
    border-radius: 4px;
    transition: background 0.12s;
  }
  .name-text:hover { background: rgba(3,97,122,0.05); }
  .name-text[contenteditable="true"] {
    background: #fff; outline: 2px solid var(--teal); outline-offset: 0;
    cursor: text;
  }
  .name-text .meta-tag {
    font-family: 'Instrument Sans', sans-serif; font-size: 10px;
    letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft);
    margin-left: 10px; opacity: 0.7;
  }

  .name-actions { display: flex; gap: 6px; align-items: center; }
  .name-actions button {
    background: transparent; color: var(--ink-soft);
    border: 1px solid var(--line); border-radius: 4px;
    padding: 6px 12px; cursor: pointer;
    font-family: 'Instrument Sans', sans-serif; font-size: 10px;
    font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase;
    transition: all 0.15s;
  }
  .name-actions button:hover { color: var(--teal); border-color: var(--teal); }
  .name-actions button.danger { color: #c0392b; border-color: rgba(192,57,43,0.3); }
  .name-actions button.danger:hover { background: #c0392b; color: #fff; border-color: #c0392b; }

  .empty {
    text-align: center; padding: 60px 0;
    color: var(--ink-soft); font-style: italic;
    font-family: 'Cormorant Garamond', serif; font-size: 22px;
  }
</style>
@include('admin.partials._typography')
</head>
<body>

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">bulletin autocomplete</span>
</header>

<main>
  <h1>Bulletin names.</h1>
  <p class="lede">These are the names appearing in autocomplete on bulletin lines. Hide typos or members no longer serving — they'll stop showing as suggestions.</p>

  <form class="add-name-bar" id="add-name-bar" autocomplete="off">
    @csrf
    <input type="text" id="add-name-input" placeholder="Add a name (e.g., Sis Patterson) — auto-saves on enter" maxlength="180" required>
    <button type="submit">+ Add</button>
  </form>

  <div class="name-list" id="name-list">
    <div class="empty" id="loading">Loading…</div>
  </div>
</main>

<script>
(function () {
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  const list = document.getElementById('name-list');

  async function load() {
    list.innerHTML = '<div class="empty">Loading…</div>';
    try {
      const res = await fetch('/api/people', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      const blocked = new Set(data.blocked || []);
      // Filter out hymn-style entries (start with # or are pure digits) — they shouldn't be in the people list
      const all = (data.all || []).filter(n => !/^#?\d/.test(n.trim()));
      if (all.length === 0) { list.innerHTML = '<div class="empty">No names yet.</div>'; return; }
      list.innerHTML = all.map(name => {
        const isHidden = blocked.has(name);
        const safe = String(name).replace(/"/g, '&quot;').replace(/</g, '&lt;');
        return `<div class="name-row ${isHidden ? 'is-hidden' : ''}" data-original="${safe}">
          <div class="name-text" contenteditable="false" spellcheck="false" tabindex="0">
            ${safe}${isHidden ? '<span class="meta-tag"> · hidden</span>' : ''}
          </div>
          <div class="name-actions">
            ${isHidden
              ? `<button data-unblock="${safe}">Restore</button>`
              : `<button data-hide="${safe}">Hide</button>
                 <button class="danger" data-clear="${safe}" title="Hide and remove from older bulletin lines">Hide + clear</button>`}
          </div>
        </div>`;
      }).join('');
      list.querySelectorAll('[data-hide]').forEach(b => b.addEventListener('click', () => block(b.dataset.hide, false)));
      list.querySelectorAll('[data-clear]').forEach(b => b.addEventListener('click', () => block(b.dataset.clear, true)));
      list.querySelectorAll('[data-unblock]').forEach(b => b.addEventListener('click', () => unblock(b.dataset.unblock)));

      // Click-to-edit each name; enter saves, esc cancels, blur saves.
      list.querySelectorAll('.name-text').forEach(span => {
        const row = span.closest('.name-row');
        if (row.classList.contains('is-hidden')) return;  // don't edit hidden names
        const original = row.dataset.original;
        span.addEventListener('click', () => {
          if (span.contentEditable === 'true') return;
          span.textContent = original;  // strip the "hidden" tag if any
          span.contentEditable = 'true';
          span.focus();
          // place cursor at end
          const range = document.createRange();
          range.selectNodeContents(span);
          range.collapse(false);
          const sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
        });
        span.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') { e.preventDefault(); span.blur(); }
          if (e.key === 'Escape') { e.preventDefault(); span.textContent = original; span.contentEditable = 'false'; span.blur(); }
        });
        span.addEventListener('blur', async () => {
          if (span.contentEditable !== 'true') return;
          span.contentEditable = 'false';
          const newName = span.textContent.trim();
          if (!newName || newName === original) {
            span.textContent = original;
            return;
          }
          try {
            const res = await fetch('/api/people', {
              method: 'PATCH', credentials: 'same-origin',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
              body: JSON.stringify({ old: original, new: newName }),
            });
            if (!res.ok) throw new Error('rename failed');
            await load();
          } catch (e) { alert('Could not rename: ' + e.message); span.textContent = original; }
        });
      });
    } catch (e) { list.innerHTML = '<div class="empty">Failed to load.</div>'; }
  }

  async function post(url, body) {
    const res = await fetch(url, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: JSON.stringify(body || {}),
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  async function block(person, clearLines) {
    if (!confirm(`Hide "${person}" from autocomplete${clearLines ? ' AND remove from past bulletin lines' : ''}?`)) return;
    try { await post('/api/people/block', { person, clear_from_lines: !!clearLines }); load(); }
    catch (e) { alert('Failed: ' + e.message); }
  }
  async function unblock(person) {
    try { await post('/api/people/unblock', { person }); load(); }
    catch (e) { alert('Failed: ' + e.message); }
  }

  // + Add new name
  document.getElementById('add-name-bar').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('add-name-input');
    const name = input.value.trim();
    if (!name) return;
    try {
      await post('/api/people', { name });
      input.value = '';
      await load();
    } catch (e) { alert('Could not add: ' + e.message); }
  });

  load();
})();
</script>

</body>
</html>
