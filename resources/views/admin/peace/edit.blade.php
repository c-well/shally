<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
@include('partials.dark-mode')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $sermon->title }} · Peace Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  :root { --parchment:#fefcef; --ink:#1a2332; --ink-soft:#334455; --teal:#03617A; --teal-dark:#024357; --brass:#b08d3c; --line:rgba(26,35,50,0.10); --warn:#a82a1f; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; }
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
  .top { padding: 22px 32px; display: flex; align-items: center; justify-content: space-between; }
  .top a { font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 600; letter-spacing: 0.18em; text-transform: uppercase; text-decoration: none; color: var(--ink-soft); }
  .top a:hover { color: var(--teal); }
  .top .meta { font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: var(--ink-soft); opacity: 0.65; }
  main { max-width: 880px; margin: 0 auto; padding: 0 32px 80px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(26px, 4vw, 36px); font-weight: 500; line-height: 1.05; margin-bottom: 4px; }
  .sub { font-size: 13px; color: var(--ink-soft); margin-bottom: 32px; }
  .sub a { color: var(--teal); text-decoration: none; }

  .status { padding: 14px 18px; background: rgba(45,134,89,0.10); border-left: 4px solid #2d8659; border-radius: 0 6px 6px 0; margin-bottom: 24px; font-size: 14px; }

  .card {
    background: #fff; border: 1px solid var(--line);
    border-radius: 6px; padding: 22px 26px; margin-bottom: 18px;
  }
  .card h2 {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 11px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase;
    color: var(--brass); margin-bottom: 16px;
  }

  .field { margin-bottom: 16px; }
  .field label { display: block; font-family: 'Instrument Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
  .field input[type=text], .field input[type=number], .field textarea {
    width: 100%; padding: 11px 14px;
    border: 1px solid var(--line); border-radius: 4px;
    background: #fff; color: var(--ink);
    font: inherit; font-size: 15px;
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .field textarea { min-height: 100px; resize: vertical; line-height: 1.55; font-family: 'Cormorant Garamond', serif; font-size: 16px; }
  .field input:focus, .field textarea:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(3,97,122,0.12); outline: none; }

  .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  @media (max-width: 600px) { .row2 { grid-template-columns: 1fr; } }

  .check-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; cursor: pointer; }
  .check-row input { width: 18px; height: 18px; accent-color: var(--teal); }
  .check-row .lbl-text { font-size: 14px; }
  .check-row small { display: block; font-size: 12px; color: var(--ink-soft); margin-top: 2px; }

  .qa-edit, .scr-edit { padding: 16px 0; border-top: 1px dashed var(--line); }
  .qa-edit:first-of-type, .scr-edit:first-of-type { border-top: 0; padding-top: 0; }

  button.primary, button.danger, button.ghost {
    font-family: 'Instrument Sans', sans-serif; font-size: 11px; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    padding: 9px 18px; border-radius: 4px; cursor: pointer;
    border: 1px solid var(--teal); background: var(--teal); color: #fff;
    transition: background 0.15s;
  }
  button.primary:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
  button.ghost { background: transparent; color: var(--teal); border-color: var(--teal); }
  button.ghost:hover { background: var(--teal); color: #fff; }
  button.danger { background: transparent; color: var(--warn); border-color: var(--warn); padding: 6px 12px; font-size: 10px; }
  button.danger:hover { background: var(--warn); color: #fff; }

  .ref-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; gap: 12px; }
  .ref-row .ref-label { font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--brass); letter-spacing: 0.16em; text-transform: uppercase; min-width: 120px; }
  .ref-row .ref-text { flex: 1; color: var(--ink-soft); font-family: 'Cormorant Garamond', serif; font-size: 14px; }

  .add-row { display: flex; gap: 8px; padding-top: 14px; border-top: 1px dashed var(--line); margin-top: 14px; }
  .add-row input { flex: 1; }

  .topic-input { display: block; width: 100%; }

  .public-link { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--teal); text-decoration: none; }
  .public-link:hover { text-decoration: underline; }
</style>
</head>
<body>

@include('partials.site-menu')

<header class="top">
  <a href="{{ route('admin.peace.index') }}">← Peace</a>
  <span class="meta">edit sermon</span>
</header>

<main>
  <h1>{{ $sermon->title }}</h1>
  <p class="sub">
    {{ strtoupper($sermon->sermon_date->format('M j Y')) }}
    @if($sermon->speaker) · {{ $sermon->speaker }} @endif
    @if($sermon->processing_status === 'published')
      · <a href="{{ route('find-peace.show', $sermon->slug) }}" target="_blank" class="public-link">View public page ↗</a>
    @endif
  </p>

  @if (session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif

  {{-- ── Audio trim — re-cut against the existing trimmed file ─────────── --}}
  @if($sermon->audio_url)
  @php
    $dh = !empty($audioDur) ? floor($audioDur/60).':'.str_pad($audioDur%60,2,'0',STR_PAD_LEFT) : '--';
  @endphp
  <details class="trim-panel" style="margin-bottom:28px;padding:18px 22px;background:rgba(3,97,122,0.04);border:1px solid var(--line);border-radius:6px;">
    <summary style="cursor:pointer;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:var(--teal);">
      Audio · current length {{ $dh }} — tighten boundaries ▾
    </summary>
    <p style="margin:14px 0 16px;font-size:13px;color:var(--ink-soft);">
      Offsets are relative to the <em>current</em> audio file (the one already trimmed by the pipeline). Use <strong>MM:SS</strong> format. End must be after start. A 3-second fade-out is always applied. To extend <em>past</em> the current end, leave it for Claude — that needs the full source.
    </p>
    <form method="POST" action="{{ route('admin.peace.trim', $sermon->slug) }}" onsubmit="return confirm('Re-trim this audio? The original boundaries will be lost (backup is kept server-side).');">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:14px;align-items:end;">
        <label style="display:block;">
          <span style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);font-weight:600;">Start (MM:SS)</span>
          <input type="text" name="start" pattern="[0-9]+:[0-5][0-9]" placeholder="0:00" required
                 style="width:100%;padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:4px;font-family:'JetBrains Mono',monospace;font-size:14px;">
        </label>
        <label style="display:block;">
          <span style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);font-weight:600;">End (MM:SS)</span>
          <input type="text" name="end" pattern="[0-9]+:[0-5][0-9]" placeholder="{{ $dh }}" required
                 style="width:100%;padding:8px 10px;background:#fff;border:1px solid var(--line);border-radius:4px;font-family:'JetBrains Mono',monospace;font-size:14px;">
        </label>
        <button type="submit" style="padding:9px 18px;background:var(--teal);color:#fff;border:0;border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;cursor:pointer;">Re-trim →</button>
      </div>
      <div style="margin-top:14px;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--ink);flex-wrap:wrap;">
        <span style="font-size:10px;letter-spacing:0.18em;text-transform:uppercase;color:var(--ink-soft);font-weight:600;">Compressor</span>
        <select name="compressor" style="padding:6px 10px;background:#fff;border:1px solid var(--line);border-radius:4px;font-family:inherit;font-size:13px;">
          <option value="none">None — preserve dynamics</option>
          <option value="simple">Simple — gentle level lift</option>
          <option value="fairchild" selected>Fairchild Vari-Mu — warmth, program-dependent</option>
          <option value="ssl">SSL 4000 G Bus — subtle glue (~2-4 dB GR)</option>
        </select>
        <button type="submit"
                formaction="{{ route('admin.peace.recompress', $sermon->slug) }}"
                formnovalidate
                onclick="return confirm('Re-encode the current audio with this compressor? Boundaries stay the same. Old file is replaced.');"
                style="margin-left:8px;padding:7px 14px;background:#fff;color:var(--teal);border:1px solid var(--teal);border-radius:4px;font-family:'Instrument Sans',sans-serif;font-size:10px;font-weight:600;letter-spacing:0.16em;text-transform:uppercase;cursor:pointer;">
          Apply compressor only →
        </button>
      </div>
      <p style="margin:10px 0 0;font-size:11px;color:var(--ink-soft);opacity:0.8;">Example: <code style="font-family:'JetBrains Mono',monospace;">5:15</code> → <code style="font-family:'JetBrains Mono',monospace;">35:50</code>. Result will be ~30:35 with a clean fade-out tail.</p>
    </form>
  </details>
  @endif

  {{-- ── Core fields ──────────────────────────────────────────── --}}
  <form method="POST" action="{{ route('admin.peace.update', $sermon->slug) }}">
    @csrf
    <div class="card">
      <h2>Sermon details</h2>

      <div class="field">
        <label>Title (public — seeker-friendly)</label>
        <input type="text" name="title" value="{{ old('title', $sermon->title) }}" required maxlength="255">
      </div>

      <div class="row2">
        <div class="field">
          <label>Speaker</label>
          <input type="text" name="speaker" value="{{ old('speaker', $sermon->speaker) }}" maxlength="255">
        </div>
        <div class="field">
          <label>Topics (comma-separated)</label>
          <input type="text" name="topics" class="topic-input" value="{{ old('topics', $sermon->topics->pluck('name')->implode(', ')) }}" placeholder="discouragement, prayer, doubt">
        </div>
      </div>

      <div class="field">
        <label>Heart line (1 sentence, the soul of the page)</label>
        <textarea name="heart_line" maxlength="1000" style="min-height: 70px;">{{ old('heart_line', $sermon->heart_line) }}</textarea>
      </div>

      <h2 style="margin-top: 24px;">Summary paragraphs</h2>
      @php $paras = old('summary_paragraphs', $sermon->summary_paragraphs ?? []); @endphp
      @for($i = 0; $i < max(3, count($paras)); $i++)
        <div class="field">
          <label>Paragraph {{ $i + 1 }}</label>
          <textarea name="summary_paragraphs[]" maxlength="5000">{{ $paras[$i] ?? '' }}</textarea>
        </div>
      @endfor

      <h2 style="margin-top: 24px;">Visibility flags</h2>
      <label class="check-row">
        <input type="hidden" name="is_offsite" value="0">
        <input type="checkbox" name="is_offsite" value="1" @checked($sermon->is_offsite ?? false)>
        <span class="lbl-text">Offsite stream<small>Camp meeting, conference, visiting venue — won't show on /find-peace</small></span>
      </label>
      <label class="check-row">
        <input type="hidden" name="is_no_sermon" value="0">
        <input type="checkbox" name="is_no_sermon" value="1" @checked($sermon->is_no_sermon ?? false)>
        <span class="lbl-text">No sermon<small>Prayer service / special program with no teaching segment — kept in DB but never published</small></span>
      </label>

      <div style="margin-top: 18px; text-align: right;">
        <button type="submit" class="primary">Save sermon</button>
      </div>
    </div>
  </form>

  {{-- ── Q&As — edit / delete each, add new at end ─────────────── --}}
  <div class="card">
    <h2>Q&A pairs ({{ $sermon->qaPairs->count() }})</h2>

    @foreach($sermon->qaPairs as $qa)
      <form method="POST" action="{{ route('admin.peace.qa.update', [$sermon->slug, $qa->id]) }}" class="qa-edit">
        @csrf @method('PATCH')
        <div class="field">
          <label>Question (#{{ $qa->id }} · order {{ $qa->display_order }})</label>
          <input type="text" name="question" value="{{ $qa->question }}" required maxlength="1000">
        </div>
        <div class="field">
          <label>Answer</label>
          <textarea name="answer" required maxlength="5000">{{ $qa->answer }}</textarea>
        </div>
        <div class="row2">
          <div class="field">
            <label>Display order</label>
            <input type="number" name="display_order" value="{{ $qa->display_order }}" min="0">
          </div>
          <div style="display:flex; align-items:flex-end; gap: 10px; padding-bottom: 4px;">
            <button type="submit" class="ghost">Save Q&A</button>
          </div>
        </div>
      </form>
      <form method="POST" action="{{ route('admin.peace.qa.destroy', [$sermon->slug, $qa->id]) }}" style="text-align:right; margin-top:-10px;" onsubmit="return confirm('Remove this Q&A?');">
        @csrf @method('DELETE')
        <button type="submit" class="danger">Delete Q&A #{{ $qa->id }}</button>
      </form>
    @endforeach

    <form method="POST" action="{{ route('admin.peace.qa.add', $sermon->slug) }}" style="border-top: 2px solid var(--line); padding-top: 18px; margin-top: 24px;">
      @csrf
      <h2>Add new Q&A</h2>
      <div class="field">
        <label>Question</label>
        <input type="text" name="question" required maxlength="1000" placeholder="What if my past trauma keeps pulling me back into old patterns?">
      </div>
      <div class="field">
        <label>Answer</label>
        <textarea name="answer" required maxlength="5000" placeholder="Pastoral, 2-4 sentences. Name the feeling, anchor in scripture, end with hope."></textarea>
      </div>
      <div style="text-align: right;">
        <button type="submit" class="primary">Add Q&A</button>
      </div>
    </form>
  </div>

  {{-- ── Scriptures — list, delete, add new (validated against bible-api) ─ --}}
  <div class="card">
    <h2>Scriptures ({{ $sermon->scriptures->count() }})</h2>

    @foreach($sermon->scriptures as $scr)
      <div class="ref-row">
        <span class="ref-label">{{ $scr->reference_display }} · {{ $scr->translation }}</span>
        <span class="ref-text">{{ \Illuminate\Support\Str::limit($scr->verse_text, 100) }}</span>
        <form method="POST" action="{{ route('admin.peace.scripture.destroy', [$sermon->slug, $scr->id]) }}" onsubmit="return confirm('Remove this scripture?');">
          @csrf @method('DELETE')
          <button type="submit" class="danger">Remove</button>
        </form>
      </div>
    @endforeach

    <form method="POST" action="{{ route('admin.peace.scripture.add', $sermon->slug) }}" class="add-row">
      @csrf
      <input type="text" name="reference_display" required maxlength="100" placeholder='e.g., "1 Samuel 30:6" or "Acts 2"'>
      <button type="submit" class="primary">Add &amp; validate</button>
    </form>
    <p style="font-size: 12px; color: var(--ink-soft); margin-top: 10px;">Reference is checked against bible-api.com — invalid refs are rejected.</p>
  </div>

</main>

</body>
</html>
