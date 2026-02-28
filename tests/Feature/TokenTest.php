<?php

use App\Models\User;

it('lets an authenticated user view the tokens page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/tokens')
        ->assertSuccessful();
});

it('redirects a guest to login', function () {
    $this->get('/tokens')->assertRedirect('/login');
});

it('creates a token with a name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/tokens', ['name' => 'My App'])
        ->assertRedirect('/tokens');

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
        'name' => 'My App',
    ]);
});

it('flashes the plain text token to the session once', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/tokens', ['name' => 'My App'])
        ->assertRedirect('/tokens')
        ->assertSessionHas('plain_text_token');
});

it('requires a name when creating a token', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/tokens', ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('can delete its own token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('To delete');
    $pat = $token->accessToken;

    $this->actingAs($user)
        ->delete("/tokens/{$pat->id}")
        ->assertRedirect('/tokens');

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $pat->id]);
});

it('cannot delete another user\'s token', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $token = $owner->createToken('Not yours');
    $pat = $token->accessToken;

    $this->actingAs($other)
        ->delete("/tokens/{$pat->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('personal_access_tokens', ['id' => $pat->id]);
});
