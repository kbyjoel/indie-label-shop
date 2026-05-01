# Blank theme & Customisation

The storefront ships with a **generic blank theme**: white background, neutral typography, black/grey palette. It is designed to be cloned and customised without touching the templates.

## CSS architecture

```
assets/styles/app.css   ← single entry point
  @import "tailwindcss" ← all Tailwind v4 utilities
  @theme { ... }        ← theme variables (→ Tailwind utilities)
  @layer base { ... }   ← base HTML styles
```

The build is handled by `symfonycasts/tailwind-bundle`, which downloads the standalone Tailwind CLI binary (no Node.js required).

## Theme variables (`@theme`)

Every variable defined inside `@theme` automatically generates the corresponding **Tailwind utilities**.

| CSS variable | Generated utilities | Default use |
|---|---|---|
| `--color-accent` | `bg-accent`, `text-accent`, `border-accent`… | Primary actions, CTA buttons |
| `--color-accent-hover` | `bg-accent-hover`… | Hover state on accent elements |
| `--color-accent-fg` | `text-accent-fg`… | Text on accent backgrounds |
| `--color-surface` | `bg-surface`… | Cards, panels, sidebars |
| `--color-surface-hover` | `bg-surface-hover`… | Hover on surface elements |
| `--color-border` | `border-border`… | Borders, dividers |
| `--color-muted` | `text-muted`… | Secondary / placeholder text |
| `--color-success` | `text-success`, `bg-success`… | Success messages |
| `--color-warning` | `text-warning`, `bg-warning`… | Warnings |
| `--color-danger` | `text-danger`, `bg-danger`… | Errors, destructive actions |
| `--font-sans` | `font-sans` | Body text |
| `--font-display` | `font-display` | Headings (swap for a display font) |
| `--font-mono` | `font-mono` | Code |

## Customising colours

Edit the values in `assets/styles/app.css`:

```css
@theme {
    /* Example: dark theme for a metal label */
    --color-accent:     #e53e3e;   /* red */
    --color-accent-fg:  #ffffff;
    --color-surface:    #1a1a1a;   /* very dark grey */
    --color-border:     #2d2d2d;
    --color-muted:      #a0a0a0;
}

@layer base {
    html {
        background-color: #0d0d0d;  /* black background */
        color: #f0f0f0;
    }
}
```

Then rebuild:
```bash
castor docker:builder -- php bin/console tailwind:build
```

## Customising typography

To use a Google Font or a self-hosted font, override `--font-display` in `@theme` and add the font import to `base.html.twig`:

```twig
{# templates/base.html.twig #}
{% block stylesheets %}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet">
{% endblock %}
```

```css
/* assets/styles/app.css */
@theme {
    --font-display: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
}
```

## Template inheritance

```
base.html.twig
  └── front/layout.html.twig      ← main layout (header, footer)
        └── front/home/index.html.twig
        └── front/album/show.html.twig
        └── …
```

**Blocks available in `base.html.twig`:**
- `title` — page `<title>` (composed by `front/layout.html.twig`)
- `seo` — inside `<head>`, for meta/hreflang tags
- `stylesheets` — additional CSS
- `javascripts` / `importmap` — JS / Asset Mapper
- `body` — full `<body>` content

**Blocks available in `front/layout.html.twig`:**
- `page_title` — page title alone (without the site name suffix); when defined, composes `{page_title} — {site.name}` in `<title>`
- `content` — main content inside `<main>`

## Building Tailwind

```bash
# One-off build (CI, deployment)
castor docker:builder -- php bin/console tailwind:build

# Watch mode (recompiles on every template or CSS change)
castor docker:builder -- php bin/console tailwind:build --watch
```

The compiled file is served through Asset Mapper with a fingerprint (`app-xxxx.css`). No further configuration is needed.
