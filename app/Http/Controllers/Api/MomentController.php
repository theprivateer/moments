<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMomentRequest;
use App\Http\Resources\MomentResource;
use App\Models\Moment;
use App\Models\MomentImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MomentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $moments = Moment::query()
            ->with('images')
            ->latest()
            ->paginate(20);

        return MomentResource::collection($moments);
    }

    public function store(StoreMomentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $moment = Moment::create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'] ?? null,
        ]);

        if (! empty($validated['images'])) {
            MomentImage::whereIn('id', $validated['images'])
                ->whereNull('moment_id')
                ->update(['moment_id' => $moment->id]);
        }

        $moment->load('images');

        return (new MomentResource($moment))->response()->setStatusCode(201);
    }
}
