<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Messages — Admin · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>  :root { --pulse:#36c980; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 980px; margin: 0 auto; padding: 0 clamp(20px, 5vw, 40px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(28px, 4vw, 38px); font-weight: 500; letter-spacing: -0.015em; line-height: 1.05; margin-bottom: 6px; }
  .lede { font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 640px; }

  .tabs { display: flex; gap: 28px; border-bottom: 1px solid var(--line); margin: 28px 0 24px; }
  .tab { padding: 12px 0; font-family: 'Instrument Sans', sans-serif; font-size: 12px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); cursor: pointer; background: transparent; border: 0; border-bottom: 2px solid transparent; transition: color 0.15s, border-color 0.15s; display: inline-flex; align-items: center; gap: 8px; }
  .tab.active { color: var(--ink); border-bottom-color: var(--teal); }
  .tab .badge { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; background: var(--warn); color: #fff; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0; }
  .tab .badge.pulse { background: var(--warn); animation: pulseBadge 2.6s ease-in-out infinite; }
  @keyframes pulseBadge {
    0%, 100% { box-shadow: 0 0 0 0 rgba(168,42,31,0.6); }
    50%      { box-shadow: 0 0 0 8px rgba(168,42,31,0); }
  }

  .tab-panel { display: none; }
  .tab-panel.active { display: block; }

  .msg { display: block; padding: 18px 0; border-bottom: 1px solid var(--line); }
  .msg-row { display: flex; align-items: baseline; gap: 12px; margin-bottom: 6px; flex-wrap: wrap; }
  .msg-name { font-family: 'Cormorant Garamond', serif; font-size: 21px; font-weight: 500; color: var(--ink); }
  .msg-meta { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ink-soft); opacity: 0.7; }
  .msg-flags { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--teal); }
  .msg-flags .flag { display: inline-block; padding: 2px 7px; border-radius: 3px; background: color-mix(in srgb, var(--teal) 8%, transparent); margin-right: 4px; }
  .msg-flags .flag.danger { color: var(--warn); background: rgba(168,42,31,0.06); }
  .msg-body { font-family: 'Cormorant Garamond', serif; font-size: 17px; line-height: 1.55; color: var(--ink); white-space: pre-wrap; margin-top: 6px; max-width: 720px; }
  .msg-actions { margin-top: 10px; display: inline-flex; gap: 12px; align-items: center; }
  .msg-actions a, .msg-actions button { font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--teal); background: transparent; border: 0; cursor: pointer; text-decoration: none; padding: 0; }
  .msg-actions a:hover, .msg-actions button:hover { color: var(--teal-dark); text-decoration: underline; }

  /* padding-left is 9px (not 12) to offset the 3px border under box-sizing:border-box,
     so unread body/actions line up exactly with read messages. (alignment fix 2026-06-18) */
  .msg.unread { background: rgba(54,201,128,0.04); margin-left: -12px; padding-left: 9px; border-left: 3px solid var(--pulse); }
  .msg-actions .delete { color: var(--warn) !important; }
  .msg-actions .delete:hover { color: #fff !important; background: var(--warn); padding: 2px 6px !important; border-radius: 3px; text-decoration: none !important; }
  .msg.unread .msg-name::before { content: '● '; color: var(--pulse); font-size: 14px; }

  .empty { padding: 40px 0; text-align: center; color: var(--ink-soft); font-style: italic; opacity: 0.7; }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">messages</span>
</header>

<main>
  <h1>Messages.</h1>
  <p class="lede">Prayer requests and public contact-form messages. Click "Mark read" once you've handled it.</p>

  <div class="tabs" role="tablist">
    <button class="tab active" data-target="prayers" role="tab">
      Prayer
      @if ($unreadPrayers > 0)
        <span class="badge pulse">{{ $unreadPrayers }}</span>
      @endif
    </button>
    <button class="tab" data-target="contacts" role="tab">
      Contact
      @if ($unreadContacts > 0)
        <span class="badge">{{ $unreadContacts }}</span>
      @endif
    </button>
  </div>

  {{-- Prayer requests --}}
  <section class="tab-panel active" id="prayers" role="tabpanel">
    @forelse ($prayers as $p)
      <div class="msg @if(!$p->read_at) unread @endif">
        <div class="msg-row">
          <span class="msg-name">{{ $p->name ?: '— anonymous —' }}</span>
          <span class="msg-meta">{{ $p->created_at->diffForHumans() }} · {{ $p->created_at->format('M j, Y g:ia') }}</span>
        </div>
        @if ($p->email || $p->phone || $p->want_followup || $p->keep_private)
          <div class="msg-flags">
            @if ($p->email)<span class="flag">✉ {{ $p->email }}</span>@endif
            @if ($p->phone)<span class="flag">☎ {{ $p->phone }}</span>@endif
            @if ($p->want_followup)<span class="flag danger">Follow-up requested</span>@endif
            @if ($p->keep_private)<span class="flag">Private — prayer team only</span>@else<span class="flag">OK to share with congregation</span>@endif
          </div>
        @endif
        <div class="msg-body">{{ $p->body }}</div>
        <div class="msg-actions">
          @if (!$p->read_at)
            <form method="POST" action="{{ route('admin.messages.prayer.read', $p->id) }}" style="display:inline;">@csrf
              <button type="submit">Mark read</button>
            </form>
          @else
            <span class="msg-meta">read {{ $p->read_at->diffForHumans() }}</span>
          @endif
          @if ($p->email)
            <a href="mailto:{{ $p->email }}?subject=Your%20prayer%20request">Reply via email</a>
          @endif
          <form method="POST" action="{{ route('admin.messages.prayer.delete', $p->id) }}" style="display:inline;" onsubmit="return confirm('Move this prayer request to trash? Recoverable for 30 days.');">@csrf
            <button type="submit" class="delete">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="empty">No prayer requests yet.</div>
    @endforelse
  </section>

  {{-- Contact messages --}}
  <section class="tab-panel" id="contacts" role="tabpanel">
    @forelse ($contacts as $c)
      <div class="msg @if(!$c->read_at) unread @endif">
        <div class="msg-row">
          <span class="msg-name">{{ $c->name }}</span>
          <span class="msg-meta">{{ $c->created_at->diffForHumans() }} · {{ $c->created_at->format('M j, Y g:ia') }}</span>
        </div>
        <div class="msg-flags"><span class="flag">✉ {{ $c->email }}</span></div>
        <div class="msg-body">{{ $c->message }}</div>
        <div class="msg-actions">
          @if (!$c->read_at)
            <form method="POST" action="{{ route('admin.messages.contact.read', $c->id) }}" style="display:inline;">@csrf
              <button type="submit">Mark read</button>
            </form>
          @else
            <span class="msg-meta">read {{ $c->read_at->diffForHumans() }}</span>
          @endif
          <a href="mailto:{{ $c->email }}?subject=Re%3A%20your%20message">Reply via email</a>
          <form method="POST" action="{{ route('admin.messages.contact.delete', $c->id) }}" style="display:inline;" onsubmit="return confirm('Move this message to trash? Recoverable for 30 days.');">@csrf
            <button type="submit" class="delete">Delete</button>
          </form>
        </div>
      </div>
    @empty
      <div class="empty">No contact-form messages yet.</div>
    @endforelse
  </section>
</main>

<script>
  document.querySelectorAll('.tab').forEach(t => t.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(x => x.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(x => x.classList.remove('active'));
    t.classList.add('active');
    document.getElementById(t.dataset.target).classList.add('active');
  }));
</script>
</body>
</html>
