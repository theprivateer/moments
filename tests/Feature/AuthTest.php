<?php

use App\Models\User;

it('shows the login page to guests', function () {
    $this->get('/login')->assertSuccessful();
});

it('lets a user log in with correct credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password and keeps the user as a guest', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-password')]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('lets an authenticated user log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/');

    $this->assertGuest();
});
