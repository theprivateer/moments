<?php

namespace App\Actions;

use App\Jobs\PublishMomentToThreads;
use App\Models\Moment;

class DispatchMomentCrossPost
{
    public function handle(Moment $moment, ?bool $crossPostOverride = null): void
    {
        if (! config('moments.threads.enabled')) {
            $this->markSkipped($moment, 'Threads integration is disabled.');

            return;
        }

        $shouldCrossPost = $crossPostOverride ?? (bool) config('moments.threads.default_cross_post', true);
        if (! $shouldCrossPost) {
            $this->markSkipped($moment, 'Cross-posting to Threads was disabled for this moment.');

            return;
        }

        if (blank(config('moments.threads.user_id')) || blank(config('moments.threads.access_token'))) {
            $this->markSkipped($moment, 'Threads credentials are not configured.');

            return;
        }

        if (blank($moment->body)) {
            $this->markSkipped($moment, 'Skipped publishing because the moment has no text body.');

            return;
        }

        $moment->forceFill([
            'threads_status' => 'pending',
            'threads_last_error' => null,
            'threads_post_id' => null,
            'threads_published_at' => null,
        ])->save();

        PublishMomentToThreads::dispatch($moment->id)->afterCommit();
    }

    private function markSkipped(Moment $moment, string $reason): void
    {
        $moment->forceFill([
            'threads_status' => 'skipped',
            'threads_last_error' => $reason,
            'threads_post_id' => null,
            'threads_published_at' => null,
            'threads_attempted_at' => now(),
        ])->save();
    }
}
