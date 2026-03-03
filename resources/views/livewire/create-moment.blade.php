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
                wire:model="body"
                rows="4"
                placeholder="What's on your mind? Markdown supported. (Optional if attaching an image.)"
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
