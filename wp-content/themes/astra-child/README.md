# The Creation Journal

Astra child theme powering the digital publication of **Creation in the Dark
Holdings**.

Archive: `/journal/` · Article: `/journal/article-name/`

---

## 1. Installation

1. Install and activate the **Astra** parent theme.
2. Place this directory at `wp-content/themes/astra-child/`.
3. Activate **Astra Child — The Creation Journal** in **Appearance → Themes**.
4. The theme flushes rewrite rules once on activation. If `/journal/` 404s on a
   host with aggressive caching, open **Settings → Permalinks** and save.

Nothing else is required. There is no build step, no package manager, and no
external dependency beyond the Astra parent theme.

---

## 2. File map

```
astra-child/
├── style.css                        Theme header only — no styles live here
├── functions.php                    Constants and module loader
├── archive-publication.php          /journal/ — also serves taxonomies + search
├── single-publication.php           /journal/article-name/
├── header-journal.php               Masthead, nav, search, drawer, progress bar
├── footer-journal.php               Footer, colophon, toast region
│
├── inc/
│   ├── setup.php                    Supports, image sizes, menus, body classes
│   ├── enqueue.php                  Stylesheets, scripts, fonts, preloads
│   ├── post-type-publication.php    The `publication` CPT + subscriber store
│   ├── taxonomies.php               Topics and Issues, plus term metadata
│   ├── meta-fields.php              Editorial metadata and reading time
│   ├── user-fields.php              Contributor profile fields
│   ├── content-processing.php       Heading ids, TOC data, table and image work
│   ├── template-tags.php            Reading time, share links, cards, icons
│   ├── query.php                    Archive tuning, featured resolver, routing
│   ├── seo.php                      JSON-LD, Open Graph, Twitter, canonical
│   ├── newsletter.php               Subscription endpoint and storage
│   ├── shortcodes.php               [pullquote], [journal_stat], [journal_note]
│   └── customizer.php               Masthead, newsletter, footer, social
│
├── template-parts/journal/
│   ├── hero-archive.php             Publication masthead block
│   ├── header-taxonomy.php          Topic / issue archive header
│   ├── header-search.php            Search results header
│   ├── featured.php                 Latest issue — the featured article
│   ├── topics.php                   Topic index ("Categories")
│   ├── card.php                     One article card
│   ├── pagination.php               Archive pagination
│   ├── newsletter.php               Signup panel (works without JavaScript)
│   ├── no-results.php               Empty state
│   ├── article-hero.php             Four hero treatments + byline strip
│   ├── executive-summary.php        Numbered summary panel
│   ├── toc.php                      Sticky contents rail
│   ├── share.php                    Share rail and inline share row
│   ├── references.php               References list
│   ├── author.php                   Author card
│   ├── prev-next.php                Previous / next article
│   └── related.php                  Related reading
│
└── assets/
    ├── css/
    │   ├── journal.css              The design system (screen)
    │   ├── journal-print.css        The print edition
    │   ├── journal-editor.css       Block editor styles
    │   └── journal-admin.css        Editorial meta boxes in wp-admin
    └── js/
        └── journal.js               All front-end behaviour
```

---

## 3. Publishing an article

**The Journal → Add Article.**

| Field | Where | Notes |
| --- | --- | --- |
| Title | Editor | The headline. Keep it under about 70 characters. |
| Hero image | Featured image | Upload at 2560px wide or more. |
| Eyebrow | Journal — Presentation | Short label above the headline. Falls back to the primary topic. |
| Deck / standfirst | Journal — Presentation | One or two sentences. Falls back to the excerpt. |
| Hero image credit | Journal — Presentation | Printed beneath the hero. |
| Hero treatment | Journal — Presentation | Standard, immersive, split or typographic. |
| Feature as the latest issue | Journal — Presentation | Promotes the article to the top of `/journal/`. |
| Show table of contents | Journal — Presentation | On by default. The rail appears once the article has three or more H2/H3 headings. |
| Executive summary | Journal — Summary | One takeaway per line. Leading bullets and numbers are stripped automatically. |
| Reading time override | Journal — Summary | Leave empty to calculate from the article length. |
| References | Journal — References | One per line. A URL at the end of a line becomes a link. |
| Topics | Sidebar | Editorial subject areas. Drives the topic index and related reading. |
| Issues | Sidebar | Optional numbered issue or volume. |

### Reading time

Calculated on save at 225 words per minute and stored in `_citd_reading_time`.
Change the speed with:

```php
add_filter( 'citd_journal_words_per_minute', fn() => 200 );
```

### Editorial furniture inside the body

```
[pullquote attribution="Jane Doe, CFO" align="right"]The quote text.[/pullquote]
[journal_stat value="38%" label="of firms report the same constraint" source="CITD, 2026"]
[journal_note title="Method"]How the sample was drawn.[/journal_note]
[journal_divider]
```

`align` accepts `wide` (default), `left` or `right`. The floated variants only
float at 1088px and above; below that they run full measure.

The core pull quote block is styled to match, so either route works.

### Contributor profiles

**Users → Profile → The Creation Journal — Contributor Profile.** Role,
organisation, LinkedIn, X, personal site and an optional portrait attachment ID.
Without a portrait the card falls back to the Gravatar.

---

## 4. Site-level settings

**Appearance → Customize → The Creation Journal.**

- **Masthead** — publication name, publisher line, archive standfirst.
- **Newsletter** — heading, supporting copy, consent wording.
- **Footer** — statement, copyright line, contact email.
- **Social profiles** — LinkedIn, X, Facebook, and the X handle used for
  `twitter:site`.
- **Imagery** — publisher logo (structured data) and a default share image.

### Menus

