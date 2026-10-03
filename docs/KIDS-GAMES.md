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
