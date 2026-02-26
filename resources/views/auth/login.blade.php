@extends('layouts.app')

@section('content')
    <div class="max-w-sm mx-auto">
        <h1 class="text-xl font-semibold text-center mb-6">Log in to Moments</h1>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                    >
                </div>

                <div class="mb-6">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded-md text-sm hover:bg-gray-700">
                    Log in
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-4">
            No account yet?
            <a href="{{ route('register') }}" class="text-gray-900 underline">Register</a>
        </p>
    </div>
@endsection
