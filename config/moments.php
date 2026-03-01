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
];
