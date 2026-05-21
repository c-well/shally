<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Peace · Submissions · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --ink-faint:rgba(26,35,50,0.45); --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); --red:#a82a1f; --green:#2d8659; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  main { max-width: 1080px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: clamp(20px, 3vw, 26px); font-weight: 500; letter-spacing: 0.04em; margin-bottom: 8px; text-transform: uppercase; }
  h2 { font-family: 'JetBrains Mono', monospace; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); font-weight: 500; margin: 36px 0 14px; }
  .lede { font-size: 14px; color: var(--ink-soft); margin-bottom: 24px; max-width: 660px; }

  .kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 30px; }
  .kpi { background: #fff; border: 1px solid var(--line); border-radius: 6px; padding: 16px 20px; }
  .kpi .lbl { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
  .kpi .num { font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 500; color: var(--ink); line-height: 1; }
  .kpi.warn .num { color: var(--brass); }
  .kpi.ok .num { color: var(--green); }

  .filters { display: flex; gap: 6px; margin-bottom: 18px; flex-wrap: wrap; }
  .filter-btn {
    padding: 6px 14px; background: transparent; color: var(--ink-soft);
    border: 1px solid var(--line); border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600;
    letter-spacing: 0.14em; text-transform: uppercase;
    cursor: pointer; text-decoration: none;
  }
  .filter-btn:hover { color: var(--teal); border-color: var(--teal); }
  .filter-btn.active { background: var(--teal); color: #fff; border-color: var(--teal); }

  .status { padding: 14px 18px; background: rgba(3,97,122,0.08); border-left: 4px solid var(--teal); border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; }

  .sub-item {
    background: #fff; border: 1px solid var(--line); border-radius: 6px;
    padding: 18px 22px; margin-bottom: 14px;
  }
  .sub-meta {
    display: flex; flex-wrap: wrap; gap: 0.7rem; align-items: center;
    font-family: 'JetBrains Mono', monospace; font-size: 11px;
    color: var(--ink-soft); margin-bottom: 12px;
  }
  .type-pill, .stat-pill {
    display: inline-block; padding: 3px 9px; border-radius: 3px;
    font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; font-weight: 600;
  }
  .type-pill.question  { background: rgba(3,97,122,0.10); color: var(--teal); }
  .type-pill.experience { background: rgba(176,141,60,0.15); color: var(--brass); }
  .stat-pill.pending  { background: rgba(176,141,60,0.15); color: var(--brass); }
  .stat-pill.approved { background: rgba(45,134,89,0.12); color: var(--green); }
  .stat-pill.archived { background: rgba(26,35,50,0.06); color: var(--ink-faint); }
  .stat-pill.replied  { background: rgba(3,97,122,0.10); color: var(--teal); }

  .sub-body { font-size: 14px; line-height: 1.65; color: var(--ink); margin-bottom: 14px; white-space: pre-wrap; }
  .sub-attribution { font-family: 'Cormorant Garamond', serif; font-style: italic; color: var(--ink-soft); font-size: 13px; margin-bottom: 14px; }
  .sub-actions { display: flex; gap: 10px; flex-wrap: wrap; padding-top: 12px; border-top: 1px dashed var(--line); }
  .btn { padding: 7px 14px; background: var(--teal); color: #fff; border: 0; border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; cursor: pointer; text-decoration: none; }
  .btn:hover { background: var(--teal-dark); }
  .btn.ghost { background: transparent; color: var(--ink-soft); border: 1px solid var(--line); }
  .btn.ghost:hover { color: var(--teal); border-color: var(--teal); }
  .btn.danger { color: var(--red); border: 1px solid rgba(168,42,31,0.3); background: transparent; }
  .btn.danger:hover { background: rgba(168,42,31,0.08); }

  details.reply-form summary {
    cursor: pointer; list-style: none;
    padding: 7px 14px; background: transparent; color: var(--ink-soft);
    border: 1px solid var(--line); border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600;
    letter-spacing: 0.14em; text-transform: uppercase;
    display: inline-block;
  }
  details.reply-form summary::-webkit-details-marker { display: none; }
  details.reply-form summary:hover { color: var(--teal); border-color: var(--teal); }
  details.reply-form[open] summary { background: var(--teal); color: #fff; }
  details.reply-form form { margin-top: 14px; padding: 14px; background: rgba(3,97,122,0.04); border-radius: 6px; }
  details.reply-form textarea { width: 100%; padding: 10px 12px; background: #fff; border: 1px solid var(--line); border-radius: 4px; font-family: inherit; font-size: 13px; min-height: 100px; }

  .empty { padding: 60px 0; text-align: center; color: var(--ink-soft); font-style: italic; }
  .replied-block { padding: 14px 16px; background: rgba(3,97,122,0.06); border-left: 3px solid var(--teal); border-radius: 0 4px 4px 0; margin-top: 12px; font-size: 13px; }
  .replied-block .h { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.14em; text-transform: uppercase; color: var(--teal); margin-bottom: 4px; }
</style>
</head>
<body>
@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.peace.index') }}">← Peace admin</a>
  <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;color:var(--ink-soft);opacity:0.65;">submissions</span>
</header>

<main>
  <h1>Submissions</h1>
  <p class="lede">Questions and experiences from seekers — every one read before anything goes public. Approve to mark "seen", archive to discard, reply to send a personal note.</p>

  @if (session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif

  <div class="kpis">
    <div class="kpi {{ $kpi['pending'] > 0 ? 'warn' : '' }}"><div class="lbl">Pending</div><div class="num">{{ $kpi['pending'] }}</div></div>
    <div class="kpi ok"><div class="lbl">Approved</div><div class="num">{{ $kpi['approved'] }}</div></div>
    <div class="kpi"><div class="lbl">Replied</div><div class="num">{{ $kpi['replied'] }}</div></div>
    <div class="kpi"><div class="lbl">Archived</div><div class="num">{{ $kpi['archived'] }}</div></div>
    <div class="kpi"><div class="lbl">Total</div><div class="num">{{ $kpi['total'] }}</div></div>
  </div>

  <div class="filters">
    @foreach (['all'=>'All', 'pending'=>'Pending', 'approved'=>'Approved', 'replied'=>'Replied', 'archived'=>'Archived'] as $key => $lbl)
      <a href="{{ route('admin.peace.submissions', ['status'=>$key]) }}" class="filter-btn {{ $filter === $key ? 'active' : '' }}">{{ $lbl }}</a>
    @endforeach
  </div>

  @forelse ($subs as $sub)
    <div class="sub-item">
      <div class="sub-meta">
        <span class="type-pill {{ $sub->type }}">{{ $sub->type }}</span>
        <span class="stat-pill {{ $sub->status }}">{{ $sub->status }}</span>
        <span>{{ $sub->subscriber->email ?? '(deleted)' }}</span>
        <span>· {{ $sub->created_at->diffForHumans() }}</span>
        @if ($sub->sermon) <span>· from <strong>{{ $sub->sermon->title }}</strong></span> @endif
        @if ($sub->topic)  <span>· topic <strong>{{ $sub->topic->name }}</strong></span> @endif
      </div>

      @if ($sub->type === 'experience')
        <div class="sub-attribution">
          @if ($sub->attribution === 'first_name_city' && $sub->attribution_display)
            display as: <strong>"{{ $sub->attribution_display }}"</strong>
          @else
            display as: <strong>anonymous ("From a fellow seeker")</strong>
          @endif
        </div>
      @endif

      <div class="sub-body">{{ $sub->body }}</div>

      @if ($sub->pastor_reply)
        <div class="replied-block">
          <div class="h">Pastor's reply · {{ $sub->pastor_replied_at?->diffForHumans() }}</div>
          <div style="white-space:pre-wrap;">{{ $sub->pastor_reply }}</div>
        </div>
      @endif

      <div class="sub-actions">
        @if ($sub->status === 'pending')
          <form method="POST" action="{{ route('admin.peace.submissions.update', $sub->id) }}" style="display:inline;">
            @csrf
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn">Approve / mark seen</button>
          </form>
        @endif

        @if ($sub->status !== 'archived')
          <form method="POST" action="{{ route('admin.peace.submissions.update', $sub->id) }}" style="display:inline;" onsubmit="return confirm('Archive this submission?');">
            @csrf
            <input type="hidden" name="action" value="archive">
            <button type="submit" class="btn danger">Archive</button>
          </form>
        @else
          <form method="POST" action="{{ route('admin.peace.submissions.update', $sub->id) }}" style="display:inline;">
            @csrf
            <input type="hidden" name="action" value="restore">
            <button type="submit" class="btn ghost">Restore</button>
          </form>
        @endif

        <details class="reply-form">
          <summary>{{ $sub->status === 'replied' ? 'Edit reply' : 'Reply by email' }}</summary>
          <form method="POST" action="{{ route('admin.peace.submissions.update', $sub->id) }}">
            @csrf
            <input type="hidden" name="action" value="reply">
            <textarea name="pastor_reply" placeholder="Type your reply — this will be emailed to {{ $sub->subscriber->email ?? 'the seeker' }} from app@thechurchofpeace.org, with replies forwarded back to you." required>{{ $sub->pastor_reply }}</textarea>
            <div style="margin-top: 10px;">
              <button type="submit" class="btn">Send reply →</button>
            </div>
          </form>
        </details>
      </div>
    </div>
  @empty
    <div class="empty">No submissions match this filter.</div>
  @endforelse
</main>
</body>
</html>
