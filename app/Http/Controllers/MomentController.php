<?php

namespace App\Http\Controllers;

use App\Actions\DestroyMomentAction;
use App\Actions\StoreMomentAction;
use App\Actions\UpdateMomentAction;
use App\Http\Requests\StoreMomentRequest;
use App\Http\Requests\UpdateMomentRequest;
use App\Models\Moment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MomentController extends Controller
{
    public function index(): View
    {
        $moments = Moment::query()->with(['user', 'images'])->latest()->simplePaginate(10);

        $intro = config('moments.intro')
            ? Str::markdown(config('moments.intro'), ['html_input' => 'strip', 'allow_unsafe_links' => false])
            : null;

        return view('moments.index', ['moments' => $moments, 'intro' => $intro]);
    }

    public function show(Moment $moment): View
    {
        $moment->load(['user', 'images']);

        return view('moments.show', ['moment' => $moment]);
    }

    public function store(StoreMomentRequest $request, StoreMomentAction $action): RedirectResponse
    {
        $this->authorize('create', Moment::class);

        $validated = $request->validated();

        $action->execute(
            $request->user()->id,
            $validated['body'] ?? null,
            $validated['images'] ?? [],
        );

        return redirect()->route('moments.index');
    }

    public function edit(Moment $moment): View
    {
        $this->authorize('update', $moment);

        $moment->load('images');

        return view('moments.edit', ['moment' => $moment]);
    }

    public function update(UpdateMomentRequest $request, Moment $moment, UpdateMomentAction $action): RedirectResponse
    {
        $this->authorize('update', $moment);

        $validated = $request->validated();

        $action->execute(
            $moment,
            $validated['body'] ?? null,
            $validated['remove_images'] ?? [],
            $validated['images'] ?? [],
        );

        return redirect()->route('moments.index');
    }

    public function destroy(Moment $moment, DestroyMomentAction $action): RedirectResponse
    {
        $this->authorize('delete', $moment);

        $action->execute($moment);

        return redirect()->route('moments.index');
    }
}
