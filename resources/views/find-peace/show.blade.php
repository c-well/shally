<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $sermon->title }} · Finding Peace</title>
<meta name="description" content="{{ $sermon->heart_line }}">

    @if(!empty($poll))
    <section class="poll-card" id="poll-{{ $poll->id }}" data-poll-id="{{ $poll->id }}">
      <div class="poll-eyebrow">A quick question</div>
      <div class="poll-question">{!! $linkifyScripture($poll->question) !!}</div>
      <form class="poll-form" data-poll-id="{{ $poll->id }}">
        @csrf
        <input type="text" name="hp" class="poll-hp" tabindex="-1" autocomplete="off">
        <input type="hidden" name="poll_id" value="{{ $poll->id }}">
        <div class="poll-options">
          @foreach($poll->options as $opt)
            <label class="poll-option">
              <input type="radio" name="option_id" value="{{ $opt->id }}" required>
              <span class="poll-option-text">{{ $opt->option_text }}</span>
            </label>
          @endforeach
        </div>
        <button type="submit" class="poll-submit" disabled>See what scripture says →</button>
      </form>
      <div class="poll-results" hidden>
        @if($poll->scripture_explainer)
          <div class="poll-explainer">
            @if($poll->scripture_ref)
              <span class="ref">{!! $linkifyScripture($poll->scripture_ref) !!}</span>
            @endif
            {!! $linkifyScripture($poll->scripture_explainer) !!}
          </div>
        @endif
        <div class="poll-result-rows" style="margin-top: 1.2rem;"></div>
        <div class="poll-total"></div>
      </div>
    </section>
    @endif

    @if($sermon->topics->isNotEmpty())
<meta name="keywords" content="{{ $sermon->topics->pluck('name')->implode(', ') }}">
@endif
<link rel="canonical" href="{{ url('/find-peace/' . $sermon->slug) }}">
<meta name="robots" content="index, follow, max-snippet:-1">
<meta property="og:type" content="article">
@php
  $sharedQa = null;
  $qid = request('qa');
  if ($qid && is_numeric($qid)) {
      $sharedQa = $sermon->qaPairs->firstWhere('id', (int) $qid);
  }
@endphp
<meta property="og:title" content="{{ $sharedQa ? $sharedQa->question : ($sermon->title . ' · Finding Peace') }}">
<meta property="og:description" content="{{ $sharedQa ? \Illuminate\Support\Str::limit($sharedQa->answer, 200) : $sermon->heart_line }}">
<meta property="og:url" content="{{ url('/find-peace/' . $sermon->slug) }}">

@if($sermon->qaPairs->isNotEmpty())
<script type="application/ld+json">@verbatim
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
@endverbatim
@foreach($sermon->qaPairs->take(5) as $qa)
    {
      "@type": "Question",
      "name": @json($qa->question),
      "acceptedAnswer": { "@type": "Answer", "text": @json($qa->answer) }
    }@if(!$loop->last),@endif
