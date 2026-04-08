<?php

namespace Privateer\Moments\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Services\SyncMomentTags;
use Privateer\Moments\Support\SyncMomentImages;

class UpdateMomentAction
{
    public function __construct(
        protected SyncMomentTags $syncMomentTags,
        protected SyncMomentImages $syncMomentImages,
    ) {}

    public function execute(object $moment, ?string $body, array $removeIds, array $orderedImages): object
    {
        DB::transaction(function () use ($body, $moment, $orderedImages, $removeIds): void {
            $toRemove = $moment->images()->whereIn('id', $removeIds)->get();

            foreach ($toRemove as $image) {
                Storage::disk($image->disk)->delete($image->path);
                $image->delete();
            }

            $this->syncMomentImages->sync($moment, $orderedImages);
            $moment->update(['body' => $body]);
            $this->syncMomentTags->sync($moment);
        });

        return $moment;
    }
}
