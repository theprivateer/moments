<?php

namespace Privateer\Moments\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Privateer\Moments\Support\Moments as MomentsSupport;
use Stringable;

class GenerateMomentImageAltTextAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You write concise accessibility alt text for images. Describe only visible content, avoid "image of" or "photo of", avoid guessing context that is not visible, keep it to one plain sentence, and do not exceed 160 characters.';
    }

    public function provider(): string|array|null
    {
        return MomentsSupport::altTextProvider();
    }

    public function model(): ?string
    {
        return MomentsSupport::altTextModel();
    }
}
