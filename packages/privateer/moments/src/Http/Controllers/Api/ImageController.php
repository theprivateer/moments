<?php

namespace Privateer\Moments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Privateer\Moments\Http\Controllers\Controller;
use Privateer\Moments\Http\Requests\StoreImageRequest;
use Privateer\Moments\Http\Resources\MomentImageResource;
use Privateer\Moments\Jobs\GenerateMomentImageAltText;
use Privateer\Moments\Support\Moments as MomentsSupport;

class ImageController extends Controller
{
    public function store(StoreImageRequest $request): JsonResponse
    {
        $this->authorize('create', MomentsSupport::momentModel());

        $disk = config('moments.image_disk');
        $momentImageModel = MomentsSupport::momentImageModel();

        $image = $momentImageModel::create([
            'moment_id' => null,
            'path' => $request->validated()['image']->store('moments', $disk),
            'disk' => $disk,
            'sort_order' => 0,
        ]);

        if (MomentsSupport::altTextEnabled()) {
            GenerateMomentImageAltText::dispatch($image->id);
        }

        return (new MomentImageResource($image))->response()->setStatusCode(201);
    }
}
