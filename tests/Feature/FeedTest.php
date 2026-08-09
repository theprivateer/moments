<?php

use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

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
    $moment = Moment::factory()->withoutBody()->create();
    MomentImage::factory()->for($moment)->create();

    $this->get('/feed')
        ->assertSuccessful()
        ->assertSee('Moment - '.$moment->created_at->format('j M Y'), false);
});

it('escapes entities in item titles exactly once', function () {
    Moment::factory()->create(['body' => 'Tom & Jerry']);

    $content = $this->get('/feed')->assertSuccessful()->getContent();

    expect($content)->toContain('<title>Tom &amp; Jerry</title>')
        ->and($content)->not->toContain('&amp;amp;');
});

it('renders a single-line title for a multi-paragraph body', function () {
    Moment::factory()->create(['body' => "First paragraph\n\nSecond paragraph"]);

    $content = $this->get('/feed')->assertSuccessful()->getContent();

    expect($content)->toContain('<title>First paragraph Second paragraph</title>');
});
