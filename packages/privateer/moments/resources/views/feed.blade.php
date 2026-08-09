<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0">
    <channel>
        <title>Moments</title>
        <link>{{ route('moments.index') }}</link>
        <description>Latest moments</description>
        <lastBuildDate>{{ $moments->isNotEmpty() ? $moments->first()->created_at->toRfc2822String() : now()->toRfc2822String() }}</lastBuildDate>
        @foreach ($moments as $moment)
        <item>
            <title>{{ $moment->feedTitle() }}</title>
            <link>{{ route('moments.show', $moment) }}</link>
            <guid isPermaLink="true">{{ route('moments.show', $moment) }}</guid>
            <pubDate>{{ $moment->created_at->toRfc2822String() }}</pubDate>
            <description><![CDATA[
                @foreach ($moment->images as $image)
                    <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: 'Moment image' }}">
                @endforeach
                @if ($moment->body)
                    {!! $moment->renderedBody() !!}
                @endif
            ]]></description>
        </item>
        @endforeach
    </channel>
</rss>
