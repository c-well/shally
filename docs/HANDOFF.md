# HANDOFF — Church of Peace (thechurchofpeace.org / Shalom SDA)

> For the next Claude picking this up, and for continuity when Karlon switches back.
> **Use git history + `docs/CHANGELOG.md` to see what changed and when.** Append your own
> notes here as you go. Last updated by the prior session (see latest commits).

## Access
- App: `/home/shalom/laravel` on `67.222.27.173`. SSH: `ssh -p 2200 shalom@67.222.27.173` (root also works: `ssh -p 2200 root@67.222.27.173`).
- PHP CLI: `/opt/cpanel/ea-php83/root/usr/bin/php`. Laravel 12 / MariaDB / LiteSpeed (cPanel).
- **After any PHP/Blade edit: `php artisan optimize:clear`** (config/view caches). One-cron model: `schedule:run` heartbeat; schedules live in `routes/console.php`.
- Deploy = scp the file up (no CI). Commit on the server repo so the changelog reflects it.

## House design language — "CONSIDERED" (Karlon is a serious designer; this matters)
- Robust type, hard restraint, **space is the hero**. References he gave: Anthropic.com, Rafal Tomal, Typewolf.
- **New/"stand on business" surfaces use IBM Plex Sans** (web) — NOT the old admin Noto Serif/Varela Round (`admin/partials/_typography`). Do NOT `@include` that partial on Considered surfaces.
- **Grad slide house style = sans (Poppins).** Fonts in `storage/fonts/` (IBM Plex Serif/Poppins; Cormorant is dead — it rendered badly, don't use it).
- Palette via `@include('partials.theme-vars')`: parchment bg, ink text, ONE teal accent used sparingly.

## What's live (built over the prior sessions)
1. **Intake engine** — schema-driven forms. `IntakeForm`/`IntakeSubmission`, public `/intake/{slug}` (slug = the memorable link). Field types: text/email/tel/date/select/textarea/photo/checkbox/checkboxes, conditional `show_if` (progressive disclosure). `app/Http/Controllers/IntakeController.php`, view `resources/views/intake/show.blade.php`.
   - **Graduation form** is seeded (`/intake/grad`, 16 adaptive fields). Generates a 1920×1080 ProPresenter PNG via `app/Services/Intake/GradCardRenderer.php` (pure GD). Photo-left/text-right; photo block takes the photo's orientation (portrait/landscape/square); no-photo → centered. `slide_style` setting (sans|serif).
   - **Gallery** `/admin/intake/{slug}`: review, per-item + bulk(zip) download, **Edit text → regenerate (one-off)**, **Photo-only/Text-back on all (bulk)**, remove/restore, **push-to-site-menu toggle** (`settings.in_menu`).
   - **Notify**: email to shalomsda3323@gmail.com + Andre (slide attached, CC contact@c-wellpics.com) + **Twilio SMS** to Andre (914)447-7199. Twilio is a SHARED account (creds from the Franz/genesis app, in `.env` as TWILIO_SID/TOKEN/FROM, from +18337005393).
   - **Media storage: `public/intake-media/`** — NOT `public/intake/` (that collides with the `/intake/{slug}` route).
2. **Bulletin editor v2** — `/admin/bulletin` (`AdminBulletinController`, `resources/views/admin/bulletin.blade.php`). Frictionless drill-through: every order-of-service item is an inline part/person field that autosaves; reorder ↑↓, add item/section, delete, edit title/date/theme, switch bulletins, Go Live; announcements too. **Person field autocompletes from past entries** via `/api/suggestions` (scope rules: part says hymn/song→hymnal, scripture→Bible, else past people). It's a NEW front door onto the existing `BulletinController` endpoints — **v1 (the inline editor on `/welcome`) is UNTOUCHED and the default.** Toggle: `AppSetting('bulletin_editor')` v1|v2; opening v2 makes it the admin's default, the "Classic editor" button flips back.
3. **Clerk events manager** — `/admin/events` (Rosharde, clerk). Quick-add name+date+flyer, autosave, auto-publish, tightened list, on/off-the-website toggle.
4. **Latest-service failsafe** — `sermons:refresh-latest` probes the channel's **/streams** (UULV live-streams playlist) newest-first via yt-dlp, caches the first watchable full service (≥10 min) in `AppSetting('latest_service_video_id')`; `landing.blade.php` embeds that id (playlist fallback). Scheduled daily 04:10 + Sat 21:00. **Rolls back through pulled/missing weeks.**

## Standing rules (from Karlon — important)
- Outbound mail: **CC contact@c-wellpics.com**. Mail is sendmail; from app@thechurchofpeace.org.
- **Never write Karlon's full name** in any content/output; don't invent people's names — ask.
- Magic-link expiry stays **30 min**. Don't use `php_flag engine off` in .htaccess (cPanel LSAPI breaks).
- "When Karlon says fix it, FIX IT — don't audit it." Verify your work (he says "check your work" a lot): render to a file + `node --check` the inline JS; for slides, render a PNG and eyeball it.

## Pending / next tasks
- **Announcements media (asked for, NOT built):** let an announcement carry an optional image and/or a video link, shown **nested/collapsed and lazy-loaded ("ajaxed out") on demand** in the public bulletin. Suggested: add `image_path` + `video_url` to the `announcements` table; in bulletin v2 add a small "＋ media" affordance per announcement (upload + link); public render shows a 📷/▶ chip that expands/loads on click. Keep it secondary/clean.
- **Form builder (Phase 3):** a phone-friendly visual builder so Andre mints new `/intake/<slug>` forms himself (this is also where "generate form links on the fly" lands). The engine already supports arbitrary schemas — this is the UI to author `IntakeForm` rows.
- **Bulletin v1→v2:** eventually retire the `/welcome` inline editor once v2 is proven; keep both for now.

## Git note for Karlon
The `Co-Authored-By: Claude ...` line on commits is a standard git **trailer** (attribution that the change was AI-assisted) — it lives only in git history, never on the site. The changelog page shows only the one-line summary.