@endforeach
@verbatim
  ]
}
@endverbatim</script>
@endif

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }
  body {
    font-family: 'Poppins', -apple-system, sans-serif;
    background: var(--bg); color: var(--ink);
    line-height: 1.7; font-weight: 300;
    -webkit-font-smoothing: antialiased;
  }

  .mark-top { text-align: center; padding: 2.5rem 2rem 0; }
  .mark-top a {
    font-family: 'Xtreem', 'Poppins', sans-serif;
    font-style: normal; font-weight: 500;
    color: var(--ink);
    text-transform: lowercase;
    font-size: clamp(48px, 9vw, 72px);
    letter-spacing: -0.02em; line-height: 0.8;
    text-decoration: none; display: inline-block;
  }

  .sermon-page { padding: 3rem 2rem 6rem; }
  .container { max-width: 720px; margin: 0 auto; }

  .sermon-header { text-align: center; margin-bottom: 2.5rem; }
  .sermon-meta-top {
    font-size: 0.7rem; letter-spacing: 0.3em; color: var(--brass);
    text-transform: uppercase; margin-bottom: 1.25rem; font-weight: 400;
  }
  .sermon-title {
    font-size: clamp(2rem, 5vw, 2.75rem);
    font-weight: 200; letter-spacing: -0.01em;
    color: var(--ink); margin-bottom: 0.5rem; line-height: 1.2;
  }
  .sermon-meta { font-size: 0.9rem; color: var(--ink-soft); font-weight: 300; }

  .heart {
    text-align: center; font-size: 1.15rem; color: var(--ink);
    font-weight: 300; line-height: 1.6;
    max-width: 540px; margin: 3rem auto 2rem;
  }
  .heart::before, .heart::after {
    content: ''; display: block;
    width: 32px; height: 1px; background: var(--brass);
    margin: 1.75rem auto; opacity: 0.6;
  }

  /* ── Audio player — light/teal, always (matches Peace Notes) ─────── */
  .player {
    --player-accent: #03617A;
    --player-accent-bright: #024357;
    --player-bg: #fbf4e0;
    --player-border: color-mix(in srgb, var(--ink) 10%, transparent);
    --player-ink: #1a2332;
    --player-ink-soft: #334455;
    --player-track: color-mix(in srgb, var(--ink) 12%, transparent);
    --player-buffer: color-mix(in srgb, var(--teal) 18%, transparent);
    --player-glow: color-mix(in srgb, var(--teal) 45%, transparent);
    --player-glow-hover: color-mix(in srgb, var(--teal) 65%, transparent);
    --player-tint: color-mix(in srgb, var(--teal) 12%, transparent);

    margin: 0 auto 3rem;
    padding: 28px clamp(20px, 4vw, 32px);
    background: var(--player-bg);
    border: 1px solid var(--player-border);
    border-radius: 14px;
    color: var(--player-ink);
    box-shadow: 0 18px 44px -22px color-mix(in srgb, var(--teal) 22%, transparent);
  }
  .player audio { display: none; }
  .player-controls {
    display: flex; align-items: center; justify-content: center;
    gap: clamp(18px, 4vw, 32px); margin-bottom: 16px;
  }
  .skip-btn {
    background: transparent; border: 0; cursor: pointer; padding: 8px;
    color: var(--player-ink-soft);
    display: inline-flex; flex-direction: column; align-items: center; gap: 2px;
    transition: color 0.15s, transform 0.1s;
    font-family: inherit;
  }
  .skip-btn:hover { color: var(--player-accent); }
  .skip-btn:active { transform: scale(0.92); }
  .skip-btn svg { width: 28px; height: 28px; }
  .skip-btn .skip-num { font-size: 9px; font-weight: 600; letter-spacing: 0.06em; font-variant-numeric: tabular-nums; }
  .play-btn {
    width: 72px; height: 72px; border-radius: 999px;
    background: var(--player-accent); color: #fff; border: 0; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 24px -8px var(--player-glow);
    transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
  }
  .play-btn:hover { background: var(--player-accent-bright); box-shadow: 0 10px 28px -8px var(--player-glow-hover); }
  .play-btn:active { transform: scale(0.95); }
  .play-btn svg { width: 28px; height: 28px; }
  .play-btn .icon-pause { display: none; }
  .player.playing .play-btn .icon-play { display: none; }
  .player.playing .play-btn .icon-pause { display: block; }

  .player-title {
    text-align: center;
    font-size: 0.95rem; font-weight: 400; color: var(--player-ink);
    margin-bottom: 1rem; line-height: 1.4;
  }
  .player-title-sep { color: var(--player-ink-soft); margin: 0 0.4rem; opacity: 0.6; }
  .player-title-speaker { color: var(--player-ink-soft); font-weight: 300; }

  .progress-wrap { padding: 14px 0; cursor: pointer; position: relative; }
  .progress-tip {
    position: absolute; bottom: 100%; left: 0; transform: translate(-50%, -8px);
    background: var(--player-ink); color: var(--player-bg);
    font-family: 'JetBrains Mono', ui-monospace, monospace;
    font-size: 11px; letter-spacing: 0.06em;
    padding: 4px 8px; border-radius: 4px;
    pointer-events: none; opacity: 0;
    transition: opacity 0.12s;
    white-space: nowrap;
    z-index: 5;
  }
  .progress-wrap:hover .progress-tip,
  .progress-wrap.scrubbing .progress-tip { opacity: 1; }
  .progress-bar { position: relative; height: 5px; border-radius: 999px; background: var(--player-track); overflow: visible; }
  .progress-buffer { position: absolute; inset: 0; width: 0%; background: var(--player-buffer); border-radius: 999px; }
  .progress-fill { position: absolute; inset: 0; width: 0%; background: var(--player-accent); border-radius: 999px; }
  .progress-handle {
    position: absolute; top: 50%; left: 0;
    width: 14px; height: 14px;
    background: var(--player-accent); border: 2px solid #fff; border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    box-shadow: 0 2px 6px var(--player-glow);
    transition: transform 0.15s;
  }
  .progress-wrap:hover .progress-handle, .player.playing .progress-handle { transform: translate(-50%, -50%) scale(1); }

  .player-meta {
    margin-top: 14px;
    display: flex; align-items: center; justify-content: space-between;
    font-size: 12px; color: var(--player-ink-soft);
    letter-spacing: 0.04em; gap: 16px;
    font-variant-numeric: tabular-nums;
  }
  .player-time { min-width: 45px; }
  .player-time.duration { text-align: right; }
  .speed-pills { display: inline-flex; gap: 4px; flex-wrap: wrap; justify-content: center; }
  .speed-pills button {
    background: transparent; border: 0; cursor: pointer;
    font-family: inherit; font-size: 11px;
    color: var(--player-ink-soft); opacity: 0.55;
    padding: 4px 8px; border-radius: 4px;
    transition: opacity 0.15s, color 0.15s, background 0.15s;
    font-variant-numeric: tabular-nums;
  }
  .speed-pills button:hover { opacity: 1; color: var(--player-accent); }
  .speed-pills button.active { opacity: 1; color: var(--player-accent); background: var(--player-tint); font-weight: 500; }

  .player-utils {
    margin-top: 16px; padding-top: 16px;
    border-top: 1px solid var(--player-border);
    display: flex; align-items: center; justify-content: flex-start;
    font-size: 10px; font-weight: 500;
    letter-spacing: 0.20em; text-transform: uppercase;
    color: var(--player-ink-soft);
  }
  .player-utils a { display: inline-flex; align-items: center; gap: 6px; color: var(--player-ink-soft); text-decoration: none; transition: color 0.15s; }
  .player-utils a:hover { color: var(--player-accent); }
  .player-utils svg { width: 13px; height: 13px; }

  @media (max-width: 600px) {
    .player { padding: 22px 18px; }
    .play-btn { width: 60px; height: 60px; }
    .play-btn svg { width: 24px; height: 24px; }
    .player-meta { font-size: 11px; }
  }

  /* ── Q&A cards (top of page, visible above the fold) ─────────────── */
  .qa-section { margin-bottom: 2.5rem; }
  .qa-section-title {
    font-size: 0.7rem; letter-spacing: 0.22em;
    color: var(--brass); text-transform: uppercase;
    margin-bottom: 1rem; font-weight: 500;
  }
  .qa-list { list-style: none; }
  .qa-item {
    margin-bottom: 0.75rem;
    background: var(--bg-elevated);
    border: 1px solid var(--line);
    border-radius: 6px;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .qa-item:hover { border-color: var(--brass); }
  .qa-item.spotlight {
    border-color: var(--brass);
    box-shadow: 0 4px 16px -8px color-mix(in srgb, var(--brass) 30%, transparent);
  }
  .qa-item details summary {
    cursor: pointer; list-style: none;
    padding: 1.1rem 1.4rem;
    display: flex; align-items: flex-start; gap: 1rem;
    user-select: none;
    transition: color 0.2s;
  }
  .qa-item details summary::-webkit-details-marker { display: none; }
  .qa-summary-text { flex: 1; min-width: 0; }
  .qa-q { color: var(--ink); margin-bottom: 0.4rem; font-size: 1rem; font-weight: 500; line-height: 1.4; }
  .qa-preview { font-size: 0.88rem; color: var(--ink-soft); font-weight: 300; line-height: 1.55; margin: 0; }
  .qa-item details[open] .qa-preview { display: none; }
  .qa-chev {
    width: 9px; height: 9px;
    border-right: 1.5px solid var(--brass);
    border-bottom: 1.5px solid var(--brass);
    transform: rotate(-45deg);
    transition: transform 0.3s;
    flex-shrink: 0; margin-top: 0.5rem;
  }
  .qa-item details[open] .qa-chev { transform: rotate(45deg); margin-top: 0.45rem; }
  .qa-body {
    padding: 0 1.4rem 1.4rem;
    border-top: 1px solid var(--line);
    padding-top: 1.1rem;
    margin-top: -1px;
  }
  .qa-a-full { color: var(--ink); font-size: 0.95rem; font-weight: 300; line-height: 1.65; margin-bottom: 0.85rem; }

  /* ── Section accordions (Summary, Scripture — collapsed by default) ── */
  .section-accordion {
    margin-bottom: 0.75rem;
    border: 1px solid var(--line);
    border-radius: 4px;
    background: color-mix(in srgb, var(--brass) 3%, transparent);
    transition: background 0.2s;
  }
  .section-accordion[open] { background: color-mix(in srgb, var(--brass) 6%, transparent); }
  .section-accordion > summary {
    cursor: pointer; list-style: none;
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1rem 1.4rem;
    font-size: 0.7rem; letter-spacing: 0.22em;
    color: var(--teal); text-transform: uppercase; font-weight: 500;
    user-select: none;
    transition: color 0.2s;
  }
  .section-accordion > summary::-webkit-details-marker { display: none; }
  .section-accordion > summary:hover { color: var(--teal-bright); }
  .section-acc-chev {
    width: 9px; height: 9px;
    border-right: 1.5px solid var(--teal);
    border-bottom: 1.5px solid var(--teal);
    transform: rotate(-45deg);
    transition: transform 0.3s;
    flex-shrink: 0;
  }
  .section-accordion[open] .section-acc-chev { transform: rotate(45deg); margin-bottom: -3px; }
  .section-acc-body {
    padding: 0 1.4rem 1.4rem;
    font-size: 0.95rem; color: var(--ink); font-weight: 300;
  }
  .section-acc-body p { margin-bottom: 1rem; }
  .section-acc-body p.muted { color: var(--ink-soft); }
  .section-acc-body p:last-child { margin-bottom: 0; }

  .scripture-list { list-style: none; }
  .scripture-list li { padding: 1rem 0; border-bottom: 1px dashed var(--line); }
  .scripture-list li:last-child { border-bottom: none; padding-bottom: 0; }
  .scripture-list li:first-child { padding-top: 0; }
  .scripture-ref {
    font-size: 0.7rem; letter-spacing: 0.2em; color: var(--brass);
    text-transform: uppercase; margin-bottom: 0.5rem; font-weight: 500;
  }
  .scripture-text { color: var(--ink-soft); line-height: 1.7; font-weight: 300; }

  .related-list { list-style: none; }
  .related-list li { padding: 0.75rem 0; border-bottom: 1px dashed var(--line); }
  .related-list li:last-child { border-bottom: none; padding-bottom: 0; }
  .related-list li:first-child { padding-top: 0; }
  .related-list a { color: var(--ink); text-decoration: none; transition: color 0.2s; }
  .related-list a:hover { color: var(--brass-bright); }


  .verse-link {
    border-bottom: 1px dotted var(--brass);
    color: inherit;
    cursor: help;
    text-decoration: none;
  }
  .verse-link:hover, .verse-link:focus { color: var(--brass-bright); border-bottom-style: solid; outline: none; }
  .verse-tooltip {
    position: absolute;
    z-index: 9999;
    max-width: 340px;
    padding: 14px 18px;
    background: var(--bg-elevated);
    border: 1px solid var(--brass);
    border-radius: 8px;
    font-size: 0.88rem; line-height: 1.55; color: var(--ink); font-weight: 300;
    box-shadow: 0 8px 24px -6px rgba(0,0,0,0.25);
    opacity: 0; transform: translateY(-4px);
    transition: opacity 0.15s, transform 0.15s;
    pointer-events: none;
  }
  .verse-tooltip.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
  .verse-tooltip-ref {
    display: block;
    font-size: 0.7rem; letter-spacing: 0.18em; color: var(--brass);
    text-transform: uppercase; font-weight: 500;
    margin-bottom: 0.5rem;
  }
  .verse-tooltip-loading { font-style: italic; color: var(--ink-soft); }
  .verse-tooltip-more { display: inline-block; margin-top: 8px; color: var(--teal); text-decoration: none; font-weight: 500; font-size: 0.85rem; }
  .verse-tooltip-more:hover { color: var(--teal-bright); text-decoration: underline; }
  @media (prefers-color-scheme: dark) {
    .verse-tooltip-more { color: var(--teal-bright); }
  }
  /* Reactions */
  .reaction {
    display: inline-flex; align-items: center; gap: 0.625rem;
    background: transparent; border: 0;
    padding: 0.5rem 0.5rem;
    font-family: inherit; font-size: 0.95rem; font-weight: 500;
    color: var(--ink-soft); cursor: pointer; border-radius: 0;
    transition: color 0.2s, transform 0.1s;
  }
  .reaction:hover { color: var(--brass); }
  .reaction:active { transform: scale(0.97); }
  .reaction.active { color: var(--brass); font-weight: 600; }
  .qa-item .reaction { padding: 0.45rem 0.95rem; font-size: 0.85rem; }
  /* ─── Q&A action row: helped (left), save+share (right) ─────────── */
  .qa-actions {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; padding-top: 0.8rem;
    border-top: 1px dashed var(--line);
    margin-top: 0.6rem;
  }
  .qa-actions-left, .qa-actions-right {
    display: inline-flex; align-items: center; gap: 0.25rem;
  }
  .action-btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: transparent; border: 0;
    padding: 0.4rem 0.6rem;
    font-family: inherit; font-size: 0.82rem; font-weight: 500;
    color: var(--ink-soft); cursor: pointer;
    transition: color 0.2s, transform 0.1s;
    line-height: 1; border-radius: 4px;
    position: relative;
  }
  .action-btn:hover { color: var(--brass); }
  .action-btn:active { transform: scale(0.96); }
  .action-btn.active { color: var(--brass); font-weight: 600; }
  .action-btn svg {
    width: 16px; height: 16px; display: block; flex-shrink: 0;
    stroke: currentColor; fill: none; stroke-width: 1.8;
    stroke-linejoin: round; stroke-linecap: round;
  }
  .action-btn.helped.active svg, .action-btn.save.active svg { fill: currentColor; }

  /* ─── Save email-modal ─────────────────────────────────────────── */
  .seeker-modal {
    display: none;
    position: fixed; inset: 0;
    background: rgba(10,16,20,0.65);
    backdrop-filter: blur(2px);
    z-index: 9998;
    align-items: center; justify-content: center;
    padding: 1.5rem;
  }
  .seeker-modal.open { display: flex; }
  .seeker-modal-card {
    background: var(--bg-elevated);
    border: 1px solid var(--line-strong);
    border-radius: 10px;
    max-width: 440px; width: 100%;
    padding: 2rem 1.75rem;
    text-align: center;
    box-shadow: 0 20px 60px -10px rgba(0,0,0,0.5);
  }
  .seeker-modal h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.7rem; font-weight: 500;
    line-height: 1.2; margin-bottom: 0.5rem;
    color: var(--ink);
  }
  .seeker-modal p.lede {
    font-size: 0.92rem; color: var(--ink-soft); line-height: 1.55;
    margin-bottom: 1.4rem;
  }
  .seeker-modal input[type=email] {
    width: 100%; padding: 0.85rem 1rem;
    background: var(--bg);
    border: 1px solid var(--line);
    border-radius: 6px;
    font-family: inherit; font-size: 1rem;
    color: var(--ink);
    margin-bottom: 1rem;
    transition: border-color 0.15s;
  }
  .seeker-modal input[type=email]:focus { outline: none; border-color: var(--brass); }
  .seeker-modal button.submit {
    width: 100%; padding: 0.9rem 1rem;
    background: var(--brass); color: #fff;
    border: 0; border-radius: 6px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 0.85rem; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s;
  }
  .seeker-modal button.submit:hover { background: var(--brass-bright); }
  .seeker-modal button.submit:disabled { opacity: 0.5; cursor: not-allowed; }
  .seeker-modal .cancel {
    margin-top: 0.85rem;
    background: transparent; border: 0;
    font-size: 0.82rem; color: var(--ink-faint);
    cursor: pointer;
  }
  .seeker-modal .cancel:hover { color: var(--ink-soft); }
  .seeker-modal .fine {
    margin-top: 1.2rem; font-size: 0.78rem; color: var(--ink-faint);
    line-height: 1.5;
  }
  .seeker-modal .hp { position: absolute; left: -10000px; opacity: 0; }
  .seeker-modal .success-state h2 { color: var(--brass); }
  .seeker-modal .success-state .check {
    font-size: 2.4rem; color: var(--brass);
    margin-bottom: 0.6rem; line-height: 1;
  }

  .seeker-toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(120%);
    background: var(--ink); color: var(--bg);
    padding: 0.85rem 1.4rem;
    border-radius: 6px;
    font-size: 0.88rem; font-weight: 500;
    box-shadow: 0 8px 24px -6px rgba(0,0,0,0.3);
    opacity: 0; transition: transform 0.3s, opacity 0.3s;
    z-index: 9999;
  }
  .seeker-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
  .seeker-toast a { color: var(--bg); border-bottom: 1px dotted var(--bg); margin-left: 10px; padding-bottom: 1px; text-decoration: none; }
  .reaction-row { text-align: center; margin-top: 3rem; margin-bottom: 0; }

  .material-symbols-outlined {
    font-family: 'Material Symbols Outlined';
    font-weight: normal; font-style: normal;
    font-size: 20px; line-height: 1;
    letter-spacing: normal; text-transform: none;
    display: inline-block; white-space: nowrap; word-wrap: normal;
    direction: ltr;
    -webkit-font-feature-settings: 'liga'; -webkit-font-smoothing: antialiased;
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    transition: font-variation-settings 0.2s, color 0.2s;
  }
  .reaction:hover .material-symbols-outlined,
  .reaction.active .material-symbols-outlined {
    font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
  }
  .qa-item .material-symbols-outlined { font-size: 17px; }

  .topics-line {
    margin-top: 3rem; text-align: center;
    font-size: 0.7rem; letter-spacing: 0.2em; color: var(--ink-faint);
    text-transform: uppercase; font-weight: 400;
  }

  .footer { text-align: center; padding: 4rem 2rem 3rem; border-top: 1px solid var(--line); }
  .footer-mark { margin-bottom: 0.75rem; }
  .footer-mark a {
    font-family: 'Xtreem', 'Poppins', sans-serif;
    font-style: normal; font-weight: 500;
    color: var(--ink);
    text-transform: lowercase;
    font-size: 40px; letter-spacing: -0.02em; line-height: 0.8;
    text-decoration: none; display: inline-block;
  }
  .footer-text { font-size: 0.8rem; color: var(--ink-faint); font-weight: 300; }
  .topic-link { color: var(--brass); text-decoration: none; border-bottom: 1px dotted var(--brass); transition: color 0.15s, border-bottom-color 0.15s; }
  .topic-link:hover { color: var(--brass-bright); border-bottom-color: var(--brass-bright); }
  .topic-chip { color: inherit; text-decoration: none; transition: color 0.15s; }
  .topic-chip:hover { color: var(--brass); }

  /* ── Poll widget ─────────────────────────────────────────────── */
  .poll-card {
    margin: 3rem 0; padding: 2rem 1.5rem;
    border: 1px solid var(--line-strong);
    border-radius: 10px;
    background: var(--bg-elevated);
  }
  .poll-eyebrow {
    font-family: 'Instrument Sans', sans-serif;
    font-size: 0.7rem; letter-spacing: 0.22em; color: var(--brass);
    text-transform: uppercase; font-weight: 600;
    margin-bottom: 0.6rem;
  }
  .poll-question {
    font-size: 1.2rem; font-weight: 500; line-height: 1.4;
    margin-bottom: 1.4rem;
  }
  .poll-options { display: flex; flex-direction: column; gap: 0.6rem; }
  .poll-option {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1rem;
    border: 1px solid var(--line);
    border-radius: 6px;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    background: var(--bg);
    user-select: none;
  }
  .poll-option:hover { border-color: var(--brass); }
  .poll-option input[type=radio] { accent-color: var(--brass); margin: 0; flex-shrink: 0; }
  .poll-option-text { flex: 1; font-size: 0.95rem; color: var(--ink); }
  .poll-option.selected { border-color: var(--brass); background: color-mix(in srgb, var(--teal) 5%, transparent); }
  .poll-submit {
    margin-top: 1.2rem;
    padding: 0.7rem 1.4rem;
    background: var(--brass); color: #fff; border: 0; border-radius: 6px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 0.78rem; letter-spacing: 0.18em; font-weight: 600; text-transform: uppercase;
    cursor: pointer; transition: background 0.15s;
  }
  .poll-submit:hover:not(:disabled) { background: var(--brass-bright); }
  .poll-submit:disabled { opacity: 0.5; cursor: not-allowed; }
  .poll-result-row {
    position: relative;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border: 1px solid var(--line);
    border-radius: 6px;
    overflow: hidden;
    background: var(--bg);
  }
  .poll-result-row.canonical { border-color: var(--brass); }
  .poll-result-bar {
    position: absolute; inset: 0;
    background: color-mix(in srgb, var(--teal) 12%, transparent);
    width: 0%; transition: width 0.6s ease-out;
    z-index: 0;
  }
  .poll-result-row.canonical .poll-result-bar { background: color-mix(in srgb, var(--teal) 20%, transparent); }
  .poll-result-content {
    position: relative; z-index: 1;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 0.9rem;
  }
  .poll-result-content .label { color: var(--ink); }
  .poll-result-content .canonical-marker {
    margin-left: 0.5rem; color: var(--brass);
    font-size: 0.7rem; letter-spacing: 0.18em;
    text-transform: uppercase; font-weight: 600;
  }
  .poll-result-content .pct { font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 0.8rem; color: var(--ink-soft); }
  .poll-explainer {
    margin-top: 1.2rem; padding-top: 1.2rem;
    border-top: 1px solid var(--line);
    font-size: 0.95rem; color: var(--ink); line-height: 1.7;
  }
  .poll-explainer .ref { display: block; margin-bottom: 0.4rem;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 0.7rem; letter-spacing: 0.18em; color: var(--brass);
    text-transform: uppercase; font-weight: 600;
  }
  .poll-total { font-size: 0.75rem; color: var(--ink-faint); margin-top: 0.8rem; }
  .poll-hp { position: absolute; left: -10000px; opacity: 0; pointer-events: none; }
  .sermon-source {
    font-size: 0.75rem; color: var(--ink-faint);
    letter-spacing: 0.04em; margin-top: 0.6rem;
  }
  .sermon-source a {
    color: var(--ink-soft); border-bottom: 1px dotted var(--ink-faint);
    text-decoration: none; transition: color 0.15s, border-color 0.15s;
  }
  .sermon-source a:hover { color: var(--teal); border-bottom-color: var(--teal); }

  /* ─── Share modal (friendly Copy/Email/Text — for non-techy users) ────────── */
  .share-modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(10,16,20,0.72);
    backdrop-filter: blur(3px);
    z-index: 9997;
    align-items: center; justify-content: center;
    padding: 1.5rem;
  }
  .share-modal-overlay.open { display: flex; }
  .share-modal-card {
    background: var(--bg-elevated);
    border: 1px solid var(--line-strong);
    border-radius: 12px;
    max-width: 420px; width: 100%;
    padding: 2rem 1.75rem;
    box-shadow: 0 20px 60px -10px rgba(0,0,0,0.5);
  }
  .share-modal-card h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.55rem; font-weight: 500; line-height: 1.2;
    color: var(--ink); text-align: center; margin-bottom: 1rem;
  }
  .share-qa-preview {
    font-size: 0.92rem; color: var(--ink-soft); font-style: italic;
    text-align: center; line-height: 1.5;
    padding: 0.9rem 1rem; margin-bottom: 1.4rem;
    background: rgba(0,0,0,0.04); border-radius: 6px;
    border-left: 2px solid var(--brass);
  }
  .share-options { display: flex; flex-direction: column; gap: 0.6rem; }
  .share-option {
    display: flex; align-items: center; gap: 0.9rem;
    padding: 0.95rem 1.1rem;
    background: var(--bg);
    border: 1px solid var(--line);
    border-radius: 8px;
    color: var(--ink); cursor: pointer;
    font-family: inherit; font-size: 0.96rem; font-weight: 500;
    text-decoration: none; text-align: left;
    transition: border-color 0.15s, background 0.15s, transform 0.1s;
    width: 100%;
  }
  .share-option:hover { border-color: var(--brass); background: color-mix(in srgb, var(--brass) 6%, transparent); }
  .share-option:active { transform: scale(0.98); }
  .share-option .icon-wrap {
    width: 36px; height: 36px; border-radius: 50%;
    background: color-mix(in srgb, var(--brass) 12%, transparent); color: var(--brass);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .share-option .icon-wrap svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linejoin: round; stroke-linecap: round; }
  .share-option .label-block { flex: 1; min-width: 0; }
  .share-option .opt-title { font-weight: 500; font-size: 0.98rem; line-height: 1.3; display: block; }
  .share-option .opt-sub { font-size: 0.78rem; color: var(--ink-soft); margin-top: 2px; line-height: 1.3; display: block; }
  .share-option .chev { color: var(--ink-faint); font-size: 1.2rem; line-height: 1; }
  .share-option.success { border-color: var(--brass); background: color-mix(in srgb, var(--brass) 10%, transparent); }
  .share-option.success .icon-wrap { background: var(--brass); color: var(--bg); }
  .share-more-toggle {
    margin-top: 0.8rem; padding-top: 0.8rem;
    border-top: 1px dashed var(--line); text-align: center;
  }
  .share-more-toggle button {
    background: transparent; border: 0;
    font-family: inherit; font-size: 0.82rem;
    color: var(--ink-soft); cursor: pointer;
    padding: 0.4rem 0.8rem; border-radius: 4px;
  }
  .share-more-toggle button:hover { color: var(--brass); }
  .share-modal-cancel {
    margin-top: 1rem;
    width: 100%; padding: 0.7rem;
    background: transparent; border: 0;
    color: var(--ink-faint); font-family: inherit; font-size: 0.85rem;
    cursor: pointer; border-radius: 6px;
    transition: color 0.15s;
  }
  .share-modal-cancel:hover { color: var(--ink-soft); }
