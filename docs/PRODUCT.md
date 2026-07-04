# The Product — Shalom-in-a-box (working name: decide)

*Drafted at v1.5, 2026-07-04, on Karlon's word: "let us ship this product."*

## What the product IS
A self-contained church operating system, proven live at thechurchofpeace.org:

| Pillar | What a church gets on day one |
|---|---|
| **The Bulletin** | Type it once on a phone → web page, portrait PDF, 2-up print sheet, autosave, drag-reorder, parent/child bullets, print/digital divider |
| **The QR loop** | Paper carries a QR → /announcements page with everything that didn't fit on print |
| **The Calendar** | Aggregates bulletins + sermons + events automatically; month/week/day; filters; edit-in-place with write-back; recurring series described once (or AI Smart-filled from plain English) |
| **Find Peace** | Sermon archive with pastoral summaries, scriptures, share, transcript-deep search — and (v2.0) the automatic YouTube→page pipeline |
| **Live** | Honest YouTube live detection + external-event tune-in buttons |
| **The Guide** | The product teaches its own volunteers (/guide slide deck) |
| **Analytics** | First-party, privacy-first: page views, click heatmaps, scroll depth, sessions — no Google, no cookies-banner hell |
| **Guardrails** | Zero-downtime deploys, spam-hardened forms, magic-link auth, WCAG AA design system |

**The pitch in one line:** *Your clerk types the bulletin once; your whole church presence — paper, web, calendar, sermon archive — stays true by itself.*

## Who it's for
Small/mid congregations (SDA first — the vocabulary is native: Sabbath, AY, Pathfinders) with a volunteer clerk and a phone. The Peace SaaS notes' market logic applies: ~50k US churches livestream; virtually all print bulletins.

## Sequencing (extends the Peace commercialization plan — one product family, not two)
1. **Now → v1.6:** Shalom stays the living reference install. Finish the calendar's public debut (share, Year, nav). Every feature built config-driven from here on (church name/colors/verse already mostly in AppSettings — audit hardcodes).
2. **Extraction audit (1 week):** inventory every Shalom-specific hardcode (name, address, YouTube channel, Twilio, themes). Move to a single `church` config + seeder. This is the real cost of "self-contained."
3. **Second install (the proof):** ONE friendly beta church, hand-deployed on the same server (separate cPanel account — we already run multi-tenant-by-account hosting for theweight/sanare/boyd). No multi-tenant rewrite yet — cPanel accounts ARE our tenancy v1.
4. **Then decide the fork:** (a) managed hosting ("we run it for you," $30–60/mo — Karlon's server economics already support 10+ installs), vs (b) true multi-tenant SaaS rewrite (~3 weeks, per Peace notes). Decision comes AFTER beta church #1 renews enthusiasm — never before validation (Peace plan rule #1).
5. **Peace pipeline** ships inside the product as the premium differentiator — no competitor auto-turns livestreams into a searchable pastoral archive.

## Decisions only Karlon can make (the v1.5 → product gate)
1. **Name** the product (Shalom is the church's; the product needs its own).
2. **Beta church #1** — who do we love enough to serve first? (Victory SDA is fresh from the crusade partnership…)
3. **Managed-hosting vs SaaS** posture — affects everything downstream; defer until after beta, but hold the question.
4. **Pricing conviction** — Peace notes suggest $30–80/mo tiers; whole-OS product supports more.

## Honest constraints
- One-man hosting operation: managed installs scale linearly with Karlon's attention. SaaS scales code but costs a rewrite + a founder-job change (Peace notes, "the pivot decision").
- The AI features (Smart fill, Peace) carry per-tenant API keys/cost — trivial ($/month), but must be metered per install.
- Don't productize past the ministry: Shalom's needs stay sovereign; the product forks from Shalom's truth, never gates it.
