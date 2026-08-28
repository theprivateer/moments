<?php

use App\Models\User;
use Privateer\Moments\Models\Moment;

it('allows a read-only token to list moments', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Reader', ['moments:read'])->plainTextToken;

    Moment::factory()->create();

    $this->withToken($token)
        ->getJson('/api/v1/moments')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('prevents a read-only token from performing write operations', function (string $operation) {
    $user = User::factory()->create();
    $token = $user->createToken('Reader', ['moments:read'])->plainTextToken;
    $moment = Moment::factory()->for($user)->create(['body' => 'Unchanged']);

    $response = match ($operation) {
        'upload an image' => $this->withToken($token)->postJson('/api/v1/images'),
        'create a moment' => $this->withToken($token)->postJson('/api/v1/moments', ['body' => 'Blocked']),
        'update a moment' => $this->withToken($token)->patchJson("/api/v1/moments/{$moment->id}", ['body' => 'Blocked']),
        'delete a moment' => $this->withToken($token)->deleteJson("/api/v1/moments/{$moment->id}"),
    };

    $response->assertForbidden();

    expect($moment->fresh()->body)->toBe('Unchanged');
})->with([
    'upload an image',
    'create a moment',
    'update a moment',
    'delete a moment',
]);

it('allows a read and write token to read and create moments', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Publisher', ['moments:read', 'moments:write'])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/moments')
        ->assertOk();

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['body' => 'Published'])
        ->assertCreated();

    $this->assertDatabaseHas('moments', ['user_id' => $user->id, 'body' => 'Published']);
});

it('requires the read ability to list moments', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Writer', ['moments:write'])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/moments')
        ->assertForbidden();
});

it('keeps legacy wildcard tokens fully functional', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Legacy')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/moments')
        ->assertOk();

    $this->withToken($token)
        ->postJson('/api/v1/moments', ['body' => 'Legacy publish'])
        ->assertCreated();

    $this->assertDatabaseHas('moments', ['user_id' => $user->id, 'body' => 'Legacy publish']);
});
