<?php

namespace Privateer\Moments\Actions;

use Illuminate\Support\Facades\DB;
use Privateer\Moments\Services\SyncMomentTags;
use Privateer\Moments\Support\Moments;
use Privateer\Moments\Support\SyncMomentImages;

class StoreMomentAction
{
    public function __construct(
        protected SyncMomentTags $syncMomentTags,
        protected SyncMomentImages $syncMomentImages,
    ) {}

    public function execute(int $userId, ?string $body, array $images): object
    {
        $momentModel = Moments::momentModel();

        $moment = DB::transaction(function () use ($body, $images, $momentModel, $userId): object {
            $moment = $momentModel::create([
                'user_id' => $userId,
                'body' => $body,
            ]);

            $this->syncMomentImages->sync(
                $moment,
                array_map(fn ($file): array => ['type' => 'upload', 'file' => $file], $images),
            );

            $this->syncMomentTags->sync($moment);

            return $moment;
        });

        return $moment;
    }
}
