<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Prayer requests · Intercessors · The Church of Peace</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: #fefcef; color: #1a2332; font-family: 'Instrument Sans', sans-serif; min-height: 100dvh; }
  main { max-width: 760px; margin: 0 auto; padding: 40px 20px 80px; }

  .top { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; }
  .top .who-am-i { font: 700 11px 'Instrument Sans'; letter-spacing: .22em; text-transform: uppercase; color: #03617A; }
  .top .signout { font: 700 10px 'Instrument Sans'; letter-spacing: .18em; text-transform: uppercase; color: #4a5568; text-decoration: none; }
  .top .signout:hover { color: #03617A; }

  h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 500; letter-spacing: -.02em; margin: 4px 0 8px; }
  .sub { color: #4a5568; margin-bottom: 30px; font-family: 'Cormorant Garamond'; font-size: 17px; }

  .req { background: #fff; border: 1px solid rgba(26,35,50,.1); border-radius: 10px; padding: 20px 22px; margin-bottom: 14px; }
  .req.private { border-left: 3px solid #b08d3c; }
  .req-head { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap; margin-bottom: 4px; }
  .req-name { font-family: 'Cormorant Garamond', serif; font-size: 21px; font-weight: 500; }
  .req-meta { font: 500 11px 'JetBrains Mono', monospace; letter-spacing: .06em; color: #6b7280; }
  .flags { margin: 6px 0 10px; display: flex; flex-wrap: wrap; gap: 6px; }
  .flag { font: 600 10px 'Instrument Sans'; letter-spacing: .12em; text-transform: uppercase; padding: 3px 8px; border-radius: 3px; background: #f4efdd; color: #6b5822; }
  .flag.danger { background: #f8d6ce; color: #8a2a10; }
  .flag.private { background: #f2e6c5; color: #6b4a10; }
  .req-body { font-family: 'Cormorant Garamond', serif; font-size: 17.5px; line-height: 1.55; white-space: pre-wrap; margin: 12px 0 16px; color: #1a2332; }

  .req-foot { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding-top: 12px; border-top: 1px dashed rgba(26,35,50,.1); flex-wrap: wrap; }
  .who-list { font-size: 12px; color: #6b7280; line-height: 1.4; }
  .who-list b { color: #1a2332; font-weight: 600; }
  .prayed { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: transparent; color: #03617A; border: 1px solid #03617A; border-radius: 6px; font: 700 11px 'Instrument Sans'; letter-spacing: .14em; text-transform: uppercase; cursor: pointer; }
  .prayed:hover { background: #03617A; color: #fff; }
  .prayed.on { background: #1f6843; color: #fff; border-color: #1f6843; }

  .empty { padding: 60px 20px; text-align: center; color: #6b7280; font-family: 'Cormorant Garamond'; font-style: italic; font-size: 18px; }
</style>
</head>
<body>
<main>
  <div class="top">
    <span class="who-am-i">Signed in as {{ $me->name }}{{ $me->isHead() ? ' ★' : '' }}</span>
    <form method="POST" action="{{ route('intercessors.signOut') }}" style="display:inline;">@csrf
      <button type="submit" class="signout" style="background:none;border:0;cursor:pointer;">Sign out</button>
    </form>
  </div>
  <h1>Prayer requests</h1>
  <p class="sub">Newest first. Marking your prayers lets the rest of the team see they've been covered.</p>

  @forelse ($requests as $r)
    <div class="req {{ $r->keep_private ? 'private' : '' }}">
      <div class="req-head">
        <span class="req-name">{{ $r->name ?: 'Anonymous' }}</span>
        <span class="req-meta">{{ $r->created_at->diffForHumans() }} · {{ $r->created_at->format('M j, Y g:ia') }}</span>
      </div>
      <div class="flags">
        @if ($r->email)<span class="flag">✉ {{ $r->email }}</span>@endif
        @if ($r->phone)<span class="flag">☎ {{ $r->phone }}</span>@endif
        @if ($r->want_followup)<span class="flag danger">Follow-up requested</span>@endif
        @if ($r->keep_private)<span class="flag private">Private — team only</span>@else<span class="flag">OK to share</span>@endif
      </div>
      <div class="req-body">{{ $r->body }}</div>
      <div class="req-foot">
        <div class="who-list">
          @php
            $viewers = $viewsByReq[$r->id] ?? collect();
            $prayers = $prayedByReq[$r->id] ?? collect();
            $iPrayed = $prayers->contains(fn($p) => $p->intercessor_id == $me->id);
          @endphp
          <div><b>{{ $viewers->count() }} seen</b>{{ $viewers->count() ? ': ' . $viewers->pluck('name')->implode(', ') : '' }}</div>
          <div><b>{{ $prayers->count() }} prayed</b>{{ $prayers->count() ? ': ' . $prayers->pluck('name')->implode(', ') : '' }}</div>
        </div>
        <form method="POST" action="{{ route('intercessors.togglePrayed', $r->id) }}">@csrf
          <button type="submit" class="prayed {{ $iPrayed ? 'on' : '' }}">
            {{ $iPrayed ? '🙏 Prayed' : '🙏 Mark prayed' }}
          </button>
        </form>
      </div>
    </div>
  @empty
    <div class="empty">No prayer requests yet.</div>
  @endforelse
</main>
</body>
</html>
