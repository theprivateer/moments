<?php

namespace Privateer\Moments\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    public array $imagesToRemove = [];

    public array $newImages = [];

    public function mount(Moment $moment): void
    {
        $this->authorize('update', $moment);

        $moment->loadMissing('images');
        $this->momentId = $moment->id;
        $this->body = $moment->body ?? '';
        $this->existingImages = $moment->images
            ->map(fn ($image): array => ['id' => $image->id, 'glideUrl' => $image->glideUrl(400)])
            ->values()
            ->all();
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
    }

    public function removeNewImage(int $index): void
    {
        $momentModel = MomentsSupport::momentModel();

        $this->authorize('update', $momentModel::query()->findOrFail($this->momentId));

        array_splice($this->newImages, $index, 1);
    }

    public function save(UpdateMomentAction $action): void
    {
        $momentModel = MomentsSupport::momentModel();
        $moment = $momentModel::query()->findOrFail($this->momentId);
        $this->authorize('update', $moment);

        $remaining = count($this->existingImages) - count($this->imagesToRemove);

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
            $this->newImages,
        );

        $this->redirect(route('moments.index'), navigate: false);
    }
}
