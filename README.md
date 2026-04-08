# Moments

Moments is a Laravel-based development host for the extracted [`privateer/moments`](./packages/privateer/moments) package.

The repository serves two purposes:

- it is a working Moments application you can run locally
- it is the package development environment, wired in through a Composer `path` repository

The host app mounts the package at `/` so the local experience still behaves like a standalone micro-blog, while the package itself defaults to a dedicated `/moments` prefix when installed into another Laravel application.

## Architecture

- Host app: this repository's Laravel application
- Package: [`packages/privateer/moments`](./packages/privateer/moments)
- Composer integration: root [`composer.json`](./composer.json) requires `privateer/moments` and resolves it through a local `path` repository
- API spec: [`packages/privateer/moments/openapi.yaml`](./packages/privateer/moments/openapi.yaml)

The package owns the Moments product surface:

- public timeline and permalinks
- Markdown rendering with hashtag auto-linking
- hashtag tagging system with per-tag pages
- image uploads, persisted image ordering across web/API flows, Glide resizing, lightbox UI, and optional AI-generated alt text
- RSS, Atom, and JSON feeds
- REST API and OpenAPI description
- login, account, and API token management
- install and maintenance commands

The host app remains responsible for generic Laravel application concerns such as the app skeleton, environment, user model, and local asset pipeline.

## Local Development

### Requirements

- PHP 8.4+
- Composer
- Node.js and npm
- [Laravel Herd](https://herd.laravel.com) or another local Laravel server

### Installation

```bash
git clone <repo-url> moments
cd moments

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Create the owner account

```bash
php artisan moments:install
```

This command creates the initial user in the host app and generates a Glide signing key.

### Run the app

```bash
composer run dev
```

With Herd, the app is typically available at [https://moments.test](https://moments.test).

## Package Development Notes

This repository intentionally develops the package in-place.

- The package lives at [`packages/privateer/moments`](./packages/privateer/moments)
- The root app requires `privateer/moments`
- Composer resolves it through a `path` repository with symlinking enabled
- The host app overrides the package route prefix to `/` in [`config/moments.php`](./config/moments.php)

If you are working on the package itself, the package-facing installation and integration guidance lives in [`packages/privateer/moments/README.md`](./packages/privateer/moments/README.md).

To experiment with styling or markup changes from the host app side, you can publish the package views:

```bash
php artisan vendor:publish --tag=moments-views
```

This writes the package views to `resources/views/vendor/moments`, where Laravel will prefer your app-level overrides over the package defaults.

## Host App Configuration

The host app uses the package's config surface but with local-development-friendly defaults.

Key host-specific choices:

- `route_prefix` is `/` so the package takes over the site root locally
- `user_model` points at the host app's `App\Models\User`
- the package web and API routes are both enabled

See [`config/moments.php`](./config/moments.php) for the full set of options and inline documentation.

### AI Alt Text

The package can optionally generate accessibility-focused alt text for uploaded images using Laravel AI.

- enable it with `MOMENTS_ALT_TEXT_ENABLED=true`
- choose a provider with `MOMENTS_ALT_TEXT_PROVIDER`
- optionally pin a model with `MOMENTS_ALT_TEXT_MODEL`

When enabled, new uploads queue alt-text generation in the background and store the result on the related `MomentImage` record. Existing images can be backfilled later with:

```bash
php artisan moments:generate-alt-text
php artisan moments:generate-alt-text --force
```

The package uses stored alt text when rendering images, and falls back to a generic `"Moment image"` alt attribute when no generated text is available.

If you use the default `public` filesystem disk for uploads, run:

```bash
php artisan storage:link
```

## API

The REST API is provided by the package and is documented in the package-owned OpenAPI file:

- [`packages/privateer/moments/openapi.yaml`](./packages/privateer/moments/openapi.yaml)

In this host app, the package API is mounted at:

- `POST /api/v1/images`
- `GET /api/v1/moments`
- `POST /api/v1/moments`
- `PATCH /api/v1/moments/{moment}`
- `DELETE /api/v1/moments/{moment}`

API authentication uses Sanctum personal access tokens managed through the account UI.

For image ordering:

- `POST /api/v1/moments` persists images in the order provided by the `images` array
- `PATCH /api/v1/moments/{moment}` accepts `image_order` for reorder-only or mixed reorder/add/remove updates
- image objects in API responses include a numeric `position` field

The packaged create and edit forms also let authors reorder images before saving.

## Commands

Useful project commands:

```bash
composer run dev
composer run test
php artisan test --compact --filter=TestName
vendor/bin/pint --dirty --format agent
php artisan moments:install
php artisan moments:glide-key --force
php artisan moments:delete-orphan-images
php artisan moments:generate-alt-text
php artisan moments:generate-alt-text --force
php artisan moments:backfill-tags
```
