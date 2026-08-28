<?php

use App\Models\User;
use Illuminate\Http\Request;
use Privateer\Moments\Http\Controllers\TokenController;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('lets an authenticated user view the tokens page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/tokens')
        ->assertRedirect('/account');
});

it('redirects a guest to login', function () {
    $this->get('/tokens')->assertRedirect('/login');
});

it('creates a token with a name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/tokens', ['name' => 'My App', 'role' => 'read-only'])
        ->assertRedirect('/account');

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
        'name' => 'My App',
    ]);

    expect($user->tokens()->first()->abilities)->toBe(['moments:read']);
});

it('creates a read and write token', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/tokens', ['name' => 'Publishing App', 'role' => 'read-write'])
        ->assertRedirect('/account');

    expect($user->tokens()->first()->abilities)->toBe(['moments:read', 'moments:write']);
});

it('flashes the plain text token to the session once', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/tokens', ['name' => 'My App', 'role' => 'read-only'])
        ->assertRedirect('/account')
        ->assertSessionHas('plain_text_token');
});

it('requires a name when creating a token', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/tokens', ['name' => '', 'role' => 'read-only'])
        ->assertSessionHasErrors('name');
});

it('requires a valid token role', function (?string $role) {
    $user = User::factory()->create();

    $payload = ['name' => 'My App'];

    if ($role !== null) {
        $payload['role'] = $role;
    }

    $this->actingAs($user)
        ->post('/tokens', $payload)
        ->assertSessionHasErrors('role');
})->with([
    'missing' => null,
    'unknown' => 'administrator',
]);

it('shows token roles on the account page', function () {
    $user = User::factory()->create();
    $user->createToken('Reader', ['moments:read']);
    $user->createToken('Publisher', ['moments:read', 'moments:write']);
    $user->createToken('Legacy');

    $this->actingAs($user)
        ->get('/account')
        ->assertOk()
        ->assertSeeText('Reader')
        ->assertSeeText('Publisher')
        ->assertSeeText('Legacy')
        ->assertSeeText('Read only')
        ->assertSeeText('Read & write');
});

it('can delete its own token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('To delete');
    $pat = $token->accessToken;

    $this->actingAs($user)
        ->delete("/tokens/{$pat->id}")
        ->assertRedirect('/account');

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

it('revokes its own token when tokenable_id arrives as a string', function () {
    $user = User::factory()->create();
    $pat = $user->createToken('Mine')->accessToken;

    // PostgreSQL and MySQL with emulated prepares return bigints as strings.
    $pat->tokenable_id = (string) $user->id;

    $request = Request::create("/tokens/{$pat->id}", 'DELETE');
    $request->setUserResolver(fn () => $user);

    (new TokenController)->destroy($request, $pat);

    $this->assertModelMissing($pat);
});

it('still forbids revoking another user\'s token when ids are strings', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $pat = $owner->createToken('Not yours')->accessToken;

    $pat->tokenable_id = (string) $owner->id;

    $request = Request::create("/tokens/{$pat->id}", 'DELETE');
    $request->setUserResolver(fn () => $other);

    expect(fn () => (new TokenController)->destroy($request, $pat))
        ->toThrow(HttpException::class);

    $this->assertModelExists($pat);
});
