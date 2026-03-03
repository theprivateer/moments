<?php

namespace App\Console\Commands;

use App\Models\MomentImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteOrphanImagesCommand extends Command
{
    protected $signature = 'moments:delete-orphan-images';

    protected $description = 'Delete uploaded images that were never attached to a moment';

    public function handle(): int
    {
        $orphans = MomentImage::whereNull('moment_id')
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
