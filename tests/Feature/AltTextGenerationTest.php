<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\StoredImage;
use Privateer\Moments\Agents\GenerateMomentImageAltTextAgent;
use Privateer\Moments\Jobs\GenerateMomentImageAltText as GenerateMomentImageAltTextJob;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;
use Privateer\Moments\Services\GenerateMomentImageAltText as GenerateMomentImageAltTextService;

it('stores generated alt text on a moment image', function () {
    $image = MomentImage::factory()->create([
        'path' => 'moments/harbour.jpg',
        'disk' => 'public',
        'alt_text' => null,
    ]);

    GenerateMomentImageAltTextAgent::fake(['Small sailboat on calm blue water']);

    app(GenerateMomentImageAltTextJob::class, ['momentImageId' => $image->id])
        ->handle(app(GenerateMomentImageAltTextService::class));

    expect($image->fresh()->alt_text)->toBe('Small sailboat on calm blue water');

    GenerateMomentImageAltTextAgent::assertPrompted(function ($prompt) {
        expect($prompt->attachments)->toHaveCount(1);
        expect($prompt->attachments->first())->toBeInstanceOf(StoredImage::class);

        return $prompt->prompt === 'Generate alt text for this image.';
    });
});

it('dispatches alt text generation for api image uploads when enabled', function () {
    Queue::fake();
    Storage::fake('public');
    config()->set('moments.alt_text.enabled', true);

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->post('/api/v1/images', ['image' => UploadedFile::fake()->image('photo.jpg')], ['Accept' => 'application/json'])
        ->assertCreated();

    Queue::assertPushed(GenerateMomentImageAltTextJob::class, function ($job) use ($response) {
        return $job->momentImageId === $response->json('data.id');
    });
});

it('does not dispatch alt text generation for api image uploads when disabled', function () {
    Queue::fake();
    Storage::fake('public');
    config()->set('moments.alt_text.enabled', false);

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->post('/api/v1/images', ['image' => UploadedFile::fake()->image('photo.jpg')], ['Accept' => 'application/json'])
        ->assertCreated();

    Queue::assertNothingPushed();
});

it('dispatches alt text generation for uploaded images when creating a moment', function () {
    Queue::fake();
    Storage::fake('public');
    config()->set('moments.alt_text.enabled', true);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/moments', [
            'body' => 'With image',
            'images' => [UploadedFile::fake()->image('photo.jpg')],
        ])
        ->assertRedirect('/');

    Queue::assertPushed(GenerateMomentImageAltTextJob::class, 1);
});

it('dispatches alt text generation for newly uploaded images when updating a moment', function () {
    Queue::fake();
    Storage::fake('public');
    config()->set('moments.alt_text.enabled', true);

    $moment = Moment::factory()->create(['body' => 'Original']);

    $this->actingAs($moment->user)
        ->patch("/moments/{$moment->id}", [
            'body' => 'Updated',
            'images' => [UploadedFile::fake()->image('updated.jpg')],
        ])
        ->assertRedirect('/');

    Queue::assertPushed(GenerateMomentImageAltTextJob::class, 1);
});
