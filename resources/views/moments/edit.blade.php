@extends('layouts.app')

@section('content')
    <div class="bg-white border border-gray-200 rounded-lg p-6 max-w-xl mx-auto">
        <h1 class="text-lg font-semibold mb-4">Edit Moment</h1>

        <form method="POST" action="{{ route('moments.update', $moment) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label for="body" class="block text-sm font-medium text-gray-700 mb-1">
                    Content
                    @if ($moment->images->isNotEmpty())
                        <span class="font-normal text-gray-400">(optional — image attached)</span>
                    @endif
                </label>
                <textarea
                    id="body"
                    name="body"
                    rows="6"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400 resize-none"
                >{{ old('body', $moment->body) }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if ($moment->images->isNotEmpty())
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Current images</p>
                    @foreach ($moment->images as $image)
                        <div class="mb-3">
                            <img src="{{ $image->url() }}" alt="Moment image" class="w-full rounded-md mb-2 object-cover max-h-48">
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="rounded">
                                Remove this image
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Add images (optional)</label>
                <input type="file" name="images[]" accept="image/*" multiple
                    class="text-sm text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                @error('images.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                    Save
                </button>
                <a href="{{ route('moments.index') }}" class="text-sm text-gray-500 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
