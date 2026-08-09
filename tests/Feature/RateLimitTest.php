<?php

use App\Models\User;

it('throttles repeated failed login attempts', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-password')]);

    $attempt = fn () => $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    foreach (range(1, 6) as $ignored) {
        $attempt()->assertSessionHasErrors('email');
    }

    $attempt()->assertTooManyRequests();
    $this->assertGuest();
});

it('does not throttle viewing the login page', function () {
    foreach (range(1, 10) as $ignored) {
        $this->get('/login')->assertSuccessful();
    }
});

it('throttles api requests beyond the per-minute limit', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    foreach (range(1, 60) as $ignored) {
        $this->withToken($token)->getJson('/api/v1/moments')->assertOk();
    }

    $this->withToken($token)->getJson('/api/v1/moments')->assertTooManyRequests();
});

/*
 * Laravel's middleware priority list sorts AuthenticatesRequests ahead of
 * ThrottleRequests, so auth rejects anonymous callers before the throttle
 * counter is touched. The API limit therefore bounds authenticated abuse
 * rather than token guessing, which a 40-character Sanctum token already
 * makes infeasible. Pinned here so the ordering is not mistaken for a bug.
 */
it('rejects unauthenticated api requests via auth rather than the throttle', function () {
    foreach (range(1, 61) as $ignored) {
        $this->getJson('/api/v1/moments')->assertUnauthorized();
    }
});