</style>
@include('partials.find-peace-vars')
</head>
<body>

@php
$linkifiedTopicSlugs = [];
$topicMap = \App\Models\PeaceTopic::has('sermons')->orderByRaw('CHAR_LENGTH(name) DESC')->pluck('slug', 'name')->all();
$linkifyTopics = function ($html) use ($topicMap, &$linkifiedTopicSlugs) {
    foreach ($topicMap as $name => $slug) {
        if (in_array($slug, $linkifiedTopicSlugs, true)) continue;
        // Word-boundary, case-insensitive. Naive skip: avoid matches that are already inside an <a> or <span> tag by requiring the match to NOT be immediately preceded by an open <a/<span> tag with no closing in between. preg_replace handles 1 occurrence at a time, so we can also bail if the matched offset is within an existing tag boundary by counting.
        $pattern = '/\\b(' . preg_quote($name, '/') . ')\\b/i';
        $newHtml = preg_replace_callback($pattern, function ($m) use ($html, $slug, &$linkifiedTopicSlugs) {
            if (in_array($slug, $linkifiedTopicSlugs, true)) return $m[0];
            // Skip if we're inside an existing <a> or <span> tag.
            $offset = $m[1] ?? null;  // not directly available; use static counter approach below
            return $m[0];  // placeholder; real logic below
        }, $html, 1);
        // The above callback can't easily access offset. Use a different strategy:
        // Walk the html token-by-token: split on tags, only match in text nodes.
        $parts = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $found = false;
        $insideLink = 0; // track <a>...</a> nesting
        $insideVerse = 0; // track <span class="verse-link"> nesting
        foreach ($parts as $i => $part) {
            if ($found) break;
            if (preg_match('/^<\\/a\\b/i', $part)) { $insideLink = max(0, $insideLink - 1); continue; }
            if (preg_match('/^<a\\b/i', $part)) { $insideLink++; continue; }
            if (preg_match('/^<\\/span\\b/i', $part) && $insideVerse > 0) { $insideVerse--; continue; }
            if (preg_match('/^<span[^>]*verse-link/i', $part)) { $insideVerse++; continue; }
            if (preg_match('/^</', $part)) continue; // any other tag
            if ($insideLink || $insideVerse) continue;
            // text node — try to match
            $replaced = preg_replace_callback($pattern, function ($mm) use ($slug, &$found, &$linkifiedTopicSlugs) {
                if ($found) return $mm[0];
                $found = true;
                $linkifiedTopicSlugs[] = $slug;
                return '<a class="topic-link" href="' . route('find-peace.topic', $slug) . '">' . $mm[1] . '</a>';
            }, $part, 1);
            if ($replaced !== null && $replaced !== $part) {
                $parts[$i] = $replaced;
            }
        }
        if ($found) $html = implode('', $parts);
    }
    return $html;
};
$linkifyScripture = function ($text) use ($linkifyTopics) {
    $escaped = e($text);
    $pattern = '/\b((?:[123]\s+)?(?:Genesis|Exodus|Leviticus|Numbers|Deuteronomy|Joshua|Judges|Ruth|Samuel|Kings|Chronicles|Ezra|Nehemiah|Esther|Job|Psalm|Psalms|Proverbs|Ecclesiastes|Song(?:\sof\s(?:Solomon|Songs))?|Isaiah|Jeremiah|Lamentations|Ezekiel|Daniel|Hosea|Joel|Amos|Obadiah|Jonah|Micah|Nahum|Habakkuk|Zephaniah|Haggai|Zechariah|Malachi|Matthew|Mark|Luke|John|Acts|Romans|Corinthians|Galatians|Ephesians|Philippians|Colossians|Thessalonians|Timothy|Titus|Philemon|Hebrews|James|Peter|Jude|Revelation))\s+(\d+)(?::(\d+)(?:-(\d+))?)?\b/';
    $withScripture = preg_replace_callback($pattern, function ($m) {
        $ref = trim($m[0]);
        return '<span class="verse-link" data-ref="' . htmlspecialchars($ref, ENT_QUOTES) . '" tabindex="0">' . $ref . '</span>';
    }, $escaped);
    return $linkifyTopics($withScripture);
};
@endphp

