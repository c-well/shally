# Laravel 13 upgrade plan — prepared 2026-07-04 (Sabbath; execute AFTER)

## Where we stand (audited 2026-07-04 ~9 AM)
- Laravel **12.62.0** = latest 12.x. `composer audit`: **zero security advisories** — the rumored security patch is already in.
- PHP 8.3.31 (meets 13's requirement). Backup at `/home/shalom/upgrade-backups/20260704-091*/` (db.sql.gz verified + full app tarball incl. vendor + composer manifests).

## Do NOT upgrade on a Sabbath. Window: any weekday evening.

## Steps (est. 30–45 min incl. verification)
1. Fresh backup (same recipe as above) — never reuse a stale one.
2. `composer.json`: `laravel/framework: ^13.0`, `laravel/tinker: ^3.0`, `phpunit/phpunit: ^12.0`; leave the rest, composer resolves.
3. `COMPOSER_MEMORY_LIMIT=-1 composer update --no-interaction` (CLI PHP 8.3 binary).
4. Read the 12→13 upgrade notes diff — release is documented minimal-breaking; check `config/` drift via `php artisan config:publish --diff` equivalents.
5. `php artisan optimize:clear && php artisan migrate --force` (13 ships no forced migrations; confirm).
6. Probe suite: `/`, `/welcome`, `/lesson`, `/find-peace`, `/kids`, `/calendar`, `/announcements`, `/guide` + one PDF render + one tinker render of admin.bulletin + `peace:check-live` run.
7. Rollback if anything is off: `rm -rf laravel/vendor && tar xzf app.tar.gz` from the backup dir + restore composer.{json,lock} + `optimize:clear`. DB untouched by this upgrade.

## Same-window candidates (after 13 is green)
- `anthropic-ai/sdk` 0.16 → 0.36 — used by ClaudeAssistant + EventController::smartParse; retest both (feedback reply + crusade parse fixture). Alternatively migrate to Laravel 13's first-party AI SDK later.
- Trivial patch bumps: breeze/pail/pint/sail/collision/fpdf (mostly dev deps).
- `setasign/fpdi-fpdf` is ABANDONED — locate usage, replace or vendor it consciously.

## Explicitly NOT today
Zero advisories + highest-traffic day of the week = freeze. This file is the trigger-pull for the next quiet evening.

## EXECUTED 2026-07-16 (Thu ~11:15 AM ET)
- First attempt failed: L13.20 requires PHP >= 8.4.1, box was on ea-php83. Rolled back clean from /home/shalom/upgrade-backups/20260716-110435 (~90s of 500s).
- Root cause of the "8.4 handler 503": LiteSpeed needs ~15s to spin up a newly-selected PHP handler. First flip was reverted too fast; second flip (behind artisan down) settled at t=15s.
- Final state: PHP 8.4.23 (web+CLI) / Laravel 13.20.0 / Tinker 3.0.2 / PHPUnit 12.5.31. fileinfo NOW PRESENT on web SAPI (8.4 package includes it — the April workaround era is over).
- Crontab (2 lines) + safe-deploy.sh PHP= repointed at ea-php84.
- Probes all green: 12 public routes, both PDFs, search corpus, peace:check-live, admin.hub tinker render, schedule:run, zero errors/deprecations logged.
- Remaining outdated=1: anthropic-ai/sdk 0.16→0.36 (same-window candidate, needs Smart-fill + feedback-reply retests).
