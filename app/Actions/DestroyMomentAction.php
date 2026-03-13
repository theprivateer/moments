<?php

namespace App\Actions;

use App\Models\Moment;
use Illuminate\Support\Facades\Storage;

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
