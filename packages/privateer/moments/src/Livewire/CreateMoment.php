<?php

namespace Privateer\Moments\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Privateer\Moments\Actions\StoreMomentAction;
use Privateer\Moments\Support\Moments as MomentsSupport;

class CreateMoment extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public string $body = '';

    public array $images = [];

    public array $imageHandles = [];

    public array $imageOrder = [];

    public function render(): View
    {
        return view('moments::livewire.create-moment');
    }

    public function updatedImages(): void
    {
        $this->validateOnly('images.*', [
            'images.*' => ['image', 'max:'.config('moments.image_max_size')],
        ]);

        $this->syncImageHandles();
    }

    public function removeImage(string $handle): void
    {
        $this->authorize('create', MomentsSupport::momentModel());

        $index = array_search($handle, $this->imageHandles, true);

        if ($index === false) {
            return;
        }

        array_splice($this->images, $index, 1);
        array_splice($this->imageHandles, $index, 1);
        $this->imageOrder = array_values(array_filter(
            $this->imageOrder,
            fn (string $orderedHandle): bool => $orderedHandle !== $handle,
        ));
    }

    public function moveImage(string $handle, string $direction): void
    {
        $this->authorize('create', MomentsSupport::momentModel());

        $this->swapImageOrder($handle, $direction);
    }

    public function save(StoreMomentAction $action): void
    {
        $this->authorize('create', MomentsSupport::momentModel());
        $this->syncImageHandles();

        $this->validate([
            'body' => [
                Rule::requiredIf(fn (): bool => empty($this->images)),
                'nullable',
                'string',
                'max:10000',
            ],
            'images.*' => ['image', 'max:'.config('moments.image_max_size')],
        ]);

        $action->execute(
            auth()->id(),
            $this->body ?: null,
            $this->orderedUploads(),
        );

        $this->redirect(route('moments.index'), navigate: false);
    }

    public function getOrderedImagePreviewsProperty(): array
    {
        return array_map(function (string $handle): array {
            $index = array_search($handle, $this->imageHandles, true);
            $image = $this->images[$index];

            return [
                'handle' => $handle,
                'temporary_url' => $image->temporaryUrl(),
            ];
        }, $this->imageOrder);
    }

    protected function orderedUploads(): array
    {
        return array_map(function (string $handle): object {
            $index = array_search($handle, $this->imageHandles, true);

            return $this->images[$index];
        }, $this->imageOrder);
    }

    protected function swapImageOrder(string $handle, string $direction): void
    {
        $index = array_search($handle, $this->imageOrder, true);

        if ($index === false) {
            return;
        }

        $targetIndex = $direction === 'left' ? $index - 1 : $index + 1;

        if (! isset($this->imageOrder[$targetIndex])) {
            return;
        }

        [$this->imageOrder[$index], $this->imageOrder[$targetIndex]] = [
            $this->imageOrder[$targetIndex],
            $this->imageOrder[$index],
        ];
    }

    protected function syncImageHandles(): void
    {
        while (count($this->imageHandles) < count($this->images)) {
            $this->imageHandles[] = (string) Str::uuid();
        }

        if (count($this->imageHandles) > count($this->images)) {
            $this->imageHandles = array_slice($this->imageHandles, 0, count($this->images));
        }

        $this->imageOrder = array_values(array_intersect($this->imageOrder, $this->imageHandles));

        foreach ($this->imageHandles as $handle) {
            if (! in_array($handle, $this->imageOrder, true)) {
                $this->imageOrder[] = $handle;
            }
        }
    }
}
