<div class="ov" id="nameGate" role="dialog" aria-modal="true" aria-labelledby="nameTitle">
  <div class="card">
    <h2 id="nameTitle">Hello, explorer! 👋</h2>
    <p>Choose a nickname. Your stars stay saved in this browser.</p>
    <label for="nameInput" class="sr-only">Your nickname</label><input id="nameInput" type="text" maxlength="30" placeholder="Your nickname" autocomplete="off">
    <p class="small">Use a nickname instead of your full name. Earned stars may put your nickname on our learning board. <a href="{{ route('privacy') }}" target="_blank" rel="noopener">For parents</a></p>
    <p id="nameError" role="alert"></p>
    <div><button class="btn" id="nameGo">Let's play →</button></div>
  </div>
</div>
