<?php

use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->envPath = app()->environmentFilePath();
    $this->envBackup = file_get_contents($this->envPath);
});

afterEach(function () {
    file_put_contents($this->envPath, $this->envBackup);
    Config::set('moments.glide_sign_key', null);
});

it('generates a 32-char key and writes it to .env when key is empty', function () {
    Config::set('moments.glide_sign_key', null);

    $contents = preg_replace('/^GLIDE_SIGN_KEY=.*/m', 'GLIDE_SIGN_KEY=', file_get_contents($this->envPath));
    file_put_contents($this->envPath, $contents);

    $this->artisan('moments:glide-key')->assertSuccessful();

    $written = file_get_contents($this->envPath);
    preg_match('/^GLIDE_SIGN_KEY=(.+)$/m', $written, $matches);

    expect($matches[1])->toHaveLength(32);
});

it('outputs a success message after writing the key', function () {
    Config::set('moments.glide_sign_key', null);

    $contents = preg_replace('/^GLIDE_SIGN_KEY=.*/m', 'GLIDE_SIGN_KEY=', file_get_contents($this->envPath));
    file_put_contents($this->envPath, $contents);

    $this->artisan('moments:glide-key')
        ->expectsOutputToContain('Glide signing key set successfully.')
        ->assertSuccessful();
});

it('warns and does not overwrite when key is already set and --force is not passed', function () {
    Config::set('moments.glide_sign_key', 'existing-key-value-already-set-here');

    $before = file_get_contents($this->envPath);

    $this->artisan('moments:glide-key')
        ->expectsOutputToContain('Glide signing key already set.')
        ->assertSuccessful();

    expect(file_get_contents($this->envPath))->toBe($before);
});

it('overwrites an existing key when --force is passed', function () {
    Config::set('moments.glide_sign_key', 'old-key');

    $contents = preg_replace('/^GLIDE_SIGN_KEY=.*/m', 'GLIDE_SIGN_KEY=old-key', file_get_contents($this->envPath));
    file_put_contents($this->envPath, $contents);

    $this->artisan('moments:glide-key', ['--force' => true])->assertSuccessful();

    $written = file_get_contents($this->envPath);
    preg_match('/^GLIDE_SIGN_KEY=(.+)$/m', $written, $matches);

    expect($matches[1])->toHaveLength(32)->not->toBe('old-key');
});

it('appends GLIDE_SIGN_KEY when the line is absent from .env', function () {
    Config::set('moments.glide_sign_key', null);

    $contents = preg_replace('/^GLIDE_SIGN_KEY=.*\n?/m', '', file_get_contents($this->envPath));
    file_put_contents($this->envPath, $contents);

    $this->artisan('moments:glide-key')->assertSuccessful();

    $written = file_get_contents($this->envPath);
    preg_match('/^GLIDE_SIGN_KEY=(.+)$/m', $written, $matches);

    expect($matches[1])->toHaveLength(32);
});

it('updates runtime config after writing the key', function () {
    Config::set('moments.glide_sign_key', null);

    $contents = preg_replace('/^GLIDE_SIGN_KEY=.*/m', 'GLIDE_SIGN_KEY=', file_get_contents($this->envPath));
    file_put_contents($this->envPath, $contents);

    $this->artisan('moments:glide-key')->assertSuccessful();

    expect(config('moments.glide_sign_key'))->toHaveLength(32);
});
