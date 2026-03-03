<?php

namespace App\Livewire;

use App\Models\Moment;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateMoment extends Component
{
    use WithFileUploads;

    public string $body = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $images = [];

    public function updatedImages(): void
    {
        $this->validateOnly('images.*', [
            'images.*' => ['image', 'max:'.config('moments.image_max_size')],
        ]);
    }

    public function removeImage(int $index): void
    {
        $this->authorize('create', Moment::class);
        array_splice($this->images, $index, 1);
    }

    public function save(): void
    {
        $this->authorize('create', Moment::class);

        $this->validate([
            'body' => [
                Rule::requiredIf(fn () => empty($this->images)),
                'nullable',
                'string',
                'max:10000',
            ],
            'images.*' => ['image', 'max:'.config('moments.image_max_size')],
        ]);

        $moment = Moment::create(['user_id' => auth()->id(), 'body' => $this->body ?: null]);

        foreach ($this->images as $image) {
            $disk = config('moments.image_disk');
            $moment->images()->create(['path' => $image->store('moments', $disk), 'disk' => $disk]);
        }

        $this->redirect(route('moments.index'), navigate: false);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.create-moment');
    }
}
