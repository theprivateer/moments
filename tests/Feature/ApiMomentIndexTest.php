<?php

use App\Models\User;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

it('rejects an unauthenticated request with 401', function () {
    $this->getJson('/api/v1/moments')
        ->assertUnauthorized();
});

it('returns an empty list when there are no moments', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/moments')
        ->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.total', 0);
});

it('returns moments in reverse-chronological order', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $oldest = Moment::factory()->create(['created_at' => now()->subMinutes(10)]);
    $middle = Moment::factory()->create(['created_at' => now()->subMinutes(5)]);
    $newest = Moment::factory()->create(['created_at' => now()]);

    $ids = $this->withToken($token)
        ->getJson('/api/v1/moments')
        ->assertOk()
        ->json('data.*.id');

    expect($ids)->toBe([$newest->id, $middle->id, $oldest->id]);
});

it('includes images for each moment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $moment = Moment::factory()->create();
    MomentImage::factory()->create(['moment_id' => $moment->id]);

    $response = $this->withToken($token)
        ->getJson('/api/v1/moments')
        ->assertOk();

    expect($response->json('data.0.images'))->toHaveCount(1);
    expect($response->json('data.0.images.0'))->toHaveKeys(['id', 'url']);
});

it('returns correct pagination metadata', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    Moment::factory()->count(25)->create();

    $this->withToken($token)
        ->getJson('/api/v1/moments')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 20)
        ->assertJsonPath('meta.total', 25)
        ->assertJsonPath('meta.last_page', 2);
});

it('returns page 2 with remaining moments', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    Moment::factory()->count(25)->create();

    $response = $this->withToken($token)
        ->getJson('/api/v1/moments?page=2')
        ->assertOk()
        ->assertJsonPath('meta.current_page', 2);

    expect($response->json('data'))->toHaveCount(5);
});
