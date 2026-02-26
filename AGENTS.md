# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Laravel 12** — PHP 8.4, Blade templates, SQLite (default)
- **Tailwind CSS v4** — CSS-first config via `@import "tailwindcss"` in `resources/css/app.css`
- **Vite** — asset bundling via `laravel-vite-plugin`
- **Pest v4** — testing framework

## Commands

```bash
composer run dev      # Start all dev processes (server, queue, logs, vite)
composer run test     # Run full test suite
php artisan test --compact --filter=TestName   # Run a single test
vendor/bin/pint --dirty   # Format changed PHP files
npm run build         # Build frontend assets
```

## Code Formatting

Run `vendor/bin/pint --dirty --format agent` after modifying any PHP files.

## Skills

Activate these skills when working in their domain:

- `pest-testing` — writing or debugging tests
- `tailwindcss-development` — any styling or CSS work

## Conventions

- Follow existing code conventions; check sibling files before creating new ones.
- Use descriptive names (`isRegisteredForDiscounts`, not `discount()`).
- Do not change dependencies without approval.
- Do not create documentation files unless explicitly requested.

## Boost MCP

Use Boost MCP tools for this project:

- `search-docs` — version-specific Laravel/ecosystem docs (use before making changes)
- `tinker` — execute PHP / query Eloquent models
- `database-query` — read-only DB queries
- `database-schema` — inspect table structure before migrations
- `get-absolute-url` — generate correct URLs to share with user
- `browser-logs` — read frontend errors
- `list-artisan-commands` — check available Artisan command options

The site is served by Laravel Herd — do not run `php artisan serve` to make it available.
