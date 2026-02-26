<?php

namespace App\Http\Controllers;

use App\Actions\ResolveMomentImageAction;
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
        $moments = Moment::query()->latest()->get();

        return view('moments.index', compact('moments'));
    }

    public function store(StoreMomentRequest $request, ResolveMomentImageAction $resolveImage): RedirectResponse
    {
        $validated = $request->validated();

        $image = $resolveImage(null, null, $request->file('image'));

        Moment::create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'image_path' => $image['path'],
            'image_disk' => $image['disk'],
        ]);

        return redirect()->route('moments.index');
    }

    public function edit(Moment $moment): View
    {
        $this->authorize('update', $moment);

        return view('moments.edit', compact('moment'));
    }

    public function update(UpdateMomentRequest $request, Moment $moment, ResolveMomentImageAction $resolveImage): RedirectResponse
    {
        $this->authorize('update', $moment);

        $validated = $request->validated();

        $image = $resolveImage(
            $moment->image_path,
            $moment->image_disk,
            $request->file('image'),
            (bool) ($validated['remove_image'] ?? false),
        );

        $moment->update([
            'body' => $validated['body'],
            'image_path' => $image['path'],
            'image_disk' => $image['disk'],
        ]);

        return redirect()->route('moments.index');
    }

    public function destroy(Moment $moment): RedirectResponse
    {
        $this->authorize('delete', $moment);

        if ($moment->image_path) {
            Storage::disk($moment->image_disk)->delete($moment->image_path);
        }

        $moment->delete();

        return redirect()->route('moments.index');
    }
}
