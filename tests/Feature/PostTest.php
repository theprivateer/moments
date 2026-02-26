<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('shows the timeline publicly', function () {
    $this->get('/')->assertSuccessful();
});

it('lets an authenticated user create a post', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/posts', ['body' => 'Hello **world**'])
        ->assertRedirect('/');

    $this->assertDatabaseHas('posts', [
        'user_id' => $user->id,
        'body' => 'Hello **world**',
    ]);
});

it('requires a body to create a post', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/posts', ['body' => ''])
        ->assertSessionHasErrors('body');
});

it('redirects unauthenticated users to login when storing a post', function () {
    $this->post('/posts', ['body' => 'Should not work'])
        ->assertRedirect('/login');
});

it('stores an uploaded image on the public disk', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($user)
        ->post('/posts', [
            'body' => 'Post with image',
            'image' => $file,
        ])
        ->assertRedirect('/');

    $post = Post::where('user_id', $user->id)->first();
    expect($post->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($post->image_path);
});

it('lets a user edit their own post', function () {
    $post = Post::factory()->create(['body' => 'Original']);

    $this->actingAs($post->user)
        ->get("/posts/{$post->id}/edit")
        ->assertSuccessful()
        ->assertSee('Original');
});

it('forbids editing another user\'s post', function () {
    $post = Post::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->get("/posts/{$post->id}/edit")
        ->assertForbidden();
});

it('lets a user delete their own post', function () {
    $post = Post::factory()->create();

    $this->actingAs($post->user)
        ->delete("/posts/{$post->id}")
        ->assertRedirect('/');

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('forbids deleting another user\'s post', function () {
    $post = Post::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($other)
        ->delete("/posts/{$post->id}")
        ->assertForbidden();
});
