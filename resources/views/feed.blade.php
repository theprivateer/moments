<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0">
    <channel>
        <title>Moments</title>
        <link>{{ url('/') }}</link>
        <description>Latest moments</description>
        <lastBuildDate>{{ $moments->isNotEmpty() ? $moments->first()->created_at->toRfc2822String() : now()->toRfc2822String() }}</lastBuildDate>
        @foreach ($moments as $moment)
        <item>
            <title>{{ $moment->body ? Str::limit(strip_tags($moment->renderedBody()), 60) : 'Moment - '.$moment->created_at->format('j M Y') }}</title>
            <link>{{ route('moments.show', $moment) }}</link>
            <guid isPermaLink="true">{{ route('moments.show', $moment) }}</guid>
            <pubDate>{{ $moment->created_at->toRfc2822String() }}</pubDate>
            <description><![CDATA[
                @if ($moment->imageUrl())
                    <img src="{{ $moment->imageUrl() }}" alt="">
                @endif
                @if ($moment->body)
                    {!! $moment->renderedBody() !!}
                @endif
            ]]></description>
        </item>
        @endforeach
    </channel>
</rss>
