<?php

namespace Privateer\Moments\Markdown;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Privateer\Moments\Services\ExtractHashtags;
use Privateer\Moments\Support\Moments as MomentsSupport;

class HashtagInlineParser implements InlineParserInterface
{
    public function __construct(
        protected ExtractHashtags $extractHashtags,
    ) {}

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('#');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();

        $hashtag = $this->extractHashtags->matchStartingAt(
            $inlineContext->getFullMatch().$this->remainderFromCursor($cursor),
            $cursor->peek(-1),
        );

        if ($hashtag === null) {
            return false;
        }

        $cursor->advanceBy(strlen($hashtag) + 1);
        $inlineContext->getContainer()->appendChild(
            new Link(
                route(MomentsSupport::routeName('tags.show'), ['tag' => $hashtag]),
                '#'.$hashtag,
            ),
        );

        return true;
    }

    protected function remainderFromCursor(Cursor $cursor): string
    {
        $remainder = '';
        $offset = 1;

        while (($character = $cursor->peek($offset)) !== null) {
            $remainder .= $character;
            $offset++;
        }

        return $remainder;
    }
}