<div class="mark-top"><a href="{{ route('find-peace.index') }}">shalom</a></div>

<section class="sermon-page">
  <div class="container">
    <div class="sermon-header">
      <div class="sermon-meta-top">SABBATH · {{ strtoupper($sermon->sermon_date->format('M j')) }}</div>
      <h1 class="sermon-title">{{ $sermon->title }}</h1>
      @if($sermon->speaker)
        <p class="sermon-meta">{{ $sermon->speaker }}</p>
      @endif
      @if($sermon->youtube_video_id)
        <p class="sermon-source">
          Originally streamed on
          <a href="https://www.youtube.com/watch?v={{ $sermon->youtube_video_id }}" target="_blank" rel="noopener">YouTube</a>
        </p>
      @endif
    </div>

    <p class="heart">{!! $linkifyScripture($sermon->heart_line) !!}</p>

    @if($sermon->audio_url)
      <div class="player" id="listen">
        <audio id="sermon-audio" preload="metadata" src="{{ $sermon->audio_url }}"></audio>
        <div class="player-controls">
          <button class="skip-btn" data-seek="-15" type="button" aria-label="Back 15 seconds">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 4v4h4"/></svg>
            <span class="skip-num">15s</span>
          </button>
          <button class="play-btn" id="play-btn" type="button" aria-label="Play">
            <svg class="icon-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="6,4 20,12 6,20"/></svg>
            <svg class="icon-pause" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
          </button>
          <button class="skip-btn" data-seek="15" type="button" aria-label="Forward 15 seconds">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 4v4h-4"/></svg>
            <span class="skip-num">15s</span>
          </button>
        </div>
        <div class="player-title">
          {{ $sermon->title }}@if($sermon->speaker) <span class="player-title-sep">·</span> <span class="player-title-speaker">{{ $sermon->speaker }}</span>@endif
        </div>
        <div class="progress-wrap" id="progress-wrap" role="slider" aria-label="Audio progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" tabindex="0">
          <div class="progress-tip" id="progress-tip">0:00</div>
          <div class="progress-bar">
            <div class="progress-buffer" id="progress-buffer"></div>
            <div class="progress-fill" id="progress-fill"></div>
            <div class="progress-handle" id="progress-handle"></div>
          </div>
        </div>
        <div class="player-meta">
          <span class="player-time current" id="time-current">0:00</span>
          <div class="speed-pills" id="speed-pills" role="group" aria-label="Playback speed">
            <button data-speed="0.75" type="button">0.75×</button>
            <button data-speed="1" type="button" class="active">1×</button>
            <button data-speed="1.25" type="button">1.25×</button>
            <button data-speed="1.5" type="button">1.5×</button>
            <button data-speed="2" type="button">2×</button>
          </div>
          <span class="player-time duration" id="time-duration">--:--</span>
        </div>
        <div class="player-utils" style="gap: 1.5rem;">
          <a href="{{ $sermon->audio_url }}" download>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download
          </a>
          <a href="#" id="player-share-btn" role="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            <span id="player-share-label">Share</span>
          </a>
        </div>
      </div>
    @endif

    @if($sermon->qaPairs->isNotEmpty())
    <section class="qa-section" id="questions">
      <div class="qa-section-title">Questions this message meets</div>
      <ul class="qa-list">
        @foreach($sermon->qaPairs->take(5) as $qa)
          <li class="qa-item" id="qa-{{ $qa->id }}" data-qa-id="{{ $qa->id }}">
            <details>
              <summary>
                <div class="qa-summary-text">
                  <p class="qa-q">{!! $linkifyScripture($qa->question) !!}</p>
                  <p class="qa-preview">{{ \Illuminate\Support\Str::limit($qa->answer, 110) }}</p>
                </div>
                <span class="qa-chev"></span>
              </summary>
              <div class="qa-body">
                <p class="qa-a-full">{!! $linkifyScripture($qa->answer) !!}</p>
                <div class="qa-actions"
                     data-qa-id="{{ $qa->id }}"
                     data-qa-question="{{ $qa->question }}"
                     data-qa-url="{{ url('/find-peace/' . $sermon->slug . '?qa=' . $qa->id) }}">
                  <div class="qa-actions-left">
                    <button class="action-btn helped" data-react="qa_pair" data-id="{{ $qa->id }}" aria-pressed="false" aria-label="Mark this Q&amp;A as helpful">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M7 11v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1h4z"/>
                        <path d="M7 11h4l3.5-7.5a2 2 0 0 1 3 1.5l-1 6h5.5a2 2 0 0 1 1.9 2.6l-2.4 8a2 2 0 0 1-1.9 1.4H7z"/>
                      </svg>
                      <span class="lbl">this helped</span>
                    </button>
                  </div>
                  <div class="qa-actions-right">
                    <button class="action-btn save{{ in_array($qa->id, $savedQaIds ?? []) ? ' active' : '' }}" data-qa-id="{{ $qa->id }}" aria-pressed="{{ in_array($qa->id, $savedQaIds ?? []) ? 'true' : 'false' }}" aria-label="Save this Q&amp;A for later">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 3.5h14v18l-7-5-7 5z"/>
                      </svg>
                      <span class="lbl">{{ in_array($qa->id, $savedQaIds ?? []) ? 'saved' : 'save' }}</span>
                    </button>
                    <button class="action-btn share" data-qa-id="{{ $qa->id }}" aria-label="Share this Q&amp;A">
                      <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="18" cy="5" r="2.6"/><circle cx="6" cy="12" r="2.6"/><circle cx="18" cy="19" r="2.6"/>
                        <line x1="8.4" y1="13.3" x2="15.5" y2="17.7"/><line x1="15.5" y1="6.3" x2="8.4" y2="10.7"/>
                      </svg>
                      <span class="lbl">share</span>
                    </button>
                  </div>
                </div>
              </div>
            </details>
          </li>
        @endforeach
      </ul>
    </section>
    @endif

    @if($sermon->summary_paragraphs)
    <details class="section-accordion">
      <summary><span class="section-acc-chev"></span><span>Read the heart of this message</span></summary>
      <div class="section-acc-body">
        @foreach($sermon->summary_paragraphs as $i => $paragraph)
          <p @if($i === count($sermon->summary_paragraphs) - 1) class="muted" @endif>{!! $linkifyScripture($paragraph) !!}</p>
        @endforeach
      </div>
    </details>
    @endif

    @if($sermon->scriptures->isNotEmpty())
    <details class="section-accordion">
      <summary><span class="section-acc-chev"></span><span>The Scriptures Pastor opened</span></summary>
      <div class="section-acc-body">
        <ul class="scripture-list">
          @foreach($sermon->scriptures as $scripture)
            <li>
              <div class="scripture-ref">{{ strtoupper($scripture->reference_display) }} · {{ $scripture->translation }}</div>
              <div class="scripture-text">{{ $scripture->verse_text }}</div>
            </li>
          @endforeach
        </ul>
      </div>
    </details>
    @endif

    @if(isset($related) && $related->isNotEmpty())
    <details class="section-accordion">
      <summary><span class="section-acc-chev"></span><span>If this spoke to you, also hear…</span></summary>
      <div class="section-acc-body">
        <ul class="related-list">
          @foreach($related as $r)
            <li>
              <div class="scripture-ref">{{ strtoupper($r->sermon_date->format('M j')) }} · {{ $r->speaker }}</div>
              <a href="{{ route('find-peace.show', $r->slug) }}">"{{ $r->title }}" — {{ \Illuminate\Support\Str::limit($r->heart_line, 100) }}</a>
            </li>
          @endforeach
        </ul>
      </div>
    </details>
    @endif

    <div class="reaction-row">
      <button class="reaction" data-react="sermon" data-id="{{ $sermon->id }}" aria-pressed="false">
        <svg viewBox="0 0 24 24" aria-hidden="true" style="width:18px;height:18px;display:inline-block;vertical-align:-3px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linejoin:round;stroke-linecap:round;">
          <path d="M7 11v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1h4z"/>
          <path d="M7 11h4l3.5-7.5a2 2 0 0 1 3 1.5l-1 6h5.5a2 2 0 0 1 1.9 2.6l-2.4 8a2 2 0 0 1-1.9 1.4H7z"/>
        </svg>
        <span class="reaction-label" style="margin-left:8px;">this message helped</span>
      </button>
    </div>

    @if($sermon->topics->isNotEmpty())
      <div class="topics-line">
        @foreach($sermon->topics as $topic){{ $loop->first ? "" : " · " }}<a href="{{ route('find-peace.topic', $topic->slug) }}" class="topic-chip">{{ $topic->name }}</a>@endforeach
      </div>
    @endif
  </div>
