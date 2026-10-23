# Little Light activity (2026-10-23)

- Staff dashboard: `/admin/games/little-light` (existing super-admin role).
- Same-origin, web/CSRF-protected endpoints: `/api/little-light/tracking/{bootstrap,start,progress}`. The API prefix avoids counting bootstrap as a page view.
- Static deployment: mirror `index.html`, `game.js`, `tracking.js` to `public/kids-adventures/little-light/`. Both new script URLs are versioned in the HTML. No external service.
- Starting actual gameplay creates an attempt; menus alone do not. Restarting or switching chapters ends it. Resume retains it. Old device journals never enter analytics.
- A random browser UUID expires after 90 days; only its server HMAC is retained. Denied local storage falls back to a page-lifetime ID. No names, IP fields, fingerprinting, raw input events, music filenames or account IDs in gameplay rows.
- Reports contain cumulative active seconds, furthest route percentage, counts of story scrolls, lights, sheep/stones, stumbles, last state and sequence. Active time requires visible playing state and a game input within five seconds. Reading/paused/idle time excluded.
- Send about every 15 seconds, on state transitions and when hidden. Bounded in-memory queue retries network errors and refreshes expired CSRF. No durable offline analytics queue; blockers/abrupt exits can lose reports.
- Server bounds times by elapsed wall time, validates count ranges, binds each unguessable attempt token to a browser HMAC, ignores old/duplicate sequences, and requires basic objectives for completion. Client-reported progress is not a replay-verified score or proof of a human/child. No leaderboard rewards.
- Active play = >=10 measured seconds plus >=5% route progress or an item. A browser may be shared or reset; it is not a person count. Route distance is not a learning assessment.
- Staff roles and game URL `?preview=1` mark attempts as tests. Staff can correct classification; changes are audited. Tests are excluded by default.
- Daily existing `model:prune` schedule includes `LittleLightRun`, retaining 90 days. Recent reports show Eastern time.
- Test: `php vendor/bin/phpunit --filter 'LittleLightTrackingTest|KidsGamesTest'`; `node --test tests/Js/little-light-tracking.test.cjs`.
