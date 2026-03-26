<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

it('deletes a moment and returns 204', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => 'To be deleted']);

    $this->withToken($token)
        ->deleteJson("/api/v1/moments/{$moment->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('moments', ['id' => $moment->id]);
});

it('deletes associated image files from storage', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => 'Has images']);
    $image = MomentImage::factory()->create(['moment_id' => $moment->id, 'disk' => 'public', 'path' => 'moments/test.jpg']);

    Storage::disk('public')->put('moments/test.jpg', 'fake content');

    $this->withToken($token)
        ->deleteJson("/api/v1/moments/{$moment->id}")
        ->assertNoContent();

    Storage::disk('public')->assertMissing('moments/test.jpg');
    $this->assertDatabaseMissing('moment_images', ['id' => $image->id]);
});

it('returns 401 when unauthenticated', function () {
    $moment = Moment::factory()->create(['body' => 'Hello']);

    $this->deleteJson("/api/v1/moments/{$moment->id}")
        ->assertUnauthorized();
});

it('returns 403 when deleting another users moment', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($other)->create(['body' => 'Not yours']);

    $this->withToken($token)
        ->deleteJson("/api/v1/moments/{$moment->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('moments', ['id' => $moment->id]);
});

it('returns 404 for a non-existent moment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->deleteJson('/api/v1/moments/99999')
        ->assertNotFound();
});
