<?php

namespace App\Services\Threads;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ThreadsClient
{
    public function publishText(string $text): string
    {
        $createResponse = $this->request(
            $this->resourcePath('threads'),
            [
                'media_type' => 'TEXT',
                'text' => $text,
            ]
        );

        $creationId = data_get($createResponse, 'id');
        if (! is_string($creationId) || $creationId === '') {
            throw new ThreadsPublishException('Threads create response did not include a creation ID.');
        }

        $publishResponse = $this->request(
            $this->resourcePath('threads_publish'),
            ['creation_id' => $creationId]
        );

        $postId = data_get($publishResponse, 'id');
        if (! is_string($postId) || $postId === '') {
            throw new ThreadsPublishException('Threads publish response did not include a post ID.');
        }

        return $postId;
    }

    /** @return array<string, mixed> */
    private function request(string $path, array $payload): array
    {
        $response = Http::withToken((string) config('moments.threads.access_token'))
            ->acceptJson()
            ->asForm()
            ->post($path, $payload);

        if (! $response->successful()) {
            $body = Str::limit($response->body(), 500);

            throw new ThreadsPublishException(
                "Threads API request failed with status {$response->status()}: {$body}"
            );
        }

        return $response->json();
    }

    private function resourcePath(string $endpoint): string
    {
        $base = rtrim((string) config('moments.threads.api_base'), '/');
        $version = trim((string) config('moments.threads.api_version'), '/');
        $userId = trim((string) config('moments.threads.user_id'));

        return "{$base}/{$version}/{$userId}/{$endpoint}";
    }
}
