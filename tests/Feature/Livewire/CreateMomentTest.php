<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Privateer\Moments\Models\Moment;

it('renders for an authenticated user', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('create-moment')
        ->assertOk();
});

it('creates a moment with body only', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('create-moment')
        ->set('body', 'Hello **world**')
        ->call('save');

    expect(Moment::where('user_id', $user->id)->first())
        ->body->toBe('Hello **world**');
});

it('fails validation with no body and no images', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('create-moment')
        ->set('body', '')
        ->call('save')
        ->assertHasErrors(['body']);
});

it('creates a moment with an image and no body', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('create-moment')
        ->set('images', [UploadedFile::fake()->image('photo.jpg')])
        ->call('save');

    $moment = Moment::where('user_id', $user->id)->first();
    expect($moment)->not->toBeNull();
    expect($moment->body)->toBeNull();
    expect($moment->images()->count())->toBe(1);
});

it('stores the uploaded image on the configured disk', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('create-moment')
        ->set('body', 'With image')
        ->set('images', [UploadedFile::fake()->image('photo.jpg')])
        ->call('save');

    $image = Moment::where('user_id', $user->id)->first()->images()->first();
    expect($image)->not->toBeNull();
    expect($image->sort_order)->toBe(1);
    Storage::disk('public')->assertExists($image->path);
});

it('rejects an oversized image', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $oversized = UploadedFile::fake()->image('big.jpg')->size(config('moments.image_max_size') + 1);

    Livewire::actingAs($user)
        ->test('create-moment')
        ->set('images', [$oversized])
        ->assertHasErrors(['images.*']);
});

it('removes a pending image from the array', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file1 = UploadedFile::fake()->image('a.jpg');
    $file2 = UploadedFile::fake()->image('b.jpg');

    $component = Livewire::actingAs($user)
        ->test('create-moment')
        ->set('images', [$file1, $file2]);

    $handle = $component->get('imageOrder')[0];

    $component
        ->call('removeImage', $handle)
        ->assertSet('images', fn ($images) => count($images) === 1);
});

it('persists the reordered upload order', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $fileA = UploadedFile::fake()->image('a.jpg');
    $fileB = UploadedFile::fake()->image('b.jpg');

    $component = Livewire::actingAs($user)
        ->test('create-moment')
        ->set('body', 'Ordered')
        ->set('images', [$fileA, $fileB]);

    $firstHandle = $component->get('imageOrder')[0];

    $component
        ->call('moveImage', $firstHandle, 'right')
        ->call('save');

    $moment = Moment::where('user_id', $user->id)->firstOrFail();
    $paths = $moment->images()->orderBy('sort_order')->pluck('path')->all();

    expect($paths)->toBe([$fileB->hashName('moments'), $fileA->hashName('moments')]);
});
