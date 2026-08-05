# CITD Web Development

Web platform for **Creation in the Dark Holdings**.

This repository currently contains the front end for **The Creation Journal**, the
company's digital publication, built as an Astra child theme for WordPress.

## Repository layout

```
wp-content/
└── themes/
    └── astra-child/          The Creation Journal theme
```

The path mirrors a WordPress installation so the theme directory can be synced
or symlinked straight into `wp-content/themes/` on any environment.

## What is in the theme

| Area | Summary |
| --- | --- |
| Custom post type | `publication` — archive at `/journal/`, singles at `/journal/article-name/` |
| Taxonomies | `journal_topic` (topics/categories), `journal_issue` (issues/volumes) |
| Archive | Masthead hero, latest issue, featured article, topic index, grid of previous issues, newsletter |
| Article | Four hero treatments, executive summary, sticky contents rail, reading progress, pull quotes, references, author card, previous/next, related reading, share controls |
| Design | Vanilla CSS design system with light and dark themes, a dedicated print edition, and block-editor styles |
| Behaviour | Vanilla JavaScript — no jQuery, no framework, no build step |

No Elementor. No Bootstrap. No page builder of any kind.

## Getting started

1. Install and activate the [Astra](https://wordpress.org/themes/astra/) parent
   theme.
2. Copy `wp-content/themes/astra-child/` into the site's `wp-content/themes/`
   directory.
3. Activate **Astra Child — The Creation Journal**.
4. Visit **Settings → Permalinks** once if `/journal/` does not resolve
   immediately. (The theme flushes rewrite rules on activation, so this is
   normally unnecessary.)

Full setup, editorial and extension notes live in
[`wp-content/themes/astra-child/README.md`](wp-content/themes/astra-child/README.md).

## Requirements

- WordPress 6.0 or later
- PHP 7.4 or later
- Astra 4.0 or later
