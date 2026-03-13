<?php

use App\Models\User;

it('redirects a guest to login on GET /account', function () {
    $this->get('/account')->assertRedirect('/login');
});

it('lets an authenticated user view the account page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/account')
        ->assertSuccessful()
        ->assertSee('Profile')
        ->assertSee('Change password')
        ->assertSee('API tokens');
});

it('updates profile and redirects with flash', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/account/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])
        ->assertRedirect('/account')
        ->assertSessionHas('profile_updated');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);
});

it('rejects a duplicate email from another user', function () {
    $other = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/account/profile', [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email');
});

it('allows re-submitting the user\'s own email', function () {
    $user = User::factory()->create(['email' => 'mine@example.com']);

    $this->actingAs($user)
        ->patch('/account/profile', [
            'name' => $user->name,
            'email' => 'mine@example.com',
        ])
        ->assertRedirect('/account')
        ->assertSessionHas('profile_updated');
});

it('validates required name and email on profile update', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/account/profile', ['name' => '', 'email' => ''])
        ->assertSessionHasErrors(['name', 'email']);
});

it('updates password with correct current password', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user)
        ->patch('/account/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect('/account')
        ->assertSessionHas('password_updated');

    $this->assertCredentials(['email' => $user->email, 'password' => 'new-password']);
});

it('rejects a wrong current password', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-password')]);

    $this->actingAs($user)
        ->patch('/account/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password');
});

it('rejects mismatched password confirmation', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    $this->actingAs($user)
        ->patch('/account/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'different',
        ])
        ->assertSessionHasErrors('password');
});

it('requires all three password fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/account/password', [])
        ->assertSessionHasErrors(['current_password', 'password']);
});
