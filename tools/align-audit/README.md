# Alignment / Layout Audit

Headless-Chrome audit that reports layout problems in text — no eyeballing.
Catches: horizontal overflow, left-edge drift between repeated components
(the ".msg.unread 3px" bug class), tiny tap targets, off-canvas elements.
Runs at desktop (1280) + mobile (390).

## One-time setup
    cd tools/align-audit && npm i

## Run
    node audit.mjs                    # default public page list
    node audit.mjs / /find-peace      # specific paths
    SH_COOKIE="church-of-peace-session=..." node audit.mjs /admin/messages

## Notes
- Drives the system Google Chrome via puppeteer-core (no browser download).
- Polite to the shared host: blocks images/fonts/media, throttles between
  loads, retries once on 429/503. Still, run it sparingly against production
  (LiteSpeed will rate-limit a fast burst) — or point BASE at a staging copy.
- Ignores elements clipped by an overflow:hidden ancestor (carousels/sliders
  are not false-flagged).
