<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Forms — Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{background:var(--parchment);color:var(--ink);font-family:'IBM Plex Sans',system-ui,sans-serif;min-height:100dvh;-webkit-font-smoothing:antialiased}
*:focus-visible{outline:2px solid var(--teal);outline-offset:2px;border-radius:3px}
.top{padding:16px clamp(16px,5vw,32px);display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line);position:sticky;top:0;background:color-mix(in srgb,var(--parchment) 94%,transparent);backdrop-filter:blur(6px);z-index:10}
.top a,.top .lnk{font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;text-decoration:none;color:var(--ink-soft)}
.top a:hover,.top .lnk:hover{color:var(--teal)}
.newbtn{font-family:inherit;font-size:12px;font-weight:600;padding:10px 20px;border-radius:7px;border:0;background:var(--teal);color:#fff;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.newbtn:hover{background:var(--teal-dark)}
main{max-width:700px;margin:0 auto;padding:clamp(24px,5vh,44px) clamp(16px,5vw,28px) 80px}
h1{font-size:clamp(26px,5vw,38px);font-weight:700;letter-spacing:-.02em;margin-bottom:28px}
.empty{padding:48px 24px;text-align:center;border:1px dashed var(--line);border-radius:10px;color:var(--ink-soft)}
.empty .big{font-size:22px;font-weight:600;color:var(--ink);margin-bottom:10px}
.forms{display:flex;flex-direction:column;gap:10px}
.frow{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid var(--line);border-radius:10px;padding:16px 18px}
.frow-info{flex:1;min-width:0}
.frow-title{font-size:16px;font-weight:600;color:var(--ink);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.frow-meta{font-size:12px;color:var(--ink-soft);display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.frow-meta .badge{font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:3px 8px;border-radius:4px;background:var(--parchment)}
.frow-meta .badge.live{background:color-mix(in srgb,var(--teal) 14%,transparent);color:var(--teal-dark)}
.frow-actions{display:flex;gap:8px;flex-shrink:0}
.frow-actions a{font-size:12px;font-weight:600;text-decoration:none;padding:8px 13px;border:1px solid var(--line);border-radius:6px;color:var(--ink-soft);white-space:nowrap}
.frow-actions a:hover{border-color:var(--teal);color:var(--teal)}
.frow-actions a.primary{background:var(--teal);border-color:var(--teal);color:#fff}
.frow-actions a.primary:hover{background:var(--teal-dark)}
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme','default') }}">
<div class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <a href="{{ route('admin.intake.create') }}" class="newbtn">＋ New form</a>
</div>

<main>
  <h1>Forms</h1>

  @if ($forms->isEmpty())
    <div class="empty">
      <div class="big">No forms yet.</div>
      <p>Create your first form — it becomes a link you can share or add to the site menu.</p>
    </div>
  @else
    <div class="forms">
      @foreach ($forms as $f)
        <div class="frow">
          <div class="frow-info">
            <div class="frow-title">{{ $f->title }}</div>
            <div class="frow-meta">
              <span>thechurchofpeace.org/intake/{{ $f->slug }}</span>
              <span class="badge {{ $f->is_active ? 'live' : '' }}">{{ $f->is_active ? 'Active' : 'Inactive' }}</span>
              <span>{{ $f->submissions()->count() }} {{ Str::plural('submission', $f->submissions()->count()) }}</span>
              @if ($f->inMenu())<span class="badge live">In menu</span>@endif
            </div>
          </div>
          <div class="frow-actions">
            <a href="{{ route('admin.intake.submissions', $f) }}">Gallery</a>
            <a href="{{ route('admin.intake.builder.edit', $f) }}" class="primary">Edit</a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</main>
</body>
</html>
