# Shalom — Changelog

## 2026-06-27 · Verse Tetris — mobile + pause

Made Verse Tetris play right on a phone. Rebuilt the layout as a single fixed-height screen (`100dvh`) so the board, score, verse, and controls all fit at once — no more scrolling to reach the buttons. The board now sizes to the available height. Added a **pause button** (⏸ in the top bar; also the `P` key) with a Resume overlay. Added **touch gestures on the board**: tap to rotate, swipe left/right to move, swipe down to drop (the on-screen ◀ ⟳ ▶ ▼ ⤓ buttons still work). Re-morph is now a tap-to-open shape picker. The name moved into the info row so the top bar stays clean.

**Files:** resources/views/kids/tetris.blade.php

## 2026-06-27 · Verse Tetris — premium look

Reskinned the Tetris board out of flat-square "8-bit" territory: refined gradient blocks with soft depth in a sophisticated palette, a soft glass board, a ghost piece showing the landing spot, and a smooth line-clear animation. Considered, not cheesy. (8-bit/retro skins can come later as an option.)

## 2026-06-27 · Verse Tetris (teens)

A real Tetris for the teens at /kids. Clear a line and the next word of the memory verse appears — you build the Word by playing well. Every few pieces, a question about Jesus: get it right and you earn a "re-morph" to reshape the falling piece; get it wrong and the game speeds up. 24 Jesus questions + 4 verse levels seeded; selectable in the admin level builder. Bible knowledge IS the difficulty dial.

## 2026-06-27 · Kids Scripture games — all three live + admin builder

/kids now has three games that bring the Word before the children — **word-search**, **memory-match**, and **hidden-words** (tap-to-reveal, read aloud). Players are just a name (kept on the device), progress autosaves, and gentle stars encourage growth, not competition. Leaders build levels at **/admin/games** (pick a book, a game, an age). 16 curated KJV levels seeded. Built to teach, not entertain.

## 2026-06-26 · Handoff: full design system + games spec

docs/HANDOFF.md now documents the **complete design system** (exact palette vars, fonts, the "Considered" language, page scaffold, shared interactions, verify workflow) so a second Claude matches the site's look pixel-for-pixel, plus **Karlon's full games/kids spec** (teaches the Word — not entertainment; book-selectable; admin-authored levels for ages 4–9 + teens; autosave; player names + gentle leaderboards). Standing rule added: update HANDOFF + CHANGELOG at the end of every session.

## 2026-06-26 · Messages (sermon audio archive)

New public **/messages** page (in the Spiritual Life menu): the church's sermons as audio, built on the Find Peace audio pipeline. Newest featured, the rest below, each with a clean player that loads only when you press play. **Games + kids area** are spec'd in docs/HANDOFF.md for the parallel build — a bigger piece, not rushed.

## 2026-06-26 · Announcements media (images + video)

Announcements can now carry an optional image and/or a video link. Add them in the new bulletin editor (v2) via a per-announcement media toggle — image upload + video URL, tucked away so the editor stays clean. On the public bulletin the first/newest announcement shows expanded; the rest are **nested behind a "More" toggle**, with the image and video **lazy-loaded on demand** so the page stays light. Re-publish the bulletin for new media to appear publicly.

## 2026-06-26 · Session polish + handoff

For continuity (Karlon is bringing in a second Claude on his laptop): see **docs/HANDOFF.md** for full state, conventions, access, and pending tasks.

This session:
- **Latest-service failsafe** now pulls from the channel's /streams (livestreams) — the /videos tab was surfacing an old 2022 upload. Resolves the true latest service and rolls back through pulled weeks. (sermons:refresh-latest, scheduled daily + Sat.)
- **Grad slide**: more air (was crammed) and orientation-adaptive — the photo block takes the shape of the photo (portrait/landscape/square) and the text reflows to fit; never cropped to a portrait, never overflows.
- **Gallery**: Andre can now edit the text on a slide and regenerate (one-off), plus bulk "photo-only / text-back on all".
- **Bulletin v2**: the "who" field autocompletes from past entries (names; hymnal/Bible when the part calls for it). Built solid — debounced, abortable, keyboard-nav, no reopen glitch.
- **Intake → site menu**: Andre can push a form to the public menu from its gallery; Graduate Recognition is live in the menu.

