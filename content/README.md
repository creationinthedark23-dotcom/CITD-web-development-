# Homepage & brand pages — Revision 3

**Keep the design. Change the words.**

`homepage.html` is the original homepage markup with the content rewritten.
Every container that survives keeps its original `block_id`, background image,
gradient, padding, margin, corner radius and layout attributes. Nothing was
rebuilt, and no visual section was removed.

| File | Goes to |
| --- | --- |
| `homepage.html` | The front page |
| `brand-blacksalt-kitchenwear.html` | `/blacksalt/` |
| `brand-creation-in-marketing.html` | `/creation-in-marketing/` |
| `brand-template.html` | Not published — pattern for future brands |

Paste through **Editor → Options (⋮) → Code editor**, then switch back to the
visual editor.

---

## What happened to each original section

| Original block | Kept? | What changed |
| --- | --- | --- |
| `477a0fc4` Hero | **Kept whole** | Background image, gradient overlay, all padding and margins untouched. New headline, shorter support line, button now goes to `#brands` instead of `contact/`. |
| `a3b9b3f0` Brands | **Kept, second row removed** | Section background swapped off the broken 658-byte image. Row `18c4d01f` and both its cards keep every border, radius and padding. Row `0beec613` (cards 03 and 04) deleted — that is where DarkTable and Nest were. |
| `b3f37bc6` Story | **Kept whole** | Section background swapped off the broken image. Both `uagb/image` blocks — including the 100px asymmetric corners and the 180px offset — are byte-for-byte unchanged. Heading and copy rewritten. |
| `saqmujyx` Three cards | **Kept whole** | Layout, 6px radii, spacing and card backgrounds untouched. "Why Choose Us" becomes "How We Work"; Innovation / Expertise / Collaboration become the three steps. |
| `258278ba` Work With Us | **Kept whole** | Keeps `close-up.jpg`, the 100px cut corners and the −100px overlap. The 30-word sentence that was marked up as a heading is now a heading plus a paragraph. |

**Removed entirely** — all demo content, no layout lost with them:
`udodjy5g` (second "Our Story") · `tmi7lh1x` ("Our Inspiring Journey") ·
`neeyqfws` (92% / 2,480 / 12+ / 640K and "Stats title here") ·
`woh0wwv7` (Jane Cooper, Wade Warren, Esther Howard) ·
`kaeihkxg` ("Let's Create Together") · reusable block 718 · trailing empty
paragraph.

**Order** is now Hero → Brands → Story → How We Work → Work With Us. The only
move is `saqmujyx` ahead of `258278ba`, so the page ends on the call to action.

---

## The three deliberate fixes

Everything else is a content change. These three touch attributes, and each
fixes something broken rather than restyling something that worked.

1. **`download.jpg` removed from three section backgrounds.** It is 294×172
   pixels and 658 bytes — it cannot render as a full-bleed background. Those
   sections now use a flat global colour (`--ast-global-color-4` / `-5`), which
   also removes the hard 50%/50% gradient that misaligned at odd widths.
   *Real photography can be dropped straight back into these three containers.*

2. **The `01` / `02` numerals are no longer `<h2>`.** They were headings
   containing only a digit, so search engines read the page outline as
   "01, 02, 03, 04" with the brand names demoted beneath. They are now
   paragraphs with explicit type settings (44px desktop / 36 tablet / 32
   mobile, weight 400) so they still look like large numerals. **If they render
   at a different size than before, adjust `headFontSize` on blocks
   `e9dfed3d` and `2a9eba4c`** — that is the one place this change is visible.

3. **The brand cards now hold two brands instead of four.** The surviving row
   is the original one, so both cards keep the right-hand border, the bottom
   border, and the 100px top-right radius on the second card.

---

## Images

| Where | Image | Note |
| --- | --- | --- |
| Hero background | `pexels-photo-10922371.jpeg` (ID 11) | Original, unchanged |
| Story, left | `pexels-photo-14094059.jpeg` (ID 12) | Original, unchanged |
| Story, right | `pexels-photo-34433513.jpeg` (ID 13) | Original, unchanged |
| Work With Us | `close-up.jpg` (ID 2264) | Original, unchanged |

No `websitedemos.net` assets remain — all five were in the deleted demo
sections. All four surviving images are stock and worth replacing with real
CITD photography, but the layout does not depend on it.

The two `uagb/image` blocks in the story section emit a malformed `srcset`
(three entries pointing at the same file). That is a Spectra bug, not a markup
error. Fixing it means re-selecting the image in the editor — I left the blocks
untouched rather than hand-editing them, because edited Spectra markup goes
permanently into WordPress's "invalid content" state.

---

## Writing rules

**BlackSalt KitchenWEAR** — always `KitchenWEAR`, never `Kitchenware`.

**Brands, not companies.** BlackSalt KitchenWEAR and Creation in Marketing are
brands within the Creation in the Dark ecosystem. Creation in the Dark Holdings
is the only registered company named on the site. Do not write about ownership
percentages, equity, shareholders, subsidiaries, capital allocation, governance
or intellectual-property arrangements.

**No invented facts.** No statistics, team members, client names, years in
business or market claims.

---

## Navigation

Keep Astra's existing header and menu. It should contain:

```
Home
Company                 /company/
Brands                  /#brands
The Creation Journal    /journal/
Build With Us           /build-with-us/
Contact                 /contact/
```

The Journal is a navigation destination only. It does not appear as a homepage
section.

---

## Pages this markup expects

`/blacksalt/` · `/creation-in-marketing/` · `/company/` · `/contact/` ·
`/journal/`

---

## Theme support

`assets/css/citd-site.css` is about 100 lines: a hover state on the brand and
step cards, a visible keyboard focus ring that works on dark panels, and a
readable maximum line length. It sets no typeface, no colours and no layout.

`inc/site-output.php` strips two Spectra defects at render time — the empty
`aria-label` and the incorrect `role="button"` on links that navigate, plus a
no-op inline `onclick`. Stored block markup is untouched.

No JavaScript is loaded on the homepage.
