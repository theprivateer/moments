# Moments

A personal micro-blog for publishing short posts to a public timeline. Built with Laravel 12, Blade, and Tailwind CSS.

## Features

- **Public timeline** — all posts are visible to anyone without an account
- **Markdown rendering** — post content is written and rendered in Markdown
- **Single-author** — one user account owns the blog
- **Post management** — create, edit, and delete posts when logged in

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
