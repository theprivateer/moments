<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ResolveMomentImageAction
{
    public function __invoke(
        ?string $currentPath,
        ?string $currentDisk,
        ?UploadedFile $newFile,
        bool $remove = false,
    ): array {
        if ($remove && $currentPath) {
            Storage::disk($currentDisk)->delete($currentPath);
            $currentPath = null;
        }

        $disk = config('moments.image_disk');

        if ($newFile) {
            if ($currentPath) {
                Storage::disk($currentDisk)->delete($currentPath);
            }

            $currentPath = $newFile->store('moments', $disk);
        }

        return ['path' => $currentPath, 'disk' => $currentPath ? $disk : null];
    }
}
