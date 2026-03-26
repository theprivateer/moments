<?php

namespace Privateer\Moments\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Support\Moments as MomentsSupport;

class DeleteOrphanImagesCommand extends Command
{
    protected $signature = 'moments:delete-orphan-images';

    protected $description = 'Delete uploaded images that were never attached to a moment';

    public function handle(): int
    {
        $momentImageModel = MomentsSupport::momentImageModel();

        $orphans = $momentImageModel::query()
            ->whereNull('moment_id')
            ->where('created_at', '<', now()->subMinutes(20))
            ->get();

        foreach ($orphans as $image) {
            Storage::disk($image->disk)->delete($image->path);
            $image->delete();
        }

        $this->components->info("Deleted {$orphans->count()} orphan image(s).");

        return self::SUCCESS;
    }
}
