# The Church of Peace audit — 2026-07-29

## Current platform

- Public host: `https://thechurchofpeace.org`
- Account: `/home/shalom`
- Application: `/home/shalom/laravel`
- Document root: `/home/shalom/public_html`
- `/home/shalom/laravel/public` is a symlink to the document root.
- Installed core: Laravel `13.20.0`, PHP `8.4.23`
- Production mode is enabled and debug mode is disabled.

## Completed safely

1. Fixed the Find Peace save modal initialization bug. “Maybe later,” Escape,
   and backdrop-click now close it. The related Share modal was fixed at the
   same initialization boundary. Commit: `887eef0`.
2. Added a 301 canonical redirect from `www.thechurchofpeace.org` to
   `thechurchofpeace.org`, preserving the path. `gotoshalom.com` remains a
   canonical redirect. Because the public directory is an external symlink,
   this rule lives in `/home/shalom/public_html/.htaccess`, outside Git.
3. Moved 14 internal mockup/audit/walkthrough HTML pages out of the public web
   root. All tested URLs now return 404.
4. Archived those pages on Rocky at:
   `/tmp/mountd/disk1_part1/shalom/mockups/2026-07-29/`
   Archive SHA-256:
   `fe06fd1ce073214a89ee7cbea6e3330cbff6524cc239e14e8cf0f11b6d0b66ff`
5. Retained a root-only VPS rollback copy at:
   `/root/shalom-public-mockups-quarantine-20260729`
6. Changed all Shalom backup trees to owner-only directories/files and added
   `umask 077` to the daily database and weekly offline-package generators.
   Cross-account reads now fail.
7. Kept the 1.34 GB July 16 upgrade archive because it is only 13 days old,
   below KC’s 30-day removal threshold.
8. Updated `dompdf/dompdf` to `3.1.6`, `guzzlehttp/guzzle` to `7.15.2`, and
   `guzzlehttp/psr7` to `2.13.0`. Composer now reports zero advisories.
   Dompdf and Guzzle smoke tests passed before deployment; production PDF
   generation and public routes passed afterward. Commit: `2fa7fee`.
9. Revoked 17,714 database sessions and all three remember-me tokens.
10. Rotated the Laravel application key after confirming that encryption is
    used only for short-lived form-render tokens, not persisted application
    records.
11. Rotated the `shalom_app` database password through cPanel and verified
    database access plus the live Find Peace route afterward.

## Security findings still requiring a maintenance pass

### P1 — Rotate external provider credentials

The application/database credentials and authentication sessions have been
closed. Provider credentials that existed in historical application archives
must still be rotated in their respective dashboards: Anthropic, OpenAI,
Resend, Twilio and the configured mail provider. Do not delete a current key
until its replacement has been installed and its feature verified.

### P2 — Authentication/session tightening

- Database sessions last 43,200 minutes (30 days).
- PIN login accepts a four-digit PIN and always creates a remembered login.
- Rate limiting is present: five failures per name per hour and ten per IP per
  15 minutes inside the controller, plus route throttling.
- Magic links are hashed, single-use, and expire after 30 minutes.

Reduce the ordinary session lifetime and require stronger PINs or use magic
links as the preferred low-friction login.

### P2 — Browser policy and public mutation review

- The CSP still permits `unsafe-inline` and `unsafe-eval`.
- `POST /api/events` is public and throttled but not authenticated or signed.
- Several youth-room mutation routes rely on controller-level room authority
  rather than visible route middleware.

These need focused tests before changing behavior.

## Healthy controls verified

- Main admin routes require authentication and explicit `super_admin` or
  `clerk` roles.
- `.env` is owner-only.
- Sensitive framework paths return 404 publicly.
- HSTS, nosniff, frame, referrer, permissions and CSP headers are present.
- Public prayer/contact/intake forms have throttles and honeypots.
- Recent logs are small, with only two recurring database query exceptions.
- The scheduler is active and the public site stayed HTTP 200 throughout this
  work.
- Composer reports zero known dependency advisories.

## Test-suite limitation found

The stock feature suite cannot currently rebuild its SQLite test database:
historical migrations contain MySQL-only statements, duplicate an index, and
later game migrations reference base tables whose creation migrations are not
present. These faults predate the dependency update. No migration rewrites
were deployed during the security patch; focused package tests and live
regression checks were used instead.

## Rollback locations

- Modal template:
  `/root/church-peace-modal-20260729-1743/show.blade.php`
- Canonical redirect:
  `/root/shalom-www-canonical-20260729-1747/`
- Backup scripts:
  `/root/shalom-backup-permissions-20260729-1750/`
- Dependency packages:
  `/root/shalom-deps-rollback-20260729-183541/`
- Application key and pre-rotation environment:
  `/root/shalom-credential-rollback-20260729-183709/`
