<!doctype html>
<html lang="en">
<head>
@include('admin.peace-news._head')
<title>Prayer team · Admin</title>
<style>
  .grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 0 12px; }
  @media (max-width: 560px) { .grid { grid-template-columns: minmax(0, 1fr); } }
  select.field { appearance: auto; }
  .who { font-size: 16px; font-weight: 600; }
  .tag { display: inline-block; margin-left: 6px; padding: 1px 8px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #f3efe6; color: #6b5a2e; vertical-align: 2px; }
  .tag.off { background: #eceef2; color: #556; }
  .acts { display: flex; flex-wrap: wrap; gap: 8px; }
  .acts .btn { min-height: 36px; padding: 0 12px; font-size: 13px; }
  .check { display: flex; gap: 8px; align-items: center; margin-top: 14px; font-size: 15px; }
  .check input { width: 20px; height: 20px; }
  .err { background: #fef0ed; color: #8a2a10; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; }
</style>
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')
<div class="top"><a href="{{ route('admin.hub') }}">‹ Admin</a></div>
<main>
  @if (session('ok'))<div class="ok">{{ session('ok') }}</div>@endif
  @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
  <h1>Prayer team</h1>
  <p class="lede">Everyone here gets a text when a prayer request comes in, and signs in with a link texted to their phone. Nobody else can see the list. <a href="{{ route('intercessors.signIn') }}">The team's page ›</a></p>
  @unless ($canText)<div class="err">Texting is not set up on this site, so sign-in links and alerts cannot go out.</div>@endunless

  <form method="post" action="{{ route('admin.prayer-team.store') }}" class="card">
    @csrf
    <h2 style="margin-top: 0;">Add someone</h2>
    <div class="grid">
      <div><label for="pt-name">Name</label><input id="pt-name" name="name" class="field" maxlength="80" required value="{{ old('name') }}" autocomplete="off"></div>
      <div><label for="pt-phone">Mobile number</label><input id="pt-phone" name="phone" type="tel" inputmode="tel" class="field" maxlength="30" required value="{{ old('phone') }}" autocomplete="off"></div>
    </div>
    <label for="pt-role">Role</label>
    <select id="pt-role" name="role" class="field"><option value="regular">Team member</option><option value="head" @selected(old('role') === 'head')>Team lead</option></select>
    <label class="check"><input type="checkbox" name="text_now" value="1" checked> Text them a welcome and a sign-in link now</label>
    <div style="margin-top: 16px;"><button class="btn" type="submit">Add to the team</button></div>
  </form>

  <h2>On the team ({{ $team->where('active', true)->count() }})</h2>
  @forelse ($team as $i)
    <div class="card">
      <div class="row">
        <div>
          <span class="who">{{ $i->name }}</span>
          @if ($i->isHead())<span class="tag">Team lead</span>@endif
          @unless ($i->active)<span class="tag off">Paused</span>@endunless
          <div class="muted" style="margin-top: 4px;">{{ $i->phone }} · {{ $i->last_seen_at ? 'Last signed in ' . $i->last_seen_at->diffForHumans() : 'Not signed in yet' }}</div>
        </div>
        <form method="post" action="{{ route('admin.prayer-team.update', $i) }}" class="acts">
          @csrf
          @if ($i->active)
            <button class="btn ghost" name="action" value="text">Text a sign-in link</button>
            <button class="btn ghost" name="action" value="role">{{ $i->isHead() ? 'Make member' : 'Make lead' }}</button>
            <button class="btn ghost" name="action" value="pause">Pause</button>
          @else
            <button class="btn ghost" name="action" value="resume">Put back on the team</button>
          @endif
        </form>
      </div>
    </div>
  @empty
    <p class="muted">Nobody yet. Add the first person above.</p>
  @endforelse
</main>
</body>
</html>