## 2026-06-25 · Intake engine + graduation slides

A schema-driven form engine — one system behind every intake the church needs.

- **Memorable links.** Every form is a slug: thechurchofpeace.org/intake/grad. Share it as-is.
- **Graduation form** (live now). A clean, progressive-disclosure form matching the contact page: it stays tucked away until the graduate picks a level, then shows only the fields that fit — honors and degree appear for college and grad, not for an 8th-grader. Name, photo, special thanks.
- **Auto-generated slides.** Each submission becomes a polished 1920x1080 PNG for ProPresenter — photo on the left, details on the right, or centered if no photo, in the church brand fonts. Rendered server-side with GD; no external service.
- **Gallery** at /admin/intake/grad: preview every slide, download one, download them all as a zip, strip the text to use just the photo, or remove an entry with undo.
- **Notifications.** New submissions email shalomsda3323@gmail.com and Andre with the slide attached, CC the office. SMS to Andre is wired and waits on Twilio credentials.

Next on this engine: a clerk events quick-add, and a phone-friendly build-your-own-form builder.

## 2026-06-24 · Edit history + per-item undo (and restore points)

**Two scales of undo, now two tools.** Rolling back a whole Laravel/dependency update is a different job from undoing one typo, so each gets its own mechanism.

- **Restore points** (Changelog page) — last-known-good snapshots of code + database. For "one major thing": a system update, a migration, a risky structural change. A roll-back reverts everything since that point, so it captures a fresh restore point first (the roll-back is itself undoable). The self-update routine now records one automatically before each run.
- **Edit history** (new — /admin/changes, plus a tile on the Admin hub) — every content edit kept as its own version, with a one-click undo *for that one item only*. For "50 quick text edits, one at a time": a bulletin tweak, a page paragraph, a lesson theme. Undoing one leaves everything else untouched, and the undo is itself reversible.

**How it works.** A `HasRevisions` trait on Bulletin, BulletinLine, Page, and QuarterlyLesson captures the prior values of changed fields on every save — at the model layer, so it fires no matter which screen did the editing (the live bulletin builder, the pages form, the lessons form). Inline "Edit history" panel on the page editor; per-item "History" links on lessons and in the bulletin builder toolbar; and a central feed at /admin/changes showing all edits with undo. Content undo is gated to super-admins.


## 2026-05-23 · Session close — Dr. Calvin Watkins incident + watermark + lesson book

**The incident.** The 3pm Sabbath scan auto-published a sermon under Shalom branding that was actually a Dr. Calvin Watkins guest message from a North Bronx simulcast a month ago. Today's actual Children's Day video didn't have captions yet, so the scanner walked down its candidate list and grabbed the next available one — which happened to be from a month-old service.

**Root cause.** No "newness" guardrail. The dedup filter excluded videos already in the DB, but anything that had never been processed — including archives — was fair game.

**Taken down.** Sermon #16 marked `soft_discarded`, `published_at` nulled, page returns 404, no longer listed on /find-peace index.

**The fix — Watermark pattern.** Stored in `AppSetting` table as `peace_scanner_watermark`. Holds the YYYYMMDD of the most recent upload_date the scanner has considered. Initial seed: `20260523`. After every successful scan run, watermark advances to the picked video's upload_date (or today if no pick was made). Scanner cannot reach backward, ever. Archive access becomes a separate, deliberate, admin-only manual flow (to be built next).

**Additional fixes shipped tonight:**
- Audio extractor's yt-dlp call now passes `--ffmpeg-location /usr/local/bin/ffmpeg` explicitly. Cron environment doesn't include `/usr/local/bin/` on PATH, which is why our manual SSH tests passed but the first live cron fire failed.
- Audio backfill for sermon #16 — file extracted to `/storage/peace/audio/cmpGe2oMBGk.mp3`, then sermon discarded entirely.
- `admin/messages.blade.php` was missing the `@include('admin.partials._typography')` — adding it brings all admin views to consistent Varela Round / Noto Serif typography. Now zero admin views without the partial.

