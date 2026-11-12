{{-- Shown only if starting a game fails: kids never type a name (KC, 2026-11-09). --}}
<div class="ov" id="nameGate" role="dialog" aria-modal="true" aria-labelledby="nameTitle">
  <div class="card">
    <div class="gate-art" aria-hidden="true"></div><div class="genre-tag">YOUR ADVENTURE STARTS HERE</div><h2 id="nameTitle">Hey, explorer.</h2>
    <p>No sign-in needed. Your stars stay saved in this browser.</p>
    <p class="small"><a href="{{ route('privacy') }}" target="_blank" rel="noopener">For parents</a></p>
    <p id="nameError" role="alert"></p>
    <div><button class="btn" id="nameGo">Let's play →</button></div>
  </div>
</div>
