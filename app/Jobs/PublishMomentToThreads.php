<?php

namespace App\Jobs;

use App\Models\Moment;
use App\Services\Threads\ThreadsClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PublishMomentToThreads implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $timeout = 30;

    public function __construct(public int $momentId)
    {
        //
    }

    public function handle(ThreadsClient $threads): void
    {
        $moment = Moment::query()->find($this->momentId);
        if (! $moment) {
            return;
        }

        if ($moment->threads_status === 'published' && filled($moment->threads_post_id)) {
            return;
        }

        $body = trim((string) $moment->body);
        if ($body === '') {
            $moment->forceFill([
                'threads_status' => 'skipped',
                'threads_last_error' => 'Skipped publishing because the moment has no text body.',
                'threads_attempted_at' => now(),
            ])->save();

            return;
        }

        $moment->forceFill([
            'threads_attempted_at' => now(),
            'threads_status' => 'pending',
        ])->save();

        try {
            $postId = $threads->publishText($this->prepareBody($body));
        } catch (Throwable $exception) {
            $moment->forceFill([
                'threads_status' => 'failed',
                'threads_last_error' => Str::limit($exception->getMessage(), 1000),
            ])->save();

            Log::warning('Threads cross-post failed for moment.', [
                'moment_id' => $moment->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $moment->forceFill([
            'threads_status' => 'published',
            'threads_post_id' => $postId,
            'threads_last_error' => null,
            'threads_published_at' => now(),
        ])->save();
    }

    private function prepareBody(string $body): string
    {
        $max = max(1, (int) config('moments.threads.max_text', 500));
        if (mb_strlen($body) <= $max) {
            return $body;
        }

        $suffix = '…';
        $limit = max(1, $max - mb_strlen($suffix));

        return mb_substr($body, 0, $limit).$suffix;
    }
}