**Post-session audit results (14-point):**
- ✓ Sermon #16 taken down
- ✓ Bulletin section pollution: 0 poisoned rows (Option B fix holds)
- ✓ Scanner watermark seeded
- ✓ Audio files: all live sermons present + sized
- ✓ Find Peace partial: all 4 views
- ✓ Admin font partial: all admin views (after messages.blade.php patch)
- ✓ Mail, Anthropic key, backups, disk usage, cron, today's site URL — all clean

**Locked architectural decisions** for next session:
- Self-healer schedule: Sabbath 7am + 9am ET, Wed 7pm ET, email on red
- Persistent admin banner with FIX button (dispatches server-side Claude)
- Folded into existing /admin/changelog as a tab (not a new top-level surface)
- ≤$1 fixes autonomous, >$1 requires user approval
- Kill switch toggle
- Archive Backfill UI: list (paginated / calendar / search — switchable), transcript view (plain / timestamps / highlighted — timestamps default), estimated cost shown above Process button
- Storage migration plan: move audio to R2 or B2 + Cloudflare CDN at ~50 sermons

**Lesson book saved** at `docs/lesson_book_2026_05_23.md` and to Karlon's local memory for the indie-app build.

**Files touched tonight:**
- `app/Console/Commands/ScanChannelCommand.php` — watermark guardrail
- `app/Services/Peace/AudioExtractor.php` — explicit ffmpeg path
- `resources/views/admin/messages.blade.php` — typography partial include
- `docs/lesson_book_2026_05_23.md` — new
- `docs/CHANGELOG.md` — this entry

**Next session priorities (in order):**
1. Self-healer audit cron + admin banner + FIX button (with kill switch)
2. Admin Archive Backfill UI
3. Pre/post snapshot discipline wired into the dev workflow

---


## 2026-05-23 · OPTION B — Bulletin section-header WYSIWYG unification (the "Pastoral Greetings phantom" fix)

**The bug Andre fought for weeks.** A "Pastoral Greetings" pill kept appearing in the bulletin even when he tried to delete it. On today's Children's Day bulletin, it appeared TWICE — once before "Closing Hymn" and once between the "Benediction" header and the actual benediction line. Andre couldn't delete them because they had no delete buttons. They had no IDs. They weren't database rows.

**Root cause — dual source of truth.** The `bulletin_lines.section` column was being used two ways:

1. On `kind=section_header` rows  → the header's title (correct, intended)
2. On `kind=line` rows           → a hidden "grouping hint" used by the renderer to auto-inject section headers

The line-row usage had no UI to edit it, so once `loadStandardOrder` populated `section="Pastoral Greetings / Baby Dedication"` on 15 lines of every new bulletin, Andre was locked out. Renaming the section_header didn't touch the hidden field on lines. Reordering rows produced phantoms wherever the field "transitioned" back to that value.

**Diagnosis evidence (bulletin id=28, before fix):**

```
sort  kind            section                part         person
26    line            NULL                   Special      Little Dancers
27    line            "Pastoral Greetings"   Closing Hymn #218 "When He Cometh"   ← phantom injected ABOVE this
28    section_header  "Benediction"          —            —
29    line            "Pastoral Greetings"   Benediction  Sis Keyashia Allison    ← phantom injected ABOVE this
```

Cross-database: **47 line rows poisoned** with stale section values across all bulletins. 25 legitimate section_header rows untouched.

**The fix — single source of truth.**

