<?php

namespace Privateer\Moments\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Privateer\Moments\Actions\UpdateMomentAction;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Support\Moments as MomentsSupport;

class EditMoment extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    #[Locked]
    public int $momentId;

    public string $body = '';

    public array $existingImages = [];

    public array $imageOrder = [];

    public array $imagesToRemove = [];

    public array $newImages = [];

    public array $newImageHandles = [];

    public function mount(Moment $moment): void
    {
        $this->authorize('update', $moment);

        $moment->loadMissing('images');
        $this->momentId = $moment->id;
        $this->body = $moment->body ?? '';
        $this->existingImages = $moment->images
            ->mapWithKeys(fn ($image): array => [
                $this->existingImageToken($image->id) => [
                    'id' => $image->id,
                    'glideUrl' => $image->glideUrl(400),
                ],
            ])
            ->all();
        $this->imageOrder = array_keys($this->existingImages);
    }

    public function render(): View
    {
        return view('moments::livewire.edit-moment');
    }

    public function updatedNewImages(): void
    {
        $this->validateOnly('newImages.*', [
            'newImages.*' => ['image', 'max:'.config('moments.image_max_size')],
        ]);

        $this->syncNewImageHandles();
    }

    public function removeNewImage(string $handle): void
    {
        $momentModel = MomentsSupport::momentModel();

        $this->authorize('update', $momentModel::query()->findOrFail($this->momentId));

        $index = array_search($handle, $this->newImageHandles, true);

        if ($index === false) {
            return;
        }

        array_splice($this->newImages, $index, 1);
        array_splice($this->newImageHandles, $index, 1);
        $this->imageOrder = array_values(array_filter(
            $this->imageOrder,
            fn (string $token): bool => $token !== $this->newImageToken($handle),
        ));
    }

    public function moveImage(string $token, string $direction): void
    {
        $momentModel = MomentsSupport::momentModel();

        $this->authorize('update', $momentModel::query()->findOrFail($this->momentId));

        $this->swapImageOrder($token, $direction);
    }

    public function toggleImageRemoval(int $imageId): void
    {
        $momentModel = MomentsSupport::momentModel();

        $this->authorize('update', $momentModel::query()->findOrFail($this->momentId));

        if (in_array($imageId, $this->imagesToRemove, true)) {
            $this->imagesToRemove = array_values(array_filter(
                $this->imagesToRemove,
                fn (int $removedImageId): bool => $removedImageId !== $imageId,
            ));

            return;
        }

        $this->imagesToRemove[] = $imageId;
    }

    public function save(UpdateMomentAction $action): void
    {
        $momentModel = MomentsSupport::momentModel();
        $moment = $momentModel::query()->findOrFail($this->momentId);
        $this->authorize('update', $moment);
        $this->syncNewImageHandles();

        $remaining = count($this->orderedImages());

        $this->validate([
            'body' => [
                Rule::requiredIf(fn (): bool => $remaining <= 0 && empty($this->newImages)),
                'nullable',
                'string',
                'max:10000',
            ],
            'newImages.*' => ['image', 'max:'.config('moments.image_max_size')],
            'imagesToRemove.*' => ['integer', 'exists:moment_images,id'],
        ]);

        $action->execute(
            $moment,
            $this->body ?: null,
            $this->imagesToRemove,
            $this->orderedImages(),
        );

        $this->redirect(route('moments.index'), navigate: false);
    }

    public function getOrderedImageItemsProperty(): array
    {
        $items = [];

        foreach ($this->imageOrder as $token) {
            if (isset($this->existingImages[$token])) {
                $items[] = [
                    'token' => $token,
                    'type' => 'existing',
                    'id' => $this->existingImages[$token]['id'],
                    'preview_url' => $this->existingImages[$token]['glideUrl'],
                    'is_removed' => in_array($this->existingImages[$token]['id'], $this->imagesToRemove, true),
                ];

                continue;
            }

            $handle = Str::after($token, 'new:');
            $index = array_search($handle, $this->newImageHandles, true);

            if ($index === false || ! isset($this->newImages[$index])) {
                continue;
            }

            $items[] = [
                'token' => $token,
                'type' => 'new',
                'handle' => $handle,
                'preview_url' => $this->newImages[$index]->temporaryUrl(),
            ];
        }

        return $items;
    }

    protected function orderedImages(): array
    {
        $orderedImages = [];

        foreach ($this->imageOrder as $token) {
            if (isset($this->existingImages[$token])) {
                $imageId = $this->existingImages[$token]['id'];

                if (! in_array($imageId, $this->imagesToRemove, true)) {
                    $orderedImages[] = ['type' => 'existing', 'id' => $imageId];
                }

                continue;
            }

            $handle = Str::after($token, 'new:');
            $index = array_search($handle, $this->newImageHandles, true);

            if ($index !== false && isset($this->newImages[$index])) {
                $orderedImages[] = ['type' => 'upload', 'file' => $this->newImages[$index]];
            }
        }

        return $orderedImages;
    }

    protected function existingImageToken(int $imageId): string
    {
        return 'existing:'.$imageId;
    }

    protected function newImageToken(string $handle): string
    {
        return 'new:'.$handle;
    }

    protected function swapImageOrder(string $token, string $direction): void
    {
        $index = array_search($token, $this->imageOrder, true);

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

    protected function syncNewImageHandles(): void
    {
        while (count($this->newImageHandles) < count($this->newImages)) {
            $handle = (string) Str::uuid();
            $this->newImageHandles[] = $handle;
            $this->imageOrder[] = $this->newImageToken($handle);
        }

        if (count($this->newImageHandles) > count($this->newImages)) {
            $this->newImageHandles = array_slice($this->newImageHandles, 0, count($this->newImages));
        }

        $validTokens = array_merge(
            array_keys($this->existingImages),
            array_map(fn (string $handle): string => $this->newImageToken($handle), $this->newImageHandles),
        );

        $this->imageOrder = array_values(array_intersect($this->imageOrder, $validTokens));

        foreach ($validTokens as $token) {
            if (! in_array($token, $this->imageOrder, true)) {
                $this->imageOrder[] = $token;
            }
        }
    }
}
