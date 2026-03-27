<?php

use Privateer\Moments\Agents\GenerateMomentImageAltTextAgent;
use Privateer\Moments\Models\MomentImage;

it('processes only images without alt text by default', function () {
    $missingAltText = MomentImage::factory()->create(['alt_text' => null]);
    $existingAltText = MomentImage::factory()->create(['alt_text' => 'Existing alt text']);

    GenerateMomentImageAltTextAgent::fake([
        'New alt text for the missing image',
    ]);

    $this->artisan('moments:generate-alt-text')
        ->expectsOutputToContain('Generating alt text for 1 image(s).')
        ->expectsOutputToContain('Processed 1 image(s).')
        ->expectsOutputToContain('Skipped 1 image(s).')
        ->expectsOutputToContain('Failed 0 image(s).')
        ->assertSuccessful();

    expect($missingAltText->fresh()->alt_text)->toBe('New alt text for the missing image');
    expect($existingAltText->fresh()->alt_text)->toBe('Existing alt text');
});

it('reprocesses all images when force is enabled', function () {
    $firstImage = MomentImage::factory()->create(['alt_text' => 'First alt text']);
    $secondImage = MomentImage::factory()->create(['alt_text' => null]);

    GenerateMomentImageAltTextAgent::fake([
        'Updated alt text for the first image',
        'Updated alt text for the second image',
    ]);

    $this->artisan('moments:generate-alt-text', ['--force' => true])
        ->expectsOutputToContain('Generating alt text for 2 image(s).')
        ->expectsOutputToContain('Processed 2 image(s).')
        ->expectsOutputToContain('Skipped 0 image(s).')
        ->expectsOutputToContain('Failed 0 image(s).')
        ->assertSuccessful();

    expect($firstImage->fresh()->alt_text)->toBe('Updated alt text for the first image');
    expect($secondImage->fresh()->alt_text)->toBe('Updated alt text for the second image');
});
