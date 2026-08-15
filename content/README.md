# Homepage & portfolio content

Block markup for the redesigned Creation in the Dark Holdings site. Every file
in this directory is pasted into WordPress through **Editor → Options (⋮) →
Code editor**, then switched back to the visual editor.

| File | Goes to |
| --- | --- |
| `homepage.html` | The front page |
| `portfolio-blacksalt.html` | `/blacksalt/` |
| `portfolio-creation-in-marketing.html` | `/creation-in-marketing/` |
| `portfolio-template.html` | Not published — the pattern for future ventures |

---

## Before pasting

**1. Set the global colours.** Appearance → Customize → Global → Colors:

| Slot | Value | Role |
| --- | --- | --- |
| Color 0 | `#6B5320` | Brass — text-safe accent |
| Color 1 | `#0B0C0E` | Ink — the dark ground |
| Color 2 | `#101215` | Headings |
| Color 3 | `#565C65` | Body text |
| Color 4 | `#F6F4EF` | Bone — alternate surface |
| Color 5 | `#FFFFFF` | Paper — base surface |
| Color 6 | `#E4E1D9` | Rules and borders |
| Color 7 | `#0B0C0E` | Overlays |

**2. Set the typography.** Customize → Global → Typography. Headings:
**Fraunces**, weight 400. Body: **Inter**, weight 400. Astra loads both from
Google Fonts and manages them; the stylesheet only names them as families.

**3. Turn off the theme page title** on the homepage and both portfolio pages.
Each page has its own `<h1>` in the content, and Astra's title would make a
second one. Page editor → sidebar → **Astra Settings → Disable Title**.

**4. Set the content width to full.** Astra Settings → Content Layout →
**Full Width / Stretched**, and Sidebar → **No Sidebar**. The bands are
`alignfull` and need the page to stop constraining them.

No plugin is required. The design system lives in the child theme at
`assets/css/citd-site.css` and loads on every page except The Creation Journal.

---

## Which blocks these use

Core Gutenberg blocks throughout — group, heading, paragraph, buttons — plus
four **HTML blocks** for the structural components: the holdings list, the
register rows, the statements and the Build With Us routes.

Those four are HTML blocks deliberately. Their markup is a grid with named
parts, and building it out of nested columns would produce five levels of
containers that break the first time someone drags something. As HTML blocks
they are still edited in WordPress, they never throw block-validation errors,
and adding a venture is copying twelve lines.

Everything else is a normal block: click the headline, type a new headline.

**Spectra is not used on these pages.** It stays installed and keeps working
everywhere else, but the new sections do not need it, and core blocks render
without Spectra's per-block CSS and JS.

---

## Adding a venture to the register

In `homepage.html`, inside the register HTML block, copy one `<div class="citd-reg">…</div>`
row, paste it below the last one, and change six values:

```html
<div class="citd-reg" data-citd-reveal>
	<p class="citd-reg__index">03</p>
	<h3 class="citd-reg__name"><a href="/slug/">Venture Name</a></h3>
	<p class="citd-reg__meta">
		<span class="citd-reg__sector">Sector</span>
		<span class="citd-status citd-status--building">Building</span>
	</p>
	<p class="citd-reg__desc">One or two sentences.</p>
	<p class="citd-reg__go">View</p>
</div>
```

Status classes, in lifecycle order:
`citd-status--concept`, `citd-status--development`, `citd-status--building`,
`citd-status--operating`.

Then build the venture's page from `portfolio-template.html`. That is the whole
process — no layout changes, no redesign, no new CSS.

---

## Navigation

Four items, plus Contact as a button:

```
Portfolio     →  /#register        (or a /portfolio/ index page once there are 5+)
Company       →  /company/
Journal       →  /journal/
Build With Us →  /build-with-us/
Contact       →  /contact/         (button style)
```

The brief's proposed navigation had two redundant pairs — *Portfolio* against
*Ventures*, and *The Company* against *About*. Collapsing them costs nothing.

*Journal* is The Creation Journal, the publication in this same repository. A
holding company that publishes reads as a company that thinks, and it gives the
site somewhere to grow that is not the homepage.

---

## Footer

Built in Astra's footer builder rather than as block markup, so it stays
editable in one place across every page. Structure:

**Column 1** — Wordmark, one-sentence statement, social links.
**Column 2 — Portfolio:** BlackSalt · Creation in Marketing.
**Column 3 — Company:** About · The Creation Journal · Build With Us · Contact.
**Column 4 — Legal:** Privacy · Terms · Company information.

Bottom bar: `© <year> Creation in the Dark Holdings (Pty) Ltd.`

Set the footer background to Color 1 (`#0B0C0E`) so the page ends on the dark
ground it opened on.

---

## Pages this markup expects to exist

`/company/` · `/build-with-us/` · `/contact/` · `/blacksalt/` ·
`/creation-in-marketing/` · `/journal/`

`/build-with-us/` receives an `?intent=` parameter from the homepage routes
(`partnership`, `supply`, `investment`, `collaboration`, `careers`). Preselect
the matching option in the form if the form plugin supports it; if it does not,
the links still work and the parameter is simply ignored.

---

## What was removed from the old homepage, and why

| Removed | Reason |
| --- | --- |
| Second "Our Story" section | Demo copy, duplicated a real section above it |
| "Our Inspiring Journey" | Demo copy, and the page's second `<h1>` |
| "Why Choose Us" | Demo copy — replaced by *The position we take* |
| Stats row: 92% / 2,480 / 12+ / 640K | Fabricated figures under labels still reading "Stats title here" |
| "Our Team" | Three fictional people with demo portraits |
| "Let's Create Together" | Demo copy, CTA linked to `#`, duplicated Build With Us |
| Reusable block 718 | Unknown template block; not carried into the rebuild |
| All `websitedemos.net` images | Five references hotlinked from a third-party demo server |
| DarkTable and Nest | Not ready for public presentation. No placeholder rows — they enter the register when their positioning is confirmed |

Defects fixed along the way: two `<h1>` elements per page, numerals marked up
as `<h2>`, a 30-word sentence marked up as `<h3>`, every venture CTA pointing at
`services/`, relative links with no leading slash, broken `srcset` on every
image, a 658-byte image stretched across two full-bleed backgrounds, and the
60/80/100-pixel corner radii that made the site read as an edited template.
