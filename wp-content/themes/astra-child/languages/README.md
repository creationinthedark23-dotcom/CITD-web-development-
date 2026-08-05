# Translations

Text domain: `citd-journal`

Every user-facing string in the theme is wrapped in a translation function, so a
`.pot` file can be generated directly from the source:

```
wp i18n make-pot . languages/citd-journal.pot --domain=citd-journal
```

Drop compiled `citd-journal-{locale}.mo` files into this directory. The theme
loads them through `load_child_theme_textdomain()` during `after_setup_theme`.
