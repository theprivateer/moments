<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Moments') }}</title>
    @if (config('moments.use_vite_assets', true))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }}" href="{{ route('feed') }}">
    <link rel="alternate" type="application/atom+xml" title="{{ config('app.name') }}" href="{{ route('feed.atom') }}">
    <link rel="alternate" type="application/feed+json" title="{{ config('app.name') }}" href="{{ route('feed.json') }}">
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('moments.index') }}" class="flex items-center gap-2 font-semibold text-lg tracking-tight">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-fading-icon lucide-clock-fading"><path d="M12 2a10 10 0 0 1 7.38 16.75"/><path d="M12 6v6l4 2"/><path d="M2.5 8.875a10 10 0 0 0-.5 3"/><path d="M2.83 16a10 10 0 0 0 2.43 3.4"/><path d="M4.636 5.235a10 10 0 0 1 .891-.857"/><path d="M8.644 21.42a10 10 0 0 0 7.631-.38"/></svg>
                <span>{{ config('app.name', 'Moments') }}</span>
            </a>

            <nav class="flex items-center gap-4 text-sm">
                @auth
                    <a href="{{ route('account.show') }}" class="text-gray-600 hover:text-gray-900">Account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900 cursor-pointer">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Log in</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 py-8">
        @yield('content')
    </main>
    @livewireScripts
    <dialog
        id="lightbox"
        x-data
        @click.self="$store.momentsLightbox.close()"
        @close="$store.momentsLightbox.reset()"
        @keydown.window.escape.prevent="$store.momentsLightbox.isOpen && $store.momentsLightbox.close()"
        @keydown.window.arrow-left.prevent="$store.momentsLightbox.isOpen && $store.momentsLightbox.prev()"
        @keydown.window.arrow-right.prevent="$store.momentsLightbox.isOpen && $store.momentsLightbox.next()"
        onclick="this.close()"
        class="fixed inset-0 w-full h-full max-w-none max-h-none m-0 p-0 border-0 bg-transparent backdrop:bg-black/75"
    >
        <div onclick="event.stopPropagation()" class="flex items-center justify-center w-full h-full">
            <div data-lightbox-gallery class="relative flex max-w-[92vw] flex-col items-center">
                <template x-if="$store.momentsLightbox.currentImage()">
                    <img
                        :src="$store.momentsLightbox.currentImage().lightboxUrl"
                        :alt="$store.momentsLightbox.currentImage().alt"
                        class="block max-h-[82vh] max-w-[92vw] rounded-lg object-contain"
                    >
                </template>

                <template x-if="$store.momentsLightbox.hasMultiple()">
                    <div class="pointer-events-none absolute inset-x-0 top-1/2 flex -translate-y-1/2 items-center justify-between px-3">
                        <button
                            type="button"
                            @click="$store.momentsLightbox.prev()"
                            :disabled="!$store.momentsLightbox.canPrev()"
                            aria-label="Previous image"
                            class="pointer-events-auto inline-flex size-10 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/75 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <span aria-hidden="true">&larr;</span>
                        </button>
                        <button
                            type="button"
                            @click="$store.momentsLightbox.next()"
                            :disabled="!$store.momentsLightbox.canNext()"
                            aria-label="Next image"
                            class="pointer-events-auto inline-flex size-10 items-center justify-center rounded-full bg-black/60 text-white transition hover:bg-black/75 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <span aria-hidden="true">&rarr;</span>
                        </button>
                    </div>
                </template>

                <div x-show="$store.momentsLightbox.hasMultiple()" class="mt-4 flex w-full max-w-xl items-center justify-between gap-3 px-3">
                    <p aria-live="polite" class="text-sm text-white/80" x-text="$store.momentsLightbox.statusLabel()"></p>

                    <div class="flex items-center gap-2">
                        <template x-for="(image, index) in $store.momentsLightbox.images" :key="image.id">
                            <button
                                type="button"
                                @click="$store.momentsLightbox.goTo(index)"
                                :aria-current="$store.momentsLightbox.currentIndex === index ? 'true' : 'false'"
                                :aria-label="`Go to image ${index + 1}`"
                                class="size-3 rounded-full border border-white/70 transition"
                                :class="$store.momentsLightbox.currentIndex === index ? 'bg-white border-white' : 'bg-transparent hover:bg-white/40'"
                            ></button>
                        </template>
                    </div>
                </div>

                <button
                    type="button"
                    @click="$store.momentsLightbox.close()"
                    aria-label="Close lightbox"
                    class="absolute right-2 top-2 flex size-8 items-center justify-center rounded-full bg-black/50 text-lg text-white hover:bg-black/75 cursor-pointer"
                >×</button>
            </div>
        </div>
    </dialog>
</body>
</html>
