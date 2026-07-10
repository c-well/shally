<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Guests — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment, #fefcef); color: var(--ink, #1a2332); font-family: 'Instrument Sans', system-ui, sans-serif; }
  .top { padding: 24px 28px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line, rgba(26,35,50,.12)); }
  .top a { font-size: 13.5px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft, #4a5568); padding: 10px 12px; margin: -10px -12px; }
  .top a:hover { color: var(--teal, #03617A); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 12.5px; color: var(--ink-soft); opacity: .65; }
  main { max-width: 780px; margin: 0 auto; padding: 34px 22px 120px; }
  h1 { font-family: 'JetBrains Mono', monospace; font-size: 24px; font-weight: 500; }
  .lede { color: var(--ink-soft); font-size: 14px; margin-top: 8px; line-height: 1.6; }
  .due-banner { margin-top: 18px; background: color-mix(in srgb, var(--teal, #03617A) 8%, #fff); border: 1px solid color-mix(in srgb, var(--teal, #03617A) 25%, transparent); border-radius: 10px; padding: 13px 16px; font-size: 13.5px; font-weight: 600; color: var(--teal, #03617A); }
  .guest { background: #fff; border: 1px solid var(--line, rgba(26,35,50,.12)); border-radius: 12px; padding: 16px 18px; margin-top: 16px; }
  .g-head { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap; }
  .g-name { font-size: 17px; font-weight: 700; }
  .g-meta { font-size: 12px; color: var(--ink-soft); }
  .g-tags { margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap; }
  .g-tag { font: 700 9.5px 'Instrument Sans'; letter-spacing: .1em; text-transform: uppercase; color: var(--teal, #03617A); border: 1px solid color-mix(in srgb, var(--teal, #03617A) 30%, transparent); border-radius: 5px; padding: 3px 8px; }
  .fu { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-top: 1px dashed var(--line, rgba(26,35,50,.12)); margin-top: 9px; flex-wrap: wrap; }
  .fu-kind { font: 700 10px 'Instrument Sans'; letter-spacing: .12em; text-transform: uppercase; flex-shrink: 0; width: 84px; }
  .fu-kind.thanks { color: var(--teal, #03617A); } .fu-kind.questions { color: #8a6c26; } .fu-kind.birthday { color: #1f6843; } .fu-kind.custom { color: #4a5568; }
  .fu-when { font: 500 11px 'JetBrains Mono', monospace; color: var(--ink-soft); flex-shrink: 0; }
  .fu-status { font: 700 9.5px 'Instrument Sans'; letter-spacing: .1em; border-radius: 5px; padding: 3px 8px; flex-shrink: 0; }
  .fu-status.pending { background: color-mix(in srgb, #8a6c26 12%, #fff); color: #8a6c26; }
  .fu-status.sent { background: color-mix(in srgb, #1f6843 12%, #fff); color: #1f6843; }
  .fu-status.failed, .fu-status.skipped { background: color-mix(in srgb, #a33d3d 10%, #fff); color: #a33d3d; }
  .fu-body { flex: 1 1 100%; }
  .fu-body textarea { width: 100%; font: 400 13px 'Instrument Sans'; line-height: 1.55; border: 1px solid var(--line); border-radius: 8px; padding: 10px 12px; background: var(--parchment, #fefcef); resize: vertical; min-height: 54px; }
  .fu-body textarea:focus { outline: none; border-color: var(--teal); background: #fff; }
  .fu-body .hint { font-size: 10.5px; color: var(--ink-faint, #6b7280); margin-top: 4px; }
  .fu-act { display: flex; gap: 6px; }
  .fu-btn { font: 700 9.5px 'Instrument Sans'; letter-spacing: .1em; text-transform: uppercase; border: 1px solid var(--line); background: #fff; color: var(--teal); border-radius: 6px; padding: 7px 11px; cursor: pointer; }
  .fu-btn:hover { border-color: var(--teal); }
  .fu-btn.warn { color: #a33d3d; }
  .empty { text-align: center; color: var(--ink-soft); border: 1px dashed var(--line); border-radius: 12px; padding: 44px 20px; margin-top: 24px; font-size: 14px; line-height: 1.7; }
  .pip { position: fixed; bottom: 22px; left: 50%; transform: translateX(-50%); font-size: 12px; font-weight: 600; color: #fff; background: var(--teal, #03617A); padding: 8px 16px; border-radius: 8px; opacity: 0; transition: opacity .2s; pointer-events: none; }
  .pip.show { opacity: 1; } .pip.err { background: #a33d3d; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<header class="top">
  <a href="{{ route('admin.hub') }}">@include('partials._arl') Admin</a>
  <span class="meta">guests · follow-up engine</span>
</header>
<main>
  <h1>Guests.</h1>
  <p class="lede">Everyone who filled the connect card at <b>/connect</b>. The engine sends the day-1 thanks, day-3 questions, and birthday wishes at 10 AM automatically — edit any pending message below to make it personal before it goes.</p>
  @php $dueCount = $guests->flatMap->followups->where('status','pending')->filter(fn($f) => $f->due_on->lte(today()))->count(); @endphp
  @if ($dueCount)
    <div class="due-banner">⏰ {{ $dueCount }} follow-up{{ $dueCount === 1 ? '' : 's' }} due — going out at the next 10 AM run (or send now below).</div>
  @endif

  @forelse ($guests as $g)
    <div class="guest">
      <div class="g-head">
        <span class="g-name">{{ $g->name }}</span>
        <span class="g-meta">visited {{ $g->visited_on->format('M j') }} · {{ $g->phone ?: '' }}{{ $g->phone && $g->email ? ' · ' : '' }}{{ $g->email ?: '' }}</span>
      </div>
      <div class="g-tags">
        @if ($g->wants_updates)<span class="g-tag">wants updates</span>@endif
        @if ($g->wants_volunteer)<span class="g-tag">volunteer ✋</span>@endif
        @if ($g->birthday_month)<span class="g-tag">🎂 {{ ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][$g->birthday_month] }} {{ $g->birthday_day }}</span>@endif
      </div>
      @foreach ($g->followups as $f)
        <div class="fu" data-id="{{ $f->id }}">
          <span class="fu-kind {{ $f->kind }}">{{ $f->kind }}</span>
          <span class="fu-when">{{ $f->due_on->format('M j') }}</span>
          <span class="fu-status {{ $f->status }}">{{ $f->status }}{{ $f->channel ? ' · ' . $f->channel : '' }}</span>
          @if ($f->status === 'pending')
            <span class="fu-act">
              <button type="button" class="fu-btn" data-send data-url="{{ route('admin.guests.followups.send', $f) }}">Send now</button>
              <button type="button" class="fu-btn warn" data-skip data-url="{{ route('admin.guests.followups.update', $f) }}">Skip</button>
            </span>
            <span class="fu-body">
              <textarea data-body data-url="{{ route('admin.guests.followups.update', $f) }}"
                        placeholder="{{ $f->defaultBody() }}">{{ $f->body }}</textarea>
              <div class="hint">Empty = the warm default above goes out. Type here to personalize.</div>
            </span>
          @endif
        </div>
      @endforeach
    </div>
  @empty
    <div class="empty">No guests yet.<br>Print a QR to <b>thechurchofpeace.org/connect</b> for the pews and the foyer — the engine handles the rest.</div>
  @endforelse
</main>
<div class="pip" id="pip">Saved</div>
@include('partials._confirm')
<script>
(function () {
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  const pip = document.getElementById('pip');
  const timers = {};
  function pipMsg(m, err) { pip.textContent = m; pip.classList.toggle('err', !!err); pip.classList.add('show'); clearTimeout(pip._t); pip._t = setTimeout(() => pip.classList.remove('show'), err ? 2800 : 1100); }
  async function api(method, url, body) {
    const r = await fetch(url, { method, headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body || {}) });
    return r.json();
  }
  document.addEventListener('input', e => {
    if (!e.target.matches('[data-body]')) return;
    const id = e.target.closest('.fu').dataset.id;
    clearTimeout(timers[id]);
    timers[id] = setTimeout(async () => {
      const d = await api('PATCH', e.target.dataset.url, { body: e.target.value });
      d.ok ? pipMsg('Saved') : pipMsg('Not saved — try again', true);
    }, 500);
  });
  document.addEventListener('click', async e => {
    if (e.target.matches('[data-skip]')) {
      if (!await window.shConfirm('Skip this follow-up?')) return;
      const d = await api('PATCH', e.target.dataset.url, { status: 'skipped' });
      if (d.ok) location.reload();
    }
    if (e.target.matches('[data-send]')) {
      if (!await window.shConfirm('Send this follow-up right now?', { okLabel: 'Send' })) return;
      e.target.disabled = true;
      const d = await api('POST', e.target.dataset.url, {});
      d.ok ? location.reload() : (pipMsg(d.message || 'Send failed', true), e.target.disabled = false);
    }
  });
})();
</script>
</body>
</html>
