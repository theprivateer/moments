@extends('layouts.app')

@section('content')
    @auth
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-8">
            <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <textarea
                        name="body"
                        rows="4"
                        placeholder="What's on your mind? Markdown supported."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-hidden focus:ring-2 focus:ring-gray-400 resize-none"
                    >{{ old('body') }}</textarea>
                    @error('body')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between gap-4">
                    <label class="text-sm text-gray-500 cursor-pointer">
                        <input type="file" name="image" accept="image/*" class="hidden" id="image-upload">
                        <span id="image-label" class="hover:text-gray-700">Attach image</span>
                    </label>
                    @error('image')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                        Post
                    </button>
                </div>
            </form>
        </div>
    @endauth

    @forelse ($posts as $post)
        <article class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-xs">{{ $post->created_at->diffForHumans() }}</span>
                </div>
                @can('update', $post)
                    <div class="flex items-center gap-3 text-sm">
                        <a href="{{ route('posts.edit', $post) }}" class="text-gray-500 hover:text-gray-900">Edit</a>
                        <form method="POST" action="{{ route('posts.destroy', $post) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700"
                                onclick="return confirm('Delete this post?')">Delete</button>
                        </form>
                    </div>
                @endcan
            </div>

            @if ($post->imageUrl())
                <img src="{{ $post->imageUrl() }}" alt="Post image" class="w-full rounded-md mb-3 object-cover max-h-96">
            @endif

            <div class="prose prose-sm text-gray-800">
                {!! $post->renderedBody() !!}
            </div>
        </article>
    @empty
        <p class="text-center text-gray-400 py-16">No posts yet. Be the first to share something!</p>
    @endforelse
@endsection

@push('scripts')
<script>
    document.getElementById('image-upload')?.addEventListener('change', function () {
        const label = document.getElementById('image-label');
        label.textContent = this.files[0] ? this.files[0].name : 'Attach image';
    });
</script>
@endpush
