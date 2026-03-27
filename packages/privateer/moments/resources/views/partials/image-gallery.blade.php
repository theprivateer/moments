@php
    $galleryImages = $moment->images
        ->values()
        ->map(fn ($image, $index) => [
            'id' => $image->id,
            'inlineUrl' => $image->glideUrl(800),
            'lightboxUrl' => $image->glideUrl(1200),
            'alt' => 'Moment image '.($index + 1),
        ])
        ->all();

    $imageCount = count($galleryImages);
@endphp

@if ($imageCount > 0)
    <div
        x-data="momentGallery({ images: {{ Js::from($galleryImages) }} })"
        data-gallery="moment-images"
        class="mt-3"
    >
        <div class="relative overflow-hidden rounded-md">
            @foreach ($galleryImages as $index => $image)
                <button
                    type="button"
                    x-show="currentIndex === {{ $index }}"
                    @click="open({{ $index }})"
                    class="block w-full cursor-zoom-in"
                    @if ($index > 0)
                        style="display: none;"
                    @endif
                >
                    <img
                        src="{{ $image['inlineUrl'] }}"
                        alt="{{ $image['alt'] }}"
                        class="w-full object-cover aspect-square"
                    >
                </button>
            @endforeach

            @if ($imageCount > 1)
                <div class="pointer-events-none absolute inset-x-0 top-1/2 flex -translate-y-1/2 items-center justify-between px-3">
                    <button
                        type="button"
                        @click="prev()"
                        :disabled="!canPrev()"
                        aria-label="Previous image"
                        class="pointer-events-auto inline-flex size-10 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/75 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <span aria-hidden="true">&larr;</span>
                    </button>
                    <button
                        type="button"
                        @click="next()"
                        :disabled="!canNext()"
                        aria-label="Next image"
                        class="pointer-events-auto inline-flex size-10 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/75 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <span aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            @endif
        </div>

        @if ($imageCount > 1)
            <div data-gallery-controls class="mt-3 flex items-center justify-between gap-3">
                <p aria-live="polite" class="text-sm text-gray-500" x-text="statusLabel()">
                    Image 1 of {{ $imageCount }}
                </p>

                <div data-gallery-dots class="flex items-center gap-2">
                    @foreach ($galleryImages as $index => $image)
                        <button
                            type="button"
                            @click="goTo({{ $index }})"
                            :aria-current="currentIndex === {{ $index }} ? 'true' : 'false'"
                            aria-label="Go to image {{ $index + 1 }}"
                            class="size-3 rounded-full border border-gray-400 transition"
                            :class="currentIndex === {{ $index }} ? 'bg-gray-900 border-gray-900' : 'bg-white hover:bg-gray-200'"
                        ></button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
