<!doctype html>
<html lang="en">
<head>
@include('intercessors._style')
<title>Sign in · Prayer team</title>
</head>
<body>
<main>
  {{-- A button, not an automatic sign-in, so a text message preview cannot use up the link. --}}
  <p class="kicker">Prayer team</p>
  @if ($intercessor)
    <h1>Welcome, {{ \Illuminate\Support\Str::before($intercessor->name, ' ') }}</h1>
    <p class="lede">Tap below to sign in. You'll stay signed in on this phone for six months.</p>
    <form method="POST" action="{{ route('intercessors.link.signIn', $token) }}">
      @csrf
      <button type="submit" class="btn wide">Sign in</button>
    </form>
  @else
    <h1>This link has ended</h1>
    <p class="lede">Sign-in links work once and expire. Ask for a new one and it comes straight to your phone.</p>
    <a class="btn wide" href="{{ route('intercessors.signIn') }}">Get a new link</a>
  @endif
</main>
</body>
</html>
