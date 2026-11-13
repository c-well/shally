<!doctype html>
<html lang="en">
<head>
@include('admin.peace-news._head')
<title>Peace Notes News · Admin</title>
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
@include('partials.site-menu')
<div class="top"><a href="{{ route('admin.peace.index') }}">‹ Peace admin</a></div>
<main>
  @if (session('ok'))<div class="ok">{{ session('ok') }}</div>@endif
  <h1>Peace Notes News</h1>
  <p class="lede">Drafted every week from Sabbath’s sermon. Andre or KC approves (first approval sends) and it goes out Sunday 8 AM to {{ number_format($subscribers) }} {{ \Illuminate\Support\Str::plural('subscriber', $subscribers) }}. <a href="{{ route('peace.news.index') }}">Public page ›</a></p>

  <h2 id="answers">Answers waiting ({{ $pending->count() }})</h2>
  @forelse ($pending as $a)
    <div class="card">
      <p style="margin: 0 0 8px; font-size: 16px; line-height: 1.6;">{{ $a->body }}</p>
      <div class="row">
        <span class="muted">{{ $a->first_name ?: 'No name' }} · {{ $a->issue->sermon->title }} · {{ $a->created_at->diffForHumans() }} · {{ $a->share_ok ? 'OK to quote' : 'Do not quote' }}</span>
        <form method="post" action="{{ route('admin.peace.news.answer', $a) }}" style="display: flex; gap: 8px;">
          @csrf
          <button class="btn go" name="action" value="approve">Approve</button>
          <button class="btn ghost" name="action" value="reject">Hide</button>
        </form>
      </div>
    </div>
  @empty
    <p class="muted">None waiting.</p>
  @endforelse

  <h2>Issues</h2>
  @forelse ($issues as $i)
    <div class="card">
      <div class="row">
        <div>
          <span class="pill s-{{ $i->status }}">{{ ucfirst($i->status) }}</span>
          <strong style="margin-left: 8px;">{{ $i->sermon->title }}</strong>
          <div class="muted" style="margin-top: 4px;">
            @if ($i->status === 'sent') Sent {{ $i->sent_at->timezone('America/New_York')->format('M j, g:i A') }} to {{ $i->sent_count }} · {{ $i->approved_count }} approved {{ \Illuminate\Support\Str::plural('answer', $i->approved_count) }}
            @else Sends {{ $i->send_after->timezone('America/New_York')->format('D M j, g:i A') }} once approved @if ($i->approved_by) · approved by {{ $i->approved_by }} @endif
            @endif
          </div>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          <a class="btn ghost" href="{{ route('peace.news.show', $i->slug) }}">View</a>
          @if ($i->status !== 'sent')<a class="btn ghost" href="{{ route('admin.peace.news.edit', $i) }}">Edit</a>@endif
        </div>
      </div>
    </div>
  @empty
    <p class="muted">The first issue is drafted after Saturday’s sermon is processed.</p>
  @endforelse
</main>
</body>
</html>
