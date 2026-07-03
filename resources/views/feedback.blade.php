<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Send Feedback — Shalom SDA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Instrument+Sans:wght@500;600;700&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
  *:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; border-radius: 3px; }
  input:focus-visible, textarea:focus-visible, select:focus-visible { outline: 0; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { background: var(--parchment); color: var(--ink); font-family: 'Poppins', system-ui, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }

  .site-header { padding: 22px clamp(20px, 5vw, 40px); border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; }
  .brand { font-family: 'Instrument Sans', sans-serif; font-size: 14px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: none; color: var(--ink); display: inline-flex; align-items: center; gap: 6px; }
  .brand em { font-family: 'Xtreem', 'Cormorant Garamond', serif; font-style: normal; color: var(--teal); text-transform: lowercase; font-size: 48px; line-height: 0.8; padding-right: 6px; transform: translateY(2px); }
  .back { font-family: 'Instrument Sans', sans-serif; font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ink-soft); text-decoration: none; padding: 8px 14px; border: 1px solid var(--line); border-radius: 4px; }
  .back:hover { color: var(--teal); border-color: var(--teal); }

  main { max-width: 680px; margin: 0 auto; padding: clamp(48px, 8vh, 80px) 24px 80px; }
  .eyebrow { font-family: 'Instrument Sans', sans-serif; font-size: 12px; letter-spacing: 0.32em; text-transform: uppercase; color: var(--teal); font-weight: 700; margin-bottom: 14px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: clamp(36px, 6vw, 56px); font-weight: 500; line-height: 1; letter-spacing: -0.03em; margin-bottom: 16px; }
  h2 { font-family: 'Cormorant Garamond', serif; font-size: clamp(22px, 3vw, 28px); font-weight: 500; margin: 42px 0 16px; letter-spacing: -0.01em; }
  .lead { color: var(--ink-soft); font-size: 17px; line-height: 1.55; margin-bottom: 36px; }

  .ok { background: color-mix(in srgb, var(--teal) 8%, transparent); border-left: 3px solid var(--teal); color: var(--teal); padding: 14px 18px; border-radius: 4px; margin-bottom: 20px; font-family: 'Instrument Sans', sans-serif; font-size: 13px; letter-spacing: 0.06em; font-weight: 600; }

  .reply { background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 22px 24px; margin-bottom: 36px; box-shadow: 0 6px 18px -8px color-mix(in srgb, var(--ink) 18%, transparent); }
  .reply-head { display: flex; align-items: center; gap: 8px; font-family: 'Instrument Sans', sans-serif; font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--teal); font-weight: 700; margin-bottom: 14px; }
  .reply-body { font-family: 'Cormorant Garamond', serif; font-size: 19px; line-height: 1.6; color: var(--ink); letter-spacing: -0.01em; white-space: pre-line; }

  form { display: flex; flex-direction: column; gap: 22px; }
  label.row { display: flex; flex-direction: column; gap: 6px; }
  label.row > .lbl { font-family: 'Instrument Sans', sans-serif; font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); font-weight: 600; }
  select, textarea, input[type="text"] { font: inherit; padding: 12px 14px; border: 1px solid var(--line); border-radius: 4px; background: #fff; color: var(--ink); width: 100%; }
  select:focus, textarea:focus, input:focus { outline: 0; border-color: var(--teal); box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal) 12%, transparent); }
  textarea { min-height: 200px; resize: vertical; font-family: 'Poppins', sans-serif; line-height: 1.5; }
  .err {
    color: var(--ink); background: rgba(192, 57, 43, 0.08);
    border-left: 3px solid #c0392b; padding: 6px 12px;
    font-size: 13px; line-height: 1.4; border-radius: 0 4px 4px 0; margin-top: 6px;
  }

  button.send { background: var(--teal); color: #fff; border: 0; padding: 14px 28px; border-radius: 4px; font-family: 'Instrument Sans', sans-serif; font-size: 12px; letter-spacing: 0.22em; text-transform: uppercase; font-weight: 700; cursor: pointer; align-self: flex-start; transition: background 0.15s; }
  button.send:hover { background: var(--teal-dark); }

  .hint { color: var(--ink-soft); font-size: 13px; line-height: 1.55; margin-top: 8px; font-style: italic; }

  /* ─── Ticket cards ─── */
  .tickets-section { margin-top: 48px; }
  .tickets-section h2 { display: flex; align-items: center; gap: 12px; }
  .tickets-count { font-family: 'Instrument Sans', sans-serif; font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--ink-soft); font-weight: 700; background: color-mix(in srgb, var(--ink) 6%, transparent); padding: 4px 10px; border-radius: 12px; }
  .ticket {
    background: #fff; border: 1px solid var(--line); border-radius: 6px;
    padding: 18px 20px; margin-bottom: 12px;
    border-left: 3px solid var(--brass);
  }
  .ticket.is-closed { border-left-color: var(--teal); background: color-mix(in srgb, var(--teal) 3%, transparent); opacity: 0.85; }
  .ticket-head {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    font-family: 'Instrument Sans', sans-serif; font-size: 10px;
    letter-spacing: 0.18em; text-transform: uppercase; font-weight: 700;
    margin-bottom: 10px;
  }
  .tag-pill { padding: 2px 9px; border-radius: 10px; font-size: 10px; letter-spacing: 0.14em; }
  .tag-pill.bug      { background: rgba(192, 57, 43, 0.12); color: #c0392b; }
  .tag-pill.idea     { background: rgba(176, 141, 60, 0.15); color: var(--brass); }
  .tag-pill.question { background: rgba(3, 97, 122, 0.12); color: var(--teal); }
  .tag-pill.note     { background: rgba(26, 35, 50, 0.06); color: var(--ink-soft); }
  .ticket-date { color: var(--ink-soft); font-weight: 600; }
  .ticket-status { margin-left: auto; }
  .ticket-status.pending { color: var(--brass); }
  .ticket-status.closed  { color: var(--teal); }
  .ticket-attach { display:flex; gap:8px; flex-wrap:wrap; margin: 12px 0 0; }
  .ticket-attach a { display:block; width:96px; height:96px; border-radius:4px; overflow:hidden; border:1px solid var(--line); background:#f5f0e0; }
  .ticket-attach img { width:100%; height:100%; object-fit:cover; display:block; }
  .ticket-attach a:hover { border-color: var(--teal); }
  .ticket-author { color: var(--ink-soft); font-weight: 600; }
  .ticket-msg { color: var(--ink); font-size: 15px; line-height: 1.55; white-space: pre-line; }
  .ticket-reply {
    margin-top: 14px; padding: 12px 14px;
    background: color-mix(in srgb, var(--teal) 5%, transparent); border-radius: 4px;
    font-family: 'Cormorant Garamond', serif;
    font-size: 15px; line-height: 1.5; color: var(--ink);
    white-space: pre-line;
  }
  .ticket-reply .rh {
    display: block; font-family: 'Instrument Sans', sans-serif;
    font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase;
    font-weight: 700; color: var(--teal); margin-bottom: 6px;
  }
  .ticket-closed-note { margin-top: 10px; padding: 10px 12px; background: color-mix(in srgb, var(--teal) 6%, transparent); border-radius: 4px; font-size: 14px; color: var(--ink); font-style: italic; }
  .ticket-actions { margin-top: 14px; display: flex; gap: 8px; }
  .btn-close, .btn-reopen {
    font-family: 'Instrument Sans', sans-serif; font-size: 10px;
    letter-spacing: 0.2em; text-transform: uppercase; font-weight: 700;
    padding: 7px 14px; border-radius: 4px; cursor: pointer;
    border: 1px solid var(--line); background: #fff; color: var(--ink-soft);
  }
  .btn-close:hover { background: var(--teal); border-color: var(--teal); color: #fff; }
  .btn-reopen:hover { background: var(--brass); border-color: var(--brass); color: #fff; }

  .empty { color: var(--ink-soft); font-style: italic; font-size: 14px; padding: 6px 0; }
</style>
@include('partials.theme-vars')
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">

@include('partials.site-menu')

<main>
  <div class="eyebrow">Direct Line</div>
  <h1>Send Feedback</h1>
  <p class="lead">Type below — Karlon and Claude will see this verbatim. No need to text or call.</p>

  @if (session('status') === 'sent')
    <div class="ok">✓ Got it — your message is in.</div>
  @endif

  @if (!empty($claudeReply))
    <div class="reply">
      <div class="reply-head">
        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
        Claude replied
      </div>
      <div class="reply-body">{{ $claudeReply }}</div>
    </div>
  @endif

  <form method="POST" action="{{ route('feedback.store') }}" enctype="multipart/form-data">
    @csrf
    <label class="row">
      <span class="lbl">Type</span>
      <select name="tag" required>
        @foreach ($tags as $t)
          <option value="{{ $t }}" {{ old('tag') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
        @endforeach
      </select>
      @error('tag')<div class="err">{{ $message }}</div>@enderror
    </label>

    <label class="row">
      <span class="lbl">Your message</span>
      <textarea name="body" required maxlength="2000" placeholder="What's on your mind? A bug, an idea, a question — anything.">{{ old('body') }}</textarea>
      <div class="hint">Up to 2,000 characters. Claude reads every submission and replies right here.</div>
      @error('body')<div class="err">{{ $message }}</div>@enderror
    </label>

    <label class="field">
      <span class="lbl">Screenshots <span style="color:var(--ink-soft); font-weight:400;">(optional, up to 5 images)</span></span>
      <input type="file" name="attachments[]" multiple accept="image/*,.heic,.heif" id="feedback-attachments" style="padding:10px; background:#fff; border:1px solid var(--line); border-radius:4px; width:100%;">
      <div class="hint" id="feedback-attachments-status">JPG, PNG, WebP, GIF, or iPhone HEIC. Max 10MB each.</div>
      @error("attachments")<div class="err">{{ $message }}</div>@enderror
      @error("attachments.*")<div class="err">{{ $message }}</div>@enderror
    </label>



    <button type="submit" class="send">Send @include('partials._ar')</button>
  </form>

  {{-- ─── Ticket history ─── --}}
  <section class="tickets-section">
    <h2>Pending <span class="tickets-count">{{ $pending->count() }}</span></h2>
    @forelse ($pending as $ticket)
      <article class="ticket is-pending" data-ticket-id="{{ $ticket->id }}">
        <div class="ticket-head">
          <span class="tag-pill {{ $ticket->tag }}">{{ $ticket->tag }}</span>
          @if ($isAdmin)<span class="ticket-author">{{ $ticket->user->name ?? '—' }}</span>@endif
          <span class="ticket-date">{{ $ticket->created_at->diffForHumans() }}</span>
          <span class="ticket-status pending">Pending</span>
        </div>
        <div class="ticket-msg">{{ $ticket->message }}</div>
        @if ($ticket->attachments)
          <div class="ticket-attach">
            @foreach ($ticket->attachments as $i => $a)
              <a href="{{ route('feedback.attachment', [$ticket, $i]) }}" target="_blank" rel="noopener" title="{{ $a['original'] ?? 'screenshot' }}">
                <img src="{{ route('feedback.attachment', [$ticket, $i]) }}" alt="screenshot {{ $i + 1 }}" loading="lazy">
              </a>
            @endforeach
          </div>
        @endif
        @if ($ticket->claude_reply)
          <div class="ticket-reply">
            <span class="rh">Claude replied</span>
            {{ $ticket->claude_reply }}
          </div>
        @endif
        @if ($isAdmin)
          <div class="ticket-actions">
            <button type="button" class="btn-close" data-close-url="{{ url('/feedback/' . $ticket->id . '/close') }}">Close ticket</button>
          </div>
        @endif
      </article>
    @empty
      <div class="empty">Nothing pending. 🎉</div>
    @endforelse

    <h2 style="margin-top:48px;">Recently closed <span class="tickets-count">{{ $closed->count() }}</span></h2>
    <p class="hint" style="margin:-6px 0 18px;">Auto-deleted 14 days after close.</p>
    @forelse ($closed as $ticket)
      <article class="ticket is-closed" data-ticket-id="{{ $ticket->id }}">
        <div class="ticket-head">
          <span class="tag-pill {{ $ticket->tag }}">{{ $ticket->tag }}</span>
          @if ($isAdmin)<span class="ticket-author">{{ $ticket->user->name ?? '—' }}</span>@endif
          <span class="ticket-date">Closed {{ $ticket->closed_at?->diffForHumans() }}</span>
          <span class="ticket-status closed">Closed</span>
        </div>
        <div class="ticket-msg">{{ $ticket->message }}</div>
        @if ($ticket->attachments)
          <div class="ticket-attach">
            @foreach ($ticket->attachments as $i => $a)
              <a href="{{ route('feedback.attachment', [$ticket, $i]) }}" target="_blank" rel="noopener" title="{{ $a['original'] ?? 'screenshot' }}">
                <img src="{{ route('feedback.attachment', [$ticket, $i]) }}" alt="screenshot {{ $i + 1 }}" loading="lazy">
              </a>
            @endforeach
          </div>
        @endif
        @if ($ticket->closed_note)
          <div class="ticket-closed-note"><strong>Resolution:</strong> {{ $ticket->closed_note }}</div>
        @endif
        @if ($isAdmin)
          <div class="ticket-actions">
            <button type="button" class="btn-reopen" data-reopen-url="{{ url('/feedback/' . $ticket->id . '/reopen') }}">Reopen</button>
          </div>
        @endif
      </article>
    @empty
      <div class="empty">Nothing closed in the last 14 days.</div>
    @endforelse
  </section>
</main>

<script>
(function () {
  const CSRF = document.querySelector('meta[name="csrf-token"]').content;
  async function patch(url, body) {
    const res = await fetch(url, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(body || {}),
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }
  document.body.addEventListener('click', async (e) => {
    const closeBtn = e.target.closest('.btn-close');
    if (closeBtn) {
      const note = prompt('Close with a short resolution note? (optional)');
      if (note === null) return;
      try { await patch(closeBtn.dataset.closeUrl, { note }); location.reload(); }
      catch (err) { shToast('Could not close: ' + err.message); }
    }
    const reopenBtn = e.target.closest('.btn-reopen');
    if (reopenBtn) {
      if (!await window.shConfirm('Reopen this ticket?')) return;
      try { await patch(reopenBtn.dataset.reopenUrl); location.reload(); }
      catch (err) { shToast('Could not reopen: ' + err.message); }
    }
  });
})();
</script>


<script>
(function () {
  const fi = document.getElementById("feedback-attachments");
  const status = document.getElementById("feedback-attachments-status");
  if (!fi) return;

  fi.addEventListener("change", async (e) => {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;
    const heicFiles = files.filter(f => /\.(heic|heif)$/i.test(f.name) || ["image/heic","image/heif"].includes(f.type));
    if (heicFiles.length === 0) {
      status.textContent = files.length + " image" + (files.length > 1 ? "s" : "") + " selected.";
      return;
    }

    status.textContent = "⏳ Converting " + heicFiles.length + " HEIC file" + (heicFiles.length > 1 ? "s" : "") + "…";

    if (!window.heic2any) {
      try {
        await new Promise((resolve, reject) => {
          const s = document.createElement("script");
          s.src = "https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js";
          s.onload = resolve; s.onerror = reject;
          document.head.appendChild(s);
        });
      } catch { status.textContent = "⚠ HEIC support did not load. Save photos as JPEG and try again."; return; }
    }

    const out = [];
    for (const f of files) {
      const isHeic = /\.(heic|heif)$/i.test(f.name) || ["image/heic","image/heif"].includes(f.type);
      if (!isHeic) { out.push(f); continue; }
      try {
        const blob = await window.heic2any({ blob: f, toType: "image/jpeg", quality: 0.9 });
        out.push(new File([blob], f.name.replace(/\.(heic|heif)$/i, ".jpg"), { type: "image/jpeg" }));
      } catch {
        status.textContent = "⚠ Could not convert " + f.name + ". Skipping it. Re-export as JPEG and re-add.";
      }
    }
    const dt = new DataTransfer();
    out.forEach(f => dt.items.add(f));
    fi.files = dt.files;
    status.textContent = "✓ " + out.length + " image" + (out.length > 1 ? "s" : "") + " ready (" + heicFiles.length + " HEIC converted).";
  });
})();
</script>
@include('partials._confirm')

</body>
</html>
