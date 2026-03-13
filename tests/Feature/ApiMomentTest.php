<?php

use App\Jobs\PublishMomentToThreads;
use App\Models\Moment;
use App\Models\MomentImage;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

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

it('dispatches a Threads publish job when integration is enabled and override is omitted', function () {
    Queue::fake();

    config([
        'moments.threads.enabled' => true,
        'moments.threads.default_cross_post' => true,
        'moments.threads.user_id' => '12345',
        'moments.threads.access_token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['body' => 'Cross-post me'])
        ->assertCreated();

    Queue::assertPushed(PublishMomentToThreads::class, 1);
    $this->assertDatabaseHas('moments', [
        'user_id' => $user->id,
        'threads_status' => 'pending',
    ]);
});

it('does not dispatch a Threads publish job when request opts out', function () {
    Queue::fake();

    config([
        'moments.threads.enabled' => true,
        'moments.threads.default_cross_post' => true,
        'moments.threads.user_id' => '12345',
        'moments.threads.access_token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/moments', [
            'body' => 'Do not cross-post',
            'cross_post_to_threads' => false,
        ])
        ->assertCreated();

    Queue::assertNotPushed(PublishMomentToThreads::class);
    $this->assertDatabaseHas('moments', [
        'user_id' => $user->id,
        'threads_status' => 'skipped',
    ]);
});

it('marks image-only moments as skipped and does not enqueue Threads publishing', function () {
    Queue::fake();

    config([
        'moments.threads.enabled' => true,
        'moments.threads.default_cross_post' => true,
        'moments.threads.user_id' => '12345',
        'moments.threads.access_token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $image = MomentImage::factory()->create(['moment_id' => null]);

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['images' => [$image->id]])
        ->assertCreated();

    Queue::assertNotPushed(PublishMomentToThreads::class);
    $this->assertDatabaseHas('moments', [
        'user_id' => $user->id,
        'threads_status' => 'skipped',
    ]);
});
