<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

it('shows the timeline publicly', function () {
    $this->get('/')->assertSuccessful();
});

it('lets an authenticated user create a moment', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/moments', ['body' => 'Hello **world**'])
        ->assertRedirect('/');

    $this->assertDatabaseHas('moments', [
        'user_id' => $user->id,
        'body' => 'Hello **world**',
    ]);
});

it('requires a body when no image is provided on store', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/moments', ['body' => ''])
        ->assertSessionHasErrors('body');
});

it('allows creating a moment without a body when an image is attached', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($user)
        ->post('/moments', ['images' => [$file]])
        ->assertRedirect('/');

    $moment = Moment::where('user_id', $user->id)->first();
    expect($moment->body)->toBeNull();
    expect($moment->images()->count())->toBe(1);
});

it('redirects unauthenticated users to login when storing a moment', function () {
    $this->post('/moments', ['body' => 'Should not work'])
        ->assertRedirect('/login');
});

it('stores an uploaded image on the public disk', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($user)
        ->post('/moments', [
            'body' => 'Moment with image',
            'images' => [$file],
        ])
        ->assertRedirect('/');

    $moment = Moment::where('user_id', $user->id)->first();
    $image = $moment->images()->first();
    expect($image)->not->toBeNull();
    Storage::disk('public')->assertExists($image->path);
});

it('stores multiple uploaded images', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $files = [
        UploadedFile::fake()->image('photo1.jpg'),
        UploadedFile::fake()->image('photo2.jpg'),
    ];

    $this->actingAs($user)
        ->post('/moments', [
            'body' => 'Moment with images',
            'images' => $files,
        ])
        ->assertRedirect('/');

    $moment = Moment::where('user_id', $user->id)->first();
    expect($moment->images()->count())->toBe(2);
});

it('lets a user edit their own moment', function () {
    $moment = Moment::factory()->create(['body' => 'Original']);

    $this->actingAs($moment->user)
        ->get("/moments/{$moment->id}/edit")
        ->assertSuccessful()
        ->assertSee('Original');
});

it('forbids editing another user\'s moment', function () {
    $moment = Moment::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->get("/moments/{$moment->id}/edit")
        ->assertForbidden();
});

it('lets a user delete their own moment', function () {
    $moment = Moment::factory()->create();

    $this->actingAs($moment->user)
        ->delete("/moments/{$moment->id}")
        ->assertRedirect('/');

    $this->assertDatabaseMissing('moments', ['id' => $moment->id]);
});

it('forbids deleting another user\'s moment', function () {
    $moment = Moment::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->delete("/moments/{$moment->id}")
        ->assertForbidden();
});

it('allows updating a moment without a body when a new image is uploaded', function () {
    Storage::fake('public');

    $moment = Moment::factory()->create(['body' => 'Original']);
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($moment->user)
        ->patch("/moments/{$moment->id}", ['body' => '', 'images' => [$file]])
        ->assertRedirect('/');

    expect($moment->fresh()->body)->toBeNull();
});

it('allows updating a moment without a body when an existing image is kept', function () {
    $moment = Moment::factory()->withoutBody()->create();
    MomentImage::factory()->for($moment)->create();

    $this->actingAs($moment->user)
        ->patch("/moments/{$moment->id}", ['body' => ''])
        ->assertRedirect('/');

    expect($moment->fresh()->body)->toBeNull();
});

it('requires a body on update when no image exists and none is uploaded', function () {
    $moment = Moment::factory()->create(['body' => 'Original']);

    $this->actingAs($moment->user)
        ->patch("/moments/{$moment->id}", ['body' => ''])
        ->assertSessionHasErrors('body');
});

it('requires a body on update when the existing image is being removed', function () {
    $moment = Moment::factory()->withoutBody()->create();
    $image = MomentImage::factory()->for($moment)->create();

    $this->actingAs($moment->user)
        ->patch("/moments/{$moment->id}", ['body' => '', 'remove_images' => [$image->id]])
        ->assertSessionHasErrors('body');
});

it('removes a specific image on update', function () {
    Storage::fake('public');

    $moment = Moment::factory()->create(['body' => 'Hello']);
    $imageToRemove = MomentImage::factory()->for($moment)->create(['path' => 'moments/remove.jpg']);
    $imageToKeep = MomentImage::factory()->for($moment)->create(['path' => 'moments/keep.jpg']);

    $this->actingAs($moment->user)
        ->patch("/moments/{$moment->id}", ['body' => 'Hello', 'remove_images' => [$imageToRemove->id]])
        ->assertRedirect('/');

    $this->assertDatabaseMissing('moment_images', ['id' => $imageToRemove->id]);
    $this->assertDatabaseHas('moment_images', ['id' => $imageToKeep->id]);
});

it('shows a single moment', function () {
    $moment = Moment::factory()->create(['body' => '# Hello']);

    $this->get("/moments/{$moment->id}")
        ->assertSuccessful()
        ->assertSee($moment->created_at->diffForHumans());
});

it('shows edit and delete actions to the moment author', function () {
    $user = User::factory()->create();
    $moment = Moment::factory()->for($user)->create();

    $this->actingAs($user)
        ->get("/moments/{$moment->id}")
        ->assertSuccessful()
        ->assertSee('Edit')
        ->assertSee('Delete');
});

it('paginates the timeline to 10 moments per page', function () {
    Moment::factory()->count(15)->create();

    $this->get('/')
        ->assertSuccessful()
        ->assertViewHas('moments', fn ($moments) => $moments->count() === 10);
});

it('shows the second page of moments', function () {
    Moment::factory()->count(15)->create();

    $this->get('/?page=2')
        ->assertSuccessful()
        ->assertViewHas('moments', fn ($moments) => $moments->count() === 5);
});

it('renders gallery markup and a lightbox gallery for images on the timeline', function () {
    $moment = Moment::factory()->create(['body' => 'Hello']);
    MomentImage::factory()->for($moment)->create(['alt_text' => 'Sunlight across a quiet beach']);

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('data-gallery="moment-images"', false)
        ->assertSee('data-lightbox-gallery', false)
        ->assertSee('alt="Sunlight across a quiet beach"', false)
        ->assertDontSee('data-gallery-controls', false);
});

it('renders slider controls for moments with multiple images on the timeline', function () {
    $moment = Moment::factory()->create(['body' => 'Hello']);
    MomentImage::factory()->for($moment)->create();
    MomentImage::factory()->for($moment)->create();

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('data-gallery-controls', false)
        ->assertSee('aria-label="Previous image"', false)
        ->assertSee('aria-label="Next image"', false)
        ->assertSee('aria-label="Go to image 2"', false)
        ->assertSee('id="lightbox"', false);
});

it('renders gallery markup and a lightbox gallery for images on the show page', function () {
    $moment = Moment::factory()->create(['body' => 'Hello']);
    MomentImage::factory()->for($moment)->create(['alt_text' => null]);

    $this->get("/moments/{$moment->id}")
        ->assertSuccessful()
        ->assertSee('data-gallery="moment-images"', false)
        ->assertSee('data-lightbox-gallery', false)
        ->assertSee('alt="Moment image"', false)
        ->assertSee('id="lightbox"', false);
});
