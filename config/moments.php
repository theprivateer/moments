<?php

return [
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
];
