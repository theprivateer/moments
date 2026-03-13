<?php

use App\Models\Moment;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
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
}; ?>
<div
    x-data="{ uploading: false }"
    x-on:livewire-upload-start="uploading = true"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    class="bg-white border border-gray-200 rounded-lg p-4 mb-8"
>
    <form wire:submit="save">
        <div class="mb-3">
            <textarea
                x-data="{
                    init() { this.resize(); this.$watch('$wire.body', () => this.resize()); },
                    resize() { this.$el.style.height = 'auto'; this.$el.style.height = this.$el.scrollHeight + 'px'; }
                }"
                @input="resize()"
                wire:model="body"
                placeholder="What's on your mind? Markdown supported. (Optional if attaching an image.)"
                style="min-height: 6rem"
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400 resize-none"
            ></textarea>
            @error('body')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if (count($images))
            <div class="mb-3 flex flex-wrap gap-2">
                @foreach ($images as $index => $image)
                    <div wire:key="pending-{{ $index }}" class="relative">
                        <img src="{{ $image->temporaryUrl() }}" alt="Pending image" class="h-20 w-20 object-cover rounded-md">
                        <button
                            type="button"
                            wire:click="removeImage({{ $index }})"
                            class="absolute -top-1 -right-1 flex items-center justify-center bg-black/60 text-white rounded-full size-5 text-xs leading-none hover:bg-black/80 cursor-pointer"
                        >×</button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mb-3">
            <input
                type="file"
                wire:model="images"
                accept="image/*"
                multiple
                class="text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
            >
            <div wire:loading wire:target="images" class="mt-1 text-xs text-gray-400">Uploading…</div>
            @error('images.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                :disabled="uploading"
                class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Post
            </button>
        </div>
    </form>
</div>
