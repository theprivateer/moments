<?php

use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

it('returns an atom feed', function () {
    Moment::factory()->count(3)->create(['body' => 'Hello world']);

    $this->get('/feed/atom')
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/atom+xml; charset=UTF-8')
        ->assertSee('<feed xmlns="http://www.w3.org/2005/Atom">', false)
        ->assertSee('Hello world');
});

it('limits the atom feed to 20 moments', function () {
    Moment::factory()->count(25)->create();

    $response = $this->get('/feed/atom')->assertSuccessful();
    expect(substr_count($response->getContent(), '<entry>'))->toBe(20);
});

it('includes image-only moments with a date-based title in atom feed', function () {
    $moment = Moment::factory()->withoutBody()->create();
    MomentImage::factory()->for($moment)->create();

    $this->get('/feed/atom')
        ->assertSuccessful()
        ->assertSee('Moment - '.$moment->created_at->format('j M Y'), false);
});
