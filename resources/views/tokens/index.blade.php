@extends('layouts.app')

@section('content')
    @if (session('plain_text_token'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-sm font-semibold text-green-800 mb-2">Your new API token — copy it now, it won't be shown again:</p>
            <code class="block break-all font-mono text-sm text-green-900 bg-green-100 rounded px-3 py-2">{{ session('plain_text_token') }}</code>
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Create API token</h2>
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

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <h2 class="text-sm font-semibold text-gray-700 px-4 py-3 border-b border-gray-200">Your tokens</h2>
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
                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm cursor-pointer"
                        onclick="return confirm('Revoke this token?')">Revoke</button>
                </form>
            </div>
        @empty
            <p class="text-center text-gray-400 text-sm py-8">No tokens yet.</p>
        @endforelse
    </div>
@endsection
