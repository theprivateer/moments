@extends('moments::layouts.app')

@section('content')
    <h1 class="text-xl font-semibold text-gray-900 mb-6">Account</h1>

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Profile</h2>

        @if (session('profile_updated'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-green-800">Profile updated successfully.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('account.profile') }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                Save
            </button>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Change password</h2>

        @if (session('password_updated'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-green-800">Password updated successfully.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('account.password') }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                >
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                >
            </div>

            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                Update password
            </button>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">API tokens</h2>

        @if (session('plain_text_token'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-sm font-semibold text-green-800 mb-2">Your new API token - copy it now, it won't be shown again:</p>
                <code class="block break-all font-mono text-sm text-green-900 bg-green-100 rounded px-3 py-2">{{ session('plain_text_token') }}</code>
            </div>
        @endif

        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Create API token</h3>
            <form method="POST" action="{{ route('tokens.store') }}" class="flex gap-3">
                @csrf
                <div class="flex-1">
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Token name"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                    Create
                </button>
            </form>
        </div>

        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide px-4 py-3 border-b border-gray-200">Your tokens</h3>
            @forelse ($tokens as $token)
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 last:border-b-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $token->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Created {{ $token->created_at->diffForHumans() }}
                            &middot;
                            Last used: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'never' }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('tokens.destroy', $token) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm cursor-pointer" onclick="return confirm('Revoke this token?')">Revoke</button>
                    </form>
                </div>
            @empty
                <p class="text-center text-gray-400 text-sm py-8">No tokens yet.</p>
            @endforelse
        </div>
    </div>
@endsection
