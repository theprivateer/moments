<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImageRequest;
use App\Http\Resources\MomentImageResource;
use App\Models\MomentImage;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    public function store(StoreImageRequest $request): JsonResponse
    {
        $disk = config('moments.image_disk');

        $image = MomentImage::create([
            'moment_id' => null,
            'path' => $request->file('image')->store('moments', $disk),
            'disk' => $disk,
        ]);

        return (new MomentImageResource($image))->response()->setStatusCode(201);
    }
}
