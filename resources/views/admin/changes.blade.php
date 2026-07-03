<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Edit history — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 900px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(28px, 4vw, 36px); font-weight: 500; letter-spacing: 0.02em; color: var(--ink); margin-bottom: 0.5rem; text-transform: uppercase; }
  .lede { font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 640px; margin-bottom: 1.4rem; }
  .lede a { color: var(--teal); }

  .rev-flash { padding: 12px 16px; margin-bottom: 22px; background: color-mix(in srgb, var(--teal) 8%, transparent); border-left: 3px solid var(--teal); border-radius: 0 4px 4px 0; font-size: 14px; color: var(--ink); }
  .rev-filterbar { margin-bottom: 1.6rem; font-size: 13px; color: var(--ink-soft); }
  .rev-filterbar a { color: var(--teal); font-weight: 500; }

  .rev-empty { padding: 22px; background: #fff; border: 1px dashed var(--line); border-radius: 6px; font-size: 14px; color: var(--ink-soft); font-style: italic; }

  .rev-list { display: flex; flex-direction: column; gap: 8px; }
  .rev { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; padding: 13px 16px; background: #fff; border: 1px solid var(--line); border-radius: 6px; }
  .rev-main { min-width: 0; flex: 1; }
  .rev-label { font-size: 14px; color: var(--ink); font-weight: 600; }
  .rev-summary { font-size: 13px; color: var(--ink-soft); margin-top: 2px; }
  .rev-meta { display: block; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.04em; color: var(--ink-soft); margin-top: 5px; }
  .rev-detail { margin-top: 9px; border-top: 1px dashed var(--line); padding-top: 8px; font-size: 12px; }
  .rev-detail summary { cursor: pointer; color: var(--teal); font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; }
  .rev-field { display: flex; gap: 8px; margin-top: 6px; }
  .rev-field-name { font-family: 'JetBrains Mono', monospace; color: var(--ink-soft); min-width: 90px; flex-shrink: 0; }
  .rev-field-val { color: var(--ink); white-space: pre-wrap; word-break: break-word; }
  .rev-btn { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--teal); background: transparent; border: 1px solid var(--line); border-radius: 5px; padding: 8px 14px; cursor: pointer; white-space: nowrap; }
  .rev-btn:hover { border-color: var(--teal); }
  .rev-btn:disabled { opacity: 0.5; cursor: default; }

  .rev-pager { margin-top: 1.8rem; display: flex; gap: 10px; }
  .rev-pager a, .rev-pager span { font-family: 'JetBrains Mono', monospace; font-size: 11px; padding: 7px 12px; border: 1px solid var(--line); border-radius: 5px; text-decoration: none; color: var(--teal); }
  .rev-pager span { color: var(--ink-soft); opacity: 0.5; }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">EDIT HISTORY</span>
</div>

<main>
  <h1>Edit history.</h1>
  <p class="lede">Every content edit — bulletins, pages, lessons — with a one-click undo for each. This reverts just that one item to how it was, and nothing else. For rolling back a whole system update, see the <a href="{{ route('admin.changelog') }}">Changelog</a>.</p>

  @if ($filtered)
    <div class="rev-filterbar">Showing one record. <a href="{{ route('admin.changes') }}">@include('partials._arl') Show all edits</a></div>
  @endif

  @if ($revisions->isEmpty())
    <div class="rev-empty">No edits recorded yet. Every time someone changes a bulletin, page, or lesson from now on, it shows up here with an undo.</div>
  @else
    <div class="rev-list">
      @foreach ($revisions as $rev)
        <div class="rev" data-rev="{{ $rev->id }}">
          <div class="rev-main">
            <div class="rev-label">{{ $rev->label }}</div>
            <div class="rev-summary">{{ $rev->summary }}</div>
            <span class="rev-meta">{{ $rev->created_at->format('M j, Y · g:ia') }} ({{ $rev->created_at->diffForHumans() }}) · {{ optional($rev->user)->name ?? 'system' }}</span>
            @if (!empty($rev->snapshot))
              <details class="rev-detail">
                <summary>View what it was before</summary>
                @foreach ($rev->snapshot as $field => $val)
                  <div class="rev-field">
                    <span class="rev-field-name">{{ str_replace('_',' ',$field) }}</span>
                    <span class="rev-field-val">{{ \Illuminate\Support\Str::limit(is_scalar($val) ? (string) $val : json_encode($val), 400) }}</span>
                  </div>
                @endforeach
              </details>
            @endif
          </div>
          <button type="button" class="rev-btn rev-restore"
                  data-url="{{ route('admin.changes.restore', $rev->id) }}"
                  data-confirm="Restore &quot;{{ $rev->label }}&quot; to how it was {{ $rev->created_at->diffForHumans() }}? Only this item changes — and the restore is itself undoable.">↺ Restore</button>
        </div>
      @endforeach
    </div>

    <div class="rev-pager">
      {!! $revisions->links('pagination::simple-default') !!}
    </div>
  @endif
</main>

@include('partials._confirm')
<script>
  // Restore reuses the branded shConfirm/shToast (from _confirm) but, unlike the
  // generic data-confirm-ajax (which fades the row), it reloads so the reverted
  // content and the freshly-captured undo-revision both show.
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.rev-restore');
    if (!btn) return;
    e.preventDefault();
    var url = btn.getAttribute('data-url');
    var token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
    window.shConfirm(btn.getAttribute('data-confirm'), { okLabel: 'Restore' }).then(function (ok) {
      if (!ok) return;
      btn.disabled = true;
      fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
          if (res.ok && res.d && res.d.ok) {
            window.shToast(res.d.message || 'Restored.');
            setTimeout(function () { window.location.reload(); }, 900);
          } else {
            btn.disabled = false;
            window.shToast((res.d && res.d.message) || 'Could not restore — try again.');
          }
        })
        .catch(function () { btn.disabled = false; window.shToast('Network error — try again.'); });
    });
  });
</script>
</body>
</html>
