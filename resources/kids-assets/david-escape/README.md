# David: A Way Through — first playable 3D mission

Public route: `/kids/david-escape`. Catalog: `/kids`. Released as a short original 3D mission, not a finished commercial-scale game.

## Engine
- `world.js`: authored platforms, cover, checkpoints, patrol paths and story moments. Coordinates: Y up, forward along negative Z.
- `core.js`: renderer-independent, fixed 60 Hz movement, coyote time, jump buffering, variable jump height, box collisions, ledge catches/animated pull-ups, stealth sight/cover, checkpoint recovery and five-second rewind of position and patrol clock.
- `scene.js`: locally hosted Three.js 0.180.0, MIT (vendor license retained). Original code-built articulated characters, architecture, procedural stone/sky textures, shadows, dust, foliage and follow camera with obstacle checks. No copied commercial game assets.
- `game.js`: keyboard, pointer joystick/multitouch actions, standard gamepad mappings, drag camera, modal pause/story/ending flow, local checkpoints, first-party tracking.
- `style.css` and Blade view: responsive HUD, accessible text buttons, focus-managed native dialogs, clear unsupported-WebGL fallback. Existing `kids.audio` honors published church quiet hours.

Keyboard: WASD/arrows move, hold Space jump, Space/E climb from a caught ledge, C toggle crouch, R hold rewind, E talk, V recenter, P/Escape pause. Gamepad: left stick move, right stick look, A jump, B hold crouch, X talk/climb, LB rewind, Start pause. Touch: left movement stick, jump/crouch/rewind buttons and contextual Talk button; drag scene to look.

## Story
Michal helps David out a window (1 Samuel 19:11–12). The scene then transitions explicitly to the later field meeting with Jonathan (1 Samuel 20:35–42). The ending quotes only “Go in peace.” Parkour, the patrol route and rewind are labeled imaginative gameplay. The escape is not presented as a continuous literal reconstruction of the two chapters.
Sources checked:
- https://www.biblegateway.com/verse/en/1%20Samuel%2019%3A12
- https://bible.usccb.org/bible/1samuel/20

## Progress evidence
Uses the existing first-party Little Light collector, separated by `game=david_escape` and three chapter/section IDs. Staff select “David: A Way Through” at `/admin/games/little-light?game=david_escape`. Server stores no identity/age claims. Section completion is client-reported with position/time bounds; no leaderboard awards. Test visits use `?preview=1`; signed-in staff are excluded from ordinary play. Existing Little Light attempts retain game=little_light. Same 90-day pruning.

Checkpoints are local and versioned; old progress is never uploaded as new play. Browser reload begins a fresh attempt at the saved section. Rewind cannot undo a checkpoint or a completed meeting. Network failure never interrupts gameplay.

## Validation
- `node --test tests/Js/david-escape.test.mjs tests/Js/little-light-tracking.test.cjs`
- `php vendor/bin/phpunit --filter 'LittleLightTrackingTest|KidsGamesTest|DavidEscapeTest'`
- Complete browser keyboard play-through: rooftop jump, high ledge catch/pull-up, courtyard cover, field meeting and ending; responsive touch layout and on-screen joystick checked. No physical controller or physical phone available for this pass.

Known scope: one authored mission, stylized first-pass 3D art, no wall-running/combat campaign or recorded voice acting. New levels should use the shared deterministic core and separate world data. Expand after evaluating real play and control feedback.
