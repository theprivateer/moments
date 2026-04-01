@extends('moments::layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">#{{ $tag->name }}</h1>
    </div>

    @forelse ($moments as $moment)
        @include('moments::partials.moment-card', ['moment' => $moment])
    @empty
        <p class="text-center text-gray-400 py-16">No moments found for this tag.</p>
    @endforelse

    <div class="mt-6">
        {{ $moments->links('pagination::simple-tailwind') }}
    </div>
@endsection
