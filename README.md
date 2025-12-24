# SmartCT

Lightweight, privacy‑first integration of Cloudflare Turnstile with WordPress core forms and WooCommerce. No puzzles, no images — just a fast, accessible user experience.

## Features

- Core WordPress forms: Login, Registration, Lost Password, Comments
- WooCommerce forms: Login, Registration, Reset Password, Checkout, Pay for Order
- Auto‑injects the widget only where needed
- Server‑side verification via Cloudflare API
- Minimal footprint; defers scripts when possible
- Developer hooks and centralized settings system

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Cloudflare Turnstile site and secret keys

## Installation

1. Upload the `turnstilewp` folder to `/wp-content/plugins/`, or install via Plugins → Add New.
2. Activate “SmartCT” from the Plugins screen.
3. Go to Settings → SmartCT and enter your Cloudflare Turnstile keys.

## Configuration

- Enter Site Key and Secret Key.
- Choose where the widget should appear (core forms and WooCommerce).
- Select widget theme (Auto/Light/Dark) and size.
- Optionally defer scripts and enable debug logging for troubleshooting.

## WooCommerce

The plugin supports multiple checkout placement options (before/after checkout form, order review, etc.). You can fine‑tune locations from Settings → SmartCT → WooCommerce.

## How it works

- The widget is rendered on eligible forms only when keys are configured.
- Tokens are verified server‑side against Cloudflare’s verification endpoint.
- Failures prevent form submission and display a helpful message.

## Documentation

- **[API Reference](docs/API-REFERENCE.md)** - Complete API documentation and settings reference
- **[Testing Guide](docs/TESTING.md)** - Comprehensive testing documentation
- **[Contributing](docs/CONTRIBUTING.md)** - How to contribute to the project
- **[WordPress.org Submission](docs/WORDPRESS-ORG.md)** - WordPress.org submission checklist
- **Admin UI** - Settings are described inline within the plugin settings pages

## Development

Project layout (high level):

```
turnstilewp/
├── turnstilewp.php
├── includes/            # Core classes and settings framework
├── integrations/        # Core WP + WooCommerce integrations
├── admin/               # Admin UI templates
├── assets/              # SCSS/JS (built via Gulp)
└── templates/           # Reusable partials
```

Setup development environment:
1. Clone the repository.
2. Run `composer install` to install PHP dependencies.
3. Run `npm install` to install Node.js dependencies.
4. Use Gulp to compile assets (see Build tools below).

Build tools:
- SCSS/JS: Gulp (run `npm run gulp:watch` or `npm run gulp:scripts`)
- SCSS migration: `npm run gulp:migrate-scss` (migrates @import to @use for Sass module system)
- PHP coding standards: `phpcs.xml.dist`
- Fix coding standards: `composer run format`
- Linting: PHP CodeSniffer `composer test:phpcs` `composer lint`
- Linting (JS/CSS/HTML): `npm run lint:all` (includes ESLint, Stylelint, HTMLHint)
- Auto-fix linting: `npm run fix:js` `npm run fix:css`
- Testing: Codeception (unit, integration, acceptance) `composer test`

## Changelog

See `CHANGELOG.md`.

## License

GPL v2 or later.