</section>

<footer class="footer">
  <div class="footer-mark"><a href="{{ route('find-peace.index') }}">shalom</a></div>
  <p class="footer-text">Peace, wellness, and freedom — for every member of our community.</p>
</footer>

<script>
// Audio player
(function () {
  const audio = document.getElementById('sermon-audio');
  const player = document.querySelector('.player');
  if (!audio || !player) return;
  const playBtn = document.getElementById('play-btn');
  const fill = document.getElementById('progress-fill');
  const buffer = document.getElementById('progress-buffer');
  const handle = document.getElementById('progress-handle');
  const wrap = document.getElementById('progress-wrap');
  const tCur = document.getElementById('time-current');
  const tDur = document.getElementById('time-duration');
  const fmt = s => { if (!isFinite(s) || s < 0) return '0:00'; const m = Math.floor(s/60); const ss = Math.floor(s%60).toString().padStart(2,'0'); return m+':'+ss; };
  playBtn.addEventListener('click', () => { audio.paused ? audio.play() : audio.pause(); });
  audio.addEventListener('play',  () => { player.classList.add('playing'); playBtn.setAttribute('aria-label','Pause'); });
  audio.addEventListener('pause', () => { player.classList.remove('playing'); playBtn.setAttribute('aria-label','Play'); });
  audio.addEventListener('ended', () => { player.classList.remove('playing'); });
  audio.addEventListener('loadedmetadata', () => { tDur.textContent = fmt(audio.duration); });
  audio.addEventListener('timeupdate', () => {
    const p = (audio.currentTime / audio.duration) * 100 || 0;
    fill.style.width = p+'%'; handle.style.left = p+'%';
    tCur.textContent = fmt(audio.currentTime);
    wrap.setAttribute('aria-valuenow', Math.round(p));
  });
  audio.addEventListener('progress', () => {
    if (audio.buffered.length > 0) {
      const end = audio.buffered.end(audio.buffered.length - 1);
      buffer.style.width = ((end / audio.duration) * 100 || 0) + '%';
    }
  });
  document.querySelectorAll('.skip-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const delta = parseInt(btn.dataset.seek, 10);
      audio.currentTime = Math.max(0, Math.min(audio.duration||0, audio.currentTime + delta));
    });
  });
  let scrubbing = false;
  const tip = document.getElementById('progress-tip');
  function timeAtX(clientX) {
    const rect = wrap.getBoundingClientRect();
    const ratio = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
    return { ratio, time: ratio * (audio.duration || 0), x: clientX - rect.left };
  }
  function seekFromX(clientX) {
    const t = timeAtX(clientX);
    audio.currentTime = t.time;
  }
  function showTipAt(clientX) {
    if (!tip) return;
    const t = timeAtX(clientX);
    tip.textContent = fmt(t.time);
    tip.style.left = t.x + 'px';
  }
  wrap.addEventListener('pointerdown', e => { scrubbing = true; wrap.classList.add('scrubbing'); seekFromX(e.clientX); showTipAt(e.clientX); wrap.setPointerCapture?.(e.pointerId); });
  wrap.addEventListener('pointermove', e => { showTipAt(e.clientX); if (scrubbing) seekFromX(e.clientX); });
  wrap.addEventListener('pointerup',   e => { scrubbing = false; wrap.classList.remove('scrubbing'); wrap.releasePointerCapture?.(e.pointerId); });
  // Fine-grained keyboard seek when scrubber is focused.
  // Arrow ±5s, Shift+Arrow ±1s, Home/End jump to start/end, PageUp/PageDown ±30s
  wrap.addEventListener('keydown', e => {
    let delta = 0;
    if (e.key === 'ArrowRight') delta = e.shiftKey ? 1 : 5;
    else if (e.key === 'ArrowLeft') delta = e.shiftKey ? -1 : -5;
    else if (e.key === 'PageUp') delta = 30;
    else if (e.key === 'PageDown') delta = -30;
    else if (e.key === 'Home') { audio.currentTime = 0; e.preventDefault(); return; }
    else if (e.key === 'End') { audio.currentTime = audio.duration || 0; e.preventDefault(); return; }
    else return;
    e.preventDefault();
    audio.currentTime = Math.max(0, Math.min(audio.duration || 0, audio.currentTime + delta));
  });
  document.querySelectorAll('#speed-pills button').forEach(b => {
    b.addEventListener('click', () => {
      audio.playbackRate = parseFloat(b.dataset.speed);
      document.querySelectorAll('#speed-pills button').forEach(x => x.classList.remove('active'));
      b.classList.add('active');
    });
  });
})();


