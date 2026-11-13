<!doctype html>
<html lang="en">
<head>
@include('peace-news._head')
<meta name="robots" content="noindex, nofollow">
<title>Peace Notes News · Unsubscribe</title>
</head>
<body data-theme="{{ \App\Models\AppSetting::get('site_theme', 'default') }}">
<main style="padding: clamp(64px, 10vw, 120px) 0; text-align: center;">
  <div class="pn-col">
    @if ($done)
      <h1 class="pn-sec">You’re unsubscribed.</h1>
      <p class="pn-sub">{{ $subscriber->email }} won’t get Peace Notes News any more. You’re always welcome back.</p>
      <p style="margin-top: 28px;"><a class="pn-btn pn-btn--ghost" href="{{ route('peace.news.index') }}">Read past issues</a></p>
    @else
      <h1 class="pn-sec">Stop Peace Notes News?</h1>
      <p class="pn-sub">{{ $subscriber->email }} will stop getting the Sunday email.</p>
      <form method="post" action="{{ $action }}" style="margin-top: 28px;">
        @csrf
        <button type="submit" class="pn-btn">Unsubscribe</button>
      </form>
    @endif
  </div>
</main>
</body>
</html>
