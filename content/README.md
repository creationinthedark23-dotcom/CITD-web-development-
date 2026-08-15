# Homepage & brand pages — Revision 2

Block markup for the Creation in the Dark homepage and brand pages. Paste each
file into WordPress through **Editor → Options (⋮) → Code editor**, then switch
back to the visual editor.

| File | Goes to |
| --- | --- |
| `homepage.html` | The front page |
| `brand-blacksalt-kitchenwear.html` | `/blacksalt/` |
| `brand-creation-in-marketing.html` | `/creation-in-marketing/` |
| `brand-template.html` | Not published — the pattern for future brands |

**Principle: keep the design, fix the story.** This is a renovation of the
existing Starter Template homepage, not a replacement. The hero image and
gradient, the card that lifts over the hero, the asymmetric corner shapes, the
coloured panels, the three-card row and the dark call-to-action block are all
carried over from the original. What changed is the content inside them, the
links, and the components that did not work.

Built with **Spectra (UAGB) blocks**, the same vocabulary as the original page,
so editing feels identical to editing the old site.

---

## Before pasting

**1. Global colours** — Customize → Global → Colors. The markup references
these by variable, so the whole page re-skins if you change them here.

| Slot | Suggested | Role |
| --- | --- | --- |
| Color 0 | `#6B5320` | Accent — eyebrows, links, underlines |
| Color 1 | `#0B0C0E` | Dark panels |
| Color 2 | `#101215` | Headings |
| Color 3 | `#565C65` | Body text |
| Color 4 | `#F6F4EF` | Warm alternate surface |
| Color 5 | `#FFFFFF` | Base surface |
| Color 6 | `#E4E1D9` | Rules and borders |
| Color 7 | `#0B0C0E` | Hero overlay |

**2. Page settings** — for the homepage and both brand pages: Astra Settings →
**Disable Title** (each page has its own `<h1>` in the content), Content Layout
→ **Full Width / Stretched**, Sidebar → **No Sidebar**.

**3. Typography** is left to Astra. Nothing in the stylesheet forces a typeface,
so whatever is set in Customize → Global → Typography wins. If you want the
display face used in The Creation Journal, set headings to **Fraunces** — but
the page is designed to work with the current fonts as-is.

---

## Images

Every `websitedemos.net` reference is gone, along with `download.jpg` (294×172
and 658 bytes, previously stretched across two full-bleed backgrounds).

The page now uses four Media Library images, each once:

| Where | Image | Status |
| --- | --- | --- |
| Hero background | `pexels-photo-10922371.jpeg` (ID 11) | Stand-in |
| BlackSalt panel | `close-up.jpg` (ID 2264) | **Replace with real BlackSalt photography** |
| Creation in Marketing panel | `pexels-photo-14094059.jpeg` (ID 12) | **Replace with real work imagery** |
| Story section | `pexels-photo-34433513.jpeg` (ID 13) | Stand-in |

All four are stock. They are placed so the layout is visually complete now and
swapping in real photography is a two-click job: select the container → Style →
Background → Image. The brand panels are the two that matter most — a real
product or kitchen shot will transform that section.

---

## Homepage structure

| # | Section | Origin |
| --- | --- | --- |
| 1 | Hero — image, gradient, headline, two buttons | Original hero, kept |
| 2 | Intro card lifting over the hero + four pillars | Original overlap device, new content |
| 3 | **Our brands** — two large image-led panels | Original four-square grid, redesigned for two |
| 4 | Our story — text beside an offset shaped image | Original story section, rewritten |
| 5 | How we work — three cards | Original "Why Choose Us" layout, real content |
| 6 | Build with us — dark panel with asymmetric corners | Original "Work With Us", fixed |
| 7 | Journal bar — one line | New, deliberately small |

### The brands section

Two brands would have left two empty squares in the old four-up grid, so the
component was rebuilt as **two large image-led panels** — a 56/44 split, each a
full-height background image with a gradient, the brand name, a short
description and a call to action. They lift slightly on hover and the whole
panel is clickable.

Adding a third brand: duplicate a panel container, change the width percentages
so they divide evenly, and swap the image, number, name, description and link.
Then build its page from `brand-template.html`.

---

## Writing rules for this site

**BlackSalt KitchenWEAR** — always `KitchenWEAR`, never `Kitchenware`. Check
headings, links, buttons, alt text and metadata.

**Brands, not companies.** BlackSalt KitchenWEAR and Creation in Marketing are
brands within the Creation in the Dark ecosystem. Do not describe ownership
percentages, equity, shareholders, subsidiaries, capital allocation, governance
or legal intellectual-property arrangements anywhere on the public site.

**No invented facts.** No statistics, no team members, no client names, no years
in business, no market claims — unless supplied.

**Tone.** Confident, creative, human, concise. Not a legal document, not an
investment prospectus, not agency filler.

---

## Navigation

Keep Astra's existing header. The menu should be:

```
Home
Company            /company/
Brands             /#brands   (or /brands/ once there is an index page)
The Creation Journal  /journal/
Build With Us      /build-with-us/
Contact            /contact/
```

The Journal is a destination in the navigation, plus the single-line bar at the
foot of the homepage. It is not a homepage section.

---

## Removed from the old homepage

Jane Cooper, Wade Warren and Esther Howard · the 92% / 2,480 / 12+ / 640K
statistics and their "Stats title here" labels · the generic "Why Choose Us"
cards · the duplicate "Our Story" · "Our Inspiring Journey" · "Let's Create
Together" · reusable block 718 · all five `websitedemos.net` images · DarkTable
and Nest, with no placeholders left standing in for them.

Also fixed: two `<h1>` elements per page, a 30-word sentence marked up as an
`<h3>`, every brand link pointing at `services/`, relative links with no leading
slash, and Nest's description that was actually DarkTable's copy.

Two Spectra defects are corrected at render time by the child theme
(`inc/site-output.php`) rather than by editing block markup, which would put
every button permanently into WordPress's "invalid content" state: the empty
`aria-label` and the incorrect `role="button"` on links that navigate, and a
no-op inline `onclick` on every card.
