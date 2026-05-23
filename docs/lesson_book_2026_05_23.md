# Lesson Book — Building a Live Production Site Solo
## Sourced from the 2026-05-23 Shalom session

This file captures the architectural decisions, failure patterns, and
systemic discipline locked in tonight. Reference it when starting the
indie app — these are the patterns that survived contact with reality.

---

## 1. Failure patterns that bit us (and the fixes)

### A. Velocity outpaced verification
**Symptom:** 7 different surfaces touched in one session (admin fonts, email rename, bulletin section fix, find-peace white screen, Sabbath gate, audio fix, watermark). Each tested in isolation. White screen, missing font partial, sermon misfire — all caught by the user, not by the system.

**Fix going forward (5 rules):**
1. Audit script runs on cron — Sabbath 7am + 9am, Wed 7pm — email on red
2. Pre/post snapshot before any change; halt if unexpected drift
3. One domain per session — admin/bulletin/find-peace/pipeline batched
4. Pipeline-critical changes get a dry-run soak test before live fire
5. Every session ends with the audit run, not just the targeted check

### B. Dual source of truth → phantom bugs
**Symptom:** `bulletin_lines.section` populated on both header rows AND regular line rows. Renderer auto-injected "Pastoral Greetings" pills from the line-row values. User couldn't delete them because they weren't real DB rows.

**Lesson:** *one column, one meaning, always.* If a field's purpose ambiguous between two row types, the renderer will eventually disagree with the editor.

### C. Pulled archives instead of new content
**Symptom:** Scanner pulled a month-old North Bronx simulcast (Dr. Calvin Watkins) because today's video had no captions yet. No "newness" guardrail. Auto-published a sermon from a different church under Shalom branding.

**Fix:** Watermark pattern — high-water mark stored as the most recent upload_date considered. Scanner cannot reach backward. Archive access is a separate, deliberate, admin-only flow.

### D. Cron environment ≠ shell environment
**Symptom:** Manual SSH tests passed because PATH included `/usr/local/bin/`. Cron stripped PATH; yt-dlp couldn't find ffmpeg.

**Lesson:** *Test the actual execution environment, not a similar one.* Cron, web SAPI, queue workers, and SSH each have different env. Pass binary paths explicitly. Never rely on PATH for production-critical tools.

### E. Inline CSS variables with no fallback → undefined → browser white
**Symptom:** find-peace/index.blade.php missing the partial that defines `--bg`, `--ink`. `var(--bg)` resolved to `initial` → transparent → browser Canvas → white.

**Lesson:** *Always defensive-fallback critical CSS.* `var(--bg, #faf7f0)` costs zero, prevents a class of bug forever.

---

## 2. Patterns to copy forward

### The watermark / high-water mark
For any "scan a stream and process new items" pipeline:
- Persist the most-recently-seen item's timestamp (or ID)
- Filter all future candidates to > watermark
- Advance the watermark on every successful pass (whether you picked something or not)
- Archive access is a separate, manual, deliberate flow

### The gate before the spend
Before any operation that costs money:
- Show user the candidates (free reconnaissance)
- Let them see what would be processed (transcript, metadata, span detection — all free)
- Show estimated cost above the action button
- User clicks → spends

For Shalom: list channel videos (free) → fetch transcript (free) → gap-detect spans (free) → show with estimated cost → user clicks → Claude generates ($0.07).

### Self-validation gate before publish
For any AI-generated content pipeline:
1. Output file exists + has nonzero size
2. Output references appear in the source (e.g. heart-line keywords in transcript)
3. Claude sanity check: "does this output match this source?" → yes/no
4. Fail any check → status=needs_review, no publish, log which check tripped

### Notification + recheck + fallback
For "ask the human" handoffs:
- t=0 — send notification, mark `awaiting_review`
- t=30min — unopened? resend, mark `awaiting_review_v2`
- t=60min — still unopened? publish without the optional piece (e.g. audio), flag for cleanup
- User approves later → attach the optional piece

### Single source of truth for shared resources
- One stylesheet for one surface (find-peace.css vs shalom.css — separate products)
- One layout for one surface (@extends('layouts.peace')) — no per-view <head> duplication
- One column = one meaning
- One partial = one job

---

## 3. Self-healer architecture (locked tonight)

### Triggers
- A: cron-itself-didn't-fire → no heartbeat detected
- B: cron ran but command crashed → exit code or log scan
- C: cron clean but site has 5xx / health probe fails

All three pipe to a dedicated Claude inbox. Fixer reads, diagnoses, applies fix.

### Schedule
- Sabbath 7am + 9am ET — pre-service health checks
- Wed 7pm ET — mid-week check
- Continuous between → any A/B/C failure → email immediately

### Spending gate
- ≤ $1 per fix → autonomous
- > $1 → email approval first (per Karlon's existing $1 circuit breaker pattern)

### UI surface
- Persistent admin banner if last audit failed
- "FIX" button on banner dispatches server-side Claude
- Updates / Fixes tab folded into existing /admin/changelog page
- Each fix logs: what failed, what was changed, cost, diff

### Kill switch
- One toggle in admin → pause auto-fixer entirely
- Required for safe mid-deploy windows

---

## 4. Operational decisions for the indie app

### Storage / CDN
- Don't pay host for storage upgrades until used > 80%
- Migrate large media (audio, video, images > 1MB) to object storage at ~50 entries
- Recommended: Cloudflare R2 ($0.015/GB-month, free egress) or Backblaze B2 ($0.005/GB-month)
- Front the whole site with Cloudflare (free tier) for cache + bandwidth offload

### Email / notification
- One inbox per system role (admin@, billing@, peace-reviews@, alerts@)
- All outbound mail tagged with `[Product Name]` in subject
- Track cost-per-API-call with circuit breaker (alert on single call > $1)
- Weekly spend summary on Sundays

### Audit discipline
- 14-point health audit runs on schedule, not on-demand
- Banner persists on red
- Audit is THE source of truth for "is the system healthy"
- New features add audit checks; the audit grows with the product

### Cron / scheduled job hardening
- Pass binary paths explicitly (never trust PATH)
- Wrap exit codes; log to file + DB row
- Heartbeat log: every cron fire writes a sentinel
- Health probe: "last cron heartbeat < 5 min ago"

### Pipeline self-validation
- Free reconnaissance before paid operation
- Validation gate after paid operation
- Estimated cost shown before user clicks the spending button
- Audit trail: every $ spent linked to what triggered it

---

## 5. Naming conventions that paid off
- WATERMARK — knowable, googleable, reusable pattern name
- SHORT_MESSAGE_GATE — explicit intent
- SECTION_OF_TRUTH — names the architectural decision (not just the code)
- EXPENSIVE_CALL_ALERT — what the code does, in plain English

Use ALL_CAPS for behavior-defining constants/comments. Search-friendly. Lets a future Claude session find the pattern by name.

---

## 6. The honest answer to "is the code too complex?"

No. A normal Laravel app at ~30-40k LOC. Bigger codebases ship without these issues daily. The bug rate isn't code complexity — it's:
- Solo dev = no second pair of eyes
- No staging = changes go to prod
- Velocity > verification (last session was 7 surfaces in one sitting)

The fix isn't simpler code. It's more discipline, automated checks, and slowing the change cadence on critical paths. The systemic rules in section 1 are the fix.

---

*Saved 2026-05-23 by Claude (Sonnet 4.5) at the end of a 4-hour live session. If you're reading this from the future, run the audit script first before believing anything in this file is still current.*