// Bible verse hover tooltips — bible-api.com (same as main site)
(function () {
  const cache = {};
  let tip;
  let activeLink = null;
  function ensureTip() {
    if (tip) return tip;
    tip = document.createElement('div');
    tip.className = 'verse-tooltip';
    tip.setAttribute('role', 'tooltip');
    document.body.appendChild(tip);
    return tip;
  }
  function showAt(link) {
    const t = ensureTip();
    const rect = link.getBoundingClientRect();
    const top = rect.bottom + window.scrollY + 8;
    const tipMax = 340;
    let left = rect.left + window.scrollX;
    if (left + tipMax > window.innerWidth - 12) left = window.innerWidth - tipMax - 12;
    if (left < 12) left = 12;
    t.style.top  = top + 'px';
    t.style.left = left + 'px';
    t.classList.add('show');
    activeLink = link;
  }
  function hide() {
    if (tip) tip.classList.remove('show');
    activeLink = null;
  }
  async function loadAndShow(link) {
    const ref = link.dataset.ref;
    if (!ref) return;
    showAt(link);
    const t = ensureTip();
    const isChapterOnly = !ref.includes(':');
    const fetchRef  = isChapterOnly ? (ref + ':1') : ref;
    const moreLabel = isChapterOnly ? 'Read full chapter →' : 'Read more →';
    const moreLink  = '<a class="verse-tooltip-more" href="https://www.biblegateway.com/passage/?search=' + encodeURIComponent(ref) + '&version=KJV" target="_blank" rel="noopener">' + moreLabel + '</a>';

    if (cache[ref] !== undefined) {
      if (cache[ref] === null) { hide(); return; }
      t.innerHTML = '<span class="verse-tooltip-ref">' + ref + ' · KJV</span>' + cache[ref] + moreLink;
      return;
    }
    t.innerHTML = '<span class="verse-tooltip-loading">Loading…</span>';
    try {
      const resp = await fetch('https://bible-api.com/' + encodeURIComponent(fetchRef) + '?translation=kjv');
      const data = await resp.json();
      let txt = (data && data.text) ? data.text.trim().replace(/\s+/g, ' ') : null;
      if (txt && isChapterOnly) {
        txt = '<em style="color:var(--ink-soft);font-size:0.78rem;">[Verse 1]</em> ' + txt;
      }
      cache[ref] = txt;
      if (!txt) { hide(); return; }
      if (activeLink === link) {
        t.innerHTML = '<span class="verse-tooltip-ref">' + ref + ' · KJV</span>' + txt + moreLink;
      }
    } catch (e) { cache[ref] = null; hide(); }
  }
  // Hover (desktop)
  document.addEventListener('mouseover', e => { const l = e.target.closest('.verse-link'); if (l) loadAndShow(l); });
  document.addEventListener('mouseout', e => {
    const l = e.target.closest('.verse-link');
    if (!l) return;
    setTimeout(() => {
      if (tip && !tip.matches(':hover') && !document.querySelector('.verse-link:hover')) hide();
    }, 250);
  });
  // Hide when cursor leaves the tooltip itself
  document.addEventListener('mouseleave', e => {
    if (tip && tip === e.target) hide();
  }, true);
  // Focus (keyboard)
  document.addEventListener('focusin',  e => { const l = e.target.closest('.verse-link'); if (l) loadAndShow(l); });
  document.addEventListener('focusout', e => { const l = e.target.closest('.verse-link'); if (l) hide(); });
  // Tap (mobile) — toggle on tap
  document.addEventListener('click', e => {
    const l = e.target.closest('.verse-link');
    if (l) { e.preventDefault(); activeLink === l ? hide() : loadAndShow(l); return; }
    if (tip && tip.contains(e.target)) return;
    hide();
  });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') hide(); });
})();

