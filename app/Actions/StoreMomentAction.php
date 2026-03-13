<?php

namespace App\Actions;

use App\Models\Moment;

class StoreMomentAction
{
    public function execute(int $userId, ?string $body, array $images): Moment
    {
        $moment = Moment::create([
            'user_id' => $userId,
            'body' => $body,
        ]);

        foreach ($images as $file) {
            $disk = config('moments.image_disk');
            $moment->images()->create(['path' => $file->store('moments', $disk), 'disk' => $disk]);
        }

        return $moment;
    }
}
