<?php

namespace Privateer\Moments\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Actions\DestroyMomentAction;
use Privateer\Moments\Http\Controllers\Controller;
use Privateer\Moments\Http\Requests\Api\StoreMomentRequest;
use Privateer\Moments\Http\Requests\Api\UpdateMomentRequest;
use Privateer\Moments\Http\Resources\MomentResource;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Services\SyncMomentTags;
use Privateer\Moments\Support\Moments as MomentsSupport;
use Privateer\Moments\Support\SyncMomentImages;

class MomentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $momentModel = MomentsSupport::momentModel();

        $moments = $momentModel::query()
            ->with(['images', 'tags'])
            ->latest()
            ->paginate(20);

        return MomentResource::collection($moments);
    }

    public function store(
        StoreMomentRequest $request,
        SyncMomentTags $syncMomentTags,
        SyncMomentImages $syncMomentImages,
    ): JsonResponse {
        $momentModel = MomentsSupport::momentModel();

        $this->authorize('create', $momentModel);

        $validated = $request->validated();

        $moment = DB::transaction(function () use ($momentModel, $request, $syncMomentImages, $syncMomentTags, $validated): object {
            $moment = $momentModel::create([
                'user_id' => $request->user()->id,
                'body' => $validated['body'] ?? null,
            ]);

            if (isset($validated['created_at'])) {
                $moment->created_at = Carbon::createFromTimestamp($validated['created_at']);
                $moment->save();
            }

            $syncMomentImages->sync(
                $moment,
                array_map(fn (int $imageId): array => ['type' => 'attach', 'id' => $imageId], $validated['images'] ?? []),
            );

            $syncMomentTags->sync($moment);

            return $moment;
        });

        $moment->load(['images', 'tags']);

        return (new MomentResource($moment))->response()->setStatusCode(201);
    }

    public function update(
        UpdateMomentRequest $request,
        Moment $moment,
        SyncMomentTags $syncMomentTags,
        SyncMomentImages $syncMomentImages,
    ): JsonResponse {
        $this->authorize('update', $moment);

        $validated = $request->validated();

        DB::transaction(function () use ($moment, $syncMomentImages, $syncMomentTags, $validated): void {
            if (! empty($validated['remove_images'])) {
                $toRemove = $moment->images()->whereIn('id', $validated['remove_images'])->get();

                foreach ($toRemove as $image) {
                    Storage::disk($image->disk)->delete($image->path);
                    $image->delete();
                }
            }

            $syncMomentImages->sync($moment, $this->orderedImagesForUpdate($moment, $validated));

            if (array_key_exists('body', $validated)) {
                $moment->update(['body' => $validated['body']]);
            }

            $syncMomentTags->sync($moment);
        });

        $moment->load(['images', 'tags']);

        return (new MomentResource($moment))->response();
    }

    public function destroy(Moment $moment, DestroyMomentAction $action): Response
    {
        $this->authorize('delete', $moment);

        $action->execute($moment);

        return response()->noContent();
    }

    protected function orderedImagesForUpdate(Moment $moment, array $validated): array
    {
        if (isset($validated['image_order'])) {
            $addedImageIds = array_map('intval', $validated['add_images'] ?? []);

            return array_map(function (int $imageId) use ($addedImageIds): array {
                return in_array($imageId, $addedImageIds, true)
                    ? ['type' => 'attach', 'id' => $imageId]
                    : ['type' => 'existing', 'id' => $imageId];
            }, array_map('intval', $validated['image_order']));
        }

        $removeImageIds = array_map('intval', $validated['remove_images'] ?? []);
        $orderedImages = $moment->images()
            ->whereNotIn('id', $removeImageIds)
            ->pluck('id')
            ->map(fn ($imageId): array => ['type' => 'existing', 'id' => (int) $imageId])
            ->all();

        foreach (array_map('intval', $validated['add_images'] ?? []) as $imageId) {
            $orderedImages[] = ['type' => 'attach', 'id' => $imageId];
        }

        return $orderedImages;
    }
}
