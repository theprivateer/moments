@extends('layouts.app')

@section('content')
    @auth
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-8">
            <form method="POST" action="{{ route('moments.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <textarea
                        name="body"
                        rows="4"
                        placeholder="What's on your mind? Markdown supported. (Optional if attaching an image.)"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400 resize-none"
                    >{{ old('body') }}</textarea>
                    @error('body')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="file" name="image" accept="image/*"
                        class="text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                        Post
                    </button>
                </div>
            </form>
        </div>
    @endauth

    @forelse ($moments as $moment)
        <article class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-xs">{{ $moment->created_at->diffForHumans() }}</span>
                </div>
                @can('update', $moment)
                    <div class="flex items-center gap-3 text-sm">
                        <a href="{{ route('moments.edit', $moment) }}" class="text-gray-500 hover:text-gray-900">Edit</a>
                        <form method="POST" action="{{ route('moments.destroy', $moment) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 cursor-pointer"
                                onclick="return confirm('Delete this moment?')">Delete</button>
                        </form>
                    </div>
                @endcan
            </div>

            @if ($moment->imageUrl())
                <img src="{{ $moment->imageUrl() }}" alt="Moment image" class="w-full rounded-md mb-3">
            @endif

            @if ($moment->body)
                <div class="prose text-gray-800">
                    {!! $moment->renderedBody() !!}
                </div>
            @endif
        </article>
    @empty
        <p class="text-center text-gray-400 py-16">No moments yet. Be the first to share something!</p>
    @endforelse

    <div class="mt-6">
        {{ $moments->links('pagination::simple-tailwind') }}
    </div>
@endsection
