<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMomentRequest;
use App\Http\Requests\UpdateMomentRequest;
use App\Models\Moment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MomentController extends Controller
{
    public function index(): View
    {
        $moments = Moment::query()->with(['user', 'images'])->latest()->simplePaginate(10);

        return view('moments.index', ['moments' => $moments]);
    }

    public function show(Moment $moment): View
    {
        $moment->load(['user', 'images']);

        return view('moments.show', ['moment' => $moment]);
    }

    public function store(StoreMomentRequest $request): RedirectResponse
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

        return redirect()->route('moments.index');
    }

    public function edit(Moment $moment): View
    {
        $this->authorize('update', $moment);

        $moment->load('images');

        return view('moments.edit', ['moment' => $moment]);
    }

    public function update(UpdateMomentRequest $request, Moment $moment): RedirectResponse
    {
        $this->authorize('update', $moment);

        $validated = $request->validated();

        $toRemove = $moment->images()->whereIn('id', $validated['remove_images'] ?? [])->get();
        foreach ($toRemove as $image) {
            Storage::disk($image->disk)->delete($image->path);
            $image->delete();
        }

        foreach ($request->file('images', []) as $file) {
            $disk = config('moments.image_disk');
            $moment->images()->create(['path' => $file->store('moments', $disk), 'disk' => $disk]);
        }

        $moment->update(['body' => $validated['body'] ?? null]);

        return redirect()->route('moments.index');
    }

    public function destroy(Moment $moment): RedirectResponse
    {
        $this->authorize('delete', $moment);

        foreach ($moment->images as $image) {
            Storage::disk($image->disk)->delete($image->path);
        }

        $moment->delete();

        return redirect()->route('moments.index');
    }
}
