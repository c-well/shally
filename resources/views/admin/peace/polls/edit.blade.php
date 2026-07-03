<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $poll->exists ? 'Edit poll' : 'New poll' }} · The Church of Peace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  main { max-width: 760px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(26px, 4vw, 34px); font-weight: 500; margin-bottom: 8px; }
  .lede { font-size: 14px; color: var(--ink-soft); margin-bottom: 24px; }
  .status { padding: 14px 18px; background: color-mix(in srgb, var(--teal) 8%, transparent); border-left: 4px solid var(--teal); border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; }
  label { display: block; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; margin-top: 22px; }
  input[type=text], textarea, select {
    width: 100%; padding: 10px 12px;
    background: #fff; color: var(--ink);
    border: 1px solid var(--line); border-radius: 4px;
    font-family: inherit; font-size: 14px;
  }
  textarea { resize: vertical; min-height: 110px; }
  .row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
  .checkbox { margin-top: 18px; display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--ink); }
  .checkbox input { width: 18px; height: 18px; accent-color: var(--teal); }
  .options-list { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; }
  .opt-row { display: grid; grid-template-columns: 1fr auto auto; gap: 8px; align-items: center; }
  .opt-row input[type=text] { margin: 0; }
  .opt-canon { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--ink-soft); white-space: nowrap; padding: 0 10px; }
  .opt-canon input { width: 16px; height: 16px; accent-color: var(--teal); }
  .opt-remove { padding: 6px 10px; background: transparent; color: var(--ink-soft); border: 1px solid var(--line); border-radius: 4px; cursor: pointer; font-size: 12px; }
  .opt-remove:hover { background: rgba(168,42,31,0.08); color: #a82a1f; border-color: rgba(168,42,31,0.3); }
  .opt-add { margin-top: 10px; padding: 7px 14px; background: transparent; color: var(--teal); border: 1px dashed var(--teal); border-radius: 4px; cursor: pointer; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; }
  .opt-resp { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--ink-soft); margin-left: auto; padding-right: 6px; }
  .actions { margin-top: 32px; display: flex; gap: 12px; }
  .btn { padding: 10px 20px; background: var(--teal); color: #fff; border: 0; border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; cursor: pointer; text-decoration: none; }
  .btn:hover { background: var(--teal-dark); }
  .btn.danger { background: rgba(168,42,31,0.85); }
  .btn.danger:hover { background: #a82a1f; }
  .stats { padding: 16px; background: color-mix(in srgb, var(--teal) 5%, transparent); border-radius: 6px; margin-top: 20px; font-size: 13px; color: var(--ink-soft); }
  .errors { padding: 12px 16px; background: rgba(168,42,31,0.08); border-left: 4px solid #a82a1f; border-radius: 0 4px 4px 0; margin-bottom: 16px; }
  .errors ul { padding-left: 18px; font-size: 13px; color: #a82a1f; }
  .help { font-size: 12px; color: var(--ink-soft); margin-top: 4px; }
</style>
@include('partials.theme-vars')
@include('admin.partials._typography')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.peace.polls.index') }}">@include('partials._arl') Polls</a>
  <span style="font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:0.14em;color:var(--ink-soft);opacity:0.65;">{{ $poll->exists ? 'edit poll #'.$poll->id : 'new poll' }}</span>
</header>

<main>
  <h1>{{ $poll->exists ? 'Edit poll' : 'New poll' }}</h1>
  <p class="lede">Frame the question pastorally — not to test, but to make a reader feel seen by scripture.</p>

  @if (session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="errors"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  <form method="POST" action="{{ $poll->exists ? route('admin.peace.polls.update', $poll) : route('admin.peace.polls.store') }}">
    @csrf

    <label>Question</label>
    <input type="text" name="question" value="{{ old('question', $poll->question) }}" required maxlength="500" placeholder="When David met Goliath, what made him bold?">

    <label>Scripture reference (shown after answer)</label>
    <input type="text" name="scripture_ref" value="{{ old('scripture_ref', $poll->scripture_ref) }}" maxlength="100" placeholder="1 Samuel 17:34-37">
    <p class="help">Auto-tooltips on the public page if it matches a Bible reference pattern.</p>

    <label>Scripture explainer (shown after answer, regardless of choice)</label>
    <textarea name="scripture_explainer" required maxlength="5000" placeholder="Before David ever faced Goliath, he had killed a lion and a bear...">{{ old('scripture_explainer', $poll->scripture_explainer) }}</textarea>

    <div class="row">
      <div>
        <label>Topic (auto-shows on matching sermons)</label>
        <select name="topic_id">
          <option value="">— none —</option>
          @foreach($topics as $t)
            <option value="{{ $t->id }}" @if(old('topic_id', $poll->topic_id) == $t->id) selected @endif>{{ $t->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label>Sermon (binds to one specific sermon)</label>
        <select name="sermon_id">
          <option value="">— none —</option>
          @foreach($sermons as $s)
            <option value="{{ $s->id }}" @if(old('sermon_id', $poll->sermon_id) == $s->id) selected @endif>{{ \Illuminate\Support\Str::limit($s->title, 38) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label>Display priority</label>
        <input type="text" name="display_priority" value="{{ old('display_priority', $poll->display_priority ?? 0) }}" placeholder="0">
        <p class="help">Higher = preferred when multiple polls match.</p>
      </div>
    </div>

    <label class="checkbox">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" value="1" @if(old('is_active', $poll->is_active)) checked @endif>
      Active (show on public pages)
    </label>

    <label>Options</label>
    <p class="help">2-6 options. Mark exactly ONE as "scripture's answer" — the canonical one. Order matters.</p>
    <div class="options-list" id="options-list">
      @php
        $existing = $poll->exists ? $poll->options : collect();
        $oldOpts = old('options', $existing->pluck('option_text')->all());
        $oldCanonIdx = old('canonical_index', $existing->search(fn($o) => $o->is_canonical));
        // Pad to at least 4 rows for new polls
        while (count($oldOpts) < 4) $oldOpts[] = '';
      @endphp
      @foreach($oldOpts as $i => $txt)
        <div class="opt-row">
          <input type="text" name="options[]" value="{{ $txt }}" maxlength="300" placeholder="Option {{ $i + 1 }}">
          <label class="opt-canon">
            <input type="radio" name="canonical_index" value="{{ $i }}" @if(($oldCanonIdx !== false && (int)$oldCanonIdx === $i)) checked @endif>
            scripture's answer
          </label>
          @if($poll->exists && isset($existing[$i]))
            <span class="opt-resp">{{ $existing[$i]->response_count }} votes</span>
          @endif
          <button type="button" class="opt-remove" onclick="this.closest('.opt-row').remove()">✕</button>
        </div>
      @endforeach
    </div>
    <button type="button" class="opt-add" id="opt-add">+ Add option</button>

    @if($poll->exists)
      <div class="stats">
        Total responses: {{ $poll->responses->count() }}
        @if($poll->responses->count())
          · last response: {{ $poll->responses->sortByDesc('created_at')->first()->created_at->diffForHumans() }}
        @endif
      </div>
    @endif

    <div class="actions">
      <button type="submit" class="btn">{{ $poll->exists ? 'Save' : 'Create poll' }}</button>
      <a href="{{ route('admin.peace.polls.index') }}" class="btn" style="background:transparent;color:var(--ink-soft);border:1px solid var(--line);">Cancel</a>
    </div>
  </form>

  @if($poll->exists)
    <form method="POST" action="{{ route('admin.peace.polls.destroy', $poll) }}" data-confirm="Delete this poll? All responses will be lost." style="margin-top: 60px;">
      @csrf @method('DELETE')
      <button type="submit" class="btn danger">Delete poll</button>
    </form>
  @endif
</main>

<script>
  let optCount = document.querySelectorAll('#options-list .opt-row').length;
  document.getElementById('opt-add').addEventListener('click', () => {
    if (document.querySelectorAll('#options-list .opt-row').length >= 6) {
      shToast('Max 6 options.'); return;
    }
    const row = document.createElement('div');
    row.className = 'opt-row';
    row.innerHTML = `
      <input type="text" name="options[]" maxlength="300" placeholder="Option ${++optCount}">
      <label class="opt-canon">
        <input type="radio" name="canonical_index" value="${optCount - 1}">
        scripture's answer
      </label>
      <button type="button" class="opt-remove" onclick="this.closest('.opt-row').remove()">✕</button>
    `;
    document.getElementById('options-list').appendChild(row);
  });
</script>
@include('partials._confirm')
</body>
</html>
