<?php

namespace Privateer\Moments\Support;

use Privateer\Moments\Jobs\GenerateMomentImageAltText;

class SyncMomentImages
{
    public function sync(object $moment, array $orderedImages): void
    {
        foreach (array_values($orderedImages) as $index => $image) {
            $position = $index + 1;

            match ($image['type']) {
                'attach' => $this->attachExistingImage($moment, $image['id'], $position),
                'existing' => $this->updateExistingImageOrder($moment, $image['id'], $position),
                'upload' => $this->storeUploadedImage($moment, $image['file'], $position),
            };
        }
    }

    protected function attachExistingImage(object $moment, int $imageId, int $position): void
    {
        $momentImageModel = Moments::momentImageModel();

        $momentImageModel::query()
            ->whereKey($imageId)
            ->whereNull('moment_id')
            ->update([
                'moment_id' => $moment->id,
                'sort_order' => $position,
            ]);
    }

    protected function updateExistingImageOrder(object $moment, int $imageId, int $position): void
    {
        $moment->images()->whereKey($imageId)->update(['sort_order' => $position]);
    }

    protected function storeUploadedImage(object $moment, object $file, int $position): void
    {
        $disk = config('moments.image_disk');

        $image = $moment->images()->create([
            'path' => $file->store('moments', $disk),
            'disk' => $disk,
            'sort_order' => $position,
        ]);

        if (Moments::altTextEnabled()) {
            GenerateMomentImageAltText::dispatch($image->id);
        }
    }
}
