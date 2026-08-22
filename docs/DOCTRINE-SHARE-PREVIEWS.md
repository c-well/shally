# Doctrine — the preview looks like the page

**Standing rule. Applies to every shareable URL we build, not just handouts.**

When a link is pasted into iMessage, WhatsApp, or Facebook, the card that
unfurls is the first thing a person sees. If that card is the favicon on a
coloured square with a bare title, three things happen:

1. The recipient learns nothing they did not already have from the URL.
2. It looks like every phishing link they have been told to be careful of.
3. Whoever sent it looks careless, and on a church link that is usually a
   member sending it to their own family.

So the preview is a **real render of the page it points to** — same background,
same theme colour, same mark, same eyebrow and title in the same faces. Someone
who taps through should recognise the page from the preview they just saw.

## How it is done here

`App\Services\HandoutOgImage` draws a 1200x630 PNG with GD + FreeType. There is
no Imagick on this box, so:

- **Fonts must exist locally.** `public/fonts/` holds `XtreemMedium.ttf` (the
  mark), `CormorantGaramond.ttf` (titles), `Poppins.ttf` (tracked caps). GD
  cannot use a webfont URL. Adding a face to a page means adding the TTF here
  too, or the OG will silently fall back and stop matching.
- **GD has no gradient and no letter-spacing.** The ribbon is drawn a column at
  a time; tracked caps are drawn a glyph at a time. Both helpers are in the
  service.
- **imagettftext draws from the BASELINE.** Measure the whole text block and
  centre it as a unit; do not start at a guessed `y` and add as you go. That
  mistake put a two-line title through its own eyebrow on the first render.

## Non-negotiables

- `og:image:width` / `height` must be declared, or some clients render a small
  square crop instead of the wide card.
- Cache the render keyed on the record's `updated_at`, and sweep older keys for
  that record. An edit must change the image; a scraper hitting the URL a
  hundred times must cost one render.
- The image route must not count as a view. A scraper is not a reader.
- The lowercase `shalom` mark, always. Xtreem's capital S is a large swooping
  display glyph and is not the logo — see `.site-menu-brand em`.

## What stays out

The picture carries the identity; the description does not need to repeat the
body. For anything personal — a registry, a family notice — keep `og:description`
to the eyebrow or omit it. The card should be recognisable without spilling the
contents into every group chat the link touches.
