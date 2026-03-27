<?php

namespace Privateer\Moments\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Image;
use Privateer\Moments\Agents\GenerateMomentImageAltTextAgent;
use RuntimeException;

class GenerateMomentImageAltText
{
    public function __construct(protected GenerateMomentImageAltTextAgent $agent) {}

    public function generate(Model $momentImage): string
    {
        $response = $this->agent->prompt(
            prompt: 'Generate alt text for this image.',
            attachments: [Image::fromStorage($momentImage->path, $momentImage->disk)],
        );

        $altText = Str::of($response->text)
            ->squish()
            ->trim(" \t\n\r\0\x0B\"'")
            ->limit(160, '');

        if ($altText->isEmpty()) {
            throw new RuntimeException('The AI provider returned empty alt text.');
        }

        return (string) $altText;
    }

    public function store(Model $momentImage): string
    {
        $altText = $this->generate($momentImage);

        $momentImage->forceFill([
            'alt_text' => $altText,
        ])->save();

        return $altText;
    }
}
