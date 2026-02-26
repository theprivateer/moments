<?php

use App\Models\User;

it('creates a user via the install command', function () {
    $this->artisan('moments:install')
        ->expectsQuestion('Name', 'Alice')
        ->expectsQuestion('Email address', 'alice@example.com')
        ->expectsQuestion('Password', 'secret1234')
        ->assertSuccessful();

    expect(User::where('email', 'alice@example.com')->exists())->toBeTrue();
});

it('rejects an invalid email', function () {
    $this->artisan('moments:install')
        ->expectsQuestion('Name', 'Bob')
        ->expectsQuestion('Email address', 'not-an-email')
        ->assertFailed();
});

it('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->artisan('moments:install')
        ->expectsQuestion('Name', 'Carol')
        ->expectsQuestion('Email address', 'taken@example.com')
        ->assertFailed();
});

it('rejects a short password', function () {
    $this->artisan('moments:install')
        ->expectsQuestion('Name', 'Dave')
        ->expectsQuestion('Email address', 'dave@example.com')
        ->expectsQuestion('Password', 'short')
        ->assertFailed();
});
