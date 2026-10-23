<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="theme-color" content="#142c39"><title>David · A Way Through — Church of Peace</title><link rel="stylesheet" href="/kids-assets/david-escape/style.css?v=1"></head>
<body data-game="david_escape"><main id="adventure" aria-label="David's 3D escape adventure">
<canvas id="world" tabindex="0" aria-label="3D adventure. W A S D or arrow keys to move. Hold Space to jump, Space again to climb a ledge. C to crouch, R to rewind, E to meet Jonathan, Escape to pause."></canvas>
<div class="vignette" aria-hidden="true"></div>
<header><a href="/kids" class="back">← Games</a><div class="identity"><span>DAVID</span><b>A way through</b></div><div class="tools"><button id="story">Story</button><button id="soundToggle" aria-pressed="false">Sound off</button><button id="pause" aria-label="Pause adventure">Ⅱ</button></div></header>
<p class="quiet" data-quiet-notice hidden></p>
<section class="objective"><span id="section-label">01 · THE ROOFTOPS</span><h1 id="objective">Leave through Michal’s window.</h1><p id="mission-detail">Follow the warm lanterns.</p></section>
<div class="sight" id="sight" hidden><span>Guard’s attention</span><progress id="alert" max="1" value="0" aria-label="Guard's attention"></progress><b>Find cover or crouch</b></div>
<div id="context" role="status" aria-live="polite"></div>
<div class="rewind-meter"><span id="rewind-label">RETRY TIME</span><progress id="rewind-time" max="5" value="0" aria-label="Available rewind seconds"></progress><small>Hold R to rewind</small></div>
<div class="desktop-help"><span><kbd>W A S D</kbd> move</span><span><kbd>SPACE</kbd> hold to jump / climb</span><span><kbd>C</kbd> crouch</span><span><kbd>E</kbd> talk</span><span>Drag to look · <kbd>V</kbd> recenter</span></div>
<div class="touch-controls"><div id="stick" aria-label="Drag to move David" role="group"><span id="thumb"></span><small>MOVE</small></div><div class="touch-actions"><button id="rewind-touch" aria-label="Hold to rewind">↶<small>REWIND</small></button><button id="crouch-touch" aria-pressed="false">↓<small>CROUCH</small></button><button id="jump-touch">↑<small>JUMP / CLIMB</small></button><button id="talk-touch" hidden>Talk<small>JONATHAN</small></button></div></div>
<dialog id="menu" aria-labelledby="menu-title"><div class="menu-card"><p class="eyebrow" id="menu-eyebrow">AN ADVENTURE FROM 1 SAMUEL 19–20</p><h2 id="menu-title">A way<br><em>through.</em></h2><p id="menu-copy">Help David escape Saul’s men. Cross the rooftops, slip past the guards, and find Jonathan.</p><div class="quick-controls" id="quick-controls"><span>Move <b>W A S D</b></span><span>Jump <b>Hold SPACE</b></span><span>Retry <b>Hold R</b></span></div><button class="primary" id="menu-primary" disabled>Preparing the adventure…</button><button class="secondary" id="menu-secondary" hidden>Start again</button><small id="pad-hint" hidden>Controller: A to continue / jump · X to talk · B to crouch · LB to rewind</small><small id="menu-note">A short 3D mission. Parkour and rewind are imaginative gameplay, not events described in Scripture.</small><a class="menu-home" href="/kids">Back to games</a></div></dialog>
<noscript><div class="fallback">This adventure needs JavaScript. <a href="/kids">Choose another game</a></div></noscript>
</main>
@include('kids.audio')
<script src="/kids-adventures/little-light/tracking.js?v=david1"></script>
<script type="module" src="/kids-assets/david-escape/game.js?v=1"></script>
</body></html>
