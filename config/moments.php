<?php

use App\Models\User;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

return [
    /*
    |--------------------------------------------------------------------------
    | Host User Model
    |--------------------------------------------------------------------------
    |
    | The package attaches moments to the host application's authenticatable
    | user model. This should be the model class that owns created moments,
    | logs into the package UI, and receives Sanctum personal access tokens.
    |
    */
    'user_model' => User::class,

    /*
    |--------------------------------------------------------------------------
    | Host Moment Models
    |--------------------------------------------------------------------------
    |
    | These model classes are used for moments and their attached images.
    | Host applications may replace them with subclasses of the package
    | models to add behavior while preserving the expected schema.
    |
    */
    'moment_model' => Moment::class,
    'moment_image_model' => MomentImage::class,

    /*
    |--------------------------------------------------------------------------
    | AI Image Alt Text
    |--------------------------------------------------------------------------
    |
    | Enable this feature to generate accessibility-focused alt text for
    | uploaded moment images in the background. Choose the AI provider the
    | package should use, and optionally pin a specific model name.
    |
    */
    'alt_text' => [
        'enabled' => (bool) env('MOMENTS_ALT_TEXT_ENABLED', false),
        'provider' => env('MOMENTS_ALT_TEXT_PROVIDER'),
        'model' => env('MOMENTS_ALT_TEXT_MODEL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Registration
    |--------------------------------------------------------------------------
    |
    | These flags control whether the package should register its built-in
    | web UI routes and API routes. The development host app enables both,
    | while external consumers may disable one surface or the other.
    |
    */
    'register_web_routes' => true,
    'register_api_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Route Prefixes
    |--------------------------------------------------------------------------
    |
    | The web route prefix controls where the package UI is mounted. This
    | host app intentionally uses "/" so the package takes over the root
    | site, while the package default for external installs is "moments".
    |
    | The API prefix controls the base segment above the versioned API
    | routes, which defaults to "api" and produces "/api/v1/..." URLs.
    |
    */
    'route_prefix' => env('MOMENTS_ROUTE_PREFIX', '/'),
    'api_prefix' => env('MOMENTS_API_PREFIX', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Route Name Prefix
    |--------------------------------------------------------------------------
    |
    | Optionally prepend a name prefix to all package routes. Leaving this
    | blank preserves the default route names such as "moments.index" and
    | "api.v1.moments.index".
    |
    */
    'route_name_prefix' => env('MOMENTS_ROUTE_NAME_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Middleware Groups
    |--------------------------------------------------------------------------
    |
    | These middleware stacks are applied to the package's public web routes,
    | authenticated routes, guest-only routes, and API routes. Override them
    | if the host app needs custom auth, session, or API middleware.
    |
    */
    'web_middleware' => ['web'],
    'authenticated_middleware' => ['auth'],
    'guest_middleware' => ['guest'],
    'api_middleware' => ['api', 'auth:sanctum'],

    /*
    |--------------------------------------------------------------------------
    | Vite Asset Rendering
    |--------------------------------------------------------------------------
    |
    | When enabled, the package layout will render the host app's Vite
    | entrypoints via @vite(...). Disable this if the host application
    | includes package styles and scripts through another asset pipeline.
    |
    */
    'use_vite_assets' => true,

    /*
    |--------------------------------------------------------------------------
    | Image Storage Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk used to store and serve moment images. This value
    | is persisted on each moment record so reads and deletes always use the
    | correct disk, even if this setting changes in the future.
    |
    */
    'image_disk' => env('MOMENTS_IMAGE_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Glide Signing Key
    |--------------------------------------------------------------------------
    |
    | A secret key used to sign image transformation URLs served via Glide.
    | This prevents unauthorised manipulation of image parameters. Generate
    | a strong random value and keep it out of version control.
    |
    */
    'glide_sign_key' => env('GLIDE_SIGN_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Image Upload Size Limit
    |--------------------------------------------------------------------------
    |
    | The maximum allowed size for each uploaded image, in kilobytes. This
    | value is passed directly to Laravel's validation `max` rule. The default
    | of 2048 permits files up to 2 MB. Increase for high-resolution uploads
    | or decrease to conserve storage.
    |
    */
    'image_max_size' => (int) env('MOMENTS_IMAGE_MAX_SIZE', 2048),

    /*
    |--------------------------------------------------------------------------
    | Timeline Introduction
    |--------------------------------------------------------------------------
    |
    | Optional Markdown text prepended to the public timeline. Leave blank (or
    | unset) to show no introduction. Supports standard Markdown — raw HTML
    | and unsafe links are stripped before rendering.
    |
    */
    'intro' => env('MOMENTS_INTRO'),
];
