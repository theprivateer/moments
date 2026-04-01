<?php

use Privateer\Moments\Models\Moment;
use Privateer\Moments\Support\Hashtags;
use Spatie\Tags\Tag;

it('backfills hashtags for existing moments', function () {
    $firstMoment = Moment::factory()->create(['body' => 'Hello #laravel']);
    $secondMoment = Moment::factory()->create(['body' => 'Hello #php']);

    $firstMoment->syncTagsWithType(['stale'], Hashtags::TYPE);
    $secondMoment->syncTagsWithType(['old'], Hashtags::TYPE);

    $this->artisan('moments:backfill-tags')
        ->expectsOutputToContain('Backfilling hashtags for 2 moments...')
        ->expectsOutputToContain('Processed 2 moments.')
        ->assertSuccessful();

    expect($firstMoment->fresh()->tags->pluck('name')->all())->toBe(['laravel']);
    expect($secondMoment->fresh()->tags->pluck('name')->all())->toBe(['php']);
    expect(Tag::findFromString('stale', Hashtags::TYPE))->not->toBeNull();
});
