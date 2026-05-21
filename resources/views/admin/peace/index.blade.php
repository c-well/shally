<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Peace · Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 980px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(28px, 4vw, 38px); font-weight: 500; line-height: 1.05; margin-bottom: 8px; }
  .lede { font-size: 15px; color: var(--ink-soft); max-width: 600px; margin-bottom: 32px; }
  .status { padding: 14px 18px; background: rgba(3,97,122,0.08); border-left: 4px solid var(--teal); border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; }
  .sermon-row { display: grid; grid-template-columns: 90px 1fr auto auto; gap: 18px; align-items: center; padding: 18px 0; border-bottom: 1px solid var(--line); }
  .sermon-row:last-child { border-bottom: 0; }
  .sermon-date { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-soft); letter-spacing: 0.08em; text-transform: uppercase; }
  .sermon-title { font-family: 'Cormorant Garamond', serif; font-size: 19px; font-weight: 500; }
  .sermon-title a { color: var(--ink); text-decoration: none; }
  .sermon-title a:hover { color: var(--teal); }
  .sermon-speaker { font-size: 13px; color: var(--ink-soft); margin-top: 2px; }
  .sermon-counts { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-soft); letter-spacing: 0.06em; }
  .sermon-flags { display: inline-flex; gap: 4px; }
  .flag { font-size: 9px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; padding: 3px 7px; border-radius: 3px; }
  .flag-pub { background: rgba(45,134,89,0.12); color: #2d8659; }
  .flag-rev { background: rgba(176,141,60,0.15); color: var(--brass); }
  .flag-offsite { background: rgba(168,42,31,0.12); color: #a82a1f; }
  .flag-nosermon { background: rgba(26,35,50,0.08); color: var(--ink-soft); }
  .edit-btn { padding: 7px 14px; background: var(--teal); color: #fff; text-decoration: none; border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; transition: background 0.15s; }
  .edit-btn:hover { background: var(--teal-dark); }
</style>
</head>
<body>

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">peace ministry</span>
</header>

<main>
  <h1>Peace.</h1>
  <p class="lede">Sermons in the Finding Peace ministry. Click any title to edit fields, Q&As, scriptures, or flag as offsite / no-sermon.</p>
  <p style="margin: -20px 0 32px; font-size: 14px;">
    <a href="{{ route('admin.peace.polls.index') }}" style="display:inline-block;padding:8px 16px;background:var(--teal);color:#fff;text-decoration:none;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;">Manage polls →</a> <a href="{{ route('admin.peace.schedule') }}" style="display:inline-block;padding:8px 16px;background:transparent;color:var(--teal);border:1px solid var(--teal);text-decoration:none;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;margin-left:8px;">Schedule →</a> <a href="{{ route('admin.peace.analytics') }}" style="display:inline-block;padding:8px 16px;background:transparent;color:var(--teal);border:1px solid var(--teal);text-decoration:none;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;margin-left:8px;">Analytics →</a> <a href="{{ route('admin.peace.subscribers') }}" style="display:inline-block;padding:8px 16px;background:transparent;color:var(--teal);border:1px solid var(--teal);text-decoration:none;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;margin-left:8px;">Subscribers →</a> @php $pendingSubs = \App\Models\PeaceUserSubmission::where('status','pending')->count(); @endphp
  <a href="{{ route('admin.peace.submissions') }}" style="display:inline-block;padding:8px 16px;background:{{ $pendingSubs > 0 ? 'var(--brass)' : 'transparent' }};color:{{ $pendingSubs > 0 ? '#fff' : 'var(--teal)' }};border:1px solid {{ $pendingSubs > 0 ? 'var(--brass)' : 'var(--teal)' }};text-decoration:none;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;margin-left:8px;">@if($pendingSubs > 0) {{ $pendingSubs }} new @endif Submissions →</a>
  </p>

  @if (session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif

  @forelse($sermons as $sermon)
    <div class="sermon-row">
      <span class="sermon-date">{{ strtoupper($sermon->sermon_date->format('M j Y')) }}</span>
      <div>
        <div class="sermon-title"><a href="{{ route('admin.peace.edit', $sermon->slug) }}">{{ $sermon->title }}</a></div>
        @if($sermon->speaker) <div class="sermon-speaker">{{ $sermon->speaker }}</div> @endif
      </div>
      <div class="sermon-counts">{{ $sermon->qa_pairs_count }} q · {{ $sermon->scriptures_count }} scr</div>
      <div class="sermon-flags">
        @if($sermon->processing_status === 'published')
          <span class="flag flag-pub">Live</span>
        @elseif($sermon->processing_status === 'needs_review')
          <span class="flag flag-rev">Review</span>
        @endif
        @if($sermon->is_offsite ?? false) <span class="flag flag-offsite">Offsite</span> @endif
        @if($sermon->is_no_sermon ?? false) <span class="flag flag-nosermon">No Sermon</span> @endif
        <a href="{{ route('admin.peace.edit', $sermon->slug) }}" class="edit-btn">Edit</a>
      </div>
    </div>
  @empty
    <p style="padding: 40px 0; text-align:center; color: var(--ink-soft);">No sermons processed yet.</p>
  @endforelse
</main>

</body>
</html>
