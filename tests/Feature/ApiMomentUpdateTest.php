<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Privateer\Moments\Models\Moment;
use Privateer\Moments\Models\MomentImage;

it('updates the body text of a moment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => 'Original']);

    $this->withToken($token)
        ->patchJson("/api/v1/moments/{$moment->id}", ['body' => 'Updated'])
        ->assertOk()
        ->assertJsonPath('data.body', 'Updated');

    $this->assertDatabaseHas('moments', ['id' => $moment->id, 'body' => 'Updated']);
});

it('adds pre-uploaded images to a moment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => 'Has body']);
    $image = MomentImage::factory()->create(['moment_id' => null]);

    $this->withToken($token)
        ->patchJson("/api/v1/moments/{$moment->id}", ['add_images' => [$image->id]])
        ->assertOk();

    $this->assertDatabaseHas('moment_images', ['id' => $image->id, 'moment_id' => $moment->id]);
});

it('removes existing images from a moment', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => 'Stays']);
    $image = MomentImage::factory()->create(['moment_id' => $moment->id]);

    $this->withToken($token)
        ->patchJson("/api/v1/moments/{$moment->id}", ['remove_images' => [$image->id]])
        ->assertOk();

    $this->assertDatabaseMissing('moment_images', ['id' => $image->id]);
});

it('can replace body and images simultaneously', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => 'Old body']);
    $oldImage = MomentImage::factory()->create(['moment_id' => $moment->id]);
    $newImage = MomentImage::factory()->create(['moment_id' => null]);

    $this->withToken($token)
        ->patchJson("/api/v1/moments/{$moment->id}", [
            'body' => 'New body',
            'add_images' => [$newImage->id],
            'remove_images' => [$oldImage->id],
        ])
        ->assertOk()
        ->assertJsonPath('data.body', 'New body');

    $this->assertDatabaseMissing('moment_images', ['id' => $oldImage->id]);
    $this->assertDatabaseHas('moment_images', ['id' => $newImage->id, 'moment_id' => $moment->id]);
});

it('returns 422 when removing all images and body is not provided', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => null]);
    $image = MomentImage::factory()->create(['moment_id' => $moment->id]);

    $this->withToken($token)
        ->patchJson("/api/v1/moments/{$moment->id}", ['remove_images' => [$image->id]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('body');
});

it('returns 403 when updating another users moment', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($other)->create(['body' => 'Not yours']);

    $this->withToken($token)
        ->patchJson("/api/v1/moments/{$moment->id}", ['body' => 'Hijacked'])
        ->assertForbidden();
});

it('returns 401 when unauthenticated', function () {
    $moment = Moment::factory()->create(['body' => 'Hello']);

    $this->patchJson("/api/v1/moments/{$moment->id}", ['body' => 'Hacked'])
        ->assertUnauthorized();
});

it('returns 404 for a non-existent moment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->patchJson('/api/v1/moments/99999', ['body' => 'Ghost'])
        ->assertNotFound();
});

it('cannot remove images belonging to a different moment', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => 'Mine']);
    $otherMoment = Moment::factory()->create();
    $foreignImage = MomentImage::factory()->create(['moment_id' => $otherMoment->id]);

    $this->withToken($token)
        ->patchJson("/api/v1/moments/{$moment->id}", ['remove_images' => [$foreignImage->id]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('remove_images.0');
});
