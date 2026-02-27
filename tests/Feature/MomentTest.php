<?php

use App\Models\Moment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        ->post('/moments', ['image' => $file])
        ->assertRedirect('/');

    $moment = Moment::where('user_id', $user->id)->first();
    expect($moment->body)->toBeNull();
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
            'image' => $file,
        ])
        ->assertRedirect('/');

    $moment = Moment::where('user_id', $user->id)->first();
    expect($moment->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($moment->image_path);
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
        ->patch("/moments/{$moment->id}", ['body' => '', 'image' => $file])
        ->assertRedirect('/');

    expect($moment->fresh()->body)->toBeNull();
});

it('allows updating a moment without a body when an existing image is kept', function () {
    $moment = Moment::factory()->withoutBody()->create(['image_path' => 'images/photo.jpg', 'image_disk' => 'public']);

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
    $moment = Moment::factory()->withoutBody()->create(['image_path' => 'images/photo.jpg', 'image_disk' => 'public']);

    $this->actingAs($moment->user)
        ->patch("/moments/{$moment->id}", ['body' => '', 'remove_image' => '1'])
        ->assertSessionHasErrors('body');
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
