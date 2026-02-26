<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ResolveMomentImageAction
{
    public function __invoke(?string $currentPath, ?UploadedFile $newFile, bool $remove = false): ?string
    {
        if ($remove && $currentPath) {
            Storage::disk('public')->delete($currentPath);
            $currentPath = null;
        }

        if ($newFile) {
            if ($currentPath) {
                Storage::disk('public')->delete($currentPath);
            }

            $currentPath = $newFile->store('moments', 'public');
        }

        return $currentPath;
    }
}
