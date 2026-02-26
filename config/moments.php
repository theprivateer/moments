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
];
