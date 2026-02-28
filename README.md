# Moments

A personal micro-blog for publishing short posts to a public timeline. Built with Laravel 12, Blade, and Tailwind CSS.

## Features

- **Public timeline** — all posts are visible to anyone without an account
- **Markdown rendering** — post content is written and rendered in Markdown
- **Single-author** — one user account owns the blog
- **Post management** — create, edit, and delete posts when logged in
- **Image attachments** — moments can include multiple images; body is optional when at least one image is present
- **Permalinks** — each moment has its own page
- **RSS feed** — subscribe at `/feed` with any feed reader
- **API access** — post moments programmatically via a REST API using bearer tokens
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

If using the default `public` disk, run `php artisan storage:link` once to make uploaded images publicly accessible.

## API

Moments exposes a REST API for posting moments from external clients.

### Getting an API token

Log in, visit `/tokens`, give the token a name, and click **Create**. Copy the token value immediately — it is only shown once. You can revoke tokens from the same page.

### Endpoint

| Method | Endpoint | Auth |
|--------|----------|------|
| `POST` | `/api/moments` | Bearer token |

### Request

Send as `multipart/form-data`. At least one of `body` or `images[]` must be provided.

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `body` | string | Required if no images | Moment text. Markdown is supported (max 10,000 chars). |
| `images[]` | file | Required if no body | One or more image files to attach (max 2 MB each). |

> [!IMPORTANT]
> All API requests must include the `Accept: application/json` header. Without it, validation errors will return an HTML redirect (302) instead of a JSON `422` error response.

### Response

**201 Created** on success:

```json
{
  "data": {
    "id": 1,
    "body": "Hello from the API",
    "body_html": "<p>Hello from the API</p>\n",
    "created_at": "2026-02-28T09:00:00.000000Z",
    "images": []
  }
}
```

| Status | Meaning |
|--------|---------|
| `201 Created` | Moment created successfully |
| `401 Unauthorized` | Missing or invalid token |
| `422 Unprocessable` | Validation failed (e.g. neither body nor image provided) |

### Examples

**Text-only moment:**
```bash
curl -X POST http://moments.test/api/moments \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json" \
  -F "body=Hello from the API"
```

**Image-only moment:**
```bash
curl -X POST http://moments.test/api/moments \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json" \
  -F "images[]=@photo.jpg"
```

**Text and image:**
```bash
curl -X POST http://moments.test/api/moments \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json" \
  -F "body=A moment with a photo" \
  -F "images[]=@photo.jpg"
```
