@extends('layouts.app')

@section('content')
    <div class="bg-white border border-gray-200 rounded-lg p-6 max-w-xl mx-auto">
        <h1 class="text-lg font-semibold mb-4">Edit Post</h1>

        <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                <textarea
                    id="body"
                    name="body"
                    rows="6"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400 resize-none"
                >{{ old('body', $post->body) }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if ($post->imageUrl())
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Current image</p>
                    <img src="{{ $post->imageUrl() }}" alt="Current image" class="w-full rounded-md mb-2 object-cover max-h-48">
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="rounded">
                        Remove image
                    </label>
                </div>
            @endif

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">New image (optional)</label>
                <input type="file" name="image" accept="image/*"
                    class="text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                @error('image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                    Save
                </button>
                <a href="{{ route('posts.index') }}" class="text-sm text-gray-500 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
