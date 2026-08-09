<?php

use Privateer\Moments\Markdown\HashtagMarkdownRenderer;
use Privateer\Moments\MomentsServiceProvider;

it('loads the moments package service provider', function () {
    expect(app()->providerIsLoaded(MomentsServiceProvider::class))->toBeTrue();
});

it('registers the package routes through the host app configuration', function () {
    expect(route('moments.index'))->toBe(url('/'));
    expect(route('api.v1.moments.index'))->toBe(url('/api/v1/moments'));
});

it('shares one markdown renderer instance across resolutions', function () {
    expect(app(HashtagMarkdownRenderer::class))
        ->toBe(app(HashtagMarkdownRenderer::class));
});
