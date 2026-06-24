<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Users — The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  input:focus-visible, select:focus-visible { outline: 0; }

  .top { padding: 22px clamp(20px, 5vw, 40px); display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line); }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }

  main { max-width: 1080px; margin: 0 auto; padding: clamp(48px, 9vh, 88px) clamp(20px, 5vw, 32px) 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(48px, 7vw, 72px); font-weight: 500; letter-spacing: -0.035em; line-height: 1; color: var(--ink); }
  .lede { margin-top: 22px; font-size: 15px; line-height: 1.55; color: var(--ink-soft); max-width: 540px; }

  .flash {
    margin-top: 26px;
    background: color-mix(in srgb, var(--brass) 10%, transparent); border-left: 3px solid var(--brass);
    padding: 14px 18px; border-radius: 0 4px 4px 0;
    font-size: 14px; color: var(--ink); line-height: 1.5;
  }

  /* Add-user form — desktop: 4 fields + button on one row; tablet: 2 cols; mobile: stacked */
  .add-form {
    margin-top: 36px;
    padding: 22px 24px;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 8px;
    max-width: 920px;
    display: grid;
    gap: 14px 16px;
    grid-template-columns: minmax(150px, 1.4fr) minmax(180px, 2fr) minmax(140px, 1.2fr) minmax(120px, 1fr) auto;
    align-items: end;
  }
  .add-form .row { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
  .add-form .lbl {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; font-weight: 700;
    letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--ink-soft);
    line-height: 1;
    height: 12px;  /* lock label height so all rows align bottom-edge perfectly */
    overflow: hidden;
  }
  .add-form input, .add-form select {
    font: inherit; font-size: 14px;
    padding: 10px 12px;
    height: 40px;  /* lock control height too — input/select natural heights differ */
    border: 1px solid var(--line); border-radius: 4px;
    background: #fff; color: var(--ink);
    width: 100%;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .add-form input:focus, .add-form select:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent);
  }
  .add-form button {
    background: var(--teal); color: #fff; border: 1px solid var(--teal);
    height: 40px;  /* match input height for clean baseline */
    padding: 0 22px; border-radius: 4px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    cursor: pointer; white-space: nowrap;
    transition: background 0.15s;
  }
  .add-form button:hover { background: var(--teal-dark); }

  /* Tablet — 2 cols, role + PIN side-by-side, button full-width */
  @media (max-width: 860px) {
    .add-form { grid-template-columns: 1fr 1fr; }
    .add-form button { grid-column: 1 / -1; }
  }
  /* Phone — stack everything */
  @media (max-width: 480px) {
    .add-form { grid-template-columns: 1fr; padding: 18px 18px; }
  }

  /* Users list */
  .users-section { margin-top: 50px; }
  .section-label {
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700;
    letter-spacing: 0.24em; text-transform: uppercase; color: var(--teal); margin-bottom: 16px;
  }
  .user-row {
    display: grid;
    grid-template-columns: minmax(140px, 1.4fr) minmax(180px, 2fr) minmax(110px, 0.9fr) minmax(80px, 0.8fr) auto;
    gap: 14px;
    align-items: center;
    padding: 14px 4px; border-bottom: 1px solid var(--line);
  }
  .user-row:last-child { border-bottom: 0; }
  .u-name { font-weight: 600; color: var(--ink); font-size: 15px; }
  .u-email { font-size: 13px; color: var(--ink-soft); font-family: 'JetBrains Mono', monospace; }
  .u-role {
    display: inline-block; padding: 3px 10px; border-radius: 3px;
    font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700;
    letter-spacing: 0.14em; text-transform: uppercase;
  }
  .u-role.super_admin { background: color-mix(in srgb, var(--teal) 10%, transparent); color: var(--teal); }
  .u-role.clerk       { background: color-mix(in srgb, var(--brass) 12%, transparent); color: var(--brass); }
  .u-role.sabbath     { background: color-mix(in srgb, var(--ink) 6%, transparent); color: var(--ink-soft); }
  .u-pin-status {
    font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 600;
    letter-spacing: 0.14em; text-transform: uppercase;
  }
  .u-pin-status.has  { color: var(--teal); }
  .u-pin-status.none { color: var(--ink-soft); opacity: 0.6; }

  .pin-form { display: flex; gap: 6px; align-items: center; }
  .pin-form input {
    font: inherit; font-family: 'JetBrains Mono', monospace; font-size: 14px;
    width: 80px; padding: 7px 10px; border: 1px solid var(--line); border-radius: 4px;
    background: #fff; color: var(--ink); text-align: center; letter-spacing: 4px;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .pin-form input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }
  .pin-form button {
    background: transparent; color: var(--ink-soft); border: 1px solid var(--line);
    padding: 7px 12px; border-radius: 4px; cursor: pointer;
    font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700;
    letter-spacing: 0.16em; text-transform: uppercase;
    transition: all 0.15s;
  }
  .pin-form button:hover { color: var(--teal); border-color: var(--teal); }
  .pin-form button.clear { color: #c0392b; border-color: rgba(192,57,43,0.3); }
  .pin-form button.clear:hover { background: #c0392b; color: #fff; border-color: #c0392b; }

  /* User-list mobile reflow */
  @media (max-width: 720px) {
    .user-row {
      grid-template-columns: 1fr auto;
      grid-template-areas:
        "name role"
        "email pin-status"
        "pin-form pin-form";
      gap: 6px 12px;
      padding: 18px 4px;
    }
    .user-row > div:nth-child(1) { grid-area: name; }
    .user-row > div:nth-child(2) { grid-area: email; font-size: 12px; }
    .user-row > div:nth-child(3) { grid-area: role; justify-self: end; }
    .user-row > div:nth-child(4) { grid-area: pin-status; justify-self: end; font-size: 9px; }
    .user-row .pin-form { grid-area: pin-form; margin-top: 8px; }
    .user-row .pin-form input { width: 100%; max-width: 140px; }
  }
</style>
@include('admin.partials._typography')
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.hub') }}">← Admin</a>
  <span class="meta">admin · users</span>
</header>

<main>
  <h1>Users.</h1>
  <p class="lede">Add anyone helping run things — clerks, elders, or members who just want to view the bulletin. Hand them a PIN and they sign in with their first name + PIN.</p>

  @if (session('status'))
    <div class="flash">{{ session('status') }}</div>
  @endif

  @if ($errors->any())
    <div class="flash" style="background:rgba(192,57,43,0.08); border-left-color:#c0392b;">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('admin.users.store') }}" class="add-form">
    @csrf
    <div class="row">
      <label class="lbl" for="name">First name</label>
      <input id="name" type="text" name="name" required maxlength="80" placeholder="Glenda" value="{{ old('name') }}">
    </div>
    <div class="row">
      <label class="lbl" for="email">Email (optional)</label>
      <input id="email" type="email" name="email" maxlength="120" placeholder="glenda@example.com" value="{{ old('email') }}">
    </div>
    <div class="row">
      <label class="lbl" for="role">Role</label>
      <select id="role" name="role" required>
        <option value="sabbath" {{ old('role') === 'sabbath' ? 'selected' : '' }}>Sabbath (member)</option>
        <option value="clerk"   {{ old('role') === 'clerk' ? 'selected' : '' }}>Clerk</option>
        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super admin</option>
      </select>
    </div>
    <div class="row">
      <label class="lbl" for="pin">PIN (optional)</label>
      <input id="pin" type="tel" name="pin" inputmode="numeric" pattern="\d{4,8}" minlength="4" maxlength="8" placeholder="8581" value="{{ old('pin') }}">
    </div>
    <button type="submit">Add →</button>
  </form>

  <section class="users-section">
    <div class="section-label">{{ $users->count() }} users</div>
    @foreach ($users as $u)
      <div class="user-row">
        <div>
          <div class="u-name">{{ $u->name }}</div>
        </div>
        <div class="u-email">{{ $u->email ?? '—' }}</div>
        <div><span class="u-role {{ $u->role }}">{{ str_replace('_', ' ', $u->role) }}</span></div>
        <div>
          <span class="u-pin-status {{ $u->pin_hash ? 'has' : 'none' }}">
            {{ $u->pin_hash ? '✓ has PIN' : 'no PIN' }}
          </span>
        </div>
        <form method="POST" action="{{ route('admin.users.pin', $u) }}" class="pin-form">
          @csrf
          @method('PATCH')
          <input type="tel" name="pin" inputmode="numeric" pattern="\d{4,8}" minlength="4" maxlength="8" placeholder="••••" required>
          <button type="submit">{{ $u->pin_hash ? 'Reset' : 'Set' }}</button>
          @if ($u->pin_hash)
            @if ($u->id !== auth()->id())
              <button type="button" class="clear"
                onclick="event.preventDefault(); window.shConfirm('Clear {{ $u->name }}\'s PIN?', {danger:true}).then(function(o){ if(o){ document.getElementById('clear-{{ $u->id }}').submit(); } });">×</button>
              <form id="clear-{{ $u->id }}" method="POST" action="{{ route('admin.users.pin.clear', $u) }}" style="display:none;">@csrf @method('DELETE')</form>
            @endif
          @endif
        </form>
      </div>
    @endforeach
  </section>
</main>
('partials._confirm')

</body>
</html>
