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

        @if (count($this->orderedImagePreviews))
            <div class="mb-4">
                <p class="mb-2 text-sm font-medium text-gray-700">Image order</p>
                <div class="flex flex-wrap gap-3">
                    @foreach ($this->orderedImagePreviews as $index => $image)
                        <div wire:key="pending-{{ $image['handle'] }}" class="w-28">
                            <img src="{{ $image['temporary_url'] }}" alt="Pending image" class="h-24 w-full rounded-md object-cover">
                            <div class="mt-2 flex items-center justify-between gap-1">
                                <button
                                    type="button"
                                    wire:click="moveImage('{{ $image['handle'] }}', 'left')"
                                    @disabled($index === 0)
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
                                    aria-label="Move image left"
                                >&larr;</button>
                                <button
                                    type="button"
                                    wire:click="removeImage('{{ $image['handle'] }}')"
                                    class="inline-flex h-8 flex-1 items-center justify-center rounded-md bg-gray-900 px-2 text-xs font-medium text-white hover:bg-gray-700"
                                >Remove</button>
                                <button
                                    type="button"
                                    wire:click="moveImage('{{ $image['handle'] }}', 'right')"
                                    @disabled($index === count($this->orderedImagePreviews) - 1)
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40"
                                    aria-label="Move image right"
                                >&rarr;</button>
                            </div>
                        </div>
                    @endforeach
                </div>
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