Four locations are registered: **Journal — Primary**, **Utility**, **Footer**
and **Legal**. If no primary menu is assigned, the masthead falls back to the
five busiest topics.

### Widgets

**Journal — Archive Aside** renders beneath the topic index on `/journal/`.

---

## 5. Newsletter

Submissions post to `admin-post.php` and are upgraded to an inline AJAX
submission by `journal.js`. Both paths share one validation and storage routine,
so the form works with JavaScript disabled.

Subscribers are stored as private posts of type `journal_subscriber`, visible
under **The Journal → Subscribers**. A honeypot field and an explicit consent
checkbox are required; the consent timestamp is stored with each record.

To forward addresses to an email service provider, hook the action rather than
editing the theme:

```php
add_action( 'citd_journal_subscriber_added', function ( $email, $name, $id ) {
	// Send $email to Mailchimp, Campaign Monitor, HubSpot, …
}, 10, 3 );
```

---

## 6. Design system

### Themes

Light and dark are both first-class. The initial theme comes from
`prefers-color-scheme`; a reader's explicit choice is stored in `localStorage`
under `citd-journal-theme` and wins from then on. A small inline script in
`<head>` applies the setting before first paint, so there is no flash.

Every colour is a custom property defined once in `:root` and again under
`:root[data-theme="dark"]`. To rebrand, change the tokens — not the rules.

### Typography

| Role | Family | Notes |
| --- | --- | --- |
| Display | Fraunces | Variable, optical size axis driven per component |
| Body | Newsreader | Variable, optical size axis |
| Interface | Inter | Metadata, eyebrows, buttons, tables |

Loaded in one Google Fonts request with `display=swap` and preconnect hints. To
self-host, or to drop webfonts entirely and fall back to the curated system
stack, filter the URL:

```php
add_filter( 'citd_journal_fonts_url', fn() => '' );
```

### Type and space scales

Both are fluid `clamp()` scales exposed as `--step--2` … `--step-6` and `--s-1`
… `--s-12`. Nothing in the stylesheet uses a raw pixel font size.

### Print

`journal-print.css` produces a proper offprint: A4, single column, black on
white, interface removed, link destinations written out beside their text, and
page-break rules that keep figures, tables, quotes and summary panels intact.

---

## 7. Accessibility

The publication targets WCAG 2.1 AA.

- Skip links to the content and, on articles, to the contents rail.
- One `<h1>` per page; heading order is never skipped.
- Landmarks on the masthead, navigation, main, complementary panels and footer,
  each labelled where more than one of a type exists.
- Focus is never removed — `:focus-visible` draws a 2px ring with offset that
  meets contrast against both themes.
- The drawer traps focus while open, closes on `Escape`, and returns focus to
  the control that opened it.
- The contents rail marks the current section with `aria-current`.
- Reading progress is exposed as a `progressbar` with a live `aria-valuenow`.
- Copy-link results and newsletter outcomes are announced through polite live
  regions.
- Wide tables become focusable, labelled scroll regions.
- `prefers-reduced-motion` disables reveals, parallax-style scaling and smooth
  scrolling; `prefers-contrast: more` strengthens hairlines and muted text.
- Body copy, muted text, placeholders and inverted panels were all checked
  against the 4.5:1 (and 3:1 for large text) thresholds in both themes.

---

## 8. Performance

- `journal.css` and `journal.js` load only on journal templates.
- `journal.js` is deferred and has no dependencies.
- The article hero is preloaded with `fetchpriority="high"` and a matching
  `imagesrcset`, so the largest contentful paint is not gated behind CSS.
- Body images get `loading="lazy"` and `decoding="async"` during the content
  pass; the hero and the first three cards are explicitly eager.
- The content pass runs once per request and is memoised.
- The featured-article lookup is cached in a transient and invalidated on save,
  trash, untrash and delete.
- WordPress emoji scripts are removed on journal templates.
- No web font is render-blocking; all three families use `display=swap`.

---

## 9. SEO

The theme emits canonical, description, Open Graph and Twitter card tags, plus
JSON-LD (`Article` + `BreadcrumbList` on singles, `CollectionPage` + `ItemList`
on the archive).

If Yoast, Rank Math, SEOPress, All in One SEO or The SEO Framework is active,
the theme stands down entirely so nothing is duplicated. Override the detection
with the `citd_journal_seo_plugin_active` filter.

---

## 10. Extension points

**Filters**

| Filter | Purpose |
| --- | --- |
| `citd_journal_name` | Publication name |
| `citd_journal_publisher` | Publisher line |
| `citd_journal_fonts_url` | Webfont stylesheet URL; return `''` to disable |
| `citd_journal_words_per_minute` | Reading speed |
| `citd_journal_archive_per_page` | Articles per archive page |
| `citd_journal_show_toc` | Whether the contents rail renders |
| `citd_journal_share_links` | Share destinations |
| `citd_journal_related_ids` | Related article selection |
| `citd_journal_structured_data` | JSON-LD graph before output |
| `citd_journal_seo_plugin_active` | Whether to suppress head metadata |

**Actions**

| Action | Purpose |
| --- | --- |
| `citd_journal_subscriber_added` | Fires after a subscriber is stored |

**Template overrides**

Everything under `template-parts/journal/` resolves through
`get_template_part()`, so a grandchild theme can replace any single part without
touching this one.

---

## 11. Browser support

Evergreen Chrome, Edge, Firefox and Safari, plus iOS and Android. The layout
uses CSS Grid, custom properties, `clamp()` and `aspect-ratio`. `color-mix()`
and `backdrop-filter` are progressive enhancements with declared fallbacks.
Without JavaScript the site remains fully readable and navigable: only the
progress bar, scrollspy, theme toggle and inline form submission are lost.
