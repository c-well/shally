# Shalom — Changelog

## 2026-07-04 · VERSION 1.5 — declared by Karlon

The line crossed: from "a church website with an editor" to ONE SOURCE OF TRUTH wearing many faces. The bulletin is typed once and becomes: web bulletin, portrait PDF, 2-up print sheet, QR-linked /announcements page, and the living /calendar (month/week/day, filters, edit-mode with write-back). Recurring events described once (or Smart-filled by AI from Andre's own words). Find Peace messages carry summaries, scriptures, share, and transcript-deep search. The system teaches its own users (/guide). Zero broken links, first-party analytics with heatmaps on every page, spam-hardened, mobile-first everywhere, all shipped zero-downtime.

v1.6 arc (named): calendar share buttons · Year view · calendar into nav + homepage strip. v2.0 headliner: the Peace pipeline goes automatic.

## 2026-07-04 · Announcements: print/digital split + QR + /announcements page

The paper and the web are now one loop (Karlon/Rosharde). (1) NEW public page /announcements — every announcement for the week (printed + digital), mobile-first cards with the Mission Statement featured, fed from the same bulletin snapshot (published for the public, live draft for clerks). A shadowing empty public/announcements dir was removed to free the URL. (2) The PRINTED bulletin now carries only what Rosharde puts above a new teal dashed divider in the editor — "PRINTED BULLETIN ENDS HERE · QR LEADS PEOPLE TO THE REST." Moving a row across the divider with ↑/↓ automatically flags it web-only (new is_web_only column, snapshot-carried, PDF-filtered before folding); web rows show a WEB chip. She can add 10+ digital-only announcements without touching the paper. (3) Both PDFs print a QR CODE (self-hosted, generated once, base64-inlined — dompdf chokes on transparent PNGs by path) after the last printed announcement, above "Have a pleasant Sabbath", captioned with the URL. (4) Footer address now carries the zip: 3323 White Plains Rd, Bronx, NY 10467 on both PDFs. Verified end-to-end: web-only test row absent from print, QR renders, /announcements 200, editor divider live.

**Files:** database/migrations/2026_07_04_add_web_only_to_announcements.php, app/Models/{Announcement,Bulletin}.php, app/Http/Controllers/BulletinController.php, routes/web.php, resources/views/announcements.blade.php (new), resources/views/admin/bulletin.blade.php + admin/partials/_ann-row.blade.php (new), resources/views/bulletins/pdf{,-2up}.blade.php, public/qr-announcements.png

## 2026-07-04 · Announcement parents/children + 2-up front page fits

Rosharde's pattern is now the feature: an announcement row with a BLANK title means "these lines belong to the announcement above" — on both PDFs it folds in as extra bullets under the parent (her "Upcoming Events:" parent now shows the Evangelistic series AND the 12/6 Retro Night row that used to vanish). Manual bullet markers she types (o, -, •, *) are stripped so bullets don't double up. Folding happens at PDF render only (Bulletin::foldAnnouncements); the editor and public page keep rows separate so per-row inline editing still works. Verified on the live bulletin: blank-title row present in data → rendered as a child bullet.

Also: the 2-up front page was clipping its footer (the email line). Tightened the front column's rhythm — slightly smaller header (16pt church name), 10.5pt program rows, trimmed margins — so all 18 rows + the full 3-line footer INCLUDING the email fit with breathing room, no tiny fonts, still exactly 2 pages.

**Files:** app/Models/Bulletin.php, resources/views/bulletins/pdf.blade.php, resources/views/bulletins/pdf-2up.blade.php

## 2026-07-04 · Calendar — Phase 2 (Day + Week + instant view engine)

Studied the real adminkc/genesis calendar as root (Karlon's go-ahead) and ported its fluidity pattern: load the data ONCE (controller ships a 36-month aggregated window as JSON), render every view client-side — so Day/Week/Month switching and month-to-month paging are INSTANT, zero round-trips (the genesis trick: views are pure renders over one local dataset). Shipped: Week view (7 columns, today ringed in teal, entries with time+location), Day view (typed cards — Service/Sermon/Event with source links), tap-any-day bottom-sheet with that day's cards, keyboard nav (arrows + T for today), and DEEP LINKS — /calendar?v=week&d=2026-07-04 opens exactly that view and the URL updates as you browse, so any view of any date is copy-linkable (first brick of the share feature). Year stays a labeled "soon". Still unlinked from nav while it matures.

**Files:** app/Http/Controllers/CalendarController.php, resources/views/calendar/index.blade.php (new; month.blade.php superseded)

## 2026-07-04 · Calendar — Phase 1 (aggregation + month view)

The events area is becoming a living calendar (Karlon's v2 vision). Phase 1 shipped: a public month view at /calendar that AGGREGATES three existing sources into one grid — Events (dept-colored), Bulletins (each service_date → "Sabbath Worship"; the Sermon line → "X preached"), and the dated sermon archive (fills history no bulletin covers). Proven live: "Pastor Kevin Brown preached" on the calendar is pulled from the bulletin's sermon line — nobody typed it into the calendar. One source of truth: add a date to a bulletin, the calendar knows. Shalom palette only (teal=services, brass=sermons, green=events), Instrument font, woven into the site (shared header/menu/CSS). Deployed UNLINKED (nav unchanged) so the live team isn't disturbed while it's built out. Decisions locked: public-to-view + managers-edit; "editable in the calendar" will write back to source (no drift). Next: Week/Day views, inline editing w/ write-back + autosave, share/copy-link, then Year view, then per-device responsive passes.

**Files:** app/Http/Controllers/CalendarController.php (new), resources/views/calendar/month.blade.php (new), routes/web.php

## 2026-07-04 · Announcements reorderable + PDF mission spacing

Two bulletin tweaks (Karlon). (1) Announcements can now be **reordered** in the bulletin editor exactly like the order-of-service lines — ↑/↓ buttons on each announcement, autosaves the new order (new `reorderAnnouncements` controller + `PATCH /bulletins/{b}/announcements/reorder` route, mirroring reorderLines). The announcements relation already ordered by sort_order everywhere (editor, public bulletin, both PDFs), so a reorder shows up immediately in print. Verified end-to-end: reverse then restore round-trips cleanly. (2) The **Mission Statement** was cramped against the announcement below it on the PDF — added margin-bottom to `.mission` in both pdf.blade.php and pdf-2up.blade.php so it breathes like the other blocks.

**Files:** app/Http/Controllers/BulletinController.php, routes/web.php, resources/views/admin/bulletin.blade.php, resources/views/bulletins/pdf.blade.php, resources/views/bulletins/pdf-2up.blade.php

## 2026-07-04 · Bulletin 2-up print layout

New "Download 2-up" option in the bulletin tools (alongside Download PDF): one LANDSCAPE letter sheet with the bulletin printed twice side by side — front sheet = order of service ×2, back sheet = announcements ×2, aligned. Print double-sided and cut down the dashed center line → TWO identical 5.5×8.5 bulletins per sheet (participants front, announcements back). Both halves identical, so the duplex flip direction doesn't matter. Delivered via ?layout=2up on the existing PDF route (setPaper landscape + a self-contained bulletins/pdf-2up view — the portrait pdf.blade.php is untouched). dompdf gotcha solved: side-by-side full-height columns must be position:absolute inside a fixed-size relative .sheet, else dompdf inserts phantom blank pages (tables and floats both did; absolute gives a clean 2 pages).

**Files:** resources/views/bulletins/pdf-2up.blade.php (new), app/Http/Controllers/BulletinController.php, resources/views/partials/site-menu.blade.php

## 2026-07-04 · Cormorant banned from admin + all numerals (Karlon)

Cormorant Garamond's oldstyle figures render numbers as squiggles ("July 2026" on the schedule, the "9"/"3" on event tiles). Per Karlon: removed Cormorant from the ENTIRE admin area — all 29 admin views (+ /schedule) now use Instrument Sans for display text (JetBrains Mono headers stay); the unused Cormorant font load was stripped from those pages too. On the public side, the three numeral-bearing spots (event tile day, duty-roster date, peace-notes cover date) switched to Instrument Sans — Cormorant remains for public prose and headers, where it belongs.

Also repaired two self-inflicted breaks from the earlier arrow sweep that Blade compiled but PHP rejected at render (glyphs inside @php/echo strings became nested-quote includes): the admin hub's card data (hub was erroring live behind the auth wall — public probes can't see admin pages) and the sermons form save button. Added the missing verification step to the toolkit: php-lint every COMPILED view after bulk blade changes — all views now lint clean.

**Files:** 29 admin views + schedule.blade.php, welcome.blade.php, peace-notes.blade.php, admin/hub.blade.php, admin/sermons/form.blade.php

## 2026-07-04 · Arrows systemized (Untitled UI circle set) + honest analytics + SEO verdict

Karlon's call: the tiny text arrows (→ ↗) looked lame. All 200+ glyphs across 65 views are now real SVG icons from Untitled UI's circle-arrow set, served from THREE partials (partials/_ar, _arup, _arl) — restyling every arrow on the site is a one-file edit. CMS-stored page content (pages.body_html) gets the same treatment at render time via a new Str::arrowize() macro, so editors can keep typing → and the site draws the icon. Email templates deliberately keep text glyphs (mail clients strip SVG).

Also: the bot filter now rejects crawlers spoofing museum-piece browsers (Chrome ≤99, Windows 7, iOS ≤13) and 4,315 junk rows were purged — analytics dropped from 15,131 to 10,821 honest views. SEO audit verdict: on-site plumbing is genuinely good (Church schema, unique titles, canonicals/OG, 124-URL sitemap, FAQ/Article schema on Find Peace); the growth lever is off-site — Google Business Profile above all.

**Files:** partials/_ar{,up,l}.blade.php (new), 65 swept views, app/Providers/AppServiceProvider.php (arrowize), app/Http/Middleware/TrackPageView.php

## 2026-07-03 · Fix: Adjust-image editor was dead (CSP) + Sariah's photo righted

Andre's report: the Adjust editor's Save/rotate buttons did nothing. Root cause in his console: the site's Content-Security-Policy (correctly) blocks cdnjs.cloudflare.com, so Cropper.js never loaded — the feature was dead in real browsers from day one, and my verification missed it because I tested the rendered page outside the live origin where the CSP header doesn't apply. Fixed by SELF-HOSTING Cropper (public/vendor/cropperjs/) — no CSP loosening, no CDN dependency; verified both assets serve 200 from the site itself. Also fixed Sariah's slide directly: her photo carries EXIF orientation 3 (upside down) which browsers honor but GD ignores (no exif extension) — auto-oriented the file (original preserved), regenerated, verified upright. Swept all other grad photos: none affected. And closed the class: the renderer's EXIF handling now falls back to ImageMagick identify via Process when the exif extension is absent, so future rotated uploads render upright automatically.

**Files:** resources/views/admin/intake/submissions.blade.php, app/Services/Intake/GradCardRenderer.php, public/vendor/cropperjs/*

## 2026-07-03 · Fix: emoji on grad cards rendered as garbled characters

Andre's catch: Melody's slide showed "winding characters" — her thanks line ends with 🙌🏾, and the card renderer draws text with IBM Plex/Poppins TTFs through GD, which have no emoji glyphs, so emoji come out as garbled boxes that read like an error. The renderer now strips emoji/pictographs (and their skin-tone/joiner modifiers) at draw time and tidies the spacing/punctuation left behind — "kept me🙌🏾." renders as "kept me." Typographic quotes, accents, and normal punctuation are untouched, and the submission's stored text keeps its emoji (only the drawn card is cleaned). All 8 live slides regenerated; Melody's verified clean.

**Files:** app/Services/Intake/GradCardRenderer.php

## 2026-07-03 · Fix: graduate class year wrong (2027) and uneditable

Some grad slides said Class of 2026 and others 2027: the renderer COMPUTED the year with a school-year heuristic (July onward → next year), so anything regenerated after July 1 flipped to 2027 — and since the year was computed, not stored, nobody could correct it. Now: class year is a real, editable field on the submission ("Class year" in the gallery's Edit text panel, sanitized to a 4-digit year), defaulting to the year the graduate submitted. Renderer reads the field; heuristic deleted. Backfilled all 8 submissions and regenerated all 8 live slides — every one now reads Class of 2026, verified on the live PNG. Editability stays where it belongs: intake submissions are admin-editable; the general inbox and prayer requests remain read-only.

**Files:** app/Services/Intake/GradCardRenderer.php, app/Http/Controllers/AdminIntakeController.php, resources/views/admin/intake/submissions.blade.php

## 2026-07-03 · Zero-downtime deploys (Karlon's rule) + button-spec fix

New standing rule after last night's two brief self-inflicted 500 windows: **the site never serves errors during an update, big or small.** Built `/home/shalom/bin/safe-deploy.sh` — every deploy now (1) lints all staged PHP and refuses to start on any failure, (2) backs up the files it will replace (last 20 sets kept in ~/deploy-backups), (3) raises the branded "Be right back" page for only the seconds of the swap, (4) clears caches, brings the site up, and probes /, /lesson, /find-peace, /kids for 200, and (5) **auto-rolls-back from the backups if any probe fails**. First real use: deploying today's hub fix — lint → backup → swap → all probes 200.

Also fixed a design-spec violation Karlon caught: the new hub view-switcher and search were pill-shaped (999px). The site's buttons use a small radius (3–8px across every admin surface); pills are reserved for badge bubbles. Both corrected; rule documented in HANDOFF.

**Files:** /home/shalom/bin/safe-deploy.sh, resources/views/admin/hub.blade.php, docs/HANDOFF.md

## 2026-07-03 · Admin hub — view latch (corrected per Karlon)

Redone per instruction: the hub no longer forces a new look. A **view latch at the top** gives each admin the choice — **Default** (all cards out, the exact familiar grid — nothing jarring), **Groups** (everything nested in the four hush latches), or **Smart ★** (their most-used destinations first, learned from real clicks, with everything else behind the latches below). Smart unlocks after that admin's 7th visit — until then the option shows its progress (e.g. "3/7") and stays disabled. The choice is remembered per admin (stored server-side), so each person's hub opens the way they like it. Mini search sits beside the latch and reveals matches in whichever view is active.

**Files:** resources/views/admin/hub.blade.php, routes/web.php (view-preference handling in the track route)

## 2026-07-03 · Admin hub — the hush latch

The admin hub's 26 cards were an unscannable wall. Rebuilt: everything now lives behind **four latches** — This week (bulletin, events, schedule, slides, lessons, names) · People & inbox (messages, users, intake, bug reports) · Ministries, games & site (Peace, sermons, kids/teens games, media, the six site pages) · System & insights (analytics, audit, edit history, changelog, API spend). All collapsed by default; each latch shows a count + a one-line peek; unread messages bubble up to the People latch.

**Mini search** sits above the latches — type anything ("slides", "spend", "games") and matching cards reveal instantly across all groups; Enter opens the first match; Esc restores the hush.

**It learns.** Every card click is recorded per admin (new admin_hub_usage table + track route). From an admin's **7th visit** onward, a starred "**Your most used**" latch appears first, auto-open, holding their top six destinations in click order. Verified: simulated 8 visits + clicks → smart latch present, bulletin (most-clicked) first; synthetic test data cleaned after.

Kept: theme picker, Anthropic cost-spike banner, unread badge. Added: a red **cron-down banner** on the hub when the scheduler heartbeat is >60 min stale (pairs with the new watchdog email).

**Files:** resources/views/admin/hub.blade.php, routes/web.php (track route), database/migrations/2026_07_03_create_admin_hub_usage.php

## 2026-07-03 · Audit round 2 — deep fixes

Proper class-sweep audit (after the hero-upload miss) turned up and fixed: (1) **7-day cron outage** — the account's cron silently stopped Jun 25 (heartbeat proof); revived by re-registering the crontab; fresh DB backup taken; (2) **cron watchdog** — TrackPageView now piggybacks a cache-gated check on normal web traffic: if the scheduler heartbeat goes >60 min stale it emails the super-admins (max once/6h) with the exact revive command — web traffic doesn't depend on cron, so this can't go blind the way cron did; (3) **/admin/lessons PDF upload** would 500 (mimes: rule needs the missing fileinfo) — now extensions:pdf + %PDF magic-byte check; (4) **restore-point capture** died at its last step on web (shell_exec is disabled) — now a pure-PHP glob; (5) **PDF event flyers un-broken** — exec() is disabled on web but proc_open is NOT (probe-verified), so the ImageMagick conversion now runs via Process; same fix applied to the pages + grad-card converter fallbacks (HEIC fallback path now actually works); (6) Twilio SMS call got a 15s timeout (was unbounded).

Also corrected an audit claim: the Peace "run now" button was NOT broken — proc_open works on the web SAPI (exec/shell_exec/passthru/system are the blocked ones). Probe file confirmed disable_functions exactly.

**Files:** app/Http/Middleware/TrackPageView.php, app/Http/Controllers/{AdminLessonsController,AdminChangelogController,EventController,AdminPagesController}.php, app/Services/Intake/{TwilioNotifier,GradCardRenderer}.php

## 2026-07-02 · Fix: hero slide upload 500 (fileinfo again) + full app sweep

The hero-slides upload (/admin/slides) was 500ing before validation even ran. Root cause is ironic: the upload pipeline itself is fileinfo-free (GD → webp), but a LEFTOVER DEBUG BLOCK from the old HEIC investigation ("remove once we know") called guessExtension()/getMimeType() on every upload — both need the php_fileinfo extension this host doesn't have. Removed the debug block (it long since did its job). Swept the entire app for the class: fixed the slides error-path (mime_content_type — would have been a FATAL, function doesn't exist without fileinfo) and AdminMediaController's audio/image kind detection + audio mime (now extension-derived; images still verified by GD decode, so a mislabeled file fails safely). Verified end-to-end in-process: 2400px JPEG → validated → resized → webp on disk → Slide row; test slide cleaned up. That's the third fileinfo casualty (intake download, now slides + media) — enabling fileinfo in cPanel → Select PHP Version remains the permanent fix.

**Files:** app/Http/Controllers/AdminSlidesController.php, app/Http/Controllers/AdminMediaController.php

## 2026-07-02 · Lesson quarter rollover — fixed now, automated forever

/lesson was still showing "Growing in a Relationship With God · Lesson 1" — Q2 ended June 26 and nobody (me included) added Q3, so the page fell back to Lesson 1 of the old quarter. Fixed now: **Q3 2026 · First and Second Corinthians** (Jun 27 → Sep 25) created from the real Adventech data, all 130 reading-days synced, print PDF attached, live and showing the correct current week.

Moving forward this is nobody's job anymore: new `lesson:ensure-current` runs daily at 4:20am NY. If no quarter covers next Sabbath, it derives the next year/quarter, pulls the real theme + dates from Adventech (never invents them), creates the row, syncs every reading, attaches the fustero PDF if published, and emails the super-admins that /lesson rolled over. If Adventech hasn't published the next quarter yet (they return their app shell for unknown slugs) it exits quietly and retries the next day. Verified both paths live: normal run → "covered"; forced 120-day lookahead → correctly detected the gap and correctly declined the unpublished Q4.

**Files:** app/Console/Commands/EnsureCurrentQuarterCommand.php, routes/console.php

## 2026-07-02 · Analytics fix + menu polish

Two audit items closed. (1) **Visitor analytics finally real**: the `v_sid` cookie was written raw but read through Laravel's decrypting cookie reader, so it never survived a round-trip — every page view counted as a brand-new visitor (uniques == views exactly) and no journey could be traced. Exempted it from cookie encryption (`bootstrap/app.php`); verified live: two requests, one visitor id, both paths recorded under one session. Historic "unique visitor" numbers before today are inflated — treat 2026-07-02 as day one for real visitor data. (2) **Menu polish**: the accordion chevron's off-brand green (#2d8659) → brand teal; added **Undercover (Youth)** to Spiritual Life; "Scripture Games (Kids)" → "Scripture Games" (teens play there too).

**Files:** bootstrap/app.php, resources/views/partials/site-menu.blade.php

## 2026-07-01 · Find Peace: discoverable + navigable (stays OFF the church menu)

Find Peace is deliberately not part of the church site — it's a doorway seekers stumble onto. This pass makes the stumbling work and gives them somewhere to go once they land. Audit found the sitemap already solid (124 URLs: hub + 17 published sermons + 85 topic pages, correct canonicals/OG/robots; the 5 sermons not listed are drafts/discarded — correct filtering). What was missing: (1) the index page hid everything below a full-viewport empty hero, (2) the 85 topic pages were linked from nowhere on the site (sitemap-only orphans), (3) no nav of its own, (4) no Article/AudioObject structured data on sermon pages (FAQPage existed).

Shipped: compact hero (question + Q&A cards + messages all above/at the fold), a quiet nav of its own (shalom mark + Messages · Topics · Yours), a "What are you carrying?" band with all 88 topic pills (fear, shame, loneliness… every chip a real page — internal links Google can walk), WebSite JSON-LD on the index, Article+AudioObject JSON-LD + an explicit "← all messages" back link on every sermon page. Aesthetic untouched: dark, calm, Poppins, brass.

**Files:** resources/views/find-peace/index.blade.php, resources/views/find-peace/show.blade.php, app/Http/Controllers/FindPeaceController.php

## 2026-07-01 · Intake: image adjust + refined card buttons

The submission gallery (Graduate Recognition and any photo intake) now has an **✂ Adjust image** editor: rotate a sideways photo, crop, and reshape it (Free / Square / Landscape / Portrait), then it regenerates the 1920×1080 slide. Non-destructive — the pristine upload is kept, so you can always re-crop from the original. Built with Cropper.js in the browser (the edited image is uploaded and the slide re-rendered by GD), so it doesn't depend on the missing server image extensions. Also gave the card buttons real hierarchy — **Download** is now the solid primary; Adjust / Edit text / Remove text / Remove are refined outline buttons (they'd looked flat and cramped). Verified end-to-end (validate → preserve original → save → regenerate). The same editor sets us up for pastors' sermon-note photos down the line.

**Files:** app/Http/Controllers/AdminIntakeController.php (adjust), app/Models/IntakeSubmission.php, resources/views/admin/intake/submissions.blade.php, routes/web.php, database/migrations/2026_07_01_add_photo_original_to_intake_submissions.php

## 2026-07-01 · Fix: recognition-card download 500 (fileinfo)

The per-card "⬇ Download" on the intake gallery (e.g. Graduate Recognition slides) was throwing a 500 — the branded "We'll be right back / Reconnecting" error page — because `AdminIntakeController::download()` let Laravel auto-guess the file's MIME type, and this server has **no `php_fileinfo`** (missing on web AND CLI) with the shell `file` fallback also blocked on the web SAPI. Fixed by passing an explicit `Content-Type: image/png` so the response never calls the MIME guesser. (Bulk "Download all" and the feedback-image route were already safe — explicit types; the bulletin PDF is dompdf, not affected.) Not related to the Undercover build. Root cause is environmental: enabling `fileinfo` in cPanel → Select PHP Version would fix this whole class permanently (also the HEIC-upload issue flagged earlier).

**Files:** app/Http/Controllers/AdminIntakeController.php

## 2026-06-28 · Undercover — the live game (Phases 2–4)

The teen hidden-identity mystery is fully playable at **/youth**. A leader hosts (big room code on a projector); teens join from their phones with no login. The app deals secret codenames + roles (1 Crook, 1 Cop, the rest Citizens — scales up for big rooms), then runs rounds: each round it asks everyone a question privately, and the answers build each codename's public "profile." The room talks it out and matches **who is who**, while hunting the **Crook** — who hides simply by *staying quiet* (never by lying). The **Cop** can privately check "is this codename the Crook?" twice. After the rounds, the room votes, then **everyone is unmasked** — the payoff and the get-to-know-you reward. Gentle stars to each player.

Realtime is short-poll (no WebSocket daemon on cPanel). Room actions are authenticated by room/host/player tokens and CSRF-exempt (so a LiteSpeed-cached page can't break them). Verified end-to-end with a headless 6-player game: roles dealt, rounds advanced, crook stayed silent, cop's check returned true, accusation caught the crook, scoring correct (Cop +20 catch +15 bonus, Citizens +20, Crook 0).

Still ahead: real (leader-vetted) questions beyond the 14 seeds, where to link it in the menu, and polish (reconnect, big-room tuning).

**Files:** app/Http/Controllers/MysteryGameController.php, app/Models/GameRoom.php + GameRoomPlayer.php + Mystery{Answer,Guess,Investigation}.php, resources/views/youth/{landing,host,play}.blade.php, routes/web.php, bootstrap/app.php, database/migrations/2026_06_28_000002_add_crook_vote_to_game_room_players.php

## 2026-06-28 · Undercover (teen mystery) — Phase 1: question bank

Started the teen hidden-identity mystery game ("Undercover" — working title). The room joins anonymously, the app asks everyone questions and leaks anonymized clues, and the kids work out **who is who** — and who the hidden **Crook** is (a hidden **Cop** helps). Design conviction, locked: **no one is ever asked to lie** — the app does the concealing; the Crook/Cop stay hidden simply by staying silent. No elimination.

This phase ships the foundation: the seven game tables, and a **leader question bank at /admin/mystery** (write the questions the app asks, mark which can become clues, edits save as you type) with 14 safe starter questions seeded. Card added to the admin hub. The live room (lobby, rounds, cop, accusation, reveal) is the next phases.

**Files:** database/migrations/2026_06_28_create_mystery_tables.php, app/Models/MysteryQuestion.php, app/Http/Controllers/AdminMysteryController.php, resources/views/admin/mystery.blade.php, resources/views/admin/hub.blade.php, routes/web.php, docs/spec-spot-the-counterfeit.md

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
