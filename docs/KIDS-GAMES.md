# Scripture games: rewards and player evidence

Updated 2026-10-02. Public games: `/kids`. Staff evidence: `/admin/games/players` (existing super-admin role). Level editor: `/admin/games`.

## Rewards

Starts create a server-owned random run token and puzzle snapshot. Every move requires the player bearer token, run token, and next sequence number. Hidden Words validates the next word index, Memory Match validates card pairs, and Word Search checks selections against its generated grid. Tetris reports each placed piece and cleared rows; the server checks basic physical limits and calculates points. Tetris is not a server replay of board input.

The server computes rewards: 3 stars for puzzle completion; Tetris gets 1 for partial verse progress and 3 for completion. Replaying keeps the best reward per level, without adding unlimited stars. Identical retries are idempotent. Stale tabs, missing sessions, edited verses, and inactive levels cannot submit new rewards. Sessions expire after one day. Puzzles resume within that window; Tetris starts a new board.

`game_progress.earned_stars` holds checked rewards from this release. Existing `stars` and player `total_stars` remain for historical reference and rollback compatibility. Historical totals may contain the early-award bug. Public totals and greetings sum `earned_stars`. Test and Excluded profiles stay off the public board.

## Evidence, not identity verification

This feature adds no email, CAPTCHA, birth date, camera, microphone, location, new IP logging, device fingerprinting, or raw input recording. A browser token is a profile, not a unique person: one person can have several profiles, and one profile can be shared. Existing site-wide telemetry is separate.

Likely play requires either completion with at least 3 valid actions and 10 seconds of measured activity, or 12 valid actions over 30 seconds with positive progress. Activity time adds at most 20 seconds between accepted moves to limit idle inflation. This is approximate server timing. Automation can imitate it; network delivery can affect it. Likely player never means verified identity.

At least 5 invalid moves, as many as valid moves, produces Needs review. Client problems can trigger this label. It does not block play or remove rewards. Fast or short games are not penalized.

Staff can select Known to the team, Test account, Excluded, or the automatic estimate. Known is for people staff already recognize, without asking children for proof. Reviews record actor, time, and before/after status in the existing audit log. Historical nicknames without measured sessions remain Not enough evidence.

## Testing and retention

Test play links in the editor add `?preview=1`, use a separate browser profile, and register a test account with no public rewards. These profiles appear to staff as Test account.

The existing scheduler prunes `game_runs` after 90 days at 04:40. Evidence covers that window. Nicknames, progress, and staff reviews remain until removed on request. Player tokens must never appear in the admin view or public leaderboard.

Run `php vendor/bin/phpunit tests/Feature/KidsGamesTest.php` with isolated SQLite. These focused tests create only their own in-memory tables. Never run destructive database-refresh tests against production.

## October 3 adventure redesign

The kids section uses a scoped design partial loaded after the shared church theme. Artwork, rounded typography, illustrated game previews, contrasting secondary buttons, and dimensional cards replace the previous cream/serif presentation. `resources/kids-assets/adventure-world-v1.jpg` must be copied to `public/kids-assets/adventure-world-v1.jpg` when deploying (the production public directory is a symlink).

Browser speech synthesis has been removed. Optional musical cues start off by default; the player's sound preference is saved locally. Reduced-motion preferences remain respected.

Treasure Trail asks players to match the next word. New challenges use `mode=word_trail` and validate the selected word on the server. Existing in-progress reveal challenges remain compatible.

Verse Blocks no longer interrupts play with quizzes. Seven-piece bags provide all shapes before reshuffling. Each cleared line reveals a four-word phrase; new server challenges specify the phrase target so rewards match completion. Two cleared lines earn a user-initiated shape swap. The visible mission note advances through brief verse-specific teaching; completion includes an everyday action and an optional reflection, with its answer available on demand. Mission text is in `KidsVerseMission`; Scripture quotation remains unchanged. Old active runs retain their original challenge.

Validation: `php vendor/bin/phpunit tests/Feature/KidsGamesTest.php` checks scoring, move validation, phrase preservation, test-account separation, and access control. `node tests/kids-blocks.mjs` checks the actual gameplay script through line locking, phrase reveal, power-ups, completion, and the former quiz interruption boundary. Phone browser checks cover catalog, word grid, trail completion, dialogs, and block controls.

The October 3 follow-up adds an independently animated sheep cutout in the catalog and puzzle screens, with tap-to-hop and discovery celebrations. Idle motion can be paused using a saved browser preference and is disabled by reduced-motion settings. The arcade start screen now shows a short verse mission and Play; controls and power-ups live in expandable “How to play” help, also available while paused.

