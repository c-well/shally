<!doctype html>
<html lang="en">
<head>
@include('peace-news._head')
<meta name="robots" content="noindex, nofollow">
<title>Approve Peace Notes News</title>
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<main style="padding: clamp(32px, 6vw, 64px) 0;">
  <div class="pn-col" style="display: flex; flex-direction: column; gap: 20px;">
    @php
      $state = [
        'draft'    => ['Waiting for approval', '#6b4d8a'],
        'approved' => ['Approved' . ($issue->approved_by ? ' by ' . $issue->approved_by : '') . '. Goes out ' . ($issue->send_after->isPast() ? 'within minutes.' : $issue->send_after->timezone('America/New_York')->format('l, M j \a\t g:i A') . '.'), '#1f6b45'],
        'held'     => ['On hold. Nothing will send.', '#7a4b00'],
        'sent'     => ['Sent ' . optional($issue->sent_at)->timezone('America/New_York')->format('M j, g:i A') . ' to ' . $issue->sent_count . ' people.', '#334455'],
      ][$issue->status] ?? [$issue->status, '#334455'];
    @endphp
    <p style="margin: 0; font-size: 15px; font-weight: 600; color: {{ $state[1] }};">{{ $state[0] }}</p>
    <div>
      <p style="margin: 0; font-size: 14px; color: #5f6876;">{{ $sermon->speaker }} · Sabbath, {{ \Illuminate\Support\Carbon::parse($sermon->sermon_date)->format('M j') }}</p>
      <h1 style="margin: 6px 0 0; font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 500; font-size: clamp(36px, 6vw, 52px); line-height: 1.05;">{{ $sermon->title }}</h1>
      @if ($sermon->heart_line)<p style="margin: 10px 0 0; font-weight: 300; font-size: 18px; line-height: 1.5;">{{ $sermon->heart_line }}</p>@endif
    </div>

    @foreach ([['How it opens', $issue->opening_summary, $issue->opening_quote, $issue->opening_seconds, 'Listen to the opening'], ['How it closes', $issue->closing_summary, $issue->closing_quote, $issue->closing_seconds, 'Listen to the close']] as [$label, $sum, $quote, $sec, $cta])
      <div style="background: #fff; border-radius: 12px; padding: 18px 20px;">
        <p style="margin: 0; font-size: 13px; font-weight: 600; color: var(--teal);">{{ $label }}@if ($sec !== null) · {{ \App\Models\PeaceNewsIssue::clock($sec) }}@endif</p>
        @if ($sum)<p style="margin: 6px 0 0; font-size: 16px; line-height: 1.55;">{{ $sum }}</p>@endif
        @if ($quote)<p style="margin: 8px 0 0; font-size: 16px; line-height: 1.55; font-style: italic; color: #334455;">“{{ $quote }}”</p>@endif
        @if ($issue->youtubeAt($sec))<a href="{{ $issue->youtubeAt($sec) }}" target="_blank" rel="noopener" style="display: inline-block; margin-top: 10px; font-size: 15px; font-weight: 500;">{{ $cta }} ›</a>@endif
      </div>
    @endforeach

    <div style="background: #fff; border-radius: 12px; padding: 18px 20px;">
      <p style="margin: 0 0 8px; font-size: 13px; font-weight: 600; color: var(--teal);">The questions that go out</p>
      @foreach ($issue->questions as $n => $q)<p style="margin: 0 0 8px; font-size: 16px; line-height: 1.5;">{{ $n + 1 }}. {{ $q }}</p>@endforeach
    </div>

    @if (! empty($issue->checks))
      <div style="background: #fff6e6; border: 1px solid #f0d9a8; border-radius: 12px; padding: 18px 20px;">
        <p style="margin: 0 0 8px; font-size: 15px; font-weight: 600; color: #7a4b00;">Check before sending</p>
        @foreach ($issue->checks as $c)<p style="margin: 0 0 6px; font-size: 15px; line-height: 1.5; color: #4a3a1a;">{{ $c }}</p>@endforeach
      </div>
    @endif

    <p style="margin: 0; font-size: 15px;"><a href="{{ route('peace.news.show', $issue->slug) }}">See the full issue page</a> (needs you to be signed in until it goes out)</p>

    @if (in_array($issue->status, ['draft', 'held', 'approved'], true))
      <form method="post" action="{{ route('peace.news.review.act', $token) }}" style="display: flex; flex-direction: column; gap: 12px; background: #fff; border-radius: 12px; padding: 18px 20px;">
        @csrf
        <label for="by" style="font-size: 15px; font-weight: 500;">Your name</label>
        <input id="by" name="by" class="pn-field" type="text" maxlength="120" placeholder="Andre or KC" autocomplete="name">
        <div style="display: flex; flex-wrap: wrap; gap: 12px;">
          @if ($issue->status !== 'approved')
            <button type="submit" name="action" value="approve" class="pn-btn" style="background: #1f6b45; border-color: #1f6b45; flex: 1 1 160px;">Approve</button>
          @endif
          @if ($issue->status !== 'held')
            <button type="submit" name="action" value="hold" class="pn-btn pn-btn--ghost" style="flex: 1 1 140px;">Hold</button>
          @endif
          <a href="{{ route('admin.peace.news.edit', $issue) }}" class="pn-btn pn-btn--ghost" style="flex: 1 1 140px;">Edit</a>
        </div>
        <p style="margin: 0; font-size: 13px; color: #5f6876;">First approval sends. The other person gets a copy.</p>
      </form>
    @endif
  </div>
</main>
</body>
</html>
