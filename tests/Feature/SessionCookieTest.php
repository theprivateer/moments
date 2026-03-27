<?php

use Illuminate\Support\Str;

it('uses a browser-safe default session cookie name', function () {
    expect(config('session.cookie'))
        ->toBe(Str::slug(config('app.name', 'laravel'), '_').'_session')
        ->and(config('session.cookie'))
        ->toMatch('/^[A-Za-z0-9_]+$/');
});

it('sets the sanitized session cookie on web responses', function () {
    $response = $this->get('/login')->assertSuccessful();

    $setCookieHeaders = $response->headers->all('set-cookie');

    expect($setCookieHeaders)
        ->not->toBeEmpty()
        ->and(collect($setCookieHeaders)->contains(
            fn (string $header): bool => str_starts_with($header, config('session.cookie').'=')
        ))
        ->toBeTrue();
});
