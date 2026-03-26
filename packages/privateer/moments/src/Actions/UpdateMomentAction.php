<?php

namespace Privateer\Moments\Actions;

use Illuminate\Support\Facades\Storage;

class UpdateMomentAction
{
    public function execute(object $moment, ?string $body, array $removeIds, array $newImages): object
    {
        $toRemove = $moment->images()->whereIn('id', $removeIds)->get();

        foreach ($toRemove as $image) {
            Storage::disk($image->disk)->delete($image->path);
            $image->delete();
        }

        foreach ($newImages as $file) {
            $disk = config('moments.image_disk');
            $moment->images()->create([
                'path' => $file->store('moments', $disk),
                'disk' => $disk,
            ]);
        }

        $moment->update(['body' => $body]);

        return $moment;
    }
}
