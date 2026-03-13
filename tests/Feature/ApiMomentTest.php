<?php

use App\Models\Moment;
use App\Models\MomentImage;
use App\Models\User;

it('creates a moment with body only and returns 201', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['body' => 'Hello API'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Hello API');

    $this->assertDatabaseHas('moments', ['user_id' => $user->id, 'body' => 'Hello API']);
});

it('creates a moment with pre-uploaded image IDs and returns 201', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $image = MomentImage::factory()->create(['moment_id' => null]);

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['images' => [$image->id]])
        ->assertCreated()
        ->assertJsonPath('data.body', null);

    $moment = Moment::where('user_id', $user->id)->first();
    expect($moment->images()->count())->toBe(1);
    $this->assertDatabaseHas('moment_images', ['id' => $image->id, 'moment_id' => $moment->id]);
});

it('creates a moment with body and multiple image IDs and returns image urls', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $imageA = MomentImage::factory()->create(['moment_id' => null]);
    $imageB = MomentImage::factory()->create(['moment_id' => null]);

    $response = $this->withToken($token)
        ->postJson('/api/v1/moments', [
            'body' => 'With images',
            'images' => [$imageA->id, $imageB->id],
        ])
        ->assertCreated();

    $images = $response->json('data.images');
    expect($images)->toHaveCount(2);
    expect($images[0])->toHaveKeys(['id', 'url']);
});

it('rejects a request with neither body nor images', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/moments', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('body');
});

it('rejects already-attached image IDs with 422', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $moment = Moment::factory()->create();
    $image = MomentImage::factory()->create(['moment_id' => $moment->id]);

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['images' => [$image->id]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images.0');
});

it('rejects an unauthenticated request with 401', function () {
    $this->postJson('/api/v1/moments', ['body' => 'Hello'])
        ->assertUnauthorized();
});

it('creates a moment with a custom created_at epoch', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $epoch = 1483228800; // 2017-01-01T00:00:00Z

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['body' => 'Historic post', 'created_at' => $epoch])
        ->assertCreated()
        ->assertJsonPath('data.created_at', '2017-01-01T00:00:00.000000Z');

    $this->assertDatabaseHas('moments', [
        'user_id' => $user->id,
        'body' => 'Historic post',
        'created_at' => '2017-01-01 00:00:00',
    ]);
});

it('uses the current time when created_at is omitted', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['body' => 'New post'])
        ->assertCreated();

    $moment = Moment::where('user_id', $user->id)->first();
    expect($moment->created_at->diffInSeconds(now()))->toBeLessThan(5);
});

it('associates the moment with the token owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['body' => 'Mine'])
        ->assertCreated();

    $this->assertDatabaseHas('moments', ['user_id' => $user->id, 'body' => 'Mine']);
    $this->assertDatabaseMissing('moments', ['user_id' => $other->id]);
});
