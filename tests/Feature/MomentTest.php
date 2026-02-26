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

it('requires a body to create a moment', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/moments', ['body' => ''])
        ->assertSessionHasErrors('body');
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
