# Privateer Moments

`privateer/moments` is a reusable Laravel package that adds a full Moments micro-blog to an existing Laravel application.

It provides:

- a public timeline and per-moment pages
- Markdown post bodies
- image uploads with persisted ordering, Glide-powered resizing, and optional AI-generated alt text
- RSS, Atom, and JSON feeds
- a REST API with an OpenAPI description
- login, account, and API token screens
- install and maintenance Artisan commands

## Requirements

- PHP 8.2+
- Laravel 13
- Livewire 4
- Sanctum for API token features

The host application must provide an authenticatable user model and a working Laravel auth/session setup.

## Installation

Install the package with Composer:

```bash
composer require privateer/moments
```

The package uses Laravel package discovery and registers `Privateer\Moments\MomentsServiceProvider` automatically.

Publish the config file if you want to customize the host integration:

```bash
php artisan vendor:publish --tag=moments-config
```

If you want to customize the package Blade views and styling, publish the views into your app:

```bash
php artisan vendor:publish --tag=moments-views
```

Run the package migrations:

```bash
php artisan migrate
```

Create the initial owner account:

```bash
php artisan moments:install
```

If you are using the default `public` filesystem disk for uploaded images, also run:

```bash
php artisan storage:link
```

## Host App Expectations

The package integrates into a host app rather than replacing the Laravel skeleton.

Your host app should provide:

- an authenticatable user model
- web auth/session support
- Sanctum if you want the built-in token management and bearer-token API flow
- a Vite/Tailwind pipeline if you want the packaged UI to use `@vite(...)`

The package config defaults `user_model` to `App\Models\User`, which is the common Laravel case.

## Routing and Mounting

By default, the package is mounted under a dedicated web prefix:

- web UI default: `/moments`
- API default: `/api/v1/...`

You can change this by publishing and editing `config/moments.php`.

Common examples:

- keep the default package mount at `/moments`
- mount the package at the site root by setting `route_prefix` to `/`
- disable package-managed web or API routes independently
- override middleware groups to fit your host app

Route names stay stable by default:

- `moments.index`
- `moments.show`
- `api.v1.moments.index`
- `account.show`
- `login`

## Configuration

The package config file covers:

- host user model integration
- AI alt-text generation settings
- route registration toggles
- web and API prefixes
- route name prefixing
- middleware groups for public, authenticated, guest, and API routes
- whether package views should call `@vite(...)`
- image disk, image size limit, Glide signing key, and optional timeline intro text

See [`config/moments.php`](./config/moments.php) for the full inline documentation.

### AI Alt Text

The package includes an opt-in AI feature that generates accessibility-focused alt text for uploaded images and stores it on `MomentImage`.

Enable it in `config/moments.php` or via environment variables:

- `MOMENTS_ALT_TEXT_ENABLED=true`
- `MOMENTS_ALT_TEXT_PROVIDER=openai` (or another configured Laravel AI provider)
- `MOMENTS_ALT_TEXT_MODEL=` to optionally override the provider's default model

Behavior:

- new uploads queue alt-text generation in the background when the feature is enabled
- generated alt text is used in package-rendered images, with `"Moment image"` as the fallback
- existing images can be backfilled later if the feature is enabled after uploads already exist

Because generation runs asynchronously, your host app should have a queue worker running when this feature is enabled.

Backfill commands:

```bash
php artisan moments:generate-alt-text
php artisan moments:generate-alt-text --force
```

The default command only processes images that do not yet have alt text. Use `--force` to regenerate alt text for every image.

## Assets and UI

The package ships Blade views and Livewire components. By default, those views call:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

That means the host app should either:

- keep compatible Vite entrypoints, or
- disable package-managed Vite tags with `use_vite_assets => false` and include assets another way

If your Tailwind build scans Blade and JS sources explicitly, make sure it includes the package view and JS paths after installation.

If you prefer to override the packaged markup directly, publish the views to:

- `resources/views/vendor/moments`

Laravel will then load your app-level overrides for the `moments::...` view namespace.

## API and OpenAPI

The package includes an OpenAPI 3.1 description at:

- [`openapi.yaml`](./openapi.yaml)

API authentication uses Sanctum personal access tokens created from the account screen. Each new token has one of two roles:

- **Read only** grants `moments:read` and can call `GET /api/v1/moments`
- **Read & write** grants both `moments:read` and `moments:write` and can call every built-in API endpoint

The account screen defaults to Read only. Existing tokens created before roles were introduced have Sanctum's wildcard ability and remain full-access. Revoke and replace those tokens if they should no longer be able to write.

That spec describes the built-in API endpoints for:

- image upload
- timeline retrieval
- moment creation
- moment updates
- moment deletion

Image ordering is preserved everywhere the package renders or returns images:

- the web create and edit forms let authors reorder images before saving
- `POST /api/v1/moments` treats the `images` array as the final persisted order
- `PATCH /api/v1/moments/{moment}` accepts `image_order` for reorder-only or combined reorder/add/remove updates
- image objects returned by the API include a `position` field so clients can round-trip ordering explicitly

## Development in This Repository

In this repository, the package is developed locally from:

- [`/Users/phil/Herd/moments/packages/privateer/moments`](/Users/phil/Herd/moments/packages/privateer/moments)

The root host app consumes it through a Composer `path` repository so package changes are reflected locally without publishing to Packagist first.
