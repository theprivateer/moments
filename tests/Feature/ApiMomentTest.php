<?php

use App\Models\Moment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('creates a moment with body only and returns 201', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/moments', ['body' => 'Hello API'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Hello API');

    $this->assertDatabaseHas('moments', ['user_id' => $user->id, 'body' => 'Hello API']);
});

it('creates a moment with image only and returns 201', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/moments', [
            'images' => [UploadedFile::fake()->image('photo.jpg')],
        ])
        ->assertCreated()
        ->assertJsonPath('data.body', null);

    $moment = Moment::where('user_id', $user->id)->first();
    expect($moment->images()->count())->toBe(1);
});

it('creates a moment with body and multiple images and returns image urls', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->postJson('/api/moments', [
            'body' => 'With images',
            'images' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
            ],
        ])
        ->assertCreated();

    $images = $response->json('data.images');
    expect($images)->toHaveCount(2);
    expect($images[0])->toHaveKeys(['id', 'url']);
});

it('rejects a request with neither body nor image', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/moments', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('body');
});

it('rejects an invalid file type', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/moments', [
            'images' => [UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images.0');
});

it('rejects an unauthenticated request with 401', function () {
    $this->postJson('/api/moments', ['body' => 'Hello'])
        ->assertUnauthorized();
});

it('associates the moment with the token owner', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/moments', ['body' => 'Mine'])
        ->assertCreated();

    $this->assertDatabaseHas('moments', ['user_id' => $user->id, 'body' => 'Mine']);
    $this->assertDatabaseMissing('moments', ['user_id' => $other->id]);
});
