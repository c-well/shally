# SHALOM / The Church of Peace — Complete Stack & Restore Sheet

> **Purpose:** hand this single file to a fresh Claude (or any competent engineer) and the
> site can be understood, run offline, or restored from a weekly package. Kept current at
> `docs/STACK.md`; a copy ships inside every weekly offline package.
> Last regenerated: 2026-07-04 (v1.5).

## What this is
The digital home of Shalom Seventh-Day Adventist Church ("The Church of Peace"),
3323 White Plains Rd, Bronx NY 10467 — live at **https://thechurchofpeace.org**.
One Laravel app, one database, one source of truth: the clerk types the bulletin once and
it becomes the web bulletin, two print PDFs (portrait + 2-up), the QR-linked /announcements
page, and the living /calendar. A separate seeker-facing ministry (Find Peace) and a
members' sermon archive (/messages) share one sermon table.

## Stack (verify live at the bottom of /admin — the system strip reads runtime truth)
| Layer | What | Notes |
|---|---|---|
| Framework | **Laravel 12.x** (PHP **8.3**) | upgrade playbook: `docs/UPGRADE-13.md` |
| DB | **MariaDB 10.11** (`shalom_app`) | creds in `.env` on server only |
| Server | cPanel + LiteSpeed, shared host | acct `shalom` @ 67.222.27.173, SSH port 2200 |
| PHP (CLI) | `/opt/cpanel/ea-php83/root/usr/bin/php` | web SAPI lacks `php_fileinfo` — always set explicit Content-Type on downloads |
| Frontend | Blade + vanilla JS, **no build step** | fonts: Cormorant Garamond (public serif), Instrument Sans (admin + ALL numerals), JetBrains Mono (admin headers), Xtreem (logo, `/fonts/XtreemMedium.ttf`) |
| PDF | barryvdh/laravel-dompdf | 2-up: absolute-positioned columns (tables = phantom pages) |
| AI | anthropic-ai/sdk (PHP) | feedback bot, event Smart-fill, Peace pipeline. Key in `.env` `ANTHROPIC_API_KEY` |
| Media tools | ffmpeg + ffprobe + yt-dlp at `/usr/local/bin/` | yt-dlp needs `TMPDIR` override (noexec /tmp) |
| Mail | SMTP via cPanel | ALWAYS CC `contact@c-wellpics.com` on outbound |
| SMS | Twilio (shared Franz/genesis acct) | from +18337005393 |
| Queue/cron | `schedule:run` every minute via crontab | DB queue driver |
| Analytics | first-party only | `page_views` + `interaction_events`, dashboard `/admin/analytics` |

## The map — where everything lives
- **Public:** `/` (landing) · `/welcome` (bulletin, also inline classic editor for clerks) · `/calendar` · `/announcements` (QR target) · `/messages` + `/messages/{slug}` (member archive w/ comments) · `/find-peace` (SEEKERS ONLY — never link members into it; own future domain planned) · `/lesson` · `/bible` · `/hymnal` · `/search` · `/kids` · `/youth` · `/contact` · `/visit` · `/about` · `/prayer`
- **Admin (role clerk/super_admin):** `/admin` hub · `/admin/bulletin` (v2 editor) · `/admin/analytics` · `/admin/peace/*` (Find Peace ministry) · `/guide` (volunteer Field Guide) · system strip at hub bottom (versions + release check)
- **Key models:** Bulletin (+lines, announcements w/ `is_web_only` print divider) · Event (recurrence: `recur_until` + per-weekday `recur_times` + `stream_url`) · PeaceSermon (+qaPairs, scriptures, topics, `transcript_raw` = search-only) · MessageComment · PageView/InteractionEvent
- **House rules:** no pill buttons (3–8px radius; 999px only for dots/badges) · Cormorant banned in admin and for all numerals · WCAG AA everywhere (lesson page = exemplar; deep brass `#8a6c26` for small text on light, faint gray is `#6b7280`) · canonical teal `#03617A` · deploys ONLY via `~/bin/safe-deploy.sh staged:target …` (lint → backup → branded 503 seconds → probe → auto-rollback)

## Restore / run offline (no Flywheel needed — Laravel version)
The weekly package at `/home/shalom/offline-packages/shalom-offline-YYYYMMDD.zip` contains:
`app/` (full Laravel tree incl. vendor), `db.sql.gz`, this file, and `RESTORE.md`.

**On any machine with PHP 8.3 + MariaDB/MySQL (macOS: `brew install php@8.3 mariadb` or use Laravel Herd):**
```bash
unzip shalom-offline-*.zip && cd app
cp .env.offline .env                     # localhost DB creds, mail/SMS/AI keys blanked
mysql -e "CREATE DATABASE shalom_app"
gunzip < ../db.sql.gz | mysql shalom_app
php artisan key:generate --force         # if APP_KEY blanked
php artisan optimize:clear
php -S localhost:8000 -t public          # or: php artisan serve / Herd
```
Site runs fully offline: bulletin, calendar, announcements, messages (audio included if
`storage/` was packaged), PDFs. Only outbound integrations (mail, SMS, YouTube checks,
Claude API) are inert without keys.

**Restore to a NEW host:** same steps on the host, point DNS, re-enter real `.env` secrets
(from Karlon's password manager — never in this file), re-add the two cron lines
(`crontab -l` is captured in the package's `crontab.txt`).

## People & conventions a fresh Claude must know
- **Karlon** — owner/operator (contact@c-wellpics.com). Rules: fix-don't-audit when he
  points at something specific; never invent names; magic links expire 30 min, don't touch.
- **Rosharde** (clerk — gentle pace, no follow-ups) and **André Marshall** (clerk) run the
  bulletin week to week. They learn from `/guide`.
- Bulletins publish via snapshot: public sees `published_snapshot`, clerks see live —
  when debugging "I deleted it but it's still there", check the snapshot.
- Peace sermons: dates from YouTube are UPLOAD days; if a derived date is Sunday, the
  sermon was preached the Saturday before.
- Full session-by-session history: `docs/CHANGELOG.md`. Product plan: `docs/PRODUCT.md`.