| Change | File |
|---|---|
| One-time data cleanup: `UPDATE bulletin_lines SET section=NULL WHERE kind='line'` | `database/migrations/2026_05_23_184500_clean_section_field_on_bulletin_line_rows.php` |
| `loadStandardOrder` no longer writes `section` to line rows — only to the section_header row | `app/Http/Controllers/BulletinController.php` |
| Renderer drops the 60-line "POSITION_AWARE_SECTION_HEADERS" reconciliation block. New rule: emit a section pill ONLY when iterating a `kind=section_header` row. Period. | `resources/views/welcome.blade.php:2436–2491` |
| Model `saving` hook nulls `section` on any line row at save time — defense-in-depth backstop | `app/Models/BulletinLine.php` |
| Today's published_snapshot refreshed from the cleaned DB data | (one-time tinker run) |

**Backup (memory of how it was):**
- `storage/backups/bulletin_lines_pre_section_fix_2026_05_23.sql.gz` (3.5KB gzip, all 126 rows with original section values)
- Rollback command embedded in the migration's `down()`: `gunzip -c <backup>.sql.gz | mysql shalom_app`
- Audit log row written: `event=bulletin_section_field_cleaned` with row count

**Verification:**
- 47 → 0 poisoned line rows after migration
- 21 section_header rows with section titles intact
- Today's bulletin (id=28) snapshot refreshed: 9 polluted snapshot lines → 0
- PDF renderer needed no changes (it was already only reading `kind=section_header` rows correctly)

**What changes for Andre:**
- Drag a section header → that's the section header that renders. Position determines which lines belong. WYSIWYG.
- Adding a one-off line in the middle: no hidden field to forget about, no phantom appears.
- Removing a section header: actually removes it. Nothing under the surface fights him.
- "Load Standard Order" on a new bulletin: only the explicit "Pastoral Greetings / Baby Dedication" row carries that title. The 15 lines underneath it have no section attribute.

**Architectural note.** The `section` column was retained (not dropped) because section_header rows still legitimately need it to store their title. What changed is the *meaning*: section is now strictly "the title of this section_header row" — never a per-line grouping attribute. The model boot hook enforces this so future code can't reintroduce the bug.

---


## 2026-05-23 · SHORT_MESSAGE_GATE — Children's Day / brief-homily safeguard

**The problem.** The deterministic boundary detector picks the longest gap-free span of caption speech. On services dominated by recitations, songs, or kids' performances (Children's Day, youth Sabbath, music Sabbath, baby dedications), that span can be tiny — a 4-minute pastoral blessing, a 3-minute closing prayer, an emcee linking recitations. None of those are preachable content for a 2am seeker, but the pipeline used to auto-publish them anyway.

**The fix.** After `peace:process` completes, check two thresholds:

| Signal | Threshold | Why |
|---|---|---|
| Detected sermon span | < 6 min (360s) | Real sermons run 20–40+ min |
| Words inside that span | < 800 | A thin span might be long but caption-sparse (e.g. mostly singing) |

If **either** trips → `processing_status = 'short_message_review'`, `published_at = null`, **no review email, no Google/Bing ping, no Find Peace exposure**. Karlon gets a different heartbeat email subject:

> `[The Church of Peace] ⚠ Short message held — review needed: <title>`

From `/admin/peace/{slug}/edit` he can publish manually if the message is preachable, or discard the week. The normal "Sabbath sermon processed" email still fires for real sermons unchanged.