// Reactions UI stub
document.querySelectorAll('.reaction').forEach(btn => {
  btn.addEventListener('click', () => {
    const active = btn.classList.toggle('active');
    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    const lbl = btn.querySelector('.reaction-label');
    if (active) {
      lbl.textContent = 'you found this helpful';
    } else {
      lbl.textContent = btn.dataset.react === 'sermon' ? 'this message helped' : 'helped';
    }
  });
});

// Auto-spotlight + open Q&A from URL: ?qa=123
(function () {
  const qaId = new URLSearchParams(window.location.search).get('qa');
  if (!qaId) return;
  const target = document.getElementById('qa-' + qaId);
  if (!target) return;
  target.classList.add('spotlight');
  const det = target.querySelector('details');
  if (det) det.open = true;
  setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'center' }), 200);
})();

  // Share audio / sermon page
  (function(){
    const btn = document.getElementById('player-share-btn');
    const lbl = document.getElementById('player-share-label');
    if (!btn) return;
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const url = window.location.href;
      const title = document.title;
      try {
        if (navigator.share) {
          await navigator.share({ title, url });
          return;
        }
      } catch(err){ /* user cancelled */ return; }
      try {
        await navigator.clipboard.writeText(url);
        const old = lbl.textContent;
        lbl.textContent = 'Link copied';
        setTimeout(() => lbl.textContent = old, 1800);
      } catch(err) {
        prompt('Copy this link:', url);
      }
    });
  })();

  // Poll widget submit handler
  (function () {
    document.querySelectorAll('.poll-form').forEach(form => {
      const card = form.closest('.poll-card');
      const submitBtn = form.querySelector('.poll-submit');
      const optionLabels = form.querySelectorAll('.poll-option');
      const resultsDiv = card.querySelector('.poll-results');
      const rowsDiv = card.querySelector('.poll-result-rows');
      const totalDiv = card.querySelector('.poll-total');
      form.querySelectorAll('input[type=radio]').forEach(r => {
        r.addEventListener('change', () => {
          submitBtn.disabled = false;
          optionLabels.forEach(l => l.classList.toggle('selected', l.querySelector('input').checked));
        });
      });
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting…';
        try {
          const fd = new FormData(form);
          const resp = await fetch('/find-peace/poll', {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || (form.querySelector('input[name=_token]')?.value || ''),
            },
            body: fd,
          });
          const data = await resp.json();
          if (!data.ok) throw new Error(data.error || 'Submission failed');
          form.style.display = 'none';
          resultsDiv.hidden = false;
          rowsDiv.innerHTML = '';
          (data.results || []).forEach(opt => {
            const row = document.createElement('div');
            row.className = 'poll-result-row' + (opt.is_canonical ? ' canonical' : '');
            const escText = String(opt.text).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));
            row.innerHTML = '<div class="poll-result-bar" style="width: ' + opt.percent + '%;"></div>' +
              '<div class="poll-result-content">' +
                '<span class="label">' + escText + (opt.is_canonical ? '<span class="canonical-marker">scripture answer</span>' : '') + '</span>' +
                '<span class="pct">' + opt.percent + '% · ' + opt.count + '</span>' +
              '</div>';
            rowsDiv.appendChild(row);
          });
          totalDiv.textContent = data.total + ' ' + (data.total === 1 ? 'person has' : 'people have') + ' answered this' + (data.already_voted ? ' (your earlier answer was kept)' : '') + '.';
        } catch (err) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'See what scripture says →';
          alert('Sorry — could not submit your answer. ' + (err.message || ''));
        }
      });
    });
  })();

// ─── Seeker signup modal + Save Q&A flow ──────────────────────────────
(function () {
  const SEEKER_ID = document.querySelector('meta[name="peace-seeker-id"]')?.content || null;  // server tells us if already signed in

  const modal = document.getElementById('seeker-modal');
  const card  = document.getElementById('seeker-modal-card');
  const form  = document.getElementById('seeker-form');
  const emailInput = document.getElementById('seeker-email');
  const submitBtn = document.getElementById('seeker-submit');
  const cancelBtn = document.getElementById('seeker-cancel');
  const openedAtInput = document.getElementById('seeker-opened-at');
  const qaIdInput = document.getElementById('seeker-qa-id');
  const toast = document.getElementById('seeker-toast');

  function openModal(qaId) {
    if (!modal) return;
    qaIdInput.value = qaId || '';
    openedAtInput.value = Date.now();
    modal.classList.add('open');
    setTimeout(() => emailInput.focus(), 80);
  }
  function closeModal() {
    if (!modal) return;
    modal.classList.remove('open');
    // reset card content (in case it showed success state)
    card.querySelector('h2').textContent = 'Save this for later.';
    card.querySelector('p.lede').style.display = '';
    form.style.display = '';
    cancelBtn.style.display = '';
    form.reset();
  }
  function showSuccess(email) {
    form.style.display = 'none';
    cancelBtn.textContent = 'Got it';
    const lede = card.querySelector('p.lede');
    lede.style.display = 'none';
    card.querySelector('h2').innerHTML = '✓<br>Check your inbox.';
    card.querySelector('h2').style.color = 'var(--brass)';
    const note = document.createElement('p');
    note.className = 'lede';
    note.style.cssText = 'display:block;color:var(--ink-soft);font-size:0.92rem;margin-top:0.6rem;';
    note.innerHTML = `A one-tap sign-in link is on its way to <strong>${escapeHtml(email)}</strong>. It expires in 30 minutes.`;
    card.insertBefore(note, form);
  }
  function showToast(msg, linkText, linkHref) {
    if (!toast) return;
    toast.innerHTML = escapeHtml(msg) + (linkText ? ` <a href="${linkHref}">${escapeHtml(linkText)}</a>` : '');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4500);
  }
  function escapeHtml(s) {
    return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  // Save button click
  document.querySelectorAll('.save-qa').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const qaId = btn.dataset.qaId;
      if (SEEKER_ID) {
        // Logged-in → AJAX toggle
        try {
          const resp = await fetch('/peace/saved/' + encodeURIComponent(qaId), {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
              'Accept': 'application/json',
            },
          });
          const data = await resp.json();
          if (data.ok) {
            btn.classList.toggle('saved', !!data.saved);
            btn.querySelector('.save-label').textContent = data.saved ? 'Saved' : 'Save';
            btn.setAttribute('aria-pressed', data.saved ? 'true' : 'false');
            showToast(data.saved ? 'Saved.' : 'Removed.', 'View saved →', '/find-peace/saved');
          }
        } catch (err) { console.error(err); }
      } else {
        // Not logged in → open email modal with this Q&A as intent
        openModal(qaId);
      }
    });
  });

  // Modal form submit
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending…';
      const email = emailInput.value.trim();
      const qaId = qaIdInput.value;
      const intent = qaId ? { type: 'save_qa', qa_id: parseInt(qaId, 10) } : { type: 'direct' };
      try {
        const fd = new FormData(form);
        const payload = {
          email,
          intent,
          source_url: window.location.pathname + window.location.search,
        };
        // The hidden honeypot + time-trap fields come from the form
        const formPayload = {
          ...payload,
          website: fd.get('website') || '',
          form_opened_at: openedAtInput.value,
        };
        const resp = await fetch('/peace/auth/request', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(formPayload),
        });
        const data = await resp.json();
        if (data.ok) {
          showSuccess(email);
        } else {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Send link →';
          alert(data.message || data.error || 'Something went wrong. Try again.');
        }
      } catch (err) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send link →';
        alert('Network error. Try again.');
      }
    });
  }

  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal && modal.classList.contains('open')) closeModal(); });

  // Flash message from server-side redirect — show as toast
  const flash = document.querySelector('meta[name="peace-flash"]')?.content;
  if (flash) showToast(flash);
})();

