# Moments

A personal micro-blog for publishing short posts to a public timeline. Built with Laravel 12, Blade, and Tailwind CSS.

## Features

- **Public timeline** — all posts are visible to anyone without an account
- **Markdown rendering** — post content is written and rendered in Markdown
- **Single-author** — one user account owns the blog
- **Post management** — create, edit, and delete posts when logged in
- **Image attachments** — moments can include multiple images; body is optional when at least one image is present
- **Image optimisation** — images are resized server-side via Glide to match their display dimensions, reducing bandwidth without sacrificing quality
- **Image lightbox** — clicking any image opens it full-screen using the native `<dialog>` element; no JavaScript framework required
- **Permalinks** — each moment has its own page
- **RSS feed** — subscribe at `/feed` with any feed reader
- **API access** — post moments programmatically via a REST API using bearer tokens; machine-readable spec at `openapi.yaml`
- **API token management** — create and revoke personal access tokens from the web UI at `/tokens`

> [!NOTE]
> I am intentionally using **Claude Code** to help build and maintain this project as an exploration of using AI coding assistants. I have chosen this project as it is a reimagining of [an idea I had in early 2017](https://github.com/theprivateer/shortform), so the spec is fairly well documented.

## Getting Started

### Requirements

- PHP 8.4+
- Composer
- Node.js & npm
- [Laravel Herd](https://herd.laravel.com) (or another local server)

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

### Create your account

```bash
php artisan moments:install
```

This will prompt you for a name, email, and password to create the owner account.

### Start the dev server

```bash
composer run dev
```

Then visit [http://moments.test](http://moments.test) in your browser.

## Configuration

| Variable | Default | Description |
|---|---|---|
| `MOMENTS_IMAGE_DISK` | `public` | Filesystem disk for uploaded images. Set to `s3` to store images in S3. |
| `MOMENTS_IMAGE_MAX_SIZE` | `2048` | Maximum image upload size in KB (default: 2048 = 2 MB). |
| `GLIDE_SIGN_KEY` | _(none)_ | Secret key used to sign Glide image URLs. Generate with `php artisan tinker moments:glide-key`. |

If using the default `public` disk, run `php artisan storage:link` once to make uploaded images publicly accessible.

## API

Moments exposes a REST API for posting moments from external clients. A full OpenAPI 3.1 description is available at [`openapi.yaml`](openapi.yaml) in the project root — import it into any OpenAPI-compatible tool (Insomnia, Postman, Scalar, etc.) to explore and test the API.

### Getting an API token

Log in, visit `/tokens`, give the token a name, and click **Create**. Copy the token value immediately — it is only shown once. You can revoke tokens from the same page.

### Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/v1/images` | Bearer token | Upload an image, receive an image ID |
| `POST` | `/api/v1/moments` | Bearer token | Create a moment, referencing uploaded image IDs |

### Two-step workflow

Posting a moment with images requires two steps:

1. **Upload each image** via `POST /api/v1/images` — returns an `id` for each uploaded image.
2. **Create the moment** via `POST /api/v1/moments` — pass the image IDs you collected in step 1.

> [!IMPORTANT]
> All API requests must include the `Accept: application/json` header. Without it, validation errors will return an HTML redirect (302) instead of a JSON `422` error response.

### POST /api/v1/images

Send as `multipart/form-data`.

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `image` | file | Yes | Image file to upload (max size configurable via `MOMENTS_IMAGE_MAX_SIZE`, default 2 MB). |

**201 Created** on success:

```json
{
  "data": {
    "id": 42,
    "url": "https://moments.test/img/moments/photo.jpg?s=..."
  }
}
```

### POST /api/v1/moments

Send as `application/json`. At least one of `body` or `images` must be provided.

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `body` | string | Required if no images | Moment text. Markdown is supported (max 10,000 chars). |
| `images` | integer[] | Required if no body | IDs of pre-uploaded images (from `POST /api/v1/images`). |

**201 Created** on success:

```json
{
  "data": {
    "id": 1,
    "body": "Hello from the API",
    "body_html": "<p>Hello from the API</p>\n",
    "created_at": "2026-02-28T09:00:00.000000Z",
    "images": [
      { "id": 42, "url": "https://moments.test/img/moments/photo.jpg?s=..." }
    ]
  }
}
```

| Status | Meaning |
|--------|---------|
| `201 Created` | Resource created successfully |
| `401 Unauthorized` | Missing or invalid token |
| `422 Unprocessable` | Validation failed |

### Examples

**Text-only moment:**
```bash
curl -X POST http://moments.test/api/v1/moments \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"body": "Hello from the API"}'
```

**Image-only moment (two steps):**
```bash
# Step 1: upload the image
IMAGE_ID=$(curl -s -X POST http://moments.test/api/v1/images \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json" \
  -F "image=@photo.jpg" | jq '.data.id')

# Step 2: create the moment
curl -X POST http://moments.test/api/v1/moments \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"images\": [$IMAGE_ID]}"
```

**Text and image:**
```bash
# Step 1: upload the image
IMAGE_ID=$(curl -s -X POST http://moments.test/api/v1/images \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json" \
  -F "image=@photo.jpg" | jq '.data.id')

# Step 2: create the moment
curl -X POST http://moments.test/api/v1/moments \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"body\": \"A moment with a photo\", \"images\": [$IMAGE_ID]}"
```

## Maintenance

### Scheduled tasks

The application includes a scheduled task that automatically removes uploaded images
that were never attached to a moment (orphaned by an incomplete API upload workflow).
It runs every 5 minutes and only removes images older than 20 minutes, giving
in-flight uploads time to complete.

To run the Laravel scheduler (required for automatic cleanup):

```bash
php artisan schedule:work
```

> **Note:** `composer run dev` does not start the scheduler. For local development,
> run `php artisan schedule:work` in a separate terminal or clean up orphans manually.

### Manual cleanup

To delete orphaned images on demand:

```bash
php artisan moments:delete-orphan-images
```
