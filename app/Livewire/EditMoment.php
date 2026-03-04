<?php

namespace App\Livewire;

use App\Models\Moment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditMoment extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $momentId;

    public string $body = '';

    /** @var array<int, array{id: int, glideUrl: string}> */
    public array $existingImages = [];

    /** @var array<int, int> */
    public array $imagesToRemove = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newImages = [];

    public function mount(Moment $moment): void
    {
        $this->authorize('update', $moment);

        $moment->loadMissing('images');
        $this->momentId = $moment->id;
        $this->body = $moment->body ?? '';
        $this->existingImages = $moment->images
            ->map(fn ($img) => ['id' => $img->id, 'glideUrl' => $img->glideUrl(400)])
            ->values()
            ->all();
    }

    public function updatedNewImages(): void
    {
        $this->validateOnly('newImages.*', [
            'newImages.*' => ['image', 'max:'.config('moments.image_max_size')],
        ]);
    }

    public function removeNewImage(int $index): void
    {
        $this->authorize('update', Moment::findOrFail($this->momentId));
        array_splice($this->newImages, $index, 1);
    }

    public function save(): void
    {
        $moment = Moment::findOrFail($this->momentId);
        $this->authorize('update', $moment);

        $remaining = count($this->existingImages) - count($this->imagesToRemove);

        $this->validate([
            'body' => [
                Rule::requiredIf(fn () => $remaining <= 0 && empty($this->newImages)),
                'nullable',
                'string',
                'max:10000',
            ],
            'newImages.*' => ['image', 'max:'.config('moments.image_max_size')],
            'imagesToRemove.*' => ['integer', 'exists:moment_images,id'],
        ]);

        $toRemove = $moment->images()->whereIn('id', $this->imagesToRemove)->get();
        foreach ($toRemove as $image) {
            Storage::disk($image->disk)->delete($image->path);
            $image->delete();
        }

        foreach ($this->newImages as $image) {
            $disk = config('moments.image_disk');
            $moment->images()->create(['path' => $image->store('moments', $disk), 'disk' => $disk]);
        }

        $moment->update(['body' => $this->body ?: null]);

        $this->redirect(route('moments.index'), navigate: false);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.edit-moment');
    }
}