// ─── Q&A action row: helped / save / share ────────────────────────────────
(function () {
  const SEEKER_ID = document.querySelector('meta[name="peace-seeker-id"]')?.content || null;
  const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

  function showToast(msg, linkText, linkHref) {
    const toast = document.getElementById('seeker-toast');
    if (!toast) return;
    toast.innerHTML = escapeHtml(msg) + (linkText ? ` <a href="${linkHref}">${escapeHtml(linkText)}</a>` : '');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4500);
  }
  function escapeHtml(s) {
    return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  // ── SAVE button click ──────────────────────────────────────────────
  document.querySelectorAll('.action-btn.save').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      const qaId = btn.dataset.qaId;
      if (SEEKER_ID) {
        try {
          const resp = await fetch('/peace/saved/' + encodeURIComponent(qaId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
          });
          const data = await resp.json();
          if (data.ok) {
            btn.classList.toggle('active', !!data.saved);
            btn.querySelector('.lbl').textContent = data.saved ? 'saved' : 'save';
            btn.setAttribute('aria-pressed', data.saved ? 'true' : 'false');
            showToast(data.saved ? 'Saved.' : 'Removed.', 'View saved →', '/find-peace/saved');
          }
        } catch (err) { console.error(err); }
      } else {
        // open email signup modal with save intent
        if (typeof window.openSeekerModal === 'function') {
          window.openSeekerModal(qaId);
        } else {
          // fallback: trigger existing modal by clicking dummy
          const modal = document.getElementById('seeker-modal');
          const qaIdInput = document.getElementById('seeker-qa-id');
          const openedAtInput = document.getElementById('seeker-opened-at');
          if (modal) {
            qaIdInput.value = qaId;
            openedAtInput.value = Date.now();
            modal.classList.add('open');
            setTimeout(() => document.getElementById('seeker-email').focus(), 80);
          }
        }
      }
    });
  });

  // ── SHARE button click → open share modal ──────────────────────────
  const shareModal   = document.getElementById('share-modal');
  const shareCancel  = document.getElementById('share-cancel');
  const shareMoreBtn = document.getElementById('share-more-btn');
  const sharePreview = document.getElementById('share-qa-preview');
  const shareOptions = document.getElementById('share-options');
  let currentShareData = null;  // { question, url, body }

  document.querySelectorAll('.action-btn.share').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const wrap = btn.closest('.qa-actions');
      if (!wrap) return;
      const question = wrap.dataset.qaQuestion || '';
      const url = wrap.dataset.qaUrl || window.location.href;
      const body = `Question: "${question}" — read the answer at ${url}`;
      currentShareData = { question, url, body };
      sharePreview.textContent = '"' + question + '"';
      // Reset the Copy button state if it was previously success
      const copyBtn = shareOptions.querySelector('[data-share-action="copy"]');
      if (copyBtn) {
        copyBtn.classList.remove('success');
        const it = copyBtn.querySelector('.opt-title'); if (it) it.textContent = 'Copy the link';
        const su = copyBtn.querySelector('.opt-sub'); if (su) su.textContent = 'Paste it anywhere — text, group chat, anywhere';
        const icon = copyBtn.querySelector('.icon-wrap svg');
        if (icon) icon.outerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
      }
      // Update email/sms hrefs
      const emailLink = shareOptions.querySelector('[data-share-action="email"]');
      if (emailLink) emailLink.href = `mailto:?subject=${encodeURIComponent('A question worth sitting with')}&body=${encodeURIComponent(body)}`;
      const smsLink = shareOptions.querySelector('[data-share-action="sms"]');
      if (smsLink) smsLink.href = `sms:?body=${encodeURIComponent(body)}`;
      shareModal.classList.add('open');
    });
  });

  // Copy button
  shareOptions?.querySelector('[data-share-action="copy"]')?.addEventListener('click', async () => {
    if (!currentShareData) return;
    const copyBtn = shareOptions.querySelector('[data-share-action="copy"]');
    try {
      await navigator.clipboard.writeText(currentShareData.body);
    } catch (err) {
      // Fallback for old browsers / insecure contexts
      const ta = document.createElement('textarea');
      ta.value = currentShareData.body;
      document.body.appendChild(ta); ta.select();
      try { document.execCommand('copy'); } catch (e) {}
      document.body.removeChild(ta);
    }
    copyBtn.classList.add('success');
    const title = copyBtn.querySelector('.opt-title');
    const sub = copyBtn.querySelector('.opt-sub');
    if (title) title.textContent = 'Copied ✓ Now paste it anywhere';
    if (sub)   sub.textContent   = 'Open Messages, Mail, or any app — long-press → Paste';
    const iconSvg = copyBtn.querySelector('.icon-wrap svg');
    if (iconSvg) iconSvg.outerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';
  });

  // More options → native share fallback
  shareMoreBtn?.addEventListener('click', async () => {
    if (!currentShareData) return;
    if (navigator.share) {
      try {
        await navigator.share({ title: currentShareData.question, text: currentShareData.body, url: currentShareData.url });
      } catch (err) {}
    } else {
      showToast('Your browser doesn\'t support extra sharing. Try Copy or Email above.');
    }
  });

  // Cancel + backdrop close
  shareCancel?.addEventListener('click', () => shareModal.classList.remove('open'));
  shareModal?.addEventListener('click', e => { if (e.target === shareModal) shareModal.classList.remove('open'); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && shareModal?.classList.contains('open')) shareModal.classList.remove('open'); });
})();
</script>


{{-- ─── Seeker signup / save modal ──────────────────────────────────── --}}
<div class="seeker-modal" id="seeker-modal" role="dialog" aria-modal="true" aria-labelledby="seeker-modal-title">
  <div class="seeker-modal-card" id="seeker-modal-card">
    <h2 id="seeker-modal-title">Save this for later.</h2>
    <p class="lede">Drop your email and we'll send you a one-tap link to come back to your saved Q&amp;As. No password. No church invites. Unsubscribe whenever.</p>
    <form id="seeker-form" autocomplete="off">
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <input type="hidden" name="form_opened_at" id="seeker-opened-at" value="0">
      <input type="hidden" name="qa_id" id="seeker-qa-id" value="">
      <input type="email" name="email" id="seeker-email" placeholder="your email" required autocomplete="email">
      <button type="submit" class="submit" id="seeker-submit">Send link →</button>
    </form>
    <button type="button" class="cancel" id="seeker-cancel">Maybe later</button>
    <p class="fine">No spam. Unsubscribe with one click. Email goes to <span style="font-family:'JetBrains Mono',ui-monospace,monospace;">hello@thechurchofpeace.org</span> — replies welcome.</p>
  </div>
</div>
<div class="seeker-toast" id="seeker-toast"></div>

{{-- ─── Share modal (Copy / Email / Text) ──────────────────────────────── --}}
<div class="share-modal-overlay" id="share-modal" role="dialog" aria-modal="true" aria-labelledby="share-modal-title">
  <div class="share-modal-card">
    <h2 id="share-modal-title">Share this with someone</h2>
    <div class="share-qa-preview" id="share-qa-preview">""</div>
    <div class="share-options" id="share-options">
      <button type="button" class="share-option" data-share-action="copy">
        <span class="icon-wrap">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        </span>
        <span class="label-block">
          <span class="opt-title">Copy the link</span>
          <span class="opt-sub">Paste it anywhere — text, group chat, anywhere</span>
        </span>
        <span class="chev">›</span>
      </button>
      <a href="#" class="share-option" data-share-action="email">
        <span class="icon-wrap">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M22 6L12 13 2 6"/></svg>
        </span>
        <span class="label-block">
          <span class="opt-title">Email this</span>
          <span class="opt-sub">Opens your email app, ready to send</span>
        </span>
        <span class="chev">›</span>
      </a>
      <a href="#" class="share-option" data-share-action="sms">
        <span class="icon-wrap">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/></svg>
        </span>
        <span class="label-block">
          <span class="opt-title">Text this</span>
          <span class="opt-sub">Opens your messages app with this ready to send</span>
        </span>
        <span class="chev">›</span>
      </a>
    </div>
    <div class="share-more-toggle">
      <button type="button" id="share-more-btn">More sharing options →</button>
    </div>
    <button type="button" class="share-modal-cancel" id="share-cancel">Never mind</button>
  </div>
</div>
</body>
</html>
