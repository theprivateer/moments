<?php

namespace Privateer\Moments\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

    public function render(): View
    {
        return view('moments::livewire.create-moment');
    }

    public function updatedImages(): void
    {
        $this->validateOnly('images.*', [
            'images.*' => ['image', 'max:'.config('moments.image_max_size')],
        ]);
    }

    public function removeImage(int $index): void
    {
        $this->authorize('create', MomentsSupport::momentModel());

        array_splice($this->images, $index, 1);
    }

    public function save(StoreMomentAction $action): void
    {
        $this->authorize('create', MomentsSupport::momentModel());

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
            $this->images,
        );

        $this->redirect(route('moments.index'), navigate: false);
    }
}
