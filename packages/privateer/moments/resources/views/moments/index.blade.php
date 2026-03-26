@extends('moments::layouts.app')

@section('content')
    @auth
        <livewire:create-moment />
    @endauth

    @if ($intro)
        <div class="prose text-gray-800 mb-6">
            {!! $intro !!}
        </div>
    @endif

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
                            <button type="submit" class="text-red-500 hover:text-red-700 cursor-pointer" onclick="return confirm('Delete this moment?')">Delete</button>
                        </form>
                    </div>
                @endcan
            </div>

            @if ($moment->body)
                <div class="prose text-gray-800">
                    {!! $moment->renderedBody() !!}
                </div>
            @endif

            @foreach ($moment->images as $image)
                <button
                    type="button"
                    onclick="openLightbox('{{ $image->glideUrl(1200) }}')"
                    class="block w-full cursor-zoom-in"
                >
                    <img src="{{ $image->glideUrl(800) }}" alt="Moment image" class="w-full rounded-md mb-3 object-cover aspect-square">
                </button>
            @endforeach
        </article>
    @empty
        <p class="text-center text-gray-400 py-16">No moments yet. Be the first to share something!</p>
    @endforelse

    <div class="mt-6">
        {{ $moments->links('pagination::simple-tailwind') }}
    </div>
@endsection
