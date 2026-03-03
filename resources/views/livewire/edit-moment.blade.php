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
                    <span class="font-normal text-gray-400">(optional — image attached)</span>
                @endif
            </label>
            <textarea
                id="body"
                wire:model="body"
                rows="6"
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400 resize-none"
            ></textarea>
            @error('body')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if (count($existingImages))
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Current images</p>
                <div class="flex flex-wrap gap-3">
                    @foreach ($existingImages as $image)
                        <div wire:key="existing-{{ $image['id'] }}" class="relative">
                            <img
                                src="{{ $image['glideUrl'] }}"
                                alt="Moment image"
                                class="h-24 w-24 object-cover rounded-md {{ in_array($image['id'], $imagesToRemove) ? 'opacity-40' : '' }}"
                            >
                            <label class="absolute inset-0 flex items-center justify-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model.live="imagesToRemove"
                                    value="{{ $image['id'] }}"
                                    class="sr-only"
                                >
                                @if (in_array($image['id'], $imagesToRemove))
                                    <span class="bg-red-500/80 text-white text-xs px-1.5 py-0.5 rounded">Remove</span>
                                @else
                                    <span class="bg-black/40 text-white text-xs px-1.5 py-0.5 rounded opacity-0 hover:opacity-100 transition-opacity">Remove</span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (count($newImages))
            <div class="mb-3 flex flex-wrap gap-2">
                @foreach ($newImages as $index => $image)
                    <div wire:key="new-{{ $index }}" class="relative">
                        <img src="{{ $image->temporaryUrl() }}" alt="Pending image" class="h-20 w-20 object-cover rounded-md">
                        <button
                            type="button"
                            wire:click="removeNewImage({{ $index }})"
                            class="absolute -top-1 -right-1 flex items-center justify-center bg-black/60 text-white rounded-full size-5 text-xs leading-none hover:bg-black/80 cursor-pointer"
                        >×</button>
                    </div>
                @endforeach
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
