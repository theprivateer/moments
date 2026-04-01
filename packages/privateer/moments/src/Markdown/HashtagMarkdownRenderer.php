<?php

namespace Privateer\Moments\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;

class HashtagMarkdownRenderer
{
    protected MarkdownConverter $converter;

    public function __construct(HashtagInlineParser $hashtagInlineParser)
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addInlineParser($hashtagInlineParser);

        $this->converter = new MarkdownConverter($environment);
    }

    public function render(?string $markdown): ?string
    {
        if ($markdown === null) {
            return null;
        }

        return (string) $this->converter->convert($markdown);
    }
}
