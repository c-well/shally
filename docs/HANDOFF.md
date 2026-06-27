# HANDOFF — Church of Peace (thechurchofpeace.org / Shalom SDA)

> For the next Claude picking this up, and for continuity when Karlon switches back.
> **Use git history + `docs/CHANGELOG.md` to see what changed and when.** Append your own
> notes here as you go. Last updated by the prior session (see latest commits).
> **STANDING RULE: at the END of every session, update this file + docs/CHANGELOG.md** — what changed, where you left off, new conventions — so the next Claude (or Karlon) never misses a beat.

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

## DESIGN SYSTEM — match this exactly (Karlon is a designer; drift gets noticed)

### Palette — CSS vars live in `public/css/shalom.css` `:root`. Use the vars, never raw hex.
- Surfaces: `--parchment #fefcef`, `--cream #faf3dd`, `--line rgba(26,35,50,.10)`
- Ink/text: `--ink #1a2332`, `--ink-soft #334455`, `--ink-faint rgba(26,35,50,.45)`
- Brand: **`--teal #03617A` (THE accent)**, `--teal-dark #024357`, `--teal-light #e6f0f3`, `--brass #b08d3c`
- Status: `--red #a82a1f`, `--green #2d8659`
- **Seasonal themes**: `body[data-theme="communion|easter|christmas|mothers|thanksgiving"]` overrides parchment/teal. Always render `<body data-theme="{{ \App\Models\AppSetting::get('site_theme','default') }}">`.
- (The grad SLIDE uses a slightly warmer teal #2F6B6B inside GD; on the WEB the canonical accent is `--teal #03617A`.)

### Fonts
- **Older site surfaces**: Cormorant Garamond (serif display/italic), Instrument Sans (tracked uppercase labels/buttons), Poppins (body), JetBrains Mono (meta). Loaded via shalom.css + Google.
- **"Considered" surfaces (NEW — intake form, bulletin v2, Messages)**: IBM Plex Sans + IBM Plex Serif. Per-page: `IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:ital,wght@0,400;0,500;1,400`.
- **Grad SLIDE (GD)**: Poppins (name, the house style) + IBM Plex Serif italic (verse) — static TTFs in `storage/fonts/`.
- **Cormorant Garamond is DEAD for new work** — it renders thin/inconsistent at size; Karlon rejected it. Don't reach for it.

### "Considered" — the language for everything new / "stand on business"
Robust type, hard restraint, **space is the hero**. Moodboard Karlon gave: Anthropic.com, Rafal Tomal, Typewolf.
- Left-aligned, generous margins; hierarchy from size/weight, not decoration.
- **ONE accent (teal), used sparingly. Hairline rules — no boxes, no drop shadows, no ornament, no rounded "cards" doing the talking.**
- Eyebrows = small tracked uppercase labels (letter-spacing .16–.26em).
- Headline = bold IBM Plex Sans 700 (or a robust serif); supporting text quiet.

### Page scaffold (each standalone page)
Full `<!doctype html>` doc (not a layout component). `<head>`: meta + `@include('partials.seo-head',[...])` + Google Fonts + inline `<style>` + `@include('partials.theme-vars')`. Body: `data-theme`, `@include('partials.site-menu')`, content, `@include('partials.footer')`.
- **Admin `_typography` trap**: older admin pages `@include('admin.partials._typography')` which FORCES Noto Serif body + Varela Round headings. **Do NOT include it on Considered surfaces** — it overrides IBM Plex with serif.

### Shared interactions
- `@include('partials._confirm')` → `window.shConfirm(msg,{okLabel,danger})`, `shAlert`, `shToast`; auto-intercepts `form[data-confirm]`, `a[data-confirm]`, `form[data-confirm-ajax]`. **No native confirm()/alert() anywhere** (site-wide rule).
- Autosave = debounce ~150–650ms → PATCH the field → subtle saved pip/toast.

### Verify workflow (Karlon says "check your work" — actually do it)
1. After Blade/PHP edits: `php artisan optimize:clear`.
2. Render a view to a file in tinker, extract its inline `<script>`, **`node --check`** it (node is on the box at /usr/bin/node).
3. Slides: render a PNG and LOOK at it.
4. HTTP-probe routes (expect 200/302), tail today's error log.
5. Headless shots: `Google Chrome --headless=new --screenshot=out.png file://page.html` — wrap with a kill-timeout, it can hang.

## What's live (built over the prior sessions)
1. **Intake engine** — schema-driven forms. `IntakeForm`/`IntakeSubmission`, public `/intake/{slug}` (slug = the memorable link). Field types: text/email/tel/date/select/textarea/photo/checkbox/checkboxes, conditional `show_if` (progressive disclosure). `app/Http/Controllers/IntakeController.php`, view `resources/views/intake/show.blade.php`.
   - **Graduation form** is seeded (`/intake/grad`, 16 adaptive fields). Generates a 1920×1080 ProPresenter PNG via `app/Services/Intake/GradCardRenderer.php` (pure GD). Photo-left/text-right; photo block takes the photo's orientation (portrait/landscape/square); no-photo → centered. `slide_style` setting (sans|serif).
   - **Gallery** `/admin/intake/{slug}`: review, per-item + bulk(zip) download, **Edit text → regenerate (one-off)**, **Photo-only/Text-back on all (bulk)**, remove/restore, **push-to-site-menu toggle** (`settings.in_menu`).
   - **Notify**: email to shalomsda3323@gmail.com + Andre (slide attached, CC contact@c-wellpics.com) + **Twilio SMS** to Andre (914)447-7199. Twilio is a SHARED account (creds from the Franz/genesis app, in `.env` as TWILIO_SID/TOKEN/FROM, from +18337005393).
   - **Media storage: `public/intake-media/`** — NOT `public/intake/` (that collides with the `/intake/{slug}` route).
2. **Bulletin editor v2** — `/admin/bulletin` (`AdminBulletinController`, `resources/views/admin/bulletin.blade.php`). Frictionless drill-through: every order-of-service item is an inline part/person field that autosaves; reorder ↑↓, add item/section, delete, edit title/date/theme, switch bulletins, Go Live; announcements too. **Person field autocompletes from past entries** via `/api/suggestions` (scope rules: part says hymn/song→hymnal, scripture→Bible, else past people). It's a NEW front door onto the existing `BulletinController` endpoints — **v1 (the inline editor on `/welcome`) is UNTOUCHED and the default.** Toggle: `AppSetting('bulletin_editor')` v1|v2; opening v2 makes it the admin's default, the "Classic editor" button flips back.
3. **Clerk events manager** — `/admin/events` (Rosharde, clerk). Quick-add name+date+flyer, autosave, auto-publish, tightened list, on/off-the-website toggle.
4. **Kids Scripture games** — /kids (3 games: word-search, memory-match, hidden-words; player = a name kept by a localStorage token; autosave + gentle stars leaderboard). Admin level-builder at /admin/games. Tables `game_levels`/`game_players`/`game_progress`; 16 curated KJV levels seeded. `KidsController` + `AdminGameLevelsController`; views `resources/views/kids/*` + `admin/games.blade.php`. Built to teach the Word, not entertain.
5. **Messages** — /messages (public, Spiritual Life menu). Public sermon-audio archive over published `PeaceSermon`s, on-brand audio player (lazy, one-at-a-time). `MessagesController` + `resources/views/messages/index.blade.php`.
6. **Latest-service failsafe** — `sermons:refresh-latest` probes the channel's **/streams** (UULV live-streams playlist) newest-first via yt-dlp, caches the first watchable full service (≥10 min) in `AppSetting('latest_service_video_id')`; `landing.blade.php` embeds that id (playlist fallback). Scheduled daily 04:10 + Sat 21:00. **Rolls back through pulled/missing weeks.**

## Standing rules (from Karlon — important)
- Outbound mail: **CC contact@c-wellpics.com**. Mail is sendmail; from app@thechurchofpeace.org.
- **Never write Karlon's full name** in any content/output; don't invent people's names — ask.
- Magic-link expiry stays **30 min**. Don't use `php_flag engine off` in .htaccess (cPanel LSAPI breaks).
- "When Karlon says fix it, FIX IT — don't audit it." Verify your work (he says "check your work" a lot): render to a file + `node --check` the inline JS; for slides, render a PNG and eyeball it.

## Pending / next tasks
- **Announcements media (DB migrated, controller done, NOT yet in public render):** `image_path` + `video_url` added to `announcements` table (migrated). `BulletinController::uploadAnnouncementImage/removeAnnouncementImage` work. Bulletin v2 editor has per-announcement media panel (image upload + video link). **TODO:** public render in `welcome.blade.php` — newest ann shows image/video inline, older ones collapsed.
- **Form builder (BUILT — /admin/intake + /admin/intake/builder):** Andre can create/edit intake forms from the UI. Index lists all forms. Builder: title/slug/output-type/intro/thank-you, visual field editor (drag-to-reorder, all field types, show-if conditions, options), notifications, slide style. Save creates/updates `IntakeForm`. Gallery link from index row.
- **Announcements media (asked for, NOT built):"** let an announcement carry an optional image and/or a video link, shown **nested/collapsed and lazy-loaded ("ajaxed out") on demand** in the public bulletin. Suggested: add `image_path` + `video_url` to the `announcements` table; in bulletin v2 add a small "＋ media" affordance per announcement (upload + link); public render shows a 📷/▶ chip that expands/loads on click. Keep it secondary/clean.
- **Kids games — BUILT (word-search, memory-match, hidden-words, admin builder, autosave, names, leaderboard).** Remaining nice-to-haves from Karlon's spec: a BOOK picker on /kids (levels are already book-tagged, just add a filter like the age chips); a few more curated levels per book; optionally tie a level to a specific sermon (PeaceSermon). The 'choose a book and the game adjusts' is mostly a filter away.
- **Form builder (Phase 3):** a phone-friendly visual builder so Andre mints new `/intake/<slug>` forms himself (this is also where "generate form links on the fly" lands). The engine already supports arbitrary schemas — this is the UI to author `IntakeForm` rows.
- **Bulletin v1→v2:** eventually retire the `/welcome` inline editor once v2 is proven; keep both for now.

## Git note for Karlon
The `Co-Authored-By: Claude ...` line on commits is a standard git **trailer** (attribution that the change was AI-assisted) — it lives only in git history, never on the site. The changelog page shows only the one-line summary.
