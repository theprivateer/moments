<?php

use App\Jobs\PublishMomentToThreads;
use App\Models\Moment;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'moments.threads.enabled' => true,
        'moments.threads.user_id' => '12345',
        'moments.threads.access_token' => 'test-token',
        'moments.threads.api_base' => 'https://graph.threads.net',
        'moments.threads.api_version' => 'v1.0',
        'moments.threads.max_text' => 20,
    ]);
});

it('publishes a moment to Threads and stores the published status', function () {
    Http::fake([
        'https://graph.threads.net/v1.0/12345/threads' => Http::response(['id' => 'creation-1'], 200),
        'https://graph.threads.net/v1.0/12345/threads_publish' => Http::response(['id' => 'post-1'], 200),
    ]);

    $moment = Moment::factory()->for(User::factory())->create([
        'body' => 'Hello from Moments',
        'threads_status' => 'pending',
    ]);

    (new PublishMomentToThreads($moment->id))->handle(app(\App\Services\Threads\ThreadsClient::class));

    $moment->refresh();

    expect($moment->threads_status)->toBe('published');
    expect($moment->threads_post_id)->toBe('post-1');
    expect($moment->threads_published_at)->not->toBeNull();
    expect($moment->threads_last_error)->toBeNull();
});

it('marks the moment as failed when Threads API returns an error', function () {
    Http::fake([
        'https://graph.threads.net/v1.0/12345/threads' => Http::response(['error' => 'bad request'], 400),
    ]);

    $moment = Moment::factory()->for(User::factory())->create([
        'body' => 'This will fail',
        'threads_status' => 'pending',
    ]);

    expect(fn () => (new PublishMomentToThreads($moment->id))->handle(app(\App\Services\Threads\ThreadsClient::class)))
        ->toThrow(RuntimeException::class);

    $moment->refresh();
    expect($moment->threads_status)->toBe('failed');
    expect($moment->threads_last_error)->not->toBeNull();
});

it('truncates oversized body text before publishing', function () {
    Http::fake([
        'https://graph.threads.net/v1.0/12345/threads' => Http::response(['id' => 'creation-2'], 200),
        'https://graph.threads.net/v1.0/12345/threads_publish' => Http::response(['id' => 'post-2'], 200),
    ]);

    $moment = Moment::factory()->for(User::factory())->create([
        'body' => str_repeat('A', 25),
        'threads_status' => 'pending',
    ]);

    (new PublishMomentToThreads($moment->id))->handle(app(\App\Services\Threads\ThreadsClient::class));

    Http::assertSent(fn (Request $request) => $request->url() === 'https://graph.threads.net/v1.0/12345/threads'
        && $request['text'] === str_repeat('A', 19).'…');
});
