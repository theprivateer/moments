# AGENTS.md

This file provides guidance to AI coding agents when working with code in this repository.

## Stack

- **Laravel 12** — PHP 8.4, Blade templates, SQLite (default)
- **Livewire v4** — reactive UI components (single-file format)
- **Tailwind CSS v4** — CSS-first config via `@import "tailwindcss"` in `resources/css/app.css`
- **Vite** — asset bundling via `laravel-vite-plugin`
- **Pest v4** — testing framework

## Commands

```bash
composer run dev      # Start all dev processes (server, queue, logs, vite)
composer run test     # Run full test suite
php artisan test --compact --filter=TestName   # Run a single test
vendor/bin/pint --dirty --format agent   # Format changed PHP files (run after every PHP edit)
npm run build         # Build frontend assets
php artisan moments:install   # Interactive: create the owner account
```

## Architecture

Moments is a single-author micro-blog. Posts ("moments") appear on a public timeline and can contain Markdown text, images, or both. The app has a web UI (Blade + Livewire, session auth) and a REST API (Sanctum bearer tokens). An OpenAPI 3.1 description lives at `openapi.yaml`.

### Models

**`Moment`** — core post model
- `body` — nullable Markdown text (rendered via `renderedBody()` using CommonMark)
- `images()` — `HasMany` → `MomentImage`
- `user()` — `BelongsTo` → `User`

**`MomentImage`** — stores one attached image per row
- `path` + `disk` — stored separately so the disk can change without breaking old URLs
- `url()` — returns `Storage::disk($this->disk)->url($this->path)`
- `glideUrl(int $width)` — returns a signed Glide URL; **use this in views** instead of `url()`

**`User`**
- `moments()` — `HasMany` → `Moment`
- Uses `HasApiTokens` (Sanctum) alongside `HasFactory`, `Notifiable`

### Validation — `StoreMomentRequest`

Shared by the web form and the API. The key rule: **`body` is required only when no images are uploaded** (and vice versa). At least one must be present.

### Image Storage

Configured via `config('moments.image_disk')` (env `MOMENTS_IMAGE_DISK`, default `public`). Controllers use:

```php
$file->store('moments', config('moments.image_disk'))
```

The `disk` value is saved on each `MomentImage` row at creation time. On delete/update, images are removed from storage via `Storage::disk($image->disk)->delete($image->path)`.

Run `php artisan storage:link` once when using the `public` disk.

### Image Serving (Glide)

Images are served through `GlideController` (`GET /img/{path}`, named `glide`) which resizes them server-side using `league/glide`. Views must call `$image->glideUrl($width)` — never `$image->url()` — to get a signed URL.

Key details:
- The signing key is `config('moments.glide_sign_key')` (env: `GLIDE_SIGN_KEY`).
- `UrlBuilderFactory` signs using the **full path** including the `/img/` prefix (e.g. `/img/moments/photo.jpg`). The controller validates with the same full path: `->validateRequest('/img/'.$path, ...)`.
- Resized images are cached at `storage/framework/cache/glide`.
- Display widths: timeline = `800`, show page = `1200`.

### Authorization

`MomentPolicy` enforces ownership for `update` and `delete`. Controllers call `$this->authorize('update', $moment)`. The `TokenController` uses `abort_if($token->tokenable_id !== $request->user()->id, 403)` for manual ownership checks.

### Authentication

- **Web** — session-based (`middleware('auth')`), managed by `Auth\LoginController`
- **API** — Sanctum personal access tokens (`middleware('auth:sanctum')`); tokens created/revoked at `/tokens`
