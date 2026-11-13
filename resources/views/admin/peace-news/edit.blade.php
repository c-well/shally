<!doctype html>
<html lang="en">
<head>
@include('admin.peace-news._head')
<title>Edit · Peace Notes News</title>
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')
<div class="top"><a href="{{ route('admin.peace.news.index') }}">‹ Peace Notes News</a></div>
<main>
  @if (session('ok'))<div class="ok">{{ session('ok') }}</div>@endif
  <span class="pill s-{{ $issue->status }}">{{ ucfirst($issue->status) }}</span>
  <h1 style="margin-top: 10px;">{{ $sermon->title }}</h1>
  <p class="lede">{{ $sermon->speaker }} · Sabbath, {{ \Illuminate\Support\Carbon::parse($sermon->sermon_date)->format('M j') }}. The summary comes from Find Peace; edit it there. <a href="{{ route('peace.news.show', $issue->slug) }}">See the issue page ›</a></p>

  @if ($issue->status !== 'sent')
    <form method="post" action="{{ route('admin.peace.news.status', $issue) }}" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 8px;">
      @csrf
      @if ($issue->status !== 'approved')<button class="btn go" name="action" value="approve">Approve</button>@endif
      @if ($issue->status !== 'held')<button class="btn ghost" name="action" value="hold">Hold</button>@endif
      <button class="btn ghost" name="action" value="resend-review">Email the review again</button>
    </form>
    <p class="muted">First approval sends, {{ $issue->send_after->isPast() ? 'within minutes' : $issue->send_after->timezone('America/New_York')->format('l g:i A') }}.</p>

    <form method="post" action="{{ route('admin.peace.news.update', $issue) }}" class="card" style="margin-top: 20px;">
      @csrf
      <h2 style="margin-top: 0;">Questions readers get</h2>
      @foreach ([0, 1, 2] as $n)
        <label for="q{{ $n }}">Question {{ $n + 1 }}</label>
        <textarea id="q{{ $n }}" name="questions[]" class="field" rows="2" maxlength="300">{{ old('questions.' . $n, $issue->questions[$n] ?? '') }}</textarea>
      @endforeach

      <h2>Preview (only Andre and KC see this)</h2>
      <label for="os">How it opens</label>
      <textarea id="os" name="opening_summary" class="field" rows="2" maxlength="600">{{ old('opening_summary', $issue->opening_summary) }}</textarea>
      <label for="oq">Opening line, in his words</label>
      <input id="oq" name="opening_quote" class="field" maxlength="380" value="{{ old('opening_quote', $issue->opening_quote) }}">
      <label for="cs">How it closes</label>
      <textarea id="cs" name="closing_summary" class="field" rows="2" maxlength="600">{{ old('closing_summary', $issue->closing_summary) }}</textarea>
      <label for="cq">Closing line, in his words</label>
      <input id="cq" name="closing_quote" class="field" maxlength="380" value="{{ old('closing_quote', $issue->closing_quote) }}">
      <div style="margin-top: 18px;"><button class="btn" type="submit">Save</button></div>
    </form>

    @if (! empty($issue->checks))
      <div class="card" style="background: #fff6e6; border-color: #f0d9a8;">
        <strong style="color: #7a4b00;">Check before sending</strong>
        @foreach ($issue->checks as $c)<p style="margin: 6px 0 0; color: #4a3a1a;">{{ $c }}</p>@endforeach
      </div>
    @endif
  @else
    <p class="muted">This issue has gone out and can’t be changed.</p>
  @endif
</main>
</body>
</html>
