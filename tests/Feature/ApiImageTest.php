<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads an image and returns 201 with id and url', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->post('/api/v1/images', ['image' => UploadedFile::fake()->image('photo.jpg')], ['Accept' => 'application/json'])
        ->assertCreated();

    expect($response->json('data'))->toHaveKeys(['id', 'url']);
    $this->assertDatabaseHas('moment_images', ['id' => $response->json('data.id'), 'moment_id' => null]);
});

it('rejects a non-image file with 422', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->post('/api/v1/images', ['image' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('image');
});

it('rejects a request with no file with 422', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/images', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('image');
});

it('rejects an unauthenticated request with 401', function () {
    $this->postJson('/api/v1/images', [])
        ->assertUnauthorized();
});
