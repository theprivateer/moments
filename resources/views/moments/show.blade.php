@extends('layouts.app')

@section('content')
    <article class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-xs">{{ $moment->created_at->diffForHumans() }}</span>
            @can('update', $moment)
                <div class="flex items-center gap-3 text-sm">
                    <a href="{{ route('moments.edit', $moment) }}" class="text-gray-500 hover:text-gray-900">Edit</a>
                    <form method="POST" action="{{ route('moments.destroy', $moment) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700"
                            onclick="return confirm('Delete this moment?')">Delete</button>
                    </form>
                </div>
            @endcan
        </div>

        @if ($moment->imageUrl())
            <img src="{{ $moment->imageUrl() }}" alt="Moment image" class="w-full rounded-md mb-3 object-cover max-h-96">
        @endif

        @if ($moment->body)
            <div class="prose prose-sm text-gray-800">
                {!! $moment->renderedBody() !!}
            </div>
        @endif
    </article>
@endsection
