<?php

namespace Privateer\Moments\Actions;

use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Jobs\GenerateMomentImageAltText;
use Privateer\Moments\Services\SyncMomentTags;
use Privateer\Moments\Support\Moments as MomentsSupport;

class UpdateMomentAction
{
    public function __construct(
        protected SyncMomentTags $syncMomentTags,
    ) {}

    public function execute(object $moment, ?string $body, array $removeIds, array $newImages): object
    {
        $toRemove = $moment->images()->whereIn('id', $removeIds)->get();

        foreach ($toRemove as $image) {
            Storage::disk($image->disk)->delete($image->path);
            $image->delete();
        }

        foreach ($newImages as $file) {
            $disk = config('moments.image_disk');
            $image = $moment->images()->create([
                'path' => $file->store('moments', $disk),
                'disk' => $disk,
            ]);

            if (MomentsSupport::altTextEnabled()) {
                GenerateMomentImageAltText::dispatch($image->id);
            }
        }

        $moment->update(['body' => $body]);
        $this->syncMomentTags->sync($moment);

        return $moment;
    }
}
