<?php

use App\Models\Moment;

it('returns an rss feed', function () {
    Moment::factory()->count(3)->create(['body' => 'Hello world']);

    $this->get('/feed')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('<rss version="2.0">', false)
        ->assertSee('Hello world');
});

it('limits the feed to 20 moments', function () {
    Moment::factory()->count(25)->create();

    $response = $this->get('/feed')->assertSuccessful();
    expect(substr_count($response->getContent(), '<item>'))->toBe(20);
});

it('includes image-only moments with a date-based title', function () {
    $moment = Moment::factory()->withoutBody()->create([
        'image_path' => 'images/photo.jpg',
        'image_disk' => 'public',
    ]);

    $this->get('/feed')
        ->assertSuccessful()
        ->assertSee('Moment — '.$moment->created_at->format('j M Y'), false);
});