## Live 3D mascot

`resources/kids-assets/sheep-3d-v1.js` builds an articulated sheep from mesh geometry and instanced fleece, lit and shaded in WebGL with Three.js 0.180.0. The locally hosted dependency and its MIT license are under `vendor/three-0.180.0`; deploy this directory and the module to matching paths beneath public/kids-assets. No runtime CDN or external service is used.

The head follows the pointer; ears, arms, and legs animate during a hop, eyes blink, and the body turns to show its depth. The existing PNG remains visible until 3D initializes successfully, and returns if the graphics context is lost. Rendering stops when offscreen or the page is hidden, is capped at about 30 frames per second, and respects saved motion and system reduced-motion settings. Completion mascots animate on demand. Dialog focus starts at the main action; expandable help is included in keyboard navigation.

## Letter blocks and readable Scripture

The current block game supersedes the earlier phrase-per-line approach. A new `letter_blocks` challenge contains server-created `letter_steps`, at most four letters from one word per piece. Long words span pieces; landing a piece advances exactly one step, and completed words highlight in the full verse. The whole verse is readable from the first move; the word builder and optional Pause & read view support reading at a child's pace. Row clears still earn score and shape swaps. Numbered squares retain their letters through rotation and shape changes.

Only the server chooses progression and completion. Idempotent retries cannot advance another letter step. Incomplete letter-block sessions resume their word progress on a fresh board; filling the board offers a fresh board without losing words. Legacy active runs retain line-based scoring and are replaced with the current mode at the next begin request. Current tests include first-piece progress, multi-piece words, resume, retries, old-run compatibility, shape rotation, fresh-board continuation, and completion.

Memory pictures now map consistently to word meanings: parent-and-child scenes for Mother/Father, caring hands for Honour/Love, sheep and crook for Shepherd, provisions for Want, sunrise for Beginning, growing plant for Created, sky for Heaven, globe for Earth, and a Bible for God/Lord. Each matched pair supplies a brief explanation; matched words highlight in the verse already visible above the cards. These are symbolic learning pictures, not depictions of God.

## Pace levels

Verse Blocks defaults to Gentle (1200ms per row), with Steady (720ms) and Challenge (460ms) choices before play and in Pause. Each 8 cleared rows advances the level and gently increases speed, with mode-specific lower limits (800/280/130ms). Changing pace keeps the current board and verse progress; the choice is saved only on the current browser. Pace does not multiply public stars. The verse remains readable in every mode. The active mascot is the softer illustrated sheep with CSS motion; the experimental procedural 3D model is disabled.

## Church-hours audio

All kids games read published service windows from ServiceTime (America/New_York). A saved sound-on choice is overridden when a game opens during a service or stays open into one. A short notice explains the quiet default; players may explicitly unmute on that page for headphones. This service-time override never changes their saved preference, and ending a service never automatically enables sound. The clock is anchored to the server, uses New York daylight-saving rules, and rechecks before each cue, every 15 seconds, and on visibility changes. Service schedule edits apply on the next page load. No additional visitor information is collected.

## Midnight in Philippi — escape room

A teen point-and-click adventure adapted from Acts 16:25–34 (KJV), with three illustrated scenes and nine objects to inspect. Room 1 restores the sequence of Acts 16:25; room 2 turns word dials to restore “we are all here”; room 3 pairs Scripture, a basin, and bread with hearing, caring, and sharing. The game explicitly identifies objects/puzzles as an imagined adaptation and links the full passage. The characters stay to help the jailer rather than fleeing. There is no timer, horror imagery, synthetic narration, or audio-only clue.

The server checks discovered clues, room sequence, and answers. Wrong answers return helpful feedback without resetting a run. Room and object progress, including partial matches, survive reload through the existing 24-hour run mechanism. Unsubmitted fragment/dial arrangements reset on reload. Hints are optional and carry no reward penalty. Existing completion/star rules and test-play exclusions apply. Assets are local compressed JPEGs; motion honors reduced-motion settings.

Publish `KidsEscapeRoomSeeder` only after the views, engine, and images are deployed. It creates the story once and preserves subsequent staff Live/Hidden choices. No database schema changes are required. Catalog and admin labels support `escape_room`; new stories require authored rooms rather than generic verse-only generation.

Validation: server tests cover discovery requirements, skipped rooms, wrong answers, resumption, object matching, and idempotent rewards. Manual browser walkthrough completed all three rooms, including wrong dials and reloading mid-match, at desktop and 390px phone widths. A local service fixture verified the quiet notice overrides remembered sound-on.
