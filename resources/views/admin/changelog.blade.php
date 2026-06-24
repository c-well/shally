<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Changelog — The Church of Peace</title>
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
  .lede { font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 600px; margin-bottom: 2.5rem; }

  .doc { line-height: 1.65; color: var(--ink); }
  .doc h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 500; margin: 2.5rem 0 0.6rem; padding-top: 1.5rem; border-top: 1px solid var(--line); color: var(--ink); }
  .doc > h2:first-of-type { border-top: 0; padding-top: 0; margin-top: 0; }
  .doc h3 { font-family: 'Instrument Sans', sans-serif; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ink-soft); font-weight: 600; margin: 1.2rem 0 0.4rem; }
  .doc p { margin: 0 0 0.8rem; font-size: 0.93rem; }
  .doc strong { color: var(--ink); font-weight: 600; }
  .doc ul, .doc ol { margin: 0 0 1rem 1.4rem; }
  .doc li { margin-bottom: 0.4rem; font-size: 0.93rem; }
  .doc code { background: color-mix(in srgb, var(--teal) 6%, transparent); color: var(--teal-dark); font-family: 'JetBrains Mono', monospace; font-size: 0.82em; padding: 1px 5px; border-radius: 3px; }
  .doc pre { background: #fff; border: 1px solid var(--line); border-radius: 4px; padding: 12px 14px; overflow-x: auto; margin: 0.8rem 0 1.2rem; }
  .doc pre code { background: none; padding: 0; color: var(--ink); font-size: 0.85em; }
  .doc hr { border: 0; height: 1px; background: var(--line); margin: 2rem 0; }
  .doc blockquote { border-left: 3px solid var(--teal); padding: 8px 18px; margin: 0 0 1.2rem; color: var(--ink-soft); background: color-mix(in srgb, var(--teal) 4%, transparent); border-radius: 0 4px 4px 0; }
  .doc blockquote p { margin-bottom: 0; }
  .doc table { width: 100%; border-collapse: collapse; margin: 0.8rem 0 1.4rem; font-size: 0.92em; }
  .doc th, .doc td { border: 1px solid var(--line); padding: 8px 12px; text-align: left; }
  .doc th { background: color-mix(in srgb, var(--teal) 4%, transparent); font-weight: 600; }

  .edit-hint { margin-top: 3rem; padding: 14px 18px; background: color-mix(in srgb, var(--brass) 7%, transparent); border-left: 3px solid var(--brass); border-radius: 0 4px 4px 0; font-size: 13px; color: var(--ink-soft); line-height: 1.55; }
  .edit-hint strong { color: var(--ink); }

  /* Restore points + rollback */
  .cl-flash { padding: 12px 16px; margin-bottom: 22px; background: color-mix(in srgb, var(--teal) 8%, transparent); border-left: 3px solid var(--teal); border-radius: 0 4px 4px 0; font-size: 14px; color: var(--ink); }
  .cl-section { margin-bottom: 2.6rem; }
  .cl-section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
  .cl-h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 500; color: var(--ink); margin-bottom: 0.4rem; }
  .cl-note { font-size: 13px; color: var(--ink-soft); line-height: 1.55; margin-bottom: 1rem; max-width: 640px; }
  .cl-empty { padding: 18px; background: #fff; border: 1px dashed var(--line); border-radius: 6px; font-size: 13px; color: var(--ink-soft); font-style: italic; }
  .cl-cps { display: flex; flex-direction: column; gap: 8px; }
  .cl-cp { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 16px; background: #fff; border: 1px solid var(--line); border-radius: 6px; }
  .cl-cp-main { min-width: 0; }
  .cl-cp-kind { display: inline-block; font-family: 'JetBrains Mono', monospace; font-size: 9px; letter-spacing: 0.1em; text-transform: uppercase; padding: 2px 7px; border-radius: 3px; margin-right: 8px; background: color-mix(in srgb, var(--ink-soft) 12%, transparent); color: var(--ink-soft); }
  .cl-cp-kind-auto_update { background: color-mix(in srgb, var(--teal) 14%, transparent); color: var(--teal-dark); }
  .cl-cp-kind-pre_rollback { background: color-mix(in srgb, var(--brass) 16%, transparent); color: var(--brass); }
  .cl-cp-label { font-size: 14px; color: var(--ink); font-weight: 500; }
  .cl-cp-meta { display: block; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.04em; color: var(--ink-soft); margin-top: 4px; }
  .cl-cp-gone { font-family: 'JetBrains Mono', monospace; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1em; color: var(--ink-soft); opacity: 0.6; }
  .cl-btn-ghost { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--teal); background: transparent; border: 1px solid var(--line); border-radius: 5px; padding: 8px 14px; cursor: pointer; }
  .cl-btn-ghost:hover { border-color: var(--teal); }
  .cl-btn-danger { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: #fff; background: var(--warn, #a82a1f); border: 0; border-radius: 5px; padding: 8px 14px; cursor: pointer; white-space: nowrap; }
  .cl-btn-danger:hover { filter: brightness(1.08); }
  .cl-commits { display: flex; flex-direction: column; gap: 2px; font-family: 'JetBrains Mono', monospace; font-size: 11px; }
  .cl-commit { display: flex; gap: 12px; padding: 4px 0; border-bottom: 1px dashed color-mix(in srgb, var(--line) 60%, transparent); color: var(--ink); }
  .cl-commit-sha { color: var(--teal); min-width: 56px; }
  .cl-commit-date { color: var(--ink-soft); min-width: 80px; }
  .cl-commit-subj { color: var(--ink); }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

<div class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">CHANGELOG</span>
</div>

<main>
  <h1>Changelog.</h1>
  <p class="lede">Every notable change to the site, in plain English. Newest at the top. When something feels off, scroll back through this to see what shifted recently.</p>

  @if (session('status'))
    <div class="cl-flash">{{ session('status') }}</div>
  @endif

  {{-- ─── RESTORE POINTS (checkpoints) ─────────────────────────────────── --}}
  <section class="cl-section">
    <div class="cl-section-head">
      <h2 class="cl-h2">Restore points</h2>
      @if ($canRollback)
        <form method="POST" action="{{ route('admin.changelog.checkpoint') }}" style="display:inline;"
              data-confirm="Capture a restore point of the site exactly as it is right now?" data-confirm-ok="Capture">@csrf
          <button type="submit" class="cl-btn-ghost">+ Capture checkpoint now</button>
        </form>
      @endif
    </div>
    <p class="cl-note">A restore point is a saved last-known-good state (code lock + database). The self-update routine captures one before every update; you can capture one manually before risky changes. {{ $canRollback ? 'You can roll back to any restorable point below.' : 'Only super-admins (and assigned users) can roll back.' }}</p>

    @if ($checkpoints->isEmpty())
      <div class="cl-empty">No restore points yet. The first one appears after the next self-update or when you capture one.</div>
    @else
      <div class="cl-cps">
        @foreach ($checkpoints as $cp)
          <div class="cl-cp">
            <div class="cl-cp-main">
              <span class="cl-cp-kind cl-cp-kind-{{ $cp->kind }}">{{ str_replace('_',' ',$cp->kind) }}</span>
              <span class="cl-cp-label">{{ $cp->label }}</span>
              @php
                $meta = $cp->created_at->diffForHumans();
                if ($cp->app_version) $meta .= ' · ' . $cp->app_version;
                if ($cp->git_sha) $meta .= ' · ' . substr($cp->git_sha, 0, 7);
                if ($cp->restored_at) $meta .= ' · ⤺ restored ' . $cp->restored_at->diffForHumans();
              @endphp
              <span class="cl-cp-meta">{{ $meta }}</span>
            </div>
            @if ($canRollback)
              @if ($cp->isRestorable())
                <form method="POST" action="{{ route('admin.changelog.restore', $cp->id) }}" style="display:inline;"
                      data-confirm="Roll the WHOLE SITE back to &quot;{{ $cp->label }}&quot; ({{ $cp->created_at->format('M j, g:ia') }})? Code + database both revert. A fresh restore point of the current state is captured first, so this is undoable." data-confirm-ok="Roll back">@csrf
                  <button type="submit" class="cl-btn-danger">↺ Roll back</button>
                </form>
              @else
                <span class="cl-cp-gone">backup expired</span>
              @endif
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </section>

  @if (!empty($commits))
  <section class="cl-section">
    <h2 class="cl-h2">Recent code commits</h2>
    <div class="cl-commits">
      @foreach ($commits as $c)
        <div class="cl-commit"><span class="cl-commit-sha">{{ $c['sha'] }}</span><span class="cl-commit-date">{{ $c['date'] }}</span><span class="cl-commit-subj">{{ $c['subj'] }}</span></div>
      @endforeach
    </div>
  </section>
  @endif

  <h2 class="cl-h2" style="margin-top:2.5rem;">Change history</h2>
  <div class="doc">
    {!! $html !!}
  </div>

  <div class="edit-hint">
    <strong>To add an entry:</strong>
    edit <code>/home/shalom/laravel/docs/CHANGELOG.md</code> on the server.
    Format: <code>## YYYY-MM-DD · Short headline</code> followed by what was wrong / what changed / fix. Newest at the top.
    This page reads the file every load — your edit shows up immediately.
  </div>
</main>

@include('partials._confirm')
</body>
</html>