**New helper.** `wordCountWithinSpan(transcript, startSec, endSec)` parses VTT/SRT timestamps and counts only the words inside the detected sermon window — not the full video. Falls back to counting the whole blob if no timestamps are present (so the gate doesn't false-trip on raw transcripts).

**Files:** `app/Console/Commands/ScanChannelCommand.php`

**Tested:** smoke test on synthetic VTT — full-blob count 23 words, 0-30s window 6 words, 360-400s window 17 words, fallback path returns 6 on a no-timestamp blob. All directionally correct.

**First live test:** tomorrow (2026-05-24, Sabbath) at the 3pm scan. Children's Day — likely the gate trips and Karlon gets the "held for review" email Sunday morning instead of a junk Peace page going live overnight.

---


## 2026-05-23 · Admin font refresh + all outbound email now reads "The Church of Peace"

**Two changes shipped together:**

**1. Admin typography swap.** Karlon flagged the admin fonts looked off. Replaced the old admin stack (JetBrains Mono headings, Poppins body, Cormorant numbers, Instrument Sans labels) with the two fonts he picked:

- **Varela Round 600** — all admin headings (h1/h2/h3), labels, eyebrows, meta text
- **Noto Serif 400** — all admin body copy (paragraphs, table cells, inputs, buttons)
- Monospace preserved where it carries meaning (`code`, `pre`, `.mono`, `.num` cells)

**Scoped to admin only.** Public site (/, /hymnal, /bible, /peace-notes, Find Peace, etc.) untouched — they don't include the admin typography partial.

**How it propagates:** all changes went into the single shared partial `resources/views/admin/partials/_typography.blade.php`. Added `@include('admin.partials._typography')` to the 11 admin blades that weren't already pulling it in (security, changelog, anthropic-usage, peace/index, peace/polls/*, peace/submissions, peace/schedule, peace/analytics, peace/edit, peace/subscribers). Future admin font tweaks = edit one file.

**2. Outbound email now says "The Church of Peace" everywhere.** "Shalom" was leaking into email subjects and bodies. Replaced in every `Mail::raw` call site:

| File | What changed |
|---|---|
| `SendAnthropicWeeklyReport.php` | Subject `[Shalom]` → `[The Church of Peace]`, header line, footer line |
| `AnthropicUsageLog.php` | Expensive-call alert subject + body |
| `ScanChannelCommand.php` | Sabbath sermon processed subject |
| `AdminPeaceController.php` | Pastor reply signature ("— A pastor at The Church of Peace") |
| `PrayerController.php` | Prayer ack body + footer |

Internal code comments and the ClaudeAssistant system prompt still reference "Shalom" — those don't hit inboxes, left alone.

---


## 2026-05-23 · Pre-Sabbath chain test + scan hardening

**Tested the full peace:scan-channel chain end-to-end before tomorrow's 3pm fire.** Discovered three real things:

1. **Anthropic API credit exhausted** — Pass 2 (Q&A generation via Claude) returns 400 "credit balance is too low". **MUST top up at https://console.anthropic.com/settings/billing before Sabbath 3pm ET** or no Q&A page will publish.

2. **"Prayer & Fasting" + "Worship through Warfare" weren't in the skip-patterns** — scan was picking these non-Sabbath services as sermon candidates. Added to TITLE_SKIP_PATTERNS: `prayer & fasting`, `worship through warfare`, `watch night`, `all night prayer`.

3. **No fall-through when captions missing** — if the most-recent video had no captions yet, the whole scan failed instead of trying the next candidate. Added CAPTION_PRECHECK: scan now iterates through candidates and picks the first one with captions available. Verified: skipped a captions-pending video, picked the next one with 2,776 caption events.

**Files:** `app/Console/Commands/ScanChannelCommand.php`

---

## 2026-05-23 · Find Peace gets its own stylesheet — architectural separation

**What changed:** Find Peace now loads `/public/css/find-peace.css` instead of `shalom.css`. Two separate stylesheets for two separate products. Edit `find-peace.css` to change the seeker experience; edit `shalom.css` to change the church site.

**Why:** Find Peace is the outreach door — for the 2am seeker, not the church. The previous setup had Find Peace hardcoding `data-theme="default"` to opt out of site themes, but that was a workaround. Real fix: Find Peace gets its own stylesheet that doesn't even contain the theme machinery. Cleaner separation, no workarounds, two products living side-by-side.

**Find Peace stylesheet handles:**
- Calm parchment + teal palette in light mode (brass tokens map to teal for cool accent feel)
- Auto-respects visitor OS dark mode (prefers-color-scheme: dark)
- AAA contrast in dark mode: ink #ededed (13.7:1), ink-soft #9e9e9e (6.4:1)
- No theme overrides (Find Peace stays consistent regardless of church site theme)
- Own @font-face for Xtreem, own universal reset

**Files touched:**
- New: `public/css/find-peace.css`, `resources/views/partials/find-peace-vars.blade.php`
- Modified: all 5 `find-peace/*.blade.php` views (swapped partial, removed inline :root + @media dark blocks, simplified body tag)

---

## 2026-05-23 · Find Peace dark mode text — fixed

**What was wrong:** Karlon viewed /find-peace/stay-with-the-word-1R46Er on his iPhone with the OS in dark mode. Background went dark correctly, but body text stayed default ink (#1a2332) — invisible against the near-black background.

**Root cause:** The find-peace dark-mode block (`@media (prefers-color-scheme: dark)`) overrode `--bg` and `--brass` but never touched `--ink` or `--ink-soft`. So text inherited the light-mode color tokens.

**Fix:** Added `--ink: #ededed`, `--ink-soft: #9e9e9e`, `--line: rgba(255,255,255,0.10)` to the dark-mode :root block in every find-peace blade. Same contrast values the dark-mode partial uses (ink 13.7:1 AAA, ink-soft 6.4:1 AA).

**Files:** `resources/views/find-peace/show.blade.php`, `index.blade.php`, `saved.blade.php`, `share.blade.php`, `topic.blade.php`

---

## 2026-05-23 · Find Peace isolated from site theme — outreach door stays calm

**What changed:** All find-peace/* pages now hardcode `data-theme="default"` on their body tag. The site theme picker (Communion / Easter / etc.) no longer affects Find Peace pages — they always render in the neutral parchment + teal palette regardless of what the church admin sets.

**Why:** Two doors, one Christ. The site theme is for the church community (Sabbath colors, liturgical days). The dechurched seeker arriving at Find Peace at 2am needs a calm, consistent, neutral space — not the church's liturgical calendar. From the foundation doc principle: "Two doors, one Christ. They share content but never share framing."

**Files:** `resources/views/find-peace/index.blade.php`, `show.blade.php`, `saved.blade.php`, `share.blade.php`, `topic.blade.php`

---

> A plain-English log of every notable change to the site.
> Newest at the top. For deeper context see `/Users/karlon/Documents/shalom/docs/`.

---

## 2026-05-21 · Slide upload silently broken — fixed

**What was wrong:** Karlon tried to upload a JPG hero slide. The page rejected every attempt, and the file the server received showed as `tempImageABv4zQ.heic` even though Finder said the source was `family.jpg` or `DSCF1752.jpeg`.

**Root cause:** macOS Safari quietly changed how it handles `<input accept="...,.heic,.heif">` recently. Old behavior: Safari converted HEIC → JPEG on upload regardless. New behavior: if the site lists `.heic` in accept, Safari honors that and hands over raw HEIC, skipping the conversion. Our slides form has had `.heic` in accept since the initial April 26 commit — the bug was latent for weeks, then surfaced when Apple shipped the Safari update.

**Fix:** Removed `.heic`, `.heif` from the file-input accept attribute. Safari resumes its native HEIC → JPEG conversion. Server only ever sees true JPEGs now.

**Where:** `resources/views/admin/slides/index.blade.php` (lines around the file input)

---

## 2026-05-21 · Hero slides cross-linked from Pages > Home editor

**What changed:** The slide upload page was orphaned at `/admin/slides` — accessible only via its own card on the admin hub. Now it's also linked from inside the Pages > Home / landing editor with a clear "Hero slides live here →" callout. The slides admin also reminds you it's a home-page surface.

**Why:** When future admins (or future Karlon at 2am) edit the home page, slides are part of that experience and should be findable from there.

---

## 2026-05-21 · v1.0 tagged

**What:** Tagged the production-hardened state of the app as `v1.0` on the server (`git tag v1.0` in `/home/shalom/laravel`). Documented in `00-FOUNDATION.md`, `ECHO.md`, and `v1.0.md` in `/Users/karlon/Documents/shalom/docs/`. Companion memory file `version_1_0_shipped.md` saved.

**What's in v1.0:** bulletin three-layer Go Live, snapshot safeguard, prune cron narrowed, no-cache headers for admins, gap-based boundary detector for Find Peace, sermon scan retry + sanity-check + heartbeat emails, yt-dlp wrapper cookieless, all the post-mortem fixes from the all-night session.

**Restore command if needed:** `git checkout v1.0` in `/home/shalom/laravel`

---

## 2026-05-21 · Sermon boundary detector switched from AI to deterministic gap heuristic

**What was wrong:** The auto-scan kept picking the wrong sermon start. For "Don't Fall Asleep" (May 17) the AI guessed `01:24:03` for sermon start, but the actual start was `01:40:56`. Every week's Q&A was being generated from a 17-minute slice that included children's-story material instead of the sermon.

**Why the AI was failing:** The old detector fed Claude only the "last 90 minutes" of the stream's transcript, assuming sermons live at the end of services. For Shalom that assumption broke regularly — sermons sometimes start at minute 100 of a 150-minute stream, sometimes much earlier.

**Fix:** Replaced the AI call with a deterministic gap-detection algorithm. The sermon is identified as the longest gap-free span of speech in the caption track. Runs in <50ms, no API tokens, no guesswork. For Wake Up: picked 01:40:56–02:24:19 (43:22 of sustained speech) — within 26 seconds of ground truth.

**Files:** `app/Services/Peace/BoundaryDetector.php`, `app/Console/Commands/ProcessSermonCommand.php`

---

## 2026-05-21 · Bulletin snapshot empty-state bug — root cause + fix

**What was wrong:** Andre published bulletin #27 four times on May 16 Sabbath morning because the public view kept showing an empty bulletin. By May 21 the `published_snapshot` column was literally `null` — public view showed nothing.

**Root cause:** The `bulletins:prune` cron job at 3:30am daily was nullifying `published_snapshot` for any bulletin > 24h past Sabbath. The original intent was to reclaim disk space, but it was nuking small JSON columns instead of the actual heavy PDF files.

**Fix:**
1. Narrowed the prune to only null `pdf_path` (and unlink the actual PDF file from disk). Snapshot + body text stay forever.
2. Added `SNAPSHOT_SAFEGUARD` in `BulletinPublisher::publish()` — throws if snapshot would write empty, so publish can no longer silently corrupt.

**Files:** `routes/console.php`, `app/Services/BulletinPublisher.php`

---

## 2026-05-21 · iOS Safari stale-page cache for admins — fixed

**What was wrong:** Andre published a bulletin, then his iPhone's Safari kept showing him the pre-publish version. He didn't realize the publish had actually worked — kept hitting Go Live thinking it was broken.

**Root cause:** Safari's back-forward cache (bfcache) ignores `Cache-Control: no-cache` in many situations. Only `no-store` reliably prevents it.

**Fix:** New `NoCacheForAdmins` middleware appends `Cache-Control: no-store, no-cache, must-revalidate, max-age=0`, `Vary: Cookie`, etc. to every response when the user is authenticated as super_admin or clerk. Guests still get normal caching for performance.

**Files:** `app/Http/Middleware/NoCacheForAdmins.php` (new), `bootstrap/app.php`

---

## 2026-05-21 · Go Live publishing — three layers of resilience

**What was wrong:** Karlon tried to publish the bulletin from his iPhone. The Go Live button silently failed — the POST never reached the server. JS click handler was broken on iOS Safari.

**Fix:** Wrapped the button in a real `<form method="POST">`. Three execution paths now:
1. **XHR fast path** — JS intercepts, calls endpoint, smooth in-place update
2. **JS-error fallback** — if XHR throws, JS triggers native form submit
3. **Pure form fallback** — even with JS fully broken, button is `type="submit"` inside form, native POST works

Server controller returns JSON for XHR or redirects-back for form submit — handles both.

**Files:** `resources/views/welcome.blade.php`, `app/Http/Controllers/BulletinController.php`

---

## 2026-05-21 · Bulletin save UX — flash on inline saves + auto-reload after publish

**What was wrong:** Andre and Karlon kept asking "did this save?" — no visual confirmation. The little toast was easy to miss.

**Fix:**
1. After any inline save (line text, person name, announcement), the edited element gets a 1.3-second green flash (`.just-saved` CSS class).
2. After Go Live succeeds, 700ms delay (so the "Live." toast is visible), then `location.reload()` — page reflects fully-published state with no ambiguity.

**Files:** `resources/views/welcome.blade.php`

---

## 2026-05-21 · Sabbath sermon scan — failure safety nets

**What was added:**
1. Saturday 3pm ET primary scan + **4:30pm ET retry** — catches the case where YouTube auto-captions weren't ready at 3pm.
2. **Sunday 12pm ET sanity check** — emails Karlon (CC Andre) if no sermon was processed since Saturday 00:00 ET. Silent on success.
3. **`emailOutputOnFailure`** on both Saturday fires — Laravel mails the captured output if either scan crashes.
4. **Success heartbeat email** — every successful scan emails Karlon with title, ID, length, URL. Absence of email = signal something's off.

**Files:** `routes/console.php`, `app/Console/Commands/ScanChannelCommand.php`

---

## 2026-05-21 · yt-dlp wrapper rewritten cookieless

**What was wrong:** Sermon audio extraction failed with "Requested format is not available." YouTube was only returning storyboard images.

**Root cause:** The wrapper at `/home/shalom/bin/ytdlp-auth` was passing stale session cookies. YouTube had flagged the session and stopped returning audio/video formats — only storyboards. Calling raw `/usr/local/bin/yt-dlp` (no cookies) returned full formats.

**Fix:** Rewrote the wrapper without `--cookies`. Public-channel sermons don't need authentication. Cookies file kept on disk for any future case that needs them (re-enable with explicit flag). Backup of old wrapper at `ytdlp-auth.bak.20260520`.

---

## 2026-05-21 · Drag handles visible on touch devices

**What was wrong:** On iPhone, the drag-handle dots (⋮⋮) on bulletin lines were too faint to see — they were at opacity 0.35 by default, only popping to 0.85 on hover. Touch devices don't hover.

**Fix:** `@media (hover: none)` rule sets handles to 0.7 opacity (teal color) on touch devices — visible without needing hover. Desktop stays unchanged (calm at 0.35, pops on hover).

---

## 2026-05-21 · "Apply compressor only" button

**Added:** On the sermon edit page, a second button next to the compressor dropdown — "Apply compressor only →" — re-encodes the current audio with the selected compressor at the same boundaries. No yt-dlp re-download, no API cost, atomic file replace.

---

## 2026-05-21 · Trim endpoint typo — fixed

**What was wrong:** Karlon updated the start point on Wake Up sermon via the admin trim form, got "Oops! An Error Occurred." Internal log showed `Call to undefined function App\Http\Controllers\ename()`.

**Root cause:** A line in `AdminPeaceController::trim()` literally read `@\nename(...)` — the `r` had been dropped from `@rename` by an earlier accidental sed/edit. PHP parsed it as a call to nonexistent function `ename()`.

**Fix:** Fixed the typo back to `@rename(...)`. Tested + PHP-lint clean.

---

## 2026-05-21 · Find Peace autocomplete scope-leak — fixed

**What was wrong:** Andre typed pastor names into the person field on a bulletin line right after an "Opening Hymn" line — got hymn suggestions, no people. Said he gave up and just typed manually.

**Root cause:** The scope detector for autocomplete used `$prevPart` (previous line's part text) in its haystack. So "Opening Hymn" leaked the "hymn" scope into the NEXT line, even if that next line was an Intercessory Prayer that should have been person-scoped.

**Fix:** Dropped `$prevPart` from the haystack. Each line's scope is determined only by its own part + section. No more cross-line contamination.

---

## How to add to this changelog

Edit `/home/shalom/laravel/docs/CHANGELOG.md` directly on the server, or via the admin page at `/admin/changelog` (read-only display). Convention:

```
## YYYY-MM-DD · Short headline

**What was wrong / what changed:** plain English

**Root cause:** if it was a bug

**Fix:** what got done

**Files:** if relevant
```

Newest at the top. Be honest about what was a bug vs a feature. Future readers (including future you) will thank you.
