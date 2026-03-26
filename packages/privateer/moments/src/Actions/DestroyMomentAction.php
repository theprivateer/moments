<?php

namespace Privateer\Moments\Actions;

use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Models\Moment;

class DestroyMomentAction
{
    public function execute(Moment $moment): void
    {
        $moment->load('images');

        foreach ($moment->images as $image) {
            Storage::disk($image->disk)->delete($image->path);
        }

        $moment->delete();
    }
}
