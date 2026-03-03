<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Moments</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="alternate" type="application/rss+xml" title="Moments" href="{{ route('feed') }}">
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('moments.index') }}" class="font-semibold text-lg tracking-tight">Moments</a>

            <nav class="flex items-center gap-4 text-sm">
                @auth
                    <a href="{{ route('tokens.index') }}" class="text-gray-600 hover:text-gray-900">API tokens</a>
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
        onclick="this.close()"
        class="fixed inset-0 w-full h-full max-w-none max-h-none m-0 p-0 border-0 bg-transparent backdrop:bg-black/75"
    >
        <div onclick="event.stopPropagation()" class="flex items-center justify-center w-full h-full">
            <div class="relative">
                <img id="lightbox-image" src="" alt="" class="block max-w-[90vw] max-h-[90vh] object-contain rounded-lg">
                <form method="dialog" class="absolute top-2 right-2">
                    <button type="submit" class="flex items-center justify-center bg-black/50 text-white rounded-full size-8 text-lg hover:bg-black/75 cursor-pointer">×</button>
                </form>
            </div>
        </div>
    </dialog>
</body>
</html>
