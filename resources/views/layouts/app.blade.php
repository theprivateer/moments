<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Moments</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="alternate" type="application/rss+xml" title="Moments" href="{{ route('feed') }}">
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('moments.index') }}" class="font-semibold text-lg tracking-tight">Moments</a>

            <nav class="flex items-center gap-4 text-sm">
                @auth
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
</body>
</html>
