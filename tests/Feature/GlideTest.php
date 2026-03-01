<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Glide\Urls\UrlBuilderFactory;

beforeEach(function () {
    config(['moments.glide_sign_key' => 'test-glide-sign-key-1234567890ab']);
});

it('serves a resized image with a valid signature', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('photo.jpg', 1600, 900);
    $path = $file->store('moments', 'public');

    $url = UrlBuilderFactory::create('/img/', config('moments.glide_sign_key'))
        ->getUrl($path, ['w' => 800, 'disk' => 'public']);

    $this->get($url)
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'image/jpeg');
});

it('returns 403 when the signature is missing or invalid', function () {
    $this->get('/img/moments/photo.jpg?w=800&s=tampered-value')
        ->assertForbidden();
});

it('returns 404 for a signed url pointing to a missing image', function () {
    Storage::fake('public');

    $url = UrlBuilderFactory::create('/img/', config('moments.glide_sign_key'))
        ->getUrl('moments/missing.jpg', ['w' => 800, 'disk' => 'public']);

    $this->get($url)->assertNotFound();
});
