<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Share · Finding Peace</title>
<meta name="robots" content="noindex, nofollow">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Poppins', system-ui, sans-serif; background: var(--bg); color: var(--ink); line-height: 1.65; min-height: 100dvh; }
  main { max-width: 680px; margin: 0 auto; padding: 4rem 1.5rem 8rem; }
  .breadcrumb { font-family: 'Instrument Sans', sans-serif; font-size: 0.7rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-faint); font-weight: 600; margin-bottom: 0.5rem; }
  .breadcrumb a { color: var(--brass); text-decoration: none; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 5vw, 2.6rem); font-weight: 500; line-height: 1.1; margin-bottom: 0.4rem; }
  .lede { color: var(--ink-soft); font-size: 1rem; margin-bottom: 2rem; }

  .flash { padding: 14px 18px; background: color-mix(in srgb, var(--teal) 10%, transparent); border-left: 3px solid var(--brass); border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; color: var(--ink); }

  .tabs { display: flex; gap: 0; margin-bottom: 1.8rem; border-bottom: 1px solid var(--line); }
  .tab-btn {
    background: transparent; border: 0; padding: 0.85rem 1.4rem;
    font-family: 'Instrument Sans', sans-serif; font-size: 0.78rem;
    letter-spacing: 0.16em; text-transform: uppercase; font-weight: 600;
    color: var(--ink-soft); cursor: pointer;
    border-bottom: 2px solid transparent; margin-bottom: -1px;
    transition: color 0.15s, border-color 0.15s;
  }
  .tab-btn:hover { color: var(--brass); }
  .tab-btn.active { color: var(--brass); border-bottom-color: var(--brass); }

  .tab-pane { display: none; }
  .tab-pane.active { display: block; }

  label { display: block; font-size: 0.78rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 0.4rem; font-weight: 500; }
  textarea, select {
    width: 100%; padding: 0.85rem 1rem;
    background: var(--bg-elevated); color: var(--ink);
    border: 1px solid var(--line); border-radius: 6px;
    font-family: inherit; font-size: 0.96rem;
    margin-bottom: 1.2rem;
    transition: border-color 0.15s;
  }
  textarea { min-height: 160px; resize: vertical; line-height: 1.55; }
  textarea:focus, select:focus { outline: none; border-color: var(--brass); }
  .char-count { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--ink-faint); text-align: right; margin-top: -1rem; margin-bottom: 1rem; }

  .attr-options { display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1.4rem; }
  .attr-option {
    display: flex; align-items: flex-start; gap: 0.65rem;
    padding: 0.85rem 1rem;
    background: var(--bg-elevated);
    border: 1px solid var(--line); border-radius: 6px;
    cursor: pointer; transition: border-color 0.15s, background 0.15s;
  }
  .attr-option:hover { border-color: var(--brass); }
  .attr-option input[type=radio] { margin-top: 4px; accent-color: var(--brass); flex-shrink: 0; }
  .attr-option .opt-text { flex: 1; min-width: 0; }
  .attr-option .opt-title { font-weight: 500; font-size: 0.94rem; color: var(--ink); }
  .attr-option .opt-sub { font-size: 0.8rem; color: var(--ink-soft); margin-top: 2px; line-height: 1.4; }
  .attr-display-input { display: none; margin-top: 0.6rem; }
  .attr-option.selected .attr-display-input { display: block; }
  .attr-display-input input {
    width: 100%; padding: 0.55rem 0.8rem;
    background: var(--bg); border: 1px solid var(--line); border-radius: 4px;
    font-family: inherit; font-size: 0.9rem; color: var(--ink);
  }

  .hp { position: absolute; left: -10000px; opacity: 0; pointer-events: none; }

  .actions { display: flex; gap: 1rem; align-items: center; }
  button.submit {
    padding: 0.85rem 1.6rem;
    background: var(--brass); color: var(--bg);
    border: 0; border-radius: 6px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 0.82rem; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s;
  }
  button.submit:hover { background: var(--brass-bright); }
  .cancel-link { font-size: 0.85rem; color: var(--ink-soft); text-decoration: none; }
  .cancel-link:hover { color: var(--brass); }

  .help { font-size: 0.85rem; color: var(--ink-soft); line-height: 1.55; margin-bottom: 1.5rem; padding: 0.9rem 1.1rem; background: var(--bg-elevated); border-radius: 6px; border-left: 3px solid var(--brass); }

  .recent { margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--line); }
  .recent h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; font-weight: 500; margin-bottom: 1rem; }
  .recent-item { padding: 0.85rem 0; border-bottom: 1px dashed var(--line); }
  .recent-item:last-child { border-bottom: none; }
  .recent-meta { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--ink-faint); letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 0.3rem; }
  .recent-body { font-size: 0.9rem; color: var(--ink-soft); line-height: 1.5; }
  .stat-pill { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 0.65rem; letter-spacing: 0.14em; text-transform: uppercase; font-weight: 600; margin-left: 0.5rem; }
  .stat-pill.pending  { background: color-mix(in srgb, var(--brass) 15%, transparent); color: var(--brass); }
  .stat-pill.approved { background: rgba(45,134,89,0.12); color: #2d8659; }
  .stat-pill.archived { background: color-mix(in srgb, var(--ink) 6%, transparent); color: var(--ink-faint); }
  .stat-pill.replied  { background: color-mix(in srgb, var(--teal) 10%, transparent); color: var(--teal); }
</style>
@include('partials.find-peace-vars')
</head>
<body>
<main>
  <div class="breadcrumb"><a href="{{ route('find-peace.index') }}">Finding Peace</a> · Share</div>
  <h1>Add your voice.</h1>
  <p class="lede">Signed in as <strong>{{ $seeker->email }}</strong>. Ask the question you couldn't find an answer to, or share what you've walked through. A pastor will read every word.</p>

  @if (session('peace_flash'))
    <div class="flash">{{ session('peace_flash') }}</div>
  @endif

  <div class="tabs">
    <button type="button" class="tab-btn active" data-tab="question">Ask a question</button>
    <button type="button" class="tab-btn"        data-tab="experience">Share an experience</button>
  </div>

  <!-- ── QUESTION TAB ─────────────────────────────────────────── -->
  <div class="tab-pane active" data-tab-pane="question">
    <div class="help">
      Got a question that didn't quite get answered here? Ask it. A pastor reads every one. You may get a personal reply at <strong>{{ $seeker->email }}</strong>, and your question may inspire a Q&amp;A on a future sermon page.
    </div>
    <form method="POST" action="{{ route('peace.share.submit') }}" id="form-question">
      @csrf
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
      <input type="hidden" name="form_opened_at" id="opened-q" value="0">
      <input type="hidden" name="type" value="question">
      <input type="hidden" name="attribution" value="anonymous">

      <label>Your question</label>
      <textarea name="body" required minlength="20" maxlength="3000" placeholder="What's been on your mind that you haven't been able to put down? Take your time. Write it like you'd say it." oninput="charCount(this, 'cc-q')"></textarea>
      <div class="char-count" id="cc-q">0 / 3000</div>

      <label>Related sermon (optional)</label>
      <select name="related_sermon_id">
        <option value="">— None / general question —</option>
        @foreach ($sermons as $s)
          <option value="{{ $s->id }}">{{ optional($s->sermon_date)->format('M j Y') }} · {{ $s->title }}</option>
        @endforeach
      </select>

      <div class="actions">
        <button type="submit" class="submit">Send to pastor →</button>
        <a href="{{ route('find-peace.index') }}" class="cancel-link">Never mind</a>
      </div>
    </form>
  </div>

  <!-- ── EXPERIENCE TAB ───────────────────────────────────────── -->
  <div class="tab-pane" data-tab-pane="experience">
    <div class="help">
      What did you walk through? What spoke to you? What did God do? Your story may be shared (with your permission, in the way you choose) on our wall of shared experiences. Every story is read by a clerk before anything goes public.
    </div>
    <form method="POST" action="{{ route('peace.share.submit') }}" id="form-experience">
      @csrf
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off">
      <input type="hidden" name="form_opened_at" id="opened-e" value="0">
      <input type="hidden" name="type" value="experience">

      <label>Your experience</label>
      <textarea name="body" required minlength="20" maxlength="3000" placeholder="Tell it the way you remember it. The hard parts, the holy parts. We're listening." oninput="charCount(this, 'cc-e')"></textarea>
      <div class="char-count" id="cc-e">0 / 3000</div>

      <label>Related sermon or topic (optional)</label>
      <select name="related_topic_id">
        <option value="">— None / general experience —</option>
        @foreach ($topics as $t)
          <option value="{{ $t->id }}">{{ ucfirst($t->name) }}</option>
        @endforeach
      </select>

      <label>How should we share it (if approved)?</label>
      <div class="attr-options">
        <label class="attr-option selected">
          <input type="radio" name="attribution" value="anonymous" checked>
          <span class="opt-text">
            <span class="opt-title">Anonymously</span>
            <span class="opt-sub">Show as "From a fellow seeker." Your email is never shown.</span>
          </span>
        </label>
        <label class="attr-option">
          <input type="radio" name="attribution" value="first_name_city">
          <span class="opt-text">
            <span class="opt-title">With my first name + city</span>
            <span class="opt-sub">Optional — feels more personal but still private. Last name + email stay private.</span>
            <div class="attr-display-input">
              <input type="text" name="attribution_display" placeholder="e.g. Karlon, NY" maxlength="100">
            </div>
          </span>
        </label>
      </div>

      <div class="actions">
        <button type="submit" class="submit">Send →</button>
        <a href="{{ route('find-peace.index') }}" class="cancel-link">Never mind</a>
      </div>
    </form>
  </div>

  @if ($recent->isNotEmpty())
    <div class="recent">
      <h2>Your previous submissions</h2>
      @foreach ($recent as $r)
        <div class="recent-item">
          <div class="recent-meta">{{ ucfirst($r->type) }} · {{ $r->created_at->diffForHumans() }}<span class="stat-pill {{ $r->status }}">{{ $r->status }}</span></div>
          <div class="recent-body">{{ \Illuminate\Support\Str::limit($r->body, 200) }}</div>
        </div>
      @endforeach
    </div>
  @endif
</main>

<script>
  // Tabs
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tab;
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === target));
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.toggle('active', p.dataset.tabPane === target));
    });
  });
  // Stamp form-opened-at for time-trap
  document.getElementById('opened-q').value = Date.now();
  document.getElementById('opened-e').value = Date.now();
  // Char count
  function charCount(el, target) {
    const len = el.value.length;
    document.getElementById(target).textContent = len + ' / 3000';
  }
  // Attribution radio reveal
  document.querySelectorAll('input[name="attribution"]').forEach(r => {
    r.addEventListener('change', () => {
      document.querySelectorAll('.attr-option').forEach(opt => {
        opt.classList.toggle('selected', opt.contains(opt.querySelector('input[type=radio]:checked')));
      });
    });
  });
</script>
</body>
</html>
