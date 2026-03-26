<?php

namespace Privateer\Moments\Actions;

use Illuminate\Support\Facades\Storage;

class DestroyMomentAction
{
    public function execute(object $moment): void
    {
        $moment->load('images');

        foreach ($moment->images as $image) {
            Storage::disk($image->disk)->delete($image->path);
        }

        $moment->delete();
    }
}
