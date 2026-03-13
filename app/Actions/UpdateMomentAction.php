<?php

namespace App\Actions;

use App\Models\Moment;
use Illuminate\Support\Facades\Storage;

class UpdateMomentAction
{
    public function execute(Moment $moment, ?string $body, array $removeIds, array $newImages): Moment
    {
        $toRemove = $moment->images()->whereIn('id', $removeIds)->get();
        foreach ($toRemove as $image) {
            Storage::disk($image->disk)->delete($image->path);
            $image->delete();
        }

        foreach ($newImages as $file) {
            $disk = config('moments.image_disk');
            $moment->images()->create(['path' => $file->store('moments', $disk), 'disk' => $disk]);
        }

        $moment->update(['body' => $body]);

        return $moment;
    }
}
