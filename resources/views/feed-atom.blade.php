<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <id>{{ route('feed.atom') }}</id>
    <title>{{ config('app.name') }}</title>
    <updated>{{ ($moments->first()?->updated_at ?? now())->toAtomString() }}</updated>
    <link rel="self" href="{{ route('feed.atom') }}"/>
    <link rel="alternate" href="{{ url('/') }}"/>
    <author>
        <name>{{ $user->name }}</name>
    </author>
    @foreach ($moments as $moment)
    <entry>
        <id>{{ route('moments.show', $moment) }}</id>
        <title>{{ $moment->body ? Str::limit(strip_tags($moment->renderedBody()), 60) : 'Moment - '.$moment->created_at->format('j M Y') }}</title>
        <published>{{ $moment->created_at->toAtomString() }}</published>
        <updated>{{ $moment->updated_at->toAtomString() }}</updated>
        <link rel="alternate" href="{{ route('moments.show', $moment) }}"/>
        <content type="html"><![CDATA[
            @foreach ($moment->images as $image)<img src="{{ $image->url() }}" alt="">@endforeach
            @if ($moment->body){!! $moment->renderedBody() !!}@endif
        ]]></content>
    </entry>
    @endforeach
</feed>
