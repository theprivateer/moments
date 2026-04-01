<?php

namespace Privateer\Moments\Services;

use Privateer\Moments\Support\Hashtags;

class SyncMomentTags
{
    public function __construct(
        protected ExtractHashtags $extractHashtags,
    ) {}

    public function sync(object $moment): void
    {
        $moment->syncTagsWithType(
            $this->extractHashtags->extract($moment->body),
            Hashtags::TYPE,
        );
    }
}
