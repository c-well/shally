<!doctype html>
<html lang="en">
<head>
@include('intercessors._style')
<title>Prayer team · The Church of Peace</title>
</head>
<body>
<main>
  {{-- The prayer team's door. No names are listed: members get a sign-in link by text. --}}
  <p class="kicker">Prayer team</p>
  <h1>For the prayer team</h1>
  <p class="lede">This page is where Shalom's prayer team reads and prays over requests.</p>

  <div class="card">
    <h2>Need prayer?</h2>
    <p>Send your request and the prayer team will hold it in prayer.</p>
    <a class="btn" href="{{ route('prayer.show') }}">Send a prayer request</a>
  </div>

  <div class="card">
    <h2>On the prayer team?</h2>
    @if (session('link_sent'))
      <p class="ok" role="status" style="margin-top: 10px;">If that number is on the prayer team, a sign-in link is on its way by text. It works once, for 30 minutes.</p>
    @endif
    @if ($errors->any())<p class="err" role="alert" style="margin-top: 10px;">{{ $errors->first() }}</p>@endif
    <p>Type the phone number the church has for you. We'll text you a link that signs you in on this phone.</p>
    <form method="POST" action="{{ route('intercessors.sendLink') }}">
      @csrf
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <label for="phone">Your phone number</label>
      <input type="tel" id="phone" name="phone" inputmode="tel" autocomplete="tel" required placeholder="(555) 123-4567" value="{{ old('phone') }}">
      <button type="submit" class="btn wide">Text me a sign-in link</button>
    </form>
  </div>
</main>
</body>
</html>
