<?php

namespace App\Http\Controllers\Api;

use App\Actions\DispatchMomentCrossPost;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMomentRequest;
use App\Http\Requests\Api\UpdateMomentRequest;
use App\Http\Resources\MomentResource;
use App\Models\Moment;
use App\Models\MomentImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MomentController extends Controller
{
    public function __construct(private readonly DispatchMomentCrossPost $dispatchMomentCrossPost) {}

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
        $this->authorize('create', Moment::class);

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

        $this->dispatchMomentCrossPost->handle($moment, $validated['cross_post_to_threads'] ?? null);

        $moment->load('images');

        return (new MomentResource($moment))->response()->setStatusCode(201);
    }

    public function update(UpdateMomentRequest $request, Moment $moment): JsonResponse
    {
        $this->authorize('update', $moment);

        $validated = $request->validated();

        if (! empty($validated['remove_images'])) {
            $toRemove = $moment->images()->whereIn('id', $validated['remove_images'])->get();
            foreach ($toRemove as $image) {
                Storage::disk($image->disk)->delete($image->path);
                $image->delete();
            }
        }

        if (! empty($validated['add_images'])) {
            MomentImage::whereIn('id', $validated['add_images'])
                ->whereNull('moment_id')
                ->update(['moment_id' => $moment->id]);
        }

        if (array_key_exists('body', $validated)) {
            $moment->update(['body' => $validated['body']]);
        }

        $moment->load('images');

        return (new MomentResource($moment))->response();
    }

    public function destroy(Moment $moment): Response
    {
        $this->authorize('delete', $moment);

        $moment->load('images');

        foreach ($moment->images as $image) {
            Storage::disk($image->disk)->delete($image->path);
        }

        $moment->delete();

        return response()->noContent();
    }
}
