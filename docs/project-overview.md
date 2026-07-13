# Protogami — Project Overview

## What is Protogami?

Protogami is a **visual website builder** built with Laravel and Livewire. It lets users assemble multi-page websites from pre-made section snippets (navbars, content blocks, footers), customize branding (colors, fonts), manage a sitemap with parent/child pages, and export the result as standalone HTML.

Templates, branding presets, and snippet definitions are stored as files in the `docs/` directory — making everything version-controllable and Git-friendly.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4, Laravel 13 |
| Frontend | Livewire 4, Flux UI v2, Tailwind CSS v4, Alpine.js |
| Auth | Laravel Fortify (login, registration, 2FA, password reset) |
| Build | Vite 8, pnpm |
| Testing | Pest 4, PHPUnit 12 |
| Static Analysis | Larastan 3, Laravel Pint |
| DB | SQLite (default) |

## Application Structure

### Routes

- **`/`** — Landing page (`pages::home.index`) with a hero CTA to start building
- **`/workspace`** — The main builder workspace (`pages::proto.workspace`)
- **`/dashboard`** — Authenticated dashboard (auth + verified required)
- **`/settings/profile`** — Profile editing (auth required)
- **`/settings/appearance`** — Appearance settings (auth + verified)
- **`/settings/security`** — Password + 2FA settings (auth + verified)

### Core Services (`app/Services/`)

- **`BrandingService`** — Manages branding presets stored as JSON files in `docs/branding/`. Each preset defines primary/secondary/accent/background/text colors and a font family. Supports list, load, save, delete with slug-sanitized filenames.
- **`SnippetService`** — Manages Blade snippet templates in `docs/snippets/`. Snippets are organized by type (navbar, content, footer) and preset (e.g., `navbar_centered`, `content_hero`). Renders snippets via `view()->file()`.
- **`TemplateService`** — Manages full site templates as JSON files in `docs/templates/`. A template contains branding, optional light/dark theme branding, and a pages array with sections.

### Workspace Component (`resources/views/pages/proto/⚡workspace/`)

The workspace is the heart of the app — a Livewire single-file component (`workspace.php` + `workspace.blade.php`) that provides:

- **Sitemap panel** — Tree view of pages with drag-to-reorder, add, delete, and nest pages
- **Sections panel** — Add/remove/reorder sections on the selected page; each section has a type (navbar/content/footer) and a preset
- **Branding panel** — Color pickers, font selector, light/dark theme saving, load/save branding presets
- **Live preview** — Renders the current page as HTML in an iframe using Tailwind CSS browser CDN; link clicks in the preview dispatch back to Livewire to select pages
- **Template management** — Load existing templates, save current state as a template (auth required)
- **Export** — Downloads the selected page as a standalone `.html` file with inline Tailwind CSS

### Snippet Presets

Snippets are Blade partials in `docs/snippets/` with the naming convention `{type}_{preset}.blade.php`:

| Type | Presets |
|---|---|
| **navbar** | `items_left`, `items_right`, `centered`, `split` |
| **content** | `hero`, `grid`, `list`, `single_column`, `features` |
| **footer** | `simple`, `multi_column`, `minimal` |

### Branding Presets (`docs/branding/`)

JSON files with color scheme + font: Ocean, Forest, Sunset, Mono Dark.

### Templates (`docs/templates/`)

JSON files defining complete site structures: `blog.json` (3 pages), `football-site.json` (6 pages with nested team pages).

### Auth & Settings

Built on Laravel Fortify with Livewire settings pages:
- **Profile** — Name/email editing with email re-verification on change
- **Security** — Password update, 2FA management
- **Appearance** — Theme preference (light/dark)
- **Delete account** — Account deletion form

### Model

- **`User`** — Standard Laravel auth user with `initials()` helper, Fortify 2FA fields, hashed passwords.

## File-Based Storage

All templates, branding presets, and snippets live in `docs/` as plain files (JSON + Blade). This means:
- Everything is version-controllable via Git
- No database tables needed for templates/branding/snippets
- Easy to share templates by committing them to a repo

## Development

```bash
# Install PHP dependencies
composer install

# Install frontend dependencies
pnpm install

# Run dev server (PHP + Vite)
composer run dev

# Build frontend assets
pnpm run build

# Run tests
php artisan test --compact

# Format code
vendor/bin/pint --dirty --format agent

# Static analysis
phpstan analyse
```

## Testing

Tests are in `tests/Feature/` and use Pest:
- `HomeTest` — Landing page
- `DashboardTest` — Authenticated dashboard
- `WorkspaceTest` — Workspace builder functionality
- `TemplateServiceTest` — Template service CRUD
- `Auth/` — Login and registration tests
- `Settings/` — Profile and security settings tests
