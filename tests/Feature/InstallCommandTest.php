<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->envPath = app()->environmentFilePath();
    $this->envBackup = file_get_contents($this->envPath);
});

afterEach(function () {
    file_put_contents($this->envPath, $this->envBackup);
    Config::set('moments.glide_sign_key', null);
});

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

it('generates a glide signing key during install', function () {
    Config::set('moments.glide_sign_key', null);

    $contents = preg_replace('/^GLIDE_SIGN_KEY=.*/m', 'GLIDE_SIGN_KEY=', file_get_contents($this->envPath));
    file_put_contents($this->envPath, $contents);

    $this->artisan('moments:install')
        ->expectsQuestion('Name', 'Eve')
        ->expectsQuestion('Email address', 'eve@example.com')
        ->expectsQuestion('Password', 'secret1234')
        ->assertSuccessful();

    $written = file_get_contents($this->envPath);
    preg_match('/^GLIDE_SIGN_KEY=(.+)$/m', $written, $matches);

    expect($matches[1])->toHaveLength(32);
});
