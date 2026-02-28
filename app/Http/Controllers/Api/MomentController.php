<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMomentRequest;
use App\Http\Resources\MomentResource;
use App\Models\Moment;
use Illuminate\Http\JsonResponse;

class MomentController extends Controller
{
    public function store(StoreMomentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $moment = Moment::create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'] ?? null,
        ]);

        foreach ($request->file('images', []) as $file) {
            $disk = config('moments.image_disk');
            $moment->images()->create(['path' => $file->store('moments', $disk), 'disk' => $disk]);
        }

        $moment->load('images');

        return (new MomentResource($moment))->response()->setStatusCode(201);
    }
}
