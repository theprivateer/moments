<?php

namespace Privateer\Moments\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Actions\DestroyMomentAction;
use Privateer\Moments\Http\Controllers\Controller;
use Privateer\Moments\Http\Requests\Api\StoreMomentRequest;
use Privateer\Moments\Http\Requests\Api\UpdateMomentRequest;
use Privateer\Moments\Http\Resources\MomentResource;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

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
        $this->authorize('create', Moment::class);

        $validated = $request->validated();

        $moment = Moment::create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'] ?? null,
        ]);

        if (isset($validated['created_at'])) {
            $moment->created_at = Carbon::createFromTimestamp($validated['created_at']);
            $moment->save();
        }

        if (! empty($validated['images'])) {
            MomentImage::query()
                ->whereIn('id', $validated['images'])
                ->whereNull('moment_id')
                ->update(['moment_id' => $moment->id]);
        }

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
            MomentImage::query()
                ->whereIn('id', $validated['add_images'])
                ->whereNull('moment_id')
                ->update(['moment_id' => $moment->id]);
        }

        if (array_key_exists('body', $validated)) {
            $moment->update(['body' => $validated['body']]);
        }

        $moment->load('images');

        return (new MomentResource($moment))->response();
    }

    public function destroy(Moment $moment, DestroyMomentAction $action): Response
    {
        $this->authorize('delete', $moment);

        $action->execute($moment);

        return response()->noContent();
    }
}
