<?php

use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

it('returns a json feed', function () {
    Moment::factory()->count(3)->create(['body' => 'Hello world']);

    $this->get('/feed/json')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/feed+json')
        ->assertJsonPath('version', 'https://jsonfeed.org/version/1.1')
        ->assertJsonPath('title', config('app.name'));
});

it('limits the json feed to 20 items', function () {
    Moment::factory()->count(25)->create();

    $response = $this->get('/feed/json')->assertSuccessful();
    expect(count($response->json('items')))->toBe(20);
});

it('includes image-only moments with a date-based title in json feed', function () {
    $moment = Moment::factory()->withoutBody()->create();
    MomentImage::factory()->for($moment)->create();

    $this->get('/feed/json')
        ->assertSuccessful()
        ->assertJsonFragment(['title' => 'Moment - '.$moment->created_at->format('j M Y')]);
});

it('includes image field for moments with images in json feed', function () {
    $moment = Moment::factory()->create(['body' => 'Has an image']);
    MomentImage::factory()->for($moment)->create();

    $response = $this->get('/feed/json')->assertSuccessful();
    $item = collect($response->json('items'))->firstWhere('id', route('moments.show', $moment));
    expect($item)->toHaveKey('image');
});

it('returns decoded entities in json feed titles', function () {
    Moment::factory()->create(['body' => 'Tom & Jerry']);

    $this->get('/feed/json')
        ->assertSuccessful()
        ->assertJsonPath('items.0.title', 'Tom & Jerry');
});
