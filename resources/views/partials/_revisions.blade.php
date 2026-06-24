{{-- Inline edit-history panel for a single HasRevisions record.
     Usage: @include('partials._revisions', ['model' => $page])
     Relies on the page also including partials._confirm (for shConfirm/shToast). --}}
@php $revs = $model->revisions()->limit(25)->get(); @endphp
<section class="rev-inline" aria-label="Edit history">
  <style>
    .rev-inline { margin-top: 2.5rem; padding-top: 1.6rem; border-top: 1px solid var(--line); }
    .rev-inline-h { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 500; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 0.4rem; }
    .rev-inline-sub { font-size: 12.5px; color: var(--ink-soft); margin-bottom: 1rem; line-height: 1.5; }
    .rev-inline-empty { font-size: 13px; color: var(--ink-soft); font-style: italic; }
    .rev-inline-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
    .rev-inline-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 9px 12px; background: #fff; border: 1px solid var(--line); border-radius: 5px; }
    .rev-inline-sum { font-size: 13px; color: var(--ink); }
    .rev-inline-meta { display: block; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.03em; color: var(--ink-soft); margin-top: 2px; }
    .rev-inline-btn { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--teal); background: transparent; border: 1px solid var(--line); border-radius: 5px; padding: 7px 12px; cursor: pointer; white-space: nowrap; }
    .rev-inline-btn:hover { border-color: var(--teal); }
    .rev-inline-btn:disabled { opacity: 0.5; cursor: default; }
    .rev-inline-all { display: inline-block; margin-top: 0.9rem; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--teal); text-decoration: none; }
  </style>
  <h2 class="rev-inline-h">Edit history</h2>
  <p class="rev-inline-sub">Each save is kept here. Undo any one of them — only this item changes, and the undo is itself reversible.</p>
  @if ($revs->isEmpty())
    <p class="rev-inline-empty">No prior versions yet. Once you save an edit, it shows up here with an undo.</p>
  @else
    <ul class="rev-inline-list">
      @foreach ($revs as $rev)
        <li class="rev-inline-item">
          <div>
            <span class="rev-inline-sum">{{ $rev->summary }}</span>
            <span class="rev-inline-meta">{{ $rev->created_at->format('M j, Y · g:ia') }} ({{ $rev->created_at->diffForHumans() }}) · {{ optional($rev->user)->name ?? 'system' }}</span>
          </div>
          <button type="button" class="rev-restore rev-inline-btn"
                  data-url="{{ route('admin.changes.restore', $rev->id) }}"
                  data-confirm="Undo this edit and restore how it was {{ $rev->created_at->diffForHumans() }}? Only this item changes — and the undo is itself reversible.">↺ Undo</button>
        </li>
      @endforeach
    </ul>
    <a class="rev-inline-all" href="{{ route('admin.changes', ['type' => get_class($model), 'id' => $model->getKey()]) }}">Full history for this item →</a>
  @endif
</section>
<script>
  // Guarded once-only restore handler (the central feed defines its own; this
  // serves inline panels). Reuses shConfirm/shToast from partials._confirm.
  if (!window.__revRestoreBound) {
    window.__revRestoreBound = true;
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.rev-restore');
      if (!btn) return;
      e.preventDefault();
      var url = btn.getAttribute('data-url');
      var token = (document.querySelector('meta[name=csrf-token]') || {}).content || '';
      window.shConfirm(btn.getAttribute('data-confirm'), { okLabel: 'Undo' }).then(function (ok) {
        if (!ok) return;
        btn.disabled = true;
        fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
          .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
          .then(function (res) {
            if (res.ok && res.d && res.d.ok) {
              window.shToast(res.d.message || 'Restored.');
              setTimeout(function () { window.location.reload(); }, 900);
            } else { btn.disabled = false; window.shToast((res.d && res.d.message) || 'Could not undo — try again.'); }
          })
          .catch(function () { btn.disabled = false; window.shToast('Network error — try again.'); });
      });
    });
  }
</script>
