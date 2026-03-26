<?php

namespace Privateer\Moments\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Privateer\Moments\Actions\DestroyMomentAction;
use Privateer\Moments\Actions\StoreMomentAction;
use Privateer\Moments\Actions\UpdateMomentAction;
use Privateer\Moments\Http\Requests\StoreMomentRequest;
use Privateer\Moments\Http\Requests\UpdateMomentRequest;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Support\Moments as MomentsSupport;

class MomentController extends Controller
{
    public function index(): View
    {
        $momentModel = MomentsSupport::momentModel();
        $moments = $momentModel::query()->with(['user', 'images'])->latest()->simplePaginate(10);

        $intro = config('moments.intro')
            ? Str::markdown(config('moments.intro'), ['html_input' => 'strip', 'allow_unsafe_links' => false])
            : null;

        return view('moments::moments.index', ['moments' => $moments, 'intro' => $intro]);
    }

    public function show(Moment $moment): View
    {
        $moment->load(['user', 'images']);

        return view('moments::moments.show', ['moment' => $moment]);
    }

    public function store(StoreMomentRequest $request, StoreMomentAction $action): RedirectResponse
    {
        $this->authorize('create', MomentsSupport::momentModel());

        $validated = $request->validated();

        $action->execute(
            $request->user()->id,
            isset($validated['body']) ? ($validated['body'] ?: null) : null,
            $validated['images'] ?? [],
        );

        return redirect()->route('moments.index');
    }

    public function edit(Moment $moment): View
    {
        $this->authorize('update', $moment);

        $moment->load('images');

        return view('moments::moments.edit', ['moment' => $moment]);
    }

    public function update(UpdateMomentRequest $request, Moment $moment, UpdateMomentAction $action): RedirectResponse
    {
        $this->authorize('update', $moment);

        $validated = $request->validated();

        $action->execute(
            $moment,
            ($validated['body'] ?? '') ?: null,
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
