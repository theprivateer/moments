<?php

namespace Privateer\Moments\Actions;

use Privateer\Moments\Jobs\GenerateMomentImageAltText;
use Privateer\Moments\Support\Moments as MomentsSupport;

class StoreMomentAction
{
    public function execute(int $userId, ?string $body, array $images): object
    {
        $momentModel = MomentsSupport::momentModel();

        $moment = $momentModel::create([
            'user_id' => $userId,
            'body' => $body,
        ]);

        foreach ($images as $file) {
            $disk = config('moments.image_disk');
            $image = $moment->images()->create([
                'path' => $file->store('moments', $disk),
                'disk' => $disk,
            ]);

            if (MomentsSupport::altTextEnabled()) {
                GenerateMomentImageAltText::dispatch($image->id);
            }
        }

        return $moment;
    }
}
