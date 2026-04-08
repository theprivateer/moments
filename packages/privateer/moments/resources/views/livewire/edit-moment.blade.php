<div
    x-data="{ uploading: false }"
    x-on:livewire-upload-start="uploading = true"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    class="bg-white border border-gray-200 rounded-lg p-6 max-w-xl mx-auto"
>
    <h1 class="text-lg font-semibold mb-4">Edit Moment</h1>

    <form wire:submit="save">
        <div class="mb-4">
            <label for="body" class="block text-sm font-medium text-gray-700 mb-1">
                Content
                @if (count($existingImages) > count($imagesToRemove) || count($newImages))
                    <span class="font-normal text-gray-400">(optional - image attached)</span>
                @endif
            </label>
            <textarea
                id="body"
                x-data="{
                    init() { this.resize(); this.$watch('$wire.body', () => this.resize()); },
                    resize() { this.$el.style.height = 'auto'; this.$el.style.height = this.$el.scrollHeight + 'px'; }
                }"
                @input="resize()"
                wire:model="body"
                style="min-height: 8rem"
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400 resize-none"
            ></textarea>
            @error('body')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if (count($this->orderedImageItems))
            <div class="mb-4">
                <p class="mb-2 text-sm font-medium text-gray-700">Image order</p>
                <div class="flex flex-wrap gap-3">
                    @foreach ($this->orderedImageItems as $index => $image)
                        <div wire:key="ordered-{{ $image['token'] }}" class="w-28">
                            <div class="relative">
                                <img
                                    src="{{ $image['preview_url'] }}"
                                    alt="Moment image"
                                    @class(['h-24 w-full rounded-md object-cover', 'opacity-40' => ($image['is_removed'] ?? false)])
                                >
                                @if (($image['is_removed'] ?? false))
                                    <span class="absolute inset-x-2 bottom-2 rounded bg-red-500/85 px-2 py-1 text-center text-xs font-medium text-white">
                                        Will be removed
                                    </span>
                                @endif
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-1">
                                <button
                                    type="button"
                                    wire:click="moveImage('{{ $image['token'] }}', 'left')"
                                    @disabled($index === 0)
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
                                    aria-label="Move image left"
                                >&larr;</button>
                                @if ($image['type'] === 'existing')
                                    <button
                                        type="button"
                                        wire:click="toggleImageRemoval({{ $image['id'] }})"
                                        @class([
                                            'inline-flex h-8 flex-1 items-center justify-center rounded-md px-2 text-xs font-medium',
                                            'bg-red-600 text-white hover:bg-red-500' => $image['is_removed'],
                                            'bg-gray-900 text-white hover:bg-gray-700' => ! $image['is_removed'],
                                        ])"
                                    >
                                        {{ $image['is_removed'] ? 'Keep' : 'Remove' }}
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="removeNewImage('{{ $image['handle'] }}')"
                                        class="inline-flex h-8 flex-1 items-center justify-center rounded-md bg-gray-900 px-2 text-xs font-medium text-white hover:bg-gray-700"
                                    >Remove</button>
                                @endif
                                <button
                                    type="button"
                                    wire:click="moveImage('{{ $image['token'] }}', 'right')"
                                    @disabled($index === count($this->orderedImageItems) - 1)
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
                                    aria-label="Move image right"
                                >&rarr;</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Add images (optional)</label>
            <input
                type="file"
                wire:model="newImages"
                accept="image/*"
                multiple
                class="text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
            >
            <div wire:loading wire:target="newImages" class="mt-1 text-xs text-gray-400">Uploading…</div>
            @error('newImages.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                :disabled="uploading"
                class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Save
            </button>
            <a href="{{ route('moments.index') }}" class="text-sm text-gray-500 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</div>
